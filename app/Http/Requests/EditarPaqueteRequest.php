<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditarPaqueteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nombre'         => 'nullable|string|max:255',
            'nombre_paquete' => 'nullable|string|max:255',
            'items'          => 'nullable|array',
            'items.*'        => 'integer|exists:items,id_item',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.max'          => 'El nombre no puede exceder 255 caracteres.',
            'nombre_paquete.max'  => 'El nombre no puede exceder 255 caracteres.',
            'items.*.exists'      => 'Uno de los artículos seleccionados no existe.',
            'items.*.integer'     => 'Los IDs de artículos deben ser números enteros.',
        ];
    }
}
