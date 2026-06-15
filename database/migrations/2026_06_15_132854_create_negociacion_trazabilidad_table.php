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
        Schema::create('negociacion_trazabilidad', function (Blueprint $table) {
            $table->id();
            $table->integer('id_negociacion');
            $table->string('estado_anterior')->nullable();
            $table->string('estado_nuevo');
            $table->text('nota')->nullable();
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->timestamps();

            $table->foreign('id_negociacion')->references('id_negociacion')->on('negociaciones')->onDelete('cascade');
            $table->foreign('id_admin')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('negociacion_trazabilidad');
    }
};
