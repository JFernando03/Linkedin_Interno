<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nivel;
use Illuminate\Http\Request;

class NivelController extends Controller
{
    public function index(Request $request)
    {
        $niveles = Nivel::orderBy('idNivel', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Niveles obtenidos correctamente',
            'niveles' => $niveles
        ], 200);
    }
}
