<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePerfilRequest extends FormRequest
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
        $userId = $this->user()->idUsuario;

        return [
            // Campos de usuario
            'sobreMi'       => 'nullable|string|max:500',
            'email'         => ['sometimes', 'email', Rule::unique('usuarios', 'email')->ignore($userId, 'idUsuario')],
            'telefono'      => 'nullable|string|max:20',
            'avatar'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Campos de alumno
            'nombre'          => 'sometimes|string|max:100',
            'apellidoPaterno' => 'sometimes|string|max:100',
            'apellidoMaterno' => 'nullable|string|max:100',
            'idCarrera'       => 'sometimes|integer|exists:carreras,idCarrera',
            'curriculum'      => 'nullable|file|mimes:pdf|max:2048',
            'semestre'        => 'required|integer|min:1',
        ];
    }
}
