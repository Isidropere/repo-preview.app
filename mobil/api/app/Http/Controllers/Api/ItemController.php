<?php

/**
 * ============================================================
 * ItemController — Catálogo de productos (API móvil)
 * ============================================================
 * Expone el catálogo de artículos publicados en CambialóRD.
 * Todas las rutas son públicas (no requieren token).
 *
 * Rutas que usa:
 *   GET /api/items           → index()     Listado con filtros
 *   GET /api/items/{id}      → show()      Detalle de un item
 *   GET /api/categorias      → categorias() Lista de categorías
 * ============================================================
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * Listar productos con filtros y paginación.
     *
     * Hace un JOIN con users para obtener datos del vendedor
     * y un LEFT JOIN con imagen_items para traer solo la
     * primera imagen de cada producto (MIN id).
     *
     * Filtros disponibles via query string:
     *   ?search=texto    → busca en el nombre del item
     *   ?categoria=1     → filtra por id_categoria
     *   ?tipo=1          → filtra por tipo_publicacion
     *   ?per_page=20     → items por página (default 20)
     *
     * @param  Request $request
     * @return JsonResponse  paginación Laravel con items
     */
    public function index(Request $request)
    {
        // Paso 1: Construir la consulta base con JOINs
        $query = DB::table('items as i')
            ->join('users as u', 'u.id', '=', 'i.id_user') // Datos del vendedor
            ->leftJoin('imagen_items as img', function ($join) {
                // Solo traer la imagen con el ID más bajo (primera imagen)
                $join->on('img.id_item', '=', 'i.id')
                     ->whereRaw('img.id = (SELECT MIN(id) FROM imagen_items WHERE id_item = i.id)');
            })
            ->where('i.estatus', 1) // Solo items activos/publicados
            ->select(
                'i.id', 'i.nombre', 'i.precio', 'i.descripcion',
                'i.id_categoria', 'i.tipo_publicacion',
                'u.nombre_usuario', 'u.foto_perfil',
                'img.imagen as imagen_principal' // Alias para la app
            );

        // Paso 2: Aplicar filtro de búsqueda por nombre
        if ($request->filled('search')) {
            $query->where('i.nombre', 'like', '%'.$request->search.'%');
        }

        // Paso 3: Aplicar filtro por categoría
        if ($request->filled('categoria')) {
            $query->where('i.id_categoria', $request->categoria);
        }

        // Paso 4: Aplicar filtro por tipo de publicación (venta/intercambio)
        if ($request->filled('tipo')) {
            $query->where('i.tipo_publicacion', $request->tipo);
        }

        // Paso 5: Ordenar por más recientes y paginar
        $items = $query->orderByDesc('i.created_at')
                       ->paginate($request->get('per_page', 20));

        return response()->json($items);
    }

    /**
     * Detalle completo de un producto.
     *
     * Devuelve todos los campos del item más datos del vendedor,
     * nombre de categoría y el array completo de imágenes.
     *
     * @param  int  $id  ID del item
     * @return JsonResponse  { item, imagenes[] }  o 404
     */
    public function show($id)
    {
        // Paso 1: Buscar el item con datos del vendedor y categoría
        $item = DB::table('items as i')
            ->join('users as u', 'u.id', '=', 'i.id_user')
            ->leftJoin('categoria_items as c', 'c.id', '=', 'i.id_categoria')
            ->where('i.id', $id)
            ->where('i.estatus', 1) // Solo mostrar si está activo
            ->select(
                'i.*',                                          // Todos los campos del item
                'u.nombre_usuario', 'u.foto_perfil', 'u.id as vendedor_id',
                'c.nombre as categoria_nombre'
            )
            ->first();

        // Paso 2: Si no existe o está inactivo, devolver 404
        if (! $item) {
            return response()->json(['message' => 'Item no encontrado.'], 404);
        }

        // Paso 3: Obtener todas las imágenes del item
        $imagenes = DB::table('imagen_items')
            ->where('id_item', $id)
            ->pluck('imagen'); // Solo el campo imagen como array plano

        // Paso 4: Devolver item + array de imágenes
        return response()->json([
            'item'     => $item,
            'imagenes' => $imagenes,
        ]);
    }

    /**
     * Listar categorías activas.
     *
     * Devuelve solo id y nombre, ordenadas alfabéticamente.
     * Usada para poblar el filtro de categorías en la app.
     *
     * @return JsonResponse  array de { id, nombre }
     */
    public function categorias()
    {
        $categorias = DB::table('categoria_items')
            ->where('estatus', 1)       // Solo categorías activas
            ->select('id', 'nombre')
            ->orderBy('nombre')         // Orden alfabético
            ->get();

        return response()->json($categorias);
    }
}
