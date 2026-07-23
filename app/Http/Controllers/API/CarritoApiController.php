<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\ItemIntencionCompra;
use App\Services\CarritoService;
use Illuminate\Http\Request;

/**
 * CarritoApiController — Carrito de compras para la app móvil
 */
class CarritoApiController extends Controller
{
    private CarritoService $carritoService;

    public function __construct(CarritoService $carritoService)
    {
        $this->carritoService = $carritoService;
    }

    /** GET /api/carrito */
    public function index(Request $request)
    {
        $resultado = $this->carritoService->obtenerCarritoConTotales($request->user()->id);

        if (!$resultado['success']) {
            return response()->json([
                'success' => false,
                'message' => $resultado['message']
            ], 404);
        }

        // Adjuntar image_url para el app móvil
        if (isset($resultado['data']['todosLosItems'])) {
            foreach ($resultado['data']['todosLosItems'] as $intencion) {
                if ($intencion->item && $intencion->imagenes && count($intencion->imagenes) > 0) {
                    $img = $intencion->imagenes[0];
                    $ruta = trim($img->ruta ?? 'imgs/articulos/items', '/');
                    $nombre = $img->nombre;
                    // Mismas reglas de fallback sin file_exists estricto para storage
                    $url = file_exists(public_path("{$ruta}/{$nombre}"))
                        ? url("{$ruta}/{$nombre}")
                        : url("storage/{$ruta}/{$nombre}");
                    $intencion->item->setAttribute('image_url', $url);
                }
            }
        }
        // Adjuntar municipioDefault
        $resultado['data']['municipioDefault'] = $request->user()
            ->direcciones()
            ->with('municipio')
            ->where('es_predeterminada', 1)
            ->first()?->municipio?->municipio ?? '';

        // Return the full structured data for the mobile app
        return response()->json($resultado['data']);
    }

    /** POST /api/carrito/agregar */
    public function agregar(Request $request)
    {
        $data = $request->validate([
            'id_item'  => 'required|integer|exists:items,id_item',
            'cantidad' => 'required|integer|min:1|max:99',
        ]);

        $resultado = $this->carritoService->agregarItem(
            $request->user()->id,
            $data['id_item'],
            $data['cantidad'],
            null // idColor, not currently sent by mobile
        );

        if (!$resultado['success']) {
            return response()->json(['message' => $resultado['message']], 400);
        }

        return response()->json(['message' => $resultado['message'], 'cart_count' => $resultado['cart_count']]);
    }

    /** DELETE /api/carrito/{id_item} */
    public function eliminar(Request $request, int $idItem)
    {
        $resultado = $this->carritoService->eliminarItem($request->user()->id, $idItem);
        return response()->json(['message' => $resultado['message']]);
    }

    /** DELETE /api/carrito/vaciar */
    public function vaciar(Request $request)
    {
        $resultado = $this->carritoService->vaciar($request->user()->id);
        return response()->json(['message' => $resultado['message']]);
    }

    /** PUT /api/carrito/{itemIntencionId}/cantidad */
    public function actualizarCantidad(Request $request, int $itemIntencionId)
    {
        $data = $request->validate([
            'accion' => 'required|string|in:incrementar,decrementar',
        ]);

        $resultado = $this->carritoService->actualizarCantidad($itemIntencionId, $data['accion']);

        if (!$resultado['success']) {
            return response()->json(['message' => $resultado['message']], 400);
        }

        return response()->json(['message' => $resultado['message']]);
    }

    /** PUT /api/carrito/{itemIntencionId}/seleccion */
    public function marcarSeleccionado(Request $request, int $itemIntencionId)
    {
        $data = $request->validate([
            'estado' => 'required|boolean',
        ]);

        $resultado = $this->carritoService->marcarSeleccionado($request->user()->id, $itemIntencionId, $data['estado']);

        if (!$resultado['success']) {
            return response()->json(['message' => $resultado['message']], 400);
        }

        return response()->json(['message' => 'Selección actualizada', 'totales' => $resultado['data']['totales']]);
    }
}
