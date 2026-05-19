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

    /** GET /api/mis-items — artículos del usuario autenticado */
    public function userItems(Request $request)
    {
        $items = Item::with(['imagenes:id_imagen,id_item,nombre,ruta', 'categoria:id_categoria_item,categoria'])
            ->where('id_user', $request->user()->id)
            ->select('id_item', 'item', 'valor', 'condicion', 'tipo_trans', 'estatus', 'fecha', 'id_categoria_item')
            ->latest('fecha')
            ->get()
            ->map(fn($item) => $this->appendImageUrl($item));

        return response()->json($items);
    }

    /** POST /api/items — publicar artículo */
    public function store(Request $request)
    {
        $data = $request->validate([
            'item'              => 'required|string|max:150',
            'presentacion'      => 'nullable|string',
            'valor'             => 'required|numeric|min:0',
            'condicion'         => 'required|integer|in:1,2,3',
            'tipo_trans'        => 'required|integer|in:1,2,3',
            'id_categoria_item' => 'required|integer|exists:categorias_item,id_categoria_item',
            'image_url'         => 'nullable|string|url',  // URL de ImgBB ya subida
        ]);

        $data['id_user']      = $request->user()->id;
        $data['estatus']      = 0; // pendiente de aprobación
        $data['fecha']        = now();
        $data['id_tipo_item'] = 1; // Producto

        $item = Item::create($data);

        // Si se envió una imagen ya hosteada en ImgBB, guardarla
        if (!empty($data['image_url'])) {
            $item->imagenes()->create([
                'nombre' => basename(parse_url($data['image_url'], PHP_URL_PATH)),
                'ruta'   => $data['image_url'],
                'estado' => 'pendiente',
            ]);
        }

        return response()->json(['message' => 'Artículo publicado. Pendiente de aprobación.', 'item' => $item], 201);
    }

    /** DELETE /api/items/{id} — eliminar artículo propio */
    public function destroy(Request $request, int $id)
    {
        $item = Item::where('id_item', $id)
            ->where('id_user', $request->user()->id)
            ->firstOrFail();

        $item->delete();

        return response()->json(['message' => 'Artículo eliminado.']);
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
