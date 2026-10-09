<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $table = 'alumnos';
    protected $primaryKey = 'idAlumno';
    public $timestamps = false;

    protected $fillable = [
        'idUsuario',
        'nombre',
        'apellidoPaterno',
        'apellidoMaterno',
        'idCarrera',
        'curriculum',
        'semestre',
    ];

    // Relación: El alumno pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuario', 'idUsuario');
    }

    // Relación: El alumno pertenece a una carrera
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'idCarrera', 'idCarrera');
    }

    //Relación: El alumno tiene muchas habilidades
    public function habilidades()
    {
        return $this->belongsToMany(Habilidad::class, 'alumno_habilidades', 'idAlumno', 'idHabilidad')
                    ->using(AlumnoHabilidad::class) // Opcional si creas el modelo pivote
                    ->withPivot('idNivel', 'id_alumno_habilidad');
    }
}
