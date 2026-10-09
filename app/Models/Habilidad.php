<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Habilidad extends Model
{
    use HasFactory;

    protected $table = 'habilidades';
    protected $primaryKey = 'idHabilidad';

    protected $fillable = ['nombre'];

    // Relacion: una habilidad pertenece a muchos usuario_habilidad
    public function usuarios()
    {
        return $this->belongsToMany(Alumno::class, 'alumno_habilidades', 'idHabilidad', 'idALumno')
                    ->using(AlumnoHabilidad::class)
                    ->withPivot('idNivel', 'idUsuarioHabilidad');
    }
}
