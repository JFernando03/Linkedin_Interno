<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAlumnoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'idRol' => 'required|exists:roles,idRol',
            'email' => 'required|string|email|max:50|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'telefono' => 'required|string|max:10',
            'sobreMi' => 'nullable|string',
            'nombre' => 'required|string|max:50',
            'apellidoPaterno' => 'required|string|max:50',
            'apellidoMaterno' => 'required|string|max:50',
            'idCarrera' => 'required|exists:carreras,idCarrera',
            'curriculum' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ];
    }
}
