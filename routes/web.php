<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlumnoController;
use App\Models\Carrera; //Se importa el modelo de carrera

Route::get('/', function () {
    return redirect('/login');
});

// Ruta de Login
Route::get('/login', function () {
    return view('inicio-sesion-alumno');
})->name('login');

// Ruta de Registro
Route::get('/registro-alumno', function () {
    return view('registro');
})->name('registro');
// 1. Ruta para VER el perfil (La que faltaba y causaba el 404)
Route::get('/perfil', function () {
    return view('visualizacion-de-perfil-alumno'); // Nombre de tu vista de perfil
})->name('perfil');

// 2. Ruta para EDITAR el perfil (Simplificada para usar la API)
Route::get('/perfil/editar', function () {
    return view('edicion-perfil-alumno');
})->name('perfil.editar');