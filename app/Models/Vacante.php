<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Vacante extends Model
{
    use HasFactory;

    protected $table = 'vacantes';
    protected $primaryKey = 'idVacante';
    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($vacante) {
            if (empty($vacante->published_at)) {
                $vacante->published_at = Carbon::now();
            }
        });
    }

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