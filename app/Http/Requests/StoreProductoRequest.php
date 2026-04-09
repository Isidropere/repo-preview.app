<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'item'              => 'required|string|max:255',
            'id_categoria_item' => 'required|exists:categorias_item,id_categoria_item',
            'valor'             => 'required|numeric|min:0',
            'descuento'         => 'nullable|numeric|min:0|max:100',
            'cantidad'          => 'required|integer|min:1|max:9999',
            'presentacion'      => 'required|string',
            'condicion'         => 'required|integer|in:1,2,3,4',
            'tipo_trans'        => 'required|integer|in:1,2,3',
            'imagen_principal'  => 'required|file|mimes:mp4,mov,jpeg,png,jpg,gif,webp|max:20480',
            'imagenes.*'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'peso_lbs'          => 'nullable|numeric|min:0',
            'alto_cm'           => 'nullable|numeric|min:0',
            'ancho_cm'          => 'nullable|numeric|min:0',
            'profundo_cm'       => 'nullable|numeric|min:0',
            'id_tipo_item'      => 'nullable|numeric|min:0',
            'colors'            => 'nullable|array',
            'colors.*'          => 'exists:colors,id_color',
            'stock.*'           => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'item.required'              => 'El nombre del producto es obligatorio.',
            'id_categoria_item.required' => 'Debe seleccionar una categoria.',
            'valor.required'             => 'El precio es obligatorio.',
            'cantidad.required'          => 'La cantidad es obligatoria.',
            'presentacion.required'      => 'La descripcion del producto es obligatoria.',
            'tipo_trans.required'        => 'Debe seleccionar un tipo de transaccion.',
            'imagen_principal.required'  => 'La imagen o video principal es obligatorio.',
        ];
    }
}
