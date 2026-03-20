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
        $tipos_usuarios = Tipos_usuario::where('id_tipo_usuario', '!=', 3)->get();
        return view('registro', compact('tipos_usuarios'));
    }

    public function registrarUsuario(Request $request)
    {
        $validado = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'telefono' => 'required|string|max:10',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'tipos_usuario_id' => 'required|integer|exists:tipos_usuarios,id_tipo_usuario|not_in:3,4',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
        $nombre_usuario_generado = strtolower(
            preg_replace('/\s+/', '', $validado['nombres'] . $validado['apellidos'])
        );


        $usuario = User::create([
            'nombres' => $validado['nombres'],
            'apellidos' => $validado['apellidos'],
            'telefono' => $validado['telefono'],
            'email' => $validado['email'],
            'nombre_usuario' => $validado['email'],  
            'isAdmin' => false,
            'password' => Hash::make($validado['password']),
            'estatus' => true,
            'tipos_usuario_id' => $validado['tipos_usuario_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
                $directory = 'userImg/img';
                $prefix = 'userImg';
                $fileName = $prefix . '_' . $usuario->id . '_' . now()->format('YmdHis') . '.' . $file->extension();
                Log::debug('Nombre de archivo generado', ['file_name' => $fileName]);

                // Crear directorio si no existe
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory, 0755, true);
                    Log::info('Directorio creado', ['path' => $directory]);
                }

                // Guardar archivo
                $path = Storage::disk('public')->putFileAs(
                    $directory,
                    $file,
                    $fileName
                );

                if (!$path) {
                    Log::error('Error al guardar archivo en almacenamiento local');
                    throw new \Exception('No se pudo guardar el archivo');
                }

                // Guardar ruta en base de datos
                $relativePath = $directory . '/' . $fileName;
                $usuario->profile_photo_path = $relativePath;
                $usuario->save();

                Log::info('Imagen guardada localmente con éxito', [
                    'user_id' => $usuario->id,
                    'path' => $relativePath
                ]);

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
        return redirect()->route('login')->with('success', 'Debes verificar tu correo electrónico antes de iniciar sesión.');
    }
}
