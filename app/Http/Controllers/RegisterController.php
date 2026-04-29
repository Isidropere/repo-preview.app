<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tipos_usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function showRegistroForm()
    {
        $tipos_usuarios = Tipos_usuario::whereNotIn('id_tipo_usuario', [3, 4])->get();
        return view('registro', compact('tipos_usuarios'));
    }

    /**
     * Paso 1: Validar datos y enviar código de verificación al email.
     * NO crea el usuario todavía.
     */
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
        ]);

        // Guardar foto en temp si existe
        $fotoTempPath = null;
        if ($request->hasFile('profile_photo') && $request->file('profile_photo')->isValid()) {
            try {
                $file = $request->file('profile_photo');
                $tmpName = 'reg_' . uniqid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tmpName;
                $contenido = file_get_contents($file->getRealPath());
                if ($contenido !== false) {
                    file_put_contents($tmpPath, $contenido);
                    $fotoTempPath = $tmpPath;
                }
            } catch (\Throwable $e) {
                Log::warning('No se pudo guardar foto temporal', ['error' => $e->getMessage()]);
            }
        }

        // Generar código de 6 dígitos
        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Guardar todo en sesión
        $request->session()->put('registro_pendiente', [
            'nombres'          => $validado['nombres'],
            'apellidos'        => $validado['apellidos'],
            'telefono'         => $validado['telefono'],
            'email'            => $validado['email'],
            'password'         => $validado['password'],
            'tipos_usuario_id' => $validado['tipos_usuario_id'],
            'foto_temp_path'   => $fotoTempPath,
            'codigo'           => $codigo,
            'codigo_expira'    => now()->addMinutes(10)->timestamp,
            'intentos'         => 0,
        ]);

        // Enviar código por email
        try {
            Mail::raw(
                "Tu código de verificación para Cambialord es: {$codigo}\n\nEste código expira en 10 minutos.",
                function ($message) use ($validado) {
                    $message->to($validado['email'])
                            ->subject('Código de verificación - Cambialord');
                }
            );
        } catch (\Throwable $e) {
            Log::error('Error al enviar código de verificación', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No se pudo enviar el código de verificación. Intenta de nuevo.');
        }

        return redirect()->route('registro.verificar.form');
    }

    /**
     * Mostrar formulario para ingresar el código
     */
    public function showVerificarForm(Request $request)
    {
        $datos = $request->session()->get('registro_pendiente');
        if (!$datos) {
            return redirect()->route('registro')->with('error', 'No hay un registro pendiente. Completa el formulario.');
        }

        $emailOculto = $this->ocultarEmail($datos['email']);
        return view('registro-verificar', compact('emailOculto'));
    }

    /**
     * Paso 2: Verificar código y crear la cuenta
     */
    public function verificarCodigo(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|size:6',
        ]);

        $datos = $request->session()->get('registro_pendiente');
        if (!$datos) {
            return redirect()->route('registro')->with('error', 'Sesión expirada. Completa el formulario nuevamente.');
        }

        // Verificar expiración
        if (now()->timestamp > $datos['codigo_expira']) {
            $request->session()->forget('registro_pendiente');
            return redirect()->route('registro')->with('error', 'El código ha expirado. Regístrate nuevamente.');
        }

        // Verificar intentos
        $datos['intentos'] = ($datos['intentos'] ?? 0) + 1;
        if ($datos['intentos'] > 5) {
            $request->session()->forget('registro_pendiente');
            return redirect()->route('registro')->with('error', 'Demasiados intentos fallidos. Regístrate nuevamente.');
        }
        $request->session()->put('registro_pendiente', $datos);

        // Verificar código
        if ($request->codigo !== $datos['codigo']) {
            return back()->with('error', 'Código incorrecto. Te quedan ' . (5 - $datos['intentos']) . ' intentos.');
        }

        // ── Código correcto: crear usuario ──
        try {
            $nombre_usuario_base = strtolower(
                preg_replace('/\s+/', '', $datos['nombres'] . $datos['apellidos'])
            );
            $nombre_usuario_generado = $nombre_usuario_base;
            if (User::where('nombre_usuario', $nombre_usuario_generado)->exists()) {
                $nombre_usuario_generado = $nombre_usuario_base . rand(100, 9999);
            }

            $usuario = User::create([
                'nombres'           => $datos['nombres'],
                'apellidos'         => $datos['apellidos'],
                'telefono'          => $datos['telefono'],
                'email'             => $datos['email'],
                'nombre_usuario'    => $nombre_usuario_generado,
                'password'          => Hash::make($datos['password']),
                'estatus'           => true,
                'id_tipo_usuario'   => $datos['tipos_usuario_id'],
                'email_verified_at' => now(), // Ya verificado con el código
            ]);

            Log::info('Usuario registrado (email verificado)', ['id' => $usuario->id, 'email' => $usuario->email]);

            // Guardar foto de perfil si existe
            if (!empty($datos['foto_temp_path']) && file_exists($datos['foto_temp_path'])) {
                try {
                    $tmpFile = new \Illuminate\Http\UploadedFile(
                        $datos['foto_temp_path'],
                        'profile_photo.jpg',
                        mime_content_type($datos['foto_temp_path']),
                        null,
                        true
                    );
                    $resultado = \App\Helpers\ImageHelper::guardar($tmpFile, 'imgs/profiles', 'profile_', $usuario->id);
                    $usuario->profile_photo_path = $resultado['path'];
                    $usuario->save();
                } catch (\Throwable $e) {
                    Log::error('Error al guardar foto de perfil', ['error' => $e->getMessage()]);
                }
            }

            // Limpiar sesión
            $request->session()->forget('registro_pendiente');

            // Auto-login
            Auth::login($usuario);

            return redirect()->route('home')->with('success', '¡Cuenta creada exitosamente! Tu correo ya está verificado.');

        } catch (\Throwable $e) {
            Log::error('Error al registrar usuario', [
                'error' => $e->getMessage(),
                'email' => $datos['email'] ?? 'N/A',
            ]);

            return back()->with('error', 'Error al crear la cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Reenviar código de verificación
     */
    public function reenviarCodigo(Request $request)
    {
        $datos = $request->session()->get('registro_pendiente');
        if (!$datos) {
            return redirect()->route('registro')->with('error', 'No hay un registro pendiente.');
        }

        // Nuevo código
        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $datos['codigo'] = $codigo;
        $datos['codigo_expira'] = now()->addMinutes(10)->timestamp;
        $datos['intentos'] = 0;
        $request->session()->put('registro_pendiente', $datos);

        try {
            Mail::raw(
                "Tu nuevo código de verificación para Cambialord es: {$codigo}\n\nEste código expira en 10 minutos.",
                function ($message) use ($datos) {
                    $message->to($datos['email'])
                            ->subject('Código de verificación - Cambialord');
                }
            );
        } catch (\Throwable $e) {
            Log::error('Error al reenviar código', ['error' => $e->getMessage()]);
            return back()->with('error', 'No se pudo reenviar el código.');
        }

        $emailOculto = $this->ocultarEmail($datos['email']);
        return back()->with('success', "Nuevo código enviado a {$emailOculto}");
    }

    /**
     * Ocultar parte del email: j***@gmail.com
     */
    private function ocultarEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';
        $visible = substr($name, 0, min(2, strlen($name)));
        return $visible . '***@' . $domain;
    }
}
