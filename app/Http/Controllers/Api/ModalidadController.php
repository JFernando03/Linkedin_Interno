<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modalidad;
use Illuminate\Http\Request;

class ModalidadController extends Controller
{
    public function index()
    {
        try {
            $modalidades = Modalidad::all();

            return response()->json([
                'success' => true,
                'modalidades' => $modalidades
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las modalidades',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}