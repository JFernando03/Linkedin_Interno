<?php

namespace Tests\Feature;

use App\Models\Mensaje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChatTest extends TestCase
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

    private function limpiarMensajes(array $mensajes): void
    {
        foreach ($mensajes as $mensaje) {
            if ($mensaje instanceof Mensaje) {
                $id = $mensaje->getKey();
                if ($id !== null) {
                    Mensaje::where($mensaje->getKeyName(), $id)->delete();
                } else {
                    $mensaje->delete();
                }
            }
        }
    }

    public function test_puede_enviar_mensaje_a_otro_usuario()
    {
        $this->crearRol(1, 'Alumno');
        $this->crearRol(2, 'Empresa');

        $remitente = $this->crearUsuario(1, 'alumno@test.com');
        $destinatario = $this->crearUsuario(2, 'empresa@test.com');

        $texto = 'Hola, me interesa la vacante.';
        $creados = [];

        try {
            $response = $this->actingAs($remitente, 'sanctum')
                ->postJson('/api/mensajes', [
                    'destinatario_id' => (string) $destinatario->idUsuario,
                    'mensaje' => $texto,
                ]);

            $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'mensaje' => [
                        'remitente_id' => (string) $remitente->idUsuario,
                        'destinatario_id' => (string) $destinatario->idUsuario,
                        'mensaje' => $texto,
                        'leido' => false,
                    ],
                ]);

            $creado = Mensaje::where('remitente_id', (string) $remitente->idUsuario)
                ->where('destinatario_id', (string) $destinatario->idUsuario)
                ->where('mensaje', $texto)
                ->first();

            $this->assertNotNull($creado);
            $creados[] = $creado;
        } finally {
            $this->limpiarMensajes($creados);
        }
    }

    public function test_no_puede_enviarse_mensaje_a_si_mismo_ni_enviar_datos_invalidos()
    {
        $this->crearRol(1, 'Alumno');
        $usuario = $this->crearUsuario(1, 'alumno@test.com');

        $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/mensajes', [
                'destinatario_id' => (string) $usuario->idUsuario,
                'mensaje' => 'Nota para mí',
            ])
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'No puedes enviarte mensajes a ti mismo.',
            ]);

        $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/mensajes', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['destinatario_id', 'mensaje']);
    }

    public function test_puede_obtener_conversacion_ordenada_y_marca_recibidos_como_leidos()
    {
        $this->crearRol(1, 'Alumno');
        $this->crearRol(2, 'Empresa');

        $usuarioActual = $this->crearUsuario(1, 'actual@test.com');
        $otroUsuario = $this->crearUsuario(2, 'otro@test.com');
        $tercero = $this->crearUsuario(1, 'tercero@test.com');

        $creados = [];

        try {
            $msg1 = Mensaje::create([
                'remitente_id' => (string) $otroUsuario->idUsuario,
                'destinatario_id' => (string) $usuarioActual->idUsuario,
                'mensaje' => 'Primero',
                'leido' => false,
                'created_at' => now()->subMinutes(10),
                'updated_at' => now()->subMinutes(10),
            ]);
            $creados[] = $msg1;

            $msg2 = Mensaje::create([
                'remitente_id' => (string) $usuarioActual->idUsuario,
                'destinatario_id' => (string) $otroUsuario->idUsuario,
                'mensaje' => 'Segundo',
                'leido' => false,
                'created_at' => now()->subMinutes(5),
                'updated_at' => now()->subMinutes(5),
            ]);
            $creados[] = $msg2;

            $msg3 = Mensaje::create([
                'remitente_id' => (string) $otroUsuario->idUsuario,
                'destinatario_id' => (string) $usuarioActual->idUsuario,
                'mensaje' => 'Tercero',
                'leido' => false,
                'created_at' => now()->subMinutes(2),
                'updated_at' => now()->subMinutes(2),
            ]);
            $creados[] = $msg3;

            $msgTercero = Mensaje::create([
                'remitente_id' => (string) $tercero->idUsuario,
                'destinatario_id' => (string) $usuarioActual->idUsuario,
                'mensaje' => 'De otro chat',
                'leido' => false,
            ]);
            $creados[] = $msgTercero;

            $response = $this->actingAs($usuarioActual, 'sanctum')
                ->getJson("/api/mensajes/{$otroUsuario->idUsuario}");

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonCount(3, 'mensajes')
                ->assertJsonPath('mensajes.0.mensaje', 'Primero')
                ->assertJsonPath('mensajes.1.mensaje', 'Segundo')
                ->assertJsonPath('mensajes.2.mensaje', 'Tercero');

            $this->assertTrue((bool) $msg1->fresh()->leido);
            $this->assertTrue((bool) $msg3->fresh()->leido);
            $this->assertFalse((bool) $msg2->fresh()->leido);
        } finally {
            $this->limpiarMensajes($creados);
        }
    }

    public function test_puede_obtener_historial_de_conversaciones_con_ultimo_mensaje_y_no_leidos()
    {
        $this->crearRol(1, 'Alumno');
        $this->crearRol(2, 'Empresa');

        $usuarioActual = $this->crearUsuario(1, 'actual@test.com');
        $empresaA = $this->crearUsuario(2, 'a@empresa.com');
        $empresaB = $this->crearUsuario(2, 'b@empresa.com');

        $creados = [];

        try {
            $creados[] = Mensaje::create([
                'remitente_id' => (string) $empresaA->idUsuario,
                'destinatario_id' => (string) $usuarioActual->idUsuario,
                'mensaje' => 'Msg A1',
                'leido' => false,
                'created_at' => now()->subMinutes(20),
                'updated_at' => now()->subMinutes(20),
            ]);

            $creados[] = Mensaje::create([
                'remitente_id' => (string) $empresaA->idUsuario,
                'destinatario_id' => (string) $usuarioActual->idUsuario,
                'mensaje' => 'Último de A',
                'leido' => false,
                'created_at' => now()->subMinutes(15),
                'updated_at' => now()->subMinutes(15),
            ]);

            $creados[] = Mensaje::create([
                'remitente_id' => (string) $empresaB->idUsuario,
                'destinatario_id' => (string) $usuarioActual->idUsuario,
                'mensaje' => 'Msg B1',
                'leido' => false,
                'created_at' => now()->subMinutes(10),
                'updated_at' => now()->subMinutes(10),
            ]);

            $creados[] = Mensaje::create([
                'remitente_id' => (string) $empresaB->idUsuario,
                'destinatario_id' => (string) $usuarioActual->idUsuario,
                'mensaje' => 'Último de B',
                'leido' => true,
                'created_at' => now()->subMinutes(2),
                'updated_at' => now()->subMinutes(2),
            ]);

            $response = $this->actingAs($usuarioActual, 'sanctum')
                ->getJson('/api/conversaciones');

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonCount(2, 'conversaciones');

            $conversaciones = collect($response->json('conversaciones'));

            $chatA = $conversaciones->firstWhere('interlocutor_id', (string) $empresaA->idUsuario);
            $chatB = $conversaciones->firstWhere('interlocutor_id', (string) $empresaB->idUsuario);

            $this->assertSame('Último de A', $chatA['ultimo_mensaje']);
            $this->assertSame(2, $chatA['no_leidos']);

            $this->assertSame('Último de B', $chatB['ultimo_mensaje']);
            $this->assertSame(1, $chatB['no_leidos']);
        } finally {
            $this->limpiarMensajes($creados);
        }
    }
}