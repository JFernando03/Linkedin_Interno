<?php

namespace App\Models;

use Carbon\Carbon;
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

    // Si 'published_at' viene vacio, darle el valor actual
    public function setPublishedAtAttribute($value)
    {
        $this->attributes['published_at'] = $value ?? Carbon::now();
    }

    // Relación: Una publicación pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuario', 'idUsuario');
    }
}
