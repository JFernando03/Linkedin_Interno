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
        Industria::create(['industria' => 'Tecnología']);
        Industria::create(['industria' => 'Agricultura']);
        Industria::create(['industria' => 'Manufactura']);

        $response = $this->getJson('/api/industrias');

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