<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UsuarioRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'nombres' => 'required|string|max:60',
            'apellidos' => 'required|string|max:60',
            'telefono' => 'required|string|max:15',
            'email' => 'required|email|unique:users,email',
            'nombre_usuario' => 'required|string|max:60|unique:users,nombre_usuario',
            'password' => 'required|string|min:8|confirmed',
            'id_tipo_usuario' => 'required|integer|exists:tipos_usuarios,id',
            'foto_perfil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        // Para actualización (PUT/PATCH)
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $userId = $this->user ? $this->user->id : $this->route('usuario');
            $rules['email'] = 'required|email|unique:users,email,' . $userId;
            $rules['nombre_usuario'] = 'required|string|max:60|unique:users,nombre_usuario,' . $userId;
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => [
                'string' => 'El campo :attribute no debe exceder :max caracteres.',
                'file' => 'El archivo :attribute no debe exceder :max kilobytes.'
            ],
            'email' => 'El campo :attribute debe ser un correo electrónico válido.',
            'unique' => 'El :attribute ya está en uso.',
            'min' => [
                'string' => 'El campo :attribute debe tener al menos :min caracteres.'
            ],
            'confirmed' => 'La confirmación de :attribute no coincide.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'exists' => 'El :attribute seleccionado no es válido.',
            'image' => 'El campo :attribute debe ser una imagen.',
            'mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',

            // Mensajes específicos
            'id_tipo_usuario.required' => 'Debe seleccionar un tipo de usuario.',
            'id_tipo_usuario.exists' => 'El tipo de usuario seleccionado no es válido.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    public function attributes()
    {
        return [
            'nombres' => 'nombres',
            'apellidos' => 'apellidos',
            'telefono' => 'teléfono',
            'email' => 'correo electrónico',
            'nombre_usuario' => 'nombre de usuario',
            'password' => 'contraseña',
            'id_tipo_usuario' => 'tipo de usuario',
            'foto_perfil' => 'foto de perfil',
        ];
    }
}
