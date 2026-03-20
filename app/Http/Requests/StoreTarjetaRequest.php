<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTarjetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $driver = config('services.payment.driver', 'cardnet');

        if ($driver === 'stripe') {
            return [
                'payment_method_id' => 'required|string|max:255',
                'last4'             => 'required|string|size:4',
                'tipo_tarjeta'      => 'nullable|string|max:50',
                'banco_tarjeta'     => 'nullable|string|max:100',
                'mes_expiracion'    => 'nullable|string|max:2',
                'nombre_titular'    => 'required|string|max:255',
            ];
        }

        return [
            'no_tarjeta'       => 'required|string|min:13|max:19',
            'mes_expiracion'   => 'required|numeric|min:1|max:12',
            'anio_expiracion'  => 'nullable|numeric',
            'nombre_titular'   => 'nullable|string|max:255',
            'banco_tarjeta'    => 'nullable|string|max:100',
            'tipo_tarjeta'     => 'nullable|string|max:50',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('no_tarjeta')) {
            $this->merge([
                'no_tarjeta' => preg_replace('/\D/', '', $this->input('no_tarjeta', '')),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'no_tarjeta.required'     => 'El número de tarjeta es obligatorio.',
            'mes_expiracion.required' => 'El mes de expiración es obligatorio.',
            'nombre_titular.required' => 'El nombre del titular es obligatorio.',
        ];
    }
}
