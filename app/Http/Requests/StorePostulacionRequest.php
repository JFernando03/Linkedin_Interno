<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostulacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && (int) $user->idRol === 1;
    }

    public function rules(): array
    {
        return [
            'idVacante' => 'required|integer|exists:vacantes,idVacante',
            'mensaje'   => 'nullable|string|max:1000',
        ];
    }
}