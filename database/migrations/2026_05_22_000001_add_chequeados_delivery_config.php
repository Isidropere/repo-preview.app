<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('delivery_config')->insertOrIgnore([
            'clave'                 => 'chequeados',
            'porcentaje'            => 10.00,
            'porcentaje_plataforma' => 10.00,
            'porcentaje_seguro'     => 10.00,
            'porcentaje_manejo'     => 6.00,
            'descripcion'           => 'Bultos chequeados - porcentajes sobre base proveedor',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('delivery_config')->where('clave', 'chequeados')->delete();
    }
};
