<?php

namespace App\Services;

use App\Models\CompraTrazabilidad;
use App\Models\ItemIntencionCompra;
use App\Models\Negociacion;
use App\Models\PagoCompra;
use App\Services\ERPService;

/**
 * ============================================================
 * AdminComprasService — Lógica de negocio del panel admin
 * ============================================================
 *
 * Gestiona las operaciones administrativas:
 * - Obtener datos del panel principal con tabs
 * - Actualizar estado de compras (con trazabilidad)
 * - Actualizar estado de intercambios
 *
 * Estados de compra: pendiente → aprobado → enviado → entregado
 *                              → rechazado / cancelado
 *
 * Estados de intercambio: Inicial → pendiente → contraoferta
 *                                 → aceptado → completado
 *                                 → rechazado / cancelado
 * ============================================================
 */
class AdminComprasService
{
    public function __construct(
        private ERPService $erpService,
    ) {}

    public const ESTADOS_COMPRA = ['pendiente', 'aprobado', 'rechazado', 'enviado', 'entregado', 'cancelado'];
    public const ESTADOS_INTERCAMBIO = ['Inicial', 'pendiente', 'aceptado', 'rechazado', 'contraoferta', 'en_envio', 'completado', 'cancelado'];

    /**
     * Datos del panel principal con todas las pestañas.
     */
    public function obtenerDatosPanelPrincipal(string $tab, ?string $estatus, ?string $buscar, ?string $fechaDesde = null, ?string $fechaHasta = null): array
    {
        return [
            'compras'                    => $this->queryCompras($tab, $estatus, $buscar, $fechaDesde, $fechaHasta)->paginate(20, ['*'], 'page_compras')->withQueryString(),
            'ventas'                     => $this->queryVentas($tab, $buscar, $fechaDesde, $fechaHasta)->paginate(20, ['*'], 'page_ventas')->withQueryString(),
            'intercambios'               => $this->queryIntercambios($tab, $estatus, $buscar, $fechaDesde, $fechaHasta)->paginate(20, ['*'], 'page_intercambios')->withQueryString(),
            'intencionCompra'            => $this->queryIntencionCompra($tab, $buscar)->paginate(20, ['*'], 'page_ic')->withQueryString(),
            'intencionIntercambio'       => $this->queryIntencionIntercambio($tab, $buscar, $fechaDesde, $fechaHasta)->paginate(20, ['*'], 'page_ii')->withQueryString(),
            'intercambiosConfirmados'    => $this->queryIntercambiosConfirmados($tab, $buscar, $fechaDesde, $fechaHasta)->paginate(20, ['*'], 'page_ic2')->withQueryString(),
            'totalCompras'               => PagoCompra::count(),
            'totalVentas'                => PagoCompra::whereHas('pagoItems.item', fn($q) => $q->whereIn('tipo_trans', [1, 3]))->count(),
            'totalIntercambios'          => Negociacion::count(),
            'totalIntencionCompra'       => ItemIntencionCompra::whereHas('item', fn($q) => $q->whereIn('tipo_trans', [1, 3]))->count(),
            'totalIntencionIntercambio'  => Negociacion::whereIn('estado', ['Inicial', 'pendiente', 'contraoferta'])->count(),
            'estadosCompra'              => self::ESTADOS_COMPRA,
            'estadosIntercambio'         => self::ESTADOS_INTERCAMBIO,
            'tab'                        => $tab,
        ];
    }

    /**
     * Actualiza el estado de una compra y registra trazabilidad.
     */
    public function actualizarEstadoCompra(string $compraId, string $nuevoEstado, ?string $nota, int $adminId): array
    {
        $compra = PagoCompra::findOrFail($compraId);
        $estadoAnterior = $compra->estatus;
        $compra->update(['estatus' => $nuevoEstado]);

        CompraTrazabilidad::create([
            'id_pago_compra'  => $compra->id_pago_compra,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $nuevoEstado,
            'nota'            => $nota,
            'id_admin'        => $adminId,
        ]);

        // --- AUTOMATIZACIÓN ERP ---
        if ($nuevoEstado === 'aprobado' && $estadoAnterior !== 'aprobado') {
            $this->erpService->procesarVentaAprobada($compra);
        }

        return ['success' => true, 'message' => 'Estado actualizado correctamente.'];
    }

    /**
     * Actualiza el estado de un intercambio.
     */
    public function actualizarEstadoIntercambio(int $intercambioId, string $nuevoEstado, ?string $nota = null, ?int $adminId = null): array
    {
        $intercambio = Negociacion::findOrFail($intercambioId);
        $estadoAnterior = $intercambio->estado;

        if (in_array($nuevoEstado, ['cancelado', 'rechazado'])) {
            app(\App\Services\NegociacionService::class)->restaurarStock($intercambio);
        }

        $intercambio->update(['estado' => $nuevoEstado]);

        // Registrar trazabilidad
        \App\Models\NegociacionTrazabilidad::create([
            'id_negociacion'  => $intercambioId,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $nuevoEstado,
            'nota'            => $nota,
            'id_admin'        => $adminId,
        ]);

        return ['success' => true, 'message' => 'Estado del intercambio actualizado.'];
    }

    // ───────────────────────────────────────────────────────
    // Queries privadas
    // ───────────────────────────────────────────────────────

    private function queryCompras(string $tab, ?string $estatus, ?string $buscar, ?string $fechaDesde = null, ?string $fechaHasta = null)
    {
        $query = PagoCompra::with(['pagoItems.item.imagenes', 'carrito.usuario'])
            ->orderByDesc('id_pago_compra');

        if ($tab === 'compras') {
            if ($estatus) $query->where('estatus', $estatus);
            if ($buscar) {
                $query->where(fn($q) => $q
                    ->where('id_pago_compra', 'like', "%$buscar%")
                    ->orWhereHas('carrito.usuario', fn($q2) => $q2
                        ->where('nombres', 'like', "%$buscar%")
                        ->orWhere('email', 'like', "%$buscar%")));
            }
            if ($fechaDesde) $query->whereDate('created_at', '>=', $fechaDesde);
            if ($fechaHasta) $query->whereDate('created_at', '<=', $fechaHasta);
        }

        return $query;
    }

    private function queryVentas(string $tab, ?string $buscar, ?string $fechaDesde = null, ?string $fechaHasta = null)
    {
        $query = PagoCompra::with(['pagoItems.item.imagenes', 'pagoItems.item.usuario', 'carrito.usuario'])
            ->whereHas('pagoItems.item', fn($q) => $q->whereIn('tipo_trans', [1, 3]))
            ->orderByDesc('id_pago_compra');

        if ($tab === 'ventas') {
            if ($buscar) {
                $query->where(fn($q) => $q
                    ->where('id_pago_compra', 'like', "%$buscar%")
                    ->orWhereHas('carrito.usuario', fn($q2) => $q2
                        ->where('nombres', 'like', "%$buscar%")
                        ->orWhere('email', 'like', "%$buscar%"))
                    ->orWhereHas('pagoItems.item.usuario', fn($q2) => $q2
                        ->where('nombres', 'like', "%$buscar%")
                        ->orWhere('email', 'like', "%$buscar%")));
            }
            if ($fechaDesde) $query->whereDate('created_at', '>=', $fechaDesde);
            if ($fechaHasta) $query->whereDate('created_at', '<=', $fechaHasta);
        }

        return $query;
    }

    private function queryIntercambios(string $tab, ?string $estatus, ?string $buscar, ?string $fechaDesde = null, ?string $fechaHasta = null)
    {
        $query = Negociacion::with(['item.imagenes', 'usuario', 'usuarioReceptor'])
            ->orderByDesc('id_negociacion');

        if ($tab === 'intercambios') {
            if ($estatus) $query->where('estado', $estatus);
            if ($buscar) {
                $query->where(fn($q) => $q
                    ->whereHas('usuario', fn($q2) => $q2
                        ->where('nombres', 'like', "%$buscar%")
                        ->orWhere('email', 'like', "%$buscar%"))
                    ->orWhereHas('item', fn($q2) => $q2
                        ->where('item', 'like', "%$buscar%")));
            }
            if ($fechaDesde) $query->whereDate('fecha_creacion', '>=', $fechaDesde);
            if ($fechaHasta) $query->whereDate('fecha_creacion', '<=', $fechaHasta);
        }

        return $query;
    }

    private function queryIntencionCompra(string $tab, ?string $buscar)
    {
        $query = ItemIntencionCompra::with(['item.imagenes', 'item.usuario', 'carrito.usuario'])
            ->whereHas('item', fn($q) => $q->whereIn('tipo_trans', [1, 3]))
            ->orderByDesc('id_item_intencion_compra');

        if ($tab === 'intencion_compra' && $buscar) {
            $query->where(fn($q) => $q
                ->whereHas('item', fn($q2) => $q2->where('item', 'like', "%$buscar%"))
                ->orWhereHas('carrito.usuario', fn($q2) => $q2
                    ->where('nombres', 'like', "%$buscar%")
                    ->orWhere('email', 'like', "%$buscar%")));
        }

        return $query;
    }

    private function queryIntencionIntercambio(string $tab, ?string $buscar, ?string $fechaDesde = null, ?string $fechaHasta = null)
    {
        $query = Negociacion::with(['item.imagenes', 'usuario', 'usuarioReceptor'])
            ->whereIn('estado', ['Inicial', 'pendiente', 'contraoferta'])
            ->orderByDesc('id_negociacion');

        if ($tab === 'intencion_intercambio') {
            if ($buscar) {
                $query->where(fn($q) => $q
                    ->whereHas('usuario', fn($q2) => $q2
                        ->where('nombres', 'like', "%$buscar%")
                        ->orWhere('email', 'like', "%$buscar%"))
                    ->orWhereHas('item', fn($q2) => $q2
                        ->where('item', 'like', "%$buscar%")));
            }
            if ($fechaDesde) $query->whereDate('fecha_creacion', '>=', $fechaDesde);
            if ($fechaHasta) $query->whereDate('fecha_creacion', '<=', $fechaHasta);
        }

        return $query;
    }

    private function queryIntercambiosConfirmados(string $tab, ?string $buscar, ?string $fechaDesde = null, ?string $fechaHasta = null)
    {
        $query = Negociacion::with(['item.imagenes', 'item.categoria', 'usuario', 'usuarioReceptor'])
            ->where(function ($q) {
                // Ambos aprobaron pendiente de pago, pagos completos pendiente envio, o ya completado
                $q->where(function ($q2) {
                    $q2->where('estado', 'aceptado')
                       ->where('emisor_confirmado', true)
                       ->where('receptor_confirmado', true);
                })
                ->orWhere('estado', 'en_envio')
                ->orWhere('estado', 'completado');
            })
            ->orderByDesc('id_negociacion');

        if ($tab === 'intercambios_confirmados') {
            if ($buscar) {
                $query->where(fn($q) => $q
                    ->whereHas('usuario', fn($q2) => $q2
                        ->where('nombres', 'like', "%$buscar%")
                        ->orWhere('email', 'like', "%$buscar%"))
                    ->orWhereHas('usuarioReceptor', fn($q2) => $q2
                        ->where('nombres', 'like', "%$buscar%")
                        ->orWhere('email', 'like', "%$buscar%"))
                    ->orWhereHas('item', fn($q2) => $q2
                        ->where('item', 'like', "%$buscar%")));
            }
            if ($fechaDesde) $query->whereDate('fecha_creacion', '>=', $fechaDesde);
            if ($fechaHasta) $query->whereDate('fecha_creacion', '<=', $fechaHasta);
        }

        return $query;
    }
}
