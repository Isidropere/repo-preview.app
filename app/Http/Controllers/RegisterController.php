<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tipos_usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function showRegistroForm()
    {
        $tipos_usuarios = Tipos_usuario::whereNotIn('id_tipo_usuario', [3, 4])->get();
        return view('registro', compact('tipos_usuarios'));
    }

    public function registrarUsuario(Request $request)
    {
        Log::info('registrarUsuario: inicio', [
            'ip' => $request->ip(),
            'data' => $request->except(['password', 'password_confirmation', 'profile_photo']),
        ]);

        $validado = $request->validate([
            'nombres'          => 'required|string|max:255',
            'apellidos'        => 'required|string|max:255',
            'telefono'         => 'required|string|max:14',
            'email'            => 'required|email|unique:users',
            'password'         => 'required|string|min:8|confirmed',
            'tipos_usuario_id' => 'required|integer|exists:tipos_usuarios,id_tipo_usuario|not_in:3',
            'profile_photo'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            $nombre_usuario_base = strtolower(
                preg_replace('/\s+/', '', $validado['nombres'] . $validado['apellidos'])
            );
            $nombre_usuario_generado = $nombre_usuario_base;
            if (User::where('nombre_usuario', $nombre_usuario_generado)->exists()) {
                $nombre_usuario_generado = $nombre_usuario_base . rand(100, 9999);
            }

            $usuario = User::create([
                'nombres'         => $validado['nombres'],
                'apellidos'       => $validado['apellidos'],
                'telefono'        => $validado['telefono'],
                'email'           => $validado['email'],
                'nombre_usuario'  => $nombre_usuario_generado,
                'password'        => Hash::make($validado['password']),
                'estatus'         => true,
                'id_tipo_usuario' => $validado['tipos_usuario_id'],
            ]);

            Log::info('Usuario registrado', ['id' => $usuario->id, 'email' => $usuario->email]);

            // Guardar foto de perfil (no bloquea el registro si falla)
            if ($request->hasFile('profile_photo')) {
                try {
                    $file = $request->file('profile_photo');
                    $directory = public_path('imgs/profiles');
                    if (!file_exists($directory)) {
                        mkdir($directory, 0755, true);
                    }
                    $fileName = 'profile_' . $usuario->id . '_' . time() . '.' . $file->extension();
                    $file->move($directory, $fileName);
                    $usuario->profile_photo_path = 'imgs/profiles/' . $fileName;
                    $usuario->save();
                } catch (\Throwable $e) {
                    Log::error('Error al guardar foto de perfil', ['error' => $e->getMessage()]);
                }
            }

            // Email de verificacion (no bloquea el registro si falla)
            try {
                $usuario->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                Log::warning('No se pudo enviar email de verificacion', ['error' => $e->getMessage()]);
            }

            // Auto-login
            Auth::login($usuario);

            return redirect()->route('verification.notice')->with('success', '¡Cuenta creada! Revisa tu correo para verificar tu cuenta.');

        } catch (\Throwable $e) {
            Log::error('Error al registrar usuario', [
                'error' => $e->getMessage(),
                'email' => $validado['email'] ?? 'N/A',
            ]);

            return back()->withInput()->with('error', 'Error al crear la cuenta: ' . $e->getMessage());
        }
    }
}
