<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostulacionRequest;
use App\Http\Requests\UpdateEstadoPostulacionRequest;
use App\Models\Notificacion;
use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostulacionController extends Controller
{
    /**
     * Alumno se postula a una vacante y dispara notificación a la empresa.
     */
    public function store(StorePostulacionRequest $request)
    {
        $validated = $request->validated();
        $usuario = $request->user();
        $vacante = Vacante::findOrFail($validated['idVacante']);

        $yaPostulado = Postulacion::where('idVacante', $vacante->idVacante)
            ->where('idUsuario', $usuario->idUsuario)
            ->exists();

        if ($yaPostulado) {
            return response()->json([
                'success' => false,
                'message' => 'Ya te has postulado a esta vacante.',
            ], 409);
        }

        $postulacion = Postulacion::create([
            'idVacante'           => $vacante->idVacante,
            'idUsuario'           => $usuario->idUsuario,
            'idEstadoPostulacion' => 1, // Pendiente
            'mensaje'             => $validated['mensaje'] ?? null,
        ]);

        // Disparar alerta en MongoDB sin romper la postulación si falla Mongo
        try {
            $nombreAlumno = trim(($usuario->nombre ?? '') . ' ' . ($usuario->apellido_paterno ?? $usuario->apellidos ?? ''));
            if ($nombreAlumno === '') {
                $nombreAlumno = 'Un alumno';
            }

            Notificacion::create([
                'idUsuario' => (int) $vacante->idUsuario,
                'titulo'    => 'Nueva postulación',
                'mensaje'   => "{$nombreAlumno} se ha postulado a la vacante: {$vacante->puesto}",
                'tipo'      => 'nueva_postulacion',
                'data'      => [
                    'idVacante'       => (int) $vacante->idVacante,
                    'idPostulacion'   => (int) $postulacion->idPostulacion,
                    'idUsuarioAlumno' => (int) $usuario->idUsuario,
                ],
                'leido'     => false,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al crear notificación de postulación en MongoDB: ' . $e->getMessage());
        }

        $postulacion->load(['vacante', 'estado']);

        return response()->json([
            'success'     => true,
            'message'     => 'Postulación enviada correctamente.',
            'postulacion' => $postulacion,
        ], 201);
    }

    /**
     * Lista las postulaciones del alumno autenticado.
     */
    public function indexPostulacionesAlumno(Request $request)
    {
        $usuario = $request->user();

        if ((int) $usuario->idRol !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Solo los alumnos pueden consultar sus postulaciones.',
            ], 403);
        }

        $postulaciones = Postulacion::with(['vacante.usuario.empresa', 'vacante.modalidad', 'estado'])
            ->where('idUsuario', $usuario->idUsuario)
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json([
            'success'       => true,
            'postulaciones' => $postulaciones,
        ], 200);
    }

    /**
     * Lista los postulantes de una vacante (solo empresa dueña o admin).
     */
    public function indexPorVacante(Request $request, int $idVacante)
    {
        $usuario = $request->user();
        $vacante = Vacante::findOrFail($idVacante);

        $esAdmin = (int) $usuario->idRol === 3;
        $esDueno = (int) $vacante->idUsuario === (int) $usuario->idUsuario;

        if (!$esAdmin && !$esDueno) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver los postulantes de esta vacante.',
            ], 403);
        }

        $postulaciones = Postulacion::with(['usuario.alumno', 'estado'])
            ->where('idVacante', $vacante->idVacante)
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json([
            'success'       => true,
            'postulaciones' => $postulaciones,
        ], 200);
    }

    /**
     * Cambia el estado de una postulación (solo empresa dueña o admin).
     */
    public function updateEstado(UpdateEstadoPostulacionRequest $request, int $idPostulacion)
    {
        $validated = $request->validated();
        $usuario = $request->user();

        $postulacion = Postulacion::with('vacante')->findOrFail($idPostulacion);

        $esAdmin = (int) $usuario->idRol === 3;
        $esDueno = (int) $postulacion->vacante->idUsuario === (int) $usuario->idUsuario;

        if (!$esAdmin && !$esDueno) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para modificar esta postulación.',
            ], 403);
        }

        $postulacion->update([
            'idEstadoPostulacion' => (int) $validated['idEstadoPostulacion'],
        ]);

        $postulacion->load('estado');

        return response()->json([
            'success'     => true,
            'message'     => 'Estado de la postulación actualizado.',
            'postulacion' => $postulacion,
        ], 200);
    }
}