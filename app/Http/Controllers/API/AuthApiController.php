<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
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
        $request->validate([
            'password_actual'       => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password_actual, $user->password)) {
            return response()->json(['message' => 'La contraseña actual es incorrecta.'], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

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
        ];
    }
}
