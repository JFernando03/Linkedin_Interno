<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class usuario_habilidad extends Pivot
{
    protected $table = 'usuario_habilidades';
    protected $primaryKey = 'id_usuario_habilidad';
    public $incrementing = true;
    
    public $timestamps = false;

    protected $fillable = [
        'idUsuario',
        'idHabilidad',
        'idNivel',
    ];
}
