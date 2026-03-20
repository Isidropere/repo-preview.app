<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->integer('id_oferta')->nullable()->change();
            $table->integer('id_paquete')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->integer('id_oferta')->nullable(false)->change();
            $table->integer('id_paquete')->nullable(false)->change();
        });
    }
};
