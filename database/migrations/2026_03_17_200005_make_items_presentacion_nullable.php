<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * items.presentacion es NOT NULL pero muchos registros tienen ''.
 * Hacerlo nullable es más correcto semánticamente.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('items')) {
            return;
        }
        Schema::table('items', function (Blueprint $table) {
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('presentacion', 250)->default('')->change();
        });
    }
};
