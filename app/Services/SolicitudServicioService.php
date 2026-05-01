<?php

namespace App\Services;

use App\Events\NuevaNotificacion;
use App\Models\Carrito;
use App\Models\ItemIntencionCompra;
use App\Models\Message;
use App\Models\SolicitudServicio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolicitudServicioService
{
    /**
     * Crea solicitudes para cada item seleccionado del carrito servicio.
     */
    public function crearDesdeCarrito(int $compradorId, Carrito $carrito): array
    {
        $items = $carrito->itemsIntencionCompra->where('es_seleccionado', true);

        if ($items->isEmpty()) {
            return ['success' => false, 'message' => 'No hay items seleccionados.'];
        }

        $creadas = 0;

        foreach ($items as $itemIntencion) {
            $item = $itemIntencion->item;

            // No permitir auto-compra
            if ($item->id_user === $compradorId) {
                continue;
            }

            // Verificar si ya existe solicitud pendiente para este comprador+item
            $existe = SolicitudServicio::where('id_comprador', $compradorId)
                ->where('id_item', $item->id_item)
                ->whereIn('estado', ['pendiente_aprobacion', 'aprobada'])
                ->exists();

            if ($existe) {
                continue;
            }

            $monto = ($item->valor * $itemIntencion->cantidad) - ($itemIntencion->descuento ?? 0);

            SolicitudServicio::create([
                'id_comprador'  => $compradorId,
                'id_proveedor'  => $item->id_user,
                'id_item'       => $item->id_item,
                'id_carrito'    => $carrito->id_carrito,
                'cantidad'      => $itemIntencion->cantidad,
                'monto_total'   => round($monto, 2),
                'estado'        => 'pendiente_aprobacion',
                'fecha_creacion' => now(),
            ]);

            $creadas++;

            // Notificar al proveedor
            $this->notificar(
                $item->id_user,
                "[Servicio] Nueva solicitud de servicio para \"{$item->item}\". Revisa en Mis Ventas de Talentos."
            );
        }

        if ($creadas === 0) {
            return ['success' => false, 'message' => 'Ya tienes solicitudes pendientes para estos servicios.'];
        }

        return [
            'success' => true,
            'message' => "Solicitud enviada. El proveedor debe aprobarla antes de que puedas pagar. ({$creadas} solicitud(es) creada(s))",
        ];
    }

    /**
     * Proveedor aprueba una solicitud.
     */
    public function aprobar(int $proveedorId, int $solicitudId): array
    {
        $solicitud = SolicitudServicio::find($solicitudId);

        if (!$solicitud) {
            return ['success' => false, 'message' => 'Solicitud no encontrada.'];
        }

        if ($solicitud->id_proveedor !== $proveedorId) {
            return ['success' => false, 'message' => 'No autorizado.', 'code' => 403];
        }

        if ($solicitud->estado !== 'pendiente_aprobacion') {
            return ['success' => false, 'message' => 'Esta solicitud ya fue procesada.'];
        }

        $solicitud->update([
            'estado' => 'aprobada',
            'fecha_actualizacion' => now(),
        ]);

        $itemNombre = $solicitud->item?->item ?? 'servicio';
        $this->notificar(
            $solicitud->id_comprador,
            "[Servicio] Tu solicitud para \"{$itemNombre}\" fue aprobada. Ya puedes proceder al pago."
        );

        return ['success' => true, 'message' => 'Solicitud aprobada correctamente.'];
    }

    /**
     * Proveedor rechaza una solicitud.
     */
    public function rechazar(int $proveedorId, int $solicitudId): array
    {
        $solicitud = SolicitudServicio::with('item')->find($solicitudId);

        if (!$solicitud) {
            return ['success' => false, 'message' => 'Solicitud no encontrada.'];
        }

        if ($solicitud->id_proveedor !== $proveedorId) {
            return ['success' => false, 'message' => 'No autorizado.', 'code' => 403];
        }

        if ($solicitud->estado !== 'pendiente_aprobacion') {
            return ['success' => false, 'message' => 'Esta solicitud ya fue procesada.'];
        }

        $solicitud->update([
            'estado' => 'rechazada',
            'fecha_actualizacion' => now(),
        ]);

        // Eliminar item del carrito del comprador
        ItemIntencionCompra::where('id_carrito', $solicitud->id_carrito)
            ->where('id_item', $solicitud->id_item)
            ->delete();

        $itemNombre = $solicitud->item?->item ?? 'servicio';
        $this->notificar(
            $solicitud->id_comprador,
            "[Servicio] Tu solicitud para \"{$itemNombre}\" fue rechazada por el proveedor."
        );

        return ['success' => true, 'message' => 'Solicitud rechazada.'];
    }

    /**
     * Marca solicitud como pagada tras pago exitoso.
     */
    public function marcarPagada(int $solicitudId): void
    {
        $solicitud = SolicitudServicio::find($solicitudId);
        if (!$solicitud) return;

        $solicitud->update([
            'estado' => 'pagada',
            'fecha_actualizacion' => now(),
        ]);

        $itemNombre = $solicitud->item?->item ?? 'servicio';
        $this->notificar(
            $solicitud->id_proveedor,
            "[Servicio] El comprador completo el pago para \"{$itemNombre}\". Monto: RD$ " . number_format($solicitud->monto_total, 2)
        );
    }

    /**
     * Verifica si un item tiene solicitud aprobada para el comprador.
     */
    public function tieneAprobacion(int $compradorId, int $itemId): bool
    {
        return SolicitudServicio::where('id_comprador', $compradorId)
            ->where('id_item', $itemId)
            ->where('estado', 'aprobada')
            ->exists();
    }

    /**
     * Obtiene la solicitud aprobada para un comprador+item.
     */
    public function obtenerAprobada(int $compradorId, int $itemId): ?SolicitudServicio
    {
        return SolicitudServicio::where('id_comprador', $compradorId)
            ->where('id_item', $itemId)
            ->where('estado', 'aprobada')
            ->first();
    }

    private function notificar(int $userId, string $mensaje): void
    {
        try {
            Message::create([
                'id_emisor'   => null,
                'id_receptor' => $userId,
                'mensaje'     => $mensaje,
                'leido'       => false,
            ]);
            event(new NuevaNotificacion($mensaje, $userId));
        } catch (\Throwable $e) {
            Log::warning('Error al notificar solicitud servicio', ['user_id' => $userId, 'error' => $e->getMessage()]);
        }
    }
}
