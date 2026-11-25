<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EstadoPostulacion;

class EstadoPostulacionController extends Controller
{
    /**
     * Devuelve el catálogo de estados de postulación.
     */
    public function index()
    {
        $estados = EstadoPostulacion::select(['idEstadoPostulacion', 'nombre'])
            ->orderBy('idEstadoPostulacion')
            ->get();

        return response()->json([
            'success' => true,
            'estados' => $estados,
        ], 200);
    }
}