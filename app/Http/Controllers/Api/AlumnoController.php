<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexAlumnoRequest;
use App\Http\Requests\StoreAlumnoRequest;
use App\Models\Alumno;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AlumnoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexAlumnoRequest $request)
    {
        $validated = $request->validated();

        $query = User::query()
            ->join('alumnos', 'usuarios.idUsuario', '=', 'alumnos.idUsuario')
            ->select('usuarios.*', 'alumnos.*');

        if (!empty($validated['idCarrera'])) {
            $query->where('alumnos.idCarrera', (int) $validated['idCarrera']);
        }

        if (!empty($validated['semestre'])) {
            $query->where('alumnos.semestre', (int) $validated['semestre']);
        }

        if (array_key_exists('tiene_curriculum', $validated) && $validated['tiene_curriculum'] !== null) {
            if ($request->boolean('tiene_curriculum')) {
                $query->whereNotNull('alumnos.curriculum')
                    ->where('alumnos.curriculum', '!=', '');
            } else {
                $query->where(function ($sub) {
                    $sub->whereNull('alumnos.curriculum')
                        ->orWhere('alumnos.curriculum', '');
                });
            }
        }

        if (!empty($validated['habilidades'])) {
            $habilidades = array_filter(array_map('trim', explode(',', $validated['habilidades'])));

            foreach ($habilidades as $skill) {
                $likeSkill = '%' . addcslashes($skill, '%_\\') . '%';
                $query->whereHas('alumno.habilidades', function ($qSkill) use ($likeSkill) {
                    $qSkill->where('nombre', 'ilike', $likeSkill);
                });
            }
        }

        $alumnos = $query->get();

        return response()->json([
            'success'    => true,
            'candidatos' => $alumnos,
        ], 200);
    }

    private function normalizarLista(mixed $valor): array
    {
        if (is_array($valor)) {
            $items = $valor;
        } elseif (is_string($valor)) {
            $items = explode(',', $valor);
        } else {
            return [];
        }

        return collect($items)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAlumnoRequest $request)
    {
        try {
            $data = $request->validated();

            $resultado = DB::transaction(function () use ($request, $data) {
                
                // 1. Preparar datos base del usuario
                $userData = [
                    'idRol' => $data['idRol'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'telefono' => $data['telefono'] ?? '', // Si no se envía, se guarda como string vacío
                    'sobreMi' => $data['sobreMi'] ?? null,
                ];

                // 2. Guardar el avatar si viene en la petición
                if ($request->hasFile('avatar')) {
                    $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
                }

                // Crear el registro en la tabla 'usuarios'
                $usuario = User::create($userData);

                // 3. Guardar el currículum si viene en la petición
                $curriculumPath = null;
                if ($request->hasFile('curriculum')) {
                    $curriculumPath = $request->file('curriculum')->store('curriculums', 'public');
                }

                // Crear el registro en la tabla 'alumnos' usando el idUsuario generado
                $alumno = Alumno::create([
                    'idUsuario' => $usuario->idUsuario,
                    'nombre' => $data['nombre'],
                    'apellidoPaterno' => $data['apellidoPaterno'],
                    'apellidoMaterno' => $data['apellidoMaterno'],
                    'idCarrera' => $data['idCarrera'],
                    'curriculum' => $curriculumPath ?? '',
                    'semestre' => $data['semestre'] ?? 1,
                ]);

                return [
                    'usuario' => $usuario,
                    'alumno' => $alumno
                ];
            });

            return response()->json([
                'message' => 'Alumno registrado exitosamente',
                'data' => $resultado
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error en el servidor',
                'error' => $e->getMessage()
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

    public function verCurriculum(Request $request)
    {
        // Obtenemos el alumno asociado al usuario autenticado
        $alumno = $request->user()->alumno;

        // Validamos que el alumno y el campo de currículum existan
        if (!$alumno || !$alumno->curriculum) {
            return response()->json([
                'success' => false,
                'message' => 'No hay ningún currículum registrado.'
            ], 404);
        }

        $path = $alumno->curriculum;

        // Verificamos que el archivo físico realmente exista en el disco 'public'
        if (!Storage::disk('public')->exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo físico no se encuentra en el servidor.'
            ], 404);
        }

        $absolutePath = Storage::disk('public')->path($path);

        return response()->file($absolutePath); 
    }
}
