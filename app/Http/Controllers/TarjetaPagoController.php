<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTarjetaRequest;
use App\Models\TarjetaPago;
use App\Services\TarjetaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================
 * TarjetaPagoController — Gestión de tarjetas de pago
 * ============================================================
 *
 * CRUD de tarjetas del usuario. Soporta múltiples tarjetas
 * con selección de tarjeta activa para pagos. Compatible con
 * CardNet (producción) y Stripe (sandbox).
 *
 * Toda la lógica está delegada a TarjetaService.
 *
 * Rutas: /carrito/tarjetas/*
 * Middleware: auth
 * ============================================================
 */
class TarjetaPagoController extends Controller
{
    public function __construct(
        private TarjetaService $tarjetaService,
    ) {}

    public function index()
    {
        $resultado = $this->tarjetaService->listar(auth()->id());
        return response()->json($resultado['data']);
    }

    public function store(StoreTarjetaRequest $request)
    {
        try {
            $resultado = $this->tarjetaService->registrar(auth()->id(), $request->validated());

            return response()->json($resultado, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos: ' . implode(', ', array_merge(...array_values($e->errors()))),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error store() tarjeta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la tarjeta: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function usarEstaTarjeta(Request $request)
    {
        $request->validate([
            'id_tarjeta' => 'required|exists:tarjetas_pagos,id_tarjeta',
        ]);

        $resultado = $this->tarjetaService->usarEstaTarjeta(auth()->id(), $request->id_tarjeta);
        $status = $resultado['success'] ? 200 : 404;

        return response()->json($resultado, $status);
    }

    public function show($id)
    {
        return TarjetaPago::where('id_tarjeta', $id)
            ->where('id_user', auth()->id())
            ->firstOrFail();
    }

    public function update(Request $request, $id)
    {
        $tarjeta = TarjetaPago::where('id_tarjeta', $id)
            ->where('id_user', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'nombre_titular'   => 'sometimes|string|max:255',
            'banco_tarjeta'    => 'sometimes|string|max:100',
            'tipo_tarjeta'     => 'sometimes|string|max:50',
            'usar_esta_tarjeta'=> 'sometimes|boolean',
        ]);

        $tarjeta->update($validated);
        return response()->json(['success' => true, 'data' => $tarjeta]);
    }

    public function destroy($id)
    {
        try {
            $resultado = $this->tarjetaService->eliminar(auth()->id(), $id);
            $status = $resultado['success'] ? 200 : 404;

            return response()->json($resultado, $status);
        } catch (\Exception $e) {
            Log::error('Error destroy() tarjeta: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar la tarjeta.'], 500);
        }
    }
}
