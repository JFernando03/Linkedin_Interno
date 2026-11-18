<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_un_usuario_puede_iniciar_sesion_exitosamente()
    {
        $idRol = DB::table('roles')->insertGetId(['rol' => 'Alumno'], 'idRol');

        $user = User::create([
            'email' => 'test@correo.com',
            'password' => Hash::make('password123'),
            'idRol' => $idRol,
            'telefono' => '6671234567',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@correo.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'access_token',
                     'token_type',
                     'usuario' => [
                         'email',
                     ]
                 ])
                 ->assertJson([
                     'message' => 'Inicio de sesión exitoso',
                     'token_type' => 'Bearer',
                 ]);

        $this->assertNotNull($response->json('access_token'));
    }

    /** @test */
    public function test_no_puede_iniciar_sesion_con_credenciales_incorrectas()
    {
        $idRol = DB::table('roles')->insertGetId(['rol' => 'Alumno'], 'idRol');

        User::create([
            'email' => 'test@correo.com',
            'password' => Hash::make('password123'),
            'idRol' => $idRol,
            'telefono' => '6671234567',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@correo.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function test_un_usuario_autenticado_puede_cerrar_sesion_exitosamente()
    {
        $idRol = DB::table('roles')->insertGetId(['rol' => 'Estudiante'], 'idRol');

        $user = User::create([
            'email' => 'test@correo.com',
            'password' => Hash::make('password123'),
            'idRol' => $idRol,
            'telefono' => '6671234567',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Sesión cerrada exitosamente.',
                 ]);
        
        $this->assertCount(0, $user->tokens);
    }
}