<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'usuarios';
    protected $primaryKey = 'idUsuario';

    protected $fillable = [
        'idRol',
        'sobreMi',
        'email',
        'password',
        'telefono',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relación: Un usuario pertenece a un rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'idRol', 'idRol');
    }

    // Relación: Un usuario tiene un registro de alumno
    public function alumno()
    {
        return $this->hasOne(Alumno::class, 'idUsuario', 'idUsuario');
    }

    // Relación: Un usuario tiene un registro de empresa
    public function empresa()
    {
        return $this->hasOne(Empresa::class, 'idUsuario', 'idUsuario');
    }
}
