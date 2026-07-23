<?php

namespace App\Http\Controllers;

use App\Models\SolicitudServicio;
use App\Services\SolicitudServicioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SolicitudServicioController extends Controller
{
    public function __construct(
        private SolicitudServicioService $service,
    ) {}

    /**
     * Vista /mis-ventas-talentos — Panel del proveedor
     */
    public function index(): View
    {
        $userId = auth()->id();

        $solicitudes = SolicitudServicio::where('id_proveedor', $userId)
            ->with([
                'comprador.direcciones' => fn($q) => $q->where('es_predeterminada', 1)->with(['municipio', 'provincia']),
                'item.imagenes',
            ])
            ->orderByDesc('fecha_creacion')
            ->get();

        $pendientes = $solicitudes->where('estado', 'pendiente_aprobacion');
        $procesadas = $solicitudes->whereIn('estado', ['aprobada', 'rechazada', 'pagada']);

        return view('solicitudes.mis-ventas-talentos', compact('pendientes', 'procesadas'));
    }

    /**
     * GET /solicitudes-servicio/mis-ventas-talentos (JSON) — Panel del proveedor
     */
    public function misVentasTalentosJson(): \Illuminate\Http\JsonResponse
    {
        $userId = auth()->id();

        $solicitudes = SolicitudServicio::where('id_proveedor', $userId)
            ->with([
                'comprador.direcciones' => fn($q) => $q->where('es_predeterminada', 1)->with(['municipio', 'provincia']),
                'item.imagenes',
            ])
            ->orderByDesc('fecha_creacion')
            ->get();

        $pendientes = $solicitudes->where('estado', 'pendiente_aprobacion')->values();
        $procesadas = $solicitudes->whereIn('estado', ['aprobada', 'rechazada', 'pagada'])->values();

        return response()->json([
            'pendientes' => $pendientes,
            'procesadas' => $procesadas,
        ]);
    }

    /**
     * POST /solicitudes-servicio/{id}/aprobar
     */
    public function aprobar(int $id): RedirectResponse
    {
        $resultado = $this->service->aprobar(auth()->id(), $id);

        if (($resultado['code'] ?? null) === 403) {
            abort(403);
        }

        return redirect()->route('solicitudes.index')
            ->with($resultado['success'] ? 'success' : 'error', $resultado['message']);
    }

    /**
     * POST /solicitudes-servicio/{id}/rechazar
     */
    public function rechazar(int $id): RedirectResponse
    {
        $resultado = $this->service->rechazar(auth()->id(), $id);

        if (($resultado['code'] ?? null) === 403) {
            abort(403);
        }

        return redirect()->route('solicitudes.index')
            ->with($resultado['success'] ? 'success' : 'error', $resultado['message']);
    }

    /**
     * POST /solicitudes-servicio/enviar — Envía solicitud desde el carrito (JSON)
     */
    public function enviarDesdeCarrito(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id_item' => 'required|integer|exists:items,id_item',
            'fecha_servicio' => 'required|date|after_or_equal:today',
        ]);

        $userId  = auth()->id();
        $idItem  = $request->id_item;
        $fecha   = $request->fecha_servicio;

        $carrito = \App\Models\Carrito::where('id_user', $userId)->where('tipo', 'servicio')->first();
        if (!$carrito) {
            return response()->json(['success' => false, 'message' => 'No tienes carrito de servicios.'], 422);
        }

        // Actualizar fecha en el item del carrito antes de crear la solicitud
        \App\Models\ItemIntencionCompra::where('id_carrito', $carrito->id_carrito)
            ->where('id_item', $idItem)
            ->update(['fecha_servicio' => $fecha, 'es_seleccionado' => true]);

        $resultado = $this->service->crearDesdeCarrito($userId, $carrito);

        // Devolver estado actualizado del item
        $solicitud = \App\Models\SolicitudServicio::where('id_comprador', $userId)
            ->where('id_item', $idItem)
            ->latest('fecha_creacion')
            ->first();

        return response()->json([
            'success'  => $resultado['success'],
            'message'  => $resultado['message'],
            'estado'   => $solicitud?->estado ?? null,
            'id_solicitud' => $solicitud?->id_solicitud ?? null,
        ]);
    }

    /**
     * GET /solicitudes-servicio/estado/{idItem} — Estado de solicitud del comprador para un item (JSON)
     */
    public function estadoItem(int $idItem): \Illuminate\Http\JsonResponse
    {
        $userId = auth()->id();

        $solicitud = \App\Models\SolicitudServicio::where('id_comprador', $userId)
            ->where('id_item', $idItem)
            ->latest('fecha_creacion')
            ->first();

        return response()->json([
            'estado'       => $solicitud?->estado ?? null,
            'id_solicitud' => $solicitud?->id_solicitud ?? null,
        ]);
    }

    /**
     * POST /solicitudes-servicio/{id}/aprobar-json — Aprobar desde mis-ventas-talentos (JSON)
     */
    public function aprobarJson(int $id): \Illuminate\Http\JsonResponse
    {
        $resultado = $this->service->aprobar(auth()->id(), $id);

        if (($resultado['code'] ?? null) === 403) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        return response()->json($resultado);
    }

    /**
     * POST /solicitudes-servicio/{id}/rechazar-json — Rechazar desde mis-ventas-talentos (JSON)
     */
    public function rechazarJson(int $id): \Illuminate\Http\JsonResponse
    {
        $resultado = $this->service->rechazar(auth()->id(), $id);

        if (($resultado['code'] ?? null) === 403) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        return response()->json($resultado);
    }
}
