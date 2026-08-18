@extends('layouts.admin')

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

            // Agregar dimensiones a la cadena de texto para el email
            $txtOrden .= "\nArtículos:\n";
            foreach($compra->pagoItems as $pi) {
                $txtOrden .= "- {$pi->nombre_item} (Cant: {$pi->cantidad})";
                if ($pi->item) {
                    $txtOrden .= " | Dim: {$pi->item->ancho_cm}x{$pi->item->alto_cm}x{$pi->item->profundo_cm} cm | Peso: {$pi->item->peso_lbs} lbs";
                }
                $txtOrden .= "\n";
            }

            $emailSubject = "Envío - Orden #{$compra->id_pago_compra}";
            $mailtoUrl = "mailto:?subject=" . rawurlencode($emailSubject) . "&body=" . rawurlencode($txtOrden);
        @endphp

        {{-- ── ENCABEZADO ── --}}
        <div id="sec-orden" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900">
                            Orden <span class="font-mono text-base">#{{ $compra->id_pago_compra }}</span>
                        </h1>
                        <button type="button" onclick="previewPdf('{{ route('admin.compras.pdf', $compra->id_pago_compra) }}')"
                           class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-medium hover:bg-gray-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Datos del envío (PDF)
                        </button>
                        <a href="{!! $mailtoUrl !!}"
                           class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Enviar por Email
                        </a>
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
                                         onerror="this.onerror=null;this.src='/imgs/defaults/producto_default.svg'">
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
                                <a href="{{ route('producto.detalle', $pi->item->slug) }}" target="_blank"
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

                {{-- ── DIRECCIONES DE LOGÍSTICA ── --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                        <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Direcciones (Recogida y Entrega)
                        </h2>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="previewPdf('{{ route('admin.compras.pdf', $compra->id_pago_compra) }}')"
                               class="inline-flex items-center gap-2 px-3 py-1 bg-primary text-white rounded-lg text-xs font-medium hover:bg-hoverPrimary transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Hoja Logística
                            </button>
                            <a href="{!! $mailtoUrl !!}"
                               class="inline-flex items-center gap-2 px-3 py-1 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Enviar Email
                            </a>
                        </div>
                    </div>

                    @php
                        $primerItem = $compra->pagoItems->first();
                        $dirRec = null;
                        if ($primerItem && $primerItem->item && $primerItem->item->usuario) {
                            $dirRec = $primerItem->item->usuario->direcciones->first();
                        }
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Punto de Recogida --}}
                        <div>
                            <h3 class="font-medium text-sm text-gray-700 mb-2 border-b pb-1">Punto de Recogida (Vendedor)</h3>
                            @if($dirRec)
                            <dl class="text-sm space-y-2">
                                <div>
                                    <dt class="text-xs text-gray-400 uppercase tracking-wide">Calle / Dirección</dt>
                                    <dd class="font-medium text-gray-700">{{ $dirRec->calle }} @if($dirRec->N_casa_edificio) #{{ $dirRec->N_casa_edificio }}@endif @if($dirRec->apto), Apto {{ $dirRec->apto }}@endif</dd>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <dt class="text-xs text-gray-400 uppercase tracking-wide">Sector</dt>
                                        <dd class="font-medium text-gray-700">{{ $dirRec->sector ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-gray-400 uppercase tracking-wide">Municipio</dt>
                                        <dd class="font-medium text-gray-700">{{ $dirRec->municipio->municipio ?? $dirRec->id_municipio ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-gray-400 uppercase tracking-wide">Provincia</dt>
                                        <dd class="font-medium text-gray-700">{{ $dirRec->provincia->provincia ?? $dirRec->id_provincia ?? 'N/A' }}</dd>
                                    </div>
                                </div>
                            </dl>
                            @else
                            <p class="text-sm text-gray-500 italic">No hay dirección de recogida registrada.</p>
                            @endif
                        </div>

                        {{-- Punto de Entrega --}}
                        <div>
                            <h3 class="font-medium text-sm text-gray-700 mb-2 border-b pb-1">Punto de Entrega (Comprador)</h3>
                            @if($compra->direccion)
                            @php $dir = $compra->direccion; @endphp
                            <dl class="text-sm space-y-2">
                                <div>
                                    <dt class="text-xs text-gray-400 uppercase tracking-wide">Calle / Dirección</dt>
                                    <dd class="font-medium text-gray-700">{{ $dir->calle }} @if($dir->N_casa_edificio) #{{ $dir->N_casa_edificio }}@endif @if($dir->apto), Apto {{ $dir->apto }}@endif</dd>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <dt class="text-xs text-gray-400 uppercase tracking-wide">Sector</dt>
                                        <dd class="font-medium text-gray-700">{{ $dir->sector ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-gray-400 uppercase tracking-wide">Municipio</dt>
                                        <dd class="font-medium text-gray-700">{{ $dir->municipio->municipio ?? $dir->id_municipio }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-gray-400 uppercase tracking-wide">Provincia</dt>
                                        <dd class="font-medium text-gray-700">{{ $dir->provincia->provincia ?? $dir->id_provincia }}</dd>
                                    </div>
                                    @if($dir->telefono_contacto)
                                    <div>
                                        <dt class="text-xs text-gray-400 uppercase tracking-wide">Tel. Contacto</dt>
                                        <dd class="font-medium text-gray-700">{{ $dir->telefono_contacto }}</dd>
                                    </div>
                                    @endif
                                </div>
                            </dl>
                            @else
                            <p class="text-sm text-gray-500 italic">No hay dirección de entrega registrada.</p>
                            @endif
                        </div>
                    </div>
                </div>

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

                {{-- Enviar tracking --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-gray-800">Envío y rastreo</h2>
                        @if($compra->tracking_url)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Enviado
                        </span>
                        @endif
                    </div>

                    @if($compra->tracking_url)
                    <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-100 text-sm">
                        <p class="text-xs text-blue-500 mb-1">Código de rastreo</p>
                        <p class="font-mono font-semibold text-blue-800">{{ $compra->tracking_code }}</p>
                        <a href="{{ $compra->tracking_url }}" target="_blank"
                           class="text-xs text-blue-600 hover:underline mt-1 inline-block break-all">
                            {{ $compra->tracking_url }}
                        </a>
                    </div>
                    @endif

                    <button type="button" onclick="document.getElementById('modalTracking').classList.remove('hidden')"
                        class="w-full border border-primary text-primary hover:bg-primary hover:text-white py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        {{ $compra->tracking_url ? 'Actualizar tracking' : 'Enviar tracking' }}
                    </button>

                    {{-- Botón Notificaciones --}}
                    @if($comprador)
                    <button type="button" onclick="document.getElementById('modalNotificacion').classList.remove('hidden')"
                        class="w-full mt-3 bg-secondary hover:bg-hoverSecondary text-white py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Notificar al usuario
                    </button>
                    @endif
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

{{-- Modal de tracking --}}
<div id="modalTracking" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,.45)">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Enviar información de rastreo</h3>
            <button type="button" onclick="document.getElementById('modalTracking').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.compras.tracking', $compra->id_pago_compra) }}"
              id="formTracking">
            @csrf
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado de la orden</label>
                    <select name="estatus" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        @foreach($estados as $estado)
                        <option value="{{ $estado }}" {{ $compra->estatus === $estado ? 'selected' : '' }}>
                            {{ ucfirst($estado) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Código de rastreo</label>
                    <input type="text" name="tracking_code" required maxlength="100"
                           value="{{ $compra->tracking_code }}"
                           placeholder="Ej: 1Z999AA10123456784"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary">
                    <p class="text-xs text-gray-400 mt-1">Este código se añadirá a la URL base de rastreo.</p>
                </div>
                @if($compra->comprador)
                <p class="text-xs text-gray-400">
                    Se enviará una notificación a
                    <span class="font-medium text-gray-600">{{ $compra->comprador->nombres }} ({{ $compra->comprador->email }})</span>.
                </p>
                @endif
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('modalTracking').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="btnTracking"
                    class="px-5 py-2 text-sm font-medium bg-primary hover:bg-hoverPrimary text-white rounded-lg transition-colors flex items-center gap-2">
                    <span id="btnTrackingTexto">Enviar tracking</span>
                    <svg id="btnTrackingSpinner" class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal de Notificación --}}
@if($comprador)
<div id="modalNotificacion" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,.45)">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Enviar notificación directa</h3>
            <button type="button" onclick="document.getElementById('modalNotificacion').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ url('/admin/notificaciones/enviar') }}">
            @csrf
            <input type="hidden" name="destino" value="usuario">
            <input type="hidden" name="usuario_id" value="{{ $comprador->id }}">
            <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
            
            <div class="px-6 py-5 space-y-4">
                {{-- Info Usuario --}}
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Destinatario</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $comprador->nombres }} {{ $comprador->apellidos ?? '' }}</p>
                    <p class="text-xs text-gray-500">{{ $comprador->email }}</p>
                </div>

                {{-- Tipo --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Tipo de notificación</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach([
                            'compra' => ['💳', 'Compra'],
                            'general' => ['📢', 'General'],
                        ] as $key => [$icon, $label])
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="tipos[]" value="{{ $key }}" {{ $key === 'compra' ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-secondary focus:ring-secondary/30">
                            <span class="text-xs text-gray-700">{{ $icon }} {{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Canales de envío --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Enviar vía</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="canales[]" value="web" checked class="w-4 h-4 rounded border-gray-300 text-secondary focus:ring-secondary/30">
                            <span class="text-xs text-gray-700">Notificación Web/Móvil</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="canales[]" value="email" checked class="w-4 h-4 rounded border-gray-300 text-secondary focus:ring-secondary/30">
                            <span class="text-xs text-gray-700">Correo Electrónico</span>
                        </label>
                    </div>
                </div>

                {{-- Mensaje --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Mensaje</label>
                    <textarea name="mensaje" rows="4" required maxlength="500" 
                              placeholder="Escribe el mensaje para el usuario..."
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary resize-none"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('modalNotificacion').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-5 py-2 text-sm font-medium bg-secondary hover:bg-hoverSecondary text-white rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Enviar notificación
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@include('admin.partials.pdf_modal')
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

    // Spinner tracking form
    const formTracking = document.getElementById('formTracking');
    if (formTracking) {
        formTracking.addEventListener('submit', function () {
            document.getElementById('btnTrackingTexto').textContent = 'Enviando...';
            document.getElementById('btnTrackingSpinner').classList.remove('hidden');
            document.getElementById('btnTracking').disabled = true;
        });
    }

    // Cerrar modal al hacer click fuera
    document.getElementById('modalTracking')?.addEventListener('click', function (e) {
        if (e.target === this) this.classList.add('hidden');
    });
    document.getElementById('modalNotificacion')?.addEventListener('click', function (e) {
        if (e.target === this) this.classList.add('hidden');
    });
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
