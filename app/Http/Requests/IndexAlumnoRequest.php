<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexAlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && in_array((int) $user->idRol, [2, 3], true);
    }

    public function rules(): array
    {
        return [
            'idCarrera'        => 'nullable|integer',
            'semestre'         => 'nullable|integer|min:1|max:12',
            'tiene_curriculum' => 'nullable|boolean',
            'habilidades'      => 'nullable|string|max:255',
        ];
    }
}