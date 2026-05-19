<?php
namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ImagenItem;
use App\Models\Color;
use App\Models\ItemColor;
use App\Models\CategoriaItem;
use App\Models\ConfigTarifaCategoria29;
use App\Models\TarjetaPago;
use App\Services\PagoService;
use Illuminate\Http\Request;
use App\Logging\ErrorLoggerTrait;
use Throwable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\Inventario;

class ItemController extends Controller
{
    protected $erpService;

    public function __construct(\App\Services\ERPService $erpService)
    {
        $this->erpService = $erpService;
    }

    public function AddTalento(Request $request)
    {
        // Evitar que el servidor corte la conexión durante el pago (CardNet puede tardar ~15s)
        set_time_limit(120);
        ignore_user_abort(true);
        Log::info('AddTalento: request recibido', [
            'ajax' => $request->ajax(),
            'wantsJson' => $request->wantsJson(),
            'has_id_tarjeta' => $request->has('id_tarjeta'),
        ]);
        // Punto 1: Verificar recepción de datos (MANTENIENDO TUS LOGS)
        Log::info('Inicio de store() - Datos recibidos:', [
            'form_data' => $request->except(['imagen_principal', 'imagenes']),
            'has_imagen_principal' => $request->hasFile('imagen_principal'),
            'num_imagenes' => $request->hasFile('imagenes') ? count($request->file('imagenes')) : 0
        ]);

        DB::beginTransaction();

        try {
            // Punto 2: Validación de datos con mensajes personalizados descuento
            $rules = [
                'item' => 'required|string|max:255',
                'id_categoria_item' => 'required|exists:categorias_item,id_categoria_item',
                'valor' => 'required|numeric|min:0',
                'descuento' => 'nullable|numeric|min:0',
                'presentacion' => 'required|string',
                'cantidad' => 'nullable|integer|min:1|max:999',
                'condicion' => 'required|integer|in:1,2,3,4',
                'tipo_trans' => 'required|integer|in:1,2,3',
                'imagen_principal' => 'required|file|mimes:mp4,mov,jpeg,png,jpg,gif,webp|max:20480', // 10MB para videos
                'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'peso_lbs' => 'nullable|numeric|min:0',
                'alto_cm' => 'nullable|numeric|min:0',
                'ancho_cm' => 'nullable|numeric|min:0',
                'profundo_cm' => 'nullable|numeric|min:0',
                'id_tipo_item' => 'nullable|numeric|min:0'
            ];

            $messages = [
                'item.required' => 'El nombre del producto es obligatorio',
                'id_categoria_item.required' => 'Debe seleccionar una categorí­a',
                'valor.required' => 'El precio es obligatorio',
                'valor.numeric' => 'El precio debe ser un níºmero válido',
                'valor.min' => 'El precio no puede ser negativo',
                'condicion.required' => 'Debe especificar la condición del producto',
                'condicion.in' => 'La condición seleccionada no es válida',
                'tipo_trans.required' => 'Debe seleccionar un tipo de transacción',
                'tipo_trans.in' => 'El tipo de transacción seleccionado no es válido',
                'imagen_principal.required' => 'La imagen/video principal es obligatorio',
                'imagen_principal.file' => 'El archivo debe ser una imagen o video válido',
                'imagen_principal.mimes' => 'Solo se permiten imágenes (JPEG, PNG, JPG, GIF, WEBP) o videos (MP4, MOV)',
                'imagen_principal.max' => 'El archivo no debe pesar más de 10MB',
                'imagenes.*.image' => 'Los archivos adicionales deben ser imágenes válidas',
                'imagenes.*.mimes' => 'Solo se permiten imágenes JPEG, PNG, JPG, GIF o WEBP',
                'imagenes.*.max' => 'Las imágenes no deben pesar más de 2MB',
                'peso_lbs.numeric' => 'El peso debe ser un níºmero válido',
                'alto_cm.numeric' => 'La altura debe ser un níºmero válido',
                'ancho_cm.numeric' => 'El ancho debe ser un níºmero válido',
                'profundo_cm.numeric' => 'La profundidad debe ser un níºmero válido',
                'presentacion.required' => 'Rellene la descripción de su producto o servicio, que se encuentra en la sección de Especificar Dimensiones.',
            ];

            $validatedData = $request->validate($rules, $messages);

            // MANTENIENDO TU LOG DE VALIDACIí“N
            Log::debug('Datos validados correctamente', $validatedData);

            // Preparar datos del item (necesario antes del bloque de pago)
            $itemData = [
                'item' => $validatedData['item'],
                'id_categoria_item' => $validatedData['id_categoria_item'],
                'valor' => $validatedData['valor'],
                'descuento' => $validatedData['descuento'] ?? null,
                'presentacion' => $validatedData['presentacion'] ?? null,
                'condicion' => $validatedData['condicion'],
                'tipo_trans' => $validatedData['tipo_trans'],
                'id_user' => auth()->id(),
                'estatus' => 1,
                'fecha' => now(),
                'peso_lbs' => $validatedData['peso_lbs'] ?? 0,
                'alto_cm' => $validatedData['alto_cm'] ?? 0,
                'ancho_cm' => $validatedData['ancho_cm'] ?? 0,
                'profundo_cm' => $validatedData['profundo_cm'] ?? 0,
                'id_tipo_item' => $validatedData['id_tipo_item'] ?? 1,
                'tiene_video' => false,
            ];

            // Interceptar categoría 29: procesar pago inline via modal
            $esCategoria29 = (int) $validatedData['id_categoria_item'] === 29;
            $tipoTransConPago = in_array((int) $validatedData['tipo_trans'], [1, 2, 3]);
            if ($esCategoria29 && $tipoTransConPago) {
                // Validar datos de pago del modal
                $request->validate([
                    'id_tarjeta' => 'required|string|exists:tarjetas_pagos,id_tarjeta',
                    'cvv'        => 'nullable|string|max:4',
                ]);

                // Preservar archivos ANTES del cobro (PHP limpia los tmp durante requests largos)
                $savedFiles = [];
                if ($request->hasFile('imagen_principal')) {
                    $f = $request->file('imagen_principal');
                    $savedFiles['principal'] = [
                        'content'   => file_get_contents($f->getRealPath()),
                        'extension' => $f->extension(),
                        'mime'      => $f->getMimeType(),
                        'original'  => $f->getClientOriginalName(),
                    ];
                }
                if ($request->hasFile('imagenes')) {
                    foreach ($request->file('imagenes') as $i => $f) {
                        if ($f->isValid()) {
                            $savedFiles['extra_' . $i] = [
                                'content'   => file_get_contents($f->getRealPath()),
                                'extension' => $f->extension(),
                                'mime'      => $f->getMimeType(),
                                'original'  => $f->getClientOriginalName(),
                            ];
                        }
                    }
                }

                $config = ConfigTarifaCategoria29::vigente();
                $cantidadServicios = (int) ($validatedData['cantidad'] ?? 1);
                $monto = (float) $config->monto_registro * $cantidadServicios;

                $tarjeta = TarjetaPago::where('id_tarjeta', $request->input('id_tarjeta'))
                    ->where('id_user', auth()->id())
                    ->where('estatus', 1)
                    ->first();

                if (!$tarjeta) {
                    return response()->json(['success' => false, 'message' => 'Tarjeta no válida.'], 422);
                }

                // Cobrar (los archivos ya están en memoria)
                $pagoService = app(PagoService::class);
                $datosTarjeta = $tarjeta->datosCardnet($request->input('cvv'));
                $opciones = [
                    'client_ip'        => $request->ip(),
                    'invoice_number'   => 'TAL' . Str::random(10),
                    'reference_number' => 'talento_' . auth()->id() . '_' . time(),
                ];

                $resultadoPago = $pagoService->cobrarTarjeta($monto, '214', $datosTarjeta, $opciones);

                if (!$resultadoPago['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => $resultadoPago['error'] ?? 'Pago rechazado. Intenta con otra tarjeta.',
                    ], 422);
                }

                // Pago aprobado — crear item y guardar archivos desde memoria
                $item = Item::create($itemData);

                Inventario::create([
                    'id_item' => $item->id_item,
                    'cantidad' => $cantidadServicios,
                    'fecha' => now(),
                ]);

                // ERP: Registrar entrada en Almacén
                $this->erpService->registrarEntradaRegistroItem($item, $cantidadServicios);

                // Guardar archivos preservados
                if (!empty($savedFiles['principal'])) {
                    $sf = $savedFiles['principal'];
                    $isVideo = str_starts_with($sf['mime'], 'video/');
                    $dir = $isVideo ? 'imgs/videos/items' : 'imgs/articulos/items';
                    $prefix = $isVideo ? 'video_' : 'item_';
                    $fileName = $prefix . $item->id_item . '_' . now()->format('YmdHis') . '_' . Str::random(10) . '.' . $sf['extension'];

                    \App\Helpers\ImageHelper::guardarContenido($sf['content'], $dir, $fileName);

                    DB::table('imagenes_item')->insert([
                        'nombre' => $fileName, 'extension' => $sf['extension'],
                        'id_item' => $item->id_item, 'orden_visualizacion' => 1,
                        'ruta' => $dir, 'tipo' => $isVideo ? 'video' : 'imagen', 'estado' => 'pendiente',
                    ]);

                    if ($isVideo) { $item->update(['tiene_video' => true]); }
                }

                $orden = 2;
                foreach ($savedFiles as $key => $sf) {
                    if ($key === 'principal') continue;
                    $fileName = 'item_' . $item->id_item . '_' . now()->format('YmdHis') . '_' . Str::random(8) . '.' . $sf['extension'];
                    \App\Helpers\ImageHelper::guardarContenido($sf['content'], 'imgs/articulos/items', $fileName);
                    DB::table('imagenes_item')->insert([
                        'nombre' => $fileName, 'extension' => $sf['extension'],
                        'id_item' => $item->id_item, 'orden_visualizacion' => $orden++,
                        'ruta' => 'imgs/articulos/items', 'tipo' => 'imagen', 'estado' => 'pendiente',
                    ]);
                }

                // Registrar pago
                \App\Models\PagoRegistroTalento::create([
                    'id_item'        => $item->id_item,
                    'id_user'        => auth()->id(),
                    'transaction_id' => $resultadoPago['transaction_id'],
                    'monto_pagado'   => $monto,
                    'estatus'        => 'aprobado',
                ]);

                DB::commit();

                \Illuminate\Support\Facades\Cache::forget('home_intercambio');
                \Illuminate\Support\Facades\Cache::forget('home_venta');

                if ($request->wantsJson()) {
                    return response()->json(['success' => true, 'message' => 'Talento publicado exitosamente!', 'redirect' => route('items.admintalento')]);
                }
                return redirect()->route('items.admintalento')->with('success', 'Talento publicado exitosamente!');
            }



            // Crear item (flujo normal, no categoria 29 con pago)
            Log::debug('Intentando crear item con datos:', $itemData);

            $item = Item::create($itemData);
            Log::info('Item creado exitosamente', ['id_item' => $item->id_item]);

            // Crear registro en el inventario
            Inventario::create([
                'id_item' => $item->id_item,
                'cantidad' => $validatedData['cantidad'] ?? 1,
                'fecha' => now(),
            ]);

            // ERP: Registrar entrada en Almacén
            $this->erpService->registrarEntradaRegistroItem($item, (int) ($validatedData['cantidad'] ?? 1));

            if ($request->hasFile('imagen_principal')) {
                Log::debug('Procesando imagen/video principal...');
                try {
                    $resultado = $this->guardarImagenTalento($request->file('imagen_principal'), $item->id_item, 1);

                    // Actualizar el item si es un video
                    if ($resultado['is_video']) {
                        $item->tiene_video = true;
                        $item->save();
                        Log::info('Video principal guardado', ['path' => $resultado['path']]);
                    } else {
                        Log::info('Imagen principal guardada', ['path' => $resultado['path']]);
                    }

                } catch (\Exception $e) {
                    Log::error('Error al guardar imagen/video principal', ['error' => $e->getMessage()]);
                    throw $e; // Relanzamos la excepción para que caiga en el catch general
                }
            }

            // Punto 6: Procesar imágenes adicionales (MANTENIENDO TUS LOGS)
            if ($request->hasFile('imagenes')) {
                Log::debug('Procesando imágenes adicionales...');
                $orden = 2;

                foreach ($request->file('imagenes') as $index => $file) {
                    if ($file->isValid()) {
                        try {
                            $this->guardarImagenTalento($file, $item->id_item, $orden);
                            Log::info("Imagen adicional {$index} guardada", ['orden' => $orden]);
                            $orden++;
                        } catch (\Exception $e) {
                            Log::error("Error al guardar imagen adicional {$index}", ['error' => $e->getMessage()]);
                            // Continuamos con las siguientes imágenes aunque falle una
                        }
                    }
                }
            }

            DB::commit();
            Log::info('Transacción completada exitosamente');

            // Registrar pago de talento si aplica (categoría 29)
            $pagoResultado = session('_talento_pago_resultado');
            if ($pagoResultado) {
                \App\Models\PagoRegistroTalento::create([
                    'id_item'        => $item->id_item,
                    'id_user'        => auth()->id(),
                    'transaction_id' => $pagoResultado['transaction_id'],
                    'monto_pagado'   => session('_talento_pago_monto'),
                    'estatus'        => 'aprobado',
                ]);
                session()->forget(['_talento_pago_resultado', '_talento_pago_monto']);
            }

            // Invalidar cache del home para reflejar el nuevo item
            \Illuminate\Support\Facades\Cache::forget('home_intercambio');
            \Illuminate\Support\Facades\Cache::forget('home_venta');

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Talento creado exitosamente!', 'redirect' => route('items.admintalento')]);
            }

            return redirect()->route('items.admintalento')->with('success', 'Talento creado exitosamente!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Error de validacion', ['errors' => $e->errors()]);
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
            }
            return back()->withErrors($e->validator)->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en store()', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error al crear el talento: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Error al crear el talento: ' . $e->getMessage());
        }
    }


    protected function guardarImagenTalento($file, $itemId, $orden, $estado = 'pendiente')
    {
        return $this->guardarImagen($file, $itemId, $orden, $estado);
    }

    public function store(Request $request)
    {
        Log::info('Inicio de store() - Datos recibidos:', [
            'form_data' => $request->except(['imagen_principal', 'imagenes']),
            'has_imagen_principal' => $request->hasFile('imagen_principal'),
            'num_imagenes' => $request->hasFile('imagenes') ? count($request->file('imagenes')) : 0
        ]);

        // Preservar archivos antes de la transaccion (PHP puede limpiar tmp durante operaciones largas)
        $savedFiles = [];
        if ($request->hasFile('imagen_principal') && $request->file('imagen_principal')->isValid()) {
            $f = $request->file('imagen_principal');
            $savedFiles['principal'] = [
                'content'   => file_get_contents($f->getRealPath()),
                'extension' => $f->extension(),
                'mime'      => $f->getMimeType(),
            ];
        }
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $i => $f) {
                if ($f && $f->isValid()) {
                    $savedFiles['extra_' . $i] = [
                        'content'   => file_get_contents($f->getRealPath()),
                        'extension' => $f->extension(),
                        'mime'      => $f->getMimeType(),
                    ];
                }
            }
        }

        DB::beginTransaction();

        try {
            // Punto 2: Validación de datos con mensajes personalizados descuento
            $rules = [
                'item' => 'required|string|max:255',
                'id_categoria_item' => 'required|exists:categorias_item,id_categoria_item',
                'valor' => 'nullable|numeric|min:0',
                'descuento' => 'nullable|numeric|min:0',
                'presentacion' => 'required|string',
                'condicion' => 'required|integer|in:1,2,3,4',
                'tipo_trans' => 'required|integer|in:1,2,3',
                'imagen_principal' => 'required|file|mimes:mp4,mov,jpeg,png,jpg,gif,webp|max:20480', // 10MB para videos
                'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'peso_lbs' => 'nullable|numeric|min:0',
                'alto_cm' => 'nullable|numeric|min:0',
                'ancho_cm' => 'nullable|numeric|min:0',
                'profundo_cm' => 'nullable|numeric|min:0',
                'id_tipo_item' => 'nullable|numeric|min:0',
                 'colors' => 'nullable|array',
                'colors.*' => 'exists:colors,id_color',
                'stock.*' => 'nullable|integer|min:0',
                'cantidad' => 'required|numeric|min:0',

            ];

            $messages = [
                'item.required' => 'El nombre del producto es obligatorio',
                'id_categoria_item.required' => 'Debe seleccionar una categorí­a',
                'valor.required' => 'El precio es obligatorio',
                'valor.numeric' => 'El precio debe ser un níºmero válido',
                'valor.min' => 'El precio no puede ser negativo',
                'condicion.required' => 'Debe especificar la condición del producto',
                'condicion.in' => 'La condición seleccionada no es válida',
                'tipo_trans.required' => 'Debe seleccionar un tipo de transacción',
                'tipo_trans.in' => 'El tipo de transacción seleccionado no es válido',
                'imagen_principal.required' => 'La imagen/video principal es obligatorio',
                'imagen_principal.file' => 'El archivo debe ser una imagen o video válido',
                'imagen_principal.mimes' => 'Solo se permiten imágenes (JPEG, PNG, JPG, GIF, WEBP) o videos (MP4, MOV)',
                'imagen_principal.max' => 'El archivo no debe pesar más de 10MB',
                'imagenes.*.image' => 'Los archivos adicionales deben ser imágenes válidas',
                'imagenes.*.mimes' => 'Solo se permiten imágenes JPEG, PNG, JPG, GIF o WEBP',
                'imagenes.*.max' => 'Las imágenes no deben pesar más de 2MB',
                'peso_lbs.numeric' => 'El peso debe ser un níºmero válido',
                'alto_cm.numeric' => 'La altura debe ser un níºmero válido',
                'ancho_cm.numeric' => 'El ancho debe ser un níºmero válido',
                'profundo_cm.numeric' => 'La profundidad debe ser un níºmero válido',
                'presentacion.required' => 'Rellene la descripción de su producto o servicio, que se encuentra en la sección de Especificar Dimensiones.',
                'cantidad.numeric' => 'La cantidad debe ser un níºmero válido',
            ];

            $validatedData = $request->validate($rules, $messages);

            // Validar que la suma de stock de colores no supere la cantidad total
            if ($request->has('colors')) {
                $totalStockColores = 0;
                foreach ($request->colors as $colorId) {
                    $totalStockColores += (int) ($request->stock[$colorId] ?? 0);
                }
                if ($totalStockColores > (int) $validatedData['cantidad']) {
                    return back()->withErrors(['cantidad' => 'La suma del stock de los colores seleccionados (' . $totalStockColores . ') no puede superar la cantidad total del producto (' . $validatedData['cantidad'] . ').'])->withInput();
                }
            }

            // MANTENIENDO TU LOG DE VALIDACIÓN
            Log::debug('Datos validados correctamente', $validatedData);

            // Punto 4: Creación del í­tem (MANTENIENDO TU ESTRUCTURA ORIGINAL)
            $itemData = [
                'item' => $validatedData['item'],
                'id_categoria_item' => $validatedData['id_categoria_item'],
                'valor' => $validatedData['valor'],
                'descuento' => $validatedData['descuento'] ?? null,
                'presentacion' => $validatedData['presentacion'] ?? null,
                'condicion' => $validatedData['condicion'],
                'tipo_trans' => $validatedData['tipo_trans'],
                'id_user' => auth()->id(),
                'estatus' => 1,
                'fecha' => now(),
                'peso_lbs' => $validatedData['peso_lbs'] ?? 0,
                'alto_cm' => $validatedData['alto_cm'] ?? 0,
                'ancho_cm' => $validatedData['ancho_cm'] ?? 0,
                'profundo_cm' => $validatedData['profundo_cm'] ?? 0,
                'id_tipo_item' => $validatedData['id_tipo_item'] ?? 1,
                'tiene_video' => false // Inicializamos como falso
            ];

            // MANTENIENDO TU LOG DE CREACIí“N
            Log::debug('Intentando crear item con datos:', $itemData);


            $item = Item::create($itemData);
            Log::info('Item creado exitosamente', ['id_item' => $item->id_item]);

            // Crear registro en el inventario
            Inventario::create([
                'id_item' => $item->id_item,
                'cantidad' => $validatedData['cantidad'] ?? 1,
                'fecha' => now()
            ]);

            // ERP: Registrar entrada en Almacén
            $this->erpService->registrarEntradaRegistroItem($item, (int) ($validatedData['cantidad'] ?? 1));


            if ($request->has('colors')) {
                $colorsWithStock = [];
                foreach ($request->colors as $colorId) {
                    $stock = $request->stock[$colorId] ?? 0;
                    $colorsWithStock[$colorId] = ['stock' => $stock];
                }
                $item->colors()->sync($colorsWithStock);
            }


            //$item = Item::create($itemData);
            //Log::info('Item creado exitosamente', ['id_item' => $item->id_item]);

            // Guardar archivos desde memoria (preservados antes de la transaccion)
            if (!empty($savedFiles['principal'])) {
                $sf = $savedFiles['principal'];
                $isVideo = str_starts_with($sf['mime'], 'video/');
                $dir = $isVideo ? 'imgs/videos/items' : 'imgs/articulos/items';
                $prefix = $isVideo ? 'video_' : 'item_';
                $fileName = $prefix . $item->id_item . '_' . now()->format('YmdHis') . '_' . Str::random(10) . '.' . $sf['extension'];

                \App\Helpers\ImageHelper::guardarContenido($sf['content'], $dir, $fileName);

                DB::table('imagenes_item')->insert([
                    'nombre' => $fileName, 'extension' => $sf['extension'],
                    'id_item' => $item->id_item, 'orden_visualizacion' => 1,
                    'ruta' => $dir, 'tipo' => $isVideo ? 'video' : 'imagen', 'estado' => 'pendiente',
                ]);

                if ($isVideo) { $item->update(['tiene_video' => true]); }
                Log::info('Imagen/video principal guardado', ['file' => $fileName]);
            }

            $orden = 2;
            foreach ($savedFiles as $key => $sf) {
                if ($key === 'principal') continue;
                $fileName = 'item_' . $item->id_item . '_' . now()->format('YmdHis') . '_' . Str::random(8) . '.' . $sf['extension'];
                \App\Helpers\ImageHelper::guardarContenido($sf['content'], 'imgs/articulos/items', $fileName);
                DB::table('imagenes_item')->insert([
                    'nombre' => $fileName, 'extension' => $sf['extension'],
                    'id_item' => $item->id_item, 'orden_visualizacion' => $orden++,
                    'ruta' => 'imgs/articulos/items', 'tipo' => 'imagen', 'estado' => 'pendiente',
                ]);
            }

            DB::commit();
            Log::info('Transacción completada exitosamente');

            // Invalidar cache del home para reflejar el nuevo item
            \Illuminate\Support\Facades\Cache::forget('home_intercambio');
            \Illuminate\Support\Facades\Cache::forget('home_venta');

            return redirect()->route('items.user')->with('success', 'Talento creado exitosamente!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            // MANTENIENDO TU LOG DE ERROR DE VALIDACIí“N
            Log::error('Error de Validación', ['errors' => $e->errors()]);
            return back()->withErrors($e->validator)->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            // MANTENIENDO TUS LOGS DE ERROR GENERAL
            Log::error('Error en store()', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error al crear el talento: ' . $e->getMessage());
        }
    }

    protected function guardarImagen($file, $itemId, $orden, $estado = 'pendiente')
    {
        $mime = $file->getClientMimeType();
        $isVideo = str_starts_with($mime, 'video/');

        $allowedMimeTypes = ['image/jpeg','image/png','image/jpg','image/webp','video/mp4','video/quicktime','video/x-m4v'];
        if (!in_array($mime, $allowedMimeTypes)) {
            throw new \Exception('Tipo de archivo no permitido: ' . $mime);
        }

        $directory = $isVideo ? 'imgs/videos/items' : 'imgs/articulos/items';
        $prefix = $isVideo ? 'video_' : 'item_';
        $resultado = null;

        try {
            $resultado = \App\Helpers\ImageHelper::guardar($file, $directory, $prefix, $itemId);

            DB::table('imagenes_item')->insert([
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
                'url'      => asset($resultado['path']),
                'is_video' => $isVideo,
            ];
        } catch (\Exception $e) {
            \Log::error('Error al guardar archivo', ['error' => $e->getMessage()]);
            if ($resultado && !empty($resultado['path'])) {
                \App\Helpers\ImageHelper::eliminar($resultado['path']);
            }
            throw $e;
        }
    }


    public function soloCategoria29()
    {
        $perPage = request()->query('per_page', 5);
        $perPage = min($perPage, 10);

        $items = Item::with([
            'categoria:id_categoria_item,categoria',
            'direcciones:id_direccion,calle,N_casa_edificio,apto,id_municipio',
            'direcciones.municipio:id_municipio,nombre',
            'imagenes:id_imagen,id_item,ruta,orden_visualizacion'
        ])
            ->where('id_categoria_item', 29)
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return response()->json([
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'last_page' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
            'links' => [
                'first' => $items->url(1),
                'last' => $items->url($items->lastPage()),
                'prev' => $items->previousPageUrl(),
                'next' => $items->nextPageUrl(),
            ]
        ]);
    }

    public function itemsCategoria29()
    {
        // Forzar parámetro de categorí­a 29
        request()->merge(['category_id' => 29]);

        // Ejecutar el método index normal
        return $this->index();
    }
  
    public function index()
    {
        try {
            $perPage = request()->query('per_page', 10);
            $maxPerPage = 10;
            $perPage = min($perPage, $maxPerPage);

            $query = Item::with([
                'categoria:id_categoria_item,categoria',
                'direcciones:id_direccion,calle,N_casa_edificio,apto,id_municipio',
                'direcciones.municipio:id_municipio,nombre',
                'imagenes:id_imagen,id_item,ruta,orden_visualizacion'
            ]);

            // Filtro por categorí­a (opcional)
            if ($categoryId = request()->query('category_id')) {
                $query->where('id_categoria_item', $categoryId);
            }

            app()->setLocale('es');

            // Filtro por tipo de item
            if ($type = request()->query('type')) {
                $query->where('id_tipo_item', $type);
            }

            // Ordenación
            $sortField = request()->query('sort', 'created_at');
            $sortDirection = request()->query('direction', 'desc');

            $validSortFields = ['id', 'item', 'valor', 'presentacion', 'condicion', 'created_at'];
            $sortField = in_array($sortField, $validSortFields) ? $sortField : 'created_at';
            $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

            $query->orderBy($sortField, $sortDirection);

            // Campos seleccionables
            $fields = request()->query('fields');
            if ($fields) {
                $selectedFields = explode(',', $fields);
                $availableFields = ['id', 'item', 'valor', 'presentacion', 'condicion', 'id_categoria_item', 'id_tipo_item', 'created_at'];
                $filteredFields = array_intersect($selectedFields, $availableFields);

                if (!empty($filteredFields)) {
                    $query->select($filteredFields);
                }
            }

            $items = $query->paginate($perPage);

            // Redirección si la página está vací­a
            if ($items->isEmpty() && $items->currentPage() > 1) {
                return redirect()->route('items.index', array_merge(
                    request()->except('page'),
                    ['page' => $items->lastPage()]
                ));
            }

            // Respuesta JSON con metadatos
            return response()->json([
                'data' => $items->items(),
                'meta' => [
                    'current_page' => $items->currentPage(),
                    'per_page' => $items->perPage(),
                    'total' => $items->total(),
                    'last_page' => $items->lastPage(),
                    'from' => $items->firstItem(),
                    'to' => $items->lastItem(),
                ],
                'links' => [
                    'first' => $items->url(1),
                    'last' => $items->url($items->lastPage()),
                    'prev' => $items->previousPageUrl(),
                    'next' => $items->nextPageUrl(),
                ]
            ]);

        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'index',
                'request_params' => request()->query(),
                'user_id' => auth()->id() ?? null
            ]);

            return response()->json([
                'error' => 'Error al obtener la lista de items',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null
            ], 501);
        }
    }
   
    protected function logError(Throwable $exception, array $context = [])
    {
        $errorData = array_merge([
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ], $context);

        Log::error('Error en ItemController', $errorData);
    }

    public function showDetail($slug)
    {
        try {
            // Extraer el hash (última parte después del último guión)
            $parts = explode('-', $slug);
            $hash  = array_pop($parts);
            $id    = \App\Helpers\HashIdHelper::decode($hash);

            if (!$id) {
                abort(404, 'Producto no encontrado');
            }

            $item = Item::with([
                    'categoria',
                    'imagenes',
                    'usuario',
                    'inventarios',
                    'colors',
                    'direccionPredeterminada',
                ])
                ->findOrFail($id);

            // Registrar vista si no es el dueño
            if (auth()->id() !== $item->id_user) {
                \App\Models\ItemView::create([
                    'id_item'    => $item->id_item,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            $relatedItems = Item::where('id_categoria_item', $item->id_categoria_item)
                ->where('id_item', '!=', $id)
                ->where('estatus', 1)
                ->with('imagenes')
                ->inRandomOrder()
                ->limit(6)
                ->get();

            // Pasar config de descuento para categoría 29
            $configTarifa29 = null;
            $hojaVidaProveedor = null;
            if ((int) $item->id_categoria_item === 29) {
                // Cargar hoja de vida del proveedor para servicios
                $hojaVidaProveedor = \App\Models\HojaVida::where('id_user', $item->id_user)->first();

                if ((int) $item->tipo_trans === 1) {
                    $configTarifa29 = \App\Models\ConfigTarifaCategoria29::vigente();
                    if ((float) $configTarifa29->descuento_venta_masiva <= 0) {
                        $configTarifa29 = null;
                    }
                }
            }

            return view('productos.producto-detalle', [
                'item'               => $item,
                'relatedItems'       => $relatedItems,
                'configTarifa29'     => $configTarifa29,
                'hojaVidaProveedor'  => $hojaVidaProveedor,
            ]);

        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'showDetail',
                'slug'   => $slug,
                'request_params' => request()->all()
            ]);

            abort(404, 'Producto no encontrado');
        }
    }
   
    public function destroy($slug)
    {
        $id = \App\Helpers\HashIdHelper::decode($slug);
        try {
            $item = Item::findOrFail($id);

            // Eliminar imágenes asociadas
            foreach ($item->todasLasImagenes as $imagen) {
                Storage::delete(str_replace('storage/', 'public/', $imagen->ruta));
                $imagen->delete();
            }

            $item->delete();

            // Invalidar cache del home al eliminar un item
            \Illuminate\Support\Facades\Cache::forget('home_intercambio');
            \Illuminate\Support\Facades\Cache::forget('home_venta');

            if ($item->id_categoria_item==29) {
                return redirect()->route('items.admintalento')->with('success', 'Producto eliminado correctamente.');
            } else {
                return redirect()->route('items.user')->with('success', 'Producto eliminado correctamente.');

            }


        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'destroy',
                'id_item' => $id,
                'request_params' => request()->all()
            ]);

            return response()->json([
                'error' => 'Error al eliminar el item',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

  
    public function VerDetalle($slug)
    {
        try {
            $parts = explode('-', $slug);
            $hash  = array_pop($parts);
            $id    = \App\Helpers\HashIdHelper::decode($hash);

            if (!$id) {
                abort(404, 'Producto no encontrado');
            }

            Log::debug('Iniciando método VerDetalle', ['id' => $id]);

            $item = Item::with(['usuario', 'categoria', 'imagenes', 'direcciones'])
                ->findOrFail($id);

            Log::debug('Item encontrado correctamente', ['item_id' => $item->id]);

            // Registrar la visualización si no es el dueño
            if (auth()->id() != $item->id_user) {
                Log::debug('Registrando visualización del item', [
                    'user_id' => auth()->id(),
                    'item_user_id' => $item->id_user,
                    'ip' => request()->ip()
                ]);

                ItemView::create([
                    'id_item' => $item->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                Log::debug('Visualización registrada');
            }

            // Obtener productos relacionados (misma categorí­a)
            $relatedItems = Item::where('id_categoria_item', $item->id_categoria_item)
                ->where('id_item', '!=', $item->id_item)
                ->with(['categoria'])
                ->inRandomOrder()
                ->limit(4)
                ->get();

            Log::debug('Productos relacionados obtenidos', [
                'cantidad' => $relatedItems->count()
            ]);

            // Texto para condición
            $item->condicion_text = match ($item->condicion) {
                1 => 'Nuevo',
                2 => 'Usado - Como nuevo',
                3 => 'Usado - Buen estado',
                4 => 'Usado - Aceptable',
                default => 'No especificado'
            };

            Log::debug('Condición del producto definida', [
                'condicion_text' => $item->condicion_text
            ]);

            Log::debug('Renderizando vista productos.ver-detalle');

            return view('productos.ver-detalle', compact('item', 'relatedItems'));

        } catch (Throwable $e) {
            Log::error('Error en VerDetalle', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'slug' => $slug,
                'request_params' => request()->all()
            ]);

            abort(401, 'Producto no encontrado');
        }
    }

    public function show($id)
    {
        try {
            // Soportar ambos: ID numérico (legacy) y slug con hash
            if (ctype_digit((string) $id)) {
                $catId = (int) $id;
            } else {
                $catId = \App\Helpers\HashIdHelper::decode($id);
            }

            if (!$catId) {
                abort(404, 'Categoría no encontrada');
            }

            $categoria = CategoriaItem::findOrFail($catId);

            // Redirigir ID numérico al slug
            if (ctype_digit((string) $id)) {
                return redirect()->route('categorias.show', $categoria->slug, 301);
            }

            $items = Item::where('id_categoria_item', $catId);

            if (request()->has('sort')) {
                $sort = request('sort');

                switch ($sort) {
                    case 'price_asc':
                        $items->orderBy('valor', 'asc');
                        break;
                    case 'price_desc':
                        $items->orderBy('valor', 'desc');
                        break;
                    case 'name_asc':
                        $items->orderBy('item', 'asc');
                        break;
                    case 'name_desc':
                        $items->orderBy('item', 'desc');
                        break;
                    case 'newest':
                        $items->orderBy('fecha', 'desc');
                        break;
                    case 'oldest':
                        $items->orderBy('fecha', 'asc');
                        break;
                }
            }
            $items = $items->with(['categoria', 'direcciones', 'imagenes', 'inventarios'])->paginate(15);

            return view('categorias.por-categoria', compact('categoria', 'items'));
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'show',
                'id_categoria' => $id,
                'request_params' => request()->all()
            ]);

            abort(404, 'Categori­a no encontrada');
        }
    }

    /**
     * Devuelve datos básicos de un item para mostrar en modales (JSON).
     */
    public function info($id)
    {
        $item = \App\Models\Item::with('imagenes')->find($id);
        if (!$item) {
            return response()->json(['error' => 'No encontrado'], 404);
        }
        $img = $item->imagenes->sortBy('orden_visualizacion')->first();
        return response()->json([
            'id'        => $item->id_item,
            'nombre'    => $item->item,
            'valor'     => $item->valor,
            'tipo_trans'=> $item->tipo_trans,
            'imagen'    => $img ? asset('storage/'.$img->ruta.'/'.$img->nombre) : null,
        ]);
    }

    public function search(Request $request)
    {
        try {
            $query = Item::query();

            if ($request->has('q')) {
                $query->where(function ($query) use ($request) {
                    $q = str_replace(['%', '_'], ['\\%', '\\_'], $request->q);
                    $query->where('item', 'like', '%' . $q . '%')
                        ->orWhere('presentacion', 'like', '%' . $q . '%');
                });
            }

            if ($request->has('categoria')) {
                $query->where('id_categoria_item', $request->categoria);
            }

            if ($request->has('tipo_trans')) {
                $query->where('tipo_trans', $request->tipo);
            }

            if ($request->has('min_valor') && $request->has('max_valor')) {
                $query->whereBetween('valor', [$request->min_valor, $request->max_valor]);
            } elseif ($request->has('min_valor')) {
                $query->where('valor', '>=', $request->min_valor);
            } elseif ($request->has('max_valor')) {
                $query->where('valor', '<=', $request->max_valor);
            }

            $perPage = $request->per_page ?? 5;
            $perPage = min($perPage, 10);

            return $query->with(['categoria', 'direcciones', 'imagenes'])
                ->paginate($perPage);
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'search',
                'request_params' => $request->all()
            ]);

            return response()->json([
                'error' => 'Error en la bíºsqueda',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Muestra todas las categorías excepto las destacadas del home.
     */
    public function otrasCategorias()
    {
        // IDs de categorías que ya aparecen en el home o que se desean excluir (ej. Monetario = 10)
        $idsHome = [26, 27, 20, 19, 16, 4, 29, 10];

        $categorias = CategoriaItem::whereNotIn('id_categoria_item', $idsHome)
            ->orderBy('categoria', 'asc')
            ->get();

        return view('categorias.otras-categorias', compact('categorias'));
    }


    public function porCategoria($slug)
    {
        try {
            // Soportar ambos formatos: ID numérico (legacy) y slug con hash
            if (ctype_digit($slug)) {
                $id = (int) $slug;
            } else {
                $id = \App\Helpers\HashIdHelper::decode($slug);
            }

            if (!$id) {
                abort(404, 'Categoría no encontrada');
            }

            $categoria = CategoriaItem::findOrFail($id);

            // Si llegó con ID numérico, redirigir al slug correcto (SEO)
            if (ctype_digit($slug)) {
                return redirect()->route('categorias.show', $categoria->slug, 301);
            }

            $query = Item::where('id_categoria_item', $id);

            switch (request('sort')) {
                case 'newest':
                    $query->orderBy('id_item', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('id_item', 'asc');
                    break;
                case 'price_asc':
                    $query->orderBy('valor', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('valor', 'desc');
                    break;
                case 'name_asc':
                    $query->orderBy('item', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('item', 'desc');
                    break;
                default:
                    $query->orderBy('id_item', 'desc');
            }

            if (request()->has('search')) {
                $s = str_replace(['%', '_'], ['\\%', '\\_'], request('search'));
                $query->where('item', 'like', '%' . $s . '%');
            }

            $items = $query->paginate(12);

            return view('categorias.por-categoria', compact('items', 'categoria'));
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'porCategoria',
                'id_categoria' => $id,
                'request_params' => request()->all()
            ]);

            abort(404, 'Categorí­a no encontrada');
        }
    }

    public function byCategory($id)
    {
        try {
            return Item::where('id_categoria_item', $id)
                ->where('estatus', 2)
                ->with('imagenes')
                ->orderBy('id_item', 'desc')
                ->paginate(12);
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'byCategory',
                'id_categoria' => $id,
                'request_params' => request()->all()
            ]);

            return response()->json([
                'error' => 'Error al obtener items por categorí­a',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function indexPublic()
    {
        try {
            return Item::with(['categoria', 'imagenes'])
                ->where('estatus', 2)
                ->orderBy('created_at', 'desc')
                ->paginate(12);
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'indexPublic',
                'request_params' => request()->all()
            ]);

            return response()->json([
                'error' => 'Error al obtener items píºblicos',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


    public function talentoAdd()
    {
         
        try {
            \Log::info('Iniciando método talentoAdd1');
            $categorias = CategoriaItem::all();
         
            \Log::debug('Categorí­as obtenidas:', $categorias->toArray());

            if ($categorias->isEmpty()) {
                \Log::info('No hay categorí­as, creando una por defecto');
                CategoriaItem::create(['categoria' => 'General']);
                $categorias = CategoriaItem::all();
                \Log::debug('Categorí­as después de Creación:', $categorias->toArray());
            }

            // Verificación final antes de enviar a la vista
            if ($categorias->isEmpty()) {
                \Log::error('No se pudieron obtener categorí­as');
                throw new \Exception('No hay categorí­as disponibles');
            }

            $direccionesCount = \App\Models\Direcciones::where('id_user', auth()->id())->count();
            return view('talentos.agregar-talentos', compact('categorias', 'direccionesCount'));

        } catch (Throwable $e) {
            \Log::error('Error en create talento: ' . $e->getMessage());
            abort(500, 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'item_color', 'item_id', 'color_id')
            ->withPivot('stock');
    }

    public function create()
    {
        try {
            \Log::info('Iniciando método create');
            $categorias = CategoriaItem::all();
            $colors = Color::all(); // Obtener todos los colores
            $groupedColors = $this->groupColorsByFamily($colors);

            \Log::debug('Categorí­as obtenidas:', $categorias->toArray());

            if ($categorias->isEmpty()) {
                \Log::info('No hay categorí­as, creando una por defecto');
                CategoriaItem::create(['categoria' => 'General']);
                $categorias = CategoriaItem::all();
                \Log::debug('Categorí­as después de Creación:', $categorias->toArray());
            }

            // Verificación final antes de enviar a la vista
            if ($categorias->isEmpty()) {
                \Log::error('No se pudieron obtener categorí­as');
                throw new \Exception('No hay categorí­as disponibles');
            }

            return view('addProduct.addProduct', compact('categorias', 'groupedColors', 'colors'));

        } catch (Throwable $e) {
            \Log::error('Error en create: ' . $e->getMessage());
            abort(500, 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    public function storeItem(Request $request)
    {
        try {
            $validated = $request->validate([
                'item' => 'required|string|max:255',
                'id_categoria_item' => 'required|exists:categorias_item,id_categoria_item',
                'valor' => 'required|numeric',
                'descuento' => 'nullable|numeric',
                'presentacion' => 'nullable|string',
                'peso_lbs' => 'nullable|numeric',
                'alto_cm' => 'nullable|numeric',
                'ancho_cm' => 'nullable|numeric',
                'profundo_cm' => 'nullable|numeric',
                'tipo_trans' => 'nullable|string',
                'condicion' => 'nullable|string',
                'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $data = $validated;
            $data['id_user'] = auth()->id();
            $data['estatus'] = 1;

            $item = Item::create($data);

            if ($request->hasFile('imagenes')) {
                foreach ($request->file('imagenes') as $index => $imagen) {
                    $path = $imagen->store('public/items');
                    ImagenItem::create([
                        'id_item' => $item->id_item,
                        'ruta' => str_replace('public/', 'storage/', $path),
                        'principal' => $index === 0
                    ]);
                }
            }

            return redirect()->route('items.showDetail', $item->id_item)
                ->with('success', 'Item creado exitosamente!');
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'storeItem',
                'input_data' => $this->filterSensitiveData($request->all()),
                'validation_errors' => $e instanceof \Illuminate\Validation\ValidationException
                    ? $e->errors()
                    : null
            ]);

            return back()->withInput()->withErrors([
                'error' => 'Error al crear el item. Por favor intente nuevamente.'
            ]);
        }
    }

    public function showItemsTipo2y3()
    {
        Log::info('[showItemsTipo2y3] INICIO', ['user_id' => auth()->id(), 'auth' => auth()->check()]);
        try {
            Log::info('[showItemsTipo2y3] Consultando items...');
            $items = Item::whereIn('tipo_trans', [2, 3])
                ->where('estatus', 1)
                ->with(['categoria', 'direccionPredeterminada.provincia', 'imagenes', 'inventarios'])
                ->orderBy('fecha', 'desc')
                ->paginate(12);

            Log::info('[showItemsTipo2y3] Items cargados: ' . $items->count() . '. Renderizando vista...');
            $view = view('blank-intercambiar.intercambio', compact('items'));
            Log::info('[showItemsTipo2y3] Vista renderizada OK');
            return $view;
        } catch (Throwable $e) {
            Log::error('[showItemsTipo2y3] ERROR: ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => collect(explode("\n", $e->getTraceAsString()))->take(8)->implode("\n"),
            ]);
            $this->logError($e, [
                'method' => 'showItemsTipo2y3',
                'request_params' => request()->all()
            ]);
            abort(500, 'Error al cargar los items para intercambio');
        }
    }

    public function showItemsTipo1()
    {
        try {
            $items = Item::where('tipo_trans', 1)
                ->where('estatus', 1)
                ->with(['categoria', 'direccionPredeterminada.provincia', 'imagenes', 'inventarios'])
                ->orderBy('fecha', 'desc')
                ->paginate(12);

            return view('compras.compra', compact('items'));
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'showItemsTipo1',
                'request_params' => request()->all()
            ]);

            abort(500, 'Error al cargar los items para compra');
        }
    }

    public function search_header(Request $request)
    {
        try {
            $searchTerm = $request->q ?? '';
            $hasSearch = !empty($searchTerm);
            
            $query = Item::where('estatus', 1)
                ->whereIn('tipo_trans', [1, 2, 3]);

            if ($hasSearch) {
                $cleanTerm = str_replace(['%', '_'], ['\\%', '\\_'], $searchTerm);
                $query->where(function ($q) use ($cleanTerm) {
                    $q->where('item', 'like', '%' . $cleanTerm . '%')
                        ->orWhere('presentacion', 'like', '%' . $cleanTerm . '%')
                        ->orWhereHas('categoria', function ($catQuery) use ($cleanTerm) {
                            $catQuery->where('categoria', 'like', '%' . $cleanTerm . '%');
                        });
                });
            }

            $items = $query->orderByDesc('fecha')
                ->with(['categoria', 'direcciones', 'imagenes'])
                ->paginate(12)
                ->appends($request->query());

            // Si no hay resultados y hubo búsqueda, obtener items relevantes
            $noResults = $hasSearch && $items->isEmpty();
            $relevantItems = collect();
            
            if ($noResults) {
                $relevantItems = Item::where('estatus', 1)
                    ->whereIn('tipo_trans', [1, 2, 3])
                    ->with(['categoria', 'direcciones', 'imagenes'])
                    ->inRandomOrder()
                    ->limit(12)
                    ->get();
            }

            return view('compras.compra', compact('items', 'searchTerm', 'noResults', 'relevantItems'));
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'search_header',
                'request_params' => $request->all()
            ]);

            abort(500, 'Error en la búsqueda');
        }
    }
    public function gestion()
    {
        return $this->userItems();
    }

    public function userItems()
    {
        try {
            $items = Item::where('id_user', auth()->id())
                ->where('id_categoria_item', '!=', 29)
                ->with(['categoria', 'todasLasImagenes'])
                ->withCount('views')
                ->orderByDesc('fecha')
                ->paginate(10);

            return view('productos.mis-productos', compact('items'));
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'userItems',
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', 'Error al cargar tus productos');
        }
    }
    public function userItemstalento()
    {
        try {
            $items = Item::where('id_user', auth()->id())
                ->where('id_categoria_item', 29) // â† Filtrar por categorí­a 29
                ->with(['categoria', 'todasLasImagenes'])
                ->withCount('views')
                ->orderByDesc('fecha')
                ->paginate(10);

            return view('talentos.admin-talento', compact('items'));
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'userItemstalento',
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', 'Error al cargar tus productos');
        }
    }

    public function edit($slug)
    {
        $id = \App\Helpers\HashIdHelper::decode($slug);
        $item = Item::with('todasLasImagenes')->findOrFail($id);
        $categorias = CategoriaItem::all();

        // Obtener la cantidad del inventario
        $inventario = Inventario::where('id_item', $item->id_item)->first();
        $cantidad = $inventario ? $inventario->cantidad : 0;


        // Obtener todos los colores
        $colors = Color::all();
        // Agrupar colores por familia usando tu método
        $groupedColors = $this->groupColorsByFamily($colors);

        // Colores seleccionados y stock
        $selectedColors = $item->colors->pluck('id_color')->toArray();
        $stockByColor = [];

        foreach ($item->colors as $color) {
            $stockByColor[$color->id_color] = $color->pivot->stock;
        }

        return view('addProduct.Edit-form', compact(
            'item',
            'categorias',
            'cantidad',
            'groupedColors',
            'selectedColors',
            'stockByColor',
            'colors'
        ));
    }

    public function talentoedit($slug)
    {
        $id = \App\Helpers\HashIdHelper::decode($slug);
        $item = Item::with('todasLasImagenes')->findOrFail($id);
        $categorias = CategoriaItem::all();
        try {
            $item = Item::where('id_user', auth()->id())
                ->findOrFail($id);

            $categorias = CategoriaItem::all();

            return view('talentos.talentoEdit-form', [
                'item' => $item,
                'categorias' => $categorias
            ]);
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'edit',
                'item_id' => $id,
                'user_id' => auth()->id()
            ]);
            return view('talentos.talentoEdit-form', compact('item', 'categorias'));

        }
    }

  
    public function update(Request $request, $slug)
    {
        $id = \App\Helpers\HashIdHelper::decode($slug);
        Log::info('Inicio de update()', [
            'user_id' => auth()->id(),
            'item_id' => $id,
            'request_data' => $request->except(['imagen_principal', 'imagenes'])
        ]);

        DB::beginTransaction();

        try {
            $item = Item::where('id_user', auth()->id())->findOrFail($id);

            // ── Resguardar archivos ANTES de validar (el Validator puede invalidar el temp file) ──
            $archivosPrincipal = null;
            $archivosSecundarios = [];
            if ($request->hasFile('imagen_principal') && $request->file('imagen_principal')->isValid()) {
                $f = $request->file('imagen_principal');
                $tmpName = 'img_' . uniqid() . '.' . $f->getClientOriginalExtension();
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tmpName;
                copy($f->getRealPath(), $tmpPath);
                $archivosPrincipal = new \Illuminate\Http\UploadedFile($tmpPath, $f->getClientOriginalName(), $f->getClientMimeType(), null, true);
            }
            if ($request->hasFile('imagenes')) {
                foreach ($request->file('imagenes') as $f) {
                    if (!$f || !$f->isValid()) { $archivosSecundarios[] = null; continue; }
                    $tmpName = 'img_' . uniqid() . '.' . $f->getClientOriginalExtension();
                    $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tmpName;
                    copy($f->getRealPath(), $tmpPath);
                    $archivosSecundarios[] = new \Illuminate\Http\UploadedFile($tmpPath, $f->getClientOriginalName(), $f->getClientMimeType(), null, true);
                }
            }

            $rules = [
                'item' => 'required|string|max:255',
                'id_categoria_item' => 'required|exists:categorias_item,id_categoria_item',
                'valor' => 'nullable|numeric|min:0',
                'descuento' => 'nullable|numeric|min:0',
                'presentacion' => 'required|string',
                'condicion' => 'required|integer|in:1,2,3,4',
                'tipo_trans' => 'required|integer|in:1,2,3',
                'peso_lbs' => 'nullable|numeric|min:0',
                'alto_cm' => 'nullable|numeric|min:0',
                'ancho_cm' => 'nullable|numeric|min:0',
                'profundo_cm' => 'nullable|numeric|min:0',
                'id_tipo_item' => 'nullable|numeric|min:0',
                'cantidad' => 'required|numeric|min:0',
                'colors' => 'nullable|array',
                'colors.*' => 'exists:colors,id_color',
                'stock.*' => 'nullable|integer|min:0',
            ];

            $messages = [
                'item.required' => 'El nombre del producto es obligatorio',
                'id_categoria_item.required' => 'Debe seleccionar una categorí­a',
                'valor.numeric' => 'El precio debe ser un níºmero válido',
                'valor.min' => 'El precio no puede ser negativo',
                'condicion.required' => 'Debe especificar la condición del producto',
                'condicion.in' => 'La condición seleccionada no es válida',
                'tipo_trans.required' => 'Debe seleccionar un tipo de transacción',
                'tipo_trans.in' => 'El tipo de transacción seleccionado no es válido',
                'peso_lbs.numeric' => 'El peso debe ser un níºmero válido',
                'alto_cm.numeric' => 'La altura debe ser un níºmero válido',
                'ancho_cm.numeric' => 'El ancho debe ser un níºmero válido',
                'profundo_cm.numeric' => 'La profundidad debe ser un níºmero válido',
                'presentacion.required' => 'Rellene la descripción de su producto o servicio, que se encuentra en la sección de Especificar Dimensiones.',
                'cantidad.required' => 'La cantidad es obligatorio',
                'cantidad.numeric' => 'La cantidad debe ser un níºmero válido',
                'cantidad.min' => 'La cantidad no puede ser negativa',
            ];


            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();
            if (isset($validated['valor'])) {
                $validated['valor'] = str_replace(',', '', $validated['valor']);
            }

            // Validar que la suma de stock de colores no supere la cantidad total en edición
            if ($request->has('colors')) {
                $totalStockColores = 0;
                foreach ($request->colors as $colorId) {
                    $totalStockColores += (int) ($request->stock[$colorId] ?? 0);
                }
                if ($totalStockColores > (int) $validated['cantidad']) {
                    return back()->withErrors(['cantidad' => 'La suma del stock de los colores seleccionados (' . $totalStockColores . ') no puede superar la cantidad total del producto (' . $validated['cantidad'] . ').'])->withInput();
                }
            }

            // Actualizar colores y stock
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

            // Actualizar o crear registro en el inventario
                $inventario = Inventario::where('id_item', $id)->first();

                if ($inventario) {
                $inventario->update([
                    'cantidad' => $validated['cantidad'],
                    'fecha' => now()
                ]);
                } else {
                    Inventario::create([
                        'id_item' => $id,
                        'cantidad' => $validated['cantidad'],
                        'fecha' => now()
                    ]);
                }

            $item->update($validated);

            // ── Imagen principal ──
            if ($archivosPrincipal) {
                // Si cambia la principal, borrar TODAS las imágenes viejas del item
                foreach ($item->todasLasImagenes as $imgVieja) {
                    \App\Helpers\ImageHelper::eliminar($imgVieja->ruta . '/' . $imgVieja->nombre);
                    $imgVieja->delete();
                }
                $this->guardarImagen($archivosPrincipal, $item->id_item, 1, 'pendiente');

                // Guardar nuevas secundarias junto con la nueva principal
                if (!empty($archivosSecundarios)) {
                    $orden = 1;
                    foreach ($archivosSecundarios as $img) {
                        if (!$img) continue;
                        $orden++;
                        $this->guardarImagen($img, $item->id_item, $orden, 'pendiente');
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

                if (!empty($archivosSecundarios)) {
                    $maxOrden = $item->todasLasImagenes()->max('orden_visualizacion') ?? 1;
                    foreach ($archivosSecundarios as $img) {
                        if (!$img) continue;
                        $maxOrden++;
                        $this->guardarImagen($img, $item->id_item, $maxOrden, 'pendiente');
                    }
                }
            }

            DB::commit();
            return redirect()->route('items.user')->with('success', 'Talento actualizado exitosamente');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error general al actualizar producto', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function talentoupdate(Request $request, $slug)
    {
        $id = \App\Helpers\HashIdHelper::decode($slug);
        Log::info('Inicio de update()', [
            'user_id' => auth()->id(),
            'item_id' => $id,
            'request_data' => $request->except(['imagen_principal', 'imagenes'])
        ]);

        DB::beginTransaction();

        try {
            $item = Item::where('id_user', auth()->id())->findOrFail($id);

            // ── Resguardar archivos ANTES de validar ──
            $archivosPrincipal = null;
            $archivosSecundarios = [];
            if ($request->hasFile('imagen_principal') && $request->file('imagen_principal')->isValid()) {
                $f = $request->file('imagen_principal');
                $tmpName = 'img_' . uniqid() . '.' . $f->getClientOriginalExtension();
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tmpName;
                copy($f->getRealPath(), $tmpPath);
                $archivosPrincipal = new \Illuminate\Http\UploadedFile($tmpPath, $f->getClientOriginalName(), $f->getClientMimeType(), null, true);
            }
            if ($request->hasFile('imagenes')) {
                foreach ($request->file('imagenes') as $f) {
                    if (!$f || !$f->isValid()) { $archivosSecundarios[] = null; continue; }
                    $tmpName = 'img_' . uniqid() . '.' . $f->getClientOriginalExtension();
                    $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tmpName;
                    copy($f->getRealPath(), $tmpPath);
                    $archivosSecundarios[] = new \Illuminate\Http\UploadedFile($tmpPath, $f->getClientOriginalName(), $f->getClientMimeType(), null, true);
                }
            }

            // Validaciones
            $rules = [
                'item' => 'required|string|max:255',
                'id_categoria_item' => 'required|exists:categorias_item,id_categoria_item',
                'valor' => 'required|numeric|min:0',
                'descuento' => 'nullable|numeric|min:0',
                'presentacion' => 'required|string',
                'condicion' => 'required|integer|in:1,2,3,4',
                'tipo_trans' => 'required|integer|in:1,2,3',
                'peso_lbs' => 'nullable|numeric|min:0',
                'alto_cm' => 'nullable|numeric|min:0',
                'ancho_cm' => 'nullable|numeric|min:0',
                'profundo_cm' => 'nullable|numeric|min:0',
                'id_tipo_item' => 'required|numeric',
                'estatus' => 'required|integer|in:1,2'
            ];

            $messages = [
                'item.required' => 'El nombre del producto es obligatorio',
                'id_categoria_item.required' => 'Debe seleccionar una categorí­a',
                'valor.required' => 'El precio es obligatorio',
                'valor.numeric' => 'El precio debe ser un níºmero válido',
                'valor.min' => 'El precio no puede ser negativo',
                'condicion.required' => 'Debe especificar la condición del producto',
                'condicion.in' => 'La condición seleccionada no es válida',
                'tipo_trans.required' => 'Debe seleccionar un tipo de transacción',
                'tipo_trans.in' => 'El tipo de transacción seleccionado no es válido',
                'peso_lbs.numeric' => 'El peso debe ser un níºmero válido',
                'alto_cm.numeric' => 'La altura debe ser un níºmero válido',
                'ancho_cm.numeric' => 'El ancho debe ser un níºmero válido',
                'profundo_cm.numeric' => 'La profundidad debe ser un níºmero válido',
                'presentacion.required' => 'Rellene la descripción de su producto o servicio, que se encuentra en la sección de Especificar Dimensiones.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $validated = $validator->validated();
            if (isset($validated['valor'])) {
                $validated['valor'] = str_replace(',', '', $validated['valor']);
            }

            // Actualizar datos del item
            $item->update($validated);

            // ── Imagen principal ──
            if ($archivosPrincipal) {
                // Si cambia la principal, borrar TODAS las imágenes viejas del item
                foreach ($item->todasLasImagenes as $imgVieja) {
                    \App\Helpers\ImageHelper::eliminar($imgVieja->ruta . '/' . $imgVieja->nombre);
                    $imgVieja->delete();
                }
                $this->guardarImagen($archivosPrincipal, $item->id_item, 1, 'pendiente');

                // Guardar nuevas secundarias junto con la nueva principal
                if (!empty($archivosSecundarios)) {
                    $orden = 1;
                    foreach ($archivosSecundarios as $img) {
                        if (!$img) continue;
                        $orden++;
                        $this->guardarImagen($img, $item->id_item, $orden, 'pendiente');
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

                if (!empty($archivosSecundarios)) {
                    $maxOrden = $item->todasLasImagenes()->max('orden_visualizacion') ?? 1;
                    foreach ($archivosSecundarios as $img) {
                        if (!$img) continue;
                        $maxOrden++;
                        $this->guardarImagen($img, $item->id_item, $maxOrden, 'pendiente');
                    }
                }
            }

            DB::commit();
            return redirect()->route('items.admintalento')->with('success', 'Talento actualizado exitosamente');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error general al actualizar producto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'item_id' => $id,
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Error al actualizar el producto: ' . $e->getMessage()])
                ->withInput()
                ->with('error', 'Error al actualizar el producto. Por favor intente nuevamente.');
        }
    }

    private function groupColorsByFamily($colors)
    {
        $families = [
            'Rojos' => [],
            'Naranjas' => [],
            'Amarillos' => [],
            'Verdes' => [],
            'Azules' => [],
            'Púrpuras' => [],
            'Rosas' => [],
            'Neutrales' => [],
        ];

        foreach ($colors as $color) {
            $hsl = $this->hexToHsl($color->codigo_hex);
            $h = $hsl['h'];
            $s = $hsl['s'] * 100;
            $l = $hsl['l'] * 100;

            // Determinar la familia
            $family = 'Neutrales';
            if ($s > 10 && $l > 5 && $l < 95) {
                if ($h < 15 || $h >= 345) {
                    $family = 'Rojos';
                } elseif ($h < 45) {
                    $family = 'Naranjas';
                } elseif ($h < 75) {
                    $family = 'Amarillos';
                } elseif ($h < 165) {
                    $family = 'Verdes';
                } elseif ($h < 255) {
                    $family = 'Azules';
                } elseif ($h < 285) {
                    $family = 'Púrpuras';
                } else { // 285-344
                    $family = 'Rosas';
                }
            }

            // Guardamos el color junto con el matiz
            $families[$family][] = [
                'color' => $color,
                'hue' => $h,
            ];
        }

        // Ordenar cada familia por matiz (Hue)
        foreach ($families as &$familyGroup) {
            usort($familyGroup, fn($a, $b) => $a['hue'] <=> $b['hue']);
            $familyGroup = array_map(fn($item) => $item['color'], $familyGroup);
        }

        // Asegurar orden de familias
        $orderedFamilies = [
            'Rojos' => $families['Rojos'],
            'Naranjas' => $families['Naranjas'],
            'Amarillos' => $families['Amarillos'],
            'Verdes' => $families['Verdes'],
            'Azules' => $families['Azules'],
            'Púrpuras' => $families['Púrpuras'],
            'Rosas' => $families['Rosas'],
            'Neutrales' => $families['Neutrales'],
        ];

        return $orderedFamilies;
    }


    private function hexToHsl($hex)
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;

        $l = ($max + $min) / 2;
        $s = 0;

        if ($delta > 0) {
            $s = $l > 0.5 ? $delta / (2 - $max - $min) : $delta / ($max + $min);
        }

        $h = 0;
        if ($delta > 0) {
            if ($max == $r) {
                $h = ($g - $b) / $delta + ($g < $b ? 6 : 0);
            } elseif ($max == $g) {
                $h = ($b - $r) / $delta + 2;
            } else {
                $h = ($r - $g) / $delta + 4;
            }
            $h *= 60;
        }

        return [
            'h' => $h,
            's' => $s,
            'l' => $l
        ];
    }


    /**
     * Filtra datos sensibles antes de registrarlos en los logs talentoupdate
     */
    protected function filterSensitiveData(array $data): array
    {
        $sensitiveKeys = ['password', 'token', 'api_key', 'clave'];

        return collect($data)->map(function ($value, $key) use ($sensitiveKeys) {
            return in_array($key, $sensitiveKeys) ? '***FILTERED***' : $value;
        })->toArray();
    }
    public function getItemsUsuario(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = auth()->user();

            if (!$user) {
                Log::warning('Usuario no autenticado intentó acceder a items-usuario', [
                    'ip' => $request->ip(),
                    'route' => $request->path()
                ]);
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            // Traer los items de ese usuario "” solo intercambiables (tipo_trans 2 o 3)
            $items = Item::where('id_user', $user->id)
                ->whereIn('tipo_trans', [2, 3])
                ->where('estatus', 1)
                ->get(['id_item', 'item', 'valor', 'tipo_trans', 'condicion']);

            Log::info('Items del usuario obtenidos correctamente', [
                'user_id' => $user->id,
                'items_count' => $items->count(),
                'items' => $items->pluck('id_item')->toArray()
            ]);

            return response()->json($items);

        } catch (\Exception $e) {
            Log::error('Error al obtener items del usuario', [
                'user_id' => $user->id ?? null,
                'error_message' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}

