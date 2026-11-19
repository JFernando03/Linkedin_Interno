<?php

namespace Tests\Feature;

use App\Models\Interes;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InteresTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Notificacion::where('tipo', 'interes_empresa')->delete();

        parent::tearDown();
    }

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

    public function test_empresa_puede_registrar_interes_y_generar_notificacion()
    {
        $this->crearRol(1, 'Alumno');
        $this->crearRol(2, 'Empresa');

        $empresa = $this->crearUsuario(2, 'empresa@test.com');
        $alumno = $this->crearUsuario(1, 'alumno@test.com');

        $response = $this->actingAs($empresa, 'sanctum')
            ->postJson("/api/alumnos/{$alumno->idUsuario}/interes");

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Interés registrado y notificación enviada con éxito.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'interes' => [
                    'idInteres',
                    'idEmpresa',
                    'idAlumno',
                ],
            ]);

        $this->assertDatabaseHas('intereses', [
            'idEmpresa' => $empresa->idUsuario,
            'idAlumno' => $alumno->idUsuario,
        ]);

        $noti = Notificacion::where('idUsuario', (int) $alumno->idUsuario)
            ->where('tipo', 'interes_empresa')
            ->first();

        $this->assertNotNull($noti);
        $this->assertEquals('interes_empresa', $noti->tipo);
    }

    public function test_alumno_no_puede_registrar_interes_en_otro_alumno()
    {
        $this->crearRol(1, 'Alumno');

        $alumnoActor = $this->crearUsuario(1, 'actor@test.com');
        $alumnoObjetivo = $this->crearUsuario(1, 'objetivo@test.com');

        $response = $this->actingAs($alumnoActor, 'sanctum')
            ->postJson("/api/alumnos/{$alumnoObjetivo->idUsuario}/interes");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Solo las empresas pueden mostrar interés en los alumnos.',
            ]);
    }

    public function test_retorna_404_si_el_alumno_no_existe_o_no_tiene_rol_alumno()
    {
        $this->crearRol(2, 'Empresa');
        $empresa = $this->crearUsuario(2, 'empresa@test.com');

        $response = $this->actingAs($empresa, 'sanctum')
            ->postJson('/api/alumnos/999999/interes');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Alumno no encontrado.',
            ]);
    }

    public function test_puede_obtener_mis_notificaciones()
    {
        $this->crearRol(1, 'Alumno');
        $this->crearRol(2, 'Empresa');

        $empresa = $this->crearUsuario(2, 'empresa@test.com');
        $alumno = $this->crearUsuario(1, 'alumno@test.com');

        $this->actingAs($empresa, 'sanctum')
            ->postJson("/api/alumnos/{$alumno->idUsuario}/interes")
            ->assertStatus(201);

        $response = $this->actingAs($alumno, 'sanctum')
            ->getJson('/api/notificaciones');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'notificaciones');
    }
}