<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interes;
use App\Models\User;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InteresController extends Controller
{
    public function store(Request $request, $idAlumno)
    {
        $usuario = $request->user();

        // Validar que solo empresas o administradores muestren interés
        if ((int) $usuario->idRol !== 2 && (int) $usuario->idRol !== 3) {
            return response()->json([
                'success' => false,
                'message' => 'Solo las empresas pueden mostrar interés en los alumnos.'
            ], 403);
        }

        // Verificar que el alumno exista y sea Rol 1
        $alumno = User::where('idUsuario', $idAlumno)->where('idRol', 1)->first();

        if (!$alumno) {
            return response()->json([
                'success' => false,
                'message' => 'Alumno no encontrado.'
            ], 404);
        }

        // 1. Registrar interes
        $interes = Interes::firstOrCreate([
            'idEmpresa' => $usuario->idUsuario,
            'idAlumno' => $alumno->idUsuario,
        ]);

        // Cargar datos de la empresa
        $usuario->load('empresa');
        $nombreEmpresa = $usuario->empresa->nombreEmpresa ?? $usuario->name;

        // 2. Guardar la notificación en MongoDB
        Notificacion::create([
            'idUsuario' => (int) $alumno->idUsuario,
            'titulo' => '¡Nuevo interés en tu perfil!',
            'mensaje' => "La empresa {$nombreEmpresa} ha mostrado interés en tu perfil profesional.",
            'idEmpresa' => (int) $usuario->idUsuario,
            'tipo' => 'interes_empresa',
            'data' => [
                'idEmpresa' => (int) $usuario->idUsuario
            ],
            'leido' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Interés registrado y notificación enviada con éxito.',
            'interes' => $interes
        ], 201);
    }

    public function misNotificaciones(Request $request)
    {
        $usuarioId = $request->user()->idUsuario;

        $notificaciones = Notificacion::where('idUsuario', (int) $usuarioId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'notificaciones' => $notificaciones
        ], 200);
    }
}