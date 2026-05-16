<?php

namespace App\Http\Controllers;

use App\Models\ConfigTarifaCategoria29;
use App\Models\TarjetaPago;
use App\Services\TalentoRegistroPagoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TalentoRegistroPagoController extends Controller
{
    public function __construct(
        private TalentoRegistroPagoService $pagoService,
    ) {}

    /**
     * Muestra la vista de pago con tarjeta para registrar el talento.
     */
    public function mostrarPago(): View|RedirectResponse
    {
        if (!session('talento_pendiente_data')) {
            return redirect()->route('items.talento_create')
                ->with('error', 'Los datos del formulario han expirado. Por favor vuelve a completar el formulario.');
        }

        $tarjetas = TarjetaPago::where('id_user', auth()->id())
            ->where('estatus', 1)
            ->get();

        $monto = ConfigTarifaCategoria29::vigente()->monto_registro;

        $direccionesCount = \App\Models\Direcciones::where('id_user', auth()->id())->count();
        return view('talentos.pago-talento', compact('tarjetas', 'monto', 'direccionesCount'));
    }

    /**
     * Procesa el pago con Cardnet y, si es aprobado, crea el talento.
     */
    public function procesarPago(Request $request)
    {
        $request->validate([
            'id_tarjeta' => 'required|string|exists:tarjetas_pagos,id_tarjeta',
            'cvv'        => 'nullable|string|max:4',
        ]);

        $resultado = $this->pagoService->procesarPagoYGuardarTalento(
            userId:    auth()->id(),
            idTarjeta: $request->input('id_tarjeta'),
            cvv:       $request->input('cvv'),
            clientIp:  $request->ip(),
        );

        if ($request->expectsJson()) {
            if (!$resultado['success']) {
                return response()->json(['success' => false, 'message' => $resultado['error']], 422);
            }
            return response()->json([
                'success'  => true,
                'message'  => 'Tu talento fue publicado exitosamente.',
                'redirect' => route('items.admintalento'),
            ]);
        }

        if (!$resultado['success']) {
            return redirect()->back()->with('error', $resultado['error']);
        }

        return redirect()->route('items.admintalento')
            ->with('success', 'Tu talento fue publicado exitosamente.');
    }
}
