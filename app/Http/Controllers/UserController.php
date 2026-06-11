<?php

namespace App\Http\Controllers;

use App\Models\Tipos_usuario;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * ============================================================
 * UserController — Gestión de usuarios y perfil
 * ============================================================
 *
 * Funcionalidades:
 * - Perfil del usuario autenticado (ver y editar)
 * - Cambio de tipo de usuario
 * - CRUD administrativo de usuarios (resource)
 * - Activar/desactivar usuarios (toggle estatus)
 *
 * Rutas:
 *   GET  /mi-perfil              → profile()
 *   PUT  /actualizar-perfil      → updateProfile()
 *   GET  /cambiar-tipo-usuario   → editTipoUsuario()
 *   POST /cambiar-tipo-usuario   → updateTipoUsuario()
 *   Resource /usuarios           → index, show, edit, update, destroy
 *   PUT  /usuarios/{id}/toggle-status → toggleStatus()
 *
 * Middleware: auth (todas), verified (tipo usuario)
 * ============================================================
 */
class UserController extends Controller
{
    /* ── Tipo de usuario ── */

    public function editTipoUsuario()
    {
        $user = auth()->user();
        $tipos = Tipos_usuario::whereNotIn('id_tipo_usuario', [$user->id_tipo_usuario])->get();

        return view('cambiar_tipo_usuario', compact('user', 'tipos'));
    }

    public function updateTipoUsuario(Request $request)
    {
        $request->validate([
            'id_tipo_usuario' => 'required|integer|exists:tipos_usuarios,id_tipo_usuario|not_in:3,4',
        ]);

        $user = auth()->user();
        $user->id_tipo_usuario = $request->id_tipo_usuario;
        $user->save();

        return redirect()->route('tu_cuenta')->with('success', 'Tipo de usuario actualizado correctamente.');
    }

    /* ── Perfil ── */

    public function profile()
    {
        $user = auth()->user()->load('tiposUsuario', 'direcciones');
        return view('perfil.index', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'nombres'        => 'sometimes|string|max:100',
            'apellidos'      => 'sometimes|string|max:100',
            'telefono'       => 'nullable|string|max:20',
            'nombre_usuario' => 'nullable|string|max:50|unique:users,nombre_usuario,' . auth()->id(),
            'profile_photo'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = auth()->user();

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $resultado = \App\Helpers\ImageHelper::guardar($file, 'imgs/profiles', 'profile_', $user->id);
            $user->profile_photo_path = $resultado['path'];
            $user->foto_perfil_estado = 'pendiente';
        }

        $fields = array_filter($request->only('nombres', 'apellidos', 'telefono', 'nombre_usuario'));
        if (!empty($fields)) {
            $user->update($fields);
        } else {
            $user->save();
        }

        return redirect()->route('tu_cuenta')->with('success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'password' => 'required|string|min:8|confirmed',
        ];

        if ($user->password_defined) {
            $rules['current_password'] = 'required|string';
        }

        $request->validate($rules, [
            'current_password.required' => 'La contraseña actual es obligatoria.',
            'password.required'         => 'La nueva contraseña es obligatoria.',
            'password.min'              => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'        => 'Las contraseñas no coinciden.',
        ]);

        if ($user->password_defined) {
            if (!\Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
            }
        }

        $user->password = \Hash::make($request->password);
        $user->password_defined = true;
        $user->save();

        return redirect()->route('contraseña')->with('success', 'Contraseña actualizada correctamente.');
    }

    /* ── Resource methods (admin) ── */

    public function index()
    {
        $usuarios = User::with('tiposUsuario')->paginate(20);
        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function show($id)
    {
        $usuario = User::with('tiposUsuario', 'direcciones')->findOrFail($id);
        return view('admin.usuarios.show', compact('usuario'));
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        $tipos = Tipos_usuario::all();
        return view('admin.usuarios.edit', compact('usuario', 'tipos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombres'        => 'required|string|max:100',
            'apellidos'      => 'required|string|max:100',
            'telefono'       => 'nullable|string|max:20',
            'id_tipo_usuario' => 'required|exists:tipos_usuarios,id_tipo_usuario',
        ]);

        $usuario = User::findOrFail($id);
        $usuario->update($request->only('nombres', 'apellidos', 'telefono', 'id_tipo_usuario'));

        return redirect()->route('usuarios.show', $id)->with('success', 'Usuario actualizado.');
    }

    public function destroy($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->update(['estatus' => 0]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario desactivado.');
    }

    public function toggleStatus($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->estatus = $usuario->estatus ? 0 : 1;
        $usuario->save();

        return back()->with('success', 'Estatus actualizado.');
    }
}
