<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Amplía no_tarjeta de VARCHAR(19) a VARCHAR(500) para almacenar
 * el número encriptado con AES-256-CBC (~200 chars en base64).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE tarjetas_pagos MODIFY no_tarjeta VARCHAR(500) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE tarjetas_pagos MODIFY no_tarjeta VARCHAR(19) NULL');
    }
};
