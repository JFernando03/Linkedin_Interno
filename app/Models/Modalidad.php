<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modalidad extends Model
{
    use HasFactory;

    protected $table = 'modalidades';
    protected $primaryKey = 'idModalidad';
    protected $guarded = [];

    // Relacion con las vacantes
    public function vacantes()
    {
        return $this->hasMany(Vacante::class, 'idModalidad', 'idModalidad');
    }
}