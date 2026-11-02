<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVacanteRequest;
use App\Models\Vacante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VacanteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVacanteRequest $request)
    {
        try {
            $data = $request->validated();
            
            $data['idUsuario'] = $request->user()->idUsuario;

            // Guardar archivo multimedia si viene en el Request
            if ($request->hasFile('multimedia')) {
                $data['multimedia'] = $request->file('multimedia')->store('vacantes', 'public');
            }

            $vacante = Vacante::create($data);

            $vacante->load(['usuario.empresa', 'modalidad']);

            return response()->json([
                'success' => true,
                'message' => 'Vacante creada exitosamente',
                'vacante' => $vacante,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error en el servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
