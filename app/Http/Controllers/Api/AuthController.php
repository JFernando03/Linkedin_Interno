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
            
            // Datos a actualizar del usuario
            $dataUsuario = $request->only(['sobreMi', 'email', 'telefono']);

            // Manejar la foto de perfil (avatar)
            if ($request->hasFile('avatar')) {
                // Borrar el avatar anterior si ya existía para no saturar disco
                if ($usuario->avatar && Storage::disk('public')->exists($usuario->avatar)) {
                    Storage::disk('public')->delete($usuario->avatar);
                }

                // Guardar el nuevo avatar en la carpeta 'avatars'
                $pathAvatar = $request->file('avatar')->store('avatars', 'public');
                $dataUsuario['avatar'] = $pathAvatar;
            }

            // Actualizar los datos del usuario
            $usuario->fill($dataUsuario);
            $usuario->save();

            // Datos para la tabla alumno
            $dataAlumno = $request->only(['nombre', 'apellidoPaterno', 'apellidoMaterno', 'idCarrera', 'semestre']);
            
            if ($request->hasFile('curriculum')) {
                // Borrar el currículum anterior si ya existía
                if ($alumno && $alumno->curriculum && Storage::disk('public')->exists($alumno->curriculum)) {
                    Storage::disk('public')->delete($alumno->curriculum);
                }

                // Guardar el nuevo archivo en la carpeta 'curriculums'
                $pathCurriculum = $request->file('curriculum')->store('curriculums', 'public');
                $dataAlumno['curriculum'] = $pathCurriculum;
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
