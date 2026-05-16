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
            $table->unsignedInteger('id_color')->nullable()->after('receptor_item_id');
        });

        Schema::table('items_intencion_compra', function (Blueprint $table) {
            $table->unsignedInteger('id_color')->nullable()->after('id_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('negociaciones', function (Blueprint $table) {
            $table->dropColumn('id_color');
        });

        Schema::table('items_intencion_compra', function (Blueprint $table) {
            $table->dropColumn('id_color');
        });
    }
};
