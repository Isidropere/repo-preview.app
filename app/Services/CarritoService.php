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
        // Obtener ambos carritos del usuario
        $carritos = Carrito::where('id_user', $userId)
            ->with([
                'itemsIntencionCompra.item.categoria',
                'itemsIntencionCompra.item.usuario.direcciones' => fn($q) => $q->where('es_predeterminada', 1)->with(['municipio', 'provincia']),
                'itemsIntencionCompra.imagenes',
                'itemsIntencionCompra.inventario',
                'itemsIntencionCompra.color',
            ])->get();

        // Carrito principal: el de productos (o el primero que exista)
        $carrito = $carritos->firstWhere('tipo', 'producto') ?? $carritos->first();

        if (!$carrito) {
            return ['success' => false, 'message' => 'No tienes un carrito creado.'];
        }

        // Combinar items de todos los carritos para la vista
        $todosLosItems = $carritos->flatMap(fn($c) => $c->itemsIntencionCompra);

        // Enriquecer items de tipo servicio con información de solicitudes y proveedores
        foreach ($todosLosItems as $itemIntencion) {
            if ($itemIntencion->item && (int) $itemIntencion->item->id_categoria_item === 29) {
                $solicitudItem = \App\Models\SolicitudServicio::where('id_comprador', $userId)
                    ->where('id_item', $itemIntencion->id_item)
                    ->latest('fecha_creacion')
                    ->first();
                $itemIntencion->setAttribute('estado_solicitud', $solicitudItem?->estado ?? null);
                $itemIntencion->setAttribute('id_solicitud', $solicitudItem?->id_solicitud ?? null);

                // Obtener datos del proveedor
                $proveedor = $itemIntencion->item->usuario;
                if ($proveedor) {
                    $dirProv = $proveedor->direcciones->first(); // Ya está filtrado por es_predeterminada = 1
                    $itemIntencion->setAttribute('proveedor_info', [
                        'nombre'          => trim(($proveedor->nombres ?? '') . ' ' . ($proveedor->apellidos ?? '')),
                        'municipio'       => $dirProv?->municipio?->municipio ?? 'Ubicación no disponible',
                        'provincia'       => $dirProv?->municipio?->provincia?->provincia ?? '',
                        'calle'           => $dirProv?->calle ?? '',
                        'N_casa_edificio' => $dirProv?->N_casa_edificio ?? '',
                        'apto'            => $dirProv?->apto ?? '',
                        'geolocalizacion' => $dirProv?->geolocalizacion ?? '',
                    ]);
                } else {
                    $itemIntencion->setAttribute('proveedor_info', null);
                }
            }
        }

        $totales = $this->calcularTotales($todosLosItems);

        return [
            'success' => true,
            'data' => [
                'carrito'              => $carrito,
                'carritos'             => $carritos,
                'todosLosItems'        => $todosLosItems,
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
    public function agregarItem(int $userId, int $idItem, int $cantidad, ?int $idColor = null): array
    {
        $itemBase = Item::findOrFail($idItem);

        // No permitir auto-compra
        if ($itemBase->id_user === $userId) {
            return ['success' => false, 'message' => 'No puedes comprar tu propio artículo.'];
        }

        // Determinar tipo de carrito según categoría del item
        $tipoCarrito = (int) $itemBase->id_categoria_item === 29 ? 'servicio' : 'producto';

        // Obtener o crear el carrito del tipo correcto
        $carrito = Carrito::firstOrCreate(
            ['id_user' => $userId, 'tipo' => $tipoCarrito]
        );

        // Validar stock disponible (excepto para servicios que no tienen inventario)
        if ($tipoCarrito !== 'servicio') {
            // Si tiene color, validar stock por color
            if ($idColor) {
                $colorPivot = $itemBase->colors()->where('colors.id_color', $idColor)->first();
                $stockDisponible = $colorPivot ? $colorPivot->pivot->stock : 0;
            } else {
                $stockDisponible = $itemBase->inventarios?->cantidad ?? 0;
            }

            if ($stockDisponible <= 0) {
                return ['success' => false, 'message' => 'Este artículo está agotado' . ($idColor ? ' en el color seleccionado.' : '.')];
            }
            if ($cantidad > $stockDisponible) {
                return ['success' => false, 'message' => "Stock insuficiente. Disponible: {$stockDisponible}"];
            }
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
            ['id_item' => $idItem, 'id_color' => $idColor],
            [
                'cantidad'         => $cantidad,
                'es_seleccionado'  => 1,
                'descuento'        => $descuento,
            ]
        );

        // Contar total de items en ambos carritos del usuario
        $totalItems = ItemIntencionCompra::whereHas('carrito', fn($q) => $q->where('id_user', $userId))
            ->count();

        return [
            'success'    => true,
            'message'    => 'Item agregado al carrito',
            'cart_count' => $totalItems,
        ];
    }

    /**
     * Elimina un item del carrito.
     */
    public function eliminarItem(int $userId, int $idItem): array
    {
        // Buscar en todos los carritos del usuario
        $carritos = Carrito::where('id_user', $userId)->get();

        foreach ($carritos as $carrito) {
            $deleted = $carrito->itemsIntencionCompra()
                ->where('id_item', $idItem)
                ->delete();
            if ($deleted) break;
        }

        return ['success' => true, 'message' => 'Item eliminado del carrito'];
    }

    /**
     * Vacía todos los items del carrito.
     */
    public function vaciar(int $userId): array
    {
        $carritos = Carrito::where('id_user', $userId)->get();
        foreach ($carritos as $carrito) {
            $carrito->itemsIntencionCompra()->delete();
        }

        return ['success' => true, 'message' => 'Carrito vaciado'];
    }

    /**
     * Prepara datos para la vista de checkout.
     */
    public function prepararCheckout(int $userId, ?string $tipo = null): array
    {
        $query = Carrito::with(['itemsIntencionCompra.item', 'itemsIntencionCompra.imagenes'])
            ->where('id_user', $userId);

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        $carrito = $query->firstOrFail();

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

        $isServicio = (int) $item->item->id_categoria_item === 29;
        $stockDisponible = 0;

        if (!$isServicio) {
            if ($item->id_color) {
                $colorPivot = $item->item->colors()->where('colors.id_color', $item->id_color)->first();
                $stockDisponible = $colorPivot ? $colorPivot->pivot->stock : 0;
            } else {
                $stockDisponible = $item->item->inventarios?->cantidad ?? 0;
            }
        }

        $message = 'Cantidad actualizada';
        if ($accion === 'incrementar') {
            if (!$isServicio) {
                if ($stockDisponible <= 0) {
                    return ['success' => false, 'message' => 'No existencia en inventario'];
                }
                if ($item->cantidad >= $stockDisponible) {
                    return ['success' => false, 'message' => 'Stock insuficiente para este producto'];
                }
            }
            $item->cantidad++;
            $message = 'Artículo agregado';
        } elseif ($accion === 'decrementar' && $item->cantidad > 1) {
            $item->cantidad--;
            $message = 'Cantidad disminuida';
        }

        $item->save();
        return ['success' => true, 'message' => $message];
    }

    /**
     * Marca/desmarca un item como seleccionado y recalcula totales.
     * Solo permite modificar items que pertenecen al carrito del usuario (previene IDOR).
     */
    public function marcarSeleccionado(int $userId, int $itemIntencionId, bool $estado): array
    {
        // Buscar en TODOS los carritos del usuario (producto y servicio)
        $carritos = Carrito::where('id_user', $userId)->pluck('id_carrito');

        $updated = ItemIntencionCompra::where('id_item_intencion_compra', $itemIntencionId)
            ->whereIn('id_carrito', $carritos)
            ->update(['es_seleccionado' => $estado]);

        if (!$updated) {
            return ['success' => false, 'message' => 'Item no encontrado en tu carrito.'];
        }

        // Recalcular totales con items de TODOS los carritos
        $todosLosItems = ItemIntencionCompra::whereIn('id_carrito', $carritos)
            ->with('item')
            ->get();

        $totales = $this->calcularTotales($todosLosItems);

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
