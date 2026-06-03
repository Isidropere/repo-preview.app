<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcesarPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'id_tarjeta' => 'required|string',
            'cvv'        => 'required|string|min:3|max:4',
        ];
    }

    public function messages(): array
    {
        return [
            'id_tarjeta.required' => 'Debes seleccionar una tarjeta de pago.',
            'cvv.required'        => 'El CVV es obligatorio.',
            'cvv.min'             => 'El CVV debe tener al menos 3 dígitos.',
            'cvv.max'             => 'El CVV no puede tener más de 4 dígitos.',
        ];
    }
}
