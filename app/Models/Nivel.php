<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nivel extends Model
{
    use HasFactory;

    protected $table = 'niveles';
    protected $primaryKey = 'idNivel';

    protected $fillable = [
        'nombre',
    ];

    // Relación: Un nivel puede tener muchas asignaciones de usuario-habilidad
    public function usuariosHabilidades()
    {
        return $this->hasMany(usuario_habilidad::class, 'idNivel', 'idNivel');
    }
}
