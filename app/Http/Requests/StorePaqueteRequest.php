<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaqueteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nombre_paquete' => 'nullable|string|max:255',
            'nombre'         => 'nullable|string|max:255',
            'items'          => 'required|array|min:1',
            'items.*'        => 'integer|exists:items,id_item',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'  => 'Debes seleccionar al menos un artículo.',
            'items.min'       => 'Debes seleccionar al menos un artículo.',
            'items.*.exists'  => 'Uno de los artículos seleccionados no existe.',
            'items.*.integer' => 'Los IDs de artículos deben ser números enteros.',
        ];
    }
}
