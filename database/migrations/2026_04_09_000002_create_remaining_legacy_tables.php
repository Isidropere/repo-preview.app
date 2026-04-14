<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea las tablas legacy que existían en la BD original pero no tenían
 * migración de creación. Usa ifNotExists para no romper entornos existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── categorias_item ────────────────────────────────────────
        if (!Schema::hasTable('categorias_item')) {
            Schema::create('categorias_item', function (Blueprint $table) {
                $table->integer('id_categoria_item')->primary();
                $table->string('categoria', 150);
            });
        }

        // ── colors ─────────────────────────────────────────────────
        if (!Schema::hasTable('colors')) {
            Schema::create('colors', function (Blueprint $table) {
                $table->increments('id_color');
                $table->string('nombre', 50);
                $table->string('codigo_hex', 7);
            });
        }

        // ── items ──────────────────────────────────────────────────
        if (!Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
                $table->increments('id_item');
                $table->string('item', 200);
                $table->integer('id_categoria_item')->nullable()->index();
                $table->decimal('peso_lbs', 8, 2)->default(0);
                $table->decimal('alto_cm', 8, 2)->default(0);
                $table->decimal('ancho_cm', 8, 2)->default(0);
                $table->decimal('profundo_cm', 8, 2)->default(0);
                $table->tinyInteger('estatus')->unsigned()->default(2)->index();
                $table->integer('id_user')->index();
                $table->dateTime('fecha')->nullable()->useCurrent();
                $table->tinyInteger('tipo_trans')->unsigned()->nullable()->index();
                $table->tinyInteger('condicion')->unsigned()->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->integer('id_tipo_item')->nullable()->index();
                $table->decimal('valor', 12, 2)->nullable();
                $table->decimal('descuento', 4, 2)->nullable();
                $table->string('presentacion', 250);
                $table->boolean('tiene_video')->default(false);
            });
        }

        // ── imagenes_item ──────────────────────────────────────────
        if (!Schema::hasTable('imagenes_item')) {
            Schema::create('imagenes_item', function (Blueprint $table) {
                $table->increments('id_imagen');
                $table->string('nombre', 150);
                $table->string('extension', 5)->nullable();
                $table->integer('id_item')->index();
                $table->integer('orden_visualizacion')->nullable()->default(0);
                $table->string('ruta', 255)->nullable();
                $table->string('tipo', 255)->default('imagen');
                $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->nullable()->default('pendiente');
                $table->text('motivo_rechazo')->nullable();
            });
        }

        // ── inventario_items ───────────────────────────────────────
        if (!Schema::hasTable('inventario_items')) {
            Schema::create('inventario_items', function (Blueprint $table) {
                $table->increments('id_inventario');
                $table->integer('id_item')->unsigned();
                $table->integer('cantidad')->default(0);
                $table->timestamp('fecha')->useCurrent();
            });
        }

        // ── item_color ─────────────────────────────────────────────
        if (!Schema::hasTable('item_color')) {
            Schema::create('item_color', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('item_id')->index();
                $table->integer('color_id')->index();
                $table->date('fecha');
                $table->integer('stock');
            });
        }

        // ── item_views ─────────────────────────────────────────────
        if (!Schema::hasTable('item_views')) {
            Schema::create('item_views', function (Blueprint $table) {
                $table->id();
                $table->integer('id_item')->index();
                $table->string('ip_address', 255)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // ── items_oferta ───────────────────────────────────────────
        if (!Schema::hasTable('items_oferta')) {
            Schema::create('items_oferta', function (Blueprint $table) {
                $table->increments('id_item_oferta');
                $table->integer('id_paquete')->nullable()->index();
                $table->dateTime('fecha')->nullable()->useCurrent();
                $table->integer('id_item')->nullable()->index();
            });
        }

        // ── deliveries ─────────────────────────────────────────────
        if (!Schema::hasTable('deliveries')) {
            Schema::create('deliveries', function (Blueprint $table) {
                $table->increments('id_delivery');
                $table->string('empresa', 100);
                $table->string('email', 60);
                $table->string('telefono', 10);
            });
        }

        // ── distritos_municipales ──────────────────────────────────
        if (!Schema::hasTable('distritos_municipales')) {
            Schema::create('distritos_municipales', function (Blueprint $table) {
                $table->string('id_distmunicipal', 8)->primary();
                $table->string('distrito_municipal', 200);
                $table->string('id_municipio', 5)->index();
            });
        }

        // ── direcciones ────────────────────────────────────────────
        if (!Schema::hasTable('direcciones')) {
            Schema::create('direcciones', function (Blueprint $table) {
                $table->increments('id_direccion');
                $table->string('calle', 60);
                $table->string('N_casa_edificio', 100)->nullable();
                $table->string('apto', 15)->nullable();
                $table->string('id_provincia', 5)->index();
                $table->string('id_municipio', 10)->nullable()->index();
                $table->string('geolocalizacion', 100)->nullable();
                $table->integer('id_user')->nullable()->index();
                $table->string('sector', 100)->nullable();
                $table->string('telefono_contacto', 20)->nullable();
                $table->boolean('es_predeterminada')->nullable()->default(false);
            });
        }

        // ── carritos ───────────────────────────────────────────────
        if (!Schema::hasTable('carritos')) {
            Schema::create('carritos', function (Blueprint $table) {
                $table->increments('id_carrito');
                $table->unsignedBigInteger('id_user');
                $table->dateTime('fecha_actualizacion')->nullable()->useCurrent();
                $table->integer('cantidad')->nullable()->default(1);
                $table->timestamps();

                $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // ── items_intencion_compra ─────────────────────────────────
        if (!Schema::hasTable('items_intencion_compra')) {
            Schema::create('items_intencion_compra', function (Blueprint $table) {
                $table->increments('id_item_intencion_compra');
                $table->integer('id_carrito')->index();
                $table->integer('id_item')->index();
                $table->integer('cantidad')->nullable()->default(1);
                $table->boolean('es_seleccionado');
                $table->decimal('descuento', 10, 2);
            });
        }

        // ── facturas_transporte_transaccion ────────────────────────
        if (!Schema::hasTable('facturas_transporte_transaccion')) {
            Schema::create('facturas_transporte_transaccion', function (Blueprint $table) {
                $table->increments('id_factura');
                $table->integer('id_delivery')->index();
                $table->decimal('valor', 10, 2);
                $table->dateTime('fecha')->nullable()->useCurrent();
                $table->integer('id_oferta')->index();
                $table->integer('id_miembro')->index();
                $table->tinyInteger('pagada')->unsigned()->nullable()->default(0);
            });
        }

        // ── messages ───────────────────────────────────────────────
        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('id_emisor')->index();
                $table->unsignedBigInteger('id_receptor')->index();
                $table->integer('id_oferta')->nullable()->index();
                $table->integer('id_paquete')->nullable();
                $table->text('mensaje');
                $table->boolean('leido')->nullable()->default(false);
                $table->timestamps();
            });
        }

        // ── miembros (legacy, solo lectura) ────────────────────────
        if (!Schema::hasTable('miembros')) {
            Schema::create('miembros', function (Blueprint $table) {
                $table->unsignedBigInteger('id_miembro')->primary();
                $table->string('nombres', 60);
                $table->string('apellidos', 60);
                $table->string('email', 60);
                $table->string('telefono', 10);
                $table->integer('id_plan')->default(1)->index();
                $table->string('calle', 60);
                $table->string('casa_numero', 15)->nullable();
                $table->string('apto', 15)->nullable();
                $table->string('edificio', 15)->nullable();
                $table->string('id_provincia', 100)->index();
                $table->string('id_municipio', 100)->nullable()->index();
                $table->string('geolocalizacion', 100)->nullable();
            });
        }

        // ── tipos_item (legacy sin auto_increment) ─────────────────
        if (!Schema::hasTable('tipos_item')) {
            Schema::create('tipos_item', function (Blueprint $table) {
                $table->integer('id_tipo_item');
                $table->string('tipo_item', 100);
                $table->integer('creado_por');
                $table->dateTime('creado_en')->useCurrent();
                $table->dateTime('actualizado_en')->nullable()->useCurrentOnUpdate();
                $table->integer('actualizado_por')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_item');
        Schema::dropIfExists('miembros');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('facturas_transporte_transaccion');
        Schema::dropIfExists('items_intencion_compra');
        Schema::dropIfExists('carritos');
        Schema::dropIfExists('direcciones');
        Schema::dropIfExists('distritos_municipales');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('items_oferta');
        Schema::dropIfExists('item_views');
        Schema::dropIfExists('item_color');
        Schema::dropIfExists('inventario_items');
        Schema::dropIfExists('imagenes_item');
        Schema::dropIfExists('items');
        Schema::dropIfExists('colors');
        Schema::dropIfExists('categorias_item');
    }
};
