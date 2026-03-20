<?php

/**
 * ============================================================
 * AuthController — Autenticación de usuarios (API móvil)
 * ============================================================
 * Maneja el registro, login y logout de usuarios desde la app.
 * Usa Laravel Sanctum para emitir tokens Bearer.
 *
 * Rutas que usa:
 *   POST /api/login     → login()
 *   POST /api/register  → register()
 *   POST /api/logout    → logout()   [protegida]
 *   GET  /api/user      → user()     [protegida]
 * ============================================================
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Iniciar sesión.
     *
     * Valida email y contraseña. Si son correctos, genera un
     * token Sanctum con nombre 'mobile' y lo devuelve junto
     * con los datos básicos del usuario.
     *
     * @param  Request $request  email, password
     * @return JsonResponse      { token, user }
     */
    public function login(Request $request)
    {
        // Paso 1: Validar que vengan email y password
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Paso 2: Buscar el usuario por email
        $user = User::where('email', $request->email)->first();

        // Paso 3: Verificar que exista y que la contraseña coincida
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas.'],
            ]);
        }

        // Paso 4: Crear token Sanctum para la app móvil
        $token = $user->createToken('mobile')->plainTextToken;

        // Paso 5: Devolver token + datos del usuario
        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    /**
     * Registrar nuevo usuario.
     *
     * Crea la cuenta con estatus activo (1) y devuelve
     * un token listo para usar, igual que en login.
     *
     * @param  Request $request  nombres, apellidos, email, password, nombre_usuario, telefono, id_tipo_usuario
     * @return JsonResponse      { token, user }  HTTP 201
     */
    public function register(Request $request)
    {
        // Paso 1: Validar todos los campos requeridos
        $request->validate([
            'nombres'         => 'required|string|max:100',
            'apellidos'       => 'required|string|max:100',
            'email'           => 'required|email|unique:users,email',       // Email único
            'password'        => 'required|min:8|confirmed',                // Requiere password_confirmation
            'nombre_usuario'  => 'required|string|max:50|unique:users,nombre_usuario', // Username único
            'telefono'        => 'nullable|string|max:20',
            'id_tipo_usuario' => 'required|in:1,2',                         // 1=Comprador, 2=Vendedor
        ]);

        // Paso 2: Crear el usuario en la base de datos
        $user = User::create([
            'nombres'         => $request->nombres,
            'apellidos'       => $request->apellidos,
            'email'           => $request->email,
            'password'        => Hash::make($request->password), // Hashear contraseña
            'nombre_usuario'  => $request->nombre_usuario,
            'telefono'        => $request->telefono,
            'id_tipo_usuario' => $request->id_tipo_usuario,
            'estatus'         => 1, // Activo por defecto
        ]);

        // Paso 3: Generar token para que el usuario quede logueado inmediatamente
        $token = $user->createToken('mobile')->plainTextToken;

        // Paso 4: Devolver token + datos (HTTP 201 Created)
        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ], 201);
    }

    /**
     * Cerrar sesión.
     *
     * Revoca únicamente el token actual (no todos los tokens
     * del usuario). Requiere estar autenticado.
     *
     * @param  Request $request
     * @return JsonResponse  { message }
     */
    public function logout(Request $request)
    {
        // Eliminar solo el token con el que se hizo esta petición
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    /**
     * Obtener usuario autenticado.
     *
     * Devuelve los datos del usuario dueño del token Bearer.
     *
     * @param  Request $request
     * @return JsonResponse  datos del usuario
     */
    public function user(Request $request)
    {
        return response()->json($this->formatUser($request->user()));
    }

    /**
     * Formatear datos del usuario para la respuesta.
     *
     * Devuelve solo los campos necesarios para la app,
     * excluyendo password, tokens y datos internos.
     *
     * @param  User  $user
     * @return array
     */
    private function formatUser(User $user): array
    {
        return [
            'id'              => $user->id,
            'nombres'         => $user->nombres,
            'apellidos'       => $user->apellidos,
            'email'           => $user->email,
            'nombre_usuario'  => $user->nombre_usuario,
            'telefono'        => $user->telefono,
            'foto_perfil'     => $user->foto_perfil,
            'id_tipo_usuario' => $user->id_tipo_usuario,
        ];
    }
}
