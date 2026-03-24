<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\CategoriaItem;
use Illuminate\Http\Request;

/**
 * ItemApiController — Productos para la app móvil
 */
class ItemApiController extends Controller
{
    /** GET /api/items — listado paginado */
    public function index(Request $request)
    {
        $query = Item::with(['imagenes:id_imagen,id_item,nombre,ruta', 'categoria:id_categoria_item,categoria'])
            ->where('estatus', 1)
            ->select('id_item', 'item', 'valor', 'condicion', 'tipo_trans', 'id_user', 'fecha', 'id_categoria_item');

        if ($request->filled('tipo')) {
            // tipo=1 venta | tipo=2,3 intercambio
            $query->where('tipo_trans', $request->tipo);
        }

        if ($request->filled('categoria')) {
            $query->where('id_categoria_item', $request->categoria);
        }

        if ($request->filled('q')) {
            $query->where('item', 'like', '%' . $request->q . '%');
        }

        $items = $query->latest('fecha')->paginate(12);

        return response()->json([
            'data'         => $items->items(),
            'current_page' => $items->currentPage(),
            'last_page'    => $items->lastPage(),
            'total'        => $items->total(),
        ]);
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

        return response()->json($item);
    }

    /** GET /api/categorias */
    public function categorias()
    {
        return response()->json(CategoriaItem::select('id_categoria_item', 'categoria')->get());
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
            ->get();

        return response()->json($items);
    }
}
