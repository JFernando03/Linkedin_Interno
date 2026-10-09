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
        Schema::create('usuario_habilidades', function (Blueprint $table) {
            $table->id('id_usuario_habilidad');
            $table->foreignId('idUsuario')->constrained('usuarios','idUsuario')->onDelete('cascade');
            $table->foreignId('idHabilidad')->constrained('habilidades','idHabilidad')->onDelete('cascade');
            $table->foreignId('idNivel')->constrained('niveles','idNivel')->onDelete('cascade');

            // Evitar que el usuario repita la misma habilidad
            $table->unique(['idUsuario', 'idHabilidad']);
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
