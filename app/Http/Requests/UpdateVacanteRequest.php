<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVacanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'puesto' => 'sometimes|string|max:255',
            'contenido' => 'sometimes|string',
            'idModalidad' => 'sometimes|exists:modalidades,idModalidad',
            'sueldo_min' => 'nullable|numeric|min:0',
            'sueldo_max' => 'nullable|numeric|min:0|gte:sueldo_min',
            'multimedia' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov|max:10240',
            'published_at' => 'nullable|date',
        ];
    }
}