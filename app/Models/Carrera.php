<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    protected $table = 'carreras';
    protected $primaryKey = 'idCarrera';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'semestres',
        'idIndustria',
    ];

    // Relación: Una carrera pertenece a una industria
    public function industria()
    {
        return $this->belongsTo(Industria::class, 'idIndustria', 'idIndustria');
    }
    // Relación: Una carrera tiene muchos alumnos
    public function alumnos()
    {
        return $this->hasMany(Alumno::class, 'idCarrera', 'idCarrera');
    }
}
