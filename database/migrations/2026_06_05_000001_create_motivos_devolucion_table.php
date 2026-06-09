<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear tabla motivos_devolucion
        Schema::create('motivos_devolucion', function (Blueprint $table) {
            $table->id();
            $table->string('motivo');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Seed inicial de motivos
        DB::table('motivos_devolucion')->insert([
            ['motivo' => 'Me arrepentí de la compra', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['motivo' => 'El artículo no coincide con la descripción', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['motivo' => 'Compré el artículo por error', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['motivo' => 'El artículo está defectuoso o no funciona', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['motivo' => 'El envío demoró demasiado', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['motivo' => 'Otro (especificar en comentarios)', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Modificar pagos_compra
        Schema::table('pagos_compra', function (Blueprint $table) {
            $table->unsignedBigInteger('id_motivo_devolucion')->nullable()->after('id_direccion');
            $table->text('comentario_devolucion')->nullable()->after('id_motivo_devolucion');

            $table->foreign('id_motivo_devolucion')->references('id')->on('motivos_devolucion')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pagos_compra', function (Blueprint $table) {
            $table->dropForeign(['id_motivo_devolucion']);
            $table->dropColumn(['id_motivo_devolucion', 'comentario_devolucion']);
        });

        Schema::dropIfExists('motivos_devolucion');
    }
};
