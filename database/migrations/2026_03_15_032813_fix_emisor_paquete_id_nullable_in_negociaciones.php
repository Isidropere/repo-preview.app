<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('negociaciones')) {
            return;
        }
        Schema::table('negociaciones', function (Blueprint $table) {
            // Hacer nullable para permitir negociaciones sin paquete (solo oferta monetaria)
            $table->unsignedInteger('emisor_paquete_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('negociaciones', function (Blueprint $table) {
            $table->unsignedInteger('emisor_paquete_id')->nullable(false)->change();
        });
    }
};
