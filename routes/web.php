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

// Ruta de Registro
Route::get('/registro-alumno', function () {
    return view('registro');
})->name('registro');

Route::get('/perfil', function () {
    return view('visualizacion-de-perfil-alumno');
});

// Ruta de edición (Pública temporalmente para pruebas)
Route::get('/perfil/editar', function () {
    return view('edicion-perfil-alumno');
})->name('perfil.editar');