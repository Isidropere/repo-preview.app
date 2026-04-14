<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La BD legacy se importó sin AUTO_INCREMENT en las PKs enteras.
 * Esto causaba: "Field 'id' doesn't have a default value" al insertar.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $fixes = [
            'compra_trazabilidad'          => ['id', 'BIGINT UNSIGNED NOT NULL'],
            'pago_items'                   => ['id', 'BIGINT UNSIGNED NOT NULL'],
            'carritos'                     => ['id_carrito', 'INT NOT NULL'],
            'items_intencion_compra'       => ['id_item_intencion_compra', 'INT NOT NULL'],
            'inventario_items'             => ['id_inventario', 'INT UNSIGNED NOT NULL'],
            'direcciones'                  => ['id_direccion', 'INT NOT NULL'],
            'items'                        => ['id_item', 'INT NOT NULL'],
            'categorias_item'              => ['id_categoria_item', 'INT NOT NULL'],
            'colors'                       => ['id_color', 'INT NOT NULL'],
            'deliveries'                   => ['id_delivery', 'INT NOT NULL'],
            'delivery_config'              => ['id', 'BIGINT UNSIGNED NOT NULL'],
            'delivery_zonas'               => ['id', 'BIGINT UNSIGNED NOT NULL'],
            'facturas_transporte_transaccion' => ['id_factura', 'INT NOT NULL'],
            'imagenes_item'                => ['id_imagen', 'INT NOT NULL'],
            'item_color'                   => ['id', 'INT NOT NULL'],
            'item_views'                   => ['id', 'BIGINT UNSIGNED NOT NULL'],
            'items_oferta'                 => ['id_item_oferta', 'INT NOT NULL'],
            'messages'                     => ['id', 'INT UNSIGNED NOT NULL'],
            'negociaciones'                => ['id_negociacion', 'INT NOT NULL'],
            'notas'                        => ['id_nota', 'INT NOT NULL'],
            'notas_detalles'               => ['id_nota_detalle', 'INT NOT NULL'],
            'ofertas'                      => ['id_oferta', 'INT NOT NULL'],
            'paquetes'                     => ['id_paquete', 'INT NOT NULL'],
            'planes'                       => ['id_plan', 'INT NOT NULL'],
            'predefined_messages'          => ['id', 'INT NOT NULL'],
            'proveedores_pago'             => ['id_proveedor_pago', 'INT NOT NULL'],
            'ratings'                      => ['id_rating', 'INT NOT NULL'],
        ];

        foreach ($fixes as $table => [$col, $type]) {
            if (\Schema::hasTable($table)) {
                DB::statement("ALTER TABLE `$table` MODIFY `$col` $type AUTO_INCREMENT");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // No revertimos: quitar AUTO_INCREMENT rompería la app
    }
};
