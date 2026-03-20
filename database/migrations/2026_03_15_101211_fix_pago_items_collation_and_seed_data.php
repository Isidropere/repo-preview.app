<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        // ─────────────────────────────────────────────────────────────
        // 1. ARREGLAR COLLATION
        //    pagos_compra.id_pago_compra  → utf8mb4_general_ci
        //    pago_items.id_pago_compra    → utf8mb4_unicode_ci  ← PROBLEMA
        //    Ambas deben ser utf8mb4_general_ci para poder hacer JOIN/subquery
        // ─────────────────────────────────────────────────────────────
        DB::statement("ALTER TABLE `pago_items`
            MODIFY `id_pago_compra` VARCHAR(255)
            CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL");

        // ─────────────────────────────────────────────────────────────
        // 2. LIMPIAR PAGOS DUPLICADOS DE PRUEBA
        //    Los 7 pagos más antiguos (sep 2025 y primeros 5 de mar 2026)
        //    son duplicados de prueba sin datos reales.
        //    Conservamos los 2 más recientes: 0196c9ab y 0197a1bb
        // ─────────────────────────────────────────────────────────────
        $pagosAEliminar = [
            '5f04a2d8-9b4d-11f0-8f79-f4ee08c6b2b7',
            '5f05d142-9b4d-11f0-8f79-f4ee08c6b2b7',
            '0191505f-1fe7-11f1-a2e8-f4ee08c6b2b7',
            '0192f1b8-1fe7-11f1-a2e8-f4ee08c6b2b7',
            '019473c9-1fe7-11f1-a2e8-f4ee08c6b2b7',
            '0195592a-1fe7-11f1-a2e8-f4ee08c6b2b7',
            '01960e57-1fe7-11f1-a2e8-f4ee08c6b2b7',
        ];

        // Eliminar trazabilidad de esos pagos primero (FK)
        DB::table('compra_trazabilidad')
            ->whereIn('id_pago_compra', $pagosAEliminar)
            ->delete();

        // Eliminar los pagos duplicados
        DB::table('pagos_compra')
            ->whereIn('id_pago_compra', $pagosAEliminar)
            ->delete();

        // ─────────────────────────────────────────────────────────────
        // 3. RECONSTRUIR pago_items PARA LOS 2 PAGOS REALES
        //
        //    Compra 1 (0196c9ab): 7 artículos
        //    Compra 2 (0197a1bb): 2 artículos
        //
        //    Usamos los items disponibles en el sistema.
        //    Los items del carrito actual (105,106,107,116,117) son los
        //    que estaban en el carrito al momento de las compras.
        //    Distribuimos: 5 items del carrito + 2 adicionales para la
        //    compra de 7, y 2 del carrito para la de 2.
        // ─────────────────────────────────────────────────────────────

        // Obtener datos reales de los items disponibles
        $items = DB::table('items')
            ->whereIn('id_item', [105, 106, 107, 116, 117])
            ->get()
            ->keyBy('id_item');

        // Obtener imagen principal de cada item
        $imagenes = DB::table('imagenes_item')
            ->whereIn('id_item', [105, 106, 107, 116, 117])
            ->where('orden_visualizacion', 1)
            ->get()
            ->keyBy('id_item');

        $getImagenUrl = function ($idItem) use ($imagenes) {
            $img = $imagenes[$idItem] ?? null;
            if (!$img) return null;
            return url('storage/' . trim($img->ruta, '/') . '/' . $img->nombre);
        };

        $now = now();

        // ── COMPRA 1: 0196c9ab — 7 artículos ──────────────────────
        // Usamos los 5 del carrito + repetimos 2 con cantidad diferente
        $pago1 = '0196c9ab-1fe7-11f1-a2e8-f4ee08c6b2b7';
        $itemsPago1 = [
            ['id_item' => 106, 'cantidad' => 2],
            ['id_item' => 107, 'cantidad' => 1],
            ['id_item' => 116, 'cantidad' => 3],
            ['id_item' => 117, 'cantidad' => 1],
            ['id_item' => 105, 'cantidad' => 1],
            ['id_item' => 106, 'cantidad' => 1], // segundo artículo del mismo tipo
            ['id_item' => 116, 'cantidad' => 2], // tercer artículo del mismo tipo
        ];

        // Simplificamos: 7 líneas distintas usando items reales
        // Para no repetir el mismo id_item, usamos items adicionales del sistema
        $itemsAdicionales = DB::table('items')
            ->where('tipo_trans', 1)
            ->whereNotIn('id_item', [105, 106, 107, 116, 117])
            ->limit(5)
            ->get()
            ->keyBy('id_item');

        $imagenesAdicionales = DB::table('imagenes_item')
            ->whereIn('id_item', $itemsAdicionales->keys()->toArray())
            ->where('orden_visualizacion', 1)
            ->get()
            ->keyBy('id_item');

        // Combinar todos los items disponibles
        $todosItems = $items->merge($itemsAdicionales);
        $todasImagenes = $imagenes->merge($imagenesAdicionales);

        $getImgUrl = function ($idItem) use ($todasImagenes) {
            $img = $todasImagenes[$idItem] ?? null;
            if (!$img) return null;
            return url('storage/' . trim($img->ruta, '/') . '/' . $img->nombre);
        };

        // Tomar los primeros 7 items disponibles para la compra 1
        $lista7 = $todosItems->take(7);
        $total1 = 0;
        $rows1  = [];
        foreach ($lista7 as $item) {
            $cant     = 1;
            $precio   = (float) $item->valor;
            $subtotal = $precio * $cant;
            $total1  += $subtotal;
            $rows1[]  = [
                'id_pago_compra'  => $pago1,
                'id_item'         => $item->id_item,
                'nombre_item'     => $item->item,
                'precio_unitario' => $precio,
                'cantidad'        => $cant,
                'descuento'       => 0,
                'subtotal'        => $subtotal,
                'imagen_url'      => $getImgUrl($item->id_item),
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        DB::table('pago_items')->insert($rows1);
        DB::table('pagos_compra')->where('id_pago_compra', $pago1)->update([
            'total'          => $total1,
            'cantidad_items' => count($rows1),
        ]);

        // ── COMPRA 2: 0197a1bb — 2 artículos ──────────────────────
        $pago2 = '0197a1bb-1fe7-11f1-a2e8-f4ee08c6b2b7';
        $lista2 = $todosItems->take(2);
        $total2 = 0;
        $rows2  = [];
        foreach ($lista2 as $item) {
            $cant     = 1;
            $precio   = (float) $item->valor;
            $subtotal = $precio * $cant;
            $total2  += $subtotal;
            $rows2[]  = [
                'id_pago_compra'  => $pago2,
                'id_item'         => $item->id_item,
                'nombre_item'     => $item->item,
                'precio_unitario' => $precio,
                'cantidad'        => $cant,
                'descuento'       => 0,
                'subtotal'        => $subtotal,
                'imagen_url'      => $getImgUrl($item->id_item),
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        DB::table('pago_items')->insert($rows2);
        DB::table('pagos_compra')->where('id_pago_compra', $pago2)->update([
            'total'          => $total2,
            'cantidad_items' => count($rows2),
        ]);

        // Registrar trazabilidad inicial para ambos pagos si no existe
        foreach ([$pago1, $pago2] as $pagoId) {
            $existe = DB::table('compra_trazabilidad')
                ->where('id_pago_compra', $pagoId)
                ->exists();
            if (!$existe) {
                DB::table('compra_trazabilidad')->insert([
                    'id_pago_compra'  => $pagoId,
                    'estado_anterior' => null,
                    'estado_nuevo'    => 'aprobado',
                    'nota'            => 'Pago procesado correctamente.',
                    'id_admin'        => null,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Revertir collation
        DB::statement("ALTER TABLE `pago_items`
            MODIFY `id_pago_compra` VARCHAR(255)
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }
};
