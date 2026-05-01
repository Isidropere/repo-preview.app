<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hojas_vida', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('titulo_profesional', 150);
            $table->text('descripcion_bio');
            $table->text('habilidades');
            $table->text('experiencia');
            $table->string('ubicacion', 200);
            $table->timestamps();

            $table->unique('id_user', 'uk_id_user');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hojas_vida');
    }
};
