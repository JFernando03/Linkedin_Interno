<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';
    protected $primaryKey = 'idEmpresa';
    public $timestamps = false;

    protected $fillable = [
        'idUsuario',
        'idIndustria',
        'nombreEmpresa',
        'paginaWeb',
    ];

    // Relación: La empresa pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuario', 'idUsuario');
    }

    // Relación: La empresa pertenece a una industria
    public function industria()
    {
        return $this->belongsTo(Industria::class, 'idIndustria', 'idIndustria');
    }
}
