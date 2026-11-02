<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacante extends Model
{
    use HasFactory;

    protected $table = 'vacantes';
    protected $primaryKey = 'idVacante';
    protected $guarded = [];

    // Relación con el usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuario', 'idUsuario');
    }

    // Relación con el catálogo de modalidad
    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class, 'idModalidad', 'idModalidad');
    }
}