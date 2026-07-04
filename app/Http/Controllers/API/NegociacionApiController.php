<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Negociacion;
use App\Services\NegociacionService;
use Illuminate\Http\Request;

class NegociacionApiController extends Controller
{
    public function __construct(
        private NegociacionService $negociacionService
    ) {}

    private function formatItem($item)
    {
        if (!$item) return null;
        $arr = is_array($item) ? $item : $item->toArray();
        $imagenes = $item['imagenes'] ?? [];
        if (count($imagenes) > 0) {
            $first = $imagenes[0];
            $nombre = $first['nombre'] ?? '';
            $ruta = $first['ruta'] ?? 'imgs/articulos/items';
            $arr['image_url'] = file_exists(public_path("{$ruta}/{$nombre}"))
                ? url("{$ruta}/{$nombre}")
                : url("storage/{$ruta}/{$nombre}");
        } else {
            $arr['image_url'] = url('storage/imgs/defaults/producto_default.svg');
        }
        return $arr;
    }

    /** GET /api/negociaciones — lista negociaciones del usuario */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $comoEmisor = Negociacion::where('usuario_emisor_id', $userId)
            ->whereNotIn('estado', ['cancelado'])
            ->with([
                'item.imagenes:id_imagen,id_item,nombre,ruta',
                'item.categoria:id_categoria_item,categoria',
                'usuarioReceptor:id,nombres,apellidos',
            ])
            ->orderByDesc('id_negociacion')
            ->get();

        $comoReceptor = Negociacion::where('usuario_receptor_id', $userId)
            ->whereNotIn('estado', ['cancelado'])
            ->with([
                'item.imagenes:id_imagen,id_item,nombre,ruta',
                'item.categoria:id_categoria_item,categoria',
                'usuario:id,nombres,apellidos',
            ])
            ->orderByDesc('id_negociacion')
            ->get();

        // Cargar todos los detalles de items ofrecidos
        $itemIds = [];
        foreach ($comoEmisor as $neg) {
            $itemIds = array_merge($itemIds, $neg->items_ofrecidos ?? []);
        }
        foreach ($comoReceptor as $neg) {
            $itemIds = array_merge($itemIds, $neg->items_ofrecidos ?? []);
        }
        $itemIds = array_unique($itemIds);

        $itemsMap = [];
        if (!empty($itemIds)) {
            $itemsMap = \App\Models\Item::with(['imagenes:id_imagen,id_item,nombre,ruta'])
                ->whereIn('id_item', $itemIds)
                ->get()
                ->keyBy('id_item');
        }

        $comoEmisor = $comoEmisor->map(function ($neg) use ($itemsMap) {
            $neg->es_servicio_servicio = $this->negociacionService->esServicioServicio($neg);
            $neg->es_producto_servicio = $this->negociacionService->esProductoServicio($neg);
            $neg->es_producto_producto = $this->negociacionService->esProductoProducto($neg);

            if ($neg->item) {
                $neg->item = $this->formatItem($neg->item);
            }

            $detalles = [];
            foreach (($neg->items_ofrecidos ?? []) as $id) {
                if (isset($itemsMap[$id])) {
                    $detalles[] = $this->formatItem($itemsMap[$id]);
                }
            }
            $neg->items_ofrecidos_detalles = $detalles;
            return $neg;
        });

        $comoReceptor = $comoReceptor->map(function ($neg) use ($itemsMap) {
            $neg->es_servicio_servicio = $this->negociacionService->esServicioServicio($neg);
            $neg->es_producto_servicio = $this->negociacionService->esProductoServicio($neg);
            $neg->es_producto_producto = $this->negociacionService->esProductoProducto($neg);

            if ($neg->item) {
                $neg->item = $this->formatItem($neg->item);
            }

            $detalles = [];
            foreach (($neg->items_ofrecidos ?? []) as $id) {
                if (isset($itemsMap[$id])) {
                    $detalles[] = $this->formatItem($itemsMap[$id]);
                }
            }
            $neg->items_ofrecidos_detalles = $detalles;
            return $neg;
        });

        return response()->json([
            'como_emisor'   => $comoEmisor,
            'como_receptor' => $comoReceptor,
        ]);
    }

    /** GET /api/negociaciones/{id} — detalle */
    public function show(Request $request, $id)
    {
        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) { return response()->json(['message' => 'Negociación no encontrada.'], 404); }

        $userId = $request->user()->id;

        $negociacion = Negociacion::with([
            'item.imagenes:id_imagen,id_item,nombre,ruta',
            'usuario:id,nombres,apellidos,profile_photo_path',
            'usuarioReceptor:id,nombres,apellidos,profile_photo_path',
        ])
            ->where('id_negociacion', $realId)
            ->where(fn($q) => $q->where('usuario_emisor_id', $userId)
                ->orWhere('usuario_receptor_id', $userId))
            ->firstOrFail();

        $negociacion->es_servicio_servicio = $this->negociacionService->esServicioServicio($negociacion);
        $negociacion->es_producto_servicio = $this->negociacionService->esProductoServicio($negociacion);
        $negociacion->es_producto_producto = $this->negociacionService->esProductoProducto($negociacion);

        if ($negociacion->item) {
            $negociacion->item = $this->formatItem($negociacion->item);
        }

        $offeredIds = $negociacion->items_ofrecidos ?? [];
        $detalles = [];
        if (!empty($offeredIds)) {
            $items = \App\Models\Item::with(['imagenes:id_imagen,id_item,nombre,ruta'])
                ->whereIn('id_item', $offeredIds)
                ->get();
            foreach ($items as $it) {
                $detalles[] = $this->formatItem($it);
            }
        }
        $negociacion->items_ofrecidos_detalles = $detalles;

        return response()->json($negociacion);
    }

    /** POST /api/negociaciones — proponer intercambio */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id'         => 'required|exists:items,id_item',
            'id_color'        => 'nullable|integer|exists:colors,id_color',
            'mensaje'         => 'required|string|max:500',
            'paquete_id'      => 'nullable|integer|exists:paquetes,id_paquete',
            'monto_oferta'    => 'nullable|numeric|min:0',
            'items_ofrecidos' => 'nullable|array',
            'items_ofrecidos.*' => 'integer|exists:items,id_item',
            'cantidades_ofrecidas' => 'nullable|array',
        ]);

        $resultado = $this->negociacionService->crear($request->user()->id, $validated);

        if (!$resultado['success']) {
            return response()->json(['message' => $resultado['message']], 422);
        }

        return response()->json(['message' => $resultado['message']], 201);
    }

    /** POST /api/negociaciones/{id}/aceptar */
    public function aceptar(Request $request, $id)
    {
        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) { return response()->json(['message' => 'Negociación no encontrada.'], 404); }
        $resultado = $this->negociacionService->aceptar($request->user()->id, $realId);
        return response()->json(['success' => $resultado['success'], 'message' => $resultado['message']], $resultado['success'] ? 200 : 422);
    }

    /** POST /api/negociaciones/{id}/rechazar */
    public function rechazar(Request $request, $id)
    {
        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) { return response()->json(['message' => 'Negociación no encontrada.'], 404); }
        $resultado = $this->negociacionService->rechazar($request->user()->id, $realId);
        return response()->json(['success' => $resultado['success'], 'message' => $resultado['message']], $resultado['success'] ? 200 : 422);
    }

    /** POST /api/negociaciones/{id}/contraoferta */
    public function contraoferta(Request $request, $id)
    {
        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) { return response()->json(['message' => 'Negociación no encontrada.'], 404); }
        $validated = $request->validate([
            'monto_contra_oferta' => 'nullable|numeric|min:0',
            'mensaje'             => 'nullable|string|max:500',
        ]);

        $resultado = $this->negociacionService->contraoferta($request->user()->id, $realId, $validated);
        return response()->json(['success' => $resultado['success'], 'message' => $resultado['message']], $resultado['success'] ? 200 : 422);
    }

    /** POST /api/negociaciones/{id}/cancelar */
    public function cancelar(Request $request, $id)
    {
        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) { return response()->json(['message' => 'Negociación no encontrada.'], 404); }
        $resultado = $this->negociacionService->cancelar($request->user()->id, $realId);
        return response()->json(['success' => $resultado['success'], 'message' => $resultado['message']], $resultado['success'] ? 200 : 422);
    }

    /** POST /api/negociaciones/{id}/confirmar-emisor */
    public function confirmarEmisor(Request $request, $id)
    {
        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) { return response()->json(['message' => 'Negociación no encontrada.'], 404); }
        $resultado = $this->negociacionService->confirmarEmisor($request->user()->id, $realId);
        return response()->json(['success' => $resultado['success'], 'message' => $resultado['message']], $resultado['success'] ? 200 : 422);
    }

    /** POST /api/negociaciones/{id}/confirmar-receptor */
    public function confirmarReceptor(Request $request, $id)
    {
        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) { return response()->json(['message' => 'Negociación no encontrada.'], 404); }
        $resultado = $this->negociacionService->confirmarReceptor($request->user()->id, $realId);
        return response()->json(['success' => $resultado['success'], 'message' => $resultado['message']], $resultado['success'] ? 200 : 422);
    }

    /** POST /api/negociaciones/{id}/aceptar-como-emisor */
    public function aceptarComoEmisor(Request $request, $id)
    {
        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) { return response()->json(['message' => 'Negociación no encontrada.'], 404); }
        $resultado = $this->negociacionService->aceptarComoEmisor($request->user()->id, $realId);
        return response()->json(['success' => $resultado['success'], 'message' => $resultado['message']], $resultado['success'] ? 200 : 422);
    }

    /** POST /api/negociaciones/{id}/modo-entrega */
    public function seleccionarModoEntrega(Request $request, $id)
    {
        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) { return response()->json(['message' => 'Negociación no encontrada.'], 404); }
        $request->validate(['modo' => 'required|in:envio,retiro']);
        $resultado = $this->negociacionService->seleccionarModoEntrega($request->user()->id, $realId, $request->modo);
        
        if ($resultado['success'] && $request->modo === 'envio') {
            $userId = $request->user()->id;
            $redirectUrl = route('negociaciones.pago.iniciar-movil', ['id_negociacion' => $realId]) . '?user_id=' . $userId;
            
            return response()->json([
                'success'      => true,
                'message'      => 'Modo de entrega seleccionado. Redirigiendo al pago de envío...',
                'redirect_url' => $redirectUrl,
            ], 200);
        }

        return response()->json(['success' => $resultado['success'], 'message' => $resultado['message']], $resultado['success'] ? 200 : 422);
    }

    /** POST /api/negociaciones/{id}/confirmar-entrega */
    public function confirmarEntrega(Request $request, $id)
    {
        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) { return response()->json(['message' => 'Negociación no encontrada.'], 404); }
        $resultado = $this->negociacionService->confirmarEntrega($request->user()->id, $realId);
        return response()->json(['success' => $resultado['success'], 'message' => $resultado['message']], $resultado['success'] ? 200 : 422);
    }

    /** POST /api/negociaciones/{id}/completar */
    public function completar(Request $request, $id)
    {
        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) { return response()->json(['message' => 'Negociación no encontrada.'], 404); }
        $resultado = $this->negociacionService->completar($request->user()->id, $realId);
        return response()->json(['success' => $resultado['success'], 'message' => $resultado['message']], $resultado['success'] ? 200 : 422);
    }

    /** GET /api/negociaciones/{id}/mensajes */
    public function mensajes(Request $request, $id)
    {
        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) { return response()->json(['message' => 'Negociación no encontrada.'], 404); }
        $negociacion = Negociacion::findOrFail($realId);
        $userId = $request->user()->id;

        if ($userId != $negociacion->usuario_emisor_id && $userId != $negociacion->usuario_receptor_id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $data = $this->negociacionService->obtenerMensajes($userId, $negociacion->usuario_emisor_id, $negociacion->usuario_receptor_id);
        return response()->json($data);
    }

    /** POST /api/negociaciones/{id}/mensajes */
    public function enviarMensaje(Request $request, $id)
    {
        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) { return response()->json(['message' => 'Negociación no encontrada.'], 404); }
        $request->validate([
            'mensaje' => 'required|string|max:500',
        ]);

        $negociacion = Negociacion::findOrFail($realId);
        $userId = $request->user()->id;

        if ($userId != $negociacion->usuario_emisor_id && $userId != $negociacion->usuario_receptor_id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $receptorId = $userId == $negociacion->usuario_emisor_id
            ? $negociacion->usuario_receptor_id
            : $negociacion->usuario_emisor_id;

        $this->negociacionService->crearMensaje(
            $userId,
            $receptorId,
            $negociacion->receptor_item_id,
            $negociacion->emisor_paquete_id,
            $request->mensaje
        );

        return response()->json(['message' => 'Mensaje enviado correctamente.']);
    }
}
