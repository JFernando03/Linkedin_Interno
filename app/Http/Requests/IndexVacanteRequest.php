<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexVacanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q'           => 'nullable|string|max:120',
            'idModalidad' => 'nullable|integer',
            'sueldo_min'  => 'nullable|numeric|min:0',
            'sueldo_max'  => 'nullable|numeric|min:0',
        ];
    }
}