<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarCantidadRequest;
use App\Http\Requests\AgregarItemCarritoRequest;
use App\Http\Requests\MarcarSeleccionadoRequest;
use App\Services\CarritoService;

/**
 * ============================================================
 * CarritoController — Controlador del carrito de compras
 * ============================================================
 *
 * Gestiona todas las operaciones del carrito: visualización,
 * agregar/eliminar items, actualizar cantidades, selección
 * de items para pago y proceso de checkout.
 *
 * Toda la lógica de negocio está delegada a CarritoService.
 *
 * Rutas: /carrito/*
 * Middleware: auth
 * ============================================================
 */
class CarritoController extends Controller
{
    public function __construct(
        private CarritoService $carritoService,
    ) {}

    public function show()
    {
        $resultado = $this->carritoService->obtenerCarritoConTotales(auth()->id());

        if (!$resultado['success']) {
            return redirect()->back()->with('alerta', $resultado['message']);
        }

        $data = $resultado['data'];

        return view('carrito.carrito_show', [
            'carrito'              => $data['carrito'],
            'carritos'             => $data['carritos'],
            'totales'              => $data['totales'],
            'mensajesPredefinidos' => $data['mensajesPredefinidos'],
            'accion'               => $data['accion'],
            'todoLosPaquetes'      => $data['todoLosPaquetes'],
            'municipioDefault'     => auth()->user()
                ->direcciones()
                ->with('municipio')
                ->where('es_predeterminada', 1)
                ->first()?->municipio?->municipio ?? '',
        ]);
    }

    public function agregarItem(AgregarItemCarritoRequest $request)
    {
        $resultado = $this->carritoService->agregarItem(
            auth()->id(),
            $request->validated('id_item'),
            $request->validated('cantidad'),
            $request->validated('id_color')
        );

        if ($request->wantsJson()) {
            return response()->json($resultado);
        }

        return redirect()->route('carrito.show')->with('success', $resultado['message']);
    }

    public function eliminarItem($id_item)
    {
        $resultado = $this->carritoService->eliminarItem(auth()->id(), (int) $id_item);
        return back()->with('success', $resultado['message']);
    }

    public function vaciar()
    {
        $resultado = $this->carritoService->vaciar(auth()->id());
        return back()->with('success', $resultado['message']);
    }

    public function checkout(\Illuminate\Http\Request $request)
    {
        $tipo = $request->get('tipo');
        $resultado = $this->carritoService->prepararCheckout(auth()->id(), $tipo);
        $data = $resultado['data'];

        if (!empty($data['carrito'])) {
            \App\Models\PagoCompra::liberarOrdenesPendientes($data['carrito']->id_carrito);
        }


        // Para servicios: cargar info del proveedor de cada item
      $proveedoresInfo = [];

if ($data['carrito']->tipo === 'servicio') {
    foreach ($data['carrito']->itemsIntencionCompra as $itemIntencion) {
        $item = $itemIntencion->item;
        $proveedor = $item?->usuario;

        if (!$proveedor) {
            continue;
        }

        if (!isset($proveedoresInfo[$proveedor->id_usuario])) {

            $dirProv = $proveedor->direcciones()
                ->where('es_predeterminada', 1)
                ->with('municipio')
                ->first();

            $proveedoresInfo[$proveedor->id_usuario] = [
                'nombre' => ($proveedor->nombres ?? '') . ' ' . ($proveedor->apellidos ?? ''),
                'municipio' => $dirProv?->municipio?->municipio ?? 'Ubicacion no disponible',
                'calle' => $dirProv->calle ?? '',
                'N_casa_edificio' => $dirProv->N_casa_edificio ?? '',
                'apto' => $dirProv->apto ?? '',
                'geolocalizacion' => $dirProv->geolocalizacion ?? '',
                
            ];
        }
    }
}

        return view('carrito.checkout', [
            'carrito'          => $data['carrito'],
            'total'            => $data['total'],
            'proveedoresInfo'  => $proveedoresInfo,
            'municipioDefault' => auth()->user()
                ->direcciones()
                ->with('municipio')
                ->where('es_predeterminada', 1)
                ->first()?->municipio?->municipio ?? '',
        ]);
    }

    public function update(ActualizarCantidadRequest $request, $id)
    {
        $resultado = $this->carritoService->actualizarCantidad((int) $id, $request->validated('accion'));

        if (!$resultado['success']) {
            return redirect()->back()->with('error', $resultado['message']);
        }

        return redirect()->back()->with('success', $resultado['message']);
    }

    public function marcarSeleccionados(MarcarSeleccionadoRequest $request)
    {
        $resultado = $this->carritoService->marcarSeleccionado(
            auth()->id(),
            $request->validated('id_item'),
            $request->validated('es_seleccionado')
        );

        return response()->json([
            'status'  => 'ok',
            'totales' => $resultado['data']['totales'],
        ]);
    }

    public function getItemIds()
    {
        $userId = auth()->id();
        if (!$userId) return response()->json([]);

        $ids = \App\Models\ItemIntencionCompra::whereHas('carrito', fn($q) => $q->where('id_user', $userId))
            ->pluck('id_item')
            ->toArray();

        return response()->json($ids);
    }
}
