<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_banco_empresa', function (Blueprint $table) {
            $table->id();
            $table->string('banco', 100);
            $table->string('numero_cuenta', 50);
            $table->enum('tipo_cuenta', ['ahorro', 'corriente', 'otro']);
            $table->string('titular', 150);
            $table->text('descripcion')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_banco_empresa');
    }
};
