<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlumnoController;

Route::get('/', function () {
    return redirect('/login');
});

// Ruta de Login
Route::get('/login', function () {
    return view('inicio-sesion-alumno');
})->name('login');

// Ruta de Registro (Asegúrate de tener ->name('registro'))
Route::get('/registro-alumno', function () {
    return view('registro');
})->name('registro');