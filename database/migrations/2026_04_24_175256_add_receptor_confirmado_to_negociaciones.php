<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negociaciones', function (Blueprint $table) {
            $table->boolean('receptor_confirmado')->default(false)->after('emisor_confirmado');
        });
    }

    public function down(): void
    {
        Schema::table('negociaciones', function (Blueprint $table) {
            $table->dropColumn('receptor_confirmado');
        });
    }
};
