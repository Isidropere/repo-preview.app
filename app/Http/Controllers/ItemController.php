<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ImagenItem;
use App\Models\Color;
use App\Models\ItemColor;
use App\Models\CategoriaItem;
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
    public function AddTalento(Request $request)
    {
        // Punto 1: Verificar recepciÃ³n de datos (MANTENIENDO TUS LOGS)
        Log::info('Inicio de store() - Datos recibidos:', [
            'form_data' => $request->except(['imagen_principal', 'imagenes']),
            'has_imagen_principal' => $request->hasFile('imagen_principal'),
            'num_imagenes' => $request->hasFile('imagenes') ? count($request->file('imagenes')) : 0
        ]);

        DB::beginTransaction();

        try {
            // Punto 2: ValidaciÃ³n de datos con mensajes personalizados descuento
            $rules = [
                'item' => 'required|string|max:255',
                'id_categoria_item' => 'required|exists:categorias_item,id_categoria_item',
                'valor' => 'required|numeric|min:0',
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
                'id_tipo_item' => 'nullable|numeric|min:0'
            ];

            $messages = [
                'item.required' => 'El nombre del producto es obligatorio',
                'id_categoria_item.required' => 'Debe seleccionar una categorÃ­a',
                'valor.required' => 'El precio es obligatorio',
                'valor.numeric' => 'El precio debe ser un nÃºmero vÃ¡lido',
                'valor.min' => 'El precio no puede ser negativo',
                'condicion.required' => 'Debe especificar la condiciÃ³n del producto',
                'condicion.in' => 'La condiciÃ³n seleccionada no es vÃ¡lida',
                'tipo_trans.required' => 'Debe seleccionar un tipo de transacciÃ³n',
                'tipo_trans.in' => 'El tipo de transacciÃ³n seleccionado no es vÃ¡lido',
                'imagen_principal.required' => 'La imagen/video principal es obligatorio',
                'imagen_principal.file' => 'El archivo debe ser una imagen o video vÃ¡lido',
                'imagen_principal.mimes' => 'Solo se permiten imÃ¡genes (JPEG, PNG, JPG, GIF, WEBP) o videos (MP4, MOV)',
                'imagen_principal.max' => 'El archivo no debe pesar mÃ¡s de 10MB',
                'imagenes.*.image' => 'Los archivos adicionales deben ser imÃ¡genes vÃ¡lidas',
                'imagenes.*.mimes' => 'Solo se permiten imÃ¡genes JPEG, PNG, JPG, GIF o WEBP',
                'imagenes.*.max' => 'Las imÃ¡genes no deben pesar mÃ¡s de 2MB',
                'peso_lbs.numeric' => 'El peso debe ser un nÃºmero vÃ¡lido',
                'alto_cm.numeric' => 'La altura debe ser un nÃºmero vÃ¡lido',
                'ancho_cm.numeric' => 'El ancho debe ser un nÃºmero vÃ¡lido',
                'profundo_cm.numeric' => 'La profundidad debe ser un nÃºmero vÃ¡lido',
                'presentacion.required' => 'Rellene la descripciÃ³n de su producto o servicio, que se encuentra en la secciÃ³n de Especificar Dimensiones.',
            ];

            $validatedData = $request->validate($rules, $messages);

            // MANTENIENDO TU LOG DE VALIDACIÃ“N
            Log::debug('Datos validados correctamente', $validatedData);
            // Interceptar categoría 29: redirigir a flujo de pago
            // Aplica cuando la categoría es 29 y la transacción es venta (1), intercambio (2) o ambas (3)
            $esCategoria29 = (int) $validatedData['id_categoria_item'] === 29;
            $tipoTransConPago = in_array((int) $validatedData['tipo_trans'], [1, 2, 3]);
            if ($esCategoria29 && $tipoTransConPago) {
                $uuid = Str::uuid()->toString();
                $tempDir = 'temp/' . $uuid;

                // Guardar datos del formulario (sin archivos) en sesión
                $datosSinArchivos = collect($validatedData)->except(['imagen_principal', 'imagenes'])->toArray();
                session(['talento_pendiente_data' => $datosSinArchivos, 'talento_pendiente_uuid' => $uuid]);

                // Guardar archivos temporalmente
                $archivosTemp = [];
                if ($request->hasFile('imagen_principal')) {
                    $file = $request->file('imagen_principal');
                    $nombre = 'principal_' . Str::random(10) . '.' . $file->extension();
                    Storage::disk('local')->putFileAs($tempDir, $file, $nombre);
                    $archivosTemp['imagen_principal'] = $tempDir . '/' . $nombre;
                }
                if ($request->hasFile('imagenes')) {
                    foreach ($request->file('imagenes') as $i => $file) {
                        if ($file->isValid()) {
                            $nombre = 'adicional_' . $i . '_' . Str::random(8) . '.' . $file->extension();
                            Storage::disk('local')->putFileAs($tempDir, $file, $nombre);
                            $archivosTemp['imagenes'][] = $tempDir . '/' . $nombre;
                        }
                    }
                }
                session(['talento_pendiente_files' => $archivosTemp]);

                return redirect()->route('talento.pago.show');
            }


            // Punto 4: CreaciÃ³n del Ã­tem (MANTENIENDO TU ESTRUCTURA ORIGINAL)  descuento
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

            // MANTENIENDO TU LOG DE CREACIÃ“N
            Log::debug('Intentando crear item con datos:', $itemData);

            $item = Item::create($itemData);
            Log::info('Item creado exitosamente', ['id_item' => $item->id_item]);

            // Punto 5: Procesar imagen/video principal
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
                    throw $e; // Relanzamos la excepciÃ³n para que caiga en el catch general
                }
            }

            // Punto 6: Procesar imÃ¡genes adicionales (MANTENIENDO TUS LOGS)
            if ($request->hasFile('imagenes')) {
                Log::debug('Procesando imÃ¡genes adicionales...');
                $orden = 2;

                foreach ($request->file('imagenes') as $index => $file) {
                    if ($file->isValid()) {
                        try {
                            $this->guardarImagenTalento($file, $item->id_item, $orden);
                            Log::info("Imagen adicional {$index} guardada", ['orden' => $orden]);
                            $orden++;
                        } catch (\Exception $e) {
                            Log::error("Error al guardar imagen adicional {$index}", ['error' => $e->getMessage()]);
                            // Continuamos con las siguientes imÃ¡genes aunque falle una
                        }
                    }
                }
            }

            DB::commit();
            Log::info('TransacciÃ³n completada exitosamente');

            // Invalidar cache del home para reflejar el nuevo item
            \Illuminate\Support\Facades\Cache::forget('home_intercambio');
            \Illuminate\Support\Facades\Cache::forget('home_venta');

            return redirect()->route('items.admintalento')->with('success', 'Talento creado exitosamente!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            // MANTENIENDO TU LOG DE ERROR DE VALIDACIÃ“N
            Log::error('Error de validaciÃ³n', ['errors' => $e->errors()]);
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


    protected function guardarImagenTalento($file, $itemId, $orden)
    {
        // VerificaciÃ³n del archivo
        if (!$file || !$file->isValid()) {
            \Log::error('Archivo invÃ¡lido', ['error' => $file->getErrorMessage()]);
            throw new \Exception('El archivo no es vÃ¡lido: ' . $file->getErrorMessage());
        }

        // ValidaciÃ³n del path fÃ­sico
        $pathname = $file->getPathname();
        if (empty($pathname) || !file_exists($pathname)) {
            \Log::error('Path fÃ­sico invÃ¡lido', ['path' => $pathname]);
            throw new \Exception('El archivo no tiene un path fÃ­sico vÃ¡lido');
        }

        // ValidaciÃ³n del tipo de archivo
        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/jpg',
            'image/webp',
            'video/mp4',
            'video/quicktime',
            'video/x-m4v'
        ];

        $mime = $file->getMimeType();
        $isVideo = str_starts_with($mime, 'video/');

        if (!in_array($mime, $allowedMimeTypes)) {
            \Log::error('Tipo de archivo no permitido', ['mime' => $mime]);
            throw new \Exception('Tipo de archivo no permitido: ' . $mime);
        }

        // ConfiguraciÃ³n de rutas segÃºn tipo
        $directory = $isVideo ? 'videos/articulos/items' : 'imgs/articulos/items';
        $prefix = $isVideo ? 'video_' : 'item_';

        // GeneraciÃ³n mejorada del nombre de archivo
        $fileName = $prefix . $itemId . '_' . now()->format('YmdHis') . '_' . Str::random(10) . '.' . $file->extension();

        try {
            // Guardar el archivo en el disco 'public'
            $path = Storage::disk('public')->putFileAs(
                rtrim($directory, '/'), // Aseguramos que no termine con /
                $file,
                $fileName
            );

            if (empty($path)) {
                throw new \Exception('No se pudo guardar el archivo en el almacenamiento');
            }

            // ValidaciÃ³n adicional del nombre del archivo
            if (empty($fileName) || strpos($fileName, '/') !== false) {
                \Log::error('Nombre de archivo invÃ¡lido', ['fileName' => $fileName]);
                throw new \Exception('Nombre de archivo invÃ¡lido generado');
            }

            // Guardar metadatos en la base de datos
            $dataToInsert = [
                'nombre' => $fileName,
                'extension' => $file->extension(),
                'id_item' => $itemId,
                'orden_visualizacion' => $orden,
                'ruta' => $directory,
                'tipo' => $isVideo ? 'video' : 'imagen'
            ];

            DB::table('imagenes_item')->insert($dataToInsert);

            return [
                'path' => $path,
                'url' => asset('storage/' . $directory . $fileName),
                'is_video' => $isVideo
            ];

        } catch (\Exception $e) {
            \Log::error('Error al guardar archivo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_type' => $isVideo ? 'video' : 'imagen'
            ]);

            // Eliminar archivo si se creÃ³ pero fallÃ³ la BD
            if (!empty($path)) {
                Storage::disk('public')->delete($path);
            }

            throw new \Exception('Error al guardar archivo: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        // Punto 1: Verificar recepciÃ³n de datos (MANTENIENDO TUS LOGS)
        Log::info('Inicio de store() - Datos recibidos:', [
            'form_data' => $request->except(['imagen_principal', 'imagenes']),
            'has_imagen_principal' => $request->hasFile('imagen_principal'),
            'num_imagenes' => $request->hasFile('imagenes') ? count($request->file('imagenes')) : 0
        ]);

        DB::beginTransaction();

        try {
            // Punto 2: ValidaciÃ³n de datos con mensajes personalizados descuento
            $rules = [
                'item' => 'required|string|max:255',
                'id_categoria_item' => 'required|exists:categorias_item,id_categoria_item',
                'valor' => 'required|numeric|min:0',
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
                'id_categoria_item.required' => 'Debe seleccionar una categorÃ­a',
                'valor.required' => 'El precio es obligatorio',
                'valor.numeric' => 'El precio debe ser un nÃºmero vÃ¡lido',
                'valor.min' => 'El precio no puede ser negativo',
                'condicion.required' => 'Debe especificar la condiciÃ³n del producto',
                'condicion.in' => 'La condiciÃ³n seleccionada no es vÃ¡lida',
                'tipo_trans.required' => 'Debe seleccionar un tipo de transacciÃ³n',
                'tipo_trans.in' => 'El tipo de transacciÃ³n seleccionado no es vÃ¡lido',
                'imagen_principal.required' => 'La imagen/video principal es obligatorio',
                'imagen_principal.file' => 'El archivo debe ser una imagen o video vÃ¡lido',
                'imagen_principal.mimes' => 'Solo se permiten imÃ¡genes (JPEG, PNG, JPG, GIF, WEBP) o videos (MP4, MOV)',
                'imagen_principal.max' => 'El archivo no debe pesar mÃ¡s de 10MB',
                'imagenes.*.image' => 'Los archivos adicionales deben ser imÃ¡genes vÃ¡lidas',
                'imagenes.*.mimes' => 'Solo se permiten imÃ¡genes JPEG, PNG, JPG, GIF o WEBP',
                'imagenes.*.max' => 'Las imÃ¡genes no deben pesar mÃ¡s de 2MB',
                'peso_lbs.numeric' => 'El peso debe ser un nÃºmero vÃ¡lido',
                'alto_cm.numeric' => 'La altura debe ser un nÃºmero vÃ¡lido',
                'ancho_cm.numeric' => 'El ancho debe ser un nÃºmero vÃ¡lido',
                'profundo_cm.numeric' => 'La profundidad debe ser un nÃºmero vÃ¡lido',
                'presentacion.required' => 'Rellene la descripciÃ³n de su producto o servicio, que se encuentra en la secciÃ³n de Especificar Dimensiones.',
                'cantidad.numeric' => 'La cantidad debe ser un nÃºmero vÃ¡lido',
            ];

            $validatedData = $request->validate($rules, $messages);

            // MANTENIENDO TU LOG DE VALIDACIÃ“N
            Log::debug('Datos validados correctamente', $validatedData);

            // Punto 4: CreaciÃ³n del Ã­tem (MANTENIENDO TU ESTRUCTURA ORIGINAL)
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

            // MANTENIENDO TU LOG DE CREACIÃ“N
            Log::debug('Intentando crear item con datos:', $itemData);


            $item = Item::create($itemData);
            Log::info('Item creado exitosamente', ['id_item' => $item->id_item]);

            // Crear registro en el inventario
            Inventario::create([
                'id_item' => $item->id_item,
                'cantidad' => $validatedData['cantidad'],
                'fecha' => now() // Usa la misma fecha que el Ã­tem
            ]);


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

            // Punto 5: Procesar imagen/video principal
            if ($request->hasFile('imagen_principal')) {
                Log::debug('Procesando imagen/video principal...');
                try {
                    $resultado = $this->guardarImagen($request->file('imagen_principal'), $item->id_item, 1);

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
                    throw $e; // Relanzamos la excepciÃ³n para que caiga en el catch general
                }
            }

            // Punto 6: Procesar imÃ¡genes adicionales (MANTENIENDO TUS LOGS)
            if ($request->hasFile('imagenes')) {
                Log::debug('Procesando imÃ¡genes adicionales...');
                $orden = 2;

                foreach ($request->file('imagenes') as $index => $file) {
                    if ($file->isValid()) {
                        try {
                            $this->guardarImagen($file, $item->id_item, $orden);
                            Log::info("Imagen adicional {$index} guardada", ['orden' => $orden]);
                            $orden++;
                        } catch (\Exception $e) {
                            Log::error("Error al guardar imagen adicional {$index}", ['error' => $e->getMessage()]);
                            // Continuamos con las siguientes imÃ¡genes aunque falle una
                        }
                    }
                }
            }

            DB::commit();
            Log::info('TransacciÃ³n completada exitosamente');

            // Invalidar cache del home para reflejar el nuevo item
            \Illuminate\Support\Facades\Cache::forget('home_intercambio');
            \Illuminate\Support\Facades\Cache::forget('home_venta');

            return redirect()->route('items.user')->with('success', 'Talento creado exitosamente!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            // MANTENIENDO TU LOG DE ERROR DE VALIDACIÃ“N
            Log::error('Error de validaciÃ³n', ['errors' => $e->errors()]);
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

    protected function guardarImagen($file, $itemId, $orden)
    {
        // VerificaciÃ³n del archivo
        if (!$file || !$file->isValid()) {
            \Log::error('Archivo invÃ¡lido', ['error' => $file->getErrorMessage()]);
            throw new \Exception('El archivo no es vÃ¡lido: ' . $file->getErrorMessage());
        }

        // ValidaciÃ³n del path fÃ­sico
        $pathname = $file->getPathname();
        if (empty($pathname) || !file_exists($pathname)) {
            \Log::error('Path fÃ­sico invÃ¡lido', ['path' => $pathname]);
            throw new \Exception('El archivo no tiene un path fÃ­sico vÃ¡lido');
        }

        // ValidaciÃ³n del tipo de archivo
        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/jpg',
            'image/webp',
            'video/mp4',
            'video/quicktime',
            'video/x-m4v'
        ];

        $mime = $file->getMimeType();
        $isVideo = str_starts_with($mime, 'video/');

        if (!in_array($mime, $allowedMimeTypes)) {
            \Log::error('Tipo de archivo no permitido', ['mime' => $mime]);
            throw new \Exception('Tipo de archivo no permitido: ' . $mime);
        }

        // ConfiguraciÃ³n de rutas segÃºn tipo
        $directory = $isVideo ? 'videos/articulos/items' : 'imgs/articulos/items';
        $prefix = $isVideo ? 'video_' : 'item_';

        // GeneraciÃ³n mejorada del nombre de archivo
        $fileName = $prefix . $itemId . '_' . now()->format('YmdHis') . '_' . Str::random(10) . '.' . $file->extension();

        try {
            // Guardar el archivo en el disco 'public'
            $path = Storage::disk('public')->putFileAs(
                rtrim($directory, '/'), // Aseguramos que no termine con /
                $file,
                $fileName
            );

            if (empty($path)) {
                throw new \Exception('No se pudo guardar el archivo en el almacenamiento');
            }

            // ValidaciÃ³n adicional del nombre del archivo
            if (empty($fileName) || strpos($fileName, '/') !== false) {
                \Log::error('Nombre de archivo invÃ¡lido', ['fileName' => $fileName]);
                throw new \Exception('Nombre de archivo invÃ¡lido generado');
            }

            // Guardar metadatos en la base de datos
            $dataToInsert = [
                'nombre' => $fileName,
                'extension' => $file->extension(),
                'id_item' => $itemId,
                'orden_visualizacion' => $orden,
                'ruta' => $directory,
                'tipo' => $isVideo ? 'video' : 'imagen'
            ];

            DB::table('imagenes_item')->insert($dataToInsert);

            return [
                'path' => $path,
                'url' => asset('storage/' . $directory . $fileName),
                'is_video' => $isVideo
            ];

        } catch (\Exception $e) {
            \Log::error('Error al guardar archivo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_type' => $isVideo ? 'video' : 'imagen'
            ]);

            // Eliminar archivo si se creÃ³ pero fallÃ³ la BD
            if (!empty($path)) {
                Storage::disk('public')->delete($path);
            }

            throw new \Exception('Error al guardar archivo: ' . $e->getMessage());
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
        // Forzar parÃ¡metro de categorÃ­a 29
        request()->merge(['category_id' => 29]);

        // Ejecutar el mÃ©todo index normal
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

            // Filtro por categorÃ­a (opcional)
            if ($categoryId = request()->query('category_id')) {
                $query->where('id_categoria_item', $categoryId);
            }

            app()->setLocale('es');

            // Filtro por tipo de item
            if ($type = request()->query('type')) {
                $query->where('id_tipo_item', $type);
            }

            // OrdenaciÃ³n
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

            // RedirecciÃ³n si la pÃ¡gina estÃ¡ vacÃ­a
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

            // Registrar vista si no es el dueÃ±o
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
            if ((int) $item->id_categoria_item === 29 && (int) $item->tipo_trans === 1) {
                $configTarifa29 = \App\Models\ConfigTarifaCategoria29::vigente();
                if ((float) $configTarifa29->descuento_venta_masiva <= 0) {
                    $configTarifa29 = null;
                }
            }

            return view('productos.producto-detalle', [
                'item'           => $item,
                'relatedItems'   => $relatedItems,
                'configTarifa29' => $configTarifa29,
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

            // Eliminar imÃ¡genes asociadas
            foreach ($item->imagenes as $imagen) {
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

            // Registrar la visualizaciÃ³n si no es el dueÃ±o
            if (auth()->id() != $item->id_user) {
                Log::debug('Registrando visualizaciÃ³n del item', [
                    'user_id' => auth()->id(),
                    'item_user_id' => $item->id_user,
                    'ip' => request()->ip()
                ]);

                ItemView::create([
                    'id_item' => $item->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                Log::debug('VisualizaciÃ³n registrada');
            }

            // Obtener productos relacionados (misma categorÃ­a)
            $relatedItems = Item::where('id_categoria_item', $item->id_categoria_item)
                ->where('id_item', '!=', $item->id_item)
                ->with(['categoria'])
                ->inRandomOrder()
                ->limit(4)
                ->get();

            Log::debug('Productos relacionados obtenidos', [
                'cantidad' => $relatedItems->count()
            ]);

            // Texto para condiciÃ³n
            $item->condicion_text = match ($item->condicion) {
                1 => 'Nuevo',
                2 => 'Usado - Como nuevo',
                3 => 'Usado - Buen estado',
                4 => 'Usado - Aceptable',
                default => 'No especificado'
            };

            Log::debug('CondiciÃ³n del producto definida', [
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
            $categoria = CategoriaItem::findOrFail($id);
            $items = Item::where('id_categoria_item', $id);

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
            $items = $items->with(['categoria', 'direcciones', 'imagenes'])->paginate(15);

            return view('categorias.por-categoria', compact('categoria', 'items'));
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'show',
                'id_categoria' => $id,
                'request_params' => request()->all()
            ]);

            abort(404, 'CategorÃ­a no encontrada');
        }
    }

    /**
     * Devuelve datos bÃ¡sicos de un item para mostrar en modales (JSON).
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
                'error' => 'Error en la bÃºsqueda',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


    public function porCategoria($id)
    {
        try {
            $categoria = CategoriaItem::findOrFail($id);
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

            abort(404, 'CategorÃ­a no encontrada');
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
                'error' => 'Error al obtener items por categorÃ­a',
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
                'error' => 'Error al obtener items pÃºblicos',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


    public function talentoAdd()
    {
         
        try {
            \Log::info('Iniciando mÃ©todo talentoAdd1');
            $categorias = CategoriaItem::all();
         
            \Log::debug('CategorÃ­as obtenidas:', $categorias->toArray());

            if ($categorias->isEmpty()) {
                \Log::info('No hay categorÃ­as, creando una por defecto');
                CategoriaItem::create(['categoria' => 'General']);
                $categorias = CategoriaItem::all();
                \Log::debug('CategorÃ­as despuÃ©s de creaciÃ³n:', $categorias->toArray());
            }

            // VerificaciÃ³n final antes de enviar a la vista
            if ($categorias->isEmpty()) {
                \Log::error('No se pudieron obtener categorÃ­as');
                throw new \Exception('No hay categorÃ­as disponibles');
            }

            return view('talentos.agregar-talentos', compact('categorias'));

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
            \Log::info('Iniciando mÃ©todo create');
            $categorias = CategoriaItem::all();
            $colors = Color::all(); // Obtener todos los colores
            $groupedColors = $this->groupColorsByFamily($colors);

            \Log::debug('CategorÃ­as obtenidas:', $categorias->toArray());

            if ($categorias->isEmpty()) {
                \Log::info('No hay categorÃ­as, creando una por defecto');
                CategoriaItem::create(['categoria' => 'General']);
                $categorias = CategoriaItem::all();
                \Log::debug('CategorÃ­as despuÃ©s de creaciÃ³n:', $categorias->toArray());
            }

            // VerificaciÃ³n final antes de enviar a la vista
            if ($categorias->isEmpty()) {
                \Log::error('No se pudieron obtener categorÃ­as');
                throw new \Exception('No hay categorÃ­as disponibles');
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
        try {
            $items = Item::whereIn('tipo_trans', [2, 3])
                ->where('estatus', 1)
                ->with(['categoria', 'direccionPredeterminada.provincia', 'imagenes'])
                ->orderBy('fecha', 'desc')
                ->paginate(12);

            return view('blank-intercambiar.intercambio', compact('items'));
        } catch (Throwable $e) {
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
                ->with(['categoria', 'direccionPredeterminada.provincia', 'imagenes'])
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
            $query = Item::where('estatus', 1)
                ->whereIn('tipo_trans', [1, 2, 3]);

            if ($request->has('q') && !empty($request->q)) {
                $searchTerm = str_replace(['%', '_'], ['\\%', '\\_'], $request->q);
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('item', 'like', '%' . $searchTerm . '%')
                        ->orWhere('presentacion', 'like', '%' . $searchTerm . '%')
                        ->orWhereHas('categoria', function ($catQuery) use ($searchTerm) {
                            $catQuery->where('categoria', 'like', '%' . $searchTerm . '%');
                        });
                });
            }

            $items = $query->orderByDesc('fecha')
                ->with(['categoria', 'direcciones', 'imagenes'])
                ->paginate(12)
                ->appends($request->query());

            return view('compras.compra', compact('items'));
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'search_header',
                'request_params' => $request->all()
            ]);

            abort(500, 'Error en la bÃºsqueda');
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
                ->with(['categoria', 'imagenes'])
                ->withCount('views')
                ->orderBy('created_at', 'desc')
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
                ->where('id_categoria_item', 29) // â† Filtrar por categorÃ­a 29
                ->with(['categoria', 'imagenes'])
                ->withCount('views')
                ->orderBy('created_at', 'desc')
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
        $item = Item::with('imagenes')->findOrFail($id);
        $categorias = CategoriaItem::all();

        // Obtener la cantidad del inventario
        $inventario = Inventario::where('id_item', $item->id_item)->first();
        $cantidad = $inventario ? $inventario->cantidad : 0;


        // Obtener todos los colores
        $colors = Color::all();
        // Agrupar colores por familia usando tu mÃ©todo
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
        $item = Item::with('imagenes')->findOrFail($id);
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

            $rules = [
                'item' => 'required|string|max:255',
                'id_categoria_item' => 'required|exists:categorias_item,id_categoria_item',
                'valor' => 'required|numeric|min:0',
                'descuento' => 'nullable|numeric|min:0',
                'presentacion' => 'required|string',
                'condicion' => 'required|integer|in:1,2,3,4',
                'tipo_trans' => 'required|integer|in:1,2,3',
                'imagen_principal' => 'nullable|file|mimes:mp4,mov,jpeg,png,jpg,gif,webp|max:20480', // 10MB para videos
                'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
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
                'id_categoria_item.required' => 'Debe seleccionar una categorÃ­a',
                'valor.required' => 'El precio es obligatorio',
                'valor.numeric' => 'El precio debe ser un nÃºmero vÃ¡lido',
                'valor.min' => 'El precio no puede ser negativo',
                'condicion.required' => 'Debe especificar la condiciÃ³n del producto',
                'condicion.in' => 'La condiciÃ³n seleccionada no es vÃ¡lida',
                'tipo_trans.required' => 'Debe seleccionar un tipo de transacciÃ³n',
                'tipo_trans.in' => 'El tipo de transacciÃ³n seleccionado no es vÃ¡lido',
                'imagen_principal.required' => 'La imagen/video principal es obligatorio',
                'imagen_principal.file' => 'El archivo debe ser una imagen o video vÃ¡lido',
                'imagen_principal.mimes' => 'Solo se permiten imÃ¡genes (JPEG, PNG, JPG, GIF, WEBP) o videos (MP4, MOV)',
                'imagen_principal.max' => 'El archivo no debe pesar mÃ¡s de 10MB',
                'imagenes.*.image' => 'Los archivos adicionales deben ser imÃ¡genes vÃ¡lidas',
                'imagenes.*.mimes' => 'Solo se permiten imÃ¡genes JPEG, PNG, JPG, GIF o WEBP',
                'imagenes.*.max' => 'Las imÃ¡genes no deben pesar mÃ¡s de 2MB',
                'peso_lbs.numeric' => 'El peso debe ser un nÃºmero vÃ¡lido',
                'alto_cm.numeric' => 'La altura debe ser un nÃºmero vÃ¡lido',
                'ancho_cm.numeric' => 'El ancho debe ser un nÃºmero vÃ¡lido',
                'profundo_cm.numeric' => 'La profundidad debe ser un nÃºmero vÃ¡lido',
                'presentacion.required' => 'Rellene la descripciÃ³n de su producto o servicio, que se encuentra en la secciÃ³n de Especificar Dimensiones.',
                'cantidad.required' => 'La cantidad es obligatorio',
                'cantidad.numeric' => 'La cantidad debe ser un nÃºmero vÃ¡lido',
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

            // Imagen principal
            if ($request->hasFile('imagen_principal')) {
                try {
                    $imagenAnterior = $item->imagenes()->where('orden_visualizacion', 1)->first();
                    if ($imagenAnterior) {
                        Storage::disk('public')->delete($imagenAnterior->ruta . '/' . $imagenAnterior->nombre);
                        $imagenAnterior->delete();
                    }

                    $this->guardarImagen($request->file('imagen_principal'), $item->id_item, 1);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    Log::error('Error al guardar imagen principal', ['error' => $e->getMessage()]);
                    return redirect()->back()->withErrors(['imagen_principal' => $e->getMessage()])->withInput();
                }
            }

            // ImÃ¡genes secundarias existentes
            $idsConservar = $request->input('imagenes_existentes', []);
            $imagenesActuales = $item->imagenes()->where('orden_visualizacion', '>', 1)->get();

            foreach ($imagenesActuales as $imagen) {
                if (!in_array($imagen->id_imagen, $idsConservar)) {
                    Storage::disk('public')->delete($imagen->ruta . '/' . $imagen->nombre);
                    $imagen->delete();
                }
            }

            // Nuevas imÃ¡genes
            if ($request->hasFile('imagenes')) {
                try {
                    $maxOrden = $item->imagenes()->max('orden_visualizacion') ?? 1;

                    foreach ($request->file('imagenes') as $imagen) {
                        $maxOrden++;
                        $this->guardarImagen($imagen, $item->id_item, $maxOrden);
                    }
                } catch (\Throwable $e) {
                    DB::rollBack();
                    Log::error('Error al guardar imÃ¡genes secundarias', ['error' => $e->getMessage()]);
                    return redirect()->back()->withErrors(['imagenes' => $e->getMessage()])->withInput();
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

            // Validaciones
            $rules = [
                'item' => 'required|string|max:255',
                'id_categoria_item' => 'required|exists:categorias_item,id_categoria_item',
                'valor' => 'required|numeric|min:0',
                'descuento' => 'nullable|numeric|min:0',
                'presentacion' => 'required|string',
                'condicion' => 'required|integer|in:1,2,3,4',
                'tipo_trans' => 'required|integer|in:1,2,3',
                'imagen_principal' => 'nullable|file|mimes:mp4,mov,jpeg,png,jpg,gif,webp|max:20480',
                'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'peso_lbs' => 'nullable|numeric|min:0',
                'alto_cm' => 'nullable|numeric|min:0',
                'ancho_cm' => 'nullable|numeric|min:0',
                'profundo_cm' => 'nullable|numeric|min:0',
                'id_tipo_item' => 'required|numeric',
                'estatus' => 'required|integer|in:1,2' // Nuevo campo
            ];

            $messages = [
                'item.required' => 'El nombre del producto es obligatorio',
                'id_categoria_item.required' => 'Debe seleccionar una categorÃ­a',
                'valor.required' => 'El precio es obligatorio',
                'valor.numeric' => 'El precio debe ser un nÃºmero vÃ¡lido',
                'valor.min' => 'El precio no puede ser negativo',
                'condicion.required' => 'Debe especificar la condiciÃ³n del producto',
                'condicion.in' => 'La condiciÃ³n seleccionada no es vÃ¡lida',
                'tipo_trans.required' => 'Debe seleccionar un tipo de transacciÃ³n',
                'tipo_trans.in' => 'El tipo de transacciÃ³n seleccionado no es vÃ¡lido',
                'imagen_principal.required' => 'La imagen/video principal es obligatorio',
                'imagen_principal.file' => 'El archivo debe ser una imagen o video vÃ¡lido',
                'imagen_principal.mimes' => 'Solo se permiten imÃ¡genes (JPEG, PNG, JPG, GIF, WEBP) o videos (MP4, MOV)',
                'imagen_principal.max' => 'El archivo no debe pesar mÃ¡s de 10MB',
                'imagenes.*.image' => 'Los archivos adicionales deben ser imÃ¡genes vÃ¡lidas',
                'imagenes.*.mimes' => 'Solo se permiten imÃ¡genes JPEG, PNG, JPG, GIF o WEBP',
                'imagenes.*.max' => 'Las imÃ¡genes no deben pesar mÃ¡s de 2MB',
                'peso_lbs.numeric' => 'El peso debe ser un nÃºmero vÃ¡lido',
                'alto_cm.numeric' => 'La altura debe ser un nÃºmero vÃ¡lido',
                'ancho_cm.numeric' => 'El ancho debe ser un nÃºmero vÃ¡lido',
                'profundo_cm.numeric' => 'La profundidad debe ser un nÃºmero vÃ¡lido',
                'presentacion.required' => 'Rellene la descripciÃ³n de su producto o servicio, que se encuentra en la secciÃ³n de Especificar Dimensiones.',
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

            // Eliminar imagen principal anterior si existe y guardar la nueva
            if ($request->hasFile('imagen_principal')) {
                try {
                    $imagenPrincipalAnterior = $item->imagenes()->where('orden_visualizacion', 1)->first();
                    if ($imagenPrincipalAnterior) {
                        Storage::disk('public')->delete($imagenPrincipalAnterior->ruta . '/' . $imagenPrincipalAnterior->nombre);
                        $imagenPrincipalAnterior->delete();
                    }

                    $resultado = $this->guardarImagenTalento($request->file('imagen_principal'), $item->id_item, 1);
                    Log::info('Imagen principal actualizada correctamente', $resultado);

                } catch (\Throwable $e) {
                    DB::rollBack();
                    Log::error('Error al guardar imagen principal con helper', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    return redirect()->back()
                        ->withErrors(['imagen_principal' => 'Error al guardar la imagen principal: ' . $e->getMessage()])
                        ->withInput();
                }
            }

            // Eliminar imÃ¡genes secundarias que no estÃ¡n marcadas como existentes
            $idsConservar = $request->input('imagenes_existentes', []);
            $imagenesActuales = $item->imagenes()->where('orden_visualizacion', '>', 1)->get();

            foreach ($imagenesActuales as $imagen) {
                if (!in_array($imagen->id_imagen, $idsConservar)) {
                    Storage::disk('public')->delete($imagen->ruta . '/' . $imagen->nombre);
                    Log::info('Imagen secundaria eliminada', [
                        'id_imagen' => $imagen->id_imagen,
                        'path' => $imagen->ruta . '/' . $imagen->nombre
                    ]);
                    $imagen->delete();
                }
            }



            // Subir nuevas imÃ¡genes adicionales
            if ($request->hasFile('imagenes')) {
                try {
                    $maxOrden = $item->imagenes()->max('orden_visualizacion') ?? 1;

                    foreach ($request->file('imagenes') as $imageFile) {
                        $maxOrden++;
                        $resultado = $this->guardarImagenTalento($imageFile, $item->id_item, $maxOrden);
                        Log::info('Imagen secundaria guardada correctamente', $resultado);
                    }

                } catch (\Throwable $e) {
                    DB::rollBack();
                    Log::error('Error al guardar imÃ¡genes secundarias con helper', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    return redirect()->back()
                        ->withErrors(['imagenes' => 'Error al guardar imÃ¡genes secundarias: ' . $e->getMessage()])
                        ->withInput();
                }
            }

            DB::commit();
            Log::info('Producto actualizado exitosamente', [
                'item_id' => $item->id_item,
                'total_imagenes' => $item->imagenes()->count()
            ]);

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
            'PÃºrpuras' => [],
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
                    $family = 'PÃºrpuras';
                } else { // 285â€“344
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
            'PÃºrpuras' => $families['PÃºrpuras'],
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
                Log::warning('Usuario no autenticado intentÃ³ acceder a items-usuario', [
                    'ip' => $request->ip(),
                    'route' => $request->path()
                ]);
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            // Traer los items de ese usuario â€” solo intercambiables (tipo_trans 2 o 3)
            $items = Item::where('id_user', $user->id)
                ->whereIn('tipo_trans', [2, 3])
                ->where('estatus', 1)
                ->get(['id_item', 'item', 'valor']);

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
