<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMensajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Asegúrate de que solo usuarios autenticados puedan enviar mensajes
        return true; 
    }

    public function rules(): array
    {
        return [
            'destinatario_id' => 'required|string', // o 'exists:users,id' si manejas IDs relacionales
            'mensaje' => 'required|string|max:1000',
        ];
    }
}