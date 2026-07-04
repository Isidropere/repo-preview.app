<?php

namespace App\Http\Controllers;

use App\Models\ConfigTarifaCategoria29;
use App\Models\Item;
use App\Models\PagoRegistroTalento;
use App\Services\Payments\AzulProvider;
use App\Services\ERPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TalentoPagoRedirectController extends Controller
{
    public function __construct(
        private AzulProvider $azulProvider,
        private ERPService $erpService,
    ) {}

    /**
     * Inicia el flujo de redirección a AZUL para registrar un talento desde la Web.
     */
    public function iniciarPagoWeb(int $id_item)
    {
        $userId = auth()->id();
        Log::info('[Talento Redirect Web] Iniciando pago de talento', ['user_id' => $userId, 'id_item' => $id_item]);

        $item = Item::where('id_item', $id_item)
            ->where('id_user', $userId)
            ->first();

        if (!$item) {
            return redirect()->route('items.admintalento')->with('error', 'No se encontró el talento especificado.');
        }

        return $this->procesarRedireccion($item, $userId, 'azul_talento_web');
    }

    /**
     * Inicia el flujo de redirección a AZUL para registrar un talento desde la App Móvil (sin requerir sesión activa).
     */
    public function iniciarPagoMovil(int $id_item)
    {
        Log::info('[Talento Redirect Móvil] Iniciando pago de talento móvil', ['id_item' => $id_item]);

        $item = Item::where('id_item', $id_item)->first();

        if (!$item) {
            return response('Talento no encontrado.', 404);
        }

        return $this->procesarRedireccion($item, $item->id_user, 'azul_talento_movil');
    }

    /**
     * Procesa la redirección generando los campos necesarios para enviar a AZUL.
     */
    private function procesarRedireccion(Item $item, int $userId, string $provider)
    {
        if ((int)$item->id_categoria_item !== 29) {
            return redirect()->route('items.admintalento')->with('error', 'El ítem seleccionado no es de la categoría de talentos.');
        }

        if ($item->estatus == 1) {
            return redirect()->route('items.admintalento')->with('success', 'El talento ya se encuentra publicado y pagado.');
        }

        // Obtener tarifa y calcular monto total
        $config = ConfigTarifaCategoria29::vigente();
        $cantidad = (int)($item->inventarios?->cantidad ?? 1);
        $monto = (float) $config->monto_registro * $cantidad;

        if ($monto <= 0) {
            // Si no requiere pago, activamos de inmediato
            $item->update(['estatus' => 1]);
            return redirect()->route('items.admintalento')->with('success', 'Talento publicado correctamente (tarifa cero).');
        }

        $isMobile = ($provider === 'azul_talento_movil');
        $queryParam = $isMobile ? '?mobile=1' : '';

        $approvedUrl = route('talento.pago.aprobado') . $queryParam;
        $declinedUrl = route('talento.pago.declinado') . $queryParam;
        $cancelUrl   = route('talento.pago.cancelado') . $queryParam;

        // Reemplazar dinámicamente el host local por el host real usado por el cliente (ej: 10.0.2.2:8000 o 10.0.0.51:8000)
        $clientHost = request()->getHttpHost();
        $approvedUrl = str_replace(['127.0.0.1:8000', 'localhost:8000', '127.0.0.1', 'localhost'], $clientHost, $approvedUrl);
        $declinedUrl = str_replace(['127.0.0.1:8000', 'localhost:8000', '127.0.0.1', 'localhost'], $clientHost, $declinedUrl);
        $cancelUrl   = str_replace(['127.0.0.1:8000', 'localhost:8000', '127.0.0.1', 'localhost'], $clientHost, $cancelUrl);

        $orderNumber = 'TAL-' . $item->id_item . '-' . time();
        $azulData = $this->azulProvider->generarCamposFormulario($monto, $orderNumber, [
            'approved_url' => $approvedUrl,
            'declined_url' => $declinedUrl,
            'cancel_url'   => $cancelUrl,
        ]);

        // Registrar log de pago
        DB::table('logs_pagos')->insert([
            'id_user'          => $userId,
            'custom_order_id'  => $orderNumber,
            'provider'         => $provider,
            'transaction_type' => 'talento_init',
            'amount'           => $monto,
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
     * Callback invocado por AZUL cuando el pago del talento es aprobado.
     */
    public function pagoAprobado(Request $request)
    {
        Log::info('[Talento Redirect] Pago aprobado callback recibido', $request->all());

        if (!$this->azulProvider->validarFirmaRespuesta($request->all())) {
            Log::critical('[Talento Redirect] Firma AuthHash inválida en pagoAprobado');
            return $this->mostrarVistaResultado(false, 'Firma de seguridad inválida de la pasarela de pagos.');
        }

        $orderNumber = $request->input('OrderNumber');
        $parts = explode('-', $orderNumber);
        $itemId = isset($parts[1]) ? (int)$parts[1] : null;
        $isRecharge = ($parts[0] === 'TALREC');

        $item = Item::where('id_item', $itemId)->first();
        if (!$item) {
            Log::critical('[Talento Redirect] Talento no encontrado en pagoAprobado', ['order_number' => $orderNumber]);
            return $this->mostrarVistaResultado(false, 'No se encontró el talento asociado a este pago.');
        }

        if ($item->estatus == 1 && !$isRecharge) {
            return $this->mostrarVistaResultado(true, '¡El talento ya ha sido publicado con éxito!', $item);
        }

        try {
            DB::transaction(function () use ($item, $request, $parts, $isRecharge) {
                $cantidad = $isRecharge ? (int)($parts[2] ?? 1) : 1;

                if ($isRecharge) {
                    if ($item->inventarios) {
                        $item->inventarios->increment('cantidad', $cantidad);
                    } else {
                        \App\Models\Inventario::create([
                            'id_item' => $item->id_item,
                            'cantidad' => $cantidad,
                            'fecha' => now(),
                        ]);
                    }
                }

                // Cambiar el talento a activo
                $item->update(['estatus' => 1]);

                // Registrar pago del talento
                $config = ConfigTarifaCategoria29::vigente();
                $cantidadMonto = $isRecharge ? $cantidad : (int)($item->inventarios?->cantidad ?? 1);
                $monto = (float) $config->monto_registro * $cantidadMonto;

                $pagoTalento = PagoRegistroTalento::create([
                    'id_item'        => $item->id_item,
                    'id_user'        => $item->id_user,
                    'transaction_id' => $request->input('RRN') ?? $request->input('AuthorizationCode') ?? 'REDIRECT_AZUL_' . time(),
                    'monto_pagado'   => $monto,
                    'estatus'        => 'aprobado',
                    'notas'          => ($isRecharge ? "Recarga de {$cantidad} publicaciones" : "Pago registro talento") . ' procesado vía redirección AZUL. Código Autorización: ' . $request->input('AuthorizationCode'),
                ]);

                // Generar Contabilidad (Asiento y Caja)
                $this->erpService->procesarRegistroTalentoAprobado($pagoTalento);

                // Registrar en logs_pagos
                DB::table('logs_pagos')->insert([
                    'id_user'          => $item->id_user,
                    'custom_order_id'  => $request->input('OrderNumber'),
                    'provider'         => 'azul_talento_redirect',
                    'transaction_type' => 'talento_approved',
                    'amount'           => $monto,
                    'request_payload'  => json_encode([]),
                    'response_payload' => json_encode($request->all()),
                    'is_success'       => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                // Invalidar caches
                \Illuminate\Support\Facades\Cache::forget('home_intercambio');
                \Illuminate\Support\Facades\Cache::forget('home_venta');
            });

            return $this->mostrarVistaResultado(true, '¡Felicidades! Tu talento ha sido pagado y publicado exitosamente.', $item);
        } catch (\Throwable $e) {
            Log::error('[Talento Redirect] Error al activar talento tras pago aprobado', ['error' => $e->getMessage()]);
            return $this->mostrarVistaResultado(false, 'El pago fue aprobado por el banco, pero ocurrió un error al registrar el talento en nuestro sistema: ' . $e->getMessage());
        }
    }

    /**
     * Callback invocado por AZUL cuando el pago del talento es declinado.
     */
    public function pagoDeclinado(Request $request)
    {
        Log::warning('[Talento Redirect] Pago declinado callback recibido', $request->all());

        if (!$this->azulProvider->validarFirmaRespuesta($request->all())) {
            Log::critical('[Talento Redirect] Firma AuthHash inválida en pagoDeclinado');
            return $this->mostrarVistaResultado(false, 'Firma de seguridad inválida de la pasarela de pagos.');
        }

        $orderNumber = $request->input('OrderNumber');
        $parts = explode('-', $orderNumber);
        $itemId = isset($parts[1]) ? (int)$parts[1] : null;
        $item = Item::where('id_item', $itemId)->first();

        $config = ConfigTarifaCategoria29::vigente();
        $cantidad = $item ? (int)($item->inventarios?->cantidad ?? 1) : 1;
        $monto = (float) $config->monto_registro * $cantidad;

        // Registrar log de pago fallido
        DB::table('logs_pagos')->insert([
            'id_user'          => $item->id_user ?? auth()->id() ?? 0,
            'custom_order_id'  => $orderNumber,
            'provider'         => 'azul_talento_redirect',
            'transaction_type' => 'talento_failed',
            'amount'           => $monto,
            'request_payload'  => json_encode([]),
            'response_payload' => json_encode($request->all()),
            'is_success'       => false,
            'error_message'    => $request->input('ErrorDescription') ?? 'Pago declinado por el banco',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $errorMsg = $request->input('ErrorDescription') ?? $request->input('ResponseMessage') ?? 'El pago fue declinado por el banco emisor.';
        return $this->mostrarVistaResultado(false, $errorMsg);
    }

    /**
     * Callback invocado cuando el usuario cancela la operación de pago.
     */
    public function pagoCancelado(Request $request)
    {
        Log::info('[Talento Redirect] Operación cancelada por el usuario');
        return $this->mostrarVistaResultado(false, 'Has cancelado el proceso de pago. Tu talento no ha sido publicado.');
    }

    /**
     * Renderiza una vista de confirmación que funciona para la Web y es amigable con la App móvil.
     */
    private function mostrarVistaResultado(bool $success, string $message, ?Item $item = null)
    {
        $isMobile = request()->segment(3) === 'movil' || request()->has('mobile') || !auth()->check();

        if ($isMobile) {
            // Mostrar página web limpia con mensaje claro de éxito/error y botón de regresar para la app
            return view('pago.resultado_movil', [
                'success' => $success,
                'message' => $message,
                'title'   => 'Pago de Talento'
            ]);
        }

        if ($success) {
            $pagoTalento = $item ? \App\Models\PagoRegistroTalento::where('id_item', $item->id_item)->orderBy('id', 'desc')->first() : null;
            $orderCompletedId = $pagoTalento ? ('TAL-' . $item->id_item . '-' . $pagoTalento->id) : null;

            return redirect()->route('historial')
                ->with('success', $message)
                ->with('order_completed_id', $orderCompletedId);
        } else {
            return redirect()->route('items.admintalento')->with('error', $message);
        }
    }

    public function iniciarRecargaWeb(Request $request, int $id_item)
    {
        $userId = auth()->id();
        $cantidad = (int) $request->query('cantidad', 1);
        if ($cantidad < 1) $cantidad = 1;

        $item = Item::where('id_item', $id_item)->where('id_user', $userId)->first();
        if (!$item) {
            return redirect()->route('items.admintalento')->with('error', 'No se encontró el talento especificado.');
        }

        return $this->procesarRedireccionRecarga($item, $userId, 'azul_talento_web', $cantidad);
    }

    public function iniciarRecargaMovil(Request $request, int $id_item)
    {
        $cantidad = (int) $request->query('cantidad', 1);
        if ($cantidad < 1) $cantidad = 1;

        $item = Item::where('id_item', $id_item)->first();
        if (!$item) {
            return response('Talento no encontrado.', 404);
        }

        return $this->procesarRedireccionRecarga($item, $item->id_user, 'azul_talento_movil', $cantidad);
    }

    private function procesarRedireccionRecarga(Item $item, int $userId, string $provider, int $cantidad)
    {
        if ((int)$item->id_categoria_item !== 29) {
            return redirect()->route('items.admintalento')->with('error', 'El ítem seleccionado no es de la categoría de talentos.');
        }

        $config = ConfigTarifaCategoria29::vigente();
        $monto = (float) $config->monto_registro * $cantidad;

        if ($monto <= 0) {
            if ($item->inventarios) {
                $item->inventarios->increment('cantidad', $cantidad);
            } else {
                \App\Models\Inventario::create([
                    'id_item' => $item->id_item,
                    'cantidad' => $cantidad,
                    'fecha' => now(),
                ]);
            }
            $item->update(['estatus' => 1]);

            $isMobile = ($provider === 'azul_talento_movil');
            if ($isMobile) {
                return view('pago.resultado_movil', [
                    'success' => true,
                    'message' => 'Publicaciones aumentadas con éxito.',
                    'title'   => 'Recarga de Talento'
                ]);
            }
            return redirect()->route('items.admintalento')->with('success', 'Publicaciones aumentadas correctamente.');
        }

        $isMobile = ($provider === 'azul_talento_movil');
        $queryParam = $isMobile ? '?mobile=1' : '';

        $approvedUrl = route('talento.pago.aprobado') . $queryParam;
        $declinedUrl = route('talento.pago.declinado') . $queryParam;
        $cancelUrl   = route('talento.pago.cancelado') . $queryParam;

        $clientHost = request()->getHttpHost();
        $approvedUrl = str_replace(['127.0.0.1:8000', 'localhost:8000', '127.0.0.1', 'localhost'], $clientHost, $approvedUrl);
        $declinedUrl = str_replace(['127.0.0.1:8000', 'localhost:8000', '127.0.0.1', 'localhost'], $clientHost, $declinedUrl);
        $cancelUrl   = str_replace(['127.0.0.1:8000', 'localhost:8000', '127.0.0.1', 'localhost'], $clientHost, $cancelUrl);

        $orderNumber = 'TALREC-' . $item->id_item . '-' . $cantidad . '-' . time();
        $azulData = $this->azulProvider->generarCamposFormulario($monto, $orderNumber, [
            'approved_url' => $approvedUrl,
            'declined_url' => $declinedUrl,
            'cancel_url'   => $cancelUrl,
        ]);

        DB::table('logs_pagos')->insert([
            'id_user'          => $userId,
            'custom_order_id'  => $orderNumber,
            'provider'         => $provider,
            'transaction_type' => 'talento_init',
            'amount'           => $monto,
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
}
