<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intereses', function (Blueprint $table) {
            $table->id('idInteres');
            $table->unsignedBigInteger('idEmpresa');
            $table->unsignedBigInteger('idAlumno');
            $table->timestamps();

            // Llaves foráneas apuntando a usuarios
            $table->foreign('idEmpresa')->references('idUsuario')->on('usuarios')->onDelete('cascade');
            $table->foreign('idAlumno')->references('idUsuario')->on('usuarios')->onDelete('cascade');
            
            // Evita que una empresa registre doble interés al mismo alumno
            $table->unique(['idEmpresa', 'idAlumno']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intereses');
    }
};