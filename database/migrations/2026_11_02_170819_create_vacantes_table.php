<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacantes', function (Blueprint $table) {
            $table->id('idVacante');
            $table->unsignedBigInteger('idUsuario');
            $table->unsignedBigInteger('idModalidad')->nullable();
            $table->string('puesto');
            $table->text('contenido');
            $table->decimal('sueldo_min', 10, 2)->nullable();
            $table->decimal('sueldo_max', 10, 2)->nullable();
            $table->string('multimedia')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('idUsuario')->references('idUsuario')->on('usuarios')->onDelete('cascade');
            $table->foreign('idModalidad')->references('idModalidad')->on('modalidades')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacantes');
    }
};