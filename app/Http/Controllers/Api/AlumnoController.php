<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlumnoRequest;
use App\Models\Alumno;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AlumnoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAlumnoRequest $request)
    {
        try {
            $data = $request->validated();

            $resultado = DB::transaction(function () use ($data) {
                
                // Crear el registro en la tabla 'usuarios'
                $usuario = User::create([
                    'idRol' => $data['idRol'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'telefono' => $data['telefono'],
                    'sobreMi' => $data['sobreMi'] ?? null,
                ]);

                $curriculum = $data['curriculum'];

                $curriculumPath = $curriculum->store('curriculums','public');
                $data['curriculum'] = $curriculumPath;

                // Crear el registro en la tabla 'alumnos' usando el idUsuario generado
                $alumno = Alumno::create([
                    'idUsuario' => $usuario->idUsuario,
                    'nombre' => $data['nombre'],
                    'apellidoPaterno' => $data['apellidoPaterno'],
                    'apellidoMaterno' => $data['apellidoMaterno'],
                    'idCarrera' => $data['idCarrera'],
                    'curriculum' => $data['curriculum'],
                ]);

                return [
                    'usuario' => $usuario,
                    'alumno' => $alumno
                ];
            });

            return response()->json([
                'message' => 'Alumno registrado exitosamente',
                'data' => $resultado
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error en el servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
