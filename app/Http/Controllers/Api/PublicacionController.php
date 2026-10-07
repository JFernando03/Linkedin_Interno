<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicacionRequest;
use App\Models\Publicacion;
use Illuminate\Http\Request;

class PublicacionController extends Controller
{
    public function store(StorePublicacionRequest $request){
        try{
            $data = $request->validated();

            //Manejar imagen o video, si viene en el request
            if($request->hasFile('multimedia')){
                $data['multimedia'] = $request->file('multimedia')->store('publicaciones','public');
            }

            //Crear publicacion
            $publicacion = Publicacion::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Publicacion creada exitosamente',
                'publicacion' => $publicacion,
            ],201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error en el servidor',
                'error' => $e->getMessage(),
            ],500);
        }
    }
}
