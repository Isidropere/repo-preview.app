<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\CompraTrazabilidad;
use App\Models\Direcciones;
use App\Models\PagoCompra;
use App\Models\PagoItem;
use App\Models\TarjetaPago;
use App\Services\PagoService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ============================================================
 * CheckoutService — Flujo completo de checkout y pago
 * ============================================================
 *
 * Orquesta todo el proceso de pago:
 * 1. Carga carrito con items seleccionados
 * 2. Valida dirección predeterminada
 * 3. Verifica stock de cada item
 * 4. Calcula monto total (valor × cantidad − descuento)
 * 5. Valida tarjeta del usuario
 * 6. Ejecuta cobro vía PagoService (CardNet/Stripe)
 * 7. Registra PagoCompra, PagoItems, trazabilidad y descuenta inventario
 * 8. Si falla el registro en BD post-cobro, intenta reembolso automático
 *
 * Moneda: RD$ (código ISO 214)
 * ============================================================
 */
class CheckoutService
{
    public function __construct(
        private PagoService $pagoService,
    ) {}

    /**
     * Ejecuta el flujo completo de checkout.
     *
     * @return array{success: bool, message: string, approval_code: string|null}
     */
    public function procesar(int $userId, string $idTarjeta, ?string $cvv, string $clientIp): array
    {
        // 1. Cargar carrito con items seleccionados
        $carrito = $this->obtenerCarrito($userId);
        $itemsSeleccionados = $carrito->itemsIntencionCompra->where('es_seleccionado', true);

        if ($itemsSeleccionados->isEmpty()) {
            return $this->error('No hay ítems seleccionados para pagar.');
        }

        // 2. Validar que no se compre a sí mismo
        $itemsPropios = $itemsSeleccionados->filter(fn($i) => $i->item->id_user === $userId);
        if ($itemsPropios->isNotEmpty()) {
            return $this->error('No puedes comprar tus propios artículos.');
        }

        // 3. Validar dirección predeterminada
        $direccion = $this->obtenerDireccionPredeterminada($userId);
        if (!$direccion) {
            return $this->error('Debes registrar una dirección de envío antes de realizar un pago. Ve a tu perfil → Direcciones.');
        }

        // 4. Verificar stock (con precios actualizados)
        $errorStock = $this->verificarStock($itemsSeleccionados);
        if ($errorStock) {
            return $this->error($errorStock);
        }

        // 5. Calcular monto total (usa precios actuales del item, no del carrito)
        $montoTotal = $this->calcularTotal($itemsSeleccionados);
        if ($montoTotal <= 0) {
            return $this->error('El monto total no puede ser cero o negativo.');
        }

        // 6. Validar tarjeta del usuario
        $tarjeta = $this->obtenerTarjetaUsuario($idTarjeta, $userId);
        if (!$tarjeta) {
            return $this->error('Tarjeta no válida o no pertenece a tu cuenta.');
        }

        // 6. Preparar datos de cobro
        $datosTarjeta = $this->prepararDatosTarjeta($tarjeta, $cvv);
        $opciones = $this->prepararOpciones($carrito, $clientIp);

        // 7. Cobrar
        $resultadoPago = $this->pagoService->cobrarTarjeta($montoTotal, '214', $datosTarjeta, $opciones);

        Log::info('Resultado del cobro', [
            'success' => $resultadoPago['success'],
            'status'  => $resultadoPago['status'] ?? null,
        ]);

        if (!$resultadoPago['success']) {
            Log::error('Pago rechazado', ['error' => $resultadoPago['error']]);
            return $this->error('Pago rechazado: ' . ($resultadoPago['error'] ?? 'Error desconocido'));
        }

        // 8. Registrar en BD (con reembolso automático si falla)
        return $this->registrarCompra(
            $itemsSeleccionados, $carrito, $tarjeta,
            $resultadoPago, $montoTotal, $direccion, $userId
        );
    }

    // ---------------------------------------------------------------
    // Métodos privados — lógica de negocio
    // ---------------------------------------------------------------

    private function obtenerCarrito(int $userId): Carrito
    {
        return Carrito::with('itemsIntencionCompra.item.inventarios')
            ->where('id_user', $userId)
            ->firstOrFail();
    }

    private function obtenerDireccionPredeterminada(int $userId): ?Direcciones
    {
        return Direcciones::where('id_user', $userId)
            ->where('es_predeterminada', 1)
            ->first();
    }

    private function verificarStock(Collection $items): ?string
    {
        foreach ($items as $item) {
            // Verificar que el item siga activo
            if ($item->item->estatus != 1) {
                return "El artículo \"{$item->item->item}\" ya no está disponible.";
            }

            $inventario = $item->item->inventarios;
            if (!$inventario || $inventario->cantidad < $item->cantidad) {
                $disponible = $inventario->cantidad ?? 0;
                return "Stock insuficiente para: {$item->item->item}. Disponible: {$disponible}";
            }
        }
        return null;
    }

    private function calcularTotal(Collection $items): float
    {
        return $items->sum(
            fn($i) => ($i->item->valor * $i->cantidad) - $i->descuento
        );
    }

    private function obtenerTarjetaUsuario(string $idTarjeta, int $userId): ?TarjetaPago
    {
        $tarjeta = TarjetaPago::where('id_tarjeta', $idTarjeta)
            ->where('id_user', $userId)
            ->first();

        if (!$tarjeta) {
            Log::warning('Tarjeta no encontrada o no pertenece al usuario', [
                'id_tarjeta' => $idTarjeta,
                'user_id'    => $userId,
            ]);
        }

        return $tarjeta;
    }

    private function prepararDatosTarjeta(TarjetaPago $tarjeta, ?string $cvv): array
    {
        $driver = config('services.payment.driver', 'cardnet');
        return $driver === 'stripe'
            ? $tarjeta->datosStripe()
            : $tarjeta->datosCardnet($cvv);
    }

    private function prepararOpciones(Carrito $carrito, string $clientIp): array
    {
        $invoiceNumber = substr((string) $carrito->id_carrito, 0, 15);
        return [
            'invoice_number'   => $invoiceNumber,
            'reference_number' => $invoiceNumber,
            'client_ip'        => $clientIp,
        ];
    }

    /**
     * Registra la compra en BD. Si falla, intenta reembolso automático.
     */
    private function registrarCompra(
        Collection $itemsSeleccionados,
        Carrito $carrito,
        TarjetaPago $tarjeta,
        array $resultadoPago,
        float $montoTotal,
        Direcciones $direccion,
        int $userId,
    ): array {
        try {
            DB::transaction(function () use ($itemsSeleccionados, $carrito, $tarjeta, $resultadoPago, $montoTotal, $direccion) {
                // Bloqueo pesimista: evita pedidos duplicados por doble submit
                $carritoLocked = Carrito::where('id_carrito', $carrito->id_carrito)->lockForUpdate()->first();
                $yaExiste = PagoCompra::where('id_carrito', $carritoLocked->id_carrito)
                    ->where('estatus', 'aprobado')
                    ->whereDate('fecha', today())
                    ->exists();
                if ($yaExiste) {
                    throw new \RuntimeException('duplicate_order');
                }

                $pagoCompra = PagoCompra::create([
                    'id_pago_compra'    => Str::uuid()->toString(),
                    'id_carrito'        => $carrito->id_carrito,
                    'estatus'           => 'aprobado',
                    'id_tarjeta'        => $tarjeta->id_tarjeta,
                    'autorizacion_pago' => $resultadoPago['approval_code'] ?? null,
                    'id_proveedor_pago' => 1,
                    'transaction_id'    => $resultadoPago['transaction_id'] ?? null,
                    'total'             => $montoTotal,
                    'cantidad_items'    => $itemsSeleccionados->count(),
                    'id_direccion'      => $direccion->id_direccion,
                ]);

                CompraTrazabilidad::create([
                    'id_pago_compra'  => $pagoCompra->id_pago_compra,
                    'estado_anterior' => null,
                    'estado_nuevo'    => 'aprobado',
                    'nota'            => 'Pago procesado correctamente. Autorización: ' . ($resultadoPago['approval_code'] ?? 'N/A') .
                                        ' | Dirección ID: ' . $direccion->id_direccion,
                    'id_admin'        => null,
                ]);

                foreach ($itemsSeleccionados as $itemIntencion) {
                    $this->registrarPagoItem($pagoCompra, $itemIntencion);
                }
            });

            Log::info('Pago completado', ['user_id' => $userId, 'carrito' => $carrito->id_carrito]);

            return $this->exito('¡Pago procesado correctamente! Tu pedido está en camino.');

        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'duplicate_order') {
                Log::warning('Pedido duplicado bloqueado', ['user_id' => $userId, 'carrito' => $carrito->id_carrito]);
                return $this->intentarReembolso($resultadoPago, $montoTotal, $userId);
            }
            Log::error('Error post-cobro al guardar en BD', ['error' => $e->getMessage()]);
            return $this->intentarReembolso($resultadoPago, $montoTotal, $userId);
        } catch (\Throwable $e) {
            Log::error('Error post-cobro al guardar en BD', ['error' => $e->getMessage()]);
            return $this->intentarReembolso($resultadoPago, $montoTotal, $userId);
        }
    }

    /**
     * Registra un item del pago, descuenta inventario y limpia el carrito.
     */
    private function registrarPagoItem(PagoCompra $pagoCompra, $itemIntencion): void
    {
        $itemModel  = $itemIntencion->item;
        $inventario = $itemModel->inventarios;

        $imagen    = $itemModel->imagenes()->first();
        $imagenUrl = null;
        if ($imagen) {
            $ruta = trim($imagen->ruta ?? '', '/');
            $directPath = $ruta . '/' . $imagen->nombre;
            // Guardar la ruta que realmente existe en el servidor
            if (file_exists(public_path($directPath))) {
                $imagenUrl = $directPath;
            } elseif (file_exists(public_path('storage/' . $directPath))) {
                $imagenUrl = 'storage/' . $directPath;
            } else {
                $imagenUrl = $directPath;
            }
        }

        PagoItem::create([
            'id_pago_compra'  => $pagoCompra->id_pago_compra,
            'id_item'         => $itemModel->id_item,
            'nombre_item'     => $itemModel->item,
            'precio_unitario' => $itemModel->valor,
            'cantidad'        => $itemIntencion->cantidad,
            'descuento'       => $itemIntencion->descuento ?? 0,
            'subtotal'        => ($itemModel->valor * $itemIntencion->cantidad) - ($itemIntencion->descuento ?? 0),
            'imagen_url'      => $imagenUrl,
        ]);

        $inventario->cantidad -= $itemIntencion->cantidad;
        $inventario->save();

        $itemIntencion->delete();
    }

    /**
     * Intenta reembolso automático tras fallo de BD post-cobro.
     */
    private function intentarReembolso(array $resultadoPago, float $montoTotal, int $userId): array
    {
        $txId         = $resultadoPago['transaction_id'] ?? null;
        $approvalCode = $resultadoPago['approval_code'] ?? 'N/A';

        if (!$txId) {
            Log::critical("COBRO_SIN_TRANSACTION_ID: no se puede reembolsar | approval_code: {$approvalCode} | user_id: {$userId}");
            return $this->error("Tu pago fue procesado pero ocurrió un error al registrarlo. Contacta soporte con tu código: {$approvalCode}");
        }

        try {
            $reembolso = $this->pagoService->reembolsar($txId, $montoTotal);

            if ($reembolso['success'] ?? false) {
                Log::info('Reembolso automático exitoso tras fallo de BD', ['transaction_id' => $txId]);
                return $this->error('Ocurrió un error al registrar tu pago. El cargo fue revertido automáticamente. Intenta de nuevo.');
            }

            Log::critical("REEMBOLSO_FALLIDO_TRAS_ERROR_BD: " . ($reembolso['error'] ?? 'sin detalle') . " | transaction_id: {$txId} | approval_code: {$approvalCode} | user_id: {$userId}");
        } catch (\Throwable $e) {
            Log::critical("EXCEPCION_EN_REEMBOLSO: {$e->getMessage()} | transaction_id: {$txId} | approval_code: {$approvalCode} | user_id: {$userId}");
        }

        return $this->error("Tu pago fue procesado pero ocurrió un error al registrarlo. Contacta soporte con tu código: {$approvalCode}");
    }

    // ---------------------------------------------------------------
    // Helpers de respuesta
    // ---------------------------------------------------------------

    private function exito(string $message): array
    {
        return ['success' => true, 'message' => $message];
    }

    private function error(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}
