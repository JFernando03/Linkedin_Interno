<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class BuscadorController extends Controller
{
    public function buscar(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
        ]);

        $termino = trim((string) $request->input('q', ''));

        if (mb_strlen($termino) < 2) {
            return response()->json([
                'success' => true,
                'usuarios' => [],
            ], 200);
        }

        // Escapar comodines % y _ para evitar búsquedas amplias no deseadas en LIKE/ILIKE
        $like = '%' . addcslashes($termino, '%_\\') . '%';

        $usuarios = User::query()
            ->leftJoin('alumnos', 'usuarios.idUsuario', '=', 'alumnos.idUsuario')
            ->leftJoin('empresas', 'usuarios.idUsuario', '=', 'empresas.idUsuario')
            ->select([
                'usuarios.idUsuario',
                'usuarios.idRol',
                'usuarios.email',
                'usuarios.avatar',
                'alumnos.nombre',
                'alumnos.apellidoPaterno',
                'alumnos.apellidoMaterno',
                'empresas.nombreEmpresa',
            ])
            ->where(function ($query) use ($like) {
                $query->where('empresas.nombreEmpresa', 'ilike', $like)
                    ->orWhere('alumnos.nombre', 'ilike', $like)
                    ->orWhere('alumnos.apellidoPaterno', 'ilike', $like)
                    ->orWhere('alumnos.apellidoMaterno', 'ilike', $like)
                    ->orWhereRaw(
                        'CONCAT_WS(\' \', alumnos.nombre, alumnos."apellidoPaterno", alumnos."apellidoMaterno") ILIKE ?',
                        [$like]
                    );
            })
            ->limit(10)
            ->get()
            ->map(function ($u) {
                $nombreCompleto = trim(implode(' ', array_filter([
                    $u->nombre,
                    $u->apellidoPaterno,
                    $u->apellidoMaterno,
                ])));

                return [
                    'idUsuario' => $u->idUsuario,
                    'idRol'     => $u->idRol,
                    'tipo'      => $u->nombreEmpresa ? 'empresa' : 'alumno',
                    'titulo'    => $u->nombreEmpresa ?: $nombreCompleto,
                    'subtitulo' => $u->nombreEmpresa ? $u->email : ($nombreCompleto ?: $u->email),
                    'avatar'    => $u->avatar,
                ];
            });

        return response()->json([
            'success' => true,
            'usuarios' => $usuarios,
        ], 200);
    }
}