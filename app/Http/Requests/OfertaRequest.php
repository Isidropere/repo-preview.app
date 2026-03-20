<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfertaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'oferente' => 'required|exists:direcciones,id_user',
            'beneficiario' => 'required|exists:direcciones,id_user',
            'condicion' => 'required|string|in:PENDIENTE,APROBADA,RECHAZADA',
            'id_paquete' => 'required|exists:paquetes,id_paquete'
        ];
    }
}
