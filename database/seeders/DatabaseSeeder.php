<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // 1. Insertar los roles base
        DB::table('roles')->insert([
            ['rol' => 'Alumno'],
            ['rol' => 'Empresa'],
            ['rol' => 'Administrador'],
        ]);

        // 2. Insertar las industrias
        DB::table('industrias')->insert([
            ['industria' => 'Programación'],
            ['industria' => 'Analisis de datos'],
        ]);

        // 3. Insertar las carreras de la facultad
        DB::table('carreras')->insert([
            [
                'carrera' => 'Licenciatura en Informática',
                'semestres' => 10,
                'idIndustria' => 1
            ],
            [
                'carrera' => 'Licenciatura en Informática Modalidad Virtual',
                'semestres' => 8,
                'idIndustria' => 1
            ],
            [
                'carrera' => 'Licenciatura en Ingeniería en Ciencia de Datos',
                'semestres' => 9,
                'idIndustria' => 2
            ],
        ]);

        // 4. Insertar los niveles de dominio
        DB::table('niveles')->insert([
            ['nombre' => 'Bajo'],
            ['nombre' => 'Medio'],
            ['nombre' => 'Alto'],
        ]);

        // 5. Insertar habilidades tecnológicas e idiomas base
        DB::table('habilidades')->insert([
            // Tecnologías / Frameworks / Lenguajes
            ['nombre' => 'PHP'],
            ['nombre' => 'Laravel'],
            ['nombre' => 'Java'],
            ['nombre' => 'Kotlin'],
            ['nombre' => 'Python'],
            ['nombre' => 'MySQL'],
            ['nombre' => 'React'],
            
            ['nombre' => 'Inglés'],
            ['nombre' => 'Francés'],
            ['nombre' => 'Español'],
        ]);
    }
}
