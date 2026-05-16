<?php

namespace App\Services;

use App\Models\ContCuenta;
use App\Models\ContDiario;
use App\Models\ContDiarioDetalle;
use App\Models\InventarioMovimiento;
use App\Models\Almacen;
use App\Models\PagoCompra;
use App\Models\PagoItem;
use App\Models\CajaSesion;
use App\Models\CajaTransaccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ERPService
{
    /**
     * Registra automáticamente la contabilidad y el inventario para una venta aprobada.
     */
    public function procesarVentaAprobada(PagoCompra $pagoCompra)
    {
        try {
            DB::transaction(function () use ($pagoCompra) {
                $this->generarAsientoContable($pagoCompra);
                $this->registrarMovimientosInventario($pagoCompra);
                $this->registrarTransaccionCaja($pagoCompra);
            });
        } catch (\Exception $e) {
            Log::error('Error en ERPService al procesar venta: ' . $e->getMessage());
        }
    }

    /**
     * Genera el asiento contable de la venta (Partida Doble).
     * Bancos vs Ingresos por Ventas
     */
    private function generarAsientoContable(PagoCompra $pagoCompra)
    {
        // 1. Crear encabezado del asiento
        $asiento = ContDiario::create([
            'fecha'           => now(),
            'concepto'        => "Venta de productos/servicios - Orden #{$pagoCompra->id_pago_compra}",
            'total_debe'      => $pagoCompra->total,
            'total_haber'     => $pagoCompra->total,
            'referencia_tipo' => 'pago_compra',
            'referencia_id'   => $pagoCompra->id_pago_compra,
            'estado'          => 'asentado',
            'id_usuario_crea' => auth()->id() ?? 1,
        ]);

        // 2. Buscar cuentas (Basado en el Seeder)
        $ctaBanco   = ContCuenta::where('codigo', '1.1.01.02')->first(); // Banco Operativo
        $ctaIngreso = ContCuenta::where('codigo', '4.1')->first();      // Ingresos por Ventas

        if ($ctaBanco && $ctaIngreso) {
            // DEBE: Banco (Activo aumenta)
            ContDiarioDetalle::create([
                'id_diario' => $asiento->id,
                'id_cuenta' => $ctaBanco->id,
                'debe'      => $pagoCompra->total,
                'haber'     => 0,
            ]);

            // HABER: Ingresos (Ingreso aumenta)
            ContDiarioDetalle::create([
                'id_diario' => $asiento->id,
                'id_cuenta' => $ctaIngreso->id,
                'debe'      => 0,
                'haber'     => $pagoCompra->total,
            ]);
        }
    }

    /**
     * Registra los movimientos de salida en el Kardex.
     */
    private function registrarMovimientosInventario(PagoCompra $pagoCompra)
    {
        $almacen = Almacen::first(); // Almacén central por defecto
        if (!$almacen) return;

        foreach ($pagoCompra->pagoItems as $pagoItem) {
            $item = $pagoItem->item;
            if ($item) {
                InventarioMovimiento::create([
                    'id_item'         => $item->id_item,
                    'id_almacen'      => $almacen->id,
                    'tipo'            => 'salida',
                    'cantidad'        => $pagoItem->cantidad,
                    'costo_unitario'  => 0,
                    'motivo'          => "Salida por venta - Orden #{$pagoCompra->id_pago_compra}",
                    'referencia_tipo' => 'pago_compra',
                    'referencia_id'   => $pagoCompra->id_pago_compra,
                ]);
            }
        }
    }

    /**
     * Registra el ingreso inicial al almacén cuando se publica un producto o servicio.
     */
    public function registrarEntradaRegistroItem(\App\Models\Item $item, int $cantidad)
    {
        try {
            $almacen = Almacen::first();
            if (!$almacen) return;

            InventarioMovimiento::create([
                'id_item'         => $item->id_item,
                'id_almacen'      => $almacen->id,
                'tipo'            => 'entrada',
                'cantidad'        => $cantidad,
                'costo_unitario'  => $item->valor ?? 0,
                'motivo'          => "Entrada por registro inicial de " . ($item->id_categoria_item == 29 ? 'servicio' : 'producto'),
                'referencia_tipo' => 'item',
                'referencia_id'   => $item->id_item,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ERPService al registrar entrada: ' . $e->getMessage());
        }
    }

    /**
     * Registra las salidas del almacén por concepto de intercambio (trueque).
     * Se debitan tanto el item solicitado como los items ofrecidos.
     */
    public function registrarSalidaIntercambio(\App\Models\Negociacion $neg)
    {
        try {
            DB::transaction(function () use ($neg) {
                $almacen = Almacen::first();
                if (!$almacen) return;

                // 1. Salida del item solicitado (el del receptor)
                InventarioMovimiento::create([
                    'id_item'         => $neg->receptor_item_id,
                    'id_almacen'      => $almacen->id,
                    'tipo'            => 'salida',
                    'cantidad'        => 1,
                    'motivo'          => "Salida por intercambio (Trueque) #{$neg->id_negociacion}",
                    'referencia_tipo' => 'negociacion',
                    'referencia_id'   => $neg->id_negociacion,
                ]);

                // 2. Salida de los items ofrecidos (los del emisor)
                if (!empty($neg->items_ofrecidos)) {
                    foreach ($neg->items_ofrecidos as $idItem) {
                        InventarioMovimiento::create([
                            'id_item'         => $idItem,
                            'id_almacen'      => $almacen->id,
                            'tipo'            => 'salida',
                            'cantidad'        => 1,
                            'motivo'          => "Salida por intercambio (Trueque) #{$neg->id_negociacion}",
                            'referencia_tipo' => 'negociacion',
                            'referencia_id'   => $neg->id_negociacion,
                        ]);
                        
                        // También debemos debitar el Inventario real de estos items ofrecidos
                        $inv = \App\Models\Inventario::where('id_item', $idItem)->first();
                        if ($inv && $inv->cantidad > 0) {
                            $inv->decrement('cantidad');
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            Log::error('Error en ERPService al registrar salida intercambio: ' . $e->getMessage());
        }
    }

    /**
     * Registra el ingreso en la sesión de caja abierta para Ventas.
     */
    private function registrarTransaccionCaja(PagoCompra $pagoCompra)
    {
        $sesion = CajaSesion::where('estado', 'abierta')->first();
        if (!$sesion) return;

        CajaTransaccion::create([
            'id_sesion'       => $sesion->id,
            'tipo'            => 'ingreso',
            'monto'           => $pagoCompra->total,
            'concepto'        => "Venta de productos/servicios - Orden #{$pagoCompra->id_pago_compra}",
            'referencia_tipo' => 'pago_compra',
            'referencia_id'   => $pagoCompra->id_pago_compra,
        ]);

        // Actualizar el monto esperado en la sesión
        $sesion->increment('monto_final_esperado', $pagoCompra->total);
    }

    /**
     * Procesa la contabilidad de un registro de talento aprobado.
     */
    public function procesarRegistroTalentoAprobado(\App\Models\PagoRegistroTalento $pagoTalento)
    {
        try {
            DB::transaction(function () use ($pagoTalento) {
                // Asiento Contable
                $asiento = ContDiario::create([
                    'fecha'           => now(),
                    'concepto'        => "Cobro por registro de Talento - Transacción #{$pagoTalento->transaction_id}",
                    'total_debe'      => $pagoTalento->monto_pagado,
                    'total_haber'     => $pagoTalento->monto_pagado,
                    'referencia_tipo' => 'pago_registro_talento',
                    'referencia_id'   => $pagoTalento->id,
                    'estado'          => 'asentado',
                    'id_usuario_crea' => $pagoTalento->id_user ?? auth()->id() ?? 1,
                ]);

                $ctaBanco   = ContCuenta::where('codigo', '1.1.01.02')->first(); // Banco
                $ctaIngreso = ContCuenta::where('codigo', '4.2')->first();      // Ingresos por Servicios

                if ($ctaBanco && $ctaIngreso) {
                    ContDiarioDetalle::create([
                        'id_diario' => $asiento->id, 'id_cuenta' => $ctaBanco->id,
                        'debe' => $pagoTalento->monto_pagado, 'haber' => 0,
                    ]);
                    ContDiarioDetalle::create([
                        'id_diario' => $asiento->id, 'id_cuenta' => $ctaIngreso->id,
                        'debe' => 0, 'haber' => $pagoTalento->monto_pagado,
                    ]);
                }

                // Caja
                $sesion = CajaSesion::where('estado', 'abierta')->first();
                if ($sesion) {
                    CajaTransaccion::create([
                        'id_sesion'       => $sesion->id,
                        'tipo'            => 'ingreso',
                        'monto'           => $pagoTalento->monto_pagado,
                        'concepto'        => "Registro de Talento - Tx #{$pagoTalento->transaction_id}",
                        'referencia_tipo' => 'pago_registro_talento',
                        'referencia_id'   => $pagoTalento->id,
                    ]);
                    $sesion->increment('monto_final_esperado', $pagoTalento->monto_pagado);
                }
            });
        } catch (\Exception $e) {
            Log::error('Error ERPService procesarRegistroTalentoAprobado: ' . $e->getMessage());
        }
    }

    /**
     * Procesa la contabilidad de un pago de envío aprobado.
     */
    public function procesarPagoEnvioAprobado(\App\Models\PagoEnvioIntercambio $pagoEnvio)
    {
        try {
            // El pago mediante Pull (descuento) no mueve dinero
            if ($pagoEnvio->tipo_pago !== 'tarjeta' || $pagoEnvio->monto <= 0) return;

            DB::transaction(function () use ($pagoEnvio) {
                // Asiento Contable
                $asiento = ContDiario::create([
                    'fecha'           => now(),
                    'concepto'        => "Cobro por envío de intercambio #{$pagoEnvio->id_negociacion}",
                    'total_debe'      => $pagoEnvio->monto,
                    'total_haber'     => $pagoEnvio->monto,
                    'referencia_tipo' => 'pago_envio_intercambio',
                    'referencia_id'   => $pagoEnvio->id,
                    'estado'          => 'asentado',
                    'id_usuario_crea' => $pagoEnvio->id_user ?? auth()->id() ?? 1,
                ]);

                $ctaBanco   = ContCuenta::where('codigo', '1.1.01.02')->first();
                $ctaIngreso = ContCuenta::firstOrCreate(
                    ['codigo' => '4.3'],
                    [
                        'nombre' => 'INGRESOS POR ENVÍOS',
                        'tipo' => 'ingreso',
                        'nivel' => 2,
                        'id_padre' => ContCuenta::where('codigo', '4')->value('id'),
                        'permite_movimiento' => true
                    ]
                );

                if ($ctaBanco && $ctaIngreso) {
                    ContDiarioDetalle::create([
                        'id_diario' => $asiento->id, 'id_cuenta' => $ctaBanco->id,
                        'debe' => $pagoEnvio->monto, 'haber' => 0,
                    ]);
                    ContDiarioDetalle::create([
                        'id_diario' => $asiento->id, 'id_cuenta' => $ctaIngreso->id,
                        'debe' => 0, 'haber' => $pagoEnvio->monto,
                    ]);
                }

                // Caja
                $sesion = CajaSesion::where('estado', 'abierta')->first();
                if ($sesion) {
                    CajaTransaccion::create([
                        'id_sesion'       => $sesion->id,
                        'tipo'            => 'ingreso',
                        'monto'           => $pagoEnvio->monto,
                        'concepto'        => "Envío de Intercambio #{$pagoEnvio->id_negociacion}",
                        'referencia_tipo' => 'pago_envio_intercambio',
                        'referencia_id'   => $pagoEnvio->id,
                    ]);
                    $sesion->increment('monto_final_esperado', $pagoEnvio->monto);
                }
            });
        } catch (\Exception $e) {
            Log::error('Error ERPService procesarPagoEnvioAprobado: ' . $e->getMessage());
        }
    }
}

