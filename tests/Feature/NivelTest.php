<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Nivel;

class NivelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_puede_listar_los_niveles_correctamente()
    {
        Nivel::create(['nombre' => 'Básico']);
        Nivel::create(['nombre' => 'Avanzado']);

        $response = $this->getJson('/api/niveles');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Niveles obtenidos correctamente',
                 ])
                 ->assertJsonCount(2, 'niveles')
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'niveles' => [
                         '*' => [
                             'idNivel',
                             'nombre'
                         ]
                     ]
                 ]);
    }
}