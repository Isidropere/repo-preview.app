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
    public function index(Request $request)
    {
        $cacheKey = 'api_items_' . md5(json_encode($request->only('tipo', 'categoria', 'q', 'page')));

        $result = \Cache::remember($cacheKey, 120, function () use ($request) {
            if ($request->filled('q')) {
                try {
                    $scout = Item::search($request->q)
                        ->where('estatus', 1);

                    if ($request->filled('categoria')) {
                        $scout->where('id_categoria_item', (int) $request->categoria);
                    }

                    $query = $scout->query(function ($q) use ($request) {
                        $q->with(['imagenes:id_imagen,id_item,nombre,ruta', 'categoria:id_categoria_item,categoria', 'inventarios'])
                            ->select('id_item', 'item', 'valor', 'condicion', 'tipo_trans', 'id_user', 'fecha', 'id_categoria_item');

                        if ($request->filled('tipo')) {
                            $tipo = (int) $request->tipo;
                            if ($tipo === 2) {
                                $q->whereIn('tipo_trans', [2, 3]);
                            } elseif ($tipo === 1) {
                                $q->whereIn('tipo_trans', [1, 3]);
                            } else {
                                $q->where('tipo_trans', $tipo);
                            }
                        }
                        $q->latest('fecha');
                    });

                    $items = $query->paginate(12);
                } catch (\Throwable $scoutException) {
                    \Log::warning('Elasticsearch caído o no configurado en API index, usando fallback de DB: ' . $scoutException->getMessage());
                    
                    $query = Item::with(['imagenes:id_imagen,id_item,nombre,ruta', 'categoria:id_categoria_item,categoria', 'inventarios'])
                        ->where('estatus', 1)
                        ->where(function($query) use ($request) {
                            $query->where('item', 'like', '%' . $request->q . '%')
                                  ->orWhere('presentacion', 'like', '%' . $request->q . '%');
                        })
                        ->select('id_item', 'item', 'valor', 'condicion', 'tipo_trans', 'id_user', 'fecha', 'id_categoria_item');

                    if ($request->filled('tipo')) {
                        $tipo = (int) $request->tipo;
                        if ($tipo === 2) {
                            $query->whereIn('tipo_trans', [2, 3]);
                        } elseif ($tipo === 1) {
                            $query->whereIn('tipo_trans', [1, 3]);
                        } else {
                            $query->where('tipo_trans', $tipo);
                        }
                    }

                    if ($request->filled('categoria')) {
                        $query->where('id_categoria_item', $request->categoria);
                    }

                    $items = $query->latest('fecha')->paginate(12);
                }
            } else {
                $query = Item::with(['imagenes:id_imagen,id_item,nombre,ruta', 'categoria:id_categoria_item,categoria', 'inventarios'])
                    ->where('estatus', 1)
                    ->select('id_item', 'item', 'valor', 'condicion', 'tipo_trans', 'id_user', 'fecha', 'id_categoria_item');

                if ($request->filled('tipo')) {
                    $tipo = (int) $request->tipo;
                    if ($tipo === 2) {
                        $query->whereIn('tipo_trans', [2, 3]);
                    } elseif ($tipo === 1) {
                        $query->whereIn('tipo_trans', [1, 3]);
                    } else {
                        $query->where('tipo_trans', $tipo);
                    }
                }
                if ($request->filled('categoria')) {
                    $query->where('id_categoria_item', $request->categoria);
                }

                $items = $query->latest('fecha')->paginate(12);
            }

            $data  = collect($items->items())->map(fn($item) => $this->appendImageUrl($item));

            return [
                'data'         => $data,
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'total'        => $items->total(),
            ];
        });

        $user = $request->user('sanctum');
        if ($user) {
            $cartItems = \App\Models\ItemIntencionCompra::whereHas('carrito', function($q) use ($user) {
                $q->where('id_user', $user->id);
            })->pluck('id_item')->toArray();
            $cartSet = array_flip($cartItems);

            $userActiveNegociaciones = \App\Models\Negociacion::where(function($q) use ($user) {
                  $q->where('usuario_emisor_id', $user->id)
                    ->orWhere('usuario_receptor_id', $user->id);
               })
              ->whereIn('estado', ['Inicial', 'aceptado', 'contraoferta'])
              ->get(['receptor_item_id', 'items_ofrecidos']);
              
            $negSet = [];
            foreach ($userActiveNegociaciones as $neg) {
                if ($neg->receptor_item_id) {
                    $negSet[$neg->receptor_item_id] = true;
                }
                if (is_array($neg->items_ofrecidos)) {
                    foreach ($neg->items_ofrecidos as $offeredId) {
                        $negSet[$offeredId] = true;
                    }
                }
            }

            $itemsData = $result['data'];
            if (is_array($itemsData) || $itemsData instanceof \Illuminate\Support\Collection) {
                $itemsData = collect($itemsData)->map(function($item) use ($cartSet, $negSet) {
                    $id = $item['id_item'] ?? null;
                    $item['ya_en_carrito'] = $id ? isset($cartSet[$id]) : false;
                    $item['con_negociacion_activa'] = $id ? isset($negSet[$id]) : false;
                    return $item;
                })->toArray();
                $result['data'] = $itemsData;
            }
        } else {
            $itemsData = $result['data'];
            if (is_array($itemsData) || $itemsData instanceof \Illuminate\Support\Collection) {
                $itemsData = collect($itemsData)->map(function($item) {
                    $item['ya_en_carrito'] = false;
                    $item['con_negociacion_activa'] = false;
                    return $item;
                })->toArray();
                $result['data'] = $itemsData;
            }
        }

        return response()->json($result);
    }

    /** GET /api/items/{id} — detalle */
    public function show(int $id)
    {
        \App\Models\PagoCompra::liberarTodasLasOrdenesPendientesExpiradas();

        $item = Item::with([
            'imagenes:id_imagen,id_item,nombre,ruta',
            'categoria:id_categoria_item,categoria',
            'usuario:id,nombres,apellidos,profile_photo_path',
            'inventarios',
        ])->where('estatus', 1)->findOrFail($id);

        $user = request()->user('sanctum');
        $yaEnCarrito = false;
        $conNegociacionActiva = false;

        if ($user) {
            $yaEnCarrito = \App\Models\ItemIntencionCompra::whereHas('carrito', function ($q) use ($user) {
                $q->where('id_user', $user->id);
            })->where('id_item', $item->id_item)->exists();

            $conNegociacionActiva = \App\Models\Negociacion::where('usuario_emisor_id', $user->id)
                ->where('receptor_item_id', $item->id_item)
                ->whereIn('estado', ['Inicial', 'aceptado', 'contraoferta'])
                ->exists();
        }

        $res = $this->appendImageUrl($item)->toArray();
        $res['ya_en_carrito'] = $yaEnCarrito;
        $res['con_negociacion_activa'] = $conNegociacionActiva;

        return response()->json($res);
    }

    /** GET /api/categorias */
    public function categorias()
    {
        return response()->json(
            CategoriaItem::select('id_categoria_item', 'categoria')->get()
        );
    }

    /** GET /api/colors */
    public function colors()
    {
        return response()->json(
            \App\Models\Color::select('id_color', 'nombre', 'codigo_hex')->get()
        );
    }


    /** GET /api/items/buscar?q=... */
    public function buscar(Request $request)
    {
        $q = $request->input('q', '');

        if (!empty($q)) {
            try {
                $items = Item::search($q)
                    ->where('estatus', 1)
                    ->query(fn($query) => $query->with(['imagenes:id_imagen,id_item,nombre,ruta', 'inventarios'])
                        ->select('id_item', 'item', 'valor', 'tipo_trans', 'fecha', 'id_categoria_item', 'id_user')
                        ->latest('fecha')
                    )
                    ->take(20)
                    ->get()
                    ->map(fn($item) => $this->appendImageUrl($item));
            } catch (\Throwable $scoutException) {
                \Log::warning('Elasticsearch caído o no configurado en API buscar, usando fallback de DB: ' . $scoutException->getMessage());
                
                $items = Item::with(['imagenes:id_imagen,id_item,nombre,ruta', 'inventarios'])
                    ->where('estatus', 1)
                    ->where(function($query) use ($q) {
                        $query->where('item', 'like', '%' . $q . '%')
                              ->orWhere('presentacion', 'like', '%' . $q . '%');
                    })
                    ->select('id_item', 'item', 'valor', 'tipo_trans', 'fecha', 'id_categoria_item', 'id_user')
                    ->latest('fecha')
                    ->limit(20)
                    ->get()
                    ->map(fn($item) => $this->appendImageUrl($item));
            }
        } else {
            $items = Item::with(['imagenes:id_imagen,id_item,nombre,ruta', 'inventarios'])
                ->where('estatus', 1)
                ->select('id_item', 'item', 'valor', 'tipo_trans', 'fecha', 'id_categoria_item', 'id_user')
                ->latest('fecha')
                ->limit(20)
                ->get()
                ->map(fn($item) => $this->appendImageUrl($item));
        }

        $user = $request->user('sanctum');
        if ($user) {
            $cartItems = \App\Models\ItemIntencionCompra::whereHas('carrito', function($q) use ($user) {
                $q->where('id_user', $user->id);
            })->pluck('id_item')->toArray();
            $cartSet = array_flip($cartItems);

            $userActiveNegociaciones = \App\Models\Negociacion::where(function($q) use ($user) {
                  $q->where('usuario_emisor_id', $user->id)
                    ->orWhere('usuario_receptor_id', $user->id);
               })
              ->whereIn('estado', ['Inicial', 'aceptado', 'contraoferta'])
              ->get(['receptor_item_id', 'items_ofrecidos']);
              
            $negSet = [];
            foreach ($userActiveNegociaciones as $neg) {
                if ($neg->receptor_item_id) {
                    $negSet[$neg->receptor_item_id] = true;
                }
                if (is_array($neg->items_ofrecidos)) {
                    foreach ($neg->items_ofrecidos as $offeredId) {
                        $negSet[$offeredId] = true;
                    }
                }
            }

            $items = collect($items)->map(function($item) use ($cartSet, $negSet) {
                $id = $item['id_item'] ?? null;
                $item['ya_en_carrito'] = $id ? isset($cartSet[$id]) : false;
                $item['con_negociacion_activa'] = $id ? isset($negSet[$id]) : false;
                return $item;
            })->toArray();
        } else {
            $items = collect($items)->map(function($item) {
                $item['ya_en_carrito'] = false;
                $item['con_negociacion_activa'] = false;
                return $item;
            })->toArray();
        }

        return response()->json($items);
    }

    /** GET /api/mis-items — artículos del usuario autenticado */
    public function userItems(Request $request)
    {
        $items = Item::with(['imagenes:id_imagen,id_item,nombre,ruta', 'categoria:id_categoria_item,categoria', 'inventarios:id_item,cantidad'])
            ->withCount('views')
            ->where('id_user', $request->user()->id)
            ->select('id_item', 'item', 'valor', 'condicion', 'tipo_trans', 'estatus', 'fecha', 'id_categoria_item', 'id_tipo_item')
            ->latest('fecha')
            ->get()
            ->map(fn($item) => $this->appendImageUrl($item));

        return response()->json($items);
    }

    /** POST /api/items — publicar artículo */
    public function store(Request $request)
    {
        // Normalizar campos numéricos vacíos para evitar fallos en la validación
        if ($request->has('descuento') && $request->input('descuento') === '') {
            $request->merge(['descuento' => null]);
        }
        if ($request->has('valor') && $request->input('valor') === '') {
            $request->merge(['valor' => null]);
        }

        $data = $request->validate([
            'item'              => 'required|string|max:150',
            'presentacion'      => 'required|string',
            'valor'             => 'required_if:tipo_trans,1,3|nullable|numeric|min:0',
            'descuento'         => 'nullable|numeric|min:0|max:100',
            'condicion'         => 'required|integer|in:1,2,3,4',
            'tipo_trans'        => 'required|integer|in:1,2,3',
            'id_categoria_item' => 'required|integer|exists:categorias_item,id_categoria_item',
            'image_url'         => 'nullable|string',
            'imagen_principal'  => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov|max:20480',
            'imagenes.*'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'peso_lbs'          => 'required|numeric|gt:0',
            'alto_cm'           => 'required|numeric|gt:0',
            'ancho_cm'          => 'required|numeric|gt:0',
            'profundo_cm'       => 'required|numeric|gt:0',
            'colors'            => 'nullable|array',
            'colors.*'          => 'exists:colors,id_color',
            'stock.*'           => 'nullable|integer|min:0',
            'cantidad'          => 'required|numeric|min:0',
            'estatus'           => 'nullable|integer|in:1,2,3',
        ]);

        $itemData = [
            'item'              => $data['item'],
            'id_categoria_item' => $data['id_categoria_item'],
            'valor'             => $data['valor'] ?? null,
            'descuento'         => $data['descuento'] ?? null,
            'presentacion'      => $data['presentacion'] ?? null,
            'condicion'         => $data['condicion'],
            'tipo_trans'        => $data['tipo_trans'],
            'id_user'           => $request->user()->id,
            'estatus'           => $request->input('estatus', 1), // Por defecto Activo (1) si no viene
            'fecha'             => now(),
            'peso_lbs'          => $data['peso_lbs'] ?? 0,
            'alto_cm'           => $data['alto_cm'] ?? 0,
            'ancho_cm'          => $data['ancho_cm'] ?? 0,
            'profundo_cm'       => $data['profundo_cm'] ?? 0,
            'id_tipo_item'      => 1, // Producto
        ];

        // Validar que la suma de stock de colores no supere la cantidad total
        if ($request->has('colors')) {
            $totalStockColores = 0;
            foreach ($request->colors as $colorId) {
                $totalStockColores += (int) ($request->stock[$colorId] ?? 0);
            }
            if ($totalStockColores > (int) $data['cantidad']) {
                return response()->json([
                    'success' => false,
                    'message' => 'La suma del stock de los colores seleccionados (' . $totalStockColores . ') no puede superar la cantidad total del producto (' . $data['cantidad'] . ').'
                ], 422);
            }
        }

        \DB::beginTransaction();
        try {
            $item = Item::create($itemData);

            // Crear registro en el inventario
            \App\Models\Inventario::create([
                'id_item'  => $item->id_item,
                'cantidad' => $data['cantidad'] ?? 1,
                'fecha'    => now()
            ]);

            // ERP registrar entrada
            if (app()->bound(\App\Services\ERPService::class)) {
                app(\App\Services\ERPService::class)->registrarEntradaRegistroItem($item, (int) ($data['cantidad'] ?? 1));
            }

            // Registrar colores y stock
            if ($request->has('colors')) {
                $colorsWithStock = [];
                foreach ($request->colors as $colorId) {
                    $stock = $request->stock[$colorId] ?? 0;
                    $colorsWithStock[$colorId] = ['stock' => $stock];
                }
                $item->colors()->sync($colorsWithStock);
            }

            // Procesar imagen principal (archivo local)
            if ($request->hasFile('imagen_principal') && $request->file('imagen_principal')->isValid()) {
                $resultado = $this->guardarImagen($request->file('imagen_principal'), $item->id_item, 1);
                if ($resultado['is_video']) {
                    $item->update(['tiene_video' => true]);
                }
            } 
            // Si no hay archivo, pero sí URL ImgBB
            elseif (!empty($data['image_url'])) {
                $item->imagenes()->create([
                    'nombre' => basename(parse_url($data['image_url'], PHP_URL_PATH)),
                    'ruta'   => $data['image_url'],
                    'estado' => 'pendiente',
                ]);
            }

            // Procesar imágenes adicionales (archivos locales)
            if ($request->hasFile('imagenes')) {
                $orden = 2;
                foreach ($request->file('imagenes') as $file) {
                    if ($file && $file->isValid()) {
                        $this->guardarImagen($file, $item->id_item, $orden++);
                    }
                }
            }

            \DB::commit();
            return response()->json(['message' => 'Artículo publicado. Pendiente de aprobación.', 'item' => $item], 201);
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('Error al crear producto en API: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    /** GET /api/mis-items/{id} — detalle de item propio para edición */
    public function userItemDetail(Request $request, int $id)
    {
        $item = Item::where('id_user', $request->user()->id)->findOrFail($id);
        
        // Cargar todas las imágenes pero renombrar el atributo para appendImageUrl
        $item->setRelation('imagenes', $item->todasLasImagenes);

        // Load other relationships
        $item->load(['categoria:id_categoria_item,categoria', 'inventarios', 'colors']);

        return response()->json($this->appendImageUrl($item));
    }

    /** POST /api/items/{id}/update — actualizar artículo propio */
    public function update(Request $request, int $id)
    {
        $item = Item::where('id_user', $request->user()->id)->findOrFail($id);

        // Normalizar campos numéricos vacíos para evitar fallos en la validación
        if ($request->has('descuento') && $request->input('descuento') === '') {
            $request->merge(['descuento' => null]);
        }
        if ($request->has('valor') && $request->input('valor') === '') {
            $request->merge(['valor' => null]);
        }

        $rules = [
            'item'              => 'required|string|max:150',
            'presentacion'      => 'required|string',
            'valor'             => 'required_if:tipo_trans,1,3|nullable|numeric|min:0',
            'descuento'         => 'nullable|numeric|min:0|max:100',
            'condicion'         => 'required|integer|in:1,2,3,4',
            'tipo_trans'        => 'required|integer|in:1,2,3',
            'id_categoria_item' => 'required|integer|exists:categorias_item,id_categoria_item',
            'imagen_principal'  => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov|max:20480',
            'imagenes.*'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'colors'            => 'nullable|array',
            'colors.*'          => 'exists:colors,id_color',
            'stock.*'           => 'nullable|integer|min:0',
            'cantidad'          => 'required|numeric|min:0',
            'imagenes_existentes'=> 'nullable|array', // IDs de imágenes adicionales existentes que se conservan
            'imagenes_existentes.*' => 'integer',
            'estatus'           => 'nullable|integer|in:1,2,3',
        ];

        // Solo requerir dimensiones si NO es de la categoría 29 (Talentos)
        if ((int) $request->input('id_categoria_item') !== 29) {
            $rules['peso_lbs']    = 'required|numeric|gt:0';
            $rules['alto_cm']     = 'required|numeric|gt:0';
            $rules['ancho_cm']    = 'required|numeric|gt:0';
            $rules['profundo_cm'] = 'required|numeric|gt:0';
        } else {
            $rules['peso_lbs']    = 'nullable|numeric|min:0';
            $rules['alto_cm']     = 'nullable|numeric|min:0';
            $rules['ancho_cm']    = 'nullable|numeric|min:0';
            $rules['profundo_cm'] = 'nullable|numeric|min:0';
        }

        $data = $request->validate($rules);

        if (isset($data['valor'])) {
            $data['valor'] = str_replace(',', '', $data['valor']);
        }

        // Validar que la suma de stock de colores no supere la cantidad total
        if ($request->has('colors')) {
            $totalStockColores = 0;
            foreach ($request->colors as $colorId) {
                $totalStockColores += (int) ($request->stock[$colorId] ?? 0);
            }
            if ($totalStockColores > (int) $data['cantidad']) {
                return response()->json([
                    'success' => false,
                    'message' => 'La suma del stock de los colores seleccionados (' . $totalStockColores . ') no puede superar la cantidad total del producto (' . $data['cantidad'] . ').'
                ], 422);
            }
        }

        \DB::beginTransaction();
        try {
            // Actualizar datos del Item
            $item->update([
                'item'              => $data['item'],
                'id_categoria_item' => $data['id_categoria_item'],
                'valor'             => $data['valor'] ?? null,
                'descuento'         => $data['descuento'] ?? null,
                'presentacion'      => $data['presentacion'] ?? null,
                'condicion'         => $data['condicion'],
                'tipo_trans'        => $data['tipo_trans'],
                'peso_lbs'          => $data['peso_lbs'] ?? 0,
                'alto_cm'           => $data['alto_cm'] ?? 0,
                'ancho_cm'          => $data['ancho_cm'] ?? 0,
                'profundo_cm'       => $data['profundo_cm'] ?? 0,
                'estatus'           => $data['estatus'] ?? $item->estatus,
            ]);

            // Actualizar o crear registro en el inventario
            $inventario = \App\Models\Inventario::where('id_item', $id)->first();
            if ($inventario) {
                $inventario->update([
                    'cantidad' => $data['cantidad'],
                    'fecha'    => now()
                ]);
            } else {
                \App\Models\Inventario::create([
                    'id_item'  => $id,
                    'cantidad' => $data['cantidad'],
                    'fecha'    => now()
                ]);
            }

            // Sync colores y existencias
            if ($request->has('colors')) {
                $colorsWithStock = [];
                foreach ($request->colors as $colorId) {
                    $stock = $request->stock[$colorId] ?? 0;
                    $colorsWithStock[$colorId] = ['stock' => $stock];
                }
                $item->colors()->sync($colorsWithStock);
            } else {
                $item->colors()->detach();
            }

            // ── Imagen principal ──
            if ($request->hasFile('imagen_principal') && $request->file('imagen_principal')->isValid()) {
                // Borrar todas las imágenes antiguas
                foreach ($item->todasLasImagenes as $imgVieja) {
                    \App\Helpers\ImageHelper::eliminar($imgVieja->ruta . '/' . $imgVieja->nombre);
                    $imgVieja->delete();
                }
                
                $resultado = $this->guardarImagen($request->file('imagen_principal'), $item->id_item, 1, 'pendiente');
                if ($resultado['is_video']) {
                    $item->update(['tiene_video' => true]);
                } else {
                    $item->update(['tiene_video' => false]);
                }

                // Guardar nuevas adicionales
                if ($request->hasFile('imagenes')) {
                    $orden = 2;
                    foreach ($request->file('imagenes') as $file) {
                        if ($file && $file->isValid()) {
                            $this->guardarImagen($file, $item->id_item, $orden++, 'pendiente');
                        }
                    }
                }
            } else {
                // No cambió la principal — solo gestionar secundarias
                $idsConservar = $request->input('imagenes_existentes', []);
                $imagenesActuales = $item->todasLasImagenes()->where('orden_visualizacion', '>', 1)->get();
                foreach ($imagenesActuales as $imagen) {
                    if (!in_array($imagen->id_imagen, $idsConservar)) {
                        \App\Helpers\ImageHelper::eliminar($imagen->ruta . '/' . $imagen->nombre);
                        $imagen->delete();
                    }
                }

                // Guardar nuevas imágenes secundarias
                if ($request->hasFile('imagenes')) {
                    $maxOrden = $item->todasLasImagenes()->max('orden_visualizacion') ?? 1;
                    foreach ($request->file('imagenes') as $file) {
                        if ($file && $file->isValid()) {
                            $maxOrden++;
                            $this->guardarImagen($file, $item->id_item, $maxOrden, 'pendiente');
                        }
                    }
                }
            }

            \DB::commit();
            return response()->json(['message' => 'Artículo actualizado exitosamente. Pendiente de aprobación.']);
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('Error al actualizar producto en API: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    protected function guardarImagen($file, $itemId, $orden, $estado = 'pendiente')
    {
        $mime = $file->getClientMimeType();
        if ($mime === 'application/octet-stream' || empty($mime)) {
            $realMime = @mime_content_type($file->getRealPath());
            if ($realMime) {
                $mime = $realMime;
            } else {
                $ext = strtolower($file->getClientOriginalExtension());
                $mimeMap = [
                    'jpg'  => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png'  => 'image/png',
                    'webp' => 'image/webp',
                    'mp4'  => 'video/mp4',
                    'mov'  => 'video/quicktime',
                    'm4v'  => 'video/x-m4v'
                ];
                if (isset($mimeMap[$ext])) {
                    $mime = $mimeMap[$ext];
                }
            }
        }

        $isVideo = str_starts_with($mime, 'video/');

        $allowedMimeTypes = ['image/jpeg','image/png','image/jpg','image/webp','video/mp4','video/quicktime','video/x-m4v'];
        if (!in_array($mime, $allowedMimeTypes)) {
            throw new \Exception('Tipo de archivo no permitido: ' . $mime);
        }

        $directory = $isVideo ? 'imgs/videos/items' : 'imgs/articulos/items';
        $prefix = $isVideo ? 'video_' : 'item_';

        $resultado = \App\Helpers\ImageHelper::guardar($file, $directory, $prefix, $itemId);

        \DB::table('imagenes_item')->insert([
            'nombre'              => $resultado['fileName'],
            'extension'           => pathinfo($resultado['fileName'], PATHINFO_EXTENSION),
            'id_item'             => $itemId,
            'orden_visualizacion' => $orden,
            'ruta'                => $directory,
            'tipo'                => $isVideo ? 'video' : 'imagen',
            'estado'              => $estado,
        ]);

        return [
            'path'     => $resultado['path'],
            'is_video' => $isVideo,
        ];
    }

    /** DELETE /api/items/{id} — eliminar artículo propio */
    public function destroy(Request $request, int $id)
    {
        \DB::beginTransaction();
        try {
            $item = Item::where('id_item', $id)
                ->where('id_user', $request->user()->id)
                ->firstOrFail();

            // 1. Desasociar colores
            $item->colors()->detach();

            // 2. Eliminar inventario
            if ($item->inventarios) {
                $item->inventarios->delete();
            }

            // 3. Eliminar imágenes físicas y registros
            foreach ($item->todasLasImagenes as $imagen) {
                \App\Helpers\ImageHelper::eliminar($imagen->ruta . '/' . $imagen->nombre);
                $imagen->delete();
            }

            // 4. Eliminar el item
            $item->delete();

            \DB::commit();
            return response()->json(['message' => 'Artículo eliminado con éxito.']);
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('Error al eliminar producto en API: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el artículo: ' . $e->getMessage()], 500);
        }
    }

    /** POST /api/talentos — publicar talento con pago */
    public function storeTalento(Request $request)
    {
        $user = $request->user();

        // 1. Verificar Hoja de Vida
        $tieneHoja = \App\Models\HojaVida::where('id_user', $user->id)->exists();
        if (!$tieneHoja) {
            return response()->json([
                'success' => false,
                'message' => 'Debes completar tu hoja de vida antes de publicar un talento.',
                'error_code' => 'NO_HOJA_VIDA'
            ], 422);
        }

        // 2. Validar datos básicos del talento
        $rules = [
            'item'              => 'required|string|max:150',
            'presentacion'      => 'required|string',
            'valor'             => 'required_if:tipo_trans,1,3|nullable|numeric|min:0',
            'condicion'         => 'required|integer|in:1,2,3,4',
            'tipo_trans'        => 'required|integer|in:1,2,3',
            'id_categoria_item' => 'required|integer|exists:categorias_item,id_categoria_item',
            'image_url'         => 'nullable|string',
            'imagen_principal'  => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov|max:20480',
            'imagenes.*'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cantidad'          => 'nullable|integer|min:1',
        ];

        $validated = $request->validate($rules);

        $esCategoria29 = (int) $validated['id_categoria_item'] === 29;
        if (!$esCategoria29) {
            return response()->json(['success' => false, 'message' => 'La categoría seleccionada no corresponde al talento.'], 422);
        }

        // 3. Obtener tarifa
        $config = \App\Models\ConfigTarifaCategoria29::vigente();
        $cantidad = (int) ($validated['cantidad'] ?? 1);
        $monto = (float) $config->monto_registro * $cantidad;

        \DB::beginTransaction();
        try {
            $estatus = $monto > 0 ? 0 : 1; // Inactivo si requiere pago, activo si es gratis

            // 6. Crear el Item de tipo talento (id_tipo_item = 2)
            $item = Item::create([
                'item'              => $validated['item'],
                'id_categoria_item' => $validated['id_categoria_item'],
                'valor'             => $validated['valor'],
                'presentacion'      => $validated['presentacion'],
                'condicion'         => $validated['condicion'],
                'tipo_trans'        => $validated['tipo_trans'],
                'id_user'           => $user->id,
                'estatus'           => $estatus,
                'fecha'             => now(),
                'id_tipo_item'      => 2, // Talento
                'tiene_video'       => false,
            ]);

            // Crear inventario
            \App\Models\Inventario::create([
                'id_item' => $item->id_item,
                'cantidad' => $cantidad,
                'fecha' => now(),
            ]);

            // ERP registrar entrada
            if (app()->bound(\App\Services\ERPService::class)) {
                app(\App\Services\ERPService::class)->registrarEntradaRegistroItem($item, $cantidad);
            }

            // Procesar imagen principal (archivo local)
            if ($request->hasFile('imagen_principal') && $request->file('imagen_principal')->isValid()) {
                try {
                    $resultado = $this->guardarImagen($request->file('imagen_principal'), $item->id_item, 1);
                    if ($resultado['is_video']) {
                        $item->update(['tiene_video' => true]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error al guardar imagen_principal de talento en API: ' . $e->getMessage());
                }
            } 
            // Si no hay archivo, pero sí URL ImgBB
            elseif (!empty($validated['image_url'])) {
                $item->imagenes()->create([
                    'nombre' => basename(parse_url($validated['image_url'], PHP_URL_PATH)),
                    'ruta'   => $validated['image_url'],
                    'estado' => 'pendiente',
                ]);
            }

            // Procesar imágenes adicionales (archivos locales)
            if ($request->hasFile('imagenes')) {
                $orden = 2;
                foreach ($request->file('imagenes') as $file) {
                    if ($file && $file->isValid()) {
                        try {
                            $this->guardarImagen($file, $item->id_item, $orden++);
                        } catch (\Exception $e) {
                            \Log::error('Error al guardar imagen adicional de talento en API: ' . $e->getMessage());
                        }
                    }
                }
            }

            if ($monto > 0) {
                \DB::commit();

                \Illuminate\Support\Facades\Cache::forget('home_intercambio');
                \Illuminate\Support\Facades\Cache::forget('home_venta');

                $redirectUrl = route('talento.pago.iniciar-movil', ['id_item' => $item->id_item]);

                return response()->json([
                    'success'      => true,
                    'message'      => 'Talento registrado. Redirigiendo al pago...',
                    'redirect_url' => $redirectUrl,
                ], 201);
            }

            // Registrar PagoRegistroTalento para tarifa gratuita
            $transactionId = 'GRATIS_' . time() . '_' . \Illuminate\Support\Str::random(4);
            \App\Models\PagoRegistroTalento::create([
                'id_item'        => $item->id_item,
                'id_user'        => $user->id,
                'transaction_id' => $transactionId,
                'monto_pagado'   => $monto,
                'estatus'        => 'aprobado',
            ]);

            \DB::commit();

            \Illuminate\Support\Facades\Cache::forget('home_intercambio');
            \Illuminate\Support\Facades\Cache::forget('home_venta');

            return response()->json([
                'success' => true,
                'message' => 'Talento publicado exitosamente.',
                'item'    => $this->appendImageUrl($item),
            ], 201);

        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('Error en storeTalento API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al publicar talento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agrega image_url resuelta al item.
     * Intenta storage/public primero, luego htdocs de Apache.
     */
    private function appendImageUrl($item): array
    {
        $arr = is_array($item) ? $item : $item->toArray();

        if (isset($arr['item'])) {
            $arr['item'] = preg_replace('/\s*\(User\s*\d+\)/i', '', $arr['item']);
        }

        $idItem = $arr['id_item'] ?? 0;
        if (is_object($item) && $item->relationLoaded('todasLasImagenes')) {
            $arr['tiene_imagenes_pendientes'] = $item->todasLasImagenes->where('estado', 'pendiente')->isNotEmpty();
        } else {
            $arr['tiene_imagenes_pendientes'] = \App\Models\ImagenItem::where('id_item', $idItem)
                ->where('estado', 'pendiente')
                ->exists();
        }

        $imagenes = $arr['imagenes'] ?? [];
        if (!empty($imagenes)) {
            $primera = $imagenes[0];
            $nombre  = $primera['nombre'] ?? '';
            $ruta    = trim($primera['ruta'] ?? 'imgs/articulos/items', '/');

            // Intentar ruta directa primero (public/), si no, asumir que está en storage/
            $arr['image_url'] = file_exists(public_path("{$ruta}/{$nombre}"))
                ? url("{$ruta}/{$nombre}")
                : url("storage/{$ruta}/{$nombre}");

            // Agregar image_url a cada imagen
            $arr['imagenes'] = array_map(function ($img) use ($ruta) {
                $n = $img['nombre'] ?? '';
                $r = trim($img['ruta'] ?? $ruta, '/');
                $img['image_url'] = file_exists(public_path("{$r}/{$n}"))
                    ? url("{$r}/{$n}")
                    : url("storage/{$r}/{$n}");
                return $img;
            }, $imagenes);
        } else {
            $arr['image_url'] = null;
        }

        $arr['stock'] = 0;
        if (is_object($item)) {
            $arr['stock'] = $item->inventarios?->cantidad ?? 0;
        } elseif (is_array($item)) {
            $arr['stock'] = isset($item['inventarios']['cantidad'])
                ? $item['inventarios']['cantidad']
                : (is_numeric($item['inventarios'] ?? null) ? $item['inventarios'] : 0);
        }

        return $arr;
    }
}
