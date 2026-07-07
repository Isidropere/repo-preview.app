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
        Schema::table('transporte_articulos', function (Blueprint $table) {
            $table->decimal('precio_pequeno', 10, 2)->default(0)->after('precio_base');
            $table->decimal('precio_mediano', 10, 2)->default(0)->after('precio_pequeno');
            $table->decimal('precio_grande', 10, 2)->default(0)->after('precio_mediano');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transporte_articulos', function (Blueprint $table) {
            $table->dropColumn(['precio_pequeno', 'precio_mediano', 'precio_grande']);
        });
    }
};
