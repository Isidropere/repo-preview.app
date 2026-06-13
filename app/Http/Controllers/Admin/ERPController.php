<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContCuenta;
use App\Models\ContDiario;
use App\Models\ContDiarioDetalle;
use App\Models\CajaSesion;
use App\Models\Almacen;
use App\Models\InventarioMovimiento;
use App\Models\Item;
use App\Models\Negociacion;
use App\Models\PagoCompra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ERPController extends Controller
{
    // --- CONTABILIDAD ---
    public function contabilidad(Request $request)
    {
        $cuentas = ContCuenta::with('hijos')->whereNull('id_padre')->orderBy('codigo')->get();
        $todasCuentas = ContCuenta::where('permite_movimiento', true)->orderBy('codigo')->get();
        
        $query = ContDiario::with('usuario')->orderBy('fecha', 'desc');

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }
        if ($request->filled('concepto')) {
            $query->where('concepto', 'like', '%' . $request->concepto . '%');
        }
        if ($request->filled('referencia_tipo')) {
            $query->where('referencia_tipo', 'like', '%' . $request->referencia_tipo . '%');
        }
        if ($request->filled('referencia_id')) {
            $query->where('referencia_id', $request->referencia_id);
        }

        $asientos = $query->paginate(15)->withQueryString();

        return view('admin.erp.contabilidad', compact('cuentas', 'todasCuentas', 'asientos'));
    }

    public function storeCuenta(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:20|unique:cont_cuentas,codigo',
            'nombre' => 'required|string|max:100',
            'tipo'   => 'required|in:activo,pasivo,capital,ingreso,gasto,costo',
            'nivel'  => 'required|integer|min:1',
            'id_padre' => 'nullable|exists:cont_cuentas,id',
            'permite_movimiento' => 'boolean'
        ]);

        ContCuenta::create($request->all());
        return back()->with('success', 'Cuenta creada exitosamente.');
    }

    public function updateCuenta(Request $request, $id)
    {
        $cuenta = ContCuenta::findOrFail($id);
        
        $request->validate([
            'codigo' => 'required|string|max:20|unique:cont_cuentas,codigo,' . $id,
            'nombre' => 'required|string|max:100',
            'tipo'   => 'required|in:activo,pasivo,capital,ingreso,gasto,costo',
            'nivel'  => 'required|integer|min:1',
            'id_padre' => 'nullable|exists:cont_cuentas,id',
            'permite_movimiento' => 'boolean'
        ]);

        $cuenta->update($request->all());
        return back()->with('success', 'Cuenta actualizada exitosamente.');
    }

    public function destroyCuenta($id)
    {
        $cuenta = ContCuenta::findOrFail($id);
        
        // Verificar si tiene asientos asociados
        if (\App\Models\ContDiarioDetalle::where('id_cuenta', $id)->exists()) {
            return back()->with('error', 'No se puede eliminar la cuenta porque tiene transacciones asociadas.');
        }

        // Verificar si tiene hijos
        if (ContCuenta::where('id_padre', $id)->exists()) {
            return back()->with('error', 'No se puede eliminar la cuenta porque tiene subcuentas.');
        }

        $cuenta->delete();
        return back()->with('success', 'Cuenta eliminada exitosamente.');
    }

    public function detalleAsiento($id)
    {
        $asiento = ContDiario::with(['detalles.cuenta', 'usuario'])->findOrFail($id);
        return response()->json($asiento);
    }

    public function libroMayor($id_cuenta, Request $request)
    {
        $cuenta = ContCuenta::findOrFail($id_cuenta);
        
        $query = \App\Models\ContDiarioDetalle::with('diario')
            ->where('id_cuenta', $id_cuenta)
            ->whereHas('diario', function($q) {
                $q->where('estado', 'asentado');
            });

        if ($request->filled('fecha_desde')) {
            $query->whereHas('diario', function($q) use ($request) {
                $q->whereDate('fecha', '>=', $request->fecha_desde);
            });
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereHas('diario', function($q) use ($request) {
                $q->whereDate('fecha', '<=', $request->fecha_hasta);
            });
        }

        // Ordenar por fecha del diario
        $detalles = $query->join('cont_diario', 'cont_diario_detalles.id_diario', '=', 'cont_diario.id')
            ->orderBy('cont_diario.fecha', 'asc')
            ->orderBy('cont_diario.id', 'asc')
            ->select('cont_diario_detalles.*')
            ->get();

        return view('admin.erp.libro_mayor', compact('cuenta', 'detalles'));
    }

    private function getSaldosReportes($fecha_desde, $fecha_hasta)
    {
        $cuentas = ContCuenta::where('permite_movimiento', true)->get();
        $saldos = [];
        foreach ($cuentas as $cuenta) {
            $movimientos = \App\Models\ContDiarioDetalle::where('id_cuenta', $cuenta->id)
                ->whereHas('diario', function($q) use ($fecha_desde, $fecha_hasta) {
                    $q->where('estado', 'asentado')
                      ->whereDate('fecha', '>=', $fecha_desde)
                      ->whereDate('fecha', '<=', $fecha_hasta);
                })->get();
            
            $total_debe = $movimientos->sum('debe');
            $total_haber = $movimientos->sum('haber');
            
            $saldo = 0;
            if (in_array($cuenta->tipo, ['activo', 'gasto', 'costo'])) {
                $saldo = $total_debe - $total_haber;
            } else {
                $saldo = $total_haber - $total_debe;
            }

            if ($total_debe > 0 || $total_haber > 0 || $saldo != 0) {
                $saldos[$cuenta->id] = [
                    'cuenta' => $cuenta,
                    'debe' => $total_debe,
                    'haber' => $total_haber,
                    'saldo' => $saldo
                ];
            }
        }
        return $saldos;
    }

    public function reportesFinancieros(Request $request)
    {
        $fecha_desde = $request->input('fecha_desde', date('Y-m-01'));
        $fecha_hasta = $request->input('fecha_hasta', date('Y-m-d'));
        $saldos = $this->getSaldosReportes($fecha_desde, $fecha_hasta);
        
        return view('admin.erp.reportes_financieros', compact('saldos', 'fecha_desde', 'fecha_hasta'));
    }

    public function descargarReportePdf(Request $request, $tipo)
    {
        $fecha_desde = $request->input('fecha_desde', date('Y-m-01'));
        $fecha_hasta = $request->input('fecha_hasta', date('Y-m-d'));
        $saldos = $this->getSaldosReportes($fecha_desde, $fecha_hasta);

        $vista = 'admin.erp.pdf.' . $tipo;
        
        if (!view()->exists($vista)) {
            return back()->with('error', 'Reporte no válido.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($vista, compact('saldos', 'fecha_desde', 'fecha_hasta'));
        return $pdf->download('reporte_' . $tipo . '_' . date('Ymd_His') . '.pdf');
    }

    // --- INVENTARIO ---
    public function inventario()
    {
        // --- Filtros para el Stock Actual ---
        $stockTipo      = request('tipo');      // 'producto' | 'servicio'
        $stockEstatus   = request('estatus');   // '1' | '0'
        $stockBuscar    = request('buscar');    // nombre del artículo
        $stockCategoria = request('categoria'); // ID de categoría
        $stockFiltro    = request('stock_filtro'); // 'agotado' | 'bajo' | 'disponible'

        $itemsQuery = \App\Models\Item::with(['inventarios', 'usuario', 'categoria'])
            ->orderByDesc('id_item');

        if ($stockTipo === 'servicio') {
            $itemsQuery->where('id_categoria_item', 29);
        } elseif ($stockTipo === 'producto') {
            $itemsQuery->where('id_categoria_item', '!=', 29);
        }

        if ($stockEstatus !== null && $stockEstatus !== '') {
            $itemsQuery->where('estatus', $stockEstatus);
        }

        if ($stockBuscar) {
            $itemsQuery->where('item', 'like', '%' . $stockBuscar . '%');
        }

        if ($stockCategoria) {
            $itemsQuery->where('id_categoria_item', $stockCategoria);
        }

        if ($stockFiltro) {
            if ($stockFiltro === 'agotado') {
                $itemsQuery->where(function($q) {
                    $q->whereHas('inventarios', function($sq) {
                        $sq->where('cantidad', '<=', 0);
                    })->orWhereDoesntHave('inventarios');
                });
            } elseif ($stockFiltro === 'bajo') {
                $itemsQuery->whereHas('inventarios', function($q) {
                    $q->whereBetween('cantidad', [1, 3]);
                });
            } elseif ($stockFiltro === 'disponible') {
                $itemsQuery->whereHas('inventarios', function($q) {
                    $q->where('cantidad', '>', 0);
                });
            }
        }

        $items = $itemsQuery->get();

        // Categorías para el filtro
        $categorias = \App\Models\CategoriaItem::orderBy('categoria')->get();

        // --- Filtros para el Kardex ---
        $kardexDesde = request('kardex_desde');
        $kardexHasta = request('kardex_hasta');
        $kardexTipo  = request('kardex_tipo');  // 'entrada' | 'salida'
        $kardexRef   = request('kardex_ref');   // 'pago_compra' | 'negociacion' | 'item'

        $movimientosQuery = \App\Models\InventarioMovimiento::with(['item', 'almacen'])
            ->orderBy('created_at', 'desc');

        if ($kardexDesde) {
            $movimientosQuery->where('created_at', '>=', $kardexDesde . ' 00:00:00');
        }
        if ($kardexHasta) {
            $movimientosQuery->where('created_at', '<=', $kardexHasta . ' 23:59:59');
        }
        if ($kardexTipo) {
            $movimientosQuery->where('tipo', $kardexTipo);
        }
        if ($kardexRef) {
            $movimientosQuery->where('referencia_tipo', $kardexRef);
        }

        $movimientos = $movimientosQuery->paginate(20)->withQueryString();
        $almacen = \App\Models\Almacen::first();

        return view('admin.erp.inventario', compact('movimientos', 'almacen', 'items', 'categorias'));
    }

    // --- CAJA ---
    public function caja()
    {
        // Garantizar que siempre haya una sesión de caja abierta
        $sesionAbierta = CajaSesion::where('estado', 'abierta')->first();
        if (!$sesionAbierta) {
            $sesionAbierta = CajaSesion::create([
                'id_usuario_abre'      => auth()->id(),
                'fecha_apertura'       => now(),
                'monto_inicial'        => 0,
                'monto_final_esperado' => 0,
                'estado'               => 'abierta',
            ]);
        }

        // Resumen de transacciones del período actual (desde apertura hasta ahora)
        $desde = $sesionAbierta->fecha_apertura;

        // Ventas por tarjeta (checkout completado)
        $ventasTarjeta = \App\Models\PagoCompra::where('estatus', 'aprobado')
            ->where('fecha', '>=', $desde)
            ->sum('total');

        $cantVentas = \App\Models\PagoCompra::where('estatus', 'aprobado')
            ->where('fecha', '>=', $desde)
            ->count();

        // Pagos de envío por intercambios
        $pagosEnvioIntercambio = \App\Models\PagoEnvioIntercambio::where('estado', 'pagado')
            ->where('created_at', '>=', $desde)
            ->sum('monto');

        $cantIntercambios = \App\Models\PagoEnvioIntercambio::where('estado', 'pagado')
            ->where('created_at', '>=', $desde)
            ->count();

        // Talentos (registro de servicios pagados)
        $pagosTalentos = \App\Models\PagoRegistroTalento::where('estatus', 'aprobado')
            ->where('created_at', '>=', $desde)
            ->sum('monto_pagado');

        $cantTalentos = \App\Models\PagoRegistroTalento::where('estatus', 'aprobado')
            ->where('created_at', '>=', $desde)
            ->count();

        $totalPeriodo = $ventasTarjeta + $pagosEnvioIntercambio + $pagosTalentos;

        $resumenPeriodo = [
            'ventas_tarjeta'      => ['monto' => $ventasTarjeta,          'cant' => $cantVentas],
            'envios_intercambio'  => ['monto' => $pagosEnvioIntercambio,  'cant' => $cantIntercambios],
            'talentos'            => ['monto' => $pagosTalentos,           'cant' => $cantTalentos],
            'total'               => $totalPeriodo,
        ];

        // Filtros del historial
        $filtroDesde  = request('desde');
        $filtroHasta  = request('hasta');
        $filtroAdmin  = request('admin_id');

        $sesionesQuery = CajaSesion::with(['usuarioAbre', 'usuarioCierra'])
            ->where('estado', 'cerrada');

        if ($filtroDesde) {
            $sesionesQuery->where('fecha_cierre', '>=', $filtroDesde . ' 00:00:00');
        }
        if ($filtroHasta) {
            $sesionesQuery->where('fecha_cierre', '<=', $filtroHasta . ' 23:59:59');
        }
        if ($filtroAdmin) {
            $sesionesQuery->where('id_usuario_cierra', $filtroAdmin);
        }

        $sesiones = $sesionesQuery->orderBy('fecha_cierre', 'desc')->paginate(15)->withQueryString();

        // Lista de admins para el filtro
        $admins = \App\Models\User::where(function($q) {
                $q->where('isAdmin', true)->orWhere('isSuperAdmin', true)->orWhere('isContable', true);
            })
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos']);

        return view('admin.erp.caja', compact('sesiones', 'sesionAbierta', 'resumenPeriodo', 'admins'));
    }

    public function abrirCaja(Request $request)
    {
        // La caja se abre automáticamente — no se requiere apertura manual
        return redirect()->route('admin.erp.caja')->with('success', 'La caja ya está abierta.');
    }

    public function cerrarCaja(Request $request)
    {
        $request->validate([
            'monto_final_real' => 'required|numeric|min:0',
            'nota'             => 'nullable|string|max:500'
        ]);

        $sesion = CajaSesion::where('estado', 'abierta')->first();
        if (!$sesion) {
            return back()->with('error', 'No hay ninguna caja abierta para arquear.');
        }

        // El total del sistema viene del formulario (calculado en el controlador)
        $totalSistema = (float) $request->input('_total_sistema', $sesion->monto_final_esperado);
        $diferencia   = $request->monto_final_real - $totalSistema;

        // 1. Cerrar la sesión actual con los totales del período
        $sesion->update([
            'id_usuario_cierra'    => auth()->id(),
            'fecha_cierre'         => now(),
            'monto_final_esperado' => $totalSistema,       // Total que registra el sistema
            'monto_final_real'     => $request->monto_final_real, // Total confirmado en CardNet
            'diferencia'           => $diferencia,
            'nota'                 => $request->nota,
            'estado'               => 'cerrada'
        ]);

        // 2. Reabrir automáticamente con saldo 0 (las ventas futuras se acumulan de nuevo)
        CajaSesion::create([
            'id_usuario_abre'      => auth()->id(),
            'fecha_apertura'       => now(),
            'monto_inicial'        => 0,
            'monto_final_esperado' => 0,
            'estado'               => 'abierta'
        ]);

        $textoDif = $diferencia == 0
            ? 'Sin diferencias.'
            : ($diferencia > 0
                ? 'Sobrante de RD$ ' . number_format(abs($diferencia), 2)
                : 'Faltante de RD$ ' . number_format(abs($diferencia), 2));

        return back()->with('success',
            "Arqueo registrado correctamente. {$textoDif} La caja fue reabierta automáticamente.");
    }

    // --- ACCIONES MANUALES ---
    public function storeAsiento(Request $request)
    {
        $request->validate([
            'fecha'    => 'required|date',
            'concepto' => 'required|string|max:255',
            'detalles' => 'required|array|min:2',
            'detalles.*.id_cuenta' => 'required|exists:cont_cuentas,id',
            'detalles.*.debe'      => 'required|numeric|min:0',
            'detalles.*.haber'     => 'required|numeric|min:0',
        ]);

        $totalDebe  = collect($request->detalles)->sum('debe');
        $totalHaber = collect($request->detalles)->sum('haber');

        if (abs($totalDebe - $totalHaber) > 0.01) {
            return back()->with('error', 'El asiento no está cuadrado. El total del Debe debe ser igual al Haber.');
        }

        DB::transaction(function () use ($request, $totalDebe, $totalHaber) {
            $asiento = ContDiario::create([
                'fecha'           => $request->fecha,
                'concepto'        => $request->concepto,
                'total_debe'      => $totalDebe,
                'total_haber'     => $totalHaber,
                'referencia_tipo' => 'manual',
                'id_usuario_crea' => auth()->id(),
            ]);

            foreach ($request->detalles as $det) {
                if ($det['debe'] > 0 || $det['haber'] > 0) {
                    ContDiarioDetalle::create([
                        'id_diario' => $asiento->id,
                        'id_cuenta' => $det['id_cuenta'],
                        'debe'      => $det['debe'],
                        'haber'     => $det['haber'],
                    ]);
                }
            }
        });

        return back()->with('success', 'Asiento contable registrado correctamente.');
    }

    public function historialTransacciones(Request $request)
    {
        $request->validate([
            'tab'         => 'nullable|string|in:ventas,intercambios',
            'buscar'      => 'nullable|string|max:100',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date',
            'estatus'     => 'nullable|string|max:50',
        ]);

        $tab = $request->get('tab', 'ventas');
        $buscar = $request->get('buscar');
        $estatus = $request->get('estatus');

        // Ventas completadas/procesadas (aprobado, enviado, entregado)
        $ventasQuery = PagoCompra::whereIn('estatus', ['aprobado', 'enviado', 'entregado'])
            ->with(['pagoItems.item.imagenes', 'carrito.usuario', 'tarjeta']);

        $talentoQuery = \App\Models\PagoRegistroTalento::where('estatus', 'aprobado')
            ->with(['item.imagenes', 'user']);

        if ($request->filled('estatus')) {
            $ventasQuery->where('estatus', $estatus);
            if ($estatus !== 'aprobado') {
                $talentoQuery->where('estatus', 'non-existent');
            }
        }

        if ($request->filled('fecha_desde')) {
            $ventasQuery->whereDate('fecha', '>=', $request->fecha_desde);
            $talentoQuery->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $ventasQuery->whereDate('fecha', '<=', $request->fecha_hasta);
            $talentoQuery->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($tab === 'ventas' && $buscar) {
            $ventasQuery->where(fn($q) => $q
                ->where('id_pago_compra', 'like', "%$buscar%")
                ->orWhereHas('carrito.usuario', fn($q2) => $q2
                    ->where('nombres', 'like', "%$buscar%")
                    ->orWhere('email', 'like', "%$buscar%")));

            $talentoQuery->where(fn($q) => $q
                ->where('transaction_id', 'like', "%$buscar%")
                ->orWhere('id_item', 'like', "%$buscar%")
                ->orWhere('notas', 'like', "%$buscar%")
                ->orWhereHas('user', fn($q2) => $q2
                    ->where('nombres', 'like', "%$buscar%")
                    ->orWhere('email', 'like', "%$buscar%"))
                ->orWhereHas('item', fn($q2) => $q2
                    ->where('item', 'like', "%$buscar%")));
        }

        $ventasCol = $ventasQuery->get();
        $talentoCol = $talentoQuery->get();

        $mappedTalentos = $talentoCol->map(function ($talento) {
            $virtualPago = new PagoCompra();
            $virtualPago->id_pago_compra = 'TAL-' . $talento->id_item . '-' . $talento->id;
            $virtualPago->estatus = 'aprobado';
            $virtualPago->total = (float) $talento->monto_pagado;
            $virtualPago->fecha = $talento->created_at;
            $virtualPago->is_talent_registration = true;
            $virtualPago->talent_name = $talento->item?->item ?? 'Talento-Servicio';
            $virtualPago->talent_id = $talento->id_item;
            $virtualPago->user = $talento->user;

            // Mock relation 'carrito' so that $pago->carrito->usuario works in views
            $carrito = new \App\Models\Carrito();
            $carrito->setRelation('usuario', $talento->user);
            $virtualPago->setRelation('carrito', $carrito);

            // Fetch logs_pagos payload
            $log = \Illuminate\Support\Facades\DB::table('logs_pagos')
                ->where('transaction_type', 'talento_approved')
                ->where('is_success', true)
                ->where(function($q) use ($talento) {
                    $q->where('custom_order_id', 'like', 'TAL-' . $talento->id_item . '-%')
                      ->orWhere('custom_order_id', 'like', '%' . $talento->transaction_id . '%')
                      ->orWhere('response_payload', 'like', '%' . $talento->transaction_id . '%');
                })
                ->first();

            if ($log && !empty($log->response_payload)) {
                $payload = json_decode($log->response_payload, true);
                if (is_array($payload)) {
                    $virtualPago->azul_response_data = $payload;
                }
            } else {
                if (!empty($talento->notas) && preg_match('/Código Autorización:\s*([A-Za-z0-9]+)/i', $talento->notas, $matches)) {
                    $authCode = $matches[1];
                    $virtualPago->azul_response_data = [
                        'CardNumber' => 'xxxx-xxxx-xxxx-xxxx',
                        'DataVaultBrand' => 'Tarjeta',
                        'AuthorizationCode' => $authCode,
                        'RRN' => $talento->transaction_id,
                    ];
                }
            }

            return $virtualPago;
        });

        $merged = $ventasCol->concat($mappedTalentos)->sortByDesc(function ($item) {
            return $item->fecha ? $item->fecha->timestamp : 0;
        });

        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage('page_ventas');
        $perPage = 15;
        $currentPageItems = $merged->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $ventas = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $merged->count(),
            $perPage,
            $currentPage,
            [
                'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page_ventas',
            ]
        );
        $ventas->withQueryString();

        // Intercambios completados/procesados (aceptado, en_envio, completado)
        $intercambiosQuery = Negociacion::whereIn('estado', ['aceptado', 'en_envio', 'completado'])
            ->with(['item.imagenes', 'usuario', 'usuarioReceptor', 'pagoEnvios.tarjeta'])
            ->orderByDesc('id_negociacion');

        if ($request->filled('estatus')) {
            $intercambiosQuery->where('estado', $estatus);
        }

        if ($request->filled('fecha_desde')) {
            $intercambiosQuery->whereDate('fecha_creacion', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $intercambiosQuery->whereDate('fecha_creacion', '<=', $request->fecha_hasta);
        }

        if ($tab === 'intercambios' && $buscar) {
            $intercambiosQuery->where(fn($q) => $q
                ->where('id_negociacion', 'like', "%$buscar%")
                ->orWhereHas('usuario', fn($q2) => $q2
                    ->where('nombres', 'like', "%$buscar%")
                    ->orWhere('email', 'like', "%$buscar%"))
                ->orWhereHas('usuarioReceptor', fn($q2) => $q2
                    ->where('nombres', 'like', "%$buscar%")
                    ->orWhere('email', 'like', "%$buscar%"))
                ->orWhereHas('item', fn($q2) => $q2
                    ->where('item', 'like', "%$buscar%")));
        }

        $intercambios = $intercambiosQuery->paginate(15, ['*'], 'page_intercambios')->withQueryString();

        return view('admin.erp.historial', compact('ventas', 'intercambios', 'tab'));
    }

    public function descargarHistorialPdf(Request $request)
    {
        $request->validate([
            'tab'         => 'nullable|string|in:ventas,intercambios',
            'buscar'      => 'nullable|string|max:100',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date',
            'estatus'     => 'nullable|string|max:50',
        ]);

        $tab = $request->get('tab', 'ventas');
        $buscar = $request->get('buscar');
        $estatus = $request->get('estatus');
        $fecha_desde = $request->get('fecha_desde');
        $fecha_hasta = $request->get('fecha_hasta');

        if ($tab === 'ventas') {
            $query = PagoCompra::whereIn('estatus', ['aprobado', 'enviado', 'entregado'])
                ->with(['pagoItems.item', 'carrito.usuario', 'tarjeta']);

            $talentoQuery = \App\Models\PagoRegistroTalento::where('estatus', 'aprobado')
                ->with(['item', 'user']);

            if ($request->filled('estatus')) {
                $query->where('estatus', $estatus);
                if ($estatus !== 'aprobado') {
                    $talentoQuery->where('estatus', 'non-existent');
                }
            }
            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha', '>=', $fecha_desde);
                $talentoQuery->whereDate('created_at', '>=', $fecha_desde);
            }
            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha', '<=', $fecha_hasta);
                $talentoQuery->whereDate('created_at', '<=', $fecha_hasta);
            }
            if ($buscar) {
                $query->where(fn($q) => $q
                    ->where('id_pago_compra', 'like', "%$buscar%")
                    ->orWhereHas('carrito.usuario', fn($q2) => $q2
                        ->where('nombres', 'like', "%$buscar%")
                        ->orWhere('email', 'like', "%$buscar%")));

                $talentoQuery->where(fn($q) => $q
                    ->where('transaction_id', 'like', "%$buscar%")
                    ->orWhere('id_item', 'like', "%$buscar%")
                    ->orWhere('notas', 'like', "%$buscar%")
                    ->orWhereHas('user', fn($q2) => $q2
                        ->where('nombres', 'like', "%$buscar%")
                        ->orWhere('email', 'like', "%$buscar%"))
                    ->orWhereHas('item', fn($q2) => $q2
                        ->where('item', 'like', "%$buscar%")));
            }

            $ventasCol = $query->get();
            $talentoCol = $talentoQuery->get();

            $mappedTalentos = $talentoCol->map(function ($talento) {
                $virtualPago = new PagoCompra();
                $virtualPago->id_pago_compra = 'TAL-' . $talento->id_item . '-' . $talento->id;
                $virtualPago->estatus = 'aprobado';
                $virtualPago->total = (float) $talento->monto_pagado;
                $virtualPago->fecha = $talento->created_at;
                $virtualPago->is_talent_registration = true;
                $virtualPago->talent_name = $talento->item?->item ?? 'Talento-Servicio';
                $virtualPago->talent_id = $talento->id_item;
                $virtualPago->user = $talento->user;

                $carrito = new \App\Models\Carrito();
                $carrito->setRelation('usuario', $talento->user);
                $virtualPago->setRelation('carrito', $carrito);

                // Fetch logs_pagos payload
                $log = \Illuminate\Support\Facades\DB::table('logs_pagos')
                    ->where('transaction_type', 'talento_approved')
                    ->where('is_success', true)
                    ->where(function($q) use ($talento) {
                        $q->where('custom_order_id', 'like', 'TAL-' . $talento->id_item . '-%')
                          ->orWhere('custom_order_id', 'like', '%' . $talento->transaction_id . '%')
                          ->orWhere('response_payload', 'like', '%' . $talento->transaction_id . '%');
                    })
                    ->first();

                if ($log && !empty($log->response_payload)) {
                    $payload = json_decode($log->response_payload, true);
                    if (is_array($payload)) {
                        $virtualPago->azul_response_data = $payload;
                    }
                } else {
                    if (!empty($talento->notas) && preg_match('/Código Autorización:\s*([A-Za-z0-9]+)/i', $talento->notas, $matches)) {
                        $authCode = $matches[1];
                        $virtualPago->azul_response_data = [
                            'CardNumber' => 'xxxx-xxxx-xxxx-xxxx',
                            'DataVaultBrand' => 'Tarjeta',
                            'AuthorizationCode' => $authCode,
                            'RRN' => $talento->transaction_id,
                        ];
                    }
                }

                return $virtualPago;
            });

            $data = $ventasCol->concat($mappedTalentos)->sortByDesc(function ($item) {
                return $item->fecha ? $item->fecha->timestamp : 0;
            });
        } else {
            $query = Negociacion::whereIn('estado', ['aceptado', 'en_envio', 'completado'])
                ->with(['item.imagenes', 'usuario', 'usuarioReceptor', 'pagoEnvios.tarjeta'])
                ->orderByDesc('id_negociacion');

            if ($request->filled('estatus')) {
                $query->where('estado', $estatus);
            }
            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha_creacion', '>=', $fecha_desde);
            }
            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha_creacion', '<=', $fecha_hasta);
            }
            if ($buscar) {
                $query->where(fn($q) => $q
                    ->where('id_negociacion', 'like', "%$buscar%")
                    ->orWhereHas('usuario', fn($q2) => $q2
                        ->where('nombres', 'like', "%$buscar%")
                        ->orWhere('email', 'like', "%$buscar%"))
                    ->orWhereHas('usuarioReceptor', fn($q2) => $q2
                        ->where('nombres', 'like', "%$buscar%")
                        ->orWhere('email', 'like', "%$buscar%"))
                    ->orWhereHas('item', fn($q2) => $q2
                        ->where('item', 'like', "%$buscar%")));
            }
            $data = $query->get();
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.erp.historial_pdf', compact('data', 'tab', 'buscar', 'estatus', 'fecha_desde', 'fecha_hasta'));
        
        $filename = "reporte-{$tab}-erp-" . date('Ymd_His') . ".pdf";
        return $pdf->download($filename);
    }
}
