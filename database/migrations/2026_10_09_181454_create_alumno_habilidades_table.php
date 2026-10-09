<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alumno_habilidades', function (Blueprint $table) {
            $table->id('id_alumno_habilidad');
            $table->foreignId('idAlumno')->constrained('alumnos','idAlumno')->onDelete('cascade');
            $table->foreignId('idHabilidad')->constrained('habilidades','idHabilidad')->onDelete('cascade');
            $table->foreignId('idNivel')->constrained('niveles','idNivel')->onDelete('cascade');

            // Evitar que el alumno repita la misma habilidad
            $table->unique(['idAlumno', 'idHabilidad']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_habilidades');
    }
};
