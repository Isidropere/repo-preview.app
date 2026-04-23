<?php

namespace App\Http\Controllers;

use App\Models\ItemIntencionCompra;
use App\Models\Negociacion;
use App\Models\PagoCompra;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $compras = PagoCompra::whereHas('carrito', fn($q) => $q->where('id_user', $userId))
            ->with(['trazabilidad', 'tarjeta', 'pagoItems.item.imagenes', 'carrito.itemsIntencionCompra'])
            ->orderByDesc('fecha')
            ->get();

        $ventas = ItemIntencionCompra::whereHas('item', fn($q) => $q->where('id_user', $userId))
            ->with(['item.imagenes', 'carrito.pagosCompra'])
            ->orderByDesc('id_item_intencion_compra')
            ->get();

        $negociaciones = Negociacion::where('usuario_emisor_id', $userId)
            ->orWhere('usuario_receptor_id', $userId)
            ->with(['item.imagenes', 'usuario', 'usuarioReceptor'])
            ->orderByDesc('id_negociacion')
            ->get();

        return view('historial.historial', compact('compras', 'ventas', 'negociaciones'));
    }
}
