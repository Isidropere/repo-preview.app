@extends('layouts.app')
@section('title', 'Checkout')

@section('content')
<main class="min-h-screen bg-gray-100 py-10">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    @include('components.btn-volver', ['backUrl' => route('carrito.show')])

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
        <p class="text-gray-500 mt-1 text-sm">Revisa tu pedido y completa el pago de forma segura.</p>
    </div>

    @if(session('success'))
    <div class="flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 rounded-2xl px-5 py-4 mb-6 shadow-sm">
        <svg class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="flex-1">
            <p class="font-semibold text-sm">Pago exitoso</p>
            <p class="text-sm mt-0.5">{{ session('success') }}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-600 text-xl leading-none ml-2">&#x2715;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-2xl px-5 py-4 mb-6 shadow-sm">
        <svg class="h-5 w-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="flex-1">
            <p class="font-semibold text-sm">Error en el pago</p>
            <p class="text-sm mt-0.5">{{ session('error') }}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 text-xl leading-none ml-2">&#x2715;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-2xl px-5 py-4 mb-6 shadow-sm">
        <svg class="h-5 w-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="flex-1">
            <p class="font-semibold text-sm">Datos inválidos</p>
            @foreach($errors->all() as $err)
                <p class="text-sm mt-0.5">{{ $err }}</p>
            @endforeach
        </div>
        <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 text-xl leading-none ml-2">&#x2715;</button>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="lg:col-span-2 space-y-6">

            {{-- Confirmación de Pago Seguro --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-blue-600 text-white text-xs font-bold flex-shrink-0">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </span>
                    <h2 class="font-semibold text-gray-800">Confirmación y Pago Seguro</h2>
                </div>

                <div class="p-6">
                    <form id="formPago" action="{{ route('pago.redirect.iniciar') }}" method="POST">
                        @csrf

                        @if($carrito->tipo !== 'servicio')
                        @php $direccionesCount = auth()->user()->direcciones()->count(); @endphp
                        @if($direccionesCount === 0)
                        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4">
                            <p class="text-sm text-red-700 font-semibold mb-2">⚠️ No tienes una dirección de envío registrada.</p>
                            <p class="text-xs text-red-600 mb-3">Es necesaria para calcular el costo de entrega y procesar el envío.</p>
                            <a href="{{ route('direcciones.index', ['return_url' => url()->full()]) }}"
                                class="inline-flex items-center gap-2 text-sm font-bold text-red-800 hover:underline">
                                Agregar dirección de envío →
                            </a>
                        </div>
                        @endif
                        <div class="mb-5">
                            <a href="{{ route('direcciones.index', ['return_url' => url()->full()]) }}"
                                class="flex items-center gap-3 border border-gray-200 rounded-xl p-4 hover:bg-gray-50 transition group">
                                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition flex-shrink-0">
                                    <img src="/imgs/icons/EditLocation.svg" alt="" class="h-5 w-5">
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-700">Dirección de envío</p>
                                    @php
                                        $dirPredeterminada = auth()->user()->direcciones()->where('es_predeterminada', 1)->first();
                                    @endphp
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $dirPredeterminada ? "{$dirPredeterminada->calle}, #{$dirPredeterminada->N_casa_edificio} ({$dirPredeterminada->municipio?->municipio}, {$dirPredeterminada->provincia?->provincia})" : 'Seleccionar o agregar dirección de entrega' }}
                                    </p>
                                </div>
                                <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            <div id="msg-espera-admin-delivery" class="hidden mt-3 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl text-xs flex items-start gap-2.5 shadow-sm">
                                <svg class="h-4 w-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>El sistema espera por una definición para el cálculo de Análisis de costos de envío.</span>
                            </div>
                        </div>
                        @else
                        <div class="mb-5 bg-orange-50 border border-orange-200 rounded-xl p-4">
                            <p class="text-sm text-orange-700 font-medium text-center mb-3">⭐ Servicio / Talento — no requiere envío</p>

                            @if(!empty($proveedoresInfo))
                            <div style="border-top:1px solid #fed7aa;padding-top:0.75rem;">
                                <p style="font-size:0.78rem;font-weight:700;color:#92400e;margin-bottom:0.5rem;">
                                    Información del proveedor:
                                </p>

                                @foreach($proveedoresInfo as $itemId => $prov)
                                <div style="margin-bottom:0.5rem;">
                                    <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:#78350f;">
                                        <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span>
                                            <strong>{{ $prov['nombre'] }}</strong> — {{ $prov['municipio'] }}
                                        </span>
                                    </div>
                                    <div style="font-size:0.75rem;color:#92400e;margin-left:1.2rem;">
                                        @if(!empty($prov['calle']))
                                            Calle: {{ $prov['calle'] }}<br>
                                        @endif
                                        @if(!empty($prov['N_casa_edificio']))
                                            Número: {{ $prov['N_casa_edificio'] }}<br>
                                        @endif
                                        @if(!empty($prov['geolocalizacion']))
                                            <a href="https://www.google.com/maps?q={{ $prov['geolocalizacion'] }}" target="_blank" style="color:#b45309;text-decoration:underline;">Ver ubicación</a>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endif

                        <div class="mb-4">
                            <label class="flex items-start gap-2.5 cursor-pointer select-none">
                                <input type="checkbox" required class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-xs text-gray-500 leading-normal">
                                    He leído y acepto los <a href="{{ route('legal.terminos') }}" target="_blank" class="text-blue-600 hover:underline font-semibold">Términos y Condiciones (Política de Entrega)</a>, la <a href="{{ route('legal.privacidad') }}" target="_blank" class="text-blue-600 hover:underline font-semibold">Política de Privacidad</a> y la <a href="{{ route('legal.devoluciones') }}" target="_blank" class="text-blue-600 hover:underline font-semibold">Política de Devoluciones y Cancelación</a> de Cámbialo RD.
                                </span>
                            </label>
                        </div>

                        <button type="submit" id="btnPagar"
                            class="w-full bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white
                                   py-5 rounded-xl font-bold text-lg shadow-lg shadow-blue-200
                                   transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ (($carrito->tipo ?? '') !== 'servicio' && auth()->user()->direcciones()->count() === 0) ? 'disabled' : '' }}>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            {{ ($carrito->tipo ?? '') === 'servicio' ? 'Enviar Solicitud al Proveedor' : 'Pagar con AZUL' }}
                        </button>

                        <div class="flex items-center justify-center gap-1.5 mt-4 text-xs text-gray-400">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Serás redirigido de forma segura a la pasarela cifrada de AZUL
                        </div>

                        <div class="mt-4 p-3 bg-gray-50 rounded-xl space-y-2 text-[11px] text-gray-500 leading-normal">
                            <div class="flex items-start gap-2">
                                <svg class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span><strong>Seguridad de Datos de Tarjetas:</strong> No almacenamos ni compartimos la información completa de tu tarjeta de crédito o débito ni el CVV. Los datos de pago son transmitidos de forma segura y encriptada mediante protocolo <strong>TLS 1.2</strong> directamente a la pasarela de pagos de <strong>AZUL</strong>.</span>
                            </div>
                            <div class="flex items-start gap-2 border-t border-gray-200/60 pt-2">
                                <svg class="h-4 w-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span><strong>Moneda de Facturación:</strong> Todas las transacciones comerciales en este portal son procesadas y facturadas en <strong>Pesos Dominicanos (RD$ / DOP)</strong>.</span>
                            </div>
                            <div class="flex items-center justify-center gap-4 border-t border-gray-200/60 pt-3 flex-wrap">
                                <img src="/imgs/Visa_Brandmark_Blue_RGB_2021.png" alt="Visa" class="h-14 object-contain">
                                <img src="/imgs/mastercard-logo.png" alt="Mastercard" class="h-14 object-contain">
                                <img src="/imgs/visa-secure_blu_2021_dkbg.png" alt="Visa Secure" class="h-16 object-contain">
                                <img src="/imgs/mastercardidentitycheck.png" alt="Mastercard Identity Check" class="h-16 object-contain">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>{{-- /col principal --}}

        {{-- Columna lateral: Resumen --}}
        <div class="lg:col-span-1">

            {{-- Mini buscador para agregar productos --}}
            <div style="background:#fff;border-radius:1rem;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;margin-bottom:1rem;">
                <div style="padding:0.85rem 1.1rem;border-bottom:1px solid #f3f4f6;background:#f9fafb;display:flex;align-items:center;gap:0.5rem;">
                    <svg style="width:0.9rem;height:0.9rem;color:#3b82f6;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span style="font-size:0.82rem;font-weight:700;color:#111827;">Agregar más productos</span>
                </div>
                <div style="padding:0.85rem;">
                    <div style="position:relative;">
                        <input type="text" id="checkoutSearch"
                               placeholder="Buscar producto..."
                               autocomplete="off"
                               style="width:100%;padding:0.55rem 0.75rem 0.55rem 2.1rem;border:1.5px solid #e5e7eb;border-radius:0.6rem;font-size:0.8rem;outline:none;box-sizing:border-box;transition:border-color .15s;"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e5e7eb'">
                        <svg style="position:absolute;left:0.6rem;top:50%;transform:translateY(-50%);width:0.85rem;height:0.85rem;color:#9ca3af;pointer-events:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    {{-- Resultados --}}
                    <div id="checkoutSearchResults" style="display:none;margin-top:0.5rem;max-height:260px;overflow-y:auto;border:1px solid #f3f4f6;border-radius:0.6rem;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,.08);">
                    </div>
                    <div id="checkoutSearchEmpty" style="display:none;padding:0.75rem;text-align:center;font-size:0.78rem;color:#9ca3af;">
                        Sin resultados
                    </div>
                    <div id="checkoutSearchLoading" style="display:none;padding:0.75rem;text-align:center;">
                        <svg style="width:1.1rem;height:1.1rem;animation:spin .8s linear infinite;color:#3b82f6;margin:0 auto;" fill="none" viewBox="0 0 24 24">
                            <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden sticky top-6">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800">Resumen del pedido</h2>
                </div>

                <div class="p-6">
                    @php
                        $itemsSeleccionados = $carrito->itemsIntencionCompra->filter(fn($i) => $i->es_seleccionado);
                        $subtotal           = $itemsSeleccionados->sum(fn($i) => $i->item->valor * $i->cantidad);
                        $totalDescuento     = $itemsSeleccionados->sum('descuento');
                        $totalFinal         = $subtotal - $totalDescuento;
                    @endphp

                    <div class="space-y-4 mb-5 max-h-72 overflow-y-auto">
                        @forelse($itemsSeleccionados as $item)
                        <div class="flex gap-3">
                            <div class="w-14 h-14 rounded-xl overflow-hidden border border-gray-100 flex-shrink-0 bg-gray-50">
                                @if($item->imagenes->first())
                                    <img src="{{ \App\Helpers\ImageHelper::urlMedia('imgs/articulos/items', $item->imagenes->first()->nombre) }}"
                                         alt="{{ $item->item->item }}" class="w-full h-full object-cover" loading="lazy" width="56" height="56">
                                @else
                                    <img src="{{ asset('imgs/defaults/producto_default.svg') }}"
                                         alt="Sin imagen" class="w-full h-full object-cover" loading="lazy" width="56" height="56">
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 leading-tight truncate">{{ $item->item->item }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">Cant: {{ $item->cantidad }}</p>
                                <p class="text-sm font-bold text-blue-600 mt-1">${{ number_format($item->item->valor * $item->cantidad, 2) }}</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-400 text-center py-4">Sin productos seleccionados.</p>
                        @endforelse
                    </div>

                    <div class="border-t border-gray-100 pt-4 space-y-2">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Subtotal</span><span>RD$ {{ number_format($subtotal, 2) }}</span>
                        </div>
                        @if($totalDescuento > 0)
                        <div class="flex justify-between text-sm text-red-500">
                            <span>Descuento</span><span>&minus; RD$ {{ number_format($totalDescuento, 2) }}</span>
                        </div>
                        @endif
                        @if($carrito->tipo !== 'servicio')
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Envío</span>
                            <span id="envio-costo" class="font-medium text-gray-400 text-xs">Calculando...</span>
                        </div>
                        @endif
                        <div id="envio-dias" class="text-right text-xs text-gray-400 hidden"></div>
                    </div>

                    <div class="border-t-2 border-gray-100 mt-3 pt-3 flex justify-between items-center">
                        <span class="font-bold text-gray-800">Total</span>
                        <div class="text-right">
                            <p class="text-xl font-bold text-blue-600" id="total-final">RD$ {{ number_format($totalFinal, 2) }}</p>
                            <p class="text-xs text-gray-400 font-semibold text-gray-500">DOP</p>
                        </div>
                    </div>

                    {{-- Dirección permanente (Requisito AZUL en el checkout) --}}
                    <div class="border-t border-gray-100 mt-3 pt-3 text-[10px] text-gray-400 leading-normal">
                        <p class="font-semibold uppercase text-gray-500 tracking-wider mb-1">Comercio Afiliado</p>
                        <p class="font-medium text-gray-600">Cámbialo RD</p>
                        <p>Napoleón Bonaparte, Manzana T, Edificio 21, Res. Pablo Mella Morales II, Santo Domingo, República Dominicana</p>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /grid --}}
</div>
</main>

{{-- MODAL: Agregar nueva tarjeta --}}
<div id="modalTarjeta" class="fixed inset-0 bg-black/60 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">

        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-blue-100">
                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-800">Nueva tarjeta</h3>
            </div>
            <button id="btnCerrarModal" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="formAgregarTarjeta" class="px-6 py-5 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre del titular</label>
                <input type="text" name="nombre_titular" placeholder="Ej: Juan Rodríguez"
                       autocomplete="cc-name"
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm
                              focus:outline-none focus:border-blue-500 transition placeholder-gray-300">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Número de tarjeta</label>
                <input type="text" name="no_tarjeta" id="inputNoTarjeta"
                       placeholder="4111  1111  1111  1111" maxlength="19"
                       autocomplete="cc-number"
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm font-mono
                              tracking-widest focus:outline-none focus:border-blue-500 transition placeholder-gray-300">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mes</label>
                    <input type="text" name="mes_expiracion" placeholder="MM" maxlength="2"
                           autocomplete="cc-exp-month"
                           class="w-full border-2 border-gray-200 rounded-xl px-3 py-3 text-sm text-center
                                  focus:outline-none focus:border-blue-500 transition placeholder-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Año</label>
                    <input type="text" name="anio_expiracion" placeholder="YYYY" maxlength="4"
                           autocomplete="cc-exp-year"
                           class="w-full border-2 border-gray-200 rounded-xl px-3 py-3 text-sm text-center
                                  focus:outline-none focus:border-blue-500 transition placeholder-gray-300">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Banco <span class="font-normal text-gray-400">(opcional)</span></label>
                <input type="text" name="banco_tarjeta" placeholder="Ej: Banco Popular, BHD León..."
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm
                              focus:outline-none focus:border-blue-500 transition placeholder-gray-300">
            </div>

            <div class="flex gap-3 pt-1">
                <button type="button" id="btnCancelarModal"
                    class="flex-1 border-2 border-gray-200 text-gray-600 hover:bg-gray-50
                           py-3 rounded-xl text-sm font-semibold transition">
                    Cancelar
                </button>
                <button type="submit" id="btnGuardarTarjeta"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white
                           py-3 rounded-xl text-sm font-bold shadow-md shadow-blue-200 transition">
                    Guardar tarjeta
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const hiddenId  = document.getElementById('hiddenIdTarjeta');
    const modal     = document.getElementById('modalTarjeta');
    const formAgregar = document.getElementById('formAgregarTarjeta');

    // ── Modal abrir/cerrar ─────────────────────────────────
    const abrirModal  = () => modal.classList.remove('hidden');
    const cerrarModal = () => modal.classList.add('hidden');

    document.getElementById('btnAgregarTarjeta')?.addEventListener('click', abrirModal);
    document.getElementById('btnCerrarModal')?.addEventListener('click', cerrarModal);
    document.getElementById('btnCancelarModal')?.addEventListener('click', cerrarModal);
    modal.addEventListener('click', e => { if (e.target === modal) cerrarModal(); });

    // ── Formato número de tarjeta ──────────────────────────
    document.getElementById('inputNoTarjeta')?.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').substring(0, 16);
        this.value = v.replace(/(.{4})/g, '$1 ').trim();
    });

    // Re-habilitar botón si la página recargó con error o errores de validación
    @if(session('error') || $errors->any())
    const btnPagarInit = document.getElementById('btnPagar');
    if (btnPagarInit) {
        btnPagarInit.disabled = false;
        btnPagarInit.innerHTML = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg> Pagar con AZUL';
    }
    @endif

    window.deliveryCostError = false;

    // ── Envío del form de pago (Redirección a AZUL) ────────
    document.getElementById('formPago')?.addEventListener('submit', function (e) {
        if (window.deliveryCostError) {
            e.preventDefault();
            alert('Esperando por el administrador para definir el costo de envío');
            return false;
        }
        const btn = document.getElementById('btnPagar');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Redirigiendo a AZUL...';
        }
    });
    // ── Cálculo de envío ──────────────────────────────────
    @php
        $maxPeso = 0;
        $maxAlto = 0;
        $maxAncho = 0;
        $maxProfundo = 0;
        if (isset($carrito) && $carrito->itemsIntencionCompra) {
            foreach ($carrito->itemsIntencionCompra as $itemIntencion) {
                if ($itemIntencion->es_seleccionado && $itemIntencion->item) {
                    $maxPeso = max($maxPeso, (float) ($itemIntencion->item->peso_lbs ?? 0));
                    $maxAlto = max($maxAlto, (float) ($itemIntencion->item->alto_cm ?? 0));
                    $maxAncho = max($maxAncho, (float) ($itemIntencion->item->ancho_cm ?? 0));
                    $maxProfundo = max($maxProfundo, (float) ($itemIntencion->item->profundo_cm ?? 0));
                }
            }
        }
    @endphp
    (function () {
        const municipio = @json($municipioDefault ?? '');
        const subtotal  = {{ $totalFinal }};
        const maxPeso = {{ $maxPeso }};
        const maxAlto = {{ $maxAlto }};
        const maxAncho = {{ $maxAncho }};
        const maxProfundo = {{ $maxProfundo }};
        const elCosto   = document.getElementById('envio-costo');
        const elDias    = document.getElementById('envio-dias');
        const elTotal   = document.getElementById('total-final');

        function agregarDiasHabiles(desde, dias) {
            const fecha = new Date(desde);
            let agregados = 0;
            while (agregados < dias) {
                fecha.setDate(fecha.getDate() + 1);
                const dow = fecha.getDay();
                if (dow !== 0 && dow !== 6) agregados++;
            }
            return fecha;
        }

        function formatearFecha(fecha) {
            const opciones = { weekday: 'long', day: 'numeric', month: 'long' };
            return fecha.toLocaleDateString('es-DO', opciones);
        }

        if (!municipio || subtotal <= 0) {
            if (elCosto) { elCosto.textContent = 'Gratis'; elCosto.className = 'font-medium text-green-600'; }
            return;
        }

        fetch('/api/delivery/calcular?pueblo=' + encodeURIComponent(municipio) + '&valor_articulo=' + subtotal + '&peso_lbs=' + maxPeso + '&alto_cm=' + maxAlto + '&ancho_cm=' + maxAncho + '&profundo_cm=' + maxProfundo)
            .then(r => {
                return r.json().then(data => {
                    if (!r.ok) {
                        data.success = false;
                    }
                    return data;
                }).catch(() => {
                    return { success: false, error_code: 'CONNECTION_ERROR' };
                });
            })
            .then(d => {
                if (!elCosto) return;
                const costo = parseFloat(d.costo_envio_total ?? 0);
                if (d.success && costo > 0) {
                    window.deliveryCostError = false;
                    document.getElementById('msg-espera-admin-delivery')?.classList.add('hidden');
                    elCosto.textContent = 'RD$ ' + costo.toLocaleString('es-DO', {minimumFractionDigits:2});
                    elCosto.className   = 'font-medium text-gray-700';
                    if (elTotal) {
                        const nuevo = subtotal + costo;
                        elTotal.textContent = 'RD$ ' + nuevo.toLocaleString('es-DO', {minimumFractionDigits:2});
                    }
                    if (elDias && d.dias_habiles) {
                        const fechaEntrega = agregarDiasHabiles(new Date(), d.dias_habiles);
                        elDias.textContent = '🚚 Entrega estimada: ' + formatearFecha(fechaEntrega) + ' (~' + d.dias_habiles + ' días hábiles)';
                        elDias.classList.remove('hidden');
                    }
                } else if (d.error_code === 'MISSING_DELIVERY_TARIFF') {
                    window.deliveryCostError = true;
                    elCosto.textContent = 'No se pudo calcular el envío';
                    elCosto.className   = 'font-medium text-red-600';
                    document.getElementById('msg-espera-admin-delivery')?.classList.remove('hidden');
                    if (elTotal) {
                        elTotal.textContent = 'RD$ ' + subtotal.toLocaleString('es-DO', {minimumFractionDigits:2});
                    }
                    if (elDias) {
                        elDias.classList.add('hidden');
                    }
                } else {
                    window.deliveryCostError = false;
                    document.getElementById('msg-espera-admin-delivery')?.classList.add('hidden');
                    elCosto.textContent = 'Gratis';
                    elCosto.className   = 'font-medium text-green-600';
                }
            })
            .catch(() => {
                window.deliveryCostError = false;
                document.getElementById('msg-espera-admin-delivery')?.classList.add('hidden');
                if (elCosto) { elCosto.textContent = 'Gratis'; elCosto.className = 'font-medium text-green-600'; }
            });
    })();
});

// ── Buscador de productos en checkout ─────────────────
(function () {
    const input    = document.getElementById('checkoutSearch');
    const results  = document.getElementById('checkoutSearchResults');
    const empty    = document.getElementById('checkoutSearchEmpty');
    const loading  = document.getElementById('checkoutSearchLoading');
    const csrfToken = @json(csrf_token());
    const urlAgregar = @json(route('carrito.agregar'));
    const urlSearch  = '/items/search';

    if (!input) return;

    let timer = null;

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        results.style.display = 'none';
        empty.style.display   = 'none';
        loading.style.display = 'none';

        if (q.length < 2) return;

        loading.style.display = 'block';
        timer = setTimeout(() => buscar(q), 350);
    });

    async function buscar(q) {
        try {
            const res  = await fetch(`${urlSearch}?q=${encodeURIComponent(q)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            loading.style.display = 'none';
            // /items/search devuelve paginación Laravel: { data: [...] }
            const list = Array.isArray(data) ? data : (data.data ?? data.items ?? []);
            renderResults(list);
        } catch {
            loading.style.display = 'none';
        }
    }

    function renderResults(items) {
        results.innerHTML = '';
        if (!items.length) {
            empty.style.display = 'block';
            return;
        }
        items.slice(0, 8).forEach(item => {
            // La relación imagenes viene como array de objetos con {nombre, ruta}
            const imgObj = item.imagenes?.[0] ?? null;
            const imgSrc = imgObj
                ? `/storage/${imgObj.ruta}/${imgObj.nombre}`
                : null;
            const precio = item.valor
                ? `RD$ ${parseFloat(item.valor).toLocaleString('es-DO', {minimumFractionDigits:2})}`
                : 'Intercambio';
            const div = document.createElement('div');
            div.style.cssText = 'display:flex;align-items:center;gap:0.6rem;padding:0.55rem 0.75rem;border-bottom:1px solid #f9fafb;cursor:default;transition:background .12s;';
            div.onmouseover = () => div.style.background = '#f9fafb';
            div.onmouseout  = () => div.style.background = '';
            div.innerHTML = `
                <div style="width:38px;height:38px;border-radius:0.4rem;overflow:hidden;flex-shrink:0;background:#f3f4f6;border:1px solid #f1f5f9;">
                    ${imgSrc
                        ? `<img src="${imgSrc}" alt="" style="width:100%;height:100%;object-fit:cover;" onerror="this.src='/imgs/defaults/producto_default.svg'">`
                        : `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;"><svg style="width:1rem;height:1rem;color:#d1d5db;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>`
                    }
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:0.75rem;font-weight:600;color:#111827;margin:0;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">${item.item ?? item.nombre ?? ''}</p>
                    <p style="font-size:0.7rem;color:#3b82f6;font-weight:700;margin:0;">${precio}</p>
                </div>
                <button data-id="${item.id_item ?? item.id}"
                        style="flex-shrink:0;background:#3b82f6;color:#fff;border:none;border-radius:0.4rem;padding:0.3rem 0.55rem;font-size:0.7rem;font-weight:700;cursor:pointer;white-space:nowrap;transition:background .15s;"
                        onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'"
                        class="btn-add-checkout">
                    + Agregar
                </button>`;
            results.appendChild(div);
        });
        results.style.display = 'block';

        results.querySelectorAll('.btn-add-checkout').forEach(btn => {
            btn.addEventListener('click', async function () {
                const id = this.dataset.id;
                const orig = this.textContent;
                this.disabled = true;
                this.textContent = '...';
                try {
                    const resp = await fetch(urlAgregar, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ id_item: id, cantidad: 1 })
                    });
                    const data = await resp.json();
                    if (data.success) {
                        this.textContent = '✓';
                        this.style.background = '#22c55e';
                        setTimeout(() => location.reload(), 800);
                    } else {
                        alert(data.message ?? 'Error al agregar');
                        this.disabled = false;
                        this.textContent = orig;
                    }
                } catch {
                    alert('Error de red.');
                    this.disabled = false;
                    this.textContent = orig;
                }
            });
        });
    }

    // Cerrar resultados al hacer click fuera
    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !results.contains(e.target)) {
            results.style.display = 'none';
            empty.style.display   = 'none';
        }
    });
})();
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

@endsection
