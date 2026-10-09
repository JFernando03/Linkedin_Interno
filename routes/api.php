<?php

use App\Http\Controllers\Api\AlumnoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarreraController;
use App\Http\Controllers\Api\PublicacionController;
use Illuminate\Support\Facades\Route;

// Ruta para el registro de alumnos (HU-01)
Route::post('/alumnos', [AlumnoController::class, 'store']);

// Ruta para el inicio de sesion de alumno (HU-02)
Route::post('/login', [AuthController::class, 'login']);

//Ruta para obtener todas las carreras
Route::get('/carreras', [CarreraController::class,'index']);

Route::middleware('auth:sanctum')->group(function () {
    //Ruta para la visualización de perfil de alumno (HU-06)
    Route::get('/perfil', [AuthController::class, 'perfil']);
    //Ruta para la edición de perfil de alumno (HU-07)
    Route::put('/perfil', [AuthController::class, 'updatePerfil']);

    // Ruta para ver/descargar el currículum
    Route::get('/alumno/curriculum', [AlumnoController::class, 'verCurriculum']);

    //Ruta para crear una publicacion
    Route::post('/publicaciones', [PublicacionController::class,'store']);
    //Ruta para editar una publicacion
    Route::put('/publicaciones/{idPublicacion}', [PublicacionController::class,'update']);
    //Ruta para eliminar una publicacion
    Route::delete('/publicaciones/{idPublicacion}', [PublicacionController::class, 'destroy']);
});