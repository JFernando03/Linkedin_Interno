<?php

namespace Tests\Feature;

use App\Models\Publicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicacionTest extends TestCase
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

    private function crearPublicacion(
        User $autor,
        string $titulo = 'Título de prueba',
        string $contenido = 'Contenido de prueba',
        ?string $multimedia = null,
        ?string $publishedAt = null
    ): Publicacion {
        return Publicacion::create([
            'idUsuario' => $autor->idUsuario,
            'titulo' => $titulo,
            'contenido' => $contenido,
            'multimedia' => $multimedia,
            'published_at' => $publishedAt ?? now(),
        ]);
    }

    public function test_puede_listar_publicaciones_e_indicar_esMia()
    {
        $this->crearRol(1, 'Alumno');
        $this->crearRol(2, 'Empresa');

        $usuarioActual = $this->crearUsuario(1, 'actual@test.com');
        $otroUsuario = $this->crearUsuario(2, 'otro@test.com');

        $publicacionPropia = $this->crearPublicacion(
            $usuarioActual,
            'Mi publicación',
            'Contenido propio',
            null,
            now()->subHour()
        );

        $publicacionAjena = $this->crearPublicacion(
            $otroUsuario,
            'Publicación ajena',
            'Contenido ajeno',
            null,
            now()->subMinutes(10)
        );

        $response = $this->actingAs($usuarioActual, 'sanctum')
            ->getJson('/api/publicaciones');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Publicaciones obtenidas correctamente',
            ]);

        $items = collect($response->json('publicaciones.data'));
        $propiaJson = $items->firstWhere('idPublicacion', $publicacionPropia->idPublicacion);
        $ajenaJson = $items->firstWhere('idPublicacion', $publicacionAjena->idPublicacion);

        $this->assertNotNull($propiaJson);
        $this->assertNotNull($ajenaJson);
        $this->assertTrue((bool) $propiaJson['esMia']);
        $this->assertFalse((bool) $ajenaJson['esMia']);
    }

    public function test_usuario_puede_crear_publicacion_con_multimedia()
    {
        Storage::fake('public');

        $this->crearRol(1, 'Alumno');
        $usuario = $this->crearUsuario(1, 'alumno@test.com');
        $archivo = UploadedFile::fake()->image('foto.png');

        $response = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/publicaciones', [
                'titulo' => 'Nueva publicación',
                'contenido' => 'Compartiendo avance del proyecto',
                'multimedia' => $archivo,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Publicacion creada exitosamente',
                'publicacion' => [
                    'titulo' => 'Nueva publicación',
                    'contenido' => 'Compartiendo avance del proyecto',
                    'idUsuario' => $usuario->idUsuario,
                ],
            ]);

        $publicacion = Publicacion::where('titulo', 'Nueva publicación')->first();
        $this->assertNotNull($publicacion);
        $this->assertNotNull($publicacion->multimedia);
        Storage::disk('public')->assertExists($publicacion->multimedia);
    }

    public function test_store_valida_campos_requeridos_y_tipo_de_archivo()
    {
        $this->crearRol(1, 'Alumno');
        $usuario = $this->crearUsuario(1, 'alumno@test.com');
        $archivoInvalido = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $response = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/publicaciones', [
                'multimedia' => $archivoInvalido,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'titulo',
                'contenido',
                'multimedia',
            ]);
    }

    public function test_dueno_puede_actualizar_publicacion_y_reemplazar_multimedia()
    {
        Storage::fake('public');

        $this->crearRol(1, 'Alumno');
        $usuario = $this->crearUsuario(1, 'alumno@test.com');

        $rutaAnterior = 'publicaciones/anterior.jpg';
        Storage::disk('public')->put($rutaAnterior, 'archivo anterior');

        $publicacion = $this->crearPublicacion($usuario, 'Título inicial', 'Contenido inicial', $rutaAnterior);
        $nuevoArchivo = UploadedFile::fake()->image('nuevo.jpg');

        $response = $this->actingAs($usuario, 'sanctum')
            ->putJson("/api/publicaciones/{$publicacion->idPublicacion}", [
                'titulo' => 'Título actualizado',
                'multimedia' => $nuevoArchivo,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Publicación actualizada exitosamente',
                'publicacion' => [
                    'idPublicacion' => $publicacion->idPublicacion,
                    'titulo' => 'Título actualizado',
                ],
            ]);

        $publicacion->refresh();
        Storage::disk('public')->assertMissing($rutaAnterior);
        Storage::disk('public')->assertExists($publicacion->multimedia);
    }

    public function test_update_retorna_403_si_no_es_dueno_o_404_si_no_existe()
    {
        $this->crearRol(1, 'Alumno');
        $dueno = $this->crearUsuario(1, 'dueno@test.com');
        $otroUsuario = $this->crearUsuario(1, 'otro@test.com');

        $publicacion = $this->crearPublicacion($dueno);

        $this->actingAs($otroUsuario, 'sanctum')
            ->putJson("/api/publicaciones/{$publicacion->idPublicacion}", [
                'titulo' => 'Intento no autorizado',
            ])
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No tienes autorización para editar esta publicación',
            ]);

        $this->actingAs($dueno, 'sanctum')
            ->putJson('/api/publicaciones/999999', [
                'titulo' => 'No existe',
            ])
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Publicación no encontrada',
            ]);
    }

    public function test_dueno_puede_eliminar_su_publicacion_y_borrar_multimedia()
    {
        Storage::fake('public');

        $this->crearRol(1, 'Alumno');
        $usuario = $this->crearUsuario(1, 'alumno@test.com');

        $rutaArchivo = 'publicaciones/evidencia.jpg';
        Storage::disk('public')->put($rutaArchivo, 'contenido multimedia');

        $publicacion = $this->crearPublicacion($usuario, 'A borrar', 'Contenido', $rutaArchivo);

        $response = $this->actingAs($usuario, 'sanctum')
            ->deleteJson("/api/publicaciones/{$publicacion->idPublicacion}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Eliminación de publicacion exitosa',
            ]);

        $this->assertDatabaseMissing('publicaciones', [
            'idPublicacion' => $publicacion->idPublicacion,
        ]);
        Storage::disk('public')->assertMissing($rutaArchivo);
    }

    public function test_destroy_retorna_404_si_no_existe_o_no_pertenece_al_usuario()
    {
        $this->crearRol(1, 'Alumno');
        $dueno = $this->crearUsuario(1, 'dueno@test.com');
        $otroUsuario = $this->crearUsuario(1, 'otro@test.com');

        $publicacion = $this->crearPublicacion($dueno);

        // Otro usuario intenta borrarla: en tu destroy actual filtra por idUsuario y devuelve 404
        $this->actingAs($otroUsuario, 'sanctum')
            ->deleteJson("/api/publicaciones/{$publicacion->idPublicacion}")
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Publicacion no encontrado',
            ]);

        // ID inexistente
        $this->actingAs($dueno, 'sanctum')
            ->deleteJson('/api/publicaciones/999999')
            ->assertStatus(404);
    }
}