<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negociaciones', function (Blueprint $table) {
            // true = el emisor confirmó después de que el receptor aceptó
            $table->boolean('emisor_confirmado')->default(false)->after('estado');
            // items que el emisor ofrece (JSON array de id_item)
            $table->json('items_ofrecidos')->nullable()->after('emisor_confirmado');
        });
    }

    public function down(): void
    {
        Schema::table('negociaciones', function (Blueprint $table) {
            $table->dropColumn(['emisor_confirmado', 'items_ofrecidos']);
        });
    }
};
