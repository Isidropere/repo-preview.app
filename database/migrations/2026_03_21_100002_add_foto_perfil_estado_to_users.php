<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('foto_perfil_estado', ['pendiente', 'aprobado', 'rechazado'])
                  ->default('pendiente')
                  ->after('foto_perfil');
            $table->text('foto_perfil_motivo_rechazo')->nullable()->after('foto_perfil_estado');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['foto_perfil_estado', 'foto_perfil_motivo_rechazo']);
        });
    }
};
