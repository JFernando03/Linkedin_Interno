<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Industria extends Model
{
    protected $table = 'industrias';
    protected $primaryKey = 'idIndustria';
    public $timestamps = false;

    // Relación: Una industria tiene muchas carreras
    public function carreras()
    {
        return $this->hasMany(Carrera::class, 'idIndustria', 'idIndustria');
    }
}
