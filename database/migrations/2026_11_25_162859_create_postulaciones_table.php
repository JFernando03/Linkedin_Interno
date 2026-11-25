<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postulaciones', function (Blueprint $table) {
            $table->id('idPostulacion');
            $table->unsignedBigInteger('idVacante');
            $table->unsignedBigInteger('idUsuario');
            $table->unsignedBigInteger('idEstadoPostulacion')->default(1);
            $table->text('mensaje')->nullable();
            $table->timestamps();

            $table->foreign('idVacante')->references('idVacante')->on('vacantes')->onDelete('cascade');
            $table->foreign('idUsuario')->references('idUsuario')->on('usuarios')->onDelete('cascade');
            $table->foreign('idEstadoPostulacion')->references('idEstadoPostulacion')->on('estadosPostulaciones')->onDelete('restrict');

            // Evita que un alumno se postule más de una vez a la misma vacante
            $table->unique(['idVacante', 'idUsuario']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postulaciones');
    }
};