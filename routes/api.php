<?php

use App\Http\Controllers\Api\AlumnoController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Ruta para el registro de alumnos (HU-01)
Route::post('/alumnos', [AlumnoController::class, 'store']);

// Ruta para el inicio de sesion de alumno (HU-02)
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas por el token del login
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/perfil', [AuthController::class, 'perfil']);
    Route::put('/perfil', [AuthController::class, 'updatePerfil']);
});