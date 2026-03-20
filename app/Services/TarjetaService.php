<?php

namespace App\Services;

use App\Models\TarjetaPago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================
 * TarjetaService — Lógica de negocio de tarjetas de pago
 * ============================================================
 *
 * Gestiona el ciclo de vida de tarjetas: listar, registrar,
 * seleccionar tarjeta activa y eliminar (soft-delete si tiene
 * pagos asociados).
 *
 * Compatible con CardNet (no_tarjeta, mes/año expiración)
 * y Stripe (token-based).
 * ============================================================
 */
class TarjetaService
{
    /**
     * Lista tarjetas activas del usuario.
     */
    public function listar(int $userId): array
    {
        $tarjetas = TarjetaPago::where('id_user', $userId)
            ->where('estatus', 1)
            ->get();

        return ['success' => true, 'data' => $tarjetas];
    }

    /**
     * Registra una nueva tarjeta.
     */
    public function registrar(int $userId, array $datos): array
    {
        $driver = config('services.payment.driver', 'cardnet');

        if ($driver !== 'stripe') {
            $datos['last4'] = substr($datos['no_tarjeta'], -4);
            $datos['mes_expiracion'] = (int) $datos['mes_expiracion'];

            // La columna en BD se llama año_expiracion (con ñ = \u00F1)
            if (isset($datos['anio_expiracion'])) {
                $colAnio = "a\u{00F1}o_expiracion";
                $datos[$colAnio] = (int) $datos['anio_expiracion'];
                unset($datos['anio_expiracion']);
            }
        }

        $datos['estatus'] = 1;
        $datos['id_user'] = $userId;

        $tarjeta = null;
        DB::transaction(function () use ($datos, &$tarjeta) {
            TarjetaPago::where('id_user', $datos['id_user'])
                ->update(['usar_esta_tarjeta' => 0]);
            $datos['usar_esta_tarjeta'] = 1;
            $tarjeta = TarjetaPago::create($datos);
        });

        Log::info('Tarjeta creada', ['id' => $tarjeta->id_tarjeta]);

        return ['success' => true, 'message' => 'Tarjeta registrada correctamente', 'data' => $tarjeta];
    }

    /**
     * Marca una tarjeta como la activa para pagos.
     */
    public function usarEstaTarjeta(int $userId, string $idTarjeta): array
    {
        $tarjeta = TarjetaPago::where('id_tarjeta', $idTarjeta)
            ->where('id_user', $userId)
            ->first();

        if (!$tarjeta) {
            return ['success' => false, 'message' => 'Tarjeta no encontrada.'];
        }

        TarjetaPago::where('id_user', $userId)->update(['usar_esta_tarjeta' => false]);
        $tarjeta->update(['usar_esta_tarjeta' => true]);

        return ['success' => true, 'message' => 'Tarjeta seleccionada.'];
    }

    /**
     * Elimina o desactiva una tarjeta (soft-delete si tiene pagos asociados).
     */
    public function eliminar(int $userId, string $idTarjeta): array
    {
        $tarjeta = TarjetaPago::where('id_tarjeta', $idTarjeta)
            ->where('id_user', $userId)
            ->first();

        if (!$tarjeta) {
            return ['success' => false, 'message' => 'Tarjeta no encontrada.'];
        }

        if ($tarjeta->pagosCompra()->exists()) {
            $tarjeta->update(['estatus' => 0, 'usar_esta_tarjeta' => 0]);
        } else {
            $tarjeta->delete();
        }

        return ['success' => true, 'message' => 'Tarjeta eliminada.'];
    }
}
