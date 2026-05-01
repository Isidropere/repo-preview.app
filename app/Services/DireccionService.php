<?php

namespace App\Services;

use App\Models\Direcciones;

/**
 * ============================================================
 * DireccionService — Lógica de negocio de direcciones
 * ============================================================
 *
 * CRUD de direcciones del usuario con soporte para dirección
 * predeterminada (usada en checkout y cálculo de delivery).
 * Todas las operaciones están filtradas por id_user para
 * garantizar que un usuario solo acceda a sus propias direcciones.
 * ============================================================
 */
class DireccionService
{
    public function listar(int $userId)
    {
        return Direcciones::where('id_user', $userId)
            ->with(['provincia', 'municipio'])
            ->get();
    }

    public function crear(int $userId, array $datos): array
    {
        $datos['id_user'] = $userId;

        // Si no tiene ninguna dirección aún, esta es la predeterminada automáticamente
        $tieneDirecciones = Direcciones::where('id_user', $userId)->exists();
        if (!$tieneDirecciones) {
            $datos['es_predeterminada'] = true;
        }

        $direccion = Direcciones::create($datos);

        return ['success' => true, 'data' => $direccion, 'message' => 'Dirección guardada exitosamente'];
    }

    public function actualizar(int $userId, int $direccionId, array $datos): array
    {
        $direccion = Direcciones::where('id_direccion', $direccionId)
            ->where('id_user', $userId)
            ->firstOrFail();

        $direccion->update($datos);

        return ['success' => true, 'data' => $direccion, 'message' => 'Dirección actualizada exitosamente'];
    }

    public function marcarPredeterminada(int $userId, int $direccionId): array
    {
        Direcciones::where('id_user', $userId)->update(['es_predeterminada' => false]);

        $direccion = Direcciones::where('id_user', $userId)->findOrFail($direccionId);
        $direccion->update(['es_predeterminada' => true]);

        return ['success' => true, 'message' => 'Dirección predeterminada actualizada'];
    }

    public function eliminar(int $userId, int $direccionId): array
    {
        $direccion = Direcciones::where('id_user', $userId)->findOrFail($direccionId);
        $direccion->delete();

        return ['success' => true, 'message' => 'Dirección eliminada correctamente'];
    }
}
