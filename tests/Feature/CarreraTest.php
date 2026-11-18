<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Carrera;
use App\Models\Industria;

class CarreraTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_puede_listar_las_carreras_correctamente()
    {
        // 1. Arrange: Creamos primero una industria (por la restricción de llave foránea)
        $industria = Industria::create([
            'industria' => 'Tecnología y Software'
        ]);

        // Creamos la carrera asociada a esa industria
        Carrera::create([
            'carrera' => 'Licenciatura en Informática',
            'semestres' => 9,
            'idIndustria' => $industria->idIndustria,
        ]);

        // 2. Act: Hacemos la petición GET al endpoint de carreras
        $response = $this->getJson('/api/carreras');

        // 3. Assert: Verificamos el resultado
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ])
                 ->assertJsonCount(1, 'carreras')
                 ->assertJsonStructure([
                     'success',
                     'carreras' => [
                         '*' => [
                             'idCarrera',
                             'carrera',
                             'semestres',
                             'idIndustria'
                         ]
                     ]
                 ]);
    }
}