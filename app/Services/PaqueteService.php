<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemOferta;
use App\Models\Paquete;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaqueteService
{
    /**
     * Lista paquetes del usuario.
     */
    public function listar(int $userId): array
    {
        $paquetes = Paquete::where('id_user', $userId)
            ->select('id_paquete', 'nombre_paquete', 'estatus', 'fecha')
            ->orderByDesc('fecha')
            ->get();

        return ['success' => true, 'data' => $paquetes];
    }

    /**
     * Obtiene un paquete con sus items y todos los items del usuario.
     */
    public function obtener(int $userId, int $paqueteId): array
    {
        $paquete = Paquete::where('id_paquete', $paqueteId)
            ->where('id_user', $userId)
            ->first();

        if (!$paquete) {
            return ['success' => false, 'message' => 'Paquete no encontrado'];
        }

        $items = DB::table('items_oferta')
            ->join('items', 'items.id_item', '=', 'items_oferta.id_item')
            ->where('items_oferta.id_paquete', $paqueteId)
            ->select('items.id_item', 'items.item', 'items.valor', 'items_oferta.id_item as seleccionado')
            ->get();

        $todosItems = Item::where('id_user', $userId)
            ->select('id_item', 'item', 'valor')
            ->get();

        return [
            'success' => true,
            'data'    => [
                'paquete'    => $paquete,
                'items'      => $items,
                'todosItems' => $todosItems,
            ],
        ];
    }

    /**
     * Obtiene un paquete con IDs de items (versión ligera).
     */
    public function obtenerLigero(int $userId, int $paqueteId): array
    {
        $paquete = Paquete::where('id_paquete', $paqueteId)
            ->where('id_user', $userId)
            ->first();

        if (!$paquete) {
            return ['success' => false, 'message' => 'Paquete no encontrado'];
        }

        $items = ItemOferta::where('id_paquete', $paqueteId)
            ->pluck('id_item')
            ->toArray();

        return [
            'success' => true,
            'data'    => ['paquete' => $paquete, 'items' => $items],
        ];
    }

    /**
     * Crea un paquete con items.
     */
    public function crear(int $userId, string $nombre, array $itemIds): array
    {
        if (empty($itemIds)) {
            return ['success' => false, 'message' => 'No se enviaron items para el paquete'];
        }

        $paquete = null;

        DB::transaction(function () use ($userId, $nombre, $itemIds, &$paquete) {
            $paquete = Paquete::create([
                'nombre_paquete' => $nombre,
                'estatus'        => 1,
                'fecha'          => now(),
                'id_user'        => $userId,
            ]);

            $insertData = array_map(fn($idItem) => [
                'id_paquete' => $paquete->id_paquete,
                'id_item'    => $idItem,
                'fecha'      => now(),
            ], $itemIds);

            ItemOferta::insert($insertData);
        });

        return ['success' => true, 'data' => ['id_paquete' => $paquete->id_paquete]];
    }

    /**
     * Edita nombre e items de un paquete.
     */
    public function editar(int $userId, int $paqueteId, ?string $nombre, array $itemIds = []): array
    {
        $paquete = Paquete::where('id_paquete', $paqueteId)
            ->where('id_user', $userId)
            ->first();

        if (!$paquete) {
            return ['success' => false, 'message' => 'Paquete no encontrado o no autorizado'];
        }

        DB::transaction(function () use ($paquete, $paqueteId, $nombre, $itemIds) {
            if ($nombre) {
                $paquete->update(['nombre_paquete' => $nombre, 'fecha' => now()]);
            }

            ItemOferta::where('id_paquete', $paqueteId)->delete();

            if (!empty($itemIds)) {
                $insertData = array_map(fn($idItem) => [
                    'id_paquete' => $paqueteId,
                    'id_item'    => $idItem,
                    'fecha'      => now(),
                ], $itemIds);

                ItemOferta::insert($insertData);
            }
        });

        return ['success' => true, 'message' => 'Paquete actualizado correctamente', 'data' => ['id_paquete' => $paqueteId]];
    }

    /**
     * Actualiza solo el nombre del paquete (verificando propiedad).
     */
    public function actualizarNombre(int $userId, int $paqueteId, string $nombre): array
    {
        $paquete = Paquete::where('id_paquete', $paqueteId)
            ->where('id_user', $userId)
            ->first();

        if (!$paquete) {
            return ['success' => false, 'message' => 'Paquete no encontrado'];
        }

        $paquete->update(['nombre_paquete' => $nombre]);

        return ['success' => true, 'message' => 'Nombre actualizado'];
    }

    /**
     * Elimina un paquete y sus items asociados.
     */
    public function eliminar(int $userId, int $paqueteId): array
    {
        $paquete = Paquete::where('id_paquete', $paqueteId)
            ->where('id_user', $userId)
            ->first();

        if (!$paquete) {
            return ['success' => false, 'message' => 'Paquete no encontrado o no tienes permiso'];
        }

        DB::transaction(function () use ($paqueteId, $paquete) {
            ItemOferta::where('id_paquete', $paqueteId)->delete();
            $paquete->delete();
        });

        return ['success' => true, 'message' => 'Paquete eliminado correctamente'];
    }

    /**
     * Items del usuario (para selector).
     */
    public function itemsDelUsuario(int $userId): array
    {
        $items = Item::where('id_user', $userId)
            ->select('id_item', 'item', 'valor')
            ->get();

        return ['success' => true, 'data' => $items];
    }
}
