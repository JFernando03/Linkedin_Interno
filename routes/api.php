<?php

use App\Http\Controllers\Api\AlumnoController;

// Ruta para el registro de alumnos (HU-01)
Route::post('/alumnos', [AlumnoController::class, 'store']);