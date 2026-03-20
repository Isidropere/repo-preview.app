<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('items')) {
            return;
        }
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (!Schema::hasTable('items')) {
            return;
        }
        Schema::table('items', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
