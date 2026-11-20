<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Publicacion;
use App\Models\Vacante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $usuarioId = $request->user()->idUsuario;
        $perPage = 10;
        $page = $request->input('page', 1);

        $publicacionesQuery = DB::table('publicaciones')
            ->select(
                'idPublicacion as id',
                'idUsuario',
                'published_at',
                DB::raw("'publicacion' as tipo")
            );

        $vacantesQuery = DB::table('vacantes')
            ->select(
                'idVacante as id',
                'idUsuario',
                'published_at',
                DB::raw("'vacante' as tipo")
            );

        $unionQuery = $publicacionesQuery->unionAll($vacantesQuery);

        $paginatedItems = DB::query()
            ->fromSub($unionQuery, 'feed')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $itemsTransformados = collect($paginatedItems->items())->map(function ($item) use ($usuarioId) {
            if ($item->tipo === 'publicacion') {
                $objeto = Publicacion::with(['usuario.alumno', 'usuario.empresa'])->find($item->id);
                if ($objeto) {
                    $objeto->setAttribute('tipo', 'publicacion');
                    $objeto->setAttribute('esMia', (int) $objeto->idUsuario === (int) $usuarioId);
                }
                return $objeto;
            } else {
                $objeto = Vacante::with(['usuario.empresa', 'modalidad'])->find($item->id);
                if ($objeto) {
                    $objeto->setAttribute('tipo', 'vacante');
                    $objeto->setAttribute('esMia', (int) $objeto->idUsuario === (int) $usuarioId);
                }
                return $objeto;
            }
        })->filter();

        $resultadoPaginado = new \Illuminate\Pagination\LengthAwarePaginator(
            $itemsTransformados,
            $paginatedItems->total(),
            $paginatedItems->perPage(),
            $paginatedItems->currentPage(),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'success' => true,
            'message' => 'Feed obtenido correctamente',
            'feed' => $resultadoPaginado
        ], 200);
    }
}