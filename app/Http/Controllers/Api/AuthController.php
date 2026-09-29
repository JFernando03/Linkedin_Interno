<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\UpdatePerfilRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $usuario = User::where('email', $request->email)->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'usuario' => $usuario
        ], 200);
    }

    public function perfil(Request $request)
    {
        //En un futuro agregar mas cosas del usuario: 
        //$request->user()->load(['follows', 'followers', 'publicaciones']);
        $usuario = $request->user();
        $usuario->load('alumno.carrera');
        
        return response()->json([
            'success' => true,
            'message' => 'Perfil obtenido correctamente',
            'usuario' => $request->user()
        ], 200);
    }

    public function updatePerfil(UpdatePerfilRequest $request)
    {
        $usuario = $request->user();
        $alumno = $usuario->alumno;

        DB::transaction(function () use ($request, $usuario, $alumno) {
            
            // Actualizar solo los datos del usuario proporcionados
            $usuario->fill($request->only(['sobreMi', 'email', 'telefono']));
            $usuario->save();

            // Lo mismo para la tabla alumno, pero se guarda para modificar el curriculum
            $dataAlumno = $request->only(['nombre', 'apellidoPaterno', 'apellidoMaterno', 'idCarrera','semestre']);
            
            if ($request->hasFile('curriculum')) {
                // Borrar el currículum anterior si ya existía para no saturar disco
                if ($alumno && $alumno->curriculum && Storage::disk('public')->exists($alumno->curriculum)) {
                    Storage::disk('public')->delete($alumno->curriculum);
                }

                // Guardar el nuevo archivo en la carpeta 'curriculums'
                $path = $request->file('curriculum')->store('curriculums', 'public');
                $dataAlumno['curriculum'] = $path;
            }

            // Actualizar datos del alumno
            if ($alumno) {
                $alumno->update($dataAlumno);
            } else {
                // Si por alguna razón no existía el registro de alumno asociado todavía
                $usuario->alumno()->create($dataAlumno);
            }
        });

        // Cargar relaciones para mostrar el perfil actualizado completo
        $usuario->load('alumno.carrera');

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado exitosamente',
            'usuario' => $usuario
        ], 200);
    }
}
