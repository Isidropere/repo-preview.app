<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'password_defined')) {
                    $table->boolean('password_defined')->default(true)->after('password');
                }
            });

            // Actualizar usuarios existentes que provienen de redes sociales a password_defined = false
            DB::table('users')
                ->whereNotNull('google_id')
                ->orWhereNotNull('facebook_id')
                ->orWhereNotNull('instagram_id')
                ->update(['password_defined' => false]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'password_defined')) {
                    $table->dropColumn('password_defined');
                }
            });
        }
    }
};
