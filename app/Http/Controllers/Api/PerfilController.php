<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHabilidadRequest;
use App\Http\Requests\UpdatePerfilRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    public function show(Request $request){
        $usuario = $request->user();

        // Arreglo base de relaciones, tiene info del rol del usuario
        $relations = ['rol'];

        // Si el usuario tiene un perfil de alumno
        if ($usuario->alumno) {
            $relations[] = 'alumno.carrera';
            $relations[] = 'alumno.habilidades';
        }

        // Si el usuario tiene un perfil de empresa
        if ($usuario->empresa) {
            $relations[] = 'empresa.industria';
        }

        $usuario->load($relations);

        return response()->json([
            'success' => true,
            'message' => 'Perfil obtenido correctamente',
            'usuario' => $usuario,
        ],200);
    }

    public function update(UpdatePerfilRequest $request)
    {
        $usuario = $request->user();
        $alumno = $usuario->alumno;
        $empresa = $usuario->empresa;

        DB::transaction(function () use ($request, $usuario, $alumno, $empresa) {
            // 1. Actualizar datos base de la tabla usuarios
            $dataUsuario = $request->only('sobreMi', 'email', 'telefono');

            if ($request->hasFile('avatar')) {
                if ($usuario->avatar && Storage::disk('public')->exists($usuario->avatar)) {
                    Storage::disk('public')->delete($usuario->avatar);
                }
                $dataUsuario['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }

            $usuario->fill($dataUsuario);
            $usuario->save();

            // 2. Actualizar datos de Alumno si existe
            if ($alumno) {
                $dataAlumno = $request->only('nombre', 'apellidoPaterno', 'apellidoMaterno', 'idCarrera', 'semestre');

                if ($request->hasFile('curriculum')) {
                    if ($alumno->curriculum && Storage::disk('public')->exists($alumno->curriculum)) {
                        Storage::disk('public')->delete($alumno->curriculum);
                    }
                    $dataAlumno['curriculum'] = $request->file('curriculum')->store('curriculums', 'public');
                }

                $alumno->update($dataAlumno);
            }

            // 3. Actualizar datos de Empresa si existe
            if ($empresa) {
                $dataEmpresa = $request->only('nombreEmpresa', 'idIndustria', 'paginaWeb');
                $empresa->update($dataEmpresa);
            }
        });

        // Cargar dinámicamente las relaciones para la respuesta
        $relaciones = [];
        if ($usuario->alumno) {
            $relaciones[] = 'alumno.carrera';
            $relaciones[] = 'alumno.habilidades';
        }
        if ($usuario->empresa) {
            $relaciones[] = 'empresa.industria';
        }

        $usuario->load($relaciones);

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado exitosamente',
            'usuario' => $usuario,
        ], 200);
    }

    public function storeHabilidad(StoreHabilidadRequest $request){
        $usuario = $request->user();
        $alumno = $usuario->alumno;

        if (!$alumno) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario autenticado no cuenta con un perfil de alumno',
            ], 400);
        }

        $alumno->habilidades()->attach($request->idHabilidad, [
            'idNivel' => $request->idNivel
        ]);

        $usuario->load(['alumno.carrera', 'alumno.habilidades']);

        return response()->json([
            'success' => true,
            'message' => 'Habilidad agregada exitosamente al perfil.',
            'usuario' => $usuario
        ], 201);
    }

    public function destroyHabilidad(Request $request, $idAlumnoHabilidad)
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
            'usuario' => $usuario
        ], 200);
    }
}
