<?php

namespace App\Http\Requests;

use App\Rules\LuhnCheck;
use Illuminate\Foundation\Http\FormRequest;

class StoreTarjetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Forzar respuesta JSON cuando la validación falla.
     * Esto evita redirects cuando se llama desde AJAX/fetch.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator,
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422)
        );
    }

    public function rules(): array
    {
        return [
            'no_tarjeta'       => ['required', 'string', 'min:13', 'max:19', new LuhnCheck],
            'mes_expiracion'   => 'required|numeric|min:1|max:12',
            'anio_expiracion'  => 'nullable|numeric|min:' . date('Y'),
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $mes = (int) $this->input('mes_expiracion');
            $anio = (int) $this->input('anio_expiracion');

            if ($anio > 0 && $mes > 0) {
                $expDate = mktime(0, 0, 0, $mes + 1, 1, $anio);
                if ($expDate < time()) {
                    $validator->errors()->add('mes_expiracion', 'La tarjeta está expirada.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'no_tarjeta.required'       => 'El número de tarjeta es obligatorio.',
            'mes_expiracion.required'   => 'El mes de expiración es obligatorio.',
            'anio_expiracion.min'       => 'El año de expiración no puede ser en el pasado.',
            'nombre_titular.required'   => 'El nombre del titular es obligatorio.',
        ];
    }
}
