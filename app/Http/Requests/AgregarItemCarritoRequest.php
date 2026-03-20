<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgregarItemCarritoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'id_item'  => 'required|integer|exists:items,id_item',
            'cantidad' => 'required|integer|min:1|max:1000000',
        ];
    }

    public function messages(): array
    {
        return [
            'id_item.required'  => 'Debes seleccionar un artículo.',
            'id_item.exists'    => 'El artículo seleccionado no existe.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.min'      => 'La cantidad mínima es 1.',
            'cantidad.max'      => 'La cantidad máxima es 1,000,000.',
        ];
    }
}
