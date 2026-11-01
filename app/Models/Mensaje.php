<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Mensaje extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'mensajes';

    protected $fillable = [
        'remitente_id',
        'destinatario_id',
        'mensaje',
        'leido',
    ];
}