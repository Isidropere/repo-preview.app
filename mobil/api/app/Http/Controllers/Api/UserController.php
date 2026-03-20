<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * ============================================================
 * UserController — Perfil y productos del usuario (API móvil)
 * ============================================================
 * Gestiona el perfil del usuario autenticado: ver datos,
 * actualizar información personal, cambiar contraseña
 * y listar sus productos publicados.
 *
 * Todas las rutas requieren autenticación (auth:sanctum).
 *
 * Rutas que usa:
 *   GET  /api/profile           → profile()
 *   PUT  /api/profile           → updateProfile()
 *   PUT  /api/profile/password  → changePassword()
 *   GET  /api/mis-productos     → misProductos()
 * ============================================================
 */
class UserController extends Controller
{
    /**
     * Ver perfil completo del usuario autenticado.
     *
     * Devuelve los datos del usuario más sus direcciones
     * registradas (para mostrar en pantalla de perfil y checkout).
     *
     * @param  Request $request
     * @return JsonResponse  { user, direcciones[] }
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        // Obtener todas las direcciones del usuario
        $direcciones = DB::table('direcciones')
            ->where('id_user', $user->id)
            ->get();

        return response()->json([
            'user'        => $user,
            'direcciones' => $direcciones,
        ]);
    }

    /**
     * Actualizar datos del perfil.
     *
     * Usa 'sometimes' para que cada campo sea opcional —
     * el usuario puede actualizar solo lo que quiera.
     * array_filter() elimina los campos nulos para no
     * sobreescribir datos con vacíos.
     *
     * @param  Request $request  nombres?, apellidos?, telefono?, nombre_usuario?
     * @return JsonResponse  { message }
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        // Paso 1: Validar solo los campos que vengan en el request
        $request->validate([
            'nombres'        => 'sometimes|string|max:100',
            'apellidos'      => 'sometimes|string|max:100',
            'telefono'       => 'sometimes|string|max:20',
            // nombre_usuario único pero ignorando el propio usuario
            'nombre_usuario' => 'sometimes|string|max:50|unique:users,nombre_usuario,'.$user->id,
        ]);

        // Paso 2: Actualizar solo los campos que vinieron en el request
        DB::table('users')
            ->where('id', $user->id)
            ->update(array_filter($request->only(['nombres', 'apellidos', 'telefono', 'nombre_usuario'])));

        return response()->json(['message' => 'Perfil actualizado.']);
    }

    /**
     * Cambiar contraseña del usuario.
     *
     * Verifica la contraseña actual antes de permitir el cambio.
     * Requiere password_confirmation para confirmar la nueva.
     *
     * @param  Request $request  current_password, password, password_confirmation
     * @return JsonResponse  { message }  o 422 si la contraseña actual es incorrecta
     */
    public function changePassword(Request $request)
    {
        // Paso 1: Validar campos requeridos
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed', // Requiere password_confirmation
        ]);

        $user = $request->user();

        // Paso 2: Verificar que la contraseña actual sea correcta
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Contraseña actual incorrecta.'], 422);
        }

        // Paso 3: Actualizar con la nueva contraseña hasheada
        DB::table('users')
            ->where('id', $user->id)
            ->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Contraseña actualizada.']);
    }

    /**
     * Listar productos publicados por el usuario autenticado.
     *
     * Devuelve todos sus items (activos e inactivos) con
     * la primera imagen de cada uno para mostrar en "Mis Productos".
     *
     * @param  Request $request
     * @return JsonResponse  array de items
     */
    public function misProductos(Request $request)
    {
        $items = DB::table('items as i')
            ->leftJoin('imagen_items as img', function ($join) {
                // Solo la primera imagen de cada item
                $join->on('img.id_item', '=', 'i.id')
                     ->whereRaw('img.id = (SELECT MIN(id) FROM imagen_items WHERE id_item = i.id)');
            })
            ->where('i.id_user', $request->user()->id) // Solo los del usuario autenticado
            ->select('i.id', 'i.nombre', 'i.precio', 'i.estatus', 'i.tipo_publicacion', 'img.imagen as imagen_principal')
            ->orderByDesc('i.created_at') // Más recientes primero
            ->get();

        return response()->json($items);
    }
}

