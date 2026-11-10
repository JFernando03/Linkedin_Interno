<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interes extends Model
{
    use HasFactory;

    protected $table = 'intereses';
    protected $primaryKey = 'idInteres';
    protected $guarded = [];

    public function empresa()
    {
        return $this->belongsTo(User::class, 'idEmpresa', 'idUsuario');
    }

    public function alumno()
    {
        return $this->belongsTo(User::class, 'idAlumno', 'idUsuario');
    }
}   