<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Modalidad;

class ModalidadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_puede_listar_las_modalidades_correctamente()
    {
        // 1. Arrange: Creamos un par de modalidades de prueba en la base de datos
        Modalidad::create(['nombre' => 'Presencial']);
        Modalidad::create(['nombre' => 'Remoto']);

        // 2. Act: Hacemos una petición GET al endpoint de modalidades
        $response = $this->getJson('/api/modalidades');

        // 3. Assert: Verificamos la respuesta
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ])
                 ->assertJsonCount(2, 'modalidades')
                 ->assertJsonStructure([
                     'success',
                     'modalidades' => [
                         '*' => [
                             'idModalidad',
                             'nombre',
                         ]
                     ]
                 ]);
    }
}