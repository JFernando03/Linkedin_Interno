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
        $user = $this->user();
        $userId = $user->idUsuario;

        // Reglas comunes para cualquier usuario
        $rules = [
            'sobreMi'  => 'nullable|string|max:500',
            'email'    => ['sometimes', 'email', Rule::unique('usuarios', 'email')->ignore($userId, 'idUsuario')],
            'telefono' => 'nullable|string|max:20',
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        // Si el usuario es un alumno, añadimos sus reglas específicas
        if ($user->alumno) {
            $rules['nombre']          = 'sometimes|string|max:100';
            $rules['apellidoPaterno'] = 'sometimes|string|max:100';
            $rules['apellidoMaterno'] = 'nullable|string|max:100';
            $rules['idCarrera']       = 'sometimes|integer|exists:carreras,idCarrera';
            $rules['curriculum']      = 'nullable|file|mimes:pdf|max:2048';
            $rules['semestre']        = 'sometimes|integer|min:1';
        }

        // Si el usuario es una empresa, añadimos sus reglas específicas
        if ($user->empresa) {
            $rules['nombreEmpresa'] = 'sometimes|string|max:255';
            $rules['idIndustria']   = 'sometimes|integer|exists:industrias,idIndustria';
            $rules['paginaWeb']     = 'nullable|url|max:255';
        }

        return $rules;
    }
}
