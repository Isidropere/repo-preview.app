<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryZonasSeeder extends Seeder
{
    public function run(): void
    {
        // Porcentajes de ganancia del negocio (3 registros configurables)
        DB::table('delivery_config')->insertOrIgnore([
            ['clave' => 'cortas',    'porcentaje' => 36.00, 'descripcion' => 'Zonas cortas - porcentaje de utilidad', 'created_at' => now(), 'updated_at' => now()],
            ['clave' => 'largas',    'porcentaje' => 46.00, 'descripcion' => 'Zonas largas - porcentaje de utilidad', 'created_at' => now(), 'updated_at' => now()],
            ['clave' => 'especiales','porcentaje' => 21.00, 'descripcion' => 'Zonas especiales - porcentaje de utilidad', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $zonas = [
            // ── CIBAO ──────────────────────────────────────────────────────────────
            [
                'zona'           => 'Cibao Corto',
                'tipo'           => 'corta',
                'pueblos'        => json_encode(['Bonao', 'La Vega', 'Salcedo', 'San Francisco de Macorís', 'Villa Altagracia']),
                'precio_empresa' => 180.00,
                'precio_persona' => 220.00,
                'dias_entrega'   => 'Lunes a Viernes',
            ],
            [
                'zona'           => 'Cibao Largo',
                'tipo'           => 'larga',
                'pueblos'        => json_encode(['Cabarete', 'Cabrera', 'Constanza', 'Cotuí', 'Esperanza', 'Fantino', 'Gaspar Hernández', 'Jarabacoa', 'Maimón', 'Monción', 'Pimentel', 'Nagua', 'Navarrete', 'Presa de Taveras', 'Puerto Plata', 'Río San Juan', 'Sánchez', 'Sosúa', 'Santiago Rodríguez', 'Tamboril', 'Valverde Mao', 'Villa González', 'Villa Vásquez']),
                'precio_empresa' => 200.00,
                'precio_persona' => 240.00,
                'dias_entrega'   => 'Lunes a Viernes',
            ],
            // ── SUR ───────────────────────────────────────────────────────────────
            [
                'zona'           => 'Sur Corto',
                'tipo'           => 'corta',
                'pueblos'        => json_encode(['Baní', 'Haina', 'San Cristóbal']),
                'precio_empresa' => 180.00,
                'precio_persona' => 220.00,
                'dias_entrega'   => 'Lunes a Viernes',
            ],
            [
                'zona'           => 'Sur Largo',
                'tipo'           => 'larga',
                'pueblos'        => json_encode(['Azua', 'Barahona', 'Cabral', 'San José de Ocoa', 'San Juan de la Maguana', 'Tamayo', 'Vicente Noble']),
                'precio_empresa' => 200.00,
                'precio_persona' => 240.00,
                'dias_entrega'   => 'Lunes a Viernes',
            ],
            // ── ESTE ──────────────────────────────────────────────────────────────
            [
                'zona'           => 'Este Corto',
                'tipo'           => 'corta',
                'pueblos'        => json_encode(['Boca Chica', 'La Caleta', 'San Pedro de Macorís']),
                'precio_empresa' => 180.00,
                'precio_persona' => 220.00,
                'dias_entrega'   => 'Lunes a Viernes',
            ],
            [
                'zona'           => 'Este Largo',
                'tipo'           => 'larga',
                'pueblos'        => json_encode(['Bayaguana', 'El Seibo', 'Guerra', 'Hato Mayor', 'Higüey', 'La Romana', 'Monte Plata', 'Sabana Grande de Boyá', 'Yamasá']),
                'precio_empresa' => 200.00,
                'precio_persona' => 240.00,
                'dias_entrega'   => 'Lunes a Viernes',
            ],
            // ── ESPECIALES ────────────────────────────────────────────────────────
            [
                'zona'           => 'Especiales',
                'tipo'           => 'especial',
                'pueblos'        => json_encode(['Bávaro', 'Enriquillo', 'Jimani', 'La Descubierta', 'Las Galeras', 'Las Terrenas', 'Miches', 'Pedernales', 'Sabana de la Mar', 'Duverge', 'El Limón', 'Samaná', 'Loma de Cabrera', 'Dajabón', 'Monte Cristi']),
                'precio_empresa' => 220.00,
                'precio_persona' => 290.00,
                'dias_entrega'   => 'Variable',
            ],
            // ── BULTOS CHEQUEADOS ─────────────────────────────────────────────────
            [
                'zona'           => 'Bultos Chequeados',
                'tipo'           => 'chequeado',
                'pueblos'        => json_encode([]),
                'precio_empresa' => 320.00,
                'precio_persona' => 390.00,
                'dias_entrega'   => 'Lunes a Viernes',
            ],
        ];

        foreach ($zonas as $zona) {
            DB::table('delivery_zonas')->insertOrIgnore(array_merge($zona, [
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
