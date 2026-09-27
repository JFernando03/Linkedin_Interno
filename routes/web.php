<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlumnoController;

Route::get('/', function () {
    return view('registro'); // Suponiendo que tu archivo es resources/views/registro.blade.php
})->name('alumnos.registro');


