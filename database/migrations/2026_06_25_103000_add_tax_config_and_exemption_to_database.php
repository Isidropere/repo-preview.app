<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categorias_item', function (Blueprint $table) {
            $table->boolean('aplica_impuesto')->default(true);
        });

        // Insert tax config rows in delivery_config table
        DB::table('delivery_config')->insertOrIgnore([
            [
                'clave'                 => 'itbis',
                'porcentaje'            => 18.00,
                'porcentaje_plataforma' => 0.00,
                'porcentaje_seguro'     => 0.00,
                'porcentaje_manejo'     => 0.00,
                'descripcion'           => 'Porcentaje de ITBIS para productos',
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'clave'                 => 'isr',
                'porcentaje'            => 10.00,
                'porcentaje_plataforma' => 0.00,
                'porcentaje_seguro'     => 0.00,
                'porcentaje_manejo'     => 0.00,
                'descripcion'           => 'Porcentaje de retención de ISR para servicios',
                'created_at'            => now(),
                'updated_at'            => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categorias_item', function (Blueprint $table) {
            $table->dropColumn('aplica_impuesto');
        });

        DB::table('delivery_config')->whereIn('clave', ['itbis', 'isr'])->delete();
    }
};
