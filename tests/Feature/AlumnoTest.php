<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AlumnoTest extends TestCase
{
    use RefreshDatabase;

    private function crearRol(int $idRol, string $nombre): int
    {
        return DB::table('roles')->insertGetId([
            'idRol' => $idRol,
            'rol' => $nombre,
        ], 'idRol');
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

    private function crearUsuario(int $idRol, string $email): User
    {
        return User::create([
            'idRol' => $idRol,
            'email' => $email,
            'password' => Hash::make('password123'),
            'telefono' => '6671234567',
        ]);
    }

    private function crearAlumnoPerfil(User $usuario, int $idCarrera, ?string $curriculum = null): void
    {
        $data = [
            'idUsuario' => $usuario->idUsuario,
            'idCarrera' => $idCarrera,
            'nombre' => 'Ismael',
            'apellidoPaterno' => 'López',
            'apellidoMaterno' => 'Pérez',
            'curriculum' => $curriculum,
        ];

        if (Schema::hasColumn('alumnos', 'semestre')) {
            $data['semestre'] = 9;
        }

        DB::table('alumnos')->insert($data);
    }

    public function test_puede_registrar_un_alumno()
    {
        Storage::fake('public');

        $this->crearRol(1, 'Alumno');
        $idCarrera = $this->crearCarrera();

        $avatar = UploadedFile::fake()->image('avatar.jpg');
        $curriculum = UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf');

        $response = $this->postJson('/api/alumnos', [
            'idRol' => 1,
            'email' => 'nuevo.alumno@test.com',
            'password' => 'secret123',
            'telefono' => '6671234567',
            'sobreMi' => 'Estudiante de informática',
            'nombre' => 'Ismael',
            'apellidoPaterno' => 'López',
            'apellidoMaterno' => 'Pérez',
            'idCarrera' => $idCarrera,
            'semestre' => 9,
            'avatar' => $avatar,
            'curriculum' => $curriculum,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Alumno registrado exitosamente',
            ]);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'nuevo.alumno@test.com',
            'idRol' => 1,
        ]);

        $usuario = User::where('email', 'nuevo.alumno@test.com')->first();

        $this->assertDatabaseHas('alumnos', [
            'idUsuario' => $usuario->idUsuario,
            'nombre' => 'Ismael',
            'idCarrera' => $idCarrera,
        ]);

        $this->assertNotNull($usuario->avatar);
        Storage::disk('public')->assertExists($usuario->avatar);

        $alumno = DB::table('alumnos')->where('idUsuario', $usuario->idUsuario)->first();
        $this->assertNotEmpty($alumno->curriculum);
        Storage::disk('public')->assertExists($alumno->curriculum);
    }

    public function test_store_valida_campos_requeridos_y_email_unico()
    {
        $this->crearRol(1, 'Alumno');
        $idCarrera = $this->crearCarrera();
        $this->crearUsuario(1, 'repetido@test.com');

        $response = $this->postJson('/api/alumnos', [
            'idRol' => 1,
            'email' => 'repetido@test.com',
            'password' => '123',
            'idCarrera' => $idCarrera,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
                'password',
                'nombre',
                'apellidoPaterno',
                'apellidoMaterno',
                'semestre',
            ]);
    }

    public function test_alumno_puede_ver_su_curriculum_si_existe_en_disco()
    {
        Storage::fake('public');

        $this->crearRol(1, 'Alumno');
        $idCarrera = $this->crearCarrera();
        $usuario = $this->crearUsuario(1, 'alumno@test.com');

        $rutaCv = 'curriculums/mi-cv.pdf';
        Storage::disk('public')->put($rutaCv, '%PDF-1.4 contenido de prueba');

        $this->crearAlumnoPerfil($usuario, $idCarrera, $rutaCv);

        $response = $this->actingAs($usuario, 'sanctum')
            ->get('/api/alumnos/curriculum');

        $response->assertStatus(200);
    }

    public function test_ver_curriculum_retorna_404_si_no_tiene_curriculum_o_no_existe_el_archivo()
    {
        Storage::fake('public');

        $this->crearRol(1, 'Alumno');
        $idCarrera = $this->crearCarrera();
        $usuarioSinCv = $this->crearUsuario(1, 'sincv@test.com');
        $this->crearAlumnoPerfil($usuarioSinCv, $idCarrera, null);

        $this->actingAs($usuarioSinCv, 'sanctum')
            ->getJson('/api/alumnos/curriculum')
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No hay ningún currículum registrado.',
            ]);

        $usuarioArchivoFaltante = $this->crearUsuario(1, 'faltante@test.com');
        $this->crearAlumnoPerfil($usuarioArchivoFaltante, $idCarrera, 'curriculums/no-existe.pdf');

        $this->actingAs($usuarioArchivoFaltante, 'sanctum')
            ->getJson('/api/alumnos/curriculum')
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'El archivo físico no se encuentra en el servidor.',
            ]);
    }
}