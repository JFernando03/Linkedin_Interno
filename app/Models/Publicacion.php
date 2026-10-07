<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publicacion extends Model
{
    use HasFactory;

    protected $table = 'publicaciones';
    protected $primaryKey = 'idPublicacion';

    protected $fillable = [
        'idUsuario',
        'titulo',
        'contenido',
        'multimedia',
        'published_at',
    ];

    // Relación: Una publicación pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuario', 'idUsuario');
    }
}
