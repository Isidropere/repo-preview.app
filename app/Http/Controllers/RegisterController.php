<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Tipos_usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function showRegistroForm()
    {
        $tipos_usuarios = Tipos_usuario::whereNotIn('id_tipo_usuario', [3, 4])->get();
        return view('registro', compact('tipos_usuarios'));
    }

    public function registrarUsuario(Request $request)
    {
        $validado = $request->validate([
            'nombres'          => 'required|string|max:255',
            'apellidos'        => 'required|string|max:255',
            'telefono'         => 'required|string|max:14',
            'email'            => 'required|email|unique:users',
            'password'         => 'required|string|min:8|confirmed',
            'tipos_usuario_id' => 'required|integer|exists:tipos_usuarios,id_tipo_usuario|not_in:3',
            'profile_photo'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $nombre_usuario_generado = strtolower(
            preg_replace('/\s+/', '', $validado['nombres'] . $validado['apellidos'])
        );

        try {
            $usuario = User::create([
                'nombres'         => $validado['nombres'],
                'apellidos'       => $validado['apellidos'],
                'telefono'        => $validado['telefono'],
                'email'           => $validado['email'],
                'nombre_usuario'  => $nombre_usuario_generado,
                'password'        => Hash::make($validado['password']),
                'estatus'         => true,
                'id_tipo_usuario' => $validado['tipos_usuario_id'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al crear usuario', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['email' => 'Error al crear la cuenta: ' . $e->getMessage()]);
        }

        // Guardar imagen localmente
        if ($request->hasFile('profile_photo')) {
            try {
                Log::info('Inicio de guardado local de imagen', ['user_id' => $usuario->id]);

                $file = $request->file('profile_photo');
                Log::debug('Archivo obtenido', [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ]);

                // Validar tipo de archivo
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                $mime = $file->getMimeType();

                if (!in_array($mime, $allowedMimeTypes)) {
                    Log::error('Tipo de archivo no permitido', [
                        'mime' => $mime,
                        'permitidos' => $allowedMimeTypes
                    ]);
                    throw new \Exception('Tipo de archivo no permitido: ' . $mime);
                }

                // Configurar ruta de almacenamiento
                $directory = public_path('imgs/profiles');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                $fileName = 'profile_' . $usuario->id . '_' . time() . '.' . $file->extension();
                $file->move($directory, $fileName);
                $usuario->profile_photo_path = 'imgs/profiles/' . $fileName;
                $usuario->save();

            } catch (\Throwable $e) {
                Log::error('Error al guardar imagen localmente', [
                    'user_id' => $usuario->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Continuar sin imagen
            }
        }

        $usuario->sendEmailVerificationNotification();

        // Auto-login después del registro
        Auth::login($usuario);

        return redirect()->route('home')->with('success', '¡Bienvenido! Tu cuenta ha sido creada exitosamente.');
    }
}
