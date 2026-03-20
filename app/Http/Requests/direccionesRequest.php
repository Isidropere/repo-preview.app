<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class direccionesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'calle' => 'required|string|max:60',
            'N_casa_edificio' => 'required|string|max:60',
            'apto' => 'required|email|unique:direcciones,email,' . $this->route('direcciones'),
            'id_provincia' => 'required|string|max:10',
            'id_municipio' => 'required|exists:planes,id_plan',
            'geolocalizacion	' => 'required|string|max:60',
            'id_user' => 'nullable|string|max:15',
            'sector' => 'nullable|string|max:15',
            'telefono_contacto' => 'nullable|string|max:15',
            'es_predeterminada' => 'required|exists:provincias,id_provincia'
        ];
    }
}
