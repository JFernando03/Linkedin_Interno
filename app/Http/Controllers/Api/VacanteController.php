<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexVacanteRequest;
use App\Http\Requests\StoreVacanteRequest;
use App\Http\Requests\UpdateVacanteRequest;
use App\Models\Vacante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VacanteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexVacanteRequest $request)
    {
        $validated = $request->validated();

        $query = Vacante::query()
            ->select([
                'idVacante',
                'idUsuario',
                'idModalidad',
                'puesto',
                'contenido',
                'sueldo_min',
                'sueldo_max',
                'multimedia',
                'published_at',
                'created_at',
                'updated_at',
            ]);

        if (!empty($validated['q'])) {
            $like = '%' . addcslashes(trim($validated['q']), '%_\\') . '%';
            $query->where(function ($sub) use ($like) {
                $sub->where('puesto', 'ilike', $like)
                    ->orWhere('contenido', 'ilike', $like);
            });
        }

        if (!empty($validated['idModalidad'])) {
            $query->where('idModalidad', (int) $validated['idModalidad']);
        }

        if (isset($validated['sueldo_min'])) {
            $sueldoMin = (float) $validated['sueldo_min'];
            $query->where(function ($sub) use ($sueldoMin) {
                $sub->where('sueldo_max', '>=', $sueldoMin)
                    ->orWhere(function ($inner) use ($sueldoMin) {
                        $inner->whereNull('sueldo_max')
                            ->where('sueldo_min', '>=', $sueldoMin);
                    });
            });
        }

        if (isset($validated['sueldo_max'])) {
            $sueldoMax = (float) $validated['sueldo_max'];
            $query->where(function ($sub) use ($sueldoMax) {
                $sub->where('sueldo_min', '<=', $sueldoMax)
                    ->orWhere(function ($inner) use ($sueldoMax) {
                        $inner->whereNull('sueldo_min')
                            ->where('sueldo_max', '<=', $sueldoMax);
                    });
            });
        }

        $vacantes = $query
            ->orderByDesc('published_at')
            ->orderByDesc('idVacante')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Vacantes obtenidas correctamente',
            'data'    => $vacantes->items(),
            'meta'    => [
                'current_page' => $vacantes->currentPage(),
                'last_page'    => $vacantes->lastPage(),
                'per_page'     => $vacantes->perPage(),
                'total'        => $vacantes->total(),
            ],
        ], 200);
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
    public function update(UpdateVacanteRequest $request, $idVacante)
    {
        try {
            // 1. Buscar la vacante
            $vacante = Vacante::find($idVacante);

            if (!$vacante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacante no encontrada',
                ], 404);
            }

            // 2. Verificar que sea la empresa creadora
            if ($vacante->idUsuario !== $request->user()->idUsuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes autorización para editar esta vacante',
                ], 403);
            }

            $data = $request->validated();

            // 3. Manejar archivo multimedia si se envía uno nuevo
            if ($request->hasFile('multimedia')) {
                if ($vacante->multimedia && Storage::disk('public')->exists($vacante->multimedia)) {
                    Storage::disk('public')->delete($vacante->multimedia);
                }

                $data['multimedia'] = $request->file('multimedia')->store('vacantes', 'public');
            }

            // 4. Actualizar registro
            $vacante->update($data);

            // Cargar relaciones para la respuesta
            $vacante->load(['usuario.empresa', 'modalidad']);
            $vacante->setAttribute('esMia', true);

            return response()->json([
                'success' => true,
                'message' => 'Vacante actualizada exitosamente',
                'vacante' => $vacante
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error en el servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $idVacante)
    {
        try {
            // 1. Buscar la vacante
            $vacante = Vacante::find($idVacante);

            if (!$vacante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacante no encontrada',
                ], 404);
            }

            $usuario = $request->user();
            $esAdmin = (int) $usuario->idRol === 3;
            $esOwner = (int) $vacante->idUsuario === (int) $usuario->idUsuario;

            // Verificar permisos (Solo el dueño o un Administrador)
            if (!$esAdmin && !$esOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes autorización para eliminar esta vacante',
                ], 403);
            }

            // 3. Eliminar el archivo multimedia asociado si existe
            if ($vacante->multimedia && Storage::disk('public')->exists($vacante->multimedia)) {
                Storage::disk('public')->delete($vacante->multimedia);
            }

            // 4. Borrar el registro de la base de datos
            $vacante->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vacante eliminada exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error en el servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
