@extends('layouts.app')

@section('title', 'Detalle de Venta')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

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
                $compra = $venta; // Para compatibilidad con los bloques de compras.show
                $item = $venta->item;
                $imagen = $item?->imagenes?->first();
                $comprador = $venta->comprador;
                $badgeClass = match($venta->estatus) {
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
                        <p class="text-sm text-gray-500 mt-1">Orden <span class="font-mono">#{{ $venta->id_pago_compra }}</span></p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $badgeClass }} self-start sm:self-auto">
                        {{ ucfirst($venta->estatus ?? 'sin estado') }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                {{-- ═══ COLUMNA IZQUIERDA ═══ --}}
                <div class="lg:col-span-2 space-y-5">
                    {{-- Artículo --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="font-semibold text-gray-800 mb-4">Artículo vendido</h2>
                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                @if($imagen)
                                    <img src="{{ \App\Helpers\ImageHelper::urlMedia($imagen->ruta, $imagen->nombre) }}"
                                         alt="{{ $item?->item }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.onerror=null;this.src='/imgs/defaults/producto_default.svg'">
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
                                <p class="text-lg font-bold text-primary mt-2">RD$ {{ number_format($item?->valor ?? 0, 2) }}</p>
                            </div>
                        </div>
                        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm border-t border-gray-50 pt-4">
                            <div>
                                <dt class="text-xs text-gray-400 uppercase tracking-wide">Cantidad</dt>
                                <dd class="mt-1 font-medium text-gray-700">{{ $venta->pagoItems->first()?->cantidad ?? 1 }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-400 uppercase tracking-wide">Subtotal</dt>
                                <dd class="mt-1 font-bold text-gray-800">RD$ {{ number_format(($item?->valor ?? 0) * ($venta->pagoItems->first()?->cantidad ?? 1), 2) }}</dd>
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
                    </div>

                    {{-- Pago asociado --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="font-semibold text-gray-800 mb-3">Información de pago</h2>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">ID Pago</dt>
                                <dd class="mt-1 font-mono text-gray-700">#{{ $venta->id_pago_compra }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Autorización</dt>
                                <dd class="mt-1 font-medium text-gray-700">{{ $venta->autorizacion_pago ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">Proveedor</dt>
                                <dd class="mt-1 font-medium text-gray-700">{{ $venta->proveedorPago?->proveedor_pago ?? 'N/A' }}</dd>
                            </div>
                            @if($venta->transaction_id)
                            <div>
                                <dt class="text-xs text-gray-400 uppercase tracking-wide mb-1">ID Transacción</dt>
                                <dd class="mt-1 font-mono text-xs text-gray-600 break-all">{{ $venta->transaction_id }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Costo de envío estimado --}}
                    @php
                        $municipioVenta = $venta->comprador?->direcciones?->where('es_predeterminada', 1)->first()?->municipio?->municipio ?? '';
                    @endphp
                    @if($municipioVenta)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Costo de envío estimado
                        </h2>
                        <div id="delivery-detail" class="text-sm text-gray-500">Calculando...</div>
                    </div>
                    @endif
                </div>

                {{-- ═══ COLUMNA DERECHA ═══ --}}
                <div class="space-y-5">
                    {{-- Actualizar estado --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="font-semibold text-gray-800 mb-4">Actualizar estado</h2>
                        <form id="formEstado" method="POST"
                              action="{{ route('admin.compras.estado', $venta->id_pago_compra) }}">
                            @csrf
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Nuevo estado</label>
                                    <select name="estatus"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                        @foreach($estados as $estado)
                                        <option value="{{ $estado }}" {{ $venta->estatus === $estado ? 'selected' : '' }}>
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
                            @if($venta->tracking_url)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Enviado
                            </span>
                            @endif
                        </div>

                        @if($venta->tracking_url)
                        <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-100 text-sm">
                            <p class="text-xs text-blue-500 mb-1">Código de rastreo</p>
                            <p class="font-mono font-semibold text-blue-800">{{ $venta->tracking_code }}</p>
                            <a href="{{ $venta->tracking_url }}" target="_blank"
                               class="text-xs text-blue-600 hover:underline mt-1 inline-block break-all">
                                {{ $venta->tracking_url }}
                            </a>
                        </div>
                        @endif

                        <button type="button" onclick="document.getElementById('modalTracking').classList.remove('hidden')"
                            class="w-full border border-primary text-primary hover:bg-primary hover:text-white py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            {{ $venta->tracking_url ? 'Actualizar tracking' : 'Enviar tracking' }}
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
                        @if($venta->trazabilidad->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-4">Sin cambios registrados aún.</p>
                        @else
                        <ol class="relative border-l border-gray-200 ml-2 space-y-5">
                            @foreach($venta->trazabilidad as $traza)
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
                </div>

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
        <form method="POST" action="{{ route('admin.compras.tracking', $venta->id_pago_compra) }}"
              id="formTracking">
            @csrf
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado de la orden</label>
                    <select name="estatus" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        @foreach($estados as $estado)
                        <option value="{{ $estado }}" {{ $estado === 'enviado' && !$venta->tracking_url ? 'selected' : ($venta->estatus === $estado && $venta->tracking_url ? 'selected' : '') }}>
                            {{ ucfirst($estado) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Código de rastreo</label>
                    <input type="text" name="tracking_code" required maxlength="100"
                           value="{{ $venta->tracking_code }}"
                           placeholder="Ej: 1Z999AA10123456784"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary">
                    <p class="text-xs text-gray-400 mt-1">Este código se añadirá a la URL base de rastreo.</p>
                </div>
                @if($comprador)
                <p class="text-xs text-gray-400">
                    Se enviará una notificación a
                    <span class="font-medium text-gray-600">{{ $comprador->nombres }} ({{ $comprador->email }})</span>.
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

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
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

        @if($municipioVenta ?? false)
        // Calcular costo de envío
        const pueblo = @json($municipioVenta ?? '');
        const valor  = {{ $venta->total ?? 0 }};
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
                                    <div class="font-semibold text-gray-800">RD$ ${new Intl.NumberFormat('es-DO', { minimumFractionDigits: 2 }).format(d.desglose.costo_flete)}</div>
                                    <div class="text-xs text-gray-400 mt-1">+${d.desglose.ganancia_negocio_pct}% ganancia</div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-xs text-gray-500 mb-1">Plataforma + Manejo</div>
                                    <div class="font-semibold text-gray-800">RD$ ${new Intl.NumberFormat('es-DO', { minimumFractionDigits: 2 }).format(d.desglose.costo_plataforma + d.desglose.costo_manejo)}</div>
                                    <div class="text-xs text-gray-400 mt-1">${d.desglose.plataforma_pct}% + ${d.desglose.manejo_pct}%</div>
                                </div>
                                <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                    <div class="text-xs text-gray-500 mb-1">Total envío</div>
                                    <div class="font-bold text-green-700 text-lg">RD$ ${new Intl.NumberFormat('es-DO', { minimumFractionDigits: 2 }).format(d.costo_envio_total)}</div>
                                    <div class="text-xs text-gray-400 mt-1">Seguro: RD$ ${new Intl.NumberFormat('es-DO', { minimumFractionDigits: 2 }).format(d.desglose.costo_seguro)}</div>
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
