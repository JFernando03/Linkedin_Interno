<?php

use App\Http\Controllers\Api\AlumnoController;
use App\Http\Controllers\Api\AuthController;

// Ruta para el registro de alumnos (HU-01)
Route::post('/alumnos', [AlumnoController::class, 'store']);

// Ruta para el inicio de sesion de alumno (HU-02)
Route::post('/login', [AuthController::class, 'login']);