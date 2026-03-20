<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pagos_compra')) {
            return;
        }
        Schema::table('pagos_compra', function (Blueprint $table) {
            $table->foreign('id_direccion')
                  ->references('id_direccion')
                  ->on('direcciones')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pagos_compra', function (Blueprint $table) {
            $table->dropForeign(['id_direccion']);
            $table->dropColumn('id_direccion');
        });
    }
};
