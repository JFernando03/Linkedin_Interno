<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
}
