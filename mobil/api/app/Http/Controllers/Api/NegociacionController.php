<?php

/**
 * ============================================================
 * NegociacionController  Negociaciones/intercambios (API móvil)
 * ============================================================
 * Gestiona las propuestas de intercambio entre compradores
 * y vendedores. Un comprador propone un precio/intercambio
 * y el vendedor puede aceptar, rechazar o contraoferta.
 *
 * Estados posibles de una negociación:
 *   pendiente    recién creada, esperando respuesta
 *   aceptada     vendedor aceptó la oferta
 *   rechazada    vendedor rechazó la oferta
 *   contraoferta  vendedor propuso otro precio
 *
 * Todas las rutas requieren autenticación (auth:sanctum).
 *
 * Rutas que usa:
 *   GET  /api/negociaciones                     index()
 *   POST /api/negociaciones                     store()
 *   POST /api/negociaciones/{id}/aceptar        aceptar()
 *   POST /api/negociaciones/{id}/rechazar       rechazar()
 *   POST /api/negociaciones/{id}/contraoferta   contraoferta()
 * ============================================================
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NegociacionController extends Controller
{
    /**
     * Listar negociaciones del usuario autenticado.
     *
     * Devuelve tanto las negociaciones donde el usuario es
     * comprador como donde es vendedor, con datos del item
     * y nombres de ambas partes.
     *
     * @param  Request $request
     * @return JsonResponse  array de negociaciones
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $negociaciones = DB::table('negociaciones as n')
            ->join('items as i', 'i.id', '=', 'n.id_item')
            ->join('users as comprador', 'comprador.id', '=', 'n.id_comprador')
            ->join('users as vendedor', 'vendedor.id', '=', 'n.id_vendedor')
            // Traer negociaciones donde participo (como comprador o vendedor)
            ->where(function ($q) use ($userId) {
                $q->where('n.id_comprador', $userId)
                  ->orWhere('n.id_vendedor', $userId);
            })
            ->select(
                'n.*',
                'i.nombre as item_nombre', 'i.precio as item_precio',
                'comprador.nombre_usuario as comprador_nombre',
                'vendedor.nombre_usuario as vendedor_nombre'
            )
            ->orderByDesc('n.created_at') // Más recientes primero
            ->get();

        return response()->json($negociaciones);
    }

    /**
     * Crear una nueva negociación (propuesta de intercambio).
     *
     * El comprador propone un precio por el item del vendedor.
     * El id_vendedor se obtiene automáticamente del item,
     * no se necesita enviarlo en el request.
     *
     * @param  Request $request  id_item, precio_ofertado, mensaje?
     * @return JsonResponse  negociación creada  HTTP 201
     */
    public function store(Request $request)
    {
        // Paso 1: Validar los datos de la propuesta
        $request->validate([
            'id_item'         => 'required|integer|exists:items,id',
            'precio_ofertado' => 'required|numeric|min:0',
            'mensaje'         => 'nullable|string|max:500',
        ]);

        // Paso 2: Obtener el item para saber quién es el vendedor
        $item = DB::table('items')->where('id', $request->id_item)->first();

        if (! $item) {
            return response()->json(['message' => 'Item no encontrado.'], 404);
        }

        // Paso 3: Crear la negociación con estatus inicial 'pendiente'
        $id = DB::table('negociaciones')->insertGetId([
            'id_item'         => $request->id_item,
            'id_comprador'    => $request->user()->id, // El usuario autenticado es el comprador
            'id_vendedor'     => $item->id_user,       // El dueño del item es el vendedor
            'precio_ofertado' => $request->precio_ofertado,
            'mensaje'         => $request->mensaje,
            'estatus'         => 'pendiente',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Paso 4: Devolver la negociación recién creada
        return response()->json(DB::table('negociaciones')->where('id', $id)->first(), 201);
    }

    /**
     * Aceptar una negociación.
     *
     * Solo el vendedor puede aceptar. La condición
     * id_vendedor = usuario autenticado garantiza esto.
     *
     * @param  Request $request
     * @param  int     $id  ID de la negociación
     * @return JsonResponse  { message }
     */
    public function aceptar(Request $request, $id)
    {
        // Solo actualiza si el usuario autenticado es el vendedor
        DB::table('negociaciones')
            ->where('id', $id)
            ->where('id_vendedor', $request->user()->id)
            ->update(['estatus' => 'aceptada', 'updated_at' => now()]);

        return response()->json(['message' => 'Negociación aceptada.']);
    }

    /**
     * Rechazar una negociación.
     *
     * Solo el vendedor puede rechazar.
     *
     * @param  Request $request
     * @param  int     $id  ID de la negociación
     * @return JsonResponse  { message }
     */
    public function rechazar(Request $request, $id)
    {
        // Solo actualiza si el usuario autenticado es el vendedor
        DB::table('negociaciones')
            ->where('id', $id)
            ->where('id_vendedor', $request->user()->id)
            ->update(['estatus' => 'rechazada', 'updated_at' => now()]);

        return response()->json(['message' => 'Negociación rechazada.']);
    }

    /**
     * Enviar una contraoferta.
     *
     * El vendedor propone un precio diferente al ofertado.
     * Cambia el estatus a 'contraoferta' para que el comprador
     * pueda ver la nueva propuesta.
     *
     * @param  Request $request  precio_contraoferta, mensaje?
     * @param  int     $id  ID de la negociación
     * @return JsonResponse  { message }
     */
    public function contraoferta(Request $request, $id)
    {
        // Paso 1: Validar el precio de la contraoferta
        $request->validate([
            'precio_contraoferta' => 'required|numeric|min:0',
            'mensaje'             => 'nullable|string|max:500',
        ]);

        // Paso 2: Actualizar con el nuevo precio y cambiar estatus
        DB::table('negociaciones')
            ->where('id', $id)
            ->update([
                'precio_contraoferta'  => $request->precio_contraoferta,
                'mensaje_contraoferta' => $request->mensaje,
                'estatus'              => 'contraoferta', // Notifica al comprador
                'updated_at'           => now(),
            ]);

        return response()->json(['message' => 'Contraoferta enviada.']);
    }
}