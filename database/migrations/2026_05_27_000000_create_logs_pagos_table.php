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
        Schema::create('logs_pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->string('custom_order_id')->nullable();
            $table->string('provider');
            $table->string('transaction_type');
            $table->decimal('amount', 12, 2);
            $table->longText('request_payload');
            $table->longText('response_payload')->nullable();
            $table->boolean('is_success');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('id_user');
            $table->index('custom_order_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_pagos');
    }
};
