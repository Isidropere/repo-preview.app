<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos_compra', function (Blueprint $table) {
            $table->string('tracking_code', 100)->nullable()->after('id_direccion');
            $table->string('tracking_url', 500)->nullable()->after('tracking_code');
        });
    }

    public function down(): void
    {
        Schema::table('pagos_compra', function (Blueprint $table) {
            $table->dropColumn(['tracking_code', 'tracking_url']);
        });
    }
};
