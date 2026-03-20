<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContraofertaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'monto_contra_oferta' => 'nullable|numeric|min:0|max:99999999.99',
            'mensaje'             => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'monto_contra_oferta.numeric' => 'El monto debe ser un número.',
            'monto_contra_oferta.min'     => 'El monto no puede ser negativo.',
            'mensaje.max'                 => 'El mensaje no puede exceder 500 caracteres.',
        ];
    }
}
