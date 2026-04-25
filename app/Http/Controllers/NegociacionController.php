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
        $neg = Negociacion::findOrFail($id);
        $userId = auth()->id();

        if ($userId != $neg->usuario_receptor_id) {
            $msg = ['success' => false, 'message' => 'No tienes permiso.'];
            return request()->wantsJson() ? response()->json($msg, 403) : back()->with('error', $msg['message']);
        }

        if ($neg->estado !== 'aceptado') {
            $msg = ['success' => false, 'message' => 'La negociación no está en estado aceptado.'];
            return request()->wantsJson() ? response()->json($msg, 422) : back()->with('error', $msg['message']);
        }

        $neg->update(['receptor_confirmado' => true]);

        // Si ambos confirmaron, notificar
        if ($neg->emisor_confirmado && $neg->receptor_confirmado) {
            $this->negociacionService->notificarAdminsCompletado($neg);
        }

        $msg = ['success' => true, 'message' => 'Has aprobado el intercambio.'];
        return request()->wantsJson() ? response()->json($msg) : back()->with('success', $msg['message']);
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
            ->with(['item.imagenes', 'usuarioReceptor', 'item.inventarios'])
            ->orderByDesc('id_negociacion')
            ->get();

        $comoReceptor = \App\Models\Negociacion::where('usuario_receptor_id', $userId)
            ->whereNotIn('estado', ['cancelado'])
            ->with(['item.imagenes', 'usuario', 'item.inventarios'])
            ->orderByDesc('id_negociacion')
            ->get();

        $tarjetas = \App\Models\TarjetaPago::where('id_user', $userId)->where('estatus', 1)->get();

        return view('negociaciones.mis-intercambios', compact('comoEmisor', 'comoReceptor', 'tarjetas'));
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
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => $msg, 'redirect' => route('direcciones.create')], 422);
            return redirect()->route('direcciones.create')->with('error', $msg);
        }

        // Con pago: validar tarjeta
        $request->validate([
            'id_tarjeta' => 'required|string|exists:tarjetas_pagos,id_tarjeta',
            'cvv'        => 'nullable|string|max:4',
        ]);

        if ($neg->monto_oferta > 0) {
            $tarjeta = \App\Models\TarjetaPago::where('id_tarjeta', $request->id_tarjeta)
                ->where('id_user', $userId)->firstOrFail();

            $pagoService = app(\App\Services\PagoService::class);
            $resultado   = $pagoService->cobrarTarjeta(
                (float) $neg->monto_oferta, '214',
                $tarjeta->datosCardnet($request->cvv),
                ['client_ip' => $request->ip(), 'invoice_number' => 'INT' . $neg->id_negociacion . $userId]
            );

            if (!$resultado['success']) {
                $msg = 'Pago rechazado: ' . $resultado['error'];
                if ($request->wantsJson()) return response()->json(['success' => false, 'message' => $msg], 422);
                return back()->with('error', $msg);
            }
        }

        $neg->update([$campo => true]);

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
