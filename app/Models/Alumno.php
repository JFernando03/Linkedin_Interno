<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $table = 'alumnos';
    protected $primaryKey = 'idAlumno';
    public $timestamps = false; // Ajusta a true si le agregaste timestamps a la migración de alumnos

    protected $fillable = [
        'idUsuario',
        'nombre',
        'apellidoPaterno',
        'apellidoMaterno',
        'idCarrera',
        'curriculum',
    ];

    // Relación: El alumno pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }

    // Relación: El alumno pertenece a una carrera
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'idCarrera', 'idCarrera');
    }
}
