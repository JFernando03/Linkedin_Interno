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

class PerfilTest extends TestCase
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

    private function crearCarrera(string $carrera, int $semestres, ?int $idIndustria = null): int
    {
        $idIndustria ??= $this->crearIndustria();

        return DB::table('carreras')->insertGetId([
            'carrera' => $carrera,
            'semestres' => $semestres,
            'idIndustria' => $idIndustria,
        ], 'idCarrera');
    }

    private function crearIndustria(): int
    {
        return DB::table('industrias')->insertGetId([
            'industria' => 'Tecnología y Software',
        ], 'idIndustria');
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

    private function crearEmpresaPerfil(User $usuario, int $idIndustria): void
    {
        $data = [
            'idUsuario' => $usuario->idUsuario,
            'idIndustria' => $idIndustria,
            'nombreEmpresa' => 'DevLab MX',
            'paginaWeb' => 'https://devlab.example.com',
        ];

        if (Schema::hasColumn('empresas', 'created_at')) {
            $data['created_at'] = now();
            $data['updated_at'] = now();
        }

        DB::table('empresas')->insert($data);
    }

    public function test_alumno_autenticado_puede_ver_su_perfil()
    {
        $this->crearRol(1, 'Alumno');
        $idCarrera = $this->crearCarrera('Licenciatura en Informática', 9);
        $usuario = $this->crearUsuario(1, 'alumno@test.com');
        $this->crearAlumnoPerfil($usuario, $idCarrera);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/perfil');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Perfil obtenido correctamente',
                'usuario' => [
                    'idUsuario' => $usuario->idUsuario,
                    'email' => 'alumno@test.com',
                    'alumno' => [
                        'idCarrera' => $idCarrera,
                        'carrera' => [
                            'idCarrera' => $idCarrera,
                        ],
                    ],
                ],
            ])
            ->assertJsonStructure([
                'usuario' => [
                    'alumno' => [
                        'carrera',
                        'habilidades',
                    ],
                ],
            ]);
    }

    public function test_empresa_autenticada_puede_ver_su_perfil()
    {
        $this->crearRol(2, 'Empresa');
        $idIndustria = $this->crearIndustria('Tecnología y Software');
        $usuario = $this->crearUsuario(2, 'empresa@test.com');
        $this->crearEmpresaPerfil($usuario, $idIndustria);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/perfil');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Perfil obtenido correctamente',
                'usuario' => [
                    'idUsuario' => $usuario->idUsuario,
                    'email' => 'empresa@test.com',
                    'empresa' => [
                        'idIndustria' => $idIndustria,
                        'industria' => [
                            'idIndustria' => $idIndustria,
                        ],
                    ],
                ],
            ])
            ->assertJsonStructure([
                'usuario' => [
                    'empresa' => [
                        'industria',
                    ],
                ],
            ]);
    }

    public function test_puede_ver_perfil_publico_de_alumno_por_id()
    {
        $this->crearRol(1, 'Alumno');
        $this->crearRol(2, 'Empresa');

        $idCarrera = $this->crearCarrera('Licenciatura en Informática', 9);
        $alumnoUser = $this->crearUsuario(1, 'alumno@test.com');
        $this->crearAlumnoPerfil($alumnoUser, $idCarrera);

        $viewer = $this->crearUsuario(2, 'viewer@test.com');

        $response = $this->actingAs($viewer, 'sanctum')
            ->getJson("/api/usuarios/{$alumnoUser->idUsuario}/perfil");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Perfil obtenido correctamente',
                'usuario' => [
                    'idUsuario' => $alumnoUser->idUsuario,
                    'email' => 'alumno@test.com',
                    'alumno' => [
                        'idCarrera' => $idCarrera,
                        'carrera' => [
                            'idCarrera' => $idCarrera,
                        ],
                    ],
                ],
            ])
            ->assertJsonStructure([
                'usuario' => [
                    'alumno' => [
                        'carrera',
                        'habilidades',
                    ],
                ],
            ]);

        $notFoundResponse = $this->actingAs($viewer, 'sanctum')
            ->getJson('/api/usuarios/999999/perfil');

        $notFoundResponse->assertStatus(404);
    }

    public function test_puede_ver_perfil_publico_de_empresa_por_id()
    {
        $this->crearRol(1, 'Alumno');
        $this->crearRol(2, 'Empresa');

        $idIndustria = $this->crearIndustria('Tecnología y Software');
        $empresaUser = $this->crearUsuario(2, 'empresa@test.com');
        $this->crearEmpresaPerfil($empresaUser, $idIndustria);

        $viewer = $this->crearUsuario(1, 'viewer@test.com');

        $response = $this->actingAs($viewer, 'sanctum')
            ->getJson("/api/usuarios/{$empresaUser->idUsuario}/perfil");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Perfil obtenido correctamente',
                'usuario' => [
                    'idUsuario' => $empresaUser->idUsuario,
                    'email' => 'empresa@test.com',
                    'empresa' => [
                        'idIndustria' => $idIndustria,
                        'industria' => [
                            'idIndustria' => $idIndustria,
                        ],
                    ],
                ],
            ])
            ->assertJsonStructure([
                'usuario' => [
                    'empresa' => [
                        'industria',
                    ],
                ],
            ]);
    }

    public function test_alumno_puede_actualizar_perfil_y_archivos()
    {
        Storage::fake('public');

        $this->crearRol(1, 'Alumno');
        $idCarreraOriginal = $this->crearCarrera('Licenciatura en Informática', 9);
        $idCarreraNueva = $this->crearCarrera('Ingeniería de Software', 9);

        $usuario = $this->crearUsuario(1, 'alumno@test.com');
        $this->crearAlumnoPerfil($usuario, $idCarreraOriginal);

        $avatar = UploadedFile::fake()->image('avatar.jpg', 200, 200);
        $curriculum = UploadedFile::fake()->create('cv.pdf', 500, 'application/pdf');

        $response = $this->actingAs($usuario, 'sanctum')
            ->putJson('/api/perfil', [
                'sobreMi' => 'Desarrollador backend y móvil',
                'telefono' => '6679876543',
                'nombre' => 'Ismael Actualizado',
                'semestre' => 9,
                'idCarrera' => $idCarreraNueva,
                'avatar' => $avatar,
                'curriculum' => $curriculum,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
            ]);

        $usuario->refresh();
        $this->assertSame('Desarrollador backend y móvil', $usuario->sobreMi);
        $this->assertSame('6679876543', $usuario->telefono);
        $this->assertNotNull($usuario->avatar);
        Storage::disk('public')->assertExists($usuario->avatar);

        $alumno = DB::table('alumnos')->where('idUsuario', $usuario->idUsuario)->first();
        $this->assertSame('Ismael Actualizado', $alumno->nombre);
        $this->assertSame($idCarreraNueva, (int) $alumno->idCarrera);
        $this->assertNotNull($alumno->curriculum);
        Storage::disk('public')->assertExists($alumno->curriculum);
    }

    public function test_empresa_puede_actualizar_su_perfil()
    {
        $this->crearRol(2, 'Empresa');
        $idIndustriaOriginal = $this->crearIndustria('Tecnología y Software');
        $idIndustriaNueva = $this->crearIndustria('Consultoría TI');

        $usuario = $this->crearUsuario(2, 'empresa@test.com');
        $this->crearEmpresaPerfil($usuario, $idIndustriaOriginal);

        $response = $this->actingAs($usuario, 'sanctum')
            ->putJson('/api/perfil', [
                'sobreMi' => 'Empresa enfocada en desarrollo de software',
                'telefono' => '6675551234',
                'nombreEmpresa' => 'DevLab Actualizada',
                'idIndustria' => $idIndustriaNueva,
                'paginaWeb' => 'https://devlab-actualizada.example.com',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
            ]);

        $usuario->refresh();
        $this->assertSame('Empresa enfocada en desarrollo de software', $usuario->sobreMi);
        $this->assertSame('6675551234', $usuario->telefono);

        $empresa = DB::table('empresas')->where('idUsuario', $usuario->idUsuario)->first();
        $this->assertSame('DevLab Actualizada', $empresa->nombreEmpresa);
        $this->assertSame($idIndustriaNueva, (int) $empresa->idIndustria);
        $this->assertSame('https://devlab-actualizada.example.com', $empresa->paginaWeb);
    }

    public function test_update_valida_datos_invalidos_y_email_duplicado()
    {
        $this->crearRol(1, 'Alumno');
        $usuario1 = $this->crearUsuario(1, 'uno@test.com');
        $this->crearUsuario(1, 'dos@test.com');

        $response = $this->actingAs($usuario1, 'sanctum')
            ->putJson('/api/perfil', [
                'email' => 'dos@test.com',
                'telefono' => '123', // debe tener 10 dígitos
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'telefono']);
    }
}