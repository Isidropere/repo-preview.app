<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarCantidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'accion' => 'required|string|in:incrementar,decrementar',
        ];
    }

    public function messages(): array
    {
        return [
            'accion.required' => 'La acción es obligatoria.',
            'accion.in'       => 'La acción debe ser incrementar o decrementar.',
        ];
    }
}
