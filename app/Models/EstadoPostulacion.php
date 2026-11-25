<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoPostulacion extends Model
{
    use HasFactory;

    protected $table = 'estadosPostulaciones';
    protected $primaryKey = 'idEstadoPostulacion';

    protected $fillable = [
        'nombre',
    ];

    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class, 'idEstadoPostulacion', 'idEstadoPostulacion');
    }
}