<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmpresaRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmpresaController extends Controller
{
    public function store(StoreEmpresaRequest $request)
    {
        $empresa = DB::transaction(function () use ($request) {
            // 1. Preparamos todos los datos del usuario base
            $dataUsuario = [
                'idRol' => $request->idRol,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefono' => $request->telefono,
                'sobreMi' => $request->sobreMi,
            ];

            // 2. Si se envió un avatar, lo guardamos en el disco public
            if ($request->hasFile('avatar')) {
                $dataUsuario['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }

            // 3. Creamos el usuario base
            $usuario = User::create($dataUsuario);

            // 4. Creamos el registro específico de la empresa vinculado al usuario
            $empresa = $usuario->empresa()->create([
                'nombreEmpresa' => $request->nombreEmpresa,
                'idIndustria' => $request->idIndustria,
                'paginaWeb' => $request->paginaWeb,
            ]);

            return $empresa;
        });

        return response()->json([
            'success' => true,
            'message' => 'Empresa registrada correctamente',
            'empresa' => $empresa->load('usuario')
        ], 201);
    }
}
