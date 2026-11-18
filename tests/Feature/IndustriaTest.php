<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Industria;

class IndustriaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_puede_listar_las_industrias_ordenadas_alfabeticamente()
    {
        // 1. Arrange: Creamos industrias desordenadas para probar el orderBy ascendente
        Industria::create(['industria' => 'Tecnología']);
        Industria::create(['industria' => 'Agricultura']);
        Industria::create(['industria' => 'Manufactura']);

        // 2. Act: Hacemos la petición GET al endpoint de industrias
        $response = $this->getJson('/api/industrias');

        // 3. Assert: Verificamos respuesta, estructura y el orden alfabético esperado
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ])
                 ->assertJsonCount(3, 'industrias')
                 ->assertJsonPath('industrias.0.industria', 'Agricultura')
                 ->assertJsonPath('industrias.1.industria', 'Manufactura')
                 ->assertJsonPath('industrias.2.industria', 'Tecnología')
                 ->assertJsonStructure([
                     'success',
                     'industrias' => [
                         '*' => [
                             'idIndustria',
                             'industria'
                         ]
                     ]
                 ]);
    }
}