<?php

use App\Http\Controllers\Api\AlumnoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarreraController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\NivelController;
use App\Http\Controllers\Api\PerfilController;
use App\Http\Controllers\Api\PublicacionController;
use App\Http\Controllers\Api\HabilidadController;
use Illuminate\Support\Facades\Route;

// Ruta para el registro de alumnos (HU-01)
Route::post('/alumnos', [AlumnoController::class, 'store']);
// Ruta para el registro de empresas (HU-15)
Route::post('/empresas', [EmpresaController::class, 'store']);

// Ruta para el inicio de sesion de alumno (HU-02)
Route::post('/login', [AuthController::class, 'login']);

//Ruta para obtener todas las carreras
Route::get('/carreras', [CarreraController::class,'index']);
// Ruta para listar el catalogo de habilidades disponibles
Route::get('/habilidades',[HabilidadController::class,'index']);
// Ruta para listar el catalogo de niveles disponibles
Route::get('/niveles', [NivelController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    //Ruta para logout
    Route::post('/logout', [AuthController::class, 'logout']);

    //Ruta para la visualización de perfil de alumno (HU-06)
    Route::get('/perfil', [PerfilController::class, 'show']);
    //Ruta para la edición de perfil de alumno (HU-07)
    Route::put('/perfil', [PerfilController::class, 'update']);

    // Ruta para ver el perfil público de cualquier usuario por su ID
    Route::get('/usuarios/{id}/perfil', [PerfilController::class, 'showPublic']);

    //Ruta para agregar una habilidad al alumno
    Route::post('alumnos/habilidades',[PerfilController::class, 'storeHabilidad']);
    //Ruta para eliminar una habilidad al alumno
    Route::delete('alumnos/habilidades/{idAlumnoHabilidad}',[PerfilController::class, 'destroyHabilidad']);

    // Ruta para ver/descargar el currículum
    Route::get('/alumnos/curriculum', [AlumnoController::class, 'verCurriculum']);

    //Ruta para ver publicaciones
    Route::get('/publicaciones', [PublicacionController::class, 'index']);
    //Ruta para crear una publicacion
    Route::post('/publicaciones', [PublicacionController::class,'store']);
    //Ruta para editar una publicacion
    Route::put('/publicaciones/{idPublicacion}', [PublicacionController::class,'update']);
    //Ruta para eliminar una publicacion
    Route::delete('/publicaciones/{idPublicacion}', [PublicacionController::class, 'destroy']);
});