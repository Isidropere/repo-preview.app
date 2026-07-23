<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('provincias', function (Blueprint $table) {
            $table->boolean('activo_entrega')->default(true);
        });

        Schema::table('municipios', function (Blueprint $table) {
            $table->boolean('activo_entrega')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('municipios', function (Blueprint $table) {
            $table->dropColumn('activo_entrega');
        });

        Schema::table('provincias', function (Blueprint $table) {
            $table->dropColumn('activo_entrega');
        });
    }
};
