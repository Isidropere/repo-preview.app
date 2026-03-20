<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'item' => 'required|string|max:200',
            'id_categoria_item' => 'nullable|exists:categorias_item,id_categoria_item',
            'tipo' => 'required|integer|in:1,2,3',
            'valor' => 'nullable|numeric|min:0',
            'presentacion' => 'required|string|max:250',
            'peso_lbs' => 'required|numeric|min:0',
            'alto_cm' => 'required|numeric|min:0',
            'ancho_cm' => 'required|numeric|min:0',
            'profundo_cm' => 'required|numeric|min:0',
            'estatus' => 'required|integer|in:1,2',
            'tipo_trans' => 'nullable|integer|in:1,2,3',
            'condicion' => 'nullable|integer|in:0,1'
        ];
    }
}
