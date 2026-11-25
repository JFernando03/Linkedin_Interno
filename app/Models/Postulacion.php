<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Postulacion extends Model
{
    use HasFactory;

    protected $table = 'postulaciones';
    protected $primaryKey = 'idPostulacion';

    protected $fillable = [
        'idVacante',
        'idUsuario',
        'idEstadoPostulacion',
        'mensaje',
    ];

    protected $casts = [
        'idVacante'           => 'integer',
        'idUsuario'           => 'integer',
        'idEstadoPostulacion' => 'integer',
    ];

    public function vacante()
    {
        return $this->belongsTo(Vacante::class, 'idVacante', 'idVacante');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuario', 'idUsuario');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoPostulacion::class, 'idEstadoPostulacion', 'idEstadoPostulacion');
    }
}