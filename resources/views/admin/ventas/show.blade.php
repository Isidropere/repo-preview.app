@extends('layouts.app')

@section('title', 'Detalle de Venta')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        {{-- Spinner --}}
        <div id="pageLoader" class="flex flex-col items-center justify-center py-16 gap-3">
            <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
            </svg>
            <span class="text-gray-500 text-sm">Cargando...</span>
        </div>

        <div id="mainContent" class="hidden">

            <div class="mb-5">
                <a href="{{ route('admin.index', ['tab' => 'ventas']) }}"
                   class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Volver a ventas
                </a>
            </div>

            @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
            @endif

            @php
                $item = $venta->item;
                $imagen = $item?->imagenes?->first();
                $pago = $venta->carrito?->pagosCompra?->first();
                $badgeClass = match($pago?->estatus) {
                    'pendiente'  => 'bg-yellow-100 text-yellow-700',
                    'aprobado'   => 'bg-green-100 text-green-700',
                    'rechazado'  => 'bg-red-100 text-red-700',
                    'enviado'    => 'bg-blue-100 text-blue-700',
                    'entregado'  => 'bg-emerald-100 text-emerald-700',
                    'cancelado'  => 'bg-gray-100 text-gray-600',
                    default      => 'bg-gray-100 text-gray-600',
                };
            @endphp

            {{-- Encabezado --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Detalle de Venta</h1>
                        <p class="text-sm text-gray-500 mt-1">Registro #{{ $venta->id_item_intencion_compra }}</p>
                    </div>
                    @if($pago)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $badgeClass }} self-start sm:self-auto">
                        {{ ucfirst($pago->estatus ?? 'sin estado') }}
                    </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                {{-- Artículo --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">Artículo vendido</h2>
                    <div class="flex gap-4">
                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                            @if($imagen)
                                <img src="{{ \App\Helpers\ImageHelper::urlMedia($imagen->ruta, $imagen->nombre) }}"
                                     alt="{{ $item?->item }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null;this.src='/imgs/no-product.jpg'">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">{{ $item?->item ?? 'Artículo eliminado' }}</p>
                            <p class="text-sm text-gray-500 mt-1">Categoría: {{ $item?->categoria?->categoria ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500">Condición: {{ ucfirst($item?->condicion ?? 'N/A') }}</p>
                            <p class="text-lg font-bold text-primary mt-2">${{ number_format($item?->valor ?? 0, 2) }}</p>
                        </div>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm border-t border-gray-50 pt-4">
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide">Cantidad</dt>
                            <dd class="mt-1 font-medium text-gray-700">{{ $venta->cantidad }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide">Descuento</dt>
                            <dd class="mt-1 font-medium text-gray-700">{{ $venta->descuento ? $venta->descuento . '%' : 'Sin descuento' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide">Subtotal</dt>
                            <dd class="mt-1 font-bold text-gray-800">${{ number_format(($item?->valor ?? 0) * $venta->cantidad, 2) }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Vendedor --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">Vendedor</h2>
                    @if($item?->usuario)
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide">Nombre</dt>
                            <dd class="mt-1 font-medium text-gray-700">{{ $item->usuario->nombres }} {{ $item->usuario->apellidos }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide">Email</dt>
                            <dd class="mt-1 font-medium text-gray-700">{{ $item->usuario->email }}</dd>
                        </div>
                    </dl>
                    @else
                    <p class="text-sm text-gray-400">Vendedor no disponible.</p>
                    @endif

                    {{-- Pago asociado --}}
                    @if($pago)
                    <h2 class="font-semibold text-gray-800 mt-5 mb-3">Pago asociado</h2>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide">ID Pago</dt>
                            <dd class="mt-1 font-mono text-gray-700">#{{ $pago->id_pago_compra }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wide">Autorización</dt>
                            <dd class="mt-1 font-medium text-gray-700">{{ $pago->autorizacion_pago ?? 'N/A' }}</dd>
                        </div>
                        <div class="pt-2">
                            <a href="{{ route('admin.compras.show', $pago->id_pago_compra) }}"
                               class="inline-flex items-center gap-1 text-primary hover:text-hoverPrimary text-sm font-medium transition-colors">
                                Ver detalle del pago →
                            </a>
                        </div>
                    </dl>
                    @endif
                </div>

                {{-- Costo de envío estimado --}}
                @php
                    $municipioVenta = $venta->carrito?->usuario?->direcciones?->where('es_predeterminada', 1)->first()?->municipio?->municipio ?? '';
                @endphp
                @if($municipioVenta)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 lg:col-span-2">
                    <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Costo de envío estimado
                    </h2>
                    <div id="delivery-detail" class="text-sm text-gray-500">Calculando...</div>
                </div>
                @endif

                {{-- Trazabilidad del pago --}}
                @if($pago && $pago->trazabilidad->isNotEmpty())
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">Historial del pago</h2>
                    <ol class="relative border-l border-gray-200 ml-2 space-y-5">
                        @foreach($pago->trazabilidad as $traza)
                        @php
                            $dotClass = match($traza->estado_nuevo) {
                                'pendiente'  => 'bg-yellow-400',
                                'aprobado'   => 'bg-green-500',
                                'rechazado'  => 'bg-red-500',
                                'enviado'    => 'bg-blue-500',
                                'entregado'  => 'bg-emerald-500',
                                'cancelado'  => 'bg-gray-400',
                                default      => 'bg-gray-300',
                            };
                        @endphp
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-1 w-3 h-3 rounded-full border-2 border-white {{ $dotClass }}"></span>
                            <div class="text-xs text-gray-400 mb-0.5">
                                {{ $traza->created_at?->format('d/m/Y H:i') ?? '—' }}
                                @if($traza->admin)
                                &bull; <span class="font-medium text-gray-500">{{ $traza->admin->nombres }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-700">
                                @if($traza->estado_anterior)
                                <span class="font-medium">{{ ucfirst($traza->estado_anterior) }}</span>
                                <span class="text-gray-400 mx-1">&rarr;</span>
                                @endif
                                <span class="font-semibold">{{ ucfirst($traza->estado_nuevo) }}</span>
                            </p>
                            @if($traza->nota)
                            <p class="text-xs text-gray-500 mt-1 italic">"{{ $traza->nota }}"</p>
                            @endif
                        </li>
                        @endforeach
                    </ol>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('pageLoader').classList.add('hidden');
        document.getElementById('mainContent').classList.remove('hidden');

        @if($municipioVenta ?? false)
        // Calcular costo de envío
        const pueblo = @json($municipioVenta ?? '');
        const valor  = {{ $venta->carrito?->pagosCompra?->first()?->total ?? 0 }};
        if (pueblo) {
            fetch('/api/delivery/calcular?pueblo=' + encodeURIComponent(pueblo) + '&tipo_destinatario=persona&valor_articulo=' + valor)
                .then(r => r.json())
                .then(d => {
                    const el = document.getElementById('delivery-detail');
                    if (!el) return;
                    if (d.success) {
                        el.innerHTML = `
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div class="bg-blue-50 rounded-lg p-3">
                                    <div class="text-xs text-gray-500 mb-1">Zona</div>
                                    <div class="font-semibold text-gray-800">${d.zona}</div>
                                    <div class="text-xs text-gray-400 mt-1">${d.dias_entrega}</div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-xs text-gray-500 mb-1">Flete base</div>
                                    <div class="font-semibold text-gray-800">RD$ ${Number(d.desglose.costo_flete).toLocaleString('es-DO',{minimumFractionDigits:2})}</div>
                                    <div class="text-xs text-gray-400 mt-1">+${d.desglose.ganancia_negocio_pct}% ganancia</div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-xs text-gray-500 mb-1">Plataforma + Manejo</div>
                                    <div class="font-semibold text-gray-800">RD$ ${Number(d.desglose.costo_plataforma + d.desglose.costo_manejo).toLocaleString('es-DO',{minimumFractionDigits:2})}</div>
                                    <div class="text-xs text-gray-400 mt-1">${d.desglose.plataforma_pct}% + ${d.desglose.manejo_pct}%</div>
                                </div>
                                <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                    <div class="text-xs text-gray-500 mb-1">Total envío</div>
                                    <div class="font-bold text-green-700 text-lg">RD$ ${Number(d.costo_envio_total).toLocaleString('es-DO',{minimumFractionDigits:2})}</div>
                                    <div class="text-xs text-gray-400 mt-1">Seguro: RD$ ${Number(d.desglose.costo_seguro).toLocaleString('es-DO',{minimumFractionDigits:2})}</div>
                                </div>
                            </div>`;
                    } else {
                        el.innerHTML = '<span class="text-gray-400 text-sm">No se pudo calcular el costo de envío para esta dirección.</span>';
                    }
                })
                .catch(() => {
                    const el = document.getElementById('delivery-detail');
                    if (el) el.innerHTML = '<span class="text-red-400 text-sm">Error al consultar la API de delivery.</span>';
                });
        }
        @endif
    });
</script>
@endpush

