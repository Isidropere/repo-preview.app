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
         Schema::table('delivery_config', function (Blueprint $table) {
             $table->decimal('min_peso_lbs', 8, 2)->default(0.00)->after('porcentaje_manejo');
             $table->decimal('min_alto_cm', 8, 2)->default(0.00)->after('min_peso_lbs');
             $table->decimal('min_ancho_cm', 8, 2)->default(0.00)->after('min_alto_cm');
             $table->decimal('min_profundo_cm', 8, 2)->default(0.00)->after('min_ancho_cm');
             $table->decimal('recargo_monto', 10, 2)->default(0.00)->after('min_profundo_cm');
         });
 
         DB::table('delivery_config')->insertOrIgnore([
             'clave'                 => 'sobredimensionado',
             'porcentaje'            => 0.00,
             'porcentaje_plataforma' => 0.00,
             'porcentaje_seguro'     => 0.00,
             'porcentaje_manejo'     => 0.00,
             'min_peso_lbs'          => 40.00, // Límite de 40 libras de ejemplo
             'min_alto_cm'           => 0.00,
             'min_ancho_cm'          => 0.00,
             'min_profundo_cm'       => 0.00,
             'recargo_monto'         => 0.00,
             'descripcion'           => 'Recargo por sobredimensionado o sobrepeso',
             'created_at'            => now(),
             'updated_at'            => now(),
         ]);
     }
 
     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         DB::table('delivery_config')->where('clave', 'sobredimensionado')->delete();
 
         Schema::table('delivery_config', function (Blueprint $table) {
             $table->dropColumn([
                 'min_peso_lbs',
                 'min_alto_cm',
                 'min_ancho_cm',
                 'min_profundo_cm',
                 'recargo_monto'
             ]);
         });
     }
 };

