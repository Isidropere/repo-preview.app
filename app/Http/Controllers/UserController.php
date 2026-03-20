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
            'nombres'        => 'required|string|max:100',
            'apellidos'      => 'required|string|max:100',
            'telefono'       => 'nullable|string|max:20',
            'nombre_usuario' => 'nullable|string|max:50|unique:users,nombre_usuario,' . auth()->id(),
        ]);

        $user = auth()->user();
        $user->update($request->only('nombres', 'apellidos', 'telefono', 'nombre_usuario'));

        return redirect()->route('profile')->with('success', 'Perfil actualizado correctamente.');
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
