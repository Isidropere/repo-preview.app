<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\ConfigTarifaCategoria29;
use App\Models\Item;
use App\Models\ItemIntencionCompra;
use App\Models\PredefinedMessage;
use App\Models\Paquete;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================
 * CarritoService — Lógica de negocio del carrito de compras
 * ============================================================
 *
 * Gestiona: obtener carrito con totales, agregar/eliminar items,
 * vaciar carrito, preparar checkout, actualizar cantidades
 * (con validación de stock) y marcar items como seleccionados.
 *
 * Los totales se calculan solo sobre items seleccionados:
 *   total_articulos = Σ(valor × cantidad)
 *   total_descuento = Σ(descuento × cantidad)
 *   total_estimado  = total_articulos − total_descuento
 * ============================================================
 */
class CarritoService
{
    /**
     * Obtiene el carrito del usuario con items, totales y datos auxiliares para la vista.
     */
    public function obtenerCarritoConTotales(int $userId): array
    {
        $carrito = Carrito::where('id_user', $userId)
            ->with([
                'itemsIntencionCompra.item.categoria',
                'itemsIntencionCompra.imagenes',
                'itemsIntencionCompra.inventario',
            ])->first();

        if (!$carrito) {
            return ['success' => false, 'message' => 'No tienes un carrito creado.'];
        }

        $items = $carrito->itemsIntencionCompra;
        $totales = $this->calcularTotales($items);

        return [
            'success' => true,
            'data' => [
                'carrito'              => $carrito,
                'totales'              => $totales,
                'mensajesPredefinidos' => PredefinedMessage::all(),
                'accion'               => PredefinedMessage::select('tipo')->distinct()->get(),
                'todoLosPaquetes'      => Paquete::where('id_user', $userId)->get(),
            ],
        ];
    }

    /**
     * Agrega o actualiza un item en el carrito.
     */
    public function agregarItem(int $userId, int $idItem, int $cantidad): array
    {
        $carrito = Carrito::firstOrCreate(['id_user' => $userId]);
        $itemBase = Item::findOrFail($idItem);

        // No permitir auto-compra
        if ($itemBase->id_user === $userId) {
            return ['success' => false, 'message' => 'No puedes comprar tu propio artículo.'];
        }

        // No permitir mezclar talentos (cat 29) con productos físicos en el mismo carrito
        $itemsExistentes = $carrito->itemsIntencionCompra()->with('item')->get();
        if ($itemsExistentes->isNotEmpty()) {
            $esNuevoTalento = (int) $itemBase->id_categoria_item === 29;
            $tieneProductos = $itemsExistentes->contains(fn($i) => (int) $i->item?->id_categoria_item !== 29);
            $tieneTalentos = $itemsExistentes->contains(fn($i) => (int) $i->item?->id_categoria_item === 29);

            if ($esNuevoTalento && $tieneProductos) {
                return ['success' => false, 'message' => 'No puedes mezclar servicios y productos físicos en el mismo carrito.'];
            }
            if (!$esNuevoTalento && $tieneTalentos) {
                return ['success' => false, 'message' => 'No puedes mezclar productos físicos y servicios en el mismo carrito.'];
            }
        }

        // Validar stock disponible
        $stockDisponible = $itemBase->inventarios?->cantidad ?? 0;
        if ($stockDisponible <= 0) {
            return ['success' => false, 'message' => 'Este artículo está agotado.'];
        }
        if ($cantidad > $stockDisponible) {
            return ['success' => false, 'message' => "Stock insuficiente. Disponible: {$stockDisponible}"];
        }

        // Calcular descuento por volumen para categoría 29 tipo venta
        $descuento = $itemBase->descuento ?? 0;
        if ((int) $itemBase->id_categoria_item === 29 && (int) $itemBase->tipo_trans === 1) {
            $config = ConfigTarifaCategoria29::vigente();
            if ($config->descuento_venta_masiva > 0 && $cantidad >= $config->cantidad_minima_descuento) {
                $descuento = round($itemBase->valor * ($config->descuento_venta_masiva / 100), 2);
            } else {
                $descuento = 0;
            }
        }

        $carrito->itemsIntencionCompra()->updateOrCreate(
            ['id_item' => $idItem],
            [
                'cantidad'         => $cantidad,
                'es_seleccionado'  => 1,
                'descuento'        => $descuento,
            ]
        );

        return [
            'success'    => true,
            'message'    => 'Item agregado al carrito',
            'cart_count' => $carrito->itemsIntencionCompra()->count(),
        ];
    }

    /**
     * Elimina un item del carrito.
     */
    public function eliminarItem(int $userId, int $idItem): array
    {
        $carrito = Carrito::where('id_user', $userId)->firstOrFail();

        $carrito->itemsIntencionCompra()
            ->where('id_item', $idItem)
            ->delete();

        return ['success' => true, 'message' => 'Item eliminado del carrito'];
    }

    /**
     * Vacía todos los items del carrito.
     */
    public function vaciar(int $userId): array
    {
        $carrito = Carrito::where('id_user', $userId)->firstOrFail();
        $carrito->itemsIntencionCompra()->delete();

        return ['success' => true, 'message' => 'Carrito vaciado'];
    }

    /**
     * Prepara datos para la vista de checkout.
     */
    public function prepararCheckout(int $userId): array
    {
        $carrito = Carrito::with(['itemsIntencionCompra.item', 'itemsIntencionCompra.imagenes'])
            ->where('id_user', $userId)
            ->firstOrFail();

        $carrito->itemsIntencionCompra = $carrito->itemsIntencionCompra
            ->filter(fn($item) => $item->es_seleccionado);

        $subtotal = $carrito->itemsIntencionCompra->sum(
            fn($item) => ($item->item->valor ?? 0) * ($item->cantidad ?? 0)
        );
        $descuento = $carrito->itemsIntencionCompra->sum(
            fn($item) => ($item->descuento ?? 0) * ($item->cantidad ?? 0)
        );
        $total = round($subtotal - $descuento, 2);

        return [
            'success' => true,
            'data'    => [
                'carrito' => $carrito,
                'total'   => $total,
            ],
        ];
    }

    /**
     * Incrementa o decrementa la cantidad de un item.
     */
    public function actualizarCantidad(int $itemIntencionId, string $accion): array
    {
        $item = ItemIntencionCompra::findOrFail($itemIntencionId);
        $stockDisponible = $item->item->inventarios?->cantidad ?? 0;

        if ($accion === 'incrementar') {
            if ($stockDisponible <= 0) {
                return ['success' => false, 'message' => 'No existencia en inventario'];
            }
            if ($item->cantidad >= $stockDisponible) {
                return ['success' => false, 'message' => 'Stock insuficiente para este producto'];
            }
            $item->cantidad++;
        } elseif ($accion === 'decrementar' && $item->cantidad > 1) {
            $item->cantidad--;
        }

        $item->save();
        return ['success' => true, 'message' => 'Cantidad actualizada'];
    }

    /**
     * Marca/desmarca un item como seleccionado y recalcula totales.
     * Solo permite modificar items que pertenecen al carrito del usuario (previene IDOR).
     */
    public function marcarSeleccionado(int $userId, int $itemIntencionId, bool $estado): array
    {
        $carrito = Carrito::where('id_user', $userId)->firstOrFail();

        $updated = ItemIntencionCompra::where('id_item_intencion_compra', $itemIntencionId)
            ->where('id_carrito', $carrito->id_carrito)
            ->update(['es_seleccionado' => $estado]);

        if (!$updated) {
            return ['success' => false, 'message' => 'Item no encontrado en tu carrito.'];
        }

        $carrito = Carrito::where('id_user', $userId)
            ->with('itemsIntencionCompra.item')
            ->firstOrFail();

        $totales = $this->calcularTotales($carrito->itemsIntencionCompra);

        return ['success' => true, 'data' => ['totales' => $totales]];
    }

    // ───────────────────────────────────────────────────────
    // Helpers privados
    // ───────────────────────────────────────────────────────

    private function calcularTotales($items): array
    {
        $seleccionados = $items->where('es_seleccionado', true);

        $totalArticulos = $seleccionados->sum(
            fn($i) => ($i->item->valor ?? 0) * ($i->cantidad ?? 0)
        );
        $totalDescuento = $seleccionados->sum(
            fn($i) => ($i->descuento ?? 0) * ($i->cantidad ?? 0)
        );

        return [
            'total_articulos' => round($totalArticulos, 2),
            'total_descuento' => round($totalDescuento, 2),
            'total_estimado'  => round($totalArticulos - $totalDescuento, 2),
        ];
    }
}
