<?php

namespace Tests\Feature;

use App\Models\Publicacion;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FeedTest extends TestCase
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

    private function crearModalidad(string $nombre = 'Remoto'): int
    {
        return DB::table('modalidades')->insertGetId([
            'nombre' => $nombre,
        ], 'idModalidad');
    }

    private function crearPublicacion(
        User $autor,
        string $titulo,
        string $publishedAt
    ): Publicacion {
        return Publicacion::create([
            'idUsuario' => $autor->idUsuario,
            'titulo' => $titulo,
            'contenido' => 'Contenido de publicación',
            'published_at' => $publishedAt,
        ]);
    }

    private function crearVacante(
        User $autor,
        int $idModalidad,
        string $puesto,
        string $publishedAt
    ): Vacante {
        return Vacante::create([
            'idUsuario' => $autor->idUsuario,
            'idModalidad' => $idModalidad,
            'puesto' => $puesto,
            'contenido' => 'Descripción de vacante',
            'sueldo_min' => 15000,
            'sueldo_max' => 25000,
            'published_at' => $publishedAt,
        ]);
    }

    public function test_puede_obtener_feed_mezclado_ordenado()
    {
        $this->crearRol(1, 'Alumno');
        $this->crearRol(2, 'Empresa');

        $usuarioActual = $this->crearUsuario(1, 'actual@test.com');
        $empresaAjena = $this->crearUsuario(2, 'empresa@test.com');
        $idModalidad = $this->crearModalidad('Híbrido');

        // Más antigua: publicación propia
        $pubAntigua = $this->crearPublicacion(
            $usuarioActual,
            'Publicación antigua',
            now()->subHours(3)->toDateTimeString()
        );

        // Intermedia: vacante de otra empresa
        $vacIntermedia = $this->crearVacante(
            $empresaAjena,
            $idModalidad,
            'Vacante intermedia',
            now()->subHours(2)->toDateTimeString()
        );

        // Más reciente: publicación de otro usuario
        $pubReciente = $this->crearPublicacion(
            $empresaAjena,
            'Publicación reciente',
            now()->subHour()->toDateTimeString()
        );

        $response = $this->actingAs($usuarioActual, 'sanctum')
            ->getJson('/api/feed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Feed obtenido correctamente',
            ])
            ->assertJsonCount(3, 'feed.data');

        $data = $response->json('feed.data');

        // 1. Más reciente primero (publicación ajena)
        $this->assertSame('publicacion', $data[0]['tipo']);
        $this->assertSame($pubReciente->idPublicacion, $data[0]['idPublicacion']);
        $this->assertFalse((bool) $data[0]['esMia']);

        // 2. Intermedia (vacante ajena)
        $this->assertSame('vacante', $data[1]['tipo']);
        $this->assertSame($vacIntermedia->idVacante, $data[1]['idVacante']);
        $this->assertFalse((bool) $data[1]['esMia']);

        // 3. Más antigua al final (publicación propia)
        $this->assertSame('publicacion', $data[2]['tipo']);
        $this->assertSame($pubAntigua->idPublicacion, $data[2]['idPublicacion']);
        $this->assertTrue((bool) $data[2]['esMia']);
    }

    public function test_feed_respeta_paginacion()
    {
        $this->crearRol(1, 'Alumno');
        $usuario = $this->crearUsuario(1, 'usuario@test.com');

        for ($i = 1; $i <= 12; $i++) {
            $this->crearPublicacion(
                $usuario,
                "Publicación {$i}",
                now()->subMinutes(20 - $i)->toDateTimeString()
            );
        }

        $page1 = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/feed?page=1');

        $page1->assertStatus(200)
            ->assertJsonCount(10, 'feed.data')
            ->assertJsonPath('feed.total', 12)
            ->assertJsonPath('feed.per_page', 10)
            ->assertJsonPath('feed.current_page', 1);

        $page2 = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/feed?page=2');

        $page2->assertStatus(200)
            ->assertJsonCount(2, 'feed.data')
            ->assertJsonPath('feed.current_page', 2);
    }
}