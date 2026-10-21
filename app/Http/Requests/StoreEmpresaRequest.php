<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmpresaRequest extends FormRequest
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
            // Datos del usuario base
            'idRol' => 'required|exists:roles,idRol',
            'email' => 'required|string|email|max:50|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'telefono' => 'nullable|string|max:20',
            'sobreMi' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Datos específicos de la empresa
            'nombreEmpresa' => 'required|string|max:255',
            'idIndustria' => 'required|exists:industrias,idIndustria',
            'paginaWeb' => 'nullable|url|max:255',
        ];
    }
}
