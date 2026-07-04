@extends('layouts.app')

@section('title', 'Historial - Cambialord')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto lg:max-w-[1250px] md:max-w-[750px] max-w-[325px] py-8 px-4">

        {{-- Encabezado --}}
        <div class="mb-6">
            <h1 class="text-3xl text-primary font-semibold">Historial de transacciones</h1>
            <p class="text-gray-500 mt-1 text-sm">Revisa todas tus transacciones realizadas</p>
        </div>

        @if(session('success'))
        <div class="flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 rounded-2xl px-5 py-4 mb-6 shadow-sm">
            <svg class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <p class="font-semibold text-sm">Transacción completada</p>
                <p class="text-sm mt-0.5">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-600 text-xl leading-none ml-2">&#10005;</button>
        </div>
        @endif

        @if(session('error'))
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-2xl px-5 py-4 mb-6 shadow-sm">
            <svg class="h-5 w-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
                <p class="font-semibold text-sm">Error en la transacción</p>
                <p class="text-sm mt-0.5">{{ session('error') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 text-xl leading-none ml-2">&#10005;</button>
        </div>
        @endif

        @auth
        {{-- Tabs --}}
        <div class="flex gap-2 mb-6 border-b border-gray-200">
            <button onclick="mostrarTab('compras')" id="tab-compras"
                class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-primary text-primary transition-all">
                Compras
            </button>
            <button onclick="mostrarTab('ventas')" id="tab-ventas"
                class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-primary transition-all">
                Ventas
            </button>
            <button onclick="mostrarTab('intercambios')" id="tab-intercambios"
                class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-primary transition-all">
                Intercambios
            </button>
        </div>

        {{-- Spinner --}}
        <div id="historialLoader" class="hidden">
            <div class="flex flex-col items-center justify-center py-16 gap-3">
                <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
                </svg>
                <span class="text-gray-500 text-sm">Cargando...</span>
            </div>
        </div>
        {{-- Paneles de contenido --}}
        <div id="historialPaneles">

        {{-- PANEL COMPRAS --}}
        <div id="panel-compras">
            @if(isset($compras) && $compras->count())
                <div class="space-y-5">
                    @foreach($compras as $pago)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        {{-- Cabecera de la orden --}}
                        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400">Orden</span>
                                    <span class="text-xs font-mono text-gray-600">#{{ Str::limit($pago->id_pago_compra, 8, '...') }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        @if($pago->estatus === 'aprobado') bg-green-100 text-green-700
                                        @elseif($pago->estatus === 'pendiente') bg-yellow-100 text-yellow-700
                                        @elseif($pago->estatus === 'enviado') bg-blue-100 text-blue-700
                                        @elseif($pago->estatus === 'entregado') bg-emerald-100 text-emerald-700
                                        @else bg-red-100 text-red-700 @endif">
                                        {{ ucfirst($pago->estatus ?? 'desconocido') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-gray-400">
                                    <span>{{ $pago->fecha ? $pago->fecha->format('d/m/Y h:i A') : 'Fecha no disponible' }}</span>
                                    <span>{{ $pago->pagoItems->count() }} articulo(s)</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-lg font-bold text-primary">RD$ {{ number_format($pago->total ?? 0, 2) }}</span>
                                @if(in_array($pago->estatus, ['pendiente', 'aprobado']))
                                <button type="button" onclick="abrirModalDevolucion('{{ $pago->id_pago_compra }}')"
                                        class="text-xs text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 border border-red-200 px-2.5 py-1.5 rounded-lg font-medium flex items-center gap-1 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                    Solicitar Devolución
                                </button>
                                @endif
                                @if($pago->trazabilidad->count())
                                <button onclick="toggleTrazabilidad('traz-{{ $loop->index }}')"
                                    class="text-xs text-primary hover:text-hoverPrimary font-medium flex items-center gap-1 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    Seguimiento
                                </button>
                                @endif
                                @if($pago->tracking_url)
                                <a href="{{ $pago->tracking_url }}" target="_blank"
                                   class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                    Rastrear envío
                                </a>
                                @endif
                            </div>
                        </div>

                        {{-- Lista de articulos (pagoItems) --}}
                        <div class="divide-y divide-gray-50">
                            @forelse($pago->pagoItems as $pi)
                            <div class="px-5 py-3 flex items-center gap-4">                            @php
                                    $imgSrc = asset('imgs/defaults/producto_default.svg');
                                    if ($pi->imagen_url) {
                                        if (str_starts_with($pi->imagen_url, 'http')) {
                                            $imgSrc = $pi->imagen_url;
                                        } else {
                                            // Buscar en public/ directo y luego en public/storage/
                                            $parts = pathinfo($pi->imagen_url);
                                            $dir = $parts['dirname'] ?? '';
                                            $file = $parts['basename'] ?? '';
                                            $imgSrc = \App\Helpers\ImageHelper::urlMedia($dir, $file);
                                        }
                                    } elseif ($pi->item?->imagenes?->first()) {
                                        $img = $pi->item->imagenes->first();
                                        $imgSrc = \App\Helpers\ImageHelper::urlMedia($img->ruta ?? '', $img->nombre);
                                    }
                                @endphp
                                <img src="{{ $imgSrc }}"
                                     alt="{{ $pi->nombre_item }}"
                                     class="w-12 h-12 rounded-lg object-cover border border-gray-100 flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $pi->nombre_item ?? 'Articulo' }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{ $pi->cantidad }} x RD$ {{ number_format($pi->precio_unitario, 2) }}
                                        @if($pi->descuento > 0)
                                            <span class="text-green-600 ml-1">-RD$ {{ number_format($pi->descuento, 2) }}</span>
                                        @endif
                                    </p>
                                </div>
                                <span class="text-sm font-semibold text-gray-700">RD$ {{ number_format($pi->subtotal, 2) }}</span>
                            </div>
                            @empty
                            <div class="px-5 py-3 text-xs text-gray-400 italic">Sin detalle de articulos</div>
                            @endforelse
                        </div>

                        {{-- Desglose de totales si aplica --}}
                        @if((float) $pago->impuestos > 0 || (float) $pago->costo_envio > 0)
                        <div class="px-5 py-3.5 border-t border-gray-100 flex flex-col items-end text-xs text-gray-500 gap-1.5 bg-gray-50/20">
                            <div class="flex justify-between w-full max-w-[280px]">
                                <span>Subtotal de productos:</span>
                                <span class="font-medium text-gray-700">RD$ {{ number_format($pago->total - $pago->impuestos - $pago->costo_envio, 2) }}</span>
                            </div>
                            @if((float) $pago->costo_envio > 0)
                            <div class="flex justify-between w-full max-w-[280px]">
                                <span>Costo de Envío:</span>
                                <span class="font-medium text-gray-700">RD$ {{ number_format($pago->costo_envio, 2) }}</span>
                            </div>
                            @endif
                            @if((float) $pago->impuestos > 0)
                            <div class="flex justify-between w-full max-w-[280px]">
                                <span>Impuestos:</span>
                                <span class="font-medium text-gray-700">RD$ {{ number_format($pago->impuestos, 2) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between w-full max-w-[280px] border-t border-gray-200 pt-1.5 font-bold text-sm text-primary">
                                <span>Total Pagado:</span>
                                <span>RD$ {{ number_format($pago->total, 2) }}</span>
                            </div>
                        </div>
                        @endif

                        {{-- Detalles de Pago y Descarga de Factura --}}
                        <div class="px-5 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-xs text-gray-600">
                            <div>
                                <p class="font-bold text-gray-700 mb-1">Detalles del Pago</p>
                                @if($pago->tarjeta)
                                    <p class="flex items-center gap-1.5 text-gray-700 font-medium">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        {{ strtoupper($pago->tarjeta->tipo_tarjeta ?? 'Tarjeta') }} terminado en <strong>{{ $pago->tarjeta->last4 }}</strong>
                                    </p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">Titular: {{ $pago->tarjeta->nombre_titular }}</p>
                                @elseif($pago->id_tarjeta === 'REDIRECT_AZUL')
                                    @php
                                        $azulResponse = $pago->azul_response;
                                        $cardNumber = $azulResponse['CardNumber'] ?? null;
                                        $brand = $azulResponse['DataVaultBrand'] ?? $azulResponse['Brand'] ?? 'Tarjeta';
                                        $last4 = $cardNumber ? substr($cardNumber, -4) : null;
                                    @endphp
                                    <p class="flex items-center gap-1.5 text-gray-700 font-medium">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        {{ strtoupper($brand) }} @if($last4) terminado en <strong>{{ $last4 }}</strong> @else de Crédito/Débito @endif
                                    </p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">Procesado vía pasarela segura AZUL</p>
                                @else
                                    <p class="text-gray-400">Método de pago no registrado</p>
                                @endif
                                
                                @if($pago->autorizacion_pago)
                                    <p class="text-[10px] text-gray-500 mt-1">Código de Autorización: <span class="font-mono font-bold text-gray-700">{{ $pago->autorizacion_pago }}</span></p>
                                @endif
                                @if($pago->transaction_id)
                                    <p class="text-[10px] text-gray-400 font-mono">ID Transacción: {{ $pago->transaction_id }}</p>
                                @endif
                            </div>
                            <div class="w-full sm:w-auto">
                                <a href="{{ route('historial.factura', $pago->id_pago_compra) }}" 
                                   class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-white border border-gray-200 text-gray-700 hover:text-primary hover:border-primary/45 px-3.5 py-2 rounded-xl font-bold shadow-sm hover:shadow transition-all text-xs">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Descargar Factura PDF
                                </a>
                            </div>
                        </div>

                        {{-- Trazabilidad expandible --}}
                        @if($pago->trazabilidad->count())
                        <div id="traz-{{ $loop->index }}" class="hidden border-t border-gray-100 bg-gray-50 px-5 py-4">
                            <p class="text-xs font-semibold text-gray-500 mb-3">Seguimiento del pedido</p>
                            <div class="relative pl-6">
                                @foreach($pago->trazabilidad as $traza)
                                <div class="relative pb-4 {{ $loop->last ? '' : 'border-l-2 border-gray-200' }} ml-1">
                                    <div class="absolute -left-[calc(0.25rem+1px)] top-0.5 w-2.5 h-2.5 rounded-full
                                        @if($traza->estado_nuevo === 'entregado') bg-emerald-500
                                        @elseif($traza->estado_nuevo === 'enviado') bg-blue-500
                                        @elseif($traza->estado_nuevo === 'aprobado') bg-green-500
                                        @elseif($traza->estado_nuevo === 'rechazado' || $traza->estado_nuevo === 'cancelado') bg-red-500
                                        @else bg-yellow-500 @endif">
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-xs font-medium text-gray-700">
                                            {{ ucfirst($traza->estado_anterior ?? '-') }}
                                            <span class="text-gray-400 mx-1">&rarr;</span>
                                            {{ ucfirst($traza->estado_nuevo) }}
                                        </p>
                                        <p class="text-xs text-gray-400">{{ $traza->created_at?->format('d/m/Y h:i A') }}</p>
                                        @if($traza->nota)
                                        <p class="text-xs text-gray-500 mt-0.5 italic">{{ $traza->nota }}</p>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                    </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-gray-500 text-sm">No tienes compras registradas aun</p>
                    <a href="{{ route('home') }}" class="mt-4 inline-block px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-hoverPrimary transition-all">
                        Explorar productos
                    </a>
                </div>
            @endif
        </div>
        {{-- PANEL VENTAS --}}
        <div id="panel-ventas" class="hidden">
            @if(isset($ventas) && $ventas->count())
                <div class="space-y-4">
                    @foreach($ventas as $intencion)
                    @php
                        $imagen = $intencion->item?->imagenes?->where('estado','aprobado')->first();
                        $src = $imagen ? \App\Helpers\ImageHelper::urlMedia($imagen->ruta ?? '', $imagen->nombre) : asset('imgs/defaults/producto_default.svg');
                        $pagoVenta = $intencion->carrito?->pagosCompra?->first();
                        $comprador = $intencion->carrito?->usuario;
                        $trazas = $pagoVenta?->trazabilidad ?? collect();
                    @endphp
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-4">
                                <img src="{{ $src }}" alt="{{ $intencion->item?->item }}"
                                     class="w-14 h-14 rounded-lg object-cover border border-gray-100 flex-shrink-0" loading="lazy" width="56" height="56">
                                <div class="flex flex-col gap-1 min-w-0">
                                    <span class="text-sm font-medium text-gray-800 truncate">{{ $intencion->item?->item ?? 'Articulo eliminado' }}</span>
                                    <div class="flex items-center gap-3 text-xs text-gray-400">
                                        <span>Cant: {{ $intencion->cantidad }}</span>
                                        @if($intencion->item?->valor)
                                            <span class="text-primary font-semibold">RD$ {{ number_format($intencion->item->valor, 2) }}</span>
                                        @endif
                                    </div>
                                    @if($comprador)
                                    <span class="text-xs text-gray-400">Comprador: {{ $comprador->nombres }} {{ $comprador->apellidos }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    @if(($pagoVenta?->estatus) === 'aprobado') bg-green-100 text-green-700
                                    @elseif(($pagoVenta?->estatus) === 'enviado') bg-blue-100 text-blue-700
                                    @elseif(($pagoVenta?->estatus) === 'entregado') bg-emerald-100 text-emerald-700
                                    @elseif(($pagoVenta?->estatus) === 'pendiente') bg-yellow-100 text-yellow-700
                                    @else bg-gray-100 text-gray-500 @endif">
                                    {{ ucfirst($pagoVenta?->estatus ?? 'sin pago') }}
                                </span>
                                @if($trazas->count())
                                <button onclick="toggleTrazabilidad('traz-venta-{{ $loop->index }}')"
                                    class="text-xs text-primary hover:text-hoverPrimary font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    Seguimiento
                                </button>
                                @endif
                            </div>
                        </div>
                        @if($trazas->count())
                        <div id="traz-venta-{{ $loop->index }}" class="hidden border-t border-gray-100 bg-gray-50 px-5 py-4">
                            <p class="text-xs font-semibold text-gray-500 mb-3">Trazabilidad de la venta</p>
                            <div class="relative pl-6">
                                @foreach($trazas as $traza)
                                <div class="relative pb-4 {{ $loop->last ? '' : 'border-l-2 border-gray-200' }} ml-1">
                                    <div class="absolute -left-[calc(0.25rem+1px)] top-0.5 w-2.5 h-2.5 rounded-full
                                        @if($traza->estado_nuevo === 'entregado') bg-emerald-500
                                        @elseif($traza->estado_nuevo === 'enviado') bg-blue-500
                                        @elseif($traza->estado_nuevo === 'aprobado') bg-green-500
                                        @elseif($traza->estado_nuevo === 'rechazado' || $traza->estado_nuevo === 'cancelado') bg-red-500
                                        @else bg-yellow-500 @endif">
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-xs font-medium text-gray-700">
                                            {{ ucfirst($traza->estado_anterior ?? '-') }} &rarr; {{ ucfirst($traza->estado_nuevo) }}
                                        </p>
                                        <p class="text-xs text-gray-400">{{ $traza->created_at?->format('d/m/Y h:i A') }}</p>
                                        @if($traza->nota)<p class="text-xs text-gray-500 mt-0.5 italic">{{ $traza->nota }}</p>@endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-gray-500 text-sm">No tienes ventas registradas aun</p>
                    <a href="{{ route('items.create') }}" class="mt-4 inline-block px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-hoverPrimary transition-all">
                        Publicar un articulo
                    </a>
                </div>
            @endif
        </div>
        {{-- PANEL INTERCAMBIOS --}}
        <div id="panel-intercambios" class="hidden">
            @if(isset($negociaciones) && $negociaciones->count())
                <div class="space-y-4">
                    @foreach($negociaciones as $neg)
                    @php
                        $imgNeg = $neg->item?->imagenes?->where('estado','aprobado')->first();
                        $imgSrc = $imgNeg ? \App\Helpers\ImageHelper::urlMedia($imgNeg->ruta, $imgNeg->nombre) : asset('imgs/defaults/producto_default.svg');
                        $esEmisor = $neg->usuario_emisor_id === auth()->id();
                        $otraParte = $esEmisor ? $neg->usuarioReceptor : $neg->usuario;
                        $estadoColor = match($neg->estado) {
                            'aceptada', 'confirmada' => 'bg-green-100 text-green-700',
                            'Inicial', 'pendiente', 'contraoferta' => 'bg-yellow-100 text-yellow-700',
                            'rechazada', 'cancelada' => 'bg-red-100 text-red-700',
                            'completada' => 'bg-emerald-100 text-emerald-700',
                            default => 'bg-gray-100 text-gray-500',
                        };
                        $pagoEmisor = $neg->pago_emisor ?? false;
                        $pagoReceptor = $neg->pago_receptor ?? false;
                    @endphp
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-4">
                                <img src="{{ $imgSrc }}" alt="{{ $neg->item?->item }}"
                                     class="w-14 h-14 rounded-lg object-cover border border-gray-100 flex-shrink-0" loading="lazy" width="56" height="56">
                                <div class="flex flex-col gap-1 min-w-0">
                                    <span class="text-sm font-medium text-gray-800 truncate">{{ $neg->item?->item ?? 'Articulo' }}</span>
                                    <div class="flex items-center gap-2 text-xs text-gray-400">
                                        <span>#{{ $neg->id_negociacion }}</span>
                                        <span>{{ $neg->fecha_creacion ? \Carbon\Carbon::parse($neg->fecha_creacion)->format('d/m/Y') : '' }}</span>
                                    </div>
                                    <span class="text-xs text-gray-500">
                                        {{ $esEmisor ? 'Enviado a' : 'Recibido de' }}:
                                        <strong>{{ $otraParte?->nombres ?? 'Usuario' }} {{ $otraParte?->apellidos ?? '' }}</strong>
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <div class="flex items-center gap-2">
                                    @if($neg->monto_oferta)
                                        <span class="text-sm font-semibold text-primary">RD$ {{ number_format($neg->monto_oferta, 2) }}</span>
                                    @endif
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $estadoColor }}">
                                        {{ ucfirst($neg->estado ?? 'desconocido') }}
                                    </span>
                                </div>
                                <button onclick="toggleTrazabilidad('traz-neg-{{ $loop->index }}')"
                                    class="text-xs text-primary hover:text-hoverPrimary font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    Ver detalle
                                </button>
                            </div>
                        </div>
                        {{-- Detalle expandible --}}
                        <div id="traz-neg-{{ $loop->index }}" class="hidden border-t border-gray-100 bg-gray-50 px-5 py-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 mb-2">Mensaje inicial</p>
                                    <p class="text-sm text-gray-700 bg-white rounded-lg p-3 border border-gray-200">{{ $neg->mensaje_inicial ?? 'Sin mensaje' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 mb-2">Flujo del intercambio</p>
                                    <div class="relative pl-5">
                                        {{-- Paso 1: Propuesta --}}
                                        <div class="relative pb-3 border-l-2 border-gray-200 ml-1">
                                            <div class="absolute -left-[calc(0.25rem+1px)] top-0.5 w-2.5 h-2.5 rounded-full bg-blue-500"></div>
                                            <div class="ml-4">
                                                <p class="text-xs font-medium text-gray-700">Propuesta enviada</p>
                                                <p class="text-xs text-gray-400">{{ $neg->fecha_creacion ? \Carbon\Carbon::parse($neg->fecha_creacion)->format('d/m/Y h:i A') : '' }}</p>
                                            </div>
                                        </div>
                                        {{-- Paso 2: Estado actual --}}
                                        <div class="relative pb-3 {{ ($neg->estado === 'confirmada' || $neg->estado === 'completada') ? 'border-l-2 border-gray-200' : '' }} ml-1">
                                            <div class="absolute -left-[calc(0.25rem+1px)] top-0.5 w-2.5 h-2.5 rounded-full
                                                @if(in_array($neg->estado, ['aceptada','confirmada','completada'])) bg-green-500
                                                @elseif(in_array($neg->estado, ['rechazada','cancelada'])) bg-red-500
                                                @else bg-yellow-500 @endif"></div>
                                            <div class="ml-4">
                                                <p class="text-xs font-medium text-gray-700">{{ ucfirst($neg->estado) }}</p>
                                                <p class="text-xs text-gray-400">{{ $neg->updated_at?->format('d/m/Y h:i A') }}</p>
                                            </div>
                                        </div>
                                        {{-- Paso 3: Confirmaciones --}}
                                        @if(in_array($neg->estado, ['confirmada', 'completada', 'aceptada']))
                                        <div class="relative pb-3 {{ $neg->estado === 'completada' ? 'border-l-2 border-gray-200' : '' }} ml-1">
                                            <div class="absolute -left-[calc(0.25rem+1px)] top-0.5 w-2.5 h-2.5 rounded-full {{ ($neg->emisor_confirmado ?? false) && ($neg->receptor_confirmado ?? false) ? 'bg-emerald-500' : 'bg-yellow-500' }}"></div>
                                            <div class="ml-4">
                                                <p class="text-xs font-medium text-gray-700">Confirmaciones</p>
                                                <p class="text-xs text-gray-500">
                                                    Emisor: <span class="{{ ($neg->emisor_confirmado ?? false) ? 'text-green-600' : 'text-yellow-600' }}">{{ ($neg->emisor_confirmado ?? false) ? 'Confirmado' : 'Pendiente' }}</span>
                                                    &middot;
                                                    Receptor: <span class="{{ ($neg->receptor_confirmado ?? false) ? 'text-green-600' : 'text-yellow-600' }}">{{ ($neg->receptor_confirmado ?? false) ? 'Confirmado' : 'Pendiente' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                        @endif
                                        {{-- Paso 4: Pagos de envio --}}
                                        @if($pagoEmisor || $pagoReceptor)
                                        <div class="relative pb-1 ml-1">
                                            <div class="absolute -left-[calc(0.25rem+1px)] top-0.5 w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                                            <div class="ml-4">
                                                <p class="text-xs font-medium text-gray-700">Pagos de envio</p>
                                                <p class="text-xs text-gray-500">
                                                    @if($pagoEmisor) Emisor: pagado @endif
                                                    @if($pagoEmisor && $pagoReceptor) &middot; @endif
                                                    @if($pagoReceptor) Receptor: pagado @endif
                                                </p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @php
                                $miPago = $neg->pagoEnvios->first();
                            @endphp
                            @if($miPago && ($miPago->estado === 'pagado' || $miPago->estado === 'pagado_pull'))
                                <div class="mt-4 bg-emerald-50/40 border border-emerald-100 rounded-xl p-4 text-xs text-gray-600">
                                    <p class="font-bold text-emerald-800 flex items-center gap-1.5 text-sm mb-2">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Pago de Envío Realizado
                                    </p>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <div>
                                            <span class="text-gray-400">Monto del Envío:</span>
                                            <p class="font-bold text-gray-800 mt-0.5">
                                                @if($miPago->estado === 'pagado_pull')
                                                    Descontado de Pull (1 crédito)
                                                @else
                                                    RD$ {{ number_format($miPago->monto, 2) }}
                                                @endif
                                            </p>
                                        </div>
                                        @if($miPago->tarjeta)
                                        <div>
                                            <span class="text-gray-400">Método de Pago:</span>
                                            <p class="font-bold text-gray-800 mt-0.5 uppercase">
                                                {{ $miPago->tarjeta->tipo_tarjeta }} ending in {{ $miPago->tarjeta->last4 }}
                                            </p>
                                        </div>
                                        @endif
                                        @if($miPago->approval_code)
                                        <div>
                                            <span class="text-gray-400">Código de Autorización:</span>
                                            <p class="font-bold text-gray-800 mt-0.5 font-mono">
                                                {{ $miPago->approval_code }}
                                            </p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if(in_array($neg->estado, ['aceptada', 'confirmada']) && !($neg->estado === 'completada'))
                            <a href="{{ route('negociaciones.mis') }}"
                               style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.4rem 0.8rem;font-size:0.78rem;font-weight:600;color:#fff;background:#f58634;border-radius:0.5rem;text-decoration:none;">
                                Ir a Mis Intercambios
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <p class="text-gray-500 text-sm">No tienes intercambios registrados aun</p>
                    <a href="{{ route('home') }}" class="mt-4 inline-block px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-hoverPrimary transition-all">
                        Explorar articulos
                    </a>
                </div>
            @endif
        </div>

        {{-- Modal de Solicitud de Devolución --}}
        <div id="modalDevolucion" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="cerrarModalDevolucion()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                    <form id="formModalDevolucion" method="POST" onsubmit="mostrarProgreso()">
                        @csrf
                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-50 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                        Solicitar Devolución / Reembolso
                                    </h3>
                                    <div class="mt-3 text-sm text-gray-500">
                                        <p class="mb-4">Por favor, selecciona el motivo de la devolución. Esta acción es definitiva y reversará el stock del inventario.</p>
                                        
                                        <div class="mb-4">
                                            <label for="id_motivo_devolucion" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Motivo de Devolución *</label>
                                            <select name="id_motivo_devolucion" id="id_motivo_devolucion" required
                                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <option value="">-- Selecciona un motivo --</option>
                                                @foreach($motivos as $motivo)
                                                    <option value="{{ $motivo->id }}">{{ $motivo->motivo }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-2">
                                            <label for="comentario_devolucion" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Comentarios adicionales (Opcional)</label>
                                            <textarea name="comentario_devolucion" id="comentario_devolucion" rows="3"
                                                      placeholder="Escribe aquí detalles sobre la devolución..."
                                                      class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                                Enviar Solicitud
                            </button>
                            <button type="button" onclick="cerrarModalDevolucion()"
                                    class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-200 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm transition-all">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        </div> {{-- fin #historialPaneles --}}

        @else
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center">
                <p class="text-gray-500 text-sm">Debes <a href="{{ route('login') }}" class="text-primary underline">iniciar sesion</a> para ver tu historial.</p>
            </div>
        @if(isset($compraCompletada))
        {{-- Modal de Pago Completado Exitosamente --}}
        <div id="modalPagoExitoso" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" onclick="cerrarModalPagoExitoso()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-100">
                    
                    {{-- Cabecera con fondo gradiente sutil --}}
                    <div class="bg-gradient-to-r from-orange-50 to-amber-50 px-6 py-6 border-b border-orange-100/60 relative">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 h-12 w-12 rounded-2xl bg-orange-500/10 flex items-center justify-center text-orange-500 shadow-inner">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-extrabold text-slate-800" id="modal-title">
                                    ¡Compra Completada Exitosamente!
                                </h3>
                                <p class="text-xs text-slate-500 mt-1">
                                    Hemos enviado un correo electrónico con tu recibo de compra a <strong class="text-slate-700 font-semibold">{{ $compraCompletada->comprador->email ?? 'tu correo registrado' }}</strong>.
                                </p>
                            </div>
                        </div>
                        <button onclick="cerrarModalPagoExitoso()" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-full hover:bg-slate-100">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Cuerpo del modal: Vista del Recibo --}}
                    <div class="bg-white px-6 py-6 max-h-[60vh] overflow-y-auto">
                        <div class="border border-slate-100 rounded-2xl bg-slate-50/50 p-5 shadow-sm">
                            
                            {{-- Detalles de la Orden --}}
                            <div class="grid grid-cols-2 gap-4 pb-4 border-b border-slate-200/60 text-xs">
                                <div>
                                    <p class="text-slate-400 font-medium">Factura / Orden</p>
                                    <p class="font-mono font-bold text-slate-800 mt-0.5">#{{ Str::limit($compraCompletada->id_pago_compra, 8, '') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-slate-400 font-medium">Fecha y Hora</p>
                                    <p class="font-semibold text-slate-800 mt-0.5">{{ \Carbon\Carbon::parse($compraCompletada->fecha)->format('d/m/Y h:i A') }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-400 font-medium">Método de Pago</p>
                                    <p class="font-semibold text-slate-800 mt-0.5 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        @if($compraCompletada->tarjeta)
                                            {{ strtoupper($compraCompletada->tarjeta->tipo_tarjeta) }} terminado en {{ $compraCompletada->tarjeta->last4 }}
                                        @elseif($compraCompletada->id_tarjeta === 'REDIRECT_AZUL')
                                            @php
                                                $azulResp = $compraCompletada->azul_response;
                                                $cardNum = $azulResp['CardNumber'] ?? null;
                                                $cardBrand = $azulResp['DataVaultBrand'] ?? $azulResp['Brand'] ?? 'Tarjeta';
                                                $cardLast4 = $cardNum ? substr($cardNum, -4) : null;
                                            @endphp
                                            {{ strtoupper($cardBrand) }} @if($cardLast4) terminado en {{ $cardLast4 }} @else de Crédito/Débito @endif
                                        @else
                                            Tarjeta de Crédito / Débito
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-slate-400 font-medium">Código de Autorización</p>
                                    <p class="font-mono font-bold text-emerald-600 mt-0.5">{{ $compraCompletada->autorizacion_pago ?? 'N/A' }}</p>
                                </div>
                            </div>

                            {{-- Artículos --}}
                            <div class="py-4 border-b border-slate-200/60">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3">Detalle de la Compra</p>
                                <div class="space-y-3">
                                    @foreach($compraCompletada->pagoItems as $item)
                                        <div class="flex justify-between items-center text-sm">
                                            <div class="min-w-0 flex-1 pr-4">
                                                <p class="font-semibold text-slate-800 truncate">{{ $item->nombre_item }}</p>
                                                <p class="text-xs text-slate-400">
                                                    {{ $item->cantidad }} x RD$ {{ number_format($item->precio_unitario, 2) }}
                                                    @if($item->descuento > 0)
                                                        <span class="text-green-600 ml-1">-RD$ {{ number_format($item->descuento, 2) }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <span class="font-bold text-slate-700">RD$ {{ number_format($item->subtotal, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Desglose de Totales --}}
                            <div class="pt-4 space-y-2 text-xs">
                                <div class="flex justify-between text-slate-500">
                                    <span>Subtotal:</span>
                                    <span>RD$ {{ number_format($compraCompletada->total - $compraCompletada->impuestos - $compraCompletada->costo_envio, 2) }}</span>
                                </div>
                                @if((float) $compraCompletada->costo_envio > 0)
                                    <div class="flex justify-between text-slate-500">
                                        <span>Costo de Envío:</span>
                                        <span>RD$ {{ number_format($compraCompletada->costo_envio, 2) }}</span>
                                    </div>
                                @endif
                                @if((float) $compraCompletada->impuestos > 0)
                                    <div class="flex justify-between text-slate-500">
                                        <span>Impuestos:</span>
                                        <span>RD$ {{ number_format($compraCompletada->impuestos, 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between text-base font-extrabold text-primary pt-2 border-t border-slate-200">
                                    <span>Total Pagado:</span>
                                    <span>RD$ {{ number_format($compraCompletada->total, 2) }}</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Acciones del modal --}}
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 sm:flex sm:flex-row-reverse sm:justify-between gap-3">
                        <div class="flex flex-col sm:flex-row-reverse gap-2 w-full sm:w-auto">
                            <a href="{{ route('historial.factura', $compraCompletada->id_pago_compra) }}"
                               class="inline-flex justify-center items-center gap-2 rounded-2xl border border-transparent shadow-sm px-5 py-2.5 bg-primary text-sm font-bold text-white hover:bg-hoverPrimary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary w-full sm:w-auto transition-all shadow-orange-500/10 hover:shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Descargar Recibo (PDF)
                            </a>
                            <button type="button" onclick="cerrarModalPagoExitoso()"
                                    class="inline-flex justify-center items-center rounded-2xl border border-slate-200 shadow-sm px-5 py-2.5 bg-white text-sm font-bold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary w-full sm:w-auto transition-all">
                                Cerrar
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @endif
        @endauth

    </div>
</div>
@endsection

@push('scripts')
<script>
function abrirModalDevolucion(id) {
    const form = document.getElementById('formModalDevolucion');
    if (form) {
        form.action = `/historial/devolucion/${id}`;
        document.getElementById('id_motivo_devolucion').value = "";
        document.getElementById('comentario_devolucion').value = "";
        document.getElementById('modalDevolucion').classList.remove('hidden');
    }
}

function cerrarModalDevolucion() {
    document.getElementById('modalDevolucion').classList.add('hidden');
}

function mostrarProgreso() {
    document.getElementById('historialLoader').classList.remove('hidden');
    document.getElementById('historialPaneles').classList.add('hidden');
}
function ocultarProgreso() {
    document.getElementById('historialLoader').classList.add('hidden');
    document.getElementById('historialPaneles').classList.remove('hidden');
}

function mostrarTab(tab) {
    mostrarProgreso();
    setTimeout(() => {
        const tabs = ['compras', 'ventas', 'intercambios'];
        tabs.forEach(t => {
            document.getElementById('panel-' + t).classList.add('hidden');
            document.getElementById('tab-' + t).classList.remove('border-primary', 'text-primary');
            document.getElementById('tab-' + t).classList.add('border-transparent', 'text-gray-500');
        });
        document.getElementById('panel-' + tab).classList.remove('hidden');
        document.getElementById('tab-' + tab).classList.add('border-primary', 'text-primary');
        document.getElementById('tab-' + tab).classList.remove('border-transparent', 'text-gray-500');
        ocultarProgreso();
    }, 150);
}

function toggleTrazabilidad(id) {
    const el = document.getElementById(id);
    if (el) el.classList.toggle('hidden');
}

function cerrarModalPagoExitoso() {
    const modal = document.getElementById('modalPagoExitoso');
    if (modal) {
        modal.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    mostrarProgreso();
    setTimeout(function() {
        // Leer tab desde URL (?tab=ventas)
        var params = new URLSearchParams(window.location.search);
        var tab = params.get('tab');
        if (tab && ['compras', 'ventas', 'intercambios'].includes(tab)) {
            mostrarTab(tab);
        } else {
            ocultarProgreso();
        }
    }, 300);
});
</script>
@endpush