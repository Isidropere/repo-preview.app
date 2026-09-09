<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('imagenes_item')) {
            Schema::table('imagenes_item', function (Blueprint $table) {
                if (!Schema::hasColumn('imagenes_item', 'estado')) {
                    $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])
                          ->default('pendiente');
                }
                if (!Schema::hasColumn('imagenes_item', 'motivo_rechazo')) {
                    $table->text('motivo_rechazo')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('imagenes_item')) {
            Schema::table('imagenes_item', function (Blueprint $table) {
                $table->dropColumn(['estado', 'motivo_rechazo']);
            });
        }
    }
};
