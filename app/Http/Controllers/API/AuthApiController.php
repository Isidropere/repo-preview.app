<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

/**
 * AuthApiController — Autenticación para la app móvil
 * Usa Laravel Sanctum (tokens)
 */
class AuthApiController extends Controller
{
    /** POST /api/auth/login */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas.'],
            ]);
        }

        if (!$user->estatus) {
            return response()->json(['message' => 'Cuenta desactivada.'], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        // Verificar inventario para enviar notificaciones (igual que en web)
        $this->verificarInventarioUsuario($user);

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    /**
     * Verifica inventario de items del usuario y envía notificaciones si hay stock bajo o agotado.
     */
    private function verificarInventarioUsuario(User $user): void
    {
        try {
            $items = \App\Models\Item::where('id_user', $user->id)
                ->where('estatus', 1)
                ->with('inventarios')
                ->get();

            if ($items->isEmpty()) return;

            $agotados = [];
            $stockBajo = [];

            foreach ($items as $item) {
                $cantidad = $item->inventarios?->cantidad ?? 0;
                $tipo = (int) $item->id_categoria_item === 29 ? 'servicio' : 'producto';

                if ($cantidad <= 0) {
                    $agotados[] = $item->item . " ({$tipo})";
                } elseif ($cantidad === 1) {
                    $stockBajo[] = $item->item . " ({$tipo}, queda 1)";
                }
            }

            // Notificar stock agotado
            if (!empty($agotados)) {
                $msg = '[Producto] Stock agotado: ' . implode(', ', array_slice($agotados, 0, 5));
                if (count($agotados) > 5) $msg .= ' y ' . (count($agotados) - 5) . ' mas';
                $this->enviarNotificacionInventario($user->id, $msg);
            }

            // Notificar stock bajo
            if (!empty($stockBajo)) {
                $msg = '[Producto] Stock bajo: ' . implode(', ', array_slice($stockBajo, 0, 5));
                if (count($stockBajo) > 5) $msg .= ' y ' . (count($stockBajo) - 5) . ' mas';
                $this->enviarNotificacionInventario($user->id, $msg);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error verificando inventario al login API', ['error' => $e->getMessage()]);
        }
    }

    private function enviarNotificacionInventario(int $userId, string $mensaje): void
    {
        $existe = \App\Models\Message::whereNull('id_emisor')
            ->where('id_receptor', $userId)
            ->where('mensaje', $mensaje)
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();

        if ($existe) return;

        \App\Models\Message::create([
            'id_emisor'   => null,
            'id_receptor' => $userId,
            'mensaje'     => $mensaje,
            'leido'       => false,
        ]);
    }

    /** POST /api/auth/register */
    public function register(Request $request)
    {
        $data = $request->validate([
            'nombres'          => 'required|string|max:100',
            'apellidos'        => 'required|string|max:100',
            'telefono'         => 'required|string|max:14',
            'email'            => 'required|email|unique:users',
            'password'         => 'required|string|min:8|confirmed',
            'tipos_usuario_id' => 'required|integer|exists:tipos_usuarios,id_tipo_usuario|not_in:3,4',
        ]);

        $base     = strtolower(preg_replace('/\s+/', '', $data['nombres'] . $data['apellidos']));
        $username = User::where('nombre_usuario', $base)->exists() ? $base . rand(100, 999) : $base;

        $user = User::create([
            'nombres'         => $data['nombres'],
            'apellidos'       => $data['apellidos'],
            'telefono'        => $data['telefono'],
            'email'           => $data['email'],
            'nombre_usuario'  => $username,
            'password'        => Hash::make($data['password']),
            'estatus'         => 1,
            'id_tipo_usuario' => $data['tipos_usuario_id'],
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        // Enviar notificación de bienvenida
        \App\Models\Message::create([
            'id_emisor'   => null,
            'id_receptor' => $user->id,
            'mensaje'     => '¡Bienvenido a Cambialo! Explora productos, servicios y talentos para intercambiar o comprar.',
            'leido'       => false,
        ]);

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ], 201);
    }

    /** POST /api/auth/logout */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada.']);
    }

    /** GET /api/auth/me */
    public function me(Request $request)
    {
        return response()->json($this->formatUser($request->user()));
    }

    /** POST /api/auth/cambiar-contrasena */
    public function cambiarContrasena(Request $request)
    {
        $user = $request->user();

        $rules = [
            'password' => 'required|string|min:8|confirmed',
        ];

        if ($user->password_defined) {
            $rules['password_actual'] = 'required|string';
        }

        $request->validate($rules, [
            'password_actual.required' => 'La contraseña actual es obligatoria.',
            'password.required'        => 'La nueva contraseña es obligatoria.',
            'password.min'             => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'       => 'Las contraseñas no coinciden.',
        ]);

        if ($user->password_defined) {
            if (!Hash::check($request->password_actual, $user->password)) {
                return response()->json(['message' => 'La contraseña actual es incorrecta.'], 422);
            }
        }

        $user->password = Hash::make($request->password);
        $user->password_defined = true;
        $user->save();

        // Revocar todos los tokens para forzar re-login (seguridad)
        $user->tokens()->delete();
        $newToken = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
            'token'   => $newToken,
        ]);
    }

    /** POST /api/auth/profile */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'nombres'           => 'sometimes|string|max:100',
            'apellidos'         => 'sometimes|string|max:100',
            'telefono'          => 'nullable|string|max:20',
            'nombre_usuario'    => 'nullable|string|max:50|unique:users,nombre_usuario,' . $user->id,
            'profile_photo'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'profile_photo_url' => 'nullable|url',
        ]);

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $resultado = \App\Helpers\ImageHelper::guardar($file, 'imgs/profiles', 'profile_', $user->id);
            $user->profile_photo_path = $resultado['path'];
            $user->foto_perfil_estado = 'pendiente';
        } elseif ($request->filled('profile_photo_url')) {
            $user->profile_photo_path = $request->profile_photo_url;
            $user->foto_perfil_estado = 'aprobado';
        }

        $fields = array_filter($request->only('nombres', 'apellidos', 'telefono', 'nombre_usuario'));
        if (!empty($fields)) {
            $user->update($fields);
        } else {
            $user->save();
        }

        return response()->json([
            'message' => 'Perfil actualizado correctamente.',
            'user'    => $this->formatUser($user),
        ]);
    }

    /** POST /api/auth/google */
    public function loginGoogle(Request $request)
    {
        $request->validate([
            'google_id' => 'required|string',
            'email'     => 'required|email',
            'nombres'   => 'required|string',
            'apellidos' => 'sometimes|string',
            'profile_photo_url' => 'nullable|string',
        ]);

        $isNewUser = false;
        // Buscar usuario existente por google_id o por email
        $user = User::where('google_id', $request->google_id)->first()
            ?? User::where('email', $request->email)->first();

        if ($user) {
            // Vincular el google_id si aún no lo tiene
            if (!$user->google_id) {
                $user->google_id = $request->google_id;
            }
            if ($request->profile_photo_url && !$user->profile_photo_path) {
                $user->profile_photo_path = $request->profile_photo_url;
            }
            $user->save();
        } else {
            $isNewUser = true;
            // Crear nuevo usuario
            $baseUsername = strtolower(preg_replace('/\s+/', '', $request->nombres));
            $username = User::where('nombre_usuario', $baseUsername)->exists() 
                ? $baseUsername . '_' . Str::random(4) 
                : $baseUsername;

            $user = User::create([
                'nombres'           => $request->nombres,
                'apellidos'         => $request->apellidos ?? '',
                'email'             => $request->email,
                'telefono'          => '',
                'google_id'         => $request->google_id,
                'nombre_usuario'    => $username,
                'password'          => Hash::make(Str::random(24)),
                'password_defined'  => false,
                'estatus'           => 1,
                'id_tipo_usuario'   => 1,
                'email_verified_at' => now(),
            ]);
        }

        if (!$user->estatus) {
            return response()->json(['message' => 'Cuenta desactivada.'], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        // Verificar inventario para enviar notificaciones (igual que en web)
        $this->verificarInventarioUsuario($user);

        if ($isNewUser) {
            \App\Models\Message::create([
                'id_emisor'   => null,
                'id_receptor' => $user->id,
                'mensaje'     => '¡Bienvenido a Cambialo! Explora productos, servicios y talentos para intercambiar o comprar.',
                'leido'       => false,
            ]);
        }

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    private function formatUser(User $user): array
    {
        $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($user->nombres . ' ' . $user->apellidos) . '&background=f58634&color=fff&size=128';
        return [
            'id'                => $user->id,
            'nombres'           => $user->nombres,
            'apellidos'         => $user->apellidos,
            'email'             => $user->email,
            'telefono'          => $user->telefono,
            'nombre_usuario'    => $user->nombre_usuario,
            'id_tipo_usuario'   => $user->id_tipo_usuario,
            'profile_photo_url' => $user->profile_photo_path
                ? (filter_var($user->profile_photo_path, FILTER_VALIDATE_URL) ? $user->profile_photo_path : url($user->profile_photo_path))
                : $avatarUrl,
            'password_defined'  => (bool)($user->password_defined ?? true),
        ];
    }

    /** GET /api/auth/badges */
    public function getBadges(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['cart' => 0, 'intercambios' => 0, 'notificaciones' => 0]);
        }

        // Notificaciones (no leídas)
        $notificacionesCount = \App\Models\Message::where('id_receptor', $user->id)
            ->where('leido', 0)
            ->count();

        // Carrito
        $cartCount = 0;
        $carritos = \App\Models\Carrito::with('itemsIntencionCompra')
            ->where('id_user', $user->id)
            ->get();
        if ($carritos->isNotEmpty()) {
            foreach ($carritos as $carrito) {
                if ($carrito->itemsIntencionCompra) {
                    $cartCount += $carrito->itemsIntencionCompra->where('es_seleccionado', 1)->count();
                }
            }
        }

        // Intercambios pendientes
        $intercambiosCount = \App\Models\Negociacion::where('estado', 'En negociacion')
            ->where(function ($q) use ($user) {
                $q->where('usuario_emisor_id', $user->id)
                  ->orWhere('usuario_receptor_id', $user->id);
            })->count();

        return response()->json([
            'cart' => $cartCount,
            'intercambios' => $intercambiosCount,
            'notificaciones' => $notificacionesCount,
        ]);
    }

    public function verificarCredencialesAdultos(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = auth()->user();

        // Verificar si el correo electrónico coincide con el del usuario autenticado
        if ($request->email !== $user->email) {
            return response()->json([
                'success' => false,
                'message' => 'El correo electrónico no coincide con tu sesión actual.'
            ], 422);
        }

        if (Hash::check($request->password, $user->password)) {
            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Contraseña incorrecta.'
        ], 422);
    }
}
