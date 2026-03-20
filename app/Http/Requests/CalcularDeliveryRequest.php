<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalcularDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // API pública
    }

    public function rules(): array
    {
        return [
            'pueblo'            => 'required|string|max:100',
            'tipo_destinatario' => 'nullable|string|in:persona,empresa',
            'valor_articulo'    => 'nullable|numeric|min:0|max:99999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'pueblo.required'          => 'El campo pueblo es requerido.',
            'pueblo.max'               => 'El nombre del pueblo no puede exceder 100 caracteres.',
            'tipo_destinatario.in'     => 'El tipo de destinatario debe ser persona o empresa.',
            'valor_articulo.numeric'   => 'El valor del artículo debe ser un número.',
            'valor_articulo.min'       => 'El valor del artículo no puede ser negativo.',
        ];
    }
}
