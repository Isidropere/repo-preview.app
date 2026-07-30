<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Negociacion;
use App\Models\PagoCompra;
use App\Events\NuevaNotificacion;
use App\Services\AdminComprasService;
use Illuminate\Http\Request;
use App\Models\Provincia;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $user = auth()->user();
        if ($user->isContableUser() && !$user->isAdmin && !$user->isSuperAdmin) {
            return view('admin.index', [
                'tab'                       => 'erp',
                'totalCompras'              => 0,
                'totalVentas'               => 0,
                'totalIntercambios'         => 0,
                'totalIntencionCompra'      => 0,
                'totalIntencionIntercambio' => 0,
                'compras'                   => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'ventas'                    => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'intercambios'              => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'intencionCompra'           => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'intencionIntercambio'      => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'estadosCompra'             => [],
                'estadosIntercambio'        => [],
            ]);
        }

        $request->validate([
            'tab'         => 'nullable|string|in:compras,ventas,intercambios,intencion_compra,intencion_intercambio,envio,intercambios_confirmados',
            'estatus'     => 'nullable|string|max:50',
            'buscar'      => 'nullable|string|max:100',
            'fecha_desde' => 'nullable|date_format:Y-m-d',
            'fecha_hasta' => 'nullable|date_format:Y-m-d',
            'provincia'   => 'nullable|string',
            'municipio'   => 'nullable|string',
        ]);

        $data = $this->adminComprasService->obtenerDatosPanelPrincipal(
            $request->get('tab', 'compras'),
            $request->estatus,
            $request->buscar,
            $request->fecha_desde,
            $request->fecha_hasta,
            $request->provincia,
            $request->municipio
        );

        $data['provincias'] = Provincia::with('municipios')->orderBy('provincia')->get();

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

        $redirect = redirect()->back();
        if ($request->estatus === 'enviado') {
            $redirect->with('download_pdf_url', route('admin.compras.pdf', $id));
        }

        return $redirect->with('success', 'Estado actualizado correctamente.');
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
        if (is_numeric($id)) {
            $hashed = \App\Helpers\HashIdHelper::encode((int)$id);
            return redirect()->route('admin.intercambios.show', $hashed);
        }

        $realId = \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) {
            abort(404);
        }

        $intercambio = Negociacion::with([
            'item.imagenes',
            'item.usuario',
            'usuario.direcciones.provincia',
            'usuario.direcciones.municipio',
            'usuarioReceptor.direcciones.provincia',
            'usuarioReceptor.direcciones.municipio',
            'trazabilidad.admin',
            'pagoEnvios.tarjeta',
            'pagoEnvios.usuario',
        ])->findOrFail($realId);

        $itemsOfrecidos = collect();
        if (!empty($intercambio->items_ofrecidos)) {
            $itemsOfrecidos = \App\Models\Item::whereIn('id_item', $intercambio->items_ofrecidos)
                ->with('imagenes')
                ->get();
        }

        $estados = AdminComprasService::ESTADOS_INTERCAMBIO;

        return view('admin.intercambios.show', compact('intercambio', 'itemsOfrecidos', 'estados'));
    }

    public function enviarTrackingIntercambio(Request $request, $id)
    {
        $request->validate([
            'estado'        => 'required|in:' . implode(',', AdminComprasService::ESTADOS_INTERCAMBIO),
            'tracking_code' => 'required|string|max:100',
        ]);

        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) {
            abort(404);
        }

        $intercambio = Negociacion::findOrFail($realId);

        // Construir URL de rastreo
        $baseUrl = rtrim(env('TRACKING_BASE_URL', 'https://tracking.transporteblanco.do/rastreo'), '/');
        $trackingUrl = $baseUrl . '/' . $request->tracking_code;

        // Actualizar estado + trazabilidad
        $this->adminComprasService->actualizarEstadoIntercambio(
            $intercambio->id_negociacion,
            $request->estado,
            'Tracking enviado: ' . $request->tracking_code,
            auth()->id()
        );

        // Guardar tracking en el intercambio
        $intercambio->update([
            'tracking_code' => $request->tracking_code,
            'tracking_url'  => $trackingUrl,
        ]);

        // Notificar a ambos usuarios (emisor y receptor) vía evento
        $emisor = $intercambio->usuario;
        $receptor = $intercambio->usuarioReceptor;
        
        $texto = "El envío de tu intercambio #{$intercambio->id_negociacion} fue actualizado a \"" . ucfirst($request->estado) . "\". Rastreo: {$trackingUrl}";
        
        if ($emisor) {
            event(new NuevaNotificacion($texto, $emisor->id));
        }
        if ($receptor) {
            event(new NuevaNotificacion($texto, $receptor->id));
        }

        return redirect()->route('admin.intercambios.show', \App\Helpers\HashIdHelper::encode($realId))
            ->with('success', 'Tracking del intercambio enviado correctamente.');
    }

    public function actualizarEstadoIntercambio(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:' . implode(',', AdminComprasService::ESTADOS_INTERCAMBIO),
            'nota'   => 'nullable|string|max:500',
        ]);

        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) {
            abort(404);
        }

        $this->adminComprasService->actualizarEstadoIntercambio(
            $realId, $request->estado, $request->nota, auth()->id()
        );

        $redirect = redirect()->back();
        if ($request->estado === 'en_envio') {
            $redirect->with('download_pdf_url', route('admin.intercambios.pdf', \App\Helpers\HashIdHelper::encode($realId)));
        }

        return $redirect->with('success', 'Estado del intercambio actualizado.');
    }

    public function descargarPdf($id)
    {
        $compra = PagoCompra::with([
            'pagoItems.item',
            'carrito.usuario',
            'direccion.provincia',
            'direccion.municipio',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('admin.compras.pdf', compact('compra'));
        
        return $pdf->download("envio-orden-{$id}.pdf");
    }

    public function descargarIntercambioPdf($id)
    {
        $realId = is_numeric($id) ? (int)$id : \App\Helpers\HashIdHelper::decode($id);
        if (!$realId) {
            abort(404);
        }

        $intercambio = Negociacion::with([
            'item.imagenes',
            'item.usuario',
            'usuario.direcciones.provincia',
            'usuario.direcciones.municipio',
            'usuarioReceptor.direcciones.provincia',
            'usuarioReceptor.direcciones.municipio',
            'trazabilidad.admin',
            'pagoEnvios.tarjeta',
            'pagoEnvios.usuario',
        ])->findOrFail($realId);

        $itemsOfrecidos = collect();
        if (!empty($intercambio->items_ofrecidos)) {
            $itemsOfrecidos = \App\Models\Item::whereIn('id_item', $intercambio->items_ofrecidos)
                ->with('imagenes')
                ->get();
        }

        $hashedId = \App\Helpers\HashIdHelper::encode($realId);

        $pdf = Pdf::loadView('admin.intercambios.pdf', compact('intercambio', 'itemsOfrecidos', 'hashedId'));
        
        return $pdf->download("detalle-intercambio-{$hashedId}.pdf");
    }
}
