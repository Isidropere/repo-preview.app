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
        $rules = [
            'id_tarjeta' => 'required|string',
        ];

        // CVV requerido solo para CardNet
        if (config('services.payment.driver', 'cardnet') === 'cardnet') {
            $rules['cvv'] = 'required|string|min:3|max:4';
        }

        return $rules;
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
