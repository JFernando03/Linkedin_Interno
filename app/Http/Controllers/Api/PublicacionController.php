<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicacionRequest;
use App\Http\Requests\UpdatePublicacionRequest;
use App\Models\Publicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicacionController extends Controller
{
    public function index(Request $request){
        $publicaciones = Publicacion::with('usuario')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Publicaciones obtenidas correctamente',
            'publicaciones' => $publicaciones
        ], 200);
    }

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

    public function update(UpdatePublicacionRequest $request, $idPublicacion){
        try{
            // Buscar la publicación
            $publicacion = Publicacion::find($idPublicacion);

            if(!$publicacion){
                return response()->json([
                    'success' => false,
                    'message' => 'Publicación no encontrada',
                ]);
            }

            $data = $request->validated();

            //Manejar el archivo multimedia si se envia uno nuevo
            if($request->hasFile('multimedia')){
                //Si tenia un archivo lo eliminamos del Storage publico
                if($publicacion->multimedia && Storage::disk('public')->exists($publicacion->multimedia)){
                    Storage::disk('public')->delete($publicacion->multimedia);
                }

                //Guardar nueva multimedia
                $data['multimedia'] = $request->file('multimedia')->store('publicaciones','public');
            }

            //Actualizar publicacion con los datos validados
            $publicacion->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Publicación actualizada exitosamente',
                'publicacion' => $publicacion
            ]);
        } catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error en el servidor',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Request $request, $idPublicacion){
        try{
            $usuario = $request->user();

            //Buscar la publicacion
            $publicacion = Publicacion::where('idPublicacion', $idPublicacion)
                                        ->where('idUsuario', $usuario->idUsuario) 
                                        ->first();
                                        
            if(!$publicacion){
                return response()->json([
                    'success' => false,
                    'message' => 'Publicacion no encontrado',
                ], 404);
            }

            //Si tiene un archivo multimedia, eliminarlo del Storage público
            if($publicacion->multimedia && Storage::disk('public')->exists($publicacion->multimedia)){
                Storage::disk('public')->delete($publicacion->multimedia);
            }
            
            //Eliminar el registro de la base de datos
            $publicacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Eliminación de publicacion exitosa',
            ],200);
        } catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error en el servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
