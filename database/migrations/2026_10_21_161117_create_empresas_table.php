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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id('idEmpresa');
            $table->foreignId('idUsuario')->constrained('usuarios','idUsuario')->onDelete('cascade');
            $table->foreignId('idIndustria')->constrained('industrias','idIndustria')->onDelete('cascade');
            $table->string('nombreEmpresa');
            $table->string('paginaWeb')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
