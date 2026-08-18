<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CuentaBancariaUsuario;
use App\Models\RetiroVendedor;
use Illuminate\Support\Facades\Validator;

class BilleteraController extends Controller
{
    /**
     * Muestra el panel principal de la billetera.
     */
    public function index()
    {
        $user = auth()->user();
        
        $balanceDisponible = $user->balance_disponible;
        $cuentas = $user->cuentasBancarias;
        $retiros = RetiroVendedor::with('cuentaBancaria')
            ->where('id_usuario', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('billetera.index', compact('balanceDisponible', 'cuentas', 'retiros'));
    }

    /**
     * Agrega una nueva cuenta bancaria.
     */
    public function agregarCuenta(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'banco' => 'required|string|max:150',
            'tipo_cuenta' => 'required|in:ahorro,corriente',
            'numero_cuenta' => 'required|string|max:50',
            'titular' => 'required|string|max:150',
            'cedula_titular' => 'required|string|max:20',
        ]);

        $cuenta = new CuentaBancariaUsuario();
        $cuenta->id_usuario = $user->id;
        $cuenta->banco = $request->banco;
        $cuenta->tipo_cuenta = $request->tipo_cuenta;
        $cuenta->numero_cuenta = $request->numero_cuenta;
        $cuenta->titular = $request->titular;
        $cuenta->cedula_titular = $request->cedula_titular;
        $cuenta->save();

        return redirect()->back()->with('success', 'Cuenta bancaria agregada exitosamente.');
    }

    /**
     * Elimina una cuenta bancaria.
     */
    public function eliminarCuenta($id)
    {
        $user = auth()->user();
        $cuenta = CuentaBancariaUsuario::where('id', $id)->where('id_usuario', $user->id)->firstOrFail();
        
        // Opcional: Podríamos evitar borrar cuentas que tienen retiros en proceso, 
        // pero la tabla retiros guarda la FK. Usualmente se hace SoftDeletes o no se borra.
        // Asumiendo que el modelo no tiene restricciones estrictas por ahora.
        $cuenta->delete();

        return redirect()->back()->with('success', 'Cuenta bancaria eliminada exitosamente.');
    }

    /**
     * Procesa una solicitud de retiro.
     */
    public function solicitarRetiro(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'monto' => 'required|numeric|min:500',
            'id_cuenta_bancaria' => 'required|exists:cuentas_bancarias_usuarios,id',
        ], [
            'monto.min' => 'El monto mínimo de retiro es de RD$ 500.',
            'id_cuenta_bancaria.exists' => 'La cuenta bancaria seleccionada no es válida.'
        ]);

        // Verificar que la cuenta pertenece al usuario
        $cuenta = CuentaBancariaUsuario::where('id', $request->id_cuenta_bancaria)
            ->where('id_usuario', $user->id)
            ->firstOrFail();

        $montoSolicitado = (float) $request->monto;

        // Verificar fondos suficientes
        if ($user->balance_disponible < $montoSolicitado) {
            return redirect()->back()->with('error', 'No tienes fondos suficientes. Tu balance es de RD$ ' . number_format($user->balance_disponible, 2));
        }

        // Crear la solicitud de retiro
        $retiro = new RetiroVendedor();
        $retiro->id_usuario = $user->id;
        $retiro->id_cuenta_bancaria = $cuenta->id;
        $retiro->monto = $montoSolicitado;
        $retiro->estado = 'pendiente';
        $retiro->save();

        return redirect()->back()->with('success', 'Tu solicitud de retiro ha sido enviada con éxito. Será procesada en breve.');
    }
}
