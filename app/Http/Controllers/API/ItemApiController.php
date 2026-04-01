<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\CategoriaItem;
use Illuminate\Http\Request;

/**
 * ItemApiController — Productos para la app móvil
 *
 * Las imágenes se sirven desde storage/public (symlink).
 * La API devuelve image_url lista para usar en Flutter.
 */
class ItemApiController extends Controller
{
    /** GET /api/items — listado paginado */
    public function index(Request $request)
    {
        $cacheKey = 'api_items_' . md5(json_encode($request->only('tipo', 'categoria', 'q', 'page')));

        $result = \Cache::remember($cacheKey, 120, function () use ($request) {
            $query = Item::with(['imagenes:id_imagen,id_item,nombre,ruta', 'categoria:id_categoria_item,categoria'])
                ->where('estatus', 1)
                ->select('id_item', 'item', 'valor', 'condicion', 'tipo_trans', 'id_user', 'fecha', 'id_categoria_item');

            if ($request->filled('tipo')) {
                $query->where('tipo_trans', $request->tipo);
            }
            if ($request->filled('categoria')) {
                $query->where('id_categoria_item', $request->categoria);
            }
            if ($request->filled('q')) {
                $query->where('item', 'like', '%' . $request->q . '%');
            }

            $items = $query->latest('fecha')->paginate(12);
            $data  = collect($items->items())->map(fn($item) => $this->appendImageUrl($item));

            return [
                'data'         => $data,
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'total'        => $items->total(),
            ];
        });

        return response()->json($result);
    }

    /** GET /api/items/{id} — detalle */
    public function show(int $id)
    {
        $item = Item::with([
            'imagenes:id_imagen,id_item,nombre,ruta',
            'categoria:id_categoria_item,categoria',
            'usuario:id,nombres,apellidos,profile_photo_path',
            'inventarios',
        ])->where('estatus', 1)->findOrFail($id);

        return response()->json($this->appendImageUrl($item));
    }

    /** GET /api/categorias */
    public function categorias()
    {
        return response()->json(
            CategoriaItem::select('id_categoria_item', 'categoria')->get()
        );
    }

    /** GET /api/items/buscar?q=... */
    public function buscar(Request $request)
    {
        $q = $request->input('q', '');

        $items = Item::with(['imagenes:id_imagen,id_item,nombre,ruta'])
            ->where('estatus', 1)
            ->where('item', 'like', "%{$q}%")
            ->select('id_item', 'item', 'valor', 'tipo_trans', 'fecha')
            ->latest('fecha')
            ->limit(20)
            ->get()
            ->map(fn($item) => $this->appendImageUrl($item));

        return response()->json($items);
    }

    /**
     * Agrega image_url resuelta al item.
     * Intenta storage/public primero, luego htdocs de Apache.
     */
    private function appendImageUrl($item): array
    {
        $arr = is_array($item) ? $item : $item->toArray();

        $imagenes = $arr['imagenes'] ?? [];
        if (!empty($imagenes)) {
            $primera = $imagenes[0];
            $nombre  = $primera['nombre'] ?? '';
            $ruta    = trim($primera['ruta'] ?? 'imgs/articulos/items', '/');

            // Intentar storage symlink primero
            $storagePath = public_path("storage/{$ruta}/{$nombre}");
            if (file_exists($storagePath)) {
                $arr['image_url'] = url("storage/{$ruta}/{$nombre}");
            } else {
                // Fallback: Apache htdocs
                $arr['image_url'] = url("{$ruta}/{$nombre}");
            }

            // Agregar image_url a cada imagen
            $arr['imagenes'] = array_map(function ($img) use ($ruta) {
                $n = $img['nombre'] ?? '';
                $r = trim($img['ruta'] ?? $ruta, '/');
                $storagePath = public_path("storage/{$r}/{$n}");
                $img['image_url'] = file_exists($storagePath)
                    ? url("storage/{$r}/{$n}")
                    : url("{$r}/{$n}");
                return $img;
            }, $imagenes);
        } else {
            $arr['image_url'] = null;
        }

        return $arr;
    }
}
