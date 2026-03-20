<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $extras = [
            'Sur Largo' => ['Neiba', 'Neyba', 'Bahoruco', 'Duvergé', 'Los Ríos', 'Galván', 'Postrer Río', 'Polo', 'Paraíso', 'El Peñón', 'Fundación', 'Las Salinas', 'Nizao', 'Cambita Garabitos', 'Sabana Grande de Palenque', 'San Gregorio de Nigua', 'Yaguate', 'Nigua', 'Palenque'],
            'Sur Corto' => ['Santo Domingo Oeste', 'Los Alcarrizos', 'Pedro Brand', 'La Victoria', 'San Antonio de Guerra'],
            'Cibao Corto' => ['Santiago', 'Licey al Medio', 'Puñal', 'Sabana Iglesia', 'San José de las Matas', 'Jánico', 'Moca', 'Espaillat'],
            'Cibao Largo' => ['Mao', 'Valverde', 'Sabaneta', 'Santiago Rodríguez', 'Dajabón', 'Monte Cristi', 'Loma de Cabrera', 'Restauración', 'San Ignacio de Sabaneta'],
            'Este Corto' => ['Santo Domingo Este', 'Distrito Nacional', 'Santo Domingo', 'San Luis', 'Santo Domingo Norte', 'Villa Mella'],
            'Este Largo' => ['Punta Cana', 'La Altagracia', 'Salvaleón de Higüey'],
        ];

        foreach ($extras as $nombreZona => $nuevos) {
            $zona = DB::table('delivery_zonas')->where('zona', $nombreZona)->first();
            if (!$zona) {
                continue;
            }
            $actuales = json_decode($zona->pueblos, true) ?? [];
            $merged = array_values(array_unique(array_merge($actuales, $nuevos)));
            DB::table('delivery_zonas')->where('id', $zona->id)->update([
                'pueblos' => json_encode($merged, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    public function down(): void
    {
        // No revertible — los pueblos originales están en el seeder
    }
};
