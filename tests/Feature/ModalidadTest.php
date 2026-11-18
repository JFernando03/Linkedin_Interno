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
        Modalidad::create(['nombre' => 'Presencial']);
        Modalidad::create(['nombre' => 'Remoto']);

        $response = $this->getJson('/api/modalidades');

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