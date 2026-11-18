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
        // 1. Arrange: Creamos habilidades en orden desordenado para probar el orderBy ascendente
        Habilidad::create(['nombre' => 'PHP']);
        Habilidad::create(['nombre' => 'Docker']);
        Habilidad::create(['nombre' => 'Laravel']);

        // 2. Act: Hacemos la petición GET al endpoint de habilidades
        $response = $this->getJson('/api/habilidades');

        // 3. Assert: Verificamos respuesta, estructura y el orden alfabético esperado
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