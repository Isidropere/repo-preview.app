<?php

namespace App\Services;

use App\Models\DeliveryZona;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================
 * DeliveryService — Cálculo de costos de envío
 * ============================================================
 *
 * Gestiona zonas de delivery y calcula costos de envío basado en:
 * - Municipio de origen y destino
 * - Peso y dimensiones del artículo
 * - Tipo de zona (corta, larga, especial)
 * - Porcentajes configurables (ganancia, plataforma, manejo, seguro)
 *
 * Los servicios (talentos, categoría 29) están excluidos del
 * cálculo de delivery — solo aplica a artículos físicos.
 *
 * Configuración en tabla: delivery_config
 * Zonas en tabla: delivery_zonas
 * ============================================================
 */
class DeliveryService
{
    /**
     * Lista zonas activas.
     */
    public function listarZonas(): array
    {
        $zonas = DeliveryZona::where('activo', true)
            ->orderBy('tipo')
            ->get(['id', 'zona', 'tipo', 'pueblos', 'precio_empresa', 'precio_persona', 'dias_entrega']);

        return ['success' => true, 'data' => $zonas];
    }

    /**
     * Calcula costo de envío para un pueblo.
     */
    public function calcular(string $pueblo, string $tipoDestinatario, float $valorArticulo): array
    {
        $pueblo = strtolower(trim($pueblo));

        if (empty($pueblo)) {
            return ['success' => false, 'message' => 'El campo pueblo es requerido.'];
        }

        $zonaEncontrada = $this->buscarZonaPorPueblo($pueblo);

        if (!$zonaEncontrada) {
            return ['success' => false, 'message' => "No se encontró zona de delivery para: {$pueblo}"];
        }

        $precioBase = $tipoDestinatario === 'empresa'
            ? (float) $zonaEncontrada->precio_empresa
            : (float) $zonaEncontrada->precio_persona;

        $config = $this->obtenerConfigZona($zonaEncontrada->tipo);

        $pctGanancia   = $config ? (float) $config->porcentaje            : 0;
        $pctPlataforma = $config ? (float) $config->porcentaje_plataforma : 0;
        $pctSeguro     = $config ? (float) $config->porcentaje_seguro     : 0;
        $pctManejo     = $config ? (float) $config->porcentaje_manejo     : 0;

        $costoFlete      = round($precioBase * (1 + $pctGanancia / 100), 2);
        $costoPlataforma = round($precioBase * ($pctPlataforma / 100), 2);
        $costoSeguro     = round($valorArticulo * ($pctSeguro / 100), 2);
        $costoManejo     = round($precioBase * ($pctManejo / 100), 2);
        $costoTotal      = round($costoFlete + $costoPlataforma + $costoSeguro + $costoManejo, 2);

        $diasHabiles = match($zonaEncontrada->tipo) {
            'corta' => 5, 'larga' => 7, 'especial' => 10, default => 7,
        };

        return [
            'success'           => true,
            'zona'              => $zonaEncontrada->zona,
            'tipo'              => $zonaEncontrada->tipo,
            'dias_entrega'      => $zonaEncontrada->dias_entrega,
            'dias_habiles'      => $diasHabiles,
            'tipo_destinatario' => $tipoDestinatario,
            'valor_articulo'    => $valorArticulo,
            'desglose' => [
                'precio_base_proveedor' => $precioBase,
                'ganancia_negocio_pct'  => $pctGanancia,
                'costo_flete'           => $costoFlete,
                'plataforma_pct'        => $pctPlataforma,
                'costo_plataforma'      => $costoPlataforma,
                'seguro_pct'            => $pctSeguro,
                'costo_seguro'          => $costoSeguro,
                'manejo_pct'            => $pctManejo,
                'costo_manejo'          => $costoManejo,
            ],
            'costo_envio_total' => $costoTotal,
        ];
    }

    /**
     * Obtiene toda la configuración de delivery.
     */
    public function obtenerConfig(): array
    {
        $config = DB::table('delivery_config')->get();
        return ['success' => true, 'data' => $config];
    }

    /**
     * Actualiza porcentajes de una clave de configuración.
     */
    public function actualizarConfig(string $clave, array $datos): array
    {
        $allowed = ['cortas', 'largas', 'especiales'];
        if (!in_array($clave, $allowed)) {
            return ['success' => false, 'message' => 'Clave inválida.'];
        }

        DB::table('delivery_config')->where('clave', $clave)->update(array_merge($datos, [
            'updated_at' => now(),
        ]));

        $updated = DB::table('delivery_config')->where('clave', $clave)->first();
        return ['success' => true, 'data' => $updated];
    }

    // ───────────────────────────────────────────────────────
    // Helpers privados
    // ───────────────────────────────────────────────────────

    private function buscarZonaPorPueblo(string $pueblo): ?DeliveryZona
    {
        foreach (DeliveryZona::where('activo', true)->get() as $zona) {
            foreach ($zona->pueblos as $p) {
                if (str_contains(strtolower($p), $pueblo) || str_contains($pueblo, strtolower($p))) {
                    return $zona;
                }
            }
        }
        return null;
    }

    private function obtenerConfigZona(string $tipo): ?object
    {
        $clave = match($tipo) {
            'corta'    => 'cortas',
            'larga'    => 'largas',
            'especial' => 'especiales',
            default    => $tipo,
        };

        return DB::table('delivery_config')->where('clave', $clave)->first();
    }
}
