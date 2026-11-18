<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Habilidad;

class HabilidadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_puede_listar_las_habilidades_ordenadas_alfabeticamente()
    {
        Habilidad::create(['nombre' => 'PHP']);
        Habilidad::create(['nombre' => 'Docker']);
        Habilidad::create(['nombre' => 'Laravel']);

        $response = $this->getJson('/api/habilidades');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Habilidades Obtenidas correctamente',
                 ])
                 ->assertJsonCount(3, 'habilidades')
                 ->assertJsonPath('habilidades.0.nombre', 'Docker')
                 ->assertJsonPath('habilidades.1.nombre', 'Laravel')
                 ->assertJsonPath('habilidades.2.nombre', 'PHP')
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'habilidades' => [
                         '*' => [
                             'idHabilidad',
                             'nombre'
                         ]
                     ]
                 ]);
    }
}