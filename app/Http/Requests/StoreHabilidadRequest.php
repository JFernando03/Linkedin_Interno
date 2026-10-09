<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHabilidadRequest extends FormRequest
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
        $usuario = $this->user();
        $alumno = $usuario->alumno;

        return [
            'idHabilidad' => [
                'required',
                'exists:habilidades,idHabilidad',
                Rule::unique('alumno_habilidades','idHabilidad')->where(function ($query) use ($alumno) {
                    return $query->where('idAlumno',$alumno->idAlumno);
                }),
            ],
            'idNivel' => 'required|exists:niveles,idNivel'
        ];
    }
}
