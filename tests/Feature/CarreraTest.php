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
        $industria = Industria::create([
            'industria' => 'Tecnología y Software'
        ]);

        Carrera::create([
            'carrera' => 'Licenciatura en Informática',
            'semestres' => 9,
            'idIndustria' => $industria->idIndustria,
        ]);

        $response = $this->getJson('/api/carreras');

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