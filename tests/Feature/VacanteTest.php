<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VacanteTest extends TestCase
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

    private function crearModalidad(string $nombre = 'Híbrido'): int
    {
        return DB::table('modalidades')->insertGetId([
            'nombre' => $nombre,
        ], 'idModalidad');
    }

    private function crearVacante(
        User $autor,
        int $idModalidad,
        string $puesto = 'Desarrollador Backend',
        ?string $multimedia = null,
        ?string $publishedAt = null
    ): Vacante {
        return Vacante::create([
            'idUsuario' => $autor->idUsuario,
            'idModalidad' => $idModalidad,
            'puesto' => $puesto,
            'contenido' => 'Descripción detallada de la vacante',
            'sueldo_min' => 15000,
            'sueldo_max' => 25000,
            'multimedia' => $multimedia,
            'published_at' => $publishedAt ?? now(),
        ]);
    }

    public function test_puede_listar_vacantes()
    {
        $this->crearRol(1, 'Alumno');
        $this->crearRol(2, 'Empresa');

        $idModalidad = $this->crearModalidad('Remoto');
        $empresaMia = $this->crearUsuario(2, 'mia@empresa.com');
        $otraEmpresa = $this->crearUsuario(2, 'otra@empresa.com');

        $vacantePropia = $this->crearVacante(
            $empresaMia,
            $idModalidad,
            'Vacante Propia',
            null,
            now()->subHour()
        );

        $vacanteAjena = $this->crearVacante(
            $otraEmpresa,
            $idModalidad,
            'Vacante Ajena',
            null,
            now()->subMinutes(10)
        );

        $response = $this->actingAs($empresaMia, 'sanctum')
            ->getJson('/api/vacantes');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Vacantes obtenidas correctamente',
            ]);

        $items = collect($response->json('vacantes.data'));
        $propiaJson = $items->firstWhere('idVacante', $vacantePropia->idVacante);
        $ajenaJson = $items->firstWhere('idVacante', $vacanteAjena->idVacante);

        $this->assertNotNull($propiaJson);
        $this->assertNotNull($ajenaJson);
        $this->assertTrue((bool) $propiaJson['esMia']);
        $this->assertFalse((bool) $ajenaJson['esMia']);
    }

    public function test_empresa_puede_crear_vacante_con_multimedia()
    {
        Storage::fake('public');

        $this->crearRol(2, 'Empresa');
        $idModalidad = $this->crearModalidad('Presencial');
        $empresa = $this->crearUsuario(2, 'empresa@test.com');
        $archivo = UploadedFile::fake()->image('banner.jpg');

        $response = $this->actingAs($empresa, 'sanctum')
            ->postJson('/api/vacantes', [
                'puesto' => 'Ingeniero DevOps',
                'contenido' => 'Automatización con Docker y CI/CD',
                'idModalidad' => $idModalidad,
                'sueldo_min' => 20000,
                'sueldo_max' => 30000,
                'multimedia' => $archivo,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Vacante creada exitosamente',
                'vacante' => [
                    'puesto' => 'Ingeniero DevOps',
                    'idUsuario' => $empresa->idUsuario,
                    'idModalidad' => $idModalidad,
                ],
            ]);

        $vacante = Vacante::where('puesto', 'Ingeniero DevOps')->first();
        $this->assertNotNull($vacante);
        $this->assertNotNull($vacante->multimedia);
        Storage::disk('public')->assertExists($vacante->multimedia);
    }

    public function test_store_rechaza_alumno_y_valida_sueldos()
    {
        $this->crearRol(1, 'Alumno');
        $this->crearRol(2, 'Empresa');

        $idModalidad = $this->crearModalidad();
        $alumno = $this->crearUsuario(1, 'alumno@test.com');
        $empresa = $this->crearUsuario(2, 'empresa@test.com');

        // Alumno no tiene rol 2 o 3
        $this->actingAs($alumno, 'sanctum')
            ->postJson('/api/vacantes', [
                'puesto' => 'Practicante',
                'contenido' => 'Contenido',
                'idModalidad' => $idModalidad,
            ])
            ->assertStatus(403);

        // sueldo_max menor que sueldo_min debe fallar validación
        $this->actingAs($empresa, 'sanctum')
            ->postJson('/api/vacantes', [
                'puesto' => 'Dev Fullstack',
                'contenido' => 'Contenido',
                'idModalidad' => $idModalidad,
                'sueldo_min' => 25000,
                'sueldo_max' => 15000,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['sueldo_max']);
    }

    public function test_dueno_puede_actualizar_vacante_y_reemplazar_multimedia()
    {
        Storage::fake('public');

        $this->crearRol(2, 'Empresa');
        $idModalidad = $this->crearModalidad();
        $empresa = $this->crearUsuario(2, 'empresa@test.com');

        $rutaAnterior = 'vacantes/anterior.jpg';
        Storage::disk('public')->put($rutaAnterior, 'imagen vieja');

        $vacante = $this->crearVacante($empresa, $idModalidad, 'Puesto Inicial', $rutaAnterior);
        $nuevaImagen = UploadedFile::fake()->image('nueva.png');

        $response = $this->actingAs($empresa, 'sanctum')
            ->putJson("/api/vacantes/{$vacante->idVacante}", [
                'puesto' => 'Puesto Actualizado',
                'multimedia' => $nuevaImagen,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Vacante actualizada exitosamente',
                'vacante' => [
                    'idVacante' => $vacante->idVacante,
                    'puesto' => 'Puesto Actualizado',
                    'esMia' => true,
                ],
            ]);

        $vacante->refresh();
        Storage::disk('public')->assertMissing($rutaAnterior);
        Storage::disk('public')->assertExists($vacante->multimedia);
    }

    public function test_update_retorna_403_si_no_es_dueno_o_404_si_no_existe()
    {
        $this->crearRol(2, 'Empresa');
        $idModalidad = $this->crearModalidad();

        $dueno = $this->crearUsuario(2, 'dueno@empresa.com');
        $otraEmpresa = $this->crearUsuario(2, 'intrusion@empresa.com');
        $vacante = $this->crearVacante($dueno, $idModalidad);

        $this->actingAs($otraEmpresa, 'sanctum')
            ->putJson("/api/vacantes/{$vacante->idVacante}", [
                'puesto' => 'Intento no autorizado',
            ])
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No tienes autorización para editar esta vacante',
            ]);

        $this->actingAs($dueno, 'sanctum')
            ->putJson('/api/vacantes/999999', [
                'puesto' => 'No existe',
            ])
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Vacante no encontrada',
            ]);
    }

    public function test_dueno_o_admin_puede_eliminar_vacante_y_borrar_multimedia()
    {
        Storage::fake('public');

        $this->crearRol(2, 'Empresa');
        $this->crearRol(3, 'Admin');
        $idModalidad = $this->crearModalidad();

        $empresa = $this->crearUsuario(2, 'empresa@test.com');
        $admin = $this->crearUsuario(3, 'admin@test.com');

        // 1. Dueño elimina su vacante con archivo
        $rutaArchivo1 = 'vacantes/archivo1.jpg';
        Storage::disk('public')->put($rutaArchivo1, 'contenido 1');
        $vacante1 = $this->crearVacante($empresa, $idModalidad, 'Vacante 1', $rutaArchivo1);

        $this->actingAs($empresa, 'sanctum')
            ->deleteJson("/api/vacantes/{$vacante1->idVacante}")
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Vacante eliminada exitosamente',
            ]);

        $this->assertDatabaseMissing('vacantes', ['idVacante' => $vacante1->idVacante]);
        Storage::disk('public')->assertMissing($rutaArchivo1);

        // 2. Admin (rol 3) elimina vacante de otra empresa
        $rutaArchivo2 = 'vacantes/archivo2.jpg';
        Storage::disk('public')->put($rutaArchivo2, 'contenido 2');
        $vacante2 = $this->crearVacante($empresa, $idModalidad, 'Vacante 2', $rutaArchivo2);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/vacantes/{$vacante2->idVacante}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('vacantes', ['idVacante' => $vacante2->idVacante]);
        Storage::disk('public')->assertMissing($rutaArchivo2);
    }

    public function test_destroy_retorna_403_si_no_es_dueno_ni_admin_o_404_si_no_existe()
    {
        $this->crearRol(2, 'Empresa');
        $idModalidad = $this->crearModalidad();

        $dueno = $this->crearUsuario(2, 'dueno@empresa.com');
        $otraEmpresa = $this->crearUsuario(2, 'otra@empresa.com');
        $vacante = $this->crearVacante($dueno, $idModalidad);

        $this->actingAs($otraEmpresa, 'sanctum')
            ->deleteJson("/api/vacantes/{$vacante->idVacante}")
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No tienes autorización para eliminar esta vacante',
            ]);

        $this->actingAs($dueno, 'sanctum')
            ->deleteJson('/api/vacantes/999999')
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Vacante no encontrada',
            ]);
    }
}