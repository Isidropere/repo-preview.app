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
            'peso_lbs'          => 'nullable|numeric|min:0',
            'alto_cm'           => 'nullable|numeric|min:0',
            'ancho_cm'          => 'nullable|numeric|min:0',
            'profundo_cm'       => 'nullable|numeric|min:0',
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
            'peso_lbs.numeric'         => 'El peso debe ser numérico.',
            'alto_cm.numeric'          => 'El alto debe ser numérico.',
            'ancho_cm.numeric'         => 'El ancho debe ser numérico.',
            'profundo_cm.numeric'      => 'La profundidad debe ser numérica.',
        ];
    }
}
