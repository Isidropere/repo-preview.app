@extends('layouts.app')

@section('title', 'Detalle de Compra')

{{-- Macro: botón copiar pequeño --}}
@php
function btnCopiar(string $id, string $label = ''): string {
    return '<button type="button" onclick="copiarSeccion(\''.$id.'\')"
        title="Copiar '.$label.'"
        style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;font-size:11px;border:1px solid #e5e7eb;border-radius:6px;background:#f9fafb;color:#6b7280;cursor:pointer;transition:all .15s"
        onmouseover="this.style.background=\'#f3f4f6\';this.style.color=\'#374151\'"
        onmouseout="this.style.background=\'#f9fafb\';this.style.color=\'#6b7280\'"
        id="btn-'.$id.'">
        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>
        Copiar
    </button>';
}
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

@include('components.btn-volver', ['backUrl' => route('admin.compras.index')])

    {{-- Spinner --}}
    <div id="pageLoader" class="flex flex-col items-center justify-center py-16 gap-3">
        <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
        </svg>
        <span class="text-gray-500 text-sm">Cargando...</span>
    </div>

    <div id="mainContent" class="hidden">

        {{-- Volver --}}
        <div class="mb-5">
            <a href="{{ route('admin.index', ['tab' => 'compras']) }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver a compras
            </a>
        </div>

        @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
        @endif

        {{-- Encabezado de la orden --}}
        @php
            $badgeClass = match($compra->estatus) {
                'pendiente' => 'bg-yellow-100 text-yellow-700',
                'aprobado'  => 'bg-green-100 text-green-700',
                'rechazado' => 'bg-red-100 text-red-700',
                'enviado'   => 'bg-blue-100 text-blue-700',
                'entregado' => 'bg-emerald-100 text-emerald-700',
                'cancelado' => 'bg-gray-100 text-gray-600',
                default     => 'bg-gray-100 text-gray-600',
            };
            $comprador = $compra->comprador;
        @endphp

        {{-- ── ENCABEZADO ── --}}
        <div id="sec-orden" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900">
                            Orden <span class="font-mono text-base">#{{ $compra->id_pago_compra }}</span>
                        </h1>
                        {!! btnCopiar('orden', 'orden') !!}
                    </div>

                    @if($comprador)
                    <div class="flex items-center gap-2 text-sm text-gray-700">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="font-medium">{{ $comprador->nombres }} {{ $comprador->apellidos ?? '' }}</span>
                        <span class="text-gray-400">&mdash;</span>
                        <span class="text-gray-500">{{ $comprador->email }}</span>
                    </div>
                    @else
                    <p class="text-sm text-gray-400">Comprador no disponible</p>
                    @endif

                    <div class="flex flex-wrap items-center gap-3 text-xs text-gray-400">
                        @if($compra->fecha)
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y H:i') }}
                        </span>
                        @endif
                        @if($compra->total)
                        <span class="font-semibold text-gray-700 text-sm">RD$ {{ number_format($compra->total, 2) }}</span>
                        @endif
                        @if($compra->cantidad_items)
                        <span>{{ $compra->cantidad_items }} artículo(s)</span>
                        @endif
                    </div>
                </div>

                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $badgeClass }} self-start">
                    {{ ucfirst($compra->estatus ?? 'sin estado') }}
                </span>
            </div>
        </div>

        {{-- Datos ocultos para copiar --}}
        @php
            $txtOrden  = "Orden #{$compra->id_pago_compra}\n";
            $txtOrden .= "Comprador: " . ($comprador?->nombres ?? '') . " " . ($comprador?->apellidos ?? '') . "\n";
            $txtOrden .= "Email: " . ($comprador?->email ?? 'N/A') . "\n";
            $txtOrden .= "Fecha: " . ($compra->fecha ? \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y H:i') : 'N/A') . "\n";
            $txtOrden .= "Estado: " . ucfirst($compra->estatus ?? '') . "\n";
            $txtOrden .= "Total: RD$ " . number_format($compra->total ?? 0, 2) . "\n";
            if ($compra->direccion) {
                $d = $compra->direccion;
                $txtOrden .= "\nDirección de envío:\n";
                $txtOrden .= "Calle: {$d->calle}";
                $txtOrden .= $d->N_casa_edificio ? " #{$d->N_casa_edificio}" : '';
                $txtOrden .= $d->apto            ? ", Apto {$d->apto}"       : '';
                $txtOrden .= "\n";
                $txtOrden .= $d->sector    ? "Sector: {$d->sector}\n"    : '';
                $txtOrden .= $d->municipio ? "Municipio: " . ($d->municipio->municipio ?? $d->id_municipio) . "\n" : '';
                $txtOrden .= $d->provincia ? "Provincia: " . ($d->provincia->provincia ?? $d->id_provincia) . "\n" : '';
                $txtOrden .= $d->telefono_contacto ? "Tel. contacto: {$d->telefono_contacto}\n" : '';
            }
        @endphp
        <div id="data-orden" style="display:none">{{ $txtOrden }}</div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- ═══ COLUMNA IZQUIERDA ═══ --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- ── ARTÍCULOS ── --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-gray-800">Artículos comprados</h2>
                            {!! btnCopiar('articulos', 'artículos') !!}
                        </div>
                        @if($compra->pagoItems->count())
                        <span class="text-xs text-gray-400">{{ $compra->pagoItems->count() }} artículo(s)</span>
                        @elseif($compra->cantidad_items)
                        <span class="text-xs text-gray-400">{{ $compra->cantidad_items }} artículo(s)</span>
                        @endif
                    </div>

                    @if($compra->pagoItems->count())
                    <ul class="divide-y divide-gray-50">
                        @foreach($compra->pagoItems as $pi)
                        <li class="flex items-center gap-4 px-5 py-4">
                            <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0 border border-gray-100">
                                @if($pi->imagen_url)
                                    <img src="{{ $pi->imagen_url }}" alt="{{ $pi->nombre_item }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.onerror=null;this.src='/imgs/no-product.jpg'">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-800 truncate">{{ $pi->nombre_item }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Precio: RD$ {{ number_format($pi->precio_unitario, 2) }}
                                    &bull; Cantidad: {{ $pi->cantidad }}
                                    @if($pi->descuento > 0)
                                    &bull; Descuento: RD$ {{ number_format($pi->descuento, 2) }}
                                    @endif
                                </p>
                                @if($pi->id_item)
                                <a href="{{ route('producto.detalle', $pi->id_item) }}" target="_blank"
                                   class="text-xs text-primary hover:underline mt-0.5 inline-block">Ver artículo →</a>
                                @endif
                            </div>
                            <p class="font-semibold text-gray-800 text-sm flex-shrink-0">
                                RD$ {{ number_format($pi->subtotal, 2) }}
                            </p>
                        </li>
                        @endforeach
                    </ul>
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-sm text-gray-500">Total de la orden</span>
                        <span class="text-lg font-bold text-gray-900">
                            RD$ {{ number_format($compra->total ?? $compra->pagoItems->sum('subtotal'), 2) }}
                        </span>
                    </div>
                    @else
                    <div class="px-5 py-10 text-center text-gray-400 text-sm">
                        <svg class="mx-auto w-10 h-10 mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                        </svg>
                        @if($compra->cantidad_items)
                        <p>{{ $compra->cantidad_items }} artículo(s) procesados</p>
                        <p class="text-xs mt-1 text-gray-300">El detalle no está disponible para órdenes anteriores</p>
                        @else
                        <p>Sin artículos registrados</p>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Datos ocultos artículos --}}
                @php
                    $txtArticulos = "Artículos — Orden #{$compra->id_pago_compra}\n";
                    foreach ($compra->pagoItems as $pi) {
                        $txtArticulos .= "- {$pi->nombre_item} | Precio: RD$ " . number_format($pi->precio_unitario,2)
                            . " | Cant: {$pi->cantidad}";
                        if ($pi->descuento > 0) {
                            $txtArticulos .= " | Desc: RD$ " . number_format($pi->descuento,2);
                        }
                        $txtArticulos .= " | Subtotal: RD$ " . number_format($pi->subtotal,2) . "\n";
                    }
                    $txtArticulos .= "Total: RD$ " . number_format($compra->total ?? $compra->pagoItems->sum('subtotal'), 2);
                @endphp
                <div id="data-articulos" style="display:none">{{ $txtArticulos }}</div>

                {{-- ── INFORMACIÓN DE PAGO ── --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <h2 class="font-semibold text-gray-800">Información de pago</h2>
                        {!! btnCopiar('pago', 'pago') !!}
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Proveedor</dt>
                            <dd class="font-medium text-gray-700">{{ $compra->proveedorPago?->proveedor_pago ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Código de autorización</dt>
                            <dd class="font-mono font-medium text-gray-700">{{ $compra->autorizacion_pago ?? 'N/A' }}</dd>
                        </div>
                        @if($compra->transaction_id)
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">ID de transacción</dt>
                            <dd class="font-mono text-xs text-gray-600 break-all">{{ $compra->transaction_id }}</dd>
                        </div>
                        @endif
                        @if($compra->tarjeta)
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Tarjeta</dt>
                            <dd class="font-medium text-gray-700 flex items-center gap-2">
                                @if($compra->tarjeta->tipo_tarjeta)
                                <span class="px-2 py-0.5 bg-gray-100 rounded text-xs font-semibold uppercase">{{ $compra->tarjeta->tipo_tarjeta }}</span>
                                @endif
                                @if($compra->tarjeta->last4)
                                <span>&bull;&bull;&bull;&bull; {{ $compra->tarjeta->last4 }}</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Titular</dt>
                            <dd class="font-medium text-gray-700">{{ $compra->tarjeta->nombre_titular ?? 'N/A' }}</dd>
                        </div>
                        @if($compra->tarjeta->banco_tarjeta)
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Banco</dt>
                            <dd class="font-medium text-gray-700">{{ $compra->tarjeta->banco_tarjeta }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Vencimiento</dt>
                            <dd class="font-medium text-gray-700">
                                @php
                                    $mes  = $compra->tarjeta->mes_expiracion;
                                    $anio = $compra->tarjeta->getAttribute(\App\Models\TarjetaPago::COL_ANIO);
                                @endphp
                                {{ $mes && $anio ? sprintf('%02d/%s', $mes, substr((string)$anio, -2)) : 'N/A' }}
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>

                {{-- Datos ocultos pago --}}
                @php
                    $txtPago  = "Información de pago — Orden #{$compra->id_pago_compra}\n";
                    $txtPago .= "Proveedor: " . ($compra->proveedorPago?->proveedor_pago ?? 'N/A') . "\n";
                    $txtPago .= "Autorización: " . ($compra->autorizacion_pago ?? 'N/A') . "\n";
                    if ($compra->transaction_id) {
                        $txtPago .= "ID Transacción: {$compra->transaction_id}\n";
                    }
                    if ($compra->tarjeta) {
                        $txtPago .= "Tarjeta: " . ($compra->tarjeta->tipo_tarjeta ?? '') . " **** " . ($compra->tarjeta->last4 ?? '') . "\n";
                        $txtPago .= "Titular: " . ($compra->tarjeta->nombre_titular ?? 'N/A') . "\n";
                        if ($compra->tarjeta->banco_tarjeta) {
                            $txtPago .= "Banco: {$compra->tarjeta->banco_tarjeta}\n";
                        }
                    }
                @endphp
                <div id="data-pago" style="display:none">{{ $txtPago }}</div>

                {{-- ── DATOS DEL COMPRADOR ── --}}
                @if($comprador)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <h2 class="font-semibold text-gray-800">Datos del comprador</h2>
                        {!! btnCopiar('comprador', 'comprador') !!}
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Nombre</dt>
                            <dd class="font-medium text-gray-700">{{ $comprador->nombres }} {{ $comprador->apellidos ?? '' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Email</dt>
                            <dd class="font-medium text-gray-700">{{ $comprador->email }}</dd>
                        </div>
                        @if($comprador->telefono ?? null)
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Teléfono</dt>
                            <dd class="font-medium text-gray-700">{{ $comprador->telefono }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
                @php
                    $txtComprador  = "Comprador — Orden #{$compra->id_pago_compra}\n";
                    $txtComprador .= "Nombre: {$comprador->nombres} " . ($comprador->apellidos ?? '') . "\n";
                    $txtComprador .= "Email: {$comprador->email}\n";
                    if ($comprador->telefono ?? null) {
                        $txtComprador .= "Teléfono: {$comprador->telefono}\n";
                    }
                @endphp
                <div id="data-comprador" style="display:none">{{ $txtComprador }}</div>
                @endif

                {{-- ── DIRECCIÓN DE ENVÍO ── --}}
                @if($compra->direccion)
                @php $dir = $compra->direccion; @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Dirección de envío
                        </h2>
                        {!! btnCopiar('direccion', 'dirección') !!}
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Calle / Dirección</dt>
                            <dd class="font-medium text-gray-700">
                                {{ $dir->calle }}
                                @if($dir->N_casa_edificio) #{{ $dir->N_casa_edificio }}@endif
                                @if($dir->apto), Apto {{ $dir->apto }}@endif
                            </dd>
                        </div>
                        @if($dir->sector)
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Sector</dt>
                            <dd class="font-medium text-gray-700">{{ $dir->sector }}</dd>
                        </div>
                        @endif
                        @if($dir->municipio)
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Municipio</dt>
                            <dd class="font-medium text-gray-700">{{ $dir->municipio->municipio ?? $dir->id_municipio }}</dd>
                        </div>
                        @endif
                        @if($dir->provincia)
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Provincia</dt>
                            <dd class="font-medium text-gray-700">{{ $dir->provincia->provincia ?? $dir->id_provincia }}</dd>
                        </div>
                        @endif
                        @if($dir->telefono_contacto)
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Teléfono de contacto</dt>
                            <dd class="font-medium text-gray-700">{{ $dir->telefono_contacto }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
                @php
                    $txtDir  = "Dirección de envío — Orden #{$compra->id_pago_compra}\n";
                    $txtDir .= "Calle: {$dir->calle}";
                    $txtDir .= $dir->N_casa_edificio ? " #{$dir->N_casa_edificio}" : '';
                    $txtDir .= $dir->apto            ? ", Apto {$dir->apto}"       : '';
                    $txtDir .= "\n";
                    $txtDir .= $dir->sector          ? "Sector: {$dir->sector}\n"  : '';
                    $txtDir .= $dir->municipio       ? "Municipio: " . ($dir->municipio->municipio ?? $dir->id_municipio) . "\n" : '';
                    $txtDir .= $dir->provincia       ? "Provincia: " . ($dir->provincia->provincia ?? $dir->id_provincia) . "\n" : '';
                    $txtDir .= $dir->telefono_contacto ? "Teléfono: {$dir->telefono_contacto}\n" : '';
                @endphp
                <div id="data-direccion" style="display:none">{{ $txtDir }}</div>
                @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h2 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Dirección de envío
                    </h2>
                    <p class="text-sm text-gray-400">No se registró dirección para esta orden.</p>
                </div>
                @endif

            </div>{{-- /col-izquierda --}}

            {{-- ═══ COLUMNA DERECHA ═══ --}}
            <div class="space-y-5">

                {{-- Actualizar estado --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">Actualizar estado</h2>
                    <form id="formEstado" method="POST"
                          action="{{ route('admin.compras.estado', $compra->id_pago_compra) }}">
                        @csrf
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Nuevo estado</label>
                                <select name="estatus"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                    @foreach($estados as $estado)
                                    <option value="{{ $estado }}" {{ $compra->estatus === $estado ? 'selected' : '' }}>
                                        {{ ucfirst($estado) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('estatus')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Nota (opcional)</label>
                                <textarea name="nota" rows="3" maxlength="500"
                                    placeholder="Agrega una nota sobre este cambio..."
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary resize-none">{{ old('nota') }}</textarea>
                            </div>
                            <button type="submit" id="btnGuardar"
                                class="w-full bg-primary hover:bg-hoverPrimary text-white py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                                <span id="btnTexto">Guardar cambio</span>
                                <svg id="btnSpinner" class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Timeline de trazabilidad --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">Historial de cambios</h2>
                    @if($compra->trazabilidad->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-4">Sin cambios registrados aún.</p>
                    @else
                    <ol class="relative border-l border-gray-200 ml-2 space-y-5">
                        @foreach($compra->trazabilidad as $traza)
                        @php
                            $dot = match($traza->estado_nuevo) {
                                'pendiente' => 'bg-yellow-400',
                                'aprobado'  => 'bg-green-500',
                                'rechazado' => 'bg-red-500',
                                'enviado'   => 'bg-blue-500',
                                'entregado' => 'bg-emerald-500',
                                'cancelado' => 'bg-gray-400',
                                default     => 'bg-gray-300',
                            };
                        @endphp
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-1 w-3 h-3 rounded-full border-2 border-white {{ $dot }}"></span>
                            <div class="text-xs text-gray-400 mb-0.5">
                                {{ $traza->created_at?->format('d/m/Y H:i') ?? '—' }}
                                @if($traza->admin)
                                &bull; <span class="font-medium text-gray-500">{{ $traza->admin->nombres }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-700">
                                @if($traza->estado_anterior && $traza->estado_anterior !== $traza->estado_nuevo)
                                <span class="text-gray-400">{{ ucfirst($traza->estado_anterior) }}</span>
                                <span class="text-gray-300 mx-1">→</span>
                                @endif
                                <span class="font-semibold">{{ ucfirst($traza->estado_nuevo) }}</span>
                            </p>
                            @if($traza->nota)
                            <p class="text-xs text-gray-500 mt-1 italic">"{{ $traza->nota }}"</p>
                            @endif
                        </li>
                        @endforeach
                    </ol>
                    @endif
                </div>

            </div>{{-- /col-derecha --}}
        </div>{{-- /grid --}}

    </div>{{-- /mainContent --}}
</div>
</div>

{{-- Toast de confirmación --}}
<div id="copyToast" style="display:none;position:fixed;bottom:24px;right:24px;background:#1f2937;color:#fff;padding:8px 16px;border-radius:8px;font-size:13px;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,.2)">
    ✓ Copiado al portapapeles
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Mostrar contenido
    document.getElementById('pageLoader').classList.add('hidden');
    document.getElementById('mainContent').classList.remove('hidden');

    // Spinner en submit
    const form = document.getElementById('formEstado');
    if (form) {
        form.addEventListener('submit', function () {
            document.getElementById('btnTexto').textContent = 'Guardando...';
            document.getElementById('btnSpinner').classList.remove('hidden');
            document.getElementById('btnGuardar').disabled = true;
        });
    }
});

function copiarSeccion(id) {
    const el = document.getElementById('data-' + id);
    if (!el) return;

    const texto = el.innerText.trim();

    navigator.clipboard.writeText(texto).then(function () {
        mostrarToast();
        // Feedback visual en el botón
        const btn = document.getElementById('btn-' + id);
        if (btn) {
            const orig = btn.innerHTML;
            btn.innerHTML = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copiado';
            btn.style.color = '#16a34a';
            btn.style.borderColor = '#bbf7d0';
            btn.style.background = '#f0fdf4';
            setTimeout(function () {
                btn.innerHTML = orig;
                btn.style.color = '';
                btn.style.borderColor = '';
                btn.style.background = '';
            }, 2000);
        }
    }).catch(function () {
        // Fallback para navegadores sin clipboard API
        const ta = document.createElement('textarea');
        ta.value = texto;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        mostrarToast();
    });
}

function mostrarToast() {
    const toast = document.getElementById('copyToast');
    toast.style.display = 'block';
    setTimeout(function () { toast.style.display = 'none'; }, 2000);
}
</script>
@endpush
