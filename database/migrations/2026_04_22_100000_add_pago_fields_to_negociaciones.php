<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negociaciones', function (Blueprint $table) {
            $table->boolean('pago_emisor')->default(false)->after('items_ofrecidos');
            $table->boolean('pago_receptor')->default(false)->after('pago_emisor');
        });
    }

    public function down(): void
    {
        Schema::table('negociaciones', function (Blueprint $table) {
            $table->dropColumn(['pago_emisor', 'pago_receptor']);
        });
    }
};
