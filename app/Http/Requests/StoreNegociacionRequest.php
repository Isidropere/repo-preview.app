<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNegociacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'item_id'      => 'required|integer|exists:items,id_item',
            'mensaje'      => 'required|string|min:1|max:500',
            'paquete_id'   => 'nullable|integer|exists:paquetes,id_paquete',
            'monto_oferta' => 'nullable|numeric|min:0|max:99999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'item_id.required'      => 'Debes seleccionar un artículo para negociar.',
            'item_id.exists'        => 'El artículo seleccionado no existe.',
            'mensaje.required'      => 'El mensaje es obligatorio.',
            'mensaje.max'           => 'El mensaje no puede exceder 500 caracteres.',
            'paquete_id.exists'     => 'El paquete seleccionado no existe.',
            'monto_oferta.numeric'  => 'El monto de oferta debe ser un número.',
            'monto_oferta.min'      => 'El monto de oferta no puede ser negativo.',
        ];
    }
}
