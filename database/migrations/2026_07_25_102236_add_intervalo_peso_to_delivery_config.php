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
        Schema::table('delivery_config', function (Blueprint $table) {
            $table->decimal('intervalo_peso_lbs', 8, 2)->default(0)->after('min_peso_lbs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_config', function (Blueprint $table) {
            $table->dropColumn('intervalo_peso_lbs');
        });
    }
};
