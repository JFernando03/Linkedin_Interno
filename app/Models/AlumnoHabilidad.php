<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AlumnoHabilidad extends Pivot
{
    protected $table = 'alumno_habilidades';
    protected $primaryKey = 'id_alumno_habilidad';
    public $incrementing = true;
    
    public $timestamps = false;

    protected $fillable = [
        'idAlumno',
        'idHabilidad',
        'idNivel',
    ];
}
