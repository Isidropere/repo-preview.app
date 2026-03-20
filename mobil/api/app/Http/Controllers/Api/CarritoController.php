<?php

/**
 * ============================================================
 * CarritoController — Gestión del carrito de compras (API móvil)
 * ============================================================
 * Maneja todas las operaciones del carrito del usuario.
 * Cada usuario tiene un único registro en la tabla `carritos`
 * y múltiples filas en `carrito_items`.
 *
 * Todas las rutas requieren autenticación (auth:sanctum).
 *
 * Rutas que usa:
 *   GET    /api/carrito                  → index()
 *   POST   /api/carrito                  → store()
 *   DELETE /api/carrito/vaciar           → vaciar()
 *   DELETE /api/carrito/{itemId}         → destroy()
 *   PUT    /api/carrito/{id}/cantidad    → updateCantidad()
 *   PUT    /api/carrito/{id}/seleccion   → toggleSeleccion()
 * ============================================================
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarritoController extends Controller
{
    /**
     * Ver el carrito del usuario autenticado.
     *
     * Devuelve los items del carrito con datos del producto
     * (nombre, precio, imagen) y el total de los items
     * que están marcados como seleccionados.
     *
     * @param  Request $request
     * @return JsonResponse  { items[], total }
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Paso 1: Buscar el carrito del usuario
        $carrito = DB::table('carritos as c')
            ->where('c.id_user', $userId)
            ->first();

        // Paso 2: Si no tiene carrito, devolver vacío
        if (! $carrito) {
            return response()->json(['items' => [], 'total' => 0]);
        }

        // Paso 3: Obtener los items del carrito con datos del producto e imagen
        $items = DB::table('carrito_items as ci')
            ->join('items as i', 'i.id', '=', 'ci.id_item')
            ->leftJoin('imagen_items as img', function ($join) {
                // Solo la primera imagen de cada producto
                $join->on('img.id_item', '=', 'i.id')
                     ->whereRaw('img.id = (SELECT MIN(id) FROM imagen_items WHERE id_item = i.id)');
            })
            ->where('ci.id_carrito', $carrito->id)
            ->select(
                'ci.id', 'ci.id_item', 'ci.cantidad', 'ci.seleccionado',
                'i.nombre', 'i.precio', 'i.tipo_publicacion',
                'img.imagen as imagen_principal'
            )
            ->get();

        // Paso 4: Calcular total solo de los items seleccionados
        $total = $items->where('seleccionado', 1)->sum(fn($i) => $i->precio * $i->cantidad);

        return response()->json(['items' => $items, 'total' => $total]);
    }

    /**
     * Agregar un item al carrito.
     *
     * Si el usuario no tiene carrito, lo crea automáticamente.
     * Si el item ya está en el carrito, suma la cantidad.
     * Si es nuevo, lo inserta con seleccionado=1 por defecto.
     *
     * @param  Request $request  id_item, cantidad
     * @return JsonResponse  { message }  HTTP 201
     */
    public function store(Request $request)
    {
        // Paso 1: Validar que el item exista y la cantidad sea válida
        $request->validate([
            'id_item'  => 'required|integer|exists:items,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $userId = $request->user()->id;

        // Paso 2: Buscar o crear el carrito del usuario
        $carrito = DB::table('carritos')->where('id_user', $userId)->first();
        if (! $carrito) {
            // Crear carrito si no existe
            $carritoId = DB::table('carritos')->insertGetId([
                'id_user'    => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $carritoId = $carrito->id;
        }

        // Paso 3: Verificar si el item ya está en el carrito
        $existing = DB::table('carrito_items')
            ->where('id_carrito', $carritoId)
            ->where('id_item', $request->id_item)
            ->first();

        if ($existing) {
            // Paso 4a: Si ya existe, sumar la cantidad
            DB::table('carrito_items')
                ->where('id', $existing->id)
                ->update([
                    'cantidad'   => $existing->cantidad + $request->cantidad,
                    'updated_at' => now(),
                ]);
        } else {
            // Paso 4b: Si es nuevo, insertar con seleccionado=1
            DB::table('carrito_items')->insert([
                'id_carrito'   => $carritoId,
                'id_item'      => $request->id_item,
                'cantidad'     => $request->cantidad,
                'seleccionado' => 1, // Seleccionado para pago por defecto
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        return response()->json(['message' => 'Item agregado al carrito.'], 201);
    }

    /**
     * Eliminar un item específico del carrito.
     *
     * Busca el carrito del usuario y elimina la fila
     * de carrito_items que corresponde al id_item.
     *
     * @param  Request $request
     * @param  int     $itemId  ID del item (no del carrito_item)
     * @return JsonResponse  { message }
     */
    public function destroy(Request $request, $itemId)
    {
        $userId  = $request->user()->id;

        // Paso 1: Obtener el carrito del usuario
        $carrito = DB::table('carritos')->where('id_user', $userId)->first();

        // Paso 2: Eliminar el item si el carrito existe
        if ($carrito) {
            DB::table('carrito_items')
                ->where('id_carrito', $carrito->id)
                ->where('id_item', $itemId)
                ->delete();
        }

        return response()->json(['message' => 'Item eliminado.']);
    }

    /**
     * Vaciar todo el carrito.
     *
     * Elimina todos los carrito_items del usuario.
     * El registro en `carritos` se mantiene para futuras compras.
     *
     * @param  Request $request
     * @return JsonResponse  { message }
     */
    public function vaciar(Request $request)
    {
        $userId  = $request->user()->id;
        $carrito = DB::table('carritos')->where('id_user', $userId)->first();

        // Eliminar todos los items si el carrito existe
        if ($carrito) {
            DB::table('carrito_items')->where('id_carrito', $carrito->id)->delete();
        }

        return response()->json(['message' => 'Carrito vaciado.']);
    }

    /**
     * Actualizar la cantidad de un item en el carrito.
     *
     * @param  Request $request  cantidad (mínimo 1)
     * @param  int     $id       ID del registro en carrito_items
     * @return JsonResponse  { message }
     */
    public function updateCantidad(Request $request, $id)
    {
        $request->validate(['cantidad' => 'required|integer|min:1']);

        DB::table('carrito_items')
            ->where('id', $id)
            ->update(['cantidad' => $request->cantidad, 'updated_at' => now()]);

        return response()->json(['message' => 'Cantidad actualizada.']);
    }

    /**
     * Marcar o desmarcar un item para incluirlo en el pago.
     *
     * Permite al usuario seleccionar qué items quiere pagar
     * sin tener que eliminarlos del carrito.
     *
     * @param  Request $request  seleccionado (boolean)
     * @param  int     $id       ID del registro en carrito_items
     * @return JsonResponse  { message }
     */
    public function toggleSeleccion(Request $request, $id)
    {
        $request->validate(['seleccionado' => 'required|boolean']);

        DB::table('carrito_items')
            ->where('id', $id)
            ->update(['seleccionado' => $request->seleccionado, 'updated_at' => now()]);

        return response()->json(['message' => 'Selección actualizada.']);
    }
}
