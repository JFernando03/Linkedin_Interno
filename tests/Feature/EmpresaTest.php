<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmpresaTest extends TestCase
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

    private function crearUsuario(int $idRol, string $email): User
    {
        return User::create([
            'idRol' => $idRol,
            'email' => $email,
            'password' => Hash::make('password123'),
            'telefono' => '6671234567',
        ]);
    }

    public function test_puede_registrar_una_empresa()
    {
        Storage::fake('public');

        $this->crearRol(2, 'Empresa');
        $idIndustria = $this->crearIndustria('Tecnología y Software');
        $avatar = UploadedFile::fake()->image('logo.png');

        $response = $this->postJson('/api/empresas', [
            'idRol' => 2,
            'email' => 'contacto@empresa.com',
            'password' => 'secret123',
            'telefono' => '6675551234',
            'sobreMi' => 'Desarrollo de software a la medida',
            'avatar' => $avatar,
            'nombreEmpresa' => 'DevLab MX',
            'idIndustria' => $idIndustria,
            'paginaWeb' => 'https://devlab.example.com',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Empresa registrada correctamente',
                'empresa' => [
                    'nombreEmpresa' => 'DevLab MX',
                    'idIndustria' => $idIndustria,
                    'paginaWeb' => 'https://devlab.example.com',
                    'usuario' => [
                        'email' => 'contacto@empresa.com',
                        'idRol' => 2,
                    ],
                ],
            ]);

        $usuario = User::where('email', 'contacto@empresa.com')->first();
        $this->assertNotNull($usuario);
        $this->assertNotNull($usuario->avatar);
        Storage::disk('public')->assertExists($usuario->avatar);

        $this->assertDatabaseHas('empresas', [
            'idUsuario' => $usuario->idUsuario,
            'idIndustria' => $idIndustria,
            'nombreEmpresa' => 'DevLab MX',
            'paginaWeb' => 'https://devlab.example.com',
        ]);
    }

    public function test_puede_registrar_una_empresa_sin_campos_opcionales()
    {
        $this->crearRol(2, 'Empresa');
        $idIndustria = $this->crearIndustria();

        $response = $this->postJson('/api/empresas', [
            'idRol' => 2,
            'email' => 'minima@empresa.com',
            'password' => 'secret123',
            'nombreEmpresa' => 'Empresa Mínima',
            'idIndustria' => $idIndustria,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Empresa registrada correctamente',
            ]);

        $usuario = User::where('email', 'minima@empresa.com')->first();
        $this->assertNotNull($usuario);
        $this->assertSame('', $usuario->telefono);
        $this->assertNull($usuario->sobreMi);

        $this->assertDatabaseHas('empresas', [
            'idUsuario' => $usuario->idUsuario,
            'idIndustria' => $idIndustria,
            'nombreEmpresa' => 'Empresa Mínima',
            'paginaWeb' => null,
        ]);
    }

    public function test_store_valida_datos_invalidos_y_email_duplicado()
    {
        $this->crearRol(2, 'Empresa');
        $this->crearUsuario(2, 'existente@empresa.com');

        $response = $this->postJson('/api/empresas', [
            'idRol' => 2,
            'email' => 'existente@empresa.com',
            'password' => '123',
            'idIndustria' => 999999,
            'paginaWeb' => 'no-es-una-url',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
                'password',
                'nombreEmpresa',
                'idIndustria',
                'paginaWeb',
            ]);
    }
}