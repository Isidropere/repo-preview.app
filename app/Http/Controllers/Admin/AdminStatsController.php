<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZona;
use App\Models\PagoCompra;
use App\Services\DeliveryService;
use App\Models\ItemIntencionCompra;
use App\Models\Negociacion;
use App\Models\CompraTrazabilidad;
use App\Models\Item;
use App\Models\ItemView;
use App\Models\User;
use App\Models\Direcciones;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================
 * AdminStatsController — Dashboard de estadísticas (Super Admin)
 * ============================================================
 *
 * Genera métricas completas de la plataforma:
 * - KPIs de compras, ventas, intercambios, usuarios
 * - Gráficos por día/estado
 * - Top vendedores, compradores, intercambiadores
 * - Tasas de conversión y tiempos de cierre
 * - Ingresos semanales/mensuales
 * - Actividad por provincia
 * - Estadísticas de delivery
 * - Alertas automáticas
 *
 * Rutas: /admin/estadisticas, /admin/estadisticas/data
 * Middleware: auth, superadmin
 * ============================================================
 */
class AdminStatsController extends Controller
{
    public function __construct(
        private DeliveryService $deliveryService,
    ) {}

    public function index()
    {
        $cuentasBanco = \App\Models\CuentaBancoEmpresa::orderBy('id', 'desc')->get();
        $configTarifa = \App\Models\ConfigTarifaCategoria29::vigente();

        return view('admin.stats', compact('cuentasBanco', 'configTarifa'));
    }

    public function data(Request $request)
    {
        // ── Filtros recibidos ──────────────────────────────────────────
        // periodo: 7d | 30d | 90d | 365d | custom
        // fecha_desde / fecha_hasta: YYYY-MM-DD (solo con periodo=custom)
        // estatus_compra: pendiente|aprobado|rechazado|enviado|entregado|cancelado|'' (todos)
        // estado_intercambio: Inicial|pendiente|contraoferta|aceptado|completado|rechazado|cancelado|''
        // tipo_trans: 1=venta|2=intercambio|3=ambos|'' (todos)

        $periodo      = $request->input('periodo', '30d');
        $estatusComp  = $request->input('estatus_compra', '');
        $estadoNeg    = $request->input('estado_intercambio', '');
        $tipoTrans    = $request->input('tipo_trans', '');

        // Calcular rango de fechas
        [$desde, $hasta] = $this->calcularRango($request, $periodo);

        // ── 1. COMPRAS POR DÍA ────────────────────────────────────────
        $qComprasDia = PagoCompra::selectRaw('DATE(fecha) as dia, COUNT(*) as total, SUM(total) as monto')
            ->whereBetween('fecha', [$desde, $hasta]);
        if ($estatusComp) $qComprasDia->where('estatus', $estatusComp);
        $comprasPorDia = $qComprasDia->groupBy('dia')->orderBy('dia')->get();

        // ── 2. COMPRAS POR ESTADO ─────────────────────────────────────
        $qComprasEst = PagoCompra::selectRaw('estatus, COUNT(*) as total')
            ->whereBetween('fecha', [$desde, $hasta]);
        if ($estatusComp) $qComprasEst->where('estatus', $estatusComp);
        $comprasPorEstado = $qComprasEst->groupBy('estatus')->get()
            ->map(fn($r) => ['estatus' => ucfirst($r->estatus ?? 'desconocido'), 'total' => $r->total]);

        // ── 3. INTERCAMBIOS POR DÍA ───────────────────────────────────
        $qNegDia = Negociacion::selectRaw('DATE(fecha_creacion) as dia, COUNT(*) as total')
            ->whereBetween('fecha_creacion', [$desde, $hasta]);
        if ($estadoNeg) $qNegDia->where('estado', $estadoNeg);
        $intercambiosPorDia = $qNegDia->groupBy('dia')->orderBy('dia')->get();

        // ── 4. INTERCAMBIOS POR ESTADO ────────────────────────────────
        $qNegEst = Negociacion::selectRaw('estado, COUNT(*) as total')
            ->whereBetween('fecha_creacion', [$desde, $hasta]);
        if ($estadoNeg) $qNegEst->where('estado', $estadoNeg);
        $intercambiosPorEstado = $qNegEst->groupBy('estado')->get();

        // ── 5. VENTAS POR DÍA ─────────────────────────────────────────
        $qVentasDia = ItemIntencionCompra::selectRaw('DATE(pagos_compra.fecha) as dia, COUNT(*) as total')
            ->join('carritos',    'items_intencion_compra.id_carrito', '=', 'carritos.id_carrito')
            ->join('pagos_compra','carritos.id_carrito',               '=', 'pagos_compra.id_carrito')
            ->join('items',       'items_intencion_compra.id_item',    '=', 'items.id_item')
            ->whereBetween('pagos_compra.fecha', [$desde, $hasta]);

        // Filtro tipo_trans: si es '' o '3' mostramos tipo 1 y 2; si es '1' solo venta; si es '2' solo intercambio
        if ($tipoTrans === '1') {
            $qVentasDia->where('items.tipo_trans', 1);
        } elseif ($tipoTrans === '2') {
            $qVentasDia->where('items.tipo_trans', 2);
        } else {
            $qVentasDia->whereIn('items.tipo_trans', [1, 2, 3]);
        }
        if ($estatusComp) $qVentasDia->where('pagos_compra.estatus', $estatusComp);
        $ventasPorDia = $qVentasDia->groupBy('dia')->orderBy('dia')->get();

        // ── 6. TRAZABILIDAD RECIENTE ──────────────────────────────────
        $qTraza = CompraTrazabilidad::with('admin:id,nombres')
            ->whereBetween('created_at', [$desde, $hasta->copy()->endOfDay()]);
        if ($estatusComp) $qTraza->where('estado_nuevo', $estatusComp);
        $trazabilidad = $qTraza->orderByDesc('created_at')->limit(50)
            ->get(['id','id_pago_compra','estado_anterior','estado_nuevo','nota','id_admin','created_at']);

        // ── 7. MONTO POR DÍA (solo aprobadas) ────────────────────────
        $qMontoDia = PagoCompra::selectRaw('DATE(fecha) as dia, SUM(total) as monto')
            ->whereBetween('fecha', [$desde, $hasta])
            ->where('estatus', 'aprobado');
        $montoPorDia = $qMontoDia->groupBy('dia')->orderBy('dia')->get();

        // ── 8. TOP CATEGORÍAS ─────────────────────────────────────────
        $topCategorias = ItemIntencionCompra::selectRaw('categorias_item.categoria, COUNT(*) as total')
            ->join('items',          'items_intencion_compra.id_item',    '=', 'items.id_item')
            ->join('categorias_item','items.id_categoria_item',           '=', 'categorias_item.id_categoria_item')
            ->join('carritos',       'items_intencion_compra.id_carrito', '=', 'carritos.id_carrito')
            ->join('pagos_compra',   'carritos.id_carrito',               '=', 'pagos_compra.id_carrito')
            ->whereBetween('pagos_compra.fecha', [$desde, $hasta])
            ->groupBy('categorias_item.id_categoria_item', 'categorias_item.categoria')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // ── 9. USUARIOS NUEVOS POR DÍA ────────────────────────────────
        $usuariosNuevos = User::selectRaw('DATE(created_at) as dia, COUNT(*) as total')
            ->whereBetween('created_at', [$desde, $hasta])
            ->groupBy('dia')->orderBy('dia')->get();

        // ── 10. ITEMS PUBLICADOS POR DÍA ──────────────────────────────
        $itemsPublicados = Item::selectRaw('DATE(fecha) as dia, COUNT(*) as total')
            ->whereBetween('fecha', [$desde, $hasta])
            ->groupBy('dia')->orderBy('dia')->get();

        // ── 11. KPIs ──────────────────────────────────────────────────
        $baseCompras = PagoCompra::whereBetween('fecha', [$desde, $hasta]);
        $baseNegs    = Negociacion::whereBetween('fecha_creacion', [$desde, $hasta]);

        $kpis = [
            'total_compras'            => (clone $baseCompras)->count(),
            'compras_pendientes'       => (clone $baseCompras)->where('estatus', 'pendiente')->count(),
            'compras_aprobadas'        => (clone $baseCompras)->where('estatus', 'aprobado')->count(),
            'compras_entregadas'       => (clone $baseCompras)->where('estatus', 'entregado')->count(),
            'monto_total'              => (clone $baseCompras)->where('estatus', 'aprobado')->sum('total'),
            'total_intercambios'       => (clone $baseNegs)->count(),
            'intercambios_activos'     => (clone $baseNegs)->whereIn('estado', ['Inicial','pendiente','contraoferta'])->count(),
            'intercambios_completados' => (clone $baseNegs)->whereIn('estado', ['completado','aceptado'])->count(),
            'total_ventas'             => ItemIntencionCompra::whereHas('carrito.pagosCompra', function($q) use ($desde, $hasta) {
                                            $q->whereBetween('fecha', [$desde, $hasta]);
                                          })
                                          ->whereHas('item', fn($q) => $q->where('tipo_trans', 1))
                                          ->distinct('id_item')->count('id_item'),
            'usuarios_nuevos'          => User::whereBetween('created_at', [$desde, $hasta])->count(),
            'items_publicados'         => Item::whereBetween('fecha', [$desde, $hasta])->count(),
        ];

        // ── 12. TASA DE CONVERSIÓN ────────────────────────────────────
        $totalIntenciones   = ItemIntencionCompra::join('carritos', 'items_intencion_compra.id_carrito', '=', 'carritos.id_carrito')
            ->join('pagos_compra', 'carritos.id_carrito', '=', 'pagos_compra.id_carrito')
            ->whereBetween('pagos_compra.fecha', [$desde, $hasta])->count();
        $intencionesPagadas = ItemIntencionCompra::join('carritos', 'items_intencion_compra.id_carrito', '=', 'carritos.id_carrito')
            ->join('pagos_compra', 'carritos.id_carrito', '=', 'pagos_compra.id_carrito')
            ->whereBetween('pagos_compra.fecha', [$desde, $hasta])
            ->where('pagos_compra.estatus', 'aprobado')->count();
        $totalNegociaciones   = (clone $baseNegs)->count();
        $negCompletadas       = (clone $baseNegs)->whereIn('estado', ['completado','aceptado'])->count();
        $tasaConversionCompra = $totalIntenciones > 0 ? round(($intencionesPagadas / $totalIntenciones) * 100, 1) : 0;
        $tasaConversionNeg    = $totalNegociaciones > 0 ? round(($negCompletadas / $totalNegociaciones) * 100, 1) : 0;

        // ── 13. TIEMPO PROMEDIO DE CIERRE ─────────────────────────────
        $tiempoCierre = Negociacion::selectRaw('estado, AVG(DATEDIFF(NOW(), fecha_creacion)) as promedio_dias, COUNT(*) as total')
            ->whereBetween('fecha_creacion', [$desde, $hasta])
            ->whereIn('estado', ['aceptado','completado','rechazado','cancelado'])
            ->groupBy('estado')->get();

        // ── 14. USUARIOS MÁS ACTIVOS ──────────────────────────────────
        $topVendedores = DB::table('pagos_compra')
            ->join('carritos', 'pagos_compra.id_carrito', '=', 'carritos.id_carrito')
            ->join('items_intencion_compra', 'carritos.id_carrito', '=', 'items_intencion_compra.id_carrito')
            ->join('items', 'items_intencion_compra.id_item', '=', 'items.id_item')
            ->join('users', 'items.id_user', '=', 'users.id')
            ->whereBetween('pagos_compra.fecha', [$desde, $hasta])
            ->where('pagos_compra.estatus', 'aprobado')
            ->selectRaw('users.id, CONCAT(users.nombres, " ", users.apellidos) as nombre, users.nombre_usuario, COUNT(*) as ventas, SUM(pagos_compra.total) as monto')
            ->groupBy('users.id', 'users.nombres', 'users.apellidos', 'users.nombre_usuario')
            ->orderByDesc('ventas')->limit(10)->get();

        $topCompradores = DB::table('pagos_compra')
            ->join('carritos', 'pagos_compra.id_carrito', '=', 'carritos.id_carrito')
            ->join('users', 'carritos.id_user', '=', 'users.id')
            ->whereBetween('pagos_compra.fecha', [$desde, $hasta])
            ->where('pagos_compra.estatus', 'aprobado')
            ->selectRaw('users.id, CONCAT(users.nombres, " ", users.apellidos) as nombre, users.nombre_usuario, COUNT(*) as compras, SUM(pagos_compra.total) as monto')
            ->groupBy('users.id', 'users.nombres', 'users.apellidos', 'users.nombre_usuario')
            ->orderByDesc('compras')->limit(10)->get();

        $topIntercambiadores = DB::table('negociaciones')
            ->join('users', 'negociaciones.usuario_emisor_id', '=', 'users.id')
            ->whereBetween('negociaciones.fecha_creacion', [$desde, $hasta])
            ->whereIn('negociaciones.estado', ['aceptado','completado'])
            ->selectRaw('users.id, CONCAT(users.nombres, " ", users.apellidos) as nombre, users.nombre_usuario, COUNT(*) as intercambios')
            ->groupBy('users.id', 'users.nombres', 'users.apellidos', 'users.nombre_usuario')
            ->orderByDesc('intercambios')->limit(10)->get();

        // ── 15. ITEMS SIN MOVIMIENTO (>30 días sin vistas ni negociaciones) ──
        $itemsSinMovimiento = DB::table('items')
            ->leftJoin('negociaciones', 'items.id_item', '=', 'negociaciones.receptor_item_id')
            ->leftJoin('item_views', function($join) {
                $join->on('items.id_item', '=', 'item_views.id_item')
                     ->where('item_views.created_at', '>=', now()->subDays(30));
            })
            ->where('items.estatus', 1)
            ->where('items.fecha', '<=', now()->subDays(30))
            ->selectRaw('items.id_item, items.item, items.fecha, items.tipo_trans,
                COUNT(DISTINCT negociaciones.id_negociacion) as neg_total,
                COUNT(DISTINCT item_views.id) as vistas_recientes')
            ->groupBy('items.id_item', 'items.item', 'items.fecha', 'items.tipo_trans')
            ->havingRaw('neg_total = 0 AND vistas_recientes = 0')
            ->orderBy('items.fecha')->limit(20)->get()
            ->map(fn($i) => [
                'id'    => $i->id_item,
                'nombre'=> $i->item,
                'fecha' => $i->fecha,
                'tipo'  => match((int)$i->tipo_trans) { 1=>'Venta', 2=>'Intercambio', 3=>'Ambos', default=>'-' },
                'dias'  => (int) abs(now()->diffInDays($i->fecha)),
            ]);

        // ── 16. INGRESOS POR PERÍODO ──────────────────────────────────
        $ingresosSemanal = PagoCompra::selectRaw('YEARWEEK(fecha,1) as semana, SUM(total) as monto, COUNT(*) as transacciones')
            ->whereBetween('fecha', [$desde, $hasta])->where('estatus', 'aprobado')
            ->groupBy('semana')->orderBy('semana')->get()
            ->map(fn($r) => ['periodo'=>'Sem '.substr($r->semana,4), 'monto'=>(float)$r->monto, 'transacciones'=>$r->transacciones]);

        $ingresosMensual = PagoCompra::selectRaw('DATE_FORMAT(fecha,"%Y-%m") as mes, SUM(total) as monto, COUNT(*) as transacciones')
            ->whereBetween('fecha', [$desde, $hasta])->where('estatus', 'aprobado')
            ->groupBy('mes')->orderBy('mes')->get()
            ->map(fn($r) => ['periodo'=>$r->mes, 'monto'=>(float)$r->monto, 'transacciones'=>$r->transacciones]);

        // ── 17. ACTIVIDAD POR PROVINCIA ───────────────────────────────
        $actividadProvincia = DB::table('provincias')
            ->leftJoin('municipios', 'provincias.id_provincia', '=', 'municipios.id_provincia')
            ->leftJoin('direcciones', function($join) {
                $join->on('municipios.id_municipio', '=', 'direcciones.id_municipio')
                     ->where('direcciones.es_predeterminada', 1);
            })
            ->leftJoin('users', 'direcciones.id_user', '=', 'users.id')
            ->selectRaw('provincias.provincia, COUNT(DISTINCT users.id) as usuarios')
            ->groupBy('provincias.id_provincia', 'provincias.provincia')
            ->orderByDesc('usuarios')->get();

        // ── 18. DELIVERY STATS ────────────────────────────────────────
        $deliveryConfig = DB::table('delivery_config')->get()->keyBy('clave');
        $deliveryZonas  = DeliveryZona::where('activo', true)->get();

        $deliveryStats = $deliveryZonas->map(function ($zona) {
            $r = $this->deliveryService->calcularPorZona($zona, 'persona');
            $d = $r['desglose'] ?? [];

            return [
                'zona'               => $zona->zona,
                'tipo'               => $zona->tipo,
                'precio_base'        => $d['precio_base_proveedor'] ?? (float) $zona->precio_persona,
                'costo_estimado'     => $r['costo_envio_total'] ?? 0,
                'pct_ganancia'       => $d['ganancia_negocio_pct'] ?? 0,
                'pct_plataforma'     => $d['plataforma_pct'] ?? 0,
                'pct_manejo'         => $d['manejo_pct'] ?? 0,
                'pct_seguro'         => $d['seguro_pct'] ?? 0,
                'costo_seguro'       => $d['costo_seguro'] ?? 0,
                'dias_entrega'       => $zona->dias_entrega,
            ];
        });

        $deliveryConfigData = $deliveryConfig->values();

        // ── 18. ALERTAS AUTOMÁTICAS ───────────────────────────────────
        $alertas = [];

        $pagosFallidos24h = PagoCompra::where('estatus', 'rechazado')->where('fecha', '>=', now()->subHours(24))->count();
        if ($pagosFallidos24h >= 3) {
            $alertas[] = ['tipo'=>'danger','icono'=>'💳','titulo'=>'Pagos fallidos','mensaje'=>"{$pagosFallidos24h} pagos rechazados en las últimas 24 horas."];
        }

        $usuariosConRechazos = DB::table('negociaciones')
            ->join('users', 'negociaciones.usuario_receptor_id', '=', 'users.id')
            ->where('negociaciones.estado', 'rechazado')
            ->whereBetween('negociaciones.fecha_creacion', [$desde, $hasta])
            ->selectRaw('users.id, CONCAT(users.nombres," ",users.apellidos) as nombre, COUNT(*) as rechazos')
            ->groupBy('users.id','users.nombres','users.apellidos')
            ->having('rechazos','>=',5)->orderByDesc('rechazos')->limit(5)->get();
        foreach ($usuariosConRechazos as $u) {
            $alertas[] = ['tipo'=>'warning','icono'=>'⚠️','titulo'=>'Alto rechazo','mensaje'=>"{$u->nombre} rechazó {$u->rechazos} negociaciones en el período."];
        }

        $itemsParados60 = Item::where('estatus', 1)->where('fecha', '<=', now()->subDays(60))
            ->whereDoesntHave('views', fn($q) => $q->where('created_at', '>=', now()->subDays(60)))->count();
        if ($itemsParados60 > 0) {
            $itemsSinVisitas60 = Item::where('estatus', 1)->where('fecha', '<=', now()->subDays(60))
                ->whereDoesntHave('views', fn($q) => $q->where('created_at', '>=', now()->subDays(60)))
                ->orderBy('fecha', 'desc')
                ->limit(20)
                ->get();

            $itemsPayload = $itemsSinVisitas60->map(function ($item) {
                $fechaStr = '-';
                if ($item->fecha) {
                    try {
                        $fechaStr = \Illuminate\Support\Carbon::parse($item->fecha)->format('d/m/Y');
                    } catch (\Throwable $e) {
                        $fechaStr = $item->fecha;
                    }
                }
                return [
                    'id_item' => $item->id_item,
                    'item' => $item->item,
                    'slug' => $item->slug,
                    'fecha' => $fechaStr,
                ];
            });

            $alertas[] = [
                'tipo' => 'info',
                'icono' => '📦',
                'titulo' => 'Items sin actividad',
                'mensaje' => "{$itemsParados60} publicaciones llevan más de 60 días sin vistas.",
                'items' => $itemsPayload
            ];
        }

        if ($tasaConversionCompra < 20 && $totalIntenciones > 10) {
            $alertas[] = ['tipo'=>'warning','icono'=>'📉','titulo'=>'Conversión baja','mensaje'=>"Solo el {$tasaConversionCompra}% de las intenciones de compra terminan en pago."];
        }

        if (empty($alertas)) {
            $alertas[] = ['tipo'=>'success','icono'=>'✅','titulo'=>'Todo en orden','mensaje'=>'No se detectaron alertas en el período seleccionado.'];
        }

        $deliveryErrors = \App\Models\Message::where('mensaje', 'like', '%pero no se pudo calcular el costo de envío porque no está registrada%')
            ->where('leido', false)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($msg) {
                preg_match("/El usuario (?:comprador )?(.*?) \(ID: (\d+)\) tiene registrada la dirección '(.*?)'/", $msg->mensaje, $matches);
                return [
                    'id' => $msg->id,
                    'usuario_id' => $matches[2] ?? null,
                    'usuario_nombre' => $matches[1] ?? 'Desconocido',
                    'direccion' => $matches[3] ?? 'Desconocida',
                    'fecha' => $msg->created_at ? $msg->created_at->format('d/m/Y H:i:s') : '-',
                ];
            });

        return response()->json([
            'kpis'                    => $kpis,
            'compras_por_dia'         => $comprasPorDia,
            'compras_por_estado'      => $comprasPorEstado,
            'ventas_por_dia'          => $ventasPorDia,
            'intercambios_por_dia'    => $intercambiosPorDia,
            'intercambios_por_estado' => $intercambiosPorEstado,
            'monto_por_dia'           => $montoPorDia,
            'top_categorias'          => $topCategorias,
            'usuarios_nuevos'         => $usuariosNuevos,
            'items_publicados'        => $itemsPublicados,
            'trazabilidad'            => $trazabilidad,
            'tasa_conversion'         => ['compra'=>$tasaConversionCompra,'negociacion'=>$tasaConversionNeg,'total_intenc'=>$totalIntenciones,'pagadas'=>$intencionesPagadas,'total_negs'=>$totalNegociaciones,'negs_cerradas'=>$negCompletadas],
            'tiempo_cierre'           => $tiempoCierre,
            'top_vendedores'          => $topVendedores,
            'top_compradores'         => $topCompradores,
            'top_intercambiadores'    => $topIntercambiadores,
            'items_sin_movimiento'    => $itemsSinMovimiento,
            'top_items_vistos'        => DB::table('item_views')
                ->join('items', 'item_views.id_item', '=', 'items.id_item')
                ->whereBetween('item_views.created_at', [$desde, $hasta])
                ->selectRaw('items.id_item, items.item as nombre, items.id_categoria_item, COUNT(item_views.id) as vistas')
                ->groupBy('items.id_item', 'items.item', 'items.id_categoria_item')
                ->orderByDesc('vistas')
                ->limit(10)
                ->get(),
            'ingresos_semanal'        => $ingresosSemanal,
            'ingresos_mensual'        => $ingresosMensual,
            'actividad_provincia'     => $actividadProvincia,
            'alertas'                 => $alertas,
            'delivery_zonas'          => $deliveryStats,
            'delivery_config'         => $deliveryConfigData,
            'delivery_errors'         => $deliveryErrors,
            'categorias'              => DB::table('categorias_item')->select('id_categoria_item', 'categoria', 'aplica_impuesto')->get(),
            'filtros'                 => [
                'desde'   => $desde->format('Y-m-d'),
                'hasta'   => $hasta->format('Y-m-d'),
                'periodo' => $periodo,
            ],
            'actualizado_en' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    private function calcularRango(Request $request, string $periodo): array
    {
        if ($periodo === 'custom') {
            $desde = Carbon::parse($request->input('fecha_desde', now()->subDays(30)->toDateString()))->startOfDay();
            $hasta = Carbon::parse($request->input('fecha_hasta', now()->toDateString()))->endOfDay();
        } else {
            $dias  = match($periodo) { '7d' => 7, '90d' => 90, '365d' => 365, default => 30 };
            $desde = now()->subDays($dias)->startOfDay();
            $hasta = now()->endOfDay();
        }
        return [$desde, $hasta];
    }

    public function updateCategoriaAplicaImpuesto(Request $request, $id)
    {
        $data = $request->validate([
            'aplica_impuesto' => 'required|boolean',
        ]);

        DB::table('categorias_item')
            ->where('id_categoria_item', $id)
            ->update([
                'aplica_impuesto' => $data['aplica_impuesto'],
            ]);

        return response()->json(['success' => true]);
    }
}
