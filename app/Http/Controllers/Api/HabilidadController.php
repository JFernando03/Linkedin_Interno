<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHabilidadRequest;
use App\Models\Habilidad;
use Illuminate\Http\Request;

class HabilidadController extends Controller
{
    public function index(Request $request)
    {
        $habilidades = Habilidad::orderBy('nombre', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Habilidades Obtenidas correctamente',
            'habilidades' => $habilidades,
        ], 200);
    }

    public function store(StoreHabilidadRequest $request)
    {
        $usuario = $request->user();
        $alumno = $usuario->alumno;

        if (!$alumno) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario autenticado no cuenta con un perfil de alumno',
            ], 400);
        }

        $alumno->habilidades()->attach($request->idHabilidad, [
            'idNivel' => $request->idNivel,
        ]);

        $usuario->load(['alumno.carrera', 'alumno.habilidades']);

        return response()->json([
            'success' => true,
            'message' => 'Habilidad agregada exitosamente al perfil.',
            'usuario' => $usuario,
        ], 201);
    }

    public function destroy(Request $request, $idAlumnoHabilidad)
    {
        $usuario = $request->user();
        $alumno = $usuario->alumno;

        if (!$alumno) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil de alumno no encontrado.',
            ], 404);
        }

        $eliminado = $alumno->habilidades()
            ->wherePivot('id_alumno_habilidad', $idAlumnoHabilidad)
            ->detach();

        if (!$eliminado) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la habilidad en tu perfil.',
            ], 404);
        }

        $usuario->load(['alumno.carrera', 'alumno.habilidades']);

        return response()->json([
            'success' => true,
            'message' => 'Habilidad eliminada del perfil correctamente.',
            'usuario' => $usuario,
        ], 200);
    }
}