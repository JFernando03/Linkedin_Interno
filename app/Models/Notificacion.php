<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Notificacion extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'notificaciones';

    protected $fillable = [
        'idUsuario',   // A quién va dirigida la notificación (Alumno, Empresa, Admin)
        'titulo',      // Título visible de la alerta
        'mensaje',     // Descripción o cuerpo del mensaje
        'tipo',        // Categoría: 'interes_empresa', 'nueva_postulacion', 'mensaje_chat', 'sistema', etc.
        'data',        // Array o JSON libre para guardar IDs relacionados (ej: idEmpresa, idVacante, idPublicacion)
        'leido',       // Booleano para saber si ya la abrió
        'created_at',
        'updated_at'
    ];
}