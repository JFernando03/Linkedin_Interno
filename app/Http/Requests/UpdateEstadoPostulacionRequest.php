<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEstadoPostulacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && in_array((int) $user->idRol, [2, 3], true);
    }

    public function rules(): array
    {
        return [
            'idEstadoPostulacion' => 'required|integer|exists:estadosPostulaciones,idEstadoPostulacion',
        ];
    }
}