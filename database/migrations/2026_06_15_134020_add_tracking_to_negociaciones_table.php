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
        Schema::table('negociaciones', function (Blueprint $table) {
            $table->string('tracking_code', 100)->nullable()->after('entrega_confirmada');
            $table->string('tracking_url', 255)->nullable()->after('tracking_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('negociaciones', function (Blueprint $table) {
            $table->dropColumn(['tracking_code', 'tracking_url']);
        });
    }
};
