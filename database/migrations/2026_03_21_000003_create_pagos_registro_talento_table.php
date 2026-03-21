<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            CREATE TABLE pagos_registro_talento (
                id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                id_item          INT(11) NOT NULL,
                id_user          BIGINT UNSIGNED NOT NULL,
                transaction_id   VARCHAR(100) NOT NULL,
                monto_pagado     DECIMAL(10,2) NOT NULL,
                estatus          VARCHAR(20) NOT NULL DEFAULT \'aprobado\',
                notas            TEXT NULL,
                created_at       TIMESTAMP NULL,
                updated_at       TIMESTAMP NULL,
                CONSTRAINT fk_prt_item FOREIGN KEY (id_item) REFERENCES items(id_item) ON DELETE CASCADE,
                CONSTRAINT fk_prt_user FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_registro_talento');
    }
};
