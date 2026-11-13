<?php

use App\Http\Controllers\Api\AlumnoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarreraController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\IndustriaController;
use App\Http\Controllers\Api\InteresController;
use App\Http\Controllers\Api\ModalidadController;
use App\Http\Controllers\Api\NivelController;
use App\Http\Controllers\Api\PerfilController;
use App\Http\Controllers\Api\PublicacionController;
use App\Http\Controllers\Api\HabilidadController;
use App\Http\Controllers\Api\VacanteController;
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
// Ruta para listar el catalogo de industrias disponibles
Route::get('/industrias', [IndustriaController::class, 'index']);
// Ruta para listar el catalogo de modalidades disponibles
Route::get('/modalidades', [ModalidadController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    //Ruta para logout
    Route::post('/logout', [AuthController::class, 'logout']);

    //Ruta para la visualización de perfil de alumno (HU-06)
    Route::get('/perfil', [PerfilController::class, 'show']);
    //Ruta para la edición de perfil de alumno (HU-07)
    Route::put('/perfil', [PerfilController::class, 'update']);

    // Ruta para ver el perfil público de cualquier usuario por su ID
    Route::get('/usuarios/{id}/perfil', [PerfilController::class, 'showPublic']);

    // Ruta para listar todos los usuarios en el directorio
    Route::get('/usuarios', function () {
        $usuarios = \App\Models\User::with(['rol', 'alumno.carrera', 'empresa.industria'])->get();
        return response()->json([
            'success' => true,
            'usuarios' => $usuarios,
        ], 200);
    });

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

    //Ruta para ver publicaciones
    Route::get('/vacantes', [VacanteController::class, 'index']);
    // Ruta para crear vacantes (HU-22)
    Route::post('/vacantes', [VacanteController::class, 'store']);
    //Ruta para editar vacantes (HU-23)
    Route::put('/vacantes/{id}', [VacanteController::class, 'update']);
    //Ruta para eliminar vacantes (HU-24)
    Route::delete('/vacantes/{id}', [VacanteController::class, 'destroy']);

    // Ruta de Feed unificado
    Route::get('/feed', [FeedController::class, 'index']);

    //Ruta para ver todas las conversaciones del usuario
    Route::get('/conversaciones', [ChatController::class, 'obtenerConversaciones']);
    //Ruta para ver historial de mensajes del usuario con otro usuario
    Route::get('/mensajes/{destinatarioId}', [ChatController::class, 'obtenerMensajes']);
    //Ruta para mandar mensajes a otro usuario (HU-14)
    Route::post('/mensajes', [ChatController::class, 'enviarMensaje']);

    // Ruta para que una empresa muestre interes a un alumno
    Route::post('/alumnos/{id}/interes', [InteresController::class, 'store']);
    // Ruta para ver las notificaciones
    Route::get('/notificaciones', [InteresController::class, 'misNotificaciones']);
});