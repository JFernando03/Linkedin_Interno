<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHabilidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $usuario = $this->user();
        $alumno = $usuario->alumno;

        $uniqueRule = Rule::unique('alumno_habilidades', 'idHabilidad');

        if ($alumno) {
            $uniqueRule = $uniqueRule->where(function ($query) use ($alumno) {
                return $query->where('idAlumno', $alumno->idAlumno);
            });
        }

        return [
            'idHabilidad' => [
                'required',
                'exists:habilidades,idHabilidad',
                $uniqueRule,
            ],
            'idNivel' => 'required|exists:niveles,idNivel',
        ];
    }
}