<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarcarSeleccionadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'id_item'          => 'required|integer|exists:items_intencion_compra,id_item_intencion_compra',
            'es_seleccionado'  => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'id_item.required'         => 'El ID del item es obligatorio.',
            'id_item.exists'           => 'El item no existe en el carrito.',
            'es_seleccionado.required' => 'El estado de selección es obligatorio.',
            'es_seleccionado.boolean'  => 'El estado debe ser verdadero o falso.',
        ];
    }
}
