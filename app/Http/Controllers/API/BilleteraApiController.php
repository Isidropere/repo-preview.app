<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CuentaBancariaUsuario;
use App\Models\RetiroVendedor;
use Illuminate\Support\Facades\Validator;

class BilleteraApiController extends Controller
{
    /**
     * Obtener el resumen de la billetera del usuario.
     */
    public function resumen(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
        }

        $balanceDisponible = $user->balance_disponible;

        return response()->json([
            'success' => true,
            'data' => [
                'balance_disponible' => $balanceDisponible,
                // Opcional: Podríamos agregar balance en tránsito en el futuro.
            ]
        ]);
    }

    /**
     * Listar las cuentas bancarias del usuario.
     */
    public function cuentasBancarias(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
        }

        $cuentas = $user->cuentasBancarias;

        return response()->json([
            'success' => true,
            'data' => $cuentas
        ]);
    }

    /**
     * Agregar una nueva cuenta bancaria.
     */
    public function agregarCuentaBancaria(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
        }

        $validator = Validator::make($request->all(), [
            'banco' => 'required|string|max:150',
            'tipo_cuenta' => 'required|in:ahorro,corriente',
            'numero_cuenta' => 'required|string|max:50',
            'titular' => 'required|string|max:150',
            'cedula_titular' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $cuenta = new CuentaBancariaUsuario();
        $cuenta->id_usuario = $user->id;
        $cuenta->banco = $request->banco;
        $cuenta->tipo_cuenta = $request->tipo_cuenta;
        $cuenta->numero_cuenta = $request->numero_cuenta;
        $cuenta->titular = $request->titular;
        $cuenta->cedula_titular = $request->cedula_titular;
        $cuenta->save();

        return response()->json([
            'success' => true,
            'message' => 'Cuenta bancaria agregada exitosamente.',
            'data' => $cuenta
        ]);
    }

    /**
     * Eliminar una cuenta bancaria.
     */
    public function eliminarCuentaBancaria($id)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
        }

        $cuenta = CuentaBancariaUsuario::where('id', $id)->where('id_usuario', $user->id)->first();
        if (!$cuenta) {
            return response()->json(['success' => false, 'message' => 'Cuenta no encontrada'], 404);
        }

        $cuenta->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cuenta bancaria eliminada.'
        ]);
    }

    /**
     * Listar el historial de retiros del usuario.
     */
    public function historialRetiros(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
        }

        $retiros = RetiroVendedor::where('id_usuario', $user->id)
            ->with('cuentaBancaria')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $retiros
        ]);
    }

    /**
     * Solicitar un nuevo retiro.
     */
    public function solicitarRetiro(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
        }

        $validator = Validator::make($request->all(), [
            'monto' => 'required|numeric|min:500', // mínimo 500 pesos para retirar, por ejemplo
            'id_cuenta_bancaria' => 'required|exists:cuentas_bancarias_usuarios,id',
        ], [
            'monto.min' => 'El monto mínimo de retiro es de RD$ 500.',
            'id_cuenta_bancaria.exists' => 'La cuenta bancaria seleccionada no es válida.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que la cuenta pertenece al usuario
        $cuenta = CuentaBancariaUsuario::where('id', $request->id_cuenta_bancaria)
            ->where('id_usuario', $user->id)
            ->first();
            
        if (!$cuenta) {
            return response()->json([
                'success' => false,
                'message' => 'La cuenta bancaria seleccionada no te pertenece.'
            ], 403);
        }

        // Verificar fondos disponibles
        $montoSolicitado = (float) $request->monto;
        if ($user->balance_disponible < $montoSolicitado) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes fondos suficientes para realizar este retiro. Tu balance disponible es de RD$ ' . number_format($user->balance_disponible, 2)
            ], 400);
        }

        // Crear la solicitud de retiro
        $retiro = new RetiroVendedor();
        $retiro->id_usuario = $user->id;
        $retiro->id_cuenta_bancaria = $cuenta->id;
        $retiro->monto = $montoSolicitado;
        $retiro->estado = 'pendiente';
        $retiro->save();

        return response()->json([
            'success' => true,
            'message' => 'Tu solicitud de retiro ha sido enviada con éxito.',
            'data' => $retiro
        ]);
    }
}
