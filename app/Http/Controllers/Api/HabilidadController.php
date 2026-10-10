<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habilidad;
use Illuminate\Http\Request;

class HabilidadController extends Controller
{
    public function index(Request $request){
        $habilidades = Habilidad::orderBy('nombre','asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Habilidades Obtenidas correctamente',
            'habilidades' => $habilidades,
        ],200);
    }
}
