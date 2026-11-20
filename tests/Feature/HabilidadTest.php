<?php

namespace Tests\Feature;

use App\Models\Habilidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HabilidadTest extends TestCase
{
    use RefreshDatabase;

    private function crearRol(int $idRol, string $nombre): int
    {
        return DB::table('roles')->insertGetId([
            'idRol' => $idRol,
            'rol' => $nombre,
        ], 'idRol');
    }

    private function crearUsuario(int $idRol, string $email): User
    {
        return User::create([
            'idRol' => $idRol,
            'email' => $email,
            'password' => Hash::make('password123'),
            'telefono' => '6671234567',
        ]);
    }

    private function crearIndustria(string $nombre = 'Tecnología y Software'): int
    {
        return DB::table('industrias')->insertGetId([
            'industria' => $nombre,
        ], 'idIndustria');
    }

    private function crearCarrera(string $nombre = 'Licenciatura en Informática', int $semestres = 9, ?int $idIndustria = null): int
    {
        if ($idIndustria === null) {
            $idIndustria = $this->crearIndustria();
        }

        return DB::table('carreras')->insertGetId([
            'carrera' => $nombre,
            'semestres' => $semestres,
            'idIndustria' => $idIndustria,
        ], 'idCarrera');
    }

    private function crearAlumnoPerfil(User $usuario, int $idCarrera): void
    {
        $data = [
            'idUsuario' => $usuario->idUsuario,
            'idCarrera' => $idCarrera,
            'nombre' => 'Ismael',
            'apellidoPaterno' => 'López',
            'apellidoMaterno' => 'Pérez',
            'semestre' => 9,
        ];

        if (Schema::hasColumn('alumnos', 'created_at')) {
            $data['created_at'] = now();
            $data['updated_at'] = now();
        }

        DB::table('alumnos')->insert($data);
    }

    private function crearNivel(int $idNivel = 1, string $nombre = 'Intermedio'): int
    {
        return DB::table('niveles')->insertGetId([
            'idNivel' => $idNivel,
            'nombre' => $nombre,
        ], 'idNivel');
    }

    public function test_puede_listar_las_habilidades_ordenadas_alfabeticamente()
    {
        Habilidad::create(['nombre' => 'PHP']);
        Habilidad::create(['nombre' => 'Docker']);
        Habilidad::create(['nombre' => 'Laravel']);

        $response = $this->getJson('/api/habilidades');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Habilidades Obtenidas correctamente',
            ])
            ->assertJsonCount(3, 'habilidades')
            ->assertJsonPath('habilidades.0.nombre', 'Docker')
            ->assertJsonPath('habilidades.1.nombre', 'Laravel')
            ->assertJsonPath('habilidades.2.nombre', 'PHP')
            ->assertJsonStructure([
                'success',
                'message',
                'habilidades' => [
                    '*' => [
                        'idHabilidad',
                        'nombre',
                    ],
                ],
            ]);
    }

    public function test_alumno_puede_agregar_habilidad_a_su_perfil()
    {
        $this->crearRol(1, 'Alumno');
        $idCarrera = $this->crearCarrera();
        $usuario = $this->crearUsuario(1, 'alumno@test.com');
        $this->crearAlumnoPerfil($usuario, $idCarrera);

        $habilidad = Habilidad::create(['nombre' => 'Laravel']);
        $idNivel = $this->crearNivel(1, 'Intermedio');

        $response = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/alumnos/habilidades', [
                'idHabilidad' => $habilidad->idHabilidad,
                'idNivel' => $idNivel,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Habilidad agregada exitosamente al perfil.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'usuario' => [
                    'alumno' => [
                        'carrera',
                        'habilidades',
                    ],
                ],
            ]);

        $alumno = DB::table('alumnos')->where('idUsuario', $usuario->idUsuario)->first();

        $this->assertDatabaseHas('alumno_habilidades', [
            'idAlumno' => $alumno->idAlumno,
            'idHabilidad' => $habilidad->idHabilidad,
            'idNivel' => $idNivel,
        ]);
    }

    public function test_store_retorna_400_si_usuario_no_tiene_perfil_de_alumno()
    {
        $this->crearRol(2, 'Empresa');
        $usuario = $this->crearUsuario(2, 'empresa@test.com');
        $habilidad = Habilidad::create(['nombre' => 'Docker']);
        $idNivel = $this->crearNivel(1, 'Avanzado');

        $response = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/alumnos/habilidades', [
                'idHabilidad' => $habilidad->idHabilidad,
                'idNivel' => $idNivel,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'El usuario autenticado no cuenta con un perfil de alumno',
            ]);
    }

    public function test_alumno_puede_eliminar_habilidad_de_su_perfil()
    {
        $this->crearRol(1, 'Alumno');
        $idCarrera = $this->crearCarrera();
        $usuario = $this->crearUsuario(1, 'alumno@test.com');
        $this->crearAlumnoPerfil($usuario, $idCarrera);

        $habilidad = Habilidad::create(['nombre' => 'PostgreSQL']);
        $idNivel = $this->crearNivel(1, 'Básico');

        $alumno = DB::table('alumnos')->where('idUsuario', $usuario->idUsuario)->first();

        $idPivot = DB::table('alumno_habilidades')->insertGetId([
            'idAlumno' => $alumno->idAlumno,
            'idHabilidad' => $habilidad->idHabilidad,
            'idNivel' => $idNivel,
        ], 'id_alumno_habilidad');

        $response = $this->actingAs($usuario, 'sanctum')
            ->deleteJson("/api/alumnos/habilidades/{$idPivot}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Habilidad eliminada del perfil correctamente.',
            ]);

        $this->assertDatabaseMissing('alumno_habilidades', [
            'id_alumno_habilidad' => $idPivot,
        ]);
    }

    public function test_destroy_retorna_404_si_no_existe_la_habilidad_en_el_perfil()
    {
        $this->crearRol(1, 'Alumno');
        $idCarrera = $this->crearCarrera();
        $usuario = $this->crearUsuario(1, 'alumno@test.com');
        $this->crearAlumnoPerfil($usuario, $idCarrera);

        $response = $this->actingAs($usuario, 'sanctum')
            ->deleteJson('/api/alumnos/habilidades/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No se encontró la habilidad en tu perfil.',
            ]);
    }
}