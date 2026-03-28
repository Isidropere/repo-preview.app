<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Negociacion;
use App\Models\PagoCompra;
use App\Events\NuevaNotificacion;
use App\Services\AdminComprasService;
use Illuminate\Http\Request;

/**
 * ============================================================
 * AdminComprasController — Panel de administración de compras
 * ============================================================
 *
 * Gestiona la vista y operaciones del panel admin:
 * - Panel principal con tabs (compras, ventas, intercambios)
 * - Detalle y cambio de estado de compras
 * - Detalle de ventas
 * - Detalle y cambio de estado de intercambios
 *
 * Toda la lógica de negocio está en AdminComprasService.
 *
 * Rutas: /admin/*
 * Middleware: auth, admin
 * ============================================================
 */
class AdminComprasController extends Controller
{
    public function __construct(
        private AdminComprasService $adminComprasService,
    ) {}

    public function index(Request $request)
    {
        $request->validate([
            'tab'     => 'nullable|string|in:compras,ventas,intercambios,intencion_compra,intencion_intercambio,envio',
            'estatus' => 'nullable|string|max:50',
            'buscar'  => 'nullable|string|max:100',
        ]);

        $data = $this->adminComprasService->obtenerDatosPanelPrincipal(
            $request->get('tab', 'compras'),
            $request->estatus,
            $request->buscar
        );

        return view('admin.index', $data);
    }

    public function indexCompras(Request $request)
    {
        $request->validate([
            'estatus' => 'nullable|string|in:' . implode(',', AdminComprasService::ESTADOS_COMPRA),
            'buscar'  => 'nullable|string|max:100',
        ]);

        $query = PagoCompra::with(['pagoItems', 'carrito.usuario'])
            ->orderByDesc('id_pago_compra');

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(fn($q) => $q
                ->where('id_pago_compra', 'like', "%$buscar%")
                ->orWhereHas('carrito.usuario', fn($q2) => $q2
                    ->where('nombres', 'like', "%$buscar%")
                    ->orWhere('email', 'like', "%$buscar%")));
        }

        $compras = $query->paginate(20)->withQueryString();
        $estados = AdminComprasService::ESTADOS_COMPRA;

        return view('admin.compras.index', compact('compras', 'estados'));
    }

    public function showCompra($id)
    {
        $compra = PagoCompra::with([
            'pagoItems.item.imagenes',
            'carrito.usuario',
            'trazabilidad.admin',
            'tarjeta',
            'proveedorPago',
            'direccion.provincia',
            'direccion.municipio',
        ])->findOrFail($id);

        $estados = AdminComprasService::ESTADOS_COMPRA;

        return view('admin.compras.show', compact('compra', 'estados'));
    }

    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estatus' => 'required|in:' . implode(',', AdminComprasService::ESTADOS_COMPRA),
            'nota'    => 'nullable|string|max:500',
        ]);

        $this->adminComprasService->actualizarEstadoCompra(
            $id, $request->estatus, $request->nota, auth()->id()
        );

        return redirect()->route('admin.compras.show', $id)
            ->with('success', 'Estado actualizado correctamente.');
    }

    public function enviarTracking(Request $request, $id)
    {
        $request->validate([
            'estatus'       => 'required|in:' . implode(',', AdminComprasService::ESTADOS_COMPRA),
            'tracking_code' => 'required|string|max:100',
        ]);

        $compra = PagoCompra::with(['carrito.usuario'])->findOrFail($id);

        // Construir URL de rastreo
        $baseUrl = rtrim(env('TRACKING_BASE_URL', 'https://tracking.transporteblanco.do/rastreo'), '/');
        $trackingUrl = $baseUrl . '/' . $request->tracking_code;

        // Actualizar estado + trazabilidad
        $this->adminComprasService->actualizarEstadoCompra(
            $compra->id_pago_compra,
            $request->estatus,
            'Tracking enviado: ' . $request->tracking_code,
            auth()->id()
        );

        // Guardar tracking en la orden
        $compra->update([
            'tracking_code' => $request->tracking_code,
            'tracking_url'  => $trackingUrl,
        ]);

        // Notificar al comprador vía evento (sin email, sin mensaje)
        $comprador = $compra->comprador;
        if ($comprador) {
            $texto = "Tu orden #{$compra->id_pago_compra} fue actualizada a \"" . ucfirst($request->estatus) . "\". Rastreo: {$trackingUrl}";
            event(new NuevaNotificacion($texto, $comprador->id));
        }

        return redirect()->route('admin.compras.show', $id)
            ->with('success', 'Tracking enviado correctamente.');
    }

    public function showVenta($id)
    {
        $venta = PagoCompra::with([
            'pagoItems.item.imagenes',
            'pagoItems.item.usuario',
            'pagoItems.item.categoria',
            'carrito.usuario',
            'trazabilidad.admin',
            'tarjeta',
            'proveedorPago',
        ])->findOrFail($id);

        $estados = AdminComprasService::ESTADOS_COMPRA;

        return view('admin.ventas.show', compact('venta', 'estados'));
    }

    public function showIntercambio($id)
    {
        $intercambio = Negociacion::with([
            'item.imagenes',
            'item.usuario',
            'usuario',
            'usuarioReceptor',
        ])->findOrFail($id);

        $estados = AdminComprasService::ESTADOS_INTERCAMBIO;

        return view('admin.intercambios.show', compact('intercambio', 'estados'));
    }

    public function actualizarEstadoIntercambio(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:' . implode(',', AdminComprasService::ESTADOS_INTERCAMBIO),
            'nota'   => 'nullable|string|max:500',
        ]);

        $this->adminComprasService->actualizarEstadoIntercambio($id, $request->estado);

        return redirect()->route('admin.intercambios.show', $id)
            ->with('success', 'Estado del intercambio actualizado.');
    }
}
