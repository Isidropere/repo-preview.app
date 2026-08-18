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
        Schema::create('retiros_vendedor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('users')->onDelete('cascade');
            $table->decimal('monto', 10, 2);
            $table->enum('estado', ['pendiente', 'procesando', 'pagado', 'rechazado'])->default('pendiente');
            $table->string('comprobante_url')->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('id_cuenta_bancaria')->nullable()->constrained('cuentas_bancarias_usuarios')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retiros_vendedor');
    }
};
