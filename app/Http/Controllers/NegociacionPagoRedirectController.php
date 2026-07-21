<?php

namespace App\Http\Controllers;

use App\Models\Negociacion;
use App\Models\PagoEnvioIntercambio;
use App\Services\Payments\AzulProvider;
use App\Services\ERPService;
use App\Services\NegociacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NegociacionPagoRedirectController extends Controller
{
    public function __construct(
        private AzulProvider $azulProvider,
        private ERPService $erpService,
        private NegociacionService $negociacionService,
    ) {}

    /**
     * Inicia el flujo de redirección a AZUL para pagar el envío de una negociación desde la Web.
     */
    public function iniciarPagoWeb($id_negociacion)
    {
        $userId = auth()->id();
        $realId = is_numeric($id_negociacion) ? (int)$id_negociacion : \App\Helpers\HashIdHelper::decode($id_negociacion);
        if (!$realId) {
            return redirect()->route('negociaciones.mis')->with('error', 'No se encontró la negociación especificada.');
        }
        Log::info('[Negociacion Redirect Web] Iniciando pago de envío', ['user_id' => $userId, 'id_negociacion' => $realId]);

        $neg = Negociacion::with(['item'])->find($realId);
        if (!$neg) {
            return redirect()->route('negociaciones.mis')->with('error', 'No se encontró la negociación especificada.');
        }

        if ($userId != $neg->usuario_emisor_id && $userId != $neg->usuario_receptor_id) {
            return redirect()->route('negociaciones.mis')->with('error', 'No autorizado.');
        }

        return $this->procesarPagoEnvio($neg, $userId, 'azul_negociacion_web');
    }

    /**
     * Inicia el flujo de redirección a AZUL para pagar el envío desde la App Móvil (sin requerir sesión activa).
     */
    public function iniciarPagoMovil($id_negociacion, Request $request)
    {
        $realId = is_numeric($id_negociacion) ? (int)$id_negociacion : \App\Helpers\HashIdHelper::decode($id_negociacion);
        if (!$realId) {
            return response('Negociación no encontrada.', 404);
        }
        Log::info('[Negociacion Redirect Móvil] Iniciando pago de envío móvil', ['id_negociacion' => $realId]);

        $neg = Negociacion::with(['item'])->find($realId);
        if (!$neg) {
            return response('Negociación no encontrada.', 404);
        }

        // Obtener el ID del usuario desde los parámetros (el token API ya validó esto en el endpoint)
        $userId = (int)$request->input('user_id', $neg->usuario_emisor_id); // fallback seguro
        if ($userId != $neg->usuario_emisor_id && $userId != $neg->usuario_receptor_id) {
            return response('No autorizado.', 403);
        }

        return $this->procesarPagoEnvio($neg, $userId, 'azul_negociacion_movil');
    }

    /**
     * Valida la dirección, calcula el costo del delivery e inicializa el registro temporal de pago.
     */
    private function procesarPagoEnvio(Negociacion $neg, int $userId, string $provider)
    {
        if (!$neg->emisor_confirmado || !($neg->receptor_confirmado ?? false)) {
            return redirect()->route('negociaciones.mis')->with('error', 'Ambas partes deben aprobar la negociación antes de proceder al pago.');
        }

        if ($neg->estado !== 'aceptado') {
            return redirect()->route('negociaciones.mis')->with('error', 'El intercambio no está en un estado válido para pago.');
        }

        $campoPago = $userId == $neg->usuario_emisor_id ? 'pago_emisor' : 'pago_receptor';
        if ($neg->$campoPago) {
            return redirect()->route('negociaciones.mis')->with('success', 'Ya has realizado el pago correspondiente para este envío.');
        }

        // 1. Obtener dirección
        $direccion = \App\Models\Direcciones::where('id_user', $userId)->with('municipio')->first();
        if (!$direccion) {
            return redirect()->to(route('direcciones.index') . '?return_url=' . urlencode(route('negociaciones.pago', \App\Helpers\HashIdHelper::encode($neg->id_negociacion))))
                ->with('error', 'Debes registrar una dirección de envío antes de pagar.');
        }

        // 2. Calcular costo
        $montoACobrar = 0;
        if ($neg->item) {
            $deliveryService = app(\App\Services\DeliveryService::class);
            $resultado = $deliveryService->calcular(
                $direccion->municipio->municipio ?? '',
                'persona',
                0,
                (float) ($neg->item->peso_lbs ?? 0),
                (float) ($neg->item->alto_cm ?? 0),
                (float) ($neg->item->ancho_cm ?? 0),
                (float) ($neg->item->profundo_cm ?? 0)
            );
            if (!$resultado['success'] && (($resultado['error_code'] ?? null) === 'MISSING_DELIVERY_TARIFF' || ($resultado['error_code'] ?? null) === 'ZONA_NO_CONTEMPLADA')) {
                return redirect()->route('negociaciones.mis')->with('error', $resultado['message'] ?? 'Actualmente no está contemplado viajar a esa zona del país.');
            }
            $montoACobrar = $resultado['success'] ? ($resultado['costo_envio_total'] ?? 0) : 0;
        }

        if ($montoACobrar <= 0) {
            // Si el costo es cero, se aprueba de forma automática
            DB::transaction(function () use ($neg, $userId, $campoPago) {
                $neg->update([$campoPago => true]);

                $pagoEnvio = PagoEnvioIntercambio::create([
                    'id_negociacion' => $neg->id_negociacion,
                    'id_user'        => $userId,
                    'monto'          => 0,
                    'tipo_pago'      => 'sin_pago',
                    'estado'         => 'pagado',
                ]);

                $this->actualizarEstadoTransicion($neg);
            });
            return redirect()->route('negociaciones.mis')->with('success', 'Confirmación de envío registrada correctamente (costo cero).');
        }

        // 3. Crear o actualizar PagoEnvioIntercambio en estado 'pendiente'
        $pagoEnvio = PagoEnvioIntercambio::updateOrCreate([
            'id_negociacion' => $neg->id_negociacion,
            'id_user'        => $userId,
            'estado'         => 'pendiente',
        ], [
            'monto'          => $montoACobrar,
            'tipo_pago'      => 'tarjeta',
            'id_tarjeta'     => null,
        ]);

        $isMobile = ($provider === 'azul_negociacion_movil');
        $queryParam = $isMobile ? '?mobile=1' : '';

        $orderNumber = 'INT-' . $pagoEnvio->id . '-' . time();
        $azulData = $this->azulProvider->generarCamposFormulario($montoACobrar, $orderNumber, [
            'approved_url' => route('negociaciones.pago.aprobado') . $queryParam,
            'declined_url' => route('negociaciones.pago.declinado') . $queryParam,
            'cancel_url'   => route('negociaciones.pago.cancelado') . $queryParam,
        ]);

        // Registrar log de pago
        DB::table('logs_pagos')->insert([
            'id_user'          => $userId,
            'custom_order_id'  => $orderNumber,
            'provider'         => $provider,
            'transaction_type' => 'negociacion_init',
            'amount'           => $montoACobrar,
            'request_payload'  => json_encode($azulData['fields']),
            'response_payload' => json_encode([]),
            'is_success'       => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return view('pago.redirect', [
            'url'    => $azulData['url'],
            'fields' => $azulData['fields']
        ]);
    }

    /**
     * Callback invocado por AZUL cuando el pago del envío de intercambio es aprobado.
     */
    public function pagoAprobado(Request $request)
    {
        Log::info('[Negociacion Redirect] Pago aprobado callback recibido', $request->all());

        if (!$this->azulProvider->validarFirmaRespuesta($request->all())) {
            Log::critical('[Negociacion Redirect] Firma AuthHash inválida en pagoAprobado');
            return $this->mostrarVistaResultado(false, 'Firma de seguridad inválida de la pasarela de pagos.');
        }

        $orderNumber = $request->input('OrderNumber');
        $parts = explode('-', $orderNumber);
        $pagoEnvioId = isset($parts[1]) ? (int)$parts[1] : null;

        $pagoEnvio = PagoEnvioIntercambio::with('negociacion')->find($pagoEnvioId);
        if (!$pagoEnvio) {
            Log::critical('[Negociacion Redirect] Pago de envío no encontrado en pagoAprobado', ['order_number' => $orderNumber]);
            return $this->mostrarVistaResultado(false, 'No se encontró el registro de pago asociado.');
        }

        if ($pagoEnvio->estado === 'pagado') {
            return $this->mostrarVistaResultado(true, 'El pago de este envío ya ha sido procesado con éxito.', $pagoEnvio->id);
        }

        try {
            DB::transaction(function () use ($pagoEnvio, $request) {
                // Actualizar PagoEnvioIntercambio
                $pagoEnvio->update([
                    'estado'         => 'pagado',
                    'transaction_id' => $request->input('RRN') ?? $request->input('AuthorizationCode') ?? 'REDIRECT_AZUL_' . time(),
                    'approval_code'  => $request->input('AuthorizationCode'),
                ]);

                // Actualizar campo de pago en Negociacion
                $neg = $pagoEnvio->negociacion;
                $campoPago = $pagoEnvio->id_user == $neg->usuario_emisor_id ? 'pago_emisor' : 'pago_receptor';
                $neg->update([$campoPago => true]);

                // Generar Contabilidad (Asiento y Caja)
                $this->erpService->procesarPagoEnvioAprobado($pagoEnvio);

                // Evaluar transiciones de estado
                $this->actualizarEstadoTransicion($neg);
            });

            return $this->mostrarVistaResultado(true, '¡El pago de tu envío se ha registrado exitosamente!', $pagoEnvio->id);
        } catch (\Throwable $e) {
            Log::error('[Negociacion Redirect] Error al asentar pago de envío', ['error' => $e->getMessage()]);
            return $this->mostrarVistaResultado(false, 'El pago fue cobrado por el banco, pero ocurrió un error al asentarlo en el sistema: ' . $e->getMessage());
        }
    }

    /**
     * Callback invocado por AZUL cuando el pago del envío de intercambio es declinado.
     */
    public function pagoDeclinado(Request $request)
    {
        Log::warning('[Negociacion Redirect] Pago declinado callback recibido', $request->all());

        if (!$this->azulProvider->validarFirmaRespuesta($request->all())) {
            Log::critical('[Negociacion Redirect] Firma AuthHash inválida en pagoDeclinado');
            return $this->mostrarVistaResultado(false, 'Firma de seguridad de respuesta inválida.');
        }

        $errorMsg = $request->input('ErrorDescription') ?? $request->input('ResponseMessage') ?? 'El pago fue rechazado por el banco.';
        return $this->mostrarVistaResultado(false, $errorMsg);
    }

    /**
     * Callback invocado cuando el usuario cancela la operación de pago.
     */
    public function pagoCancelado(Request $request)
    {
        Log::info('[Negociacion Redirect] Pago cancelado por el usuario');
        return $this->mostrarVistaResultado(false, 'Has cancelado el proceso de pago. El envío sigue pendiente.');
    }

    /**
     * Evalúa la transición de estado del intercambio tras un pago exitoso.
     */
    private function actualizarEstadoTransicion(Negociacion $neg)
    {
        $negFresh = $neg->fresh();
        $esProductoServicio = $this->negociacionService->esProductoServicio($negFresh);

        if ($esProductoServicio) {
            // En Producto vs Servicio, si AL MENOS UNO paga, ya puede ir a envío
            if ($negFresh->pago_emisor || $negFresh->pago_receptor) {
                $neg->update(['estado' => 'en_envio']);
                $this->negociacionService->notificarAdminsCompletado($negFresh);
            }
        } else {
            // Caso Producto vs Producto: ambos deben pagar
            if ($negFresh->pago_emisor && $negFresh->pago_receptor) {
                $neg->update(['estado' => 'en_envio']);
                $this->negociacionService->notificarAdminsCompletado($negFresh);
            }
        }
    }

    /**
     * Renderiza una vista de confirmación que funciona para la Web y es amigable con la App móvil.
     */
    private function mostrarVistaResultado(bool $success, string $message, $pagoEnvioId = null)
    {
        $isMobile = request()->segment(3) === 'movil' || request()->has('mobile') || !auth()->check();

        if ($isMobile) {
            return view('pago.resultado_movil', [
                'success' => $success,
                'message' => $message,
                'title'   => 'Pago de Envío'
            ]);
        }

        if ($success) {
            if ($pagoEnvioId) {
                return redirect()->route('historial')->with('success', $message)->with('order_completed_id', 'ENV-' . $pagoEnvioId);
            }
            return redirect()->route('negociaciones.mis')->with('success', $message);
        } else {
            return redirect()->route('negociaciones.mis')->with('error', $message);
        }
    }
}
