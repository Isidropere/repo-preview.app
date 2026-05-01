<?php

namespace App\Http\Controllers;

use App\Models\Negociacion;
use App\Services\NegociacionService;
use Illuminate\Http\Request;

/**
 * ============================================================
 * NEGOCIACIONES (INTERCAMBIOS)
 * ============================================================
 *
 * ACTORES:
 *   Emisor   → usuario que QUIERE el artículo ajeno y propone el intercambio
 *   Receptor → usuario DUEÑO del artículo solicitado
 *
 * ESTADOS:
 *   Inicial → aceptado → completado
 *           → contraoferta → aceptado / rechazado
 *           → rechazado
 *           → cancelado (por el emisor)
 * ============================================================
 */
class NegociacionController extends Controller
{
    public function __construct(
        private NegociacionService $negociacionService,
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id'         => 'required|exists:items,id_item',
            'mensaje'         => 'required|string|max:500',
            'paquete_id'      => 'nullable|integer|exists:paquetes,id_paquete',
            'monto_oferta'    => 'nullable|numeric|min:0',
            'items_ofrecidos' => 'nullable|array',
            'items_ofrecidos.*' => 'integer|exists:items,id_item',
        ]);

        $resultado = $this->negociacionService->crear(auth()->id(), $validated);

        $status = $resultado['success'] ? 200 : 422;
        return response()->json([
            'status'  => $resultado['success'] ? 'ok' : 'error',
            'message' => $resultado['message'],
        ], $status);
    }

    public function enviarMensaje(Request $request, $id)
    {
        $validated = $request->validate([
            'mensaje'     => 'required|string|max:500',
            'tipo_accion' => 'nullable|string',
        ]);

        $negociacion = Negociacion::findOrFail($id);
        $userId = auth()->id();

        // Verify user is either emisor or receptor
        if ($userId != $negociacion->usuario_emisor_id && $userId != $negociacion->usuario_receptor_id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para enviar mensajes en esta negociación.',
            ], 403);
        }

        // Verify negociacion is in an active state
        $estadosActivos = ['Inicial', 'aceptado', 'contraoferta'];
        if (!in_array($negociacion->estado, $estadosActivos)) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden enviar mensajes en una negociación en estado "' . $negociacion->estado . '".',
            ], 422);
        }

        // Determine the receptor of the message (the other party)
        $receptorId = $userId == $negociacion->usuario_emisor_id
            ? $negociacion->usuario_receptor_id
            : $negociacion->usuario_emisor_id;

        $this->negociacionService->crearMensaje(
            $userId,
            $receptorId,
            $negociacion->receptor_item_id,
            $negociacion->emisor_paquete_id,
            $validated['mensaje']
        );

        return response()->json([
            'success' => true,
            'message' => 'Mensaje enviado correctamente.',
        ]);
    }

    public function getNegociaciones($itemId)
    {
        $data = $this->negociacionService->obtenerNegociaciones(auth()->id(), $itemId);
        return response()->json($data);
    }

    public function obtenerMensajes($id_emisor, $id_receptor)
    {
        if (!auth()->id()) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $data = $this->negociacionService->obtenerMensajes(auth()->id(), $id_emisor, $id_receptor);
        return response()->json($data);
    }

    public function aceptar($id)
    {
        $resultado = $this->negociacionService->aceptar(auth()->id(), $id);
        if (request()->wantsJson()) {
            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        }
        return back()->with($resultado['success'] ? 'success' : 'error', $resultado['message']);
    }

    public function rechazar(Request $request, $id)
    {
        $resultado = $this->negociacionService->rechazar(auth()->id(), $id);
        if ($request->wantsJson()) {
            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        }
        return back()->with($resultado['success'] ? 'success' : 'error', $resultado['message']);
    }

    public function cancelar($id)
    {
        $resultado = $this->negociacionService->cancelar(auth()->id(), $id);
        if (request()->wantsJson()) {
            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        }
        return back()->with($resultado['success'] ? 'success' : 'error', $resultado['message']);
    }

    public function completar($id)
    {
        $resultado = $this->negociacionService->completar(auth()->id(), $id);
        if (request()->wantsJson()) {
            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        }
        return back()->with($resultado['success'] ? 'success' : 'error', $resultado['message']);
    }

    public function confirmarEmisor($id)
    {
        $resultado = $this->negociacionService->confirmarEmisor(auth()->id(), $id);
        if (request()->wantsJson()) {
            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        }
        return back()->with($resultado['success'] ? 'success' : 'error', $resultado['message']);
    }

    public function confirmarReceptor($id)
    {
        $resultado = $this->negociacionService->confirmarReceptor(auth()->id(), $id);
        if (($resultado['code'] ?? null) === 403) {
            $msg = ['success' => false, 'message' => $resultado['message']];
            return request()->wantsJson() ? response()->json($msg, 403) : back()->with('error', $msg['message']);
        }
        $msg = $resultado;
        return request()->wantsJson() ? response()->json($msg, $msg['success'] ? 200 : 422) : back()->with($msg['success'] ? 'success' : 'error', $msg['message']);
    }

    public function aceptarComoEmisor($id)
    {
        $resultado = $this->negociacionService->aceptarComoEmisor(auth()->id(), $id);
        if (request()->wantsJson()) {
            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        }
        return back()->with($resultado['success'] ? 'success' : 'error', $resultado['message']);
    }

    public function seleccionarModoEntrega(Request $request, $id)
    {
        $request->validate(['modo' => 'required|in:envio,retiro']);
        $resultado = $this->negociacionService->seleccionarModoEntrega(auth()->id(), $id, $request->modo);
        if ($request->wantsJson()) {
            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        }
        return back()->with($resultado['success'] ? 'success' : 'error', $resultado['message']);
    }

    public function confirmarEntrega($id)
    {
        $resultado = $this->negociacionService->confirmarEntrega(auth()->id(), $id);
        if (request()->wantsJson()) {
            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        }
        return back()->with($resultado['success'] ? 'success' : 'error', $resultado['message']);
    }

    public function index($item)
    {
        try {
            // El slug tiene formato: nombre-HASH — extraer el hash del final
            $parts = explode('-', $item);
            $hash  = end($parts);
            $id    = \App\Helpers\HashIdHelper::decode($hash);

            $itemModel = \App\Models\Item::with(['imagenes', 'usuario'])
                ->findOrFail($id);

            $userId = auth()->id();
            $mensajesPredefinidos = \App\Models\PredefinedMessage::all();
            $accion = \App\Models\PredefinedMessage::select('tipo')->distinct()->get();
            $todoLosPaquetes = \App\Models\Paquete::where('id_user', $userId)->get();

            return view('negociaciones.index', compact('itemModel', 'mensajesPredefinidos', 'accion', 'todoLosPaquetes'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[NegociacionController::index] ERROR: ' . $e->getMessage());
            abort(500, $e->getMessage());
        }
    }

    public function misIntercambios()
    {
        $userId = auth()->id();

        $comoEmisor = \App\Models\Negociacion::where('usuario_emisor_id', $userId)
            ->whereNotIn('estado', ['cancelado'])
            ->with(['item.imagenes', 'item.categoria', 'usuarioReceptor', 'item.inventarios'])
            ->orderByDesc('id_negociacion')
            ->get();

        $comoReceptor = \App\Models\Negociacion::where('usuario_receptor_id', $userId)
            ->whereNotIn('estado', ['cancelado'])
            ->with(['item.imagenes', 'item.categoria', 'usuario', 'item.inventarios'])
            ->orderByDesc('id_negociacion')
            ->get();

        $tarjetas = \App\Models\TarjetaPago::where('id_user', $userId)->where('estatus', 1)->get();

        // Calcular costo de envío para cada negociación que lo requiera
        $direccion = \App\Models\Direcciones::where('id_user', $userId)
            ->with('municipio')
            ->first();

        $costoEnvioPorNeg = ['_municipio' => ''];
        if ($direccion && $direccion->municipio) {
            $deliveryService = app(\App\Services\DeliveryService::class);
            $municipio = $direccion->municipio->municipio ?? '';
            $costoEnvioPorNeg['_municipio'] = $municipio;

            $todasNegs = $comoEmisor->merge($comoReceptor);
            foreach ($todasNegs as $neg) {
                if ($neg->item) {
                    // Intercambio: valor_articulo = 0 (no se cobra seguro sobre el valor)
                    $resultado = $deliveryService->calcular($municipio, 'persona', 0);
                    $costoEnvioPorNeg[$neg->id_negociacion] = $resultado['success'] ? ($resultado['costo_envio_total'] ?? 0) : 0;
                } else {
                    $costoEnvioPorNeg[$neg->id_negociacion] = 0;
                }
            }
        }

        $mensajesPredefinidos = \App\Models\PredefinedMessage::where('activo', true)->get();
        $accionesPredefinidas = \App\Models\PredefinedMessage::where('activo', true)->select('tipo')->distinct()->pluck('tipo');

        return view('negociaciones.mis-intercambios', compact('comoEmisor', 'comoReceptor', 'tarjetas', 'costoEnvioPorNeg', 'mensajesPredefinidos', 'accionesPredefinidas'));
    }

    public function contarPendientes()
    {
        $userId = auth()->id();
        $count = \App\Models\Negociacion::where('usuario_receptor_id', $userId)
            ->whereIn('estado', ['Inicial', 'contraoferta'])
            ->count();

        return response()->json(['count' => $count]);
    }

    public function storeContraoferta(Request $request, $id)
    {
        $validated = $request->validate([
            'monto_contra_oferta' => 'nullable|numeric|min:0',
            'mensaje'             => 'nullable|string|max:500',
        ]);

        $resultado = $this->negociacionService->contraoferta(auth()->id(), $id, $validated);
        if ($request->wantsJson()) {
            return response()->json($resultado, $resultado['success'] ? 200 : 422);
        }
        return back()->with($resultado['success'] ? 'success' : 'error', $resultado['message']);
    }

    public function verChat($id)
    {
        $negociacion = Negociacion::with(['usuario', 'usuarioReceptor'])->findOrFail($id);
        return view('negociaciones', compact('negociacion'));
    }

    public function mostrarPago($id)
    {
        $neg = Negociacion::with(['item', 'usuario', 'usuarioReceptor'])->findOrFail($id);
        $userId = auth()->id();

        if ($userId != $neg->usuario_emisor_id && $userId != $neg->usuario_receptor_id) {
            abort(403);
        }

        if ($neg->estado !== 'aceptado' || !$neg->emisor_confirmado) {
            return redirect()->route('negociaciones.mis')
                ->with('error', 'El intercambio no está listo para pago.');
        }

        $tarjetas = \App\Models\TarjetaPago::where('id_user', $userId)->where('estatus', 1)->get();
        $monto    = $neg->monto_oferta ?? 0;

        return view('negociaciones.pago', compact('neg', 'tarjetas', 'monto'));
    }

    public function procesarPago(\Illuminate\Http\Request $request, $id)
    {
        $neg    = Negociacion::findOrFail($id);
        $userId = auth()->id();

        if ($userId != $neg->usuario_emisor_id && $userId != $neg->usuario_receptor_id) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
            abort(403);
        }

        if (!$neg->emisor_confirmado || !($neg->receptor_confirmado ?? false)) {
            $msg = 'Ambas partes deben aprobar antes de continuar.';
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => $msg], 422);
            return back()->with('error', $msg);
        }

        $campo = $userId == $neg->usuario_emisor_id ? 'pago_emisor' : 'pago_receptor';

        // Sin pago: marcar como pagado sin cobrar (servicio o aprobación sin pago)
        if ($request->input('sin_pago')) {
            $neg->update([$campo => true]);

            \App\Models\PagoEnvioIntercambio::create([
                'id_negociacion' => $neg->id_negociacion,
                'id_user'        => $userId,
                'monto'          => 0,
                'tipo_pago'      => 'sin_pago',
                'estado'         => 'pagado',
            ]);

            $negFresh = $neg->fresh();
            if ($negFresh->pago_emisor && $negFresh->pago_receptor) {
                $neg->update(['estado' => 'completado']);
                $this->negociacionService->notificarAdminsCompletado($neg);
            }

            $msg = 'Aprobado correctamente.';
            if ($request->wantsJson()) return response()->json(['success' => true, 'message' => $msg]);
            return redirect()->route('negociaciones.mis')->with('success', $msg);
        }

        // Con pago: verificar dirección
        $tieneDireccion = \App\Models\Direcciones::where('id_user', $userId)->exists();
        if (!$tieneDireccion) {
            $msg = 'Debes registrar una dirección de envío antes de pagar.';
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => $msg, 'redirect' => route('direcciones.index')], 422);
            return redirect()->route('direcciones.index')->with('error', $msg);
        }

        // Con pago: validar tarjeta
        $request->validate([
            'id_tarjeta' => 'required|string|exists:tarjetas_pagos,id_tarjeta',
            'cvv'        => 'nullable|string|max:4',
        ]);

        if ($neg->monto_oferta > 0 || $request->input('monto_envio')) {
            // Calcular el monto real de envío
            $montoACobrar = 0;
            $direccion = \App\Models\Direcciones::where('id_user', $userId)->with('municipio')->first();
            if ($direccion && $direccion->municipio && $neg->item) {
                $deliveryService = app(\App\Services\DeliveryService::class);
                $resultado = $deliveryService->calcular($direccion->municipio->municipio ?? '', 'persona', 0);
                $montoACobrar = $resultado['success'] ? ($resultado['costo_envio_total'] ?? 0) : 0;
            }

            if ($montoACobrar > 0) {
                $tarjeta = \App\Models\TarjetaPago::where('id_tarjeta', $request->id_tarjeta)
                    ->where('id_user', $userId)->firstOrFail();

                $pagoService = app(\App\Services\PagoService::class);
                $resultado   = $pagoService->cobrarTarjeta(
                    (float) $montoACobrar, '214',
                    $tarjeta->datosCardnet($request->cvv),
                    ['client_ip' => $request->ip(), 'invoice_number' => 'INT' . $neg->id_negociacion . $userId]
                );

                if (!$resultado['success']) {
                    $msg = 'Pago rechazado: ' . $resultado['error'];
                    if ($request->wantsJson()) return response()->json(['success' => false, 'message' => $msg], 422);
                    return back()->with('error', $msg);
                }
            }
        }

        $neg->update([$campo => true]);

        // Registrar en pago_envio_intercambio
        \App\Models\PagoEnvioIntercambio::create([
            'id_negociacion' => $neg->id_negociacion,
            'id_user'        => $userId,
            'monto'          => $montoACobrar ?? 0,
            'tipo_pago'      => 'tarjeta',
            'estado'         => 'pagado',
            'id_tarjeta'     => $request->id_tarjeta ?? null,
            'transaction_id' => $resultado['transaction_id'] ?? null,
            'approval_code'  => $resultado['approval_code'] ?? null,
        ]);

        $negFresh = $neg->fresh();
        if ($negFresh->pago_emisor && $negFresh->pago_receptor) {
            $neg->update(['estado' => 'completado']);
            $this->negociacionService->notificarAdminsCompletado($neg);
        }

        $msg = 'Pago registrado correctamente.';
        if ($request->wantsJson()) return response()->json(['success' => true, 'message' => $msg]);
        return redirect()->route('negociaciones.mis')->with('success', $msg);
    }
}
