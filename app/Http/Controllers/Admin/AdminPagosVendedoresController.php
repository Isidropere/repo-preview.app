<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RetiroVendedor;
use Illuminate\Support\Facades\Storage;
use App\Models\Message;

class AdminPagosVendedoresController extends Controller
{

    public function index(Request $request)
    {
        $estado = $request->query('estado', 'pendiente');
        $fecha_inicio = $request->query('fecha_inicio');
        $fecha_fin = $request->query('fecha_fin');
        
        $retiros = RetiroVendedor::with(['usuario', 'cuentaBancaria'])
            ->when($estado !== 'todos', function ($q) use ($estado) {
                return $q->where('estado', $estado);
            })
            ->when($fecha_inicio, function ($q) use ($fecha_inicio) {
                return $q->whereDate('created_at', '>=', $fecha_inicio);
            })
            ->when($fecha_fin, function ($q) use ($fecha_fin) {
                return $q->whereDate('created_at', '<=', $fecha_fin);
            })
            ->orderBy('created_at', 'desc')
            ->get(); // Cambiado a get() en lugar de paginate() para facilitar la impresión completa de la tabla.

        return view('admin.pagos_vendedores.index', compact('retiros', 'estado', 'fecha_inicio', 'fecha_fin'));
    }

    public function pagar(Request $request, $id)
    {
        $request->validate([
            'comprobante' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'notas' => 'nullable|string'
        ]);

        $retiro = RetiroVendedor::findOrFail($id);

        if ($request->hasFile('comprobante')) {
            $path = $request->file('comprobante')->store('comprobantes_pagos_vendedores', 'public');
            $retiro->comprobante_url = '/storage/' . $path;
        }

        $retiro->estado = 'pagado';
        $retiro->notas = $request->input('notas');
        $retiro->save();

        // Notificar al vendedor
        Message::create([
            'id_receptor' => $retiro->id_usuario,
            'id_remitente' => auth()->id(),
            'mensaje' => "Tu solicitud de retiro de RD$ " . number_format($retiro->monto, 2) . " ha sido pagada a tu cuenta bancaria. Revisa el historial de tu billetera.",
            'estatus' => 1, // no leido
        ]);

        return redirect()->back()->with('success', 'El pago al vendedor ha sido registrado correctamente.');
    }

    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'notas' => 'required|string'
        ]);

        $retiro = RetiroVendedor::findOrFail($id);
        $retiro->estado = 'rechazado';
        $retiro->notas = $request->input('notas');
        $retiro->save();

        // Notificar al vendedor
        Message::create([
            'id_receptor' => $retiro->id_usuario,
            'id_remitente' => auth()->id(),
            'mensaje' => "Tu solicitud de retiro ha sido rechazada. Motivo: " . $request->input('notas'),
            'estatus' => 1,
        ]);

        return redirect()->back()->with('error', 'El retiro ha sido rechazado.');
    }
}
