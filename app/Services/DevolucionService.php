<?php

namespace App\Services;

use App\Models\CompraTrazabilidad;
use App\Models\PagoCompra;
use App\Models\Inventario;
use App\Services\PagoService;
use App\Services\ERPService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DevolucionService
{
    public function __construct(
        private PagoService $pagoService,
        private ERPService $erpService,
    ) {}

    /**
     * Procesa la devolución/cancelación de una compra de forma transaccional.
     *
     * @param string $compraId
     * @param int $userId
     * @param int|null $motivoId
     * @param string|null $comentario
     * @return array
     * @throws \Exception
     */
    public function procesarDevolucion(string $compraId, int $userId, ?int $motivoId = null, ?string $comentario = null): array
    {
        return DB::transaction(function () use ($compraId, $userId, $motivoId, $comentario) {
            // 1. Cargar y bloquear la orden para evitar modificaciones concurrentes
            $compra = PagoCompra::where('id_pago_compra', $compraId)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Validar propiedad (solo si el que solicita es el comprador)
            if ($compra->comprador?->id !== $userId) {
                throw new \Exception('No estás autorizado para solicitar la devolución de esta orden.');
            }

            // 3. Validar estatus elegible (pendiente o aprobado)
            if (!in_array($compra->estatus, ['pendiente', 'aprobado'])) {
                throw new \Exception('No se puede solicitar devolución en el estado actual de la orden (' . $compra->estatus . ').');
            }

            // Validar motivo si viene provisto
            $motivoTexto = 'No especificado';
            if ($motivoId) {
                $motivoModel = \App\Models\MotivoDevolucion::find($motivoId);
                if ($motivoModel) {
                    $motivoTexto = $motivoModel->motivo;
                }
            }

            // 4. Intentar revertir el pago si existe un ID de transacción
            $reembolsoExitoso = false;
            $txId = $compra->transaction_id;

            if ($txId) {
                Log::info("[DevolucionService] Intentando anular la transacción {$txId} por valor de {$compra->total}");
                try {
                    // Primero intentamos la anulación (void)
                    $res = $this->pagoService->anularTransaccion($txId, $compra->total);
                    if ($res['success'] ?? false) {
                        $reembolsoExitoso = true;
                        Log::info("[DevolucionService] Anulación exitosa para la transacción {$txId}");
                    } else {
                        Log::warning("[DevolucionService] Anulación fallida, intentando reembolso", ['error' => $res['error'] ?? 'desconocido']);
                        // Si falla la anulación, intentamos reembolso (refund)
                        $resRefund = $this->pagoService->reembolsar($txId, $compra->total);
                        if ($resRefund['success'] ?? false) {
                            $reembolsoExitoso = true;
                            Log::info("[DevolucionService] Reembolso exitoso para la transacción {$txId}");
                        } else {
                            Log::error("[DevolucionService] Reembolso fallido en pasarela", ['error' => $resRefund['error'] ?? 'desconocido']);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::critical("[DevolucionService] Excepción al interactuar con pasarela de pagos: " . $e->getMessage());
                }

                // Si falló el reembolso automático en la pasarela, levantamos una excepción para no registrar la cancelación en BD.
                if (!$reembolsoExitoso) {
                    throw new \Exception('No se pudo procesar el reembolso en la pasarela de pagos. Por favor, intente más tarde o contacte con soporte.');
                }
            } else {
                // Si no tiene transaction_id (ej: pedidos de prueba), se asume exitoso de forma automática.
                $reembolsoExitoso = true;
                Log::info("[DevolucionService] Orden sin transaction_id, procediendo con la cancelación únicamente en base de datos.");
            }

            $estadoAnterior = $compra->estatus;

            // 5. Actualizar estado de la compra y guardar motivos
            $compra->update([
                'estatus' => 'cancelado',
                'id_motivo_devolucion' => $motivoId,
                'comentario_devolucion' => $comentario,
            ]);

            // 6. Registrar en Trazabilidad
            CompraTrazabilidad::create([
                'id_pago_compra'  => $compra->id_pago_compra,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => 'cancelado',
                'nota'            => "Devolución solicitada por el usuario. Motivo: {$motivoTexto}." . ($comentario ? " Comentario: {$comentario}." : '') . ($txId ? ' Reembolso procesado.' : ' Sin transacción financiera.'),
                'id_admin'        => null,
            ]);

            // 7. Devolver stock físico de los artículos
            foreach ($compra->pagoItems as $pagoItem) {
                $item = $pagoItem->item;
                if ($item) {
                    if ((int) ($item->id_categoria_item ?? 0) === 29) {
                        continue;
                    }

                    $inventario = $item->inventarios;
                    if ($inventario) {
                        $inventario->cantidad += $pagoItem->cantidad;
                        $inventario->save();
                        Log::info("[DevolucionService] Stock incrementado para item {$item->id_item} en {$pagoItem->cantidad} unidades.");
                    }
                }
            }

            // 8. Procesar reversión en el ERP
            $this->erpService->procesarVentaCancelada($compra);

            // 9. Enviar notificaciones a los administradores
            try {
                $admins = \App\Models\User::where('isAdmin', 1)
                    ->orWhere('isSuperAdmin', 1)
                    ->get();

                $compradorNombre = $compra->comprador ? ($compra->comprador->nombres . ' ' . $compra->comprador->apellidos) : 'Usuario';
                $notifMensaje = "El usuario {$compradorNombre} ha solicitado devolución para la orden #{$compra->id_pago_compra}. Motivo: {$motivoTexto}";

                foreach ($admins as $admin) {
                    DB::table('notificaciones')->insert([
                        'id_usuario' => $admin->id,
                        'mensaje' => $notifMensaje,
                        'leida' => 0,
                        'fecha_envio' => now()
                    ]);

                    event(new \App\Events\NuevaNotificacion($notifMensaje, $admin->id));
                }
                Log::info("[DevolucionService] Notificados " . $admins->count() . " administradores.");
            } catch (\Throwable $e) {
                Log::error("[DevolucionService] Error al notificar a administradores: " . $e->getMessage());
            }

            return [
                'success' => true,
                'message' => 'Devolución y reembolso procesados exitosamente.',
            ];
        });
    }
}
