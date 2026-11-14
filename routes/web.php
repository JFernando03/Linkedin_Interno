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
//Ruta de perfil
Route::get('/perfil', function () {
    return view('visualizacion-de-perfil-alumno'); // Nombre de tu vista de perfil
})->name('perfil');
//Ruta de perfil-editar
Route::get('/perfil/editar', function () {
    return view('edicion-perfil-alumno');
})->name('perfil.editar');
//Ruta para el feed de publicaciones
Route::get('/feed', function () {
    return view('visualizacion-feed');
});
// Ruta para ver el perfil de la empresa
Route::get('/perfil/empresa', function () {
    return view('visualizacion-perfil-empresa');
});

Route::get('/perfil/empresa/editar', function () {
    return view('edicion-perfil-empresa');
});



Route::get('/usuarios/{id}/perfil', function ($id) {
    return view('vista-perfiles');
});

// Ruta para el módulo de Chat / Mensajes
Route::get('/chat', function () {
    return view('chat-usuarios');
})->name('chat');