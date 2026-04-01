<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\ItemIntencionCompra;
use Illuminate\Http\Request;

/**
 * CarritoApiController — Carrito de compras para la app móvil
 */
class CarritoApiController extends Controller
{
    /** GET /api/carrito */
    public function index(Request $request)
    {
        $carrito = $this->obtenerOCrearCarrito($request->user()->id);

        $carrito->load(['itemsIntencionCompra.item.imagenes:id_imagen,id_item,nombre,ruta']);

        return response()->json([
            'id_carrito' => $carrito->id_carrito,
            'items'      => $carrito->itemsIntencionCompra,
            'total'      => $carrito->itemsIntencionCompra
                ->where('es_seleccionado', 1)
                ->sum(fn($i) => ($i->item->valor ?? 0) * $i->cantidad - ($i->descuento ?? 0)),
        ]);
    }

    /** POST /api/carrito/agregar */
    public function agregar(Request $request)
    {
        $data = $request->validate([
            'id_item'  => 'required|integer|exists:items,id_item',
            'cantidad' => 'required|integer|min:1|max:99',
        ]);

        $carrito = $this->obtenerOCrearCarrito($request->user()->id);

        $existente = ItemIntencionCompra::where('id_carrito', $carrito->id_carrito)
            ->where('id_item', $data['id_item'])
            ->first();

        if ($existente) {
            $existente->increment('cantidad', $data['cantidad']);
        } else {
            ItemIntencionCompra::create([
                'id_carrito'    => $carrito->id_carrito,
                'id_item'       => $data['id_item'],
                'cantidad'      => $data['cantidad'],
                'es_seleccionado' => 1,
                'descuento'     => 0,
            ]);
        }

        return response()->json(['message' => 'Item agregado al carrito.']);
    }

    /** DELETE /api/carrito/{id_item} */
    public function eliminar(Request $request, int $idItem)
    {
        $carrito = $this->obtenerOCrearCarrito($request->user()->id);

        ItemIntencionCompra::where('id_carrito', $carrito->id_carrito)
            ->where('id_item', $idItem)
            ->delete();

        return response()->json(['message' => 'Item eliminado del carrito.']);
    }

    private function obtenerOCrearCarrito(int $userId): Carrito
    {
        return Carrito::firstOrCreate(['id_user' => $userId]);
    }
}
