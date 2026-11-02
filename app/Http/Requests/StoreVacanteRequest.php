<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVacanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'puesto' => 'required|string|max:255',
            'contenido' => 'required|string',
            'idModalidad' => 'required|exists:modalidades,idModalidad',
            'sueldo_min' => 'nullable|numeric|min:0',
            'sueldo_max' => 'nullable|numeric|min:0|gte:sueldo_min',
            'multimedia' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov|max:10240',
            'published_at' => 'nullable|date',
        ];
    }
}