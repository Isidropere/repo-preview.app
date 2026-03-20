<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // tipos_usuarios
        if (!Schema::hasTable('tipos_usuarios')) {
            Schema::create('tipos_usuarios', function (Blueprint $table) {
                $table->increments('id_tipo_usuario');
                $table->string('tipo', 100);
                $table->timestamps();
            });
            \DB::table('tipos_usuarios')->insert([
                ['tipo' => 'Persona', 'created_at' => now(), 'updated_at' => now()],
                ['tipo' => 'Empresa', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // tipos_items
        if (!Schema::hasTable('tipos_items')) {
            Schema::create('tipos_items', function (Blueprint $table) {
                $table->increments('id_tipo_item');
                $table->string('tipo_item', 100);
                $table->integer('creado_por')->nullable();
                $table->timestamps();
            });
        }

        // planes
        if (!Schema::hasTable('planes')) {
            Schema::create('planes', function (Blueprint $table) {
                $table->increments('id_plan');
                $table->string('plan', 100);
                $table->decimal('valor', 10, 2)->default(0);
                $table->timestamps();
            });
            \DB::table('planes')->insert([
                ['plan' => 'Básico', 'valor' => 0, 'created_at' => now(), 'updated_at' => now()],
                ['plan' => 'Premium', 'valor' => 500, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // provincias
        if (!Schema::hasTable('provincias')) {
            Schema::create('provincias', function (Blueprint $table) {
                $table->string('id_provincia', 5)->primary();
                $table->string('provincia', 100);
            });
        }

        // municipios
        if (!Schema::hasTable('municipios')) {
            Schema::create('municipios', function (Blueprint $table) {
                $table->string('id_municipio', 10)->primary();
                $table->string('municipio', 100);
                $table->string('id_provincia', 5);
                $table->foreign('id_provincia')->references('id_provincia')->on('provincias');
            });
        }

        // paquetes
        if (!Schema::hasTable('paquetes')) {
            Schema::create('paquetes', function (Blueprint $table) {
                $table->increments('id_paquete');
                $table->string('nombre_paquete', 255);
                $table->tinyInteger('estatus')->default(1);
                $table->unsignedBigInteger('id_user');
                $table->dateTime('fecha')->nullable();
                $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // ofertas
        if (!Schema::hasTable('ofertas')) {
            Schema::create('ofertas', function (Blueprint $table) {
                $table->bigIncrements('id_oferta');
                $table->unsignedBigInteger('oferente');
                $table->unsignedBigInteger('beneficiario');
                $table->dateTime('fecha')->nullable();
                $table->string('condicion', 20)->default('PENDIENTE');
                $table->unsignedInteger('id_paquete')->nullable();
                $table->foreign('oferente')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('beneficiario')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('id_paquete')->references('id_paquete')->on('paquetes')->onDelete('set null');
            });
        }

        // negociaciones
        if (!Schema::hasTable('negociaciones')) {
            Schema::create('negociaciones', function (Blueprint $table) {
                $table->bigIncrements('id_negociacion');
                $table->unsignedBigInteger('receptor_item_id');
                $table->unsignedInteger('emisor_paquete_id')->nullable();
                $table->unsignedBigInteger('usuario_emisor_id');
                $table->unsignedBigInteger('usuario_receptor_id');
                $table->text('mensaje_inicial')->nullable();
                $table->decimal('monto_oferta', 12, 2)->nullable();
                $table->decimal('monto_contra_oferta', 12, 2)->nullable();
                $table->string('estado', 30)->default('Inicial');
                $table->dateTime('fecha_creacion')->nullable();
                $table->index('usuario_emisor_id');
                $table->index('usuario_receptor_id');
                $table->index('receptor_item_id');
                $table->foreign('usuario_emisor_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('usuario_receptor_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('emisor_paquete_id')->references('id_paquete')->on('paquetes')->onDelete('set null');
            });
        }

        // tarjetas_pagos
        if (!Schema::hasTable('tarjetas_pagos')) {
            Schema::create('tarjetas_pagos', function (Blueprint $table) {
                $table->string('id_tarjeta', 36)->primary();
                $table->string('no_tarjeta', 19)->nullable();
                $table->string('tipo_tarjeta', 50)->nullable();
                $table->string('banco_tarjeta', 100)->nullable();
                $table->integer('mes_expiracion')->nullable();
                $table->integer('año_expiracion')->nullable();
                $table->tinyInteger('estatus')->default(1);
                $table->string('payment_method_id', 100)->nullable();
                $table->string('last4', 4)->nullable();
                $table->string('nombre_titular', 255)->nullable();
                $table->tinyInteger('usar_esta_tarjeta')->default(0);
                $table->unsignedBigInteger('id_user');
                $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // proveedores_pago (requerida por pagos_compra)
        if (!Schema::hasTable('proveedores_pago')) {
            Schema::create('proveedores_pago', function (Blueprint $table) {
                $table->increments('id_proveedor_pago');
                $table->string('proveedor', 100);
                $table->timestamps();
            });
            \DB::table('proveedores_pago')->insert([
                ['proveedor' => 'CardNet', 'created_at' => now(), 'updated_at' => now()],
                ['proveedor' => 'Stripe', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // pagos_compra
        if (!Schema::hasTable('pagos_compra')) {
            Schema::create('pagos_compra', function (Blueprint $table) {
                $table->string('id_pago_compra', 36)->primary();
                $table->unsignedBigInteger('id_carrito')->nullable();
                $table->string('estatus', 30)->default('pendiente');
                $table->string('id_tarjeta', 36)->nullable();
                $table->string('autorizacion_pago', 100)->nullable();
                $table->unsignedInteger('id_proveedor_pago')->nullable();
                $table->string('transaction_id', 100)->nullable();
                $table->decimal('total', 12, 2)->nullable();
                $table->unsignedSmallInteger('cantidad_items')->default(0);
                $table->unsignedInteger('id_direccion')->nullable();
                $table->dateTime('fecha')->nullable();
                $table->index('fecha');
                $table->index('estatus');
            });
        }

        // predefined_messages
        if (!Schema::hasTable('predefined_messages')) {
            Schema::create('predefined_messages', function (Blueprint $table) {
                $table->id();
                $table->string('titulo', 255);
                $table->text('mensaje');
                $table->string('tipo', 50)->nullable()->default('general');
                $table->string('rol', 30)->default('general');
                $table->boolean('activo')->default(true);
                $table->integer('Clasificador')->nullable();
                $table->timestamps();
                $table->unique(['tipo', 'rol', 'titulo'], 'uq_predefined_tipo_rol_titulo');
            });
        }

        // ratings
        if (!Schema::hasTable('ratings')) {
            Schema::create('ratings', function (Blueprint $table) {
                $table->bigIncrements('id_rating');
                $table->decimal('rating', 3, 2)->default(0);
                $table->unsignedBigInteger('id_usuario');
                $table->unsignedBigInteger('id_user_rated')->nullable();
                $table->dateTime('fecha')->nullable();
                $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('id_user_rated')->references('id')->on('users')->onDelete('set null');
            });
        }

        // notas
        if (!Schema::hasTable('notas')) {
            Schema::create('notas', function (Blueprint $table) {
                $table->bigIncrements('id_nota');
                $table->unsignedBigInteger('id_oferta')->nullable();
                $table->boolean('visualizado')->default(false);
                $table->timestamps();
            });
        }

        // notas_detalles
        if (!Schema::hasTable('notas_detalles')) {
            Schema::create('notas_detalles', function (Blueprint $table) {
                $table->bigIncrements('id_nota_detalle');
                $table->text('nota')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_detalles');
        Schema::dropIfExists('notas');
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('predefined_messages');
        Schema::dropIfExists('pagos_compra');
        Schema::dropIfExists('proveedores_pago');
        Schema::dropIfExists('tarjetas_pagos');
        Schema::dropIfExists('negociaciones');
        Schema::dropIfExists('ofertas');
        Schema::dropIfExists('paquetes');
        Schema::dropIfExists('municipios');
        Schema::dropIfExists('provincias');
        Schema::dropIfExists('planes');
        Schema::dropIfExists('tipos_items');
        Schema::dropIfExists('tipos_usuarios');
    }
};
