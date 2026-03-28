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
        Schema::create('application_errors', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->uuid('error_reference')->unique();
            $blueprint->text('message');
            $blueprint->longText('stack_trace')->nullable();
            $blueprint->string('url')->nullable();
            $blueprint->string('method')->nullable();
            $blueprint->unsignedBigInteger('user_id')->nullable();
            $blueprint->string('ip_address')->nullable();
            $blueprint->text('user_agent')->nullable();
            $blueprint->json('input_data')->nullable();
            $blueprint->timestamps();

            $blueprint->index('error_reference');
            $blueprint->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_errors');
    }
};
