@extends('layouts.app')

@section('title', 'Detalle de Intercambio #' . $intercambio->id_negociacion)

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
                <a href="{{ route('admin.index', ['tab' => 'intercambios']) }}"
                   class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Volver a intercambios
                </a>
            </div>

            @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
            @endif

            @php
                $badgeClass = match($intercambio->estado) {
                    'pendiente'    => 'bg-yellow-100 text-yellow-700',
                    'aceptado'     => 'bg-green-100 text-green-700',
                    'rechazado'    => 'bg-red-100 text-red-700',
                    'contraoferta' => 'bg-orange-100 text-orange-700',
                    'en_envio'     => 'bg-blue-100 text-blue-700',
                    'completado'   => 'bg-emerald-100 text-emerald-700',
                    'cancelado'    => 'bg-gray-100 text-gray-600',
                    default        => 'bg-gray-100 text-gray-600',
                };
            @endphp

            {{-- Encabezado --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">
                            Intercambio <span class="font-mono">#{{ $intercambio->id_negociacion }}</span>
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $intercambio->usuario?->nombres }} &rarr; {{ $intercambio->usuarioReceptor?->nombres ?? 'Sin receptor' }}
                        </p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $badgeClass }} self-start sm:self-auto">
                        {{ ucfirst($intercambio->estado ?? 'sin estado') }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                {{-- Columna izquierda --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- Flujo de Intercambio (Items vs Items) --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-bold text-gray-950 text-base mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            Flujo de Intercambio (Items vs Items)
                        </h2>
                        
                        <div class="flex flex-col md:flex-row items-stretch gap-6 justify-between">
                            
                            {{-- LADO RECEPTOR (Artículo Solicitado) --}}
                            @php
                                $itemSolicitado = $intercambio->item;
                                $imgSol = $itemSolicitado?->imagenes?->first();
                            @endphp
                            <div class="w-full md:w-[45%] bg-purple-50/20 border border-purple-100 rounded-xl p-4 flex flex-col items-center text-center shadow-sm">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-100 text-purple-700 mb-3">Artículo Solicitado</span>
                                <div class="w-24 h-24 rounded-lg overflow-hidden bg-gray-100 border border-gray-100 mb-3 shadow-sm flex-shrink-0">
                                    @if($imgSol)
                                        <img src="{{ \App\Helpers\ImageHelper::urlMedia($imgSol->ruta, $imgSol->nombre) }}"
                                             alt="{{ $itemSolicitado?->item }}"
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
                                <h3 class="font-bold text-gray-950 text-sm line-clamp-2 min-h-[40px]">{{ $itemSolicitado?->item ?? 'Artículo eliminado' }}</h3>
                                <div class="text-[11px] text-gray-500 mt-2 space-y-0.5 text-left w-full border-t border-purple-100/50 pt-2">
                                    <p>Dueño: <span class="font-medium text-gray-700">{{ $intercambio->usuarioReceptor?->nombres ?? 'N/A' }}</span></p>
                                    <p>Categoría: <span class="font-medium text-gray-700">{{ $itemSolicitado?->categoria?->categoria ?? 'N/A' }}</span></p>
                                </div>
                                <p class="text-sm font-extrabold text-primary mt-3">RD$ {{ number_format($itemSolicitado?->valor ?? 0, 2) }}</p>
                            </div>

                            {{-- INDICADOR DE FLUJO CENTRAL --}}
                            <div class="flex flex-col items-center justify-center gap-1.5 flex-shrink-0 self-center">
                                <div class="bg-primary/10 text-primary p-3 rounded-full shadow-sm animate-pulse">
                                    <svg class="w-6 h-6 rotate-90 md:rotate-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                </div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Contra</span>
                            </div>

                            {{-- LADO EMISOR (Artículos Ofrecidos) --}}
                            <div class="w-full md:w-[45%] bg-orange-50/20 border border-orange-100 rounded-xl p-4 flex flex-col items-center text-center shadow-sm">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-orange-100 text-orange-700 mb-3">Artículos Ofrecidos</span>
                                @if($itemsOfrecidos->isEmpty())
                                    <div class="flex flex-col items-center justify-center h-full py-8 text-gray-400">
                                        <svg class="w-10 h-10 mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                        </svg>
                                        <p class="text-xs">Solo oferta económica</p>
                                    </div>
                                @else
                                    <div class="w-full space-y-4">
                                        @foreach($itemsOfrecidos as $io)
                                            @php $imgIo = $io->imagenes?->first(); @endphp
                                            <div class="flex items-center gap-3 border-b border-orange-100/30 pb-3 last:border-b-0 last:pb-0 text-left">
                                                <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0 border border-gray-100">
                                                    @if($imgIo)
                                                        <img src="{{ \App\Helpers\ImageHelper::urlMedia($imgIo->ruta, $imgIo->nombre) }}"
                                                             alt="{{ $io->item }}"
                                                             class="w-full h-full object-cover"
                                                             onerror="this.onerror=null;this.src='/imgs/defaults/producto_default.svg'">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="font-bold text-gray-950 text-xs truncate">{{ $io->item }}</h4>
                                                    <p class="text-[10px] text-gray-500">Dueño: <span class="font-medium text-gray-700">{{ $intercambio->usuario?->nombres ?? 'N/A' }}</span></p>
                                                    <p class="text-[11px] font-bold text-primary mt-0.5">RD$ {{ number_format($io->valor ?? 0, 2) }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>

                    {{-- Mensaje inicial --}}
                    @if($intercambio->mensaje_inicial)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="font-semibold text-gray-800 mb-2">Mensaje inicial</h2>
                        <p class="text-sm text-gray-600 italic">"{{ $intercambio->mensaje_inicial }}"</p>
                    </div>
                    @endif

                </div>

                {{-- Columna derecha: usuarios + actualizar estado --}}
                <div class="space-y-5">

                    {{-- Emisor --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="font-semibold text-gray-800 mb-3">Emisor</h2>
                        @if($intercambio->usuario)
                        <p class="font-medium text-gray-800">{{ $intercambio->usuario->nombres }} {{ $intercambio->usuario->apellidos }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $intercambio->usuario->email }}</p>
                        @else
                        <p class="text-sm text-gray-400">Sin datos</p>
                        @endif
                    </div>

                    {{-- Receptor --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="font-semibold text-gray-800 mb-3">Receptor</h2>
                        @if($intercambio->usuarioReceptor)
                        <p class="font-medium text-gray-800">{{ $intercambio->usuarioReceptor->nombres }} {{ $intercambio->usuarioReceptor->apellidos }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $intercambio->usuarioReceptor->email }}</p>
                        @else
                        <p class="text-sm text-gray-400">Sin datos</p>
                        @endif
                    </div>

                    {{-- Montos --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="font-semibold text-gray-800 mb-3">Montos</h2>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Oferta</dt>
                                <dd class="font-semibold text-gray-800">
                                    {{ $intercambio->monto_oferta ? 'RD$ ' . number_format($intercambio->monto_oferta, 2) : '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Contraoferta</dt>
                                <dd class="font-semibold text-gray-800">
                                    {{ $intercambio->monto_contra_oferta ? 'RD$ ' . number_format($intercambio->monto_contra_oferta, 2) : '—' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Actualizar estado --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="font-semibold text-gray-800 mb-4">Actualizar Estado</h2>
                        <form id="formEstado" method="POST"
                              action="{{ route('admin.intercambios.estado', $intercambio->id_negociacion) }}">
                            @csrf
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Nuevo estado</label>
                                    <select name="estado"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        @foreach($estados as $e)
                                        <option value="{{ $e }}" {{ $intercambio->estado === $e ? 'selected' : '' }}>
                                            {{ ucfirst($e) }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('estado')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Nota (opcional)</label>
                                    <textarea name="nota" rows="3" maxlength="500"
                                              placeholder="Agrega una nota..."
                                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none">{{ old('nota') }}</textarea>
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
                            @if($intercambio->tracking_url)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Enviado
                            </span>
                            @endif
                        </div>

                        @if($intercambio->tracking_url)
                        <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-100 text-sm">
                            <p class="text-xs text-blue-500 mb-1">Código de rastreo</p>
                            <p class="font-mono font-semibold text-blue-800">{{ $intercambio->tracking_code }}</p>
                            <a href="{{ $intercambio->tracking_url }}" target="_blank"
                               class="text-xs text-blue-600 hover:underline mt-1 inline-block break-all">
                                {{ $intercambio->tracking_url }}
                            </a>
                        </div>
                        @endif

                        <button type="button" onclick="document.getElementById('modalTracking').classList.remove('hidden')"
                            class="w-full border border-primary text-primary hover:bg-primary hover:text-white py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            {{ $intercambio->tracking_url ? 'Actualizar tracking' : 'Enviar tracking' }}
                        </button>

                        {{-- Botón Notificaciones --}}
                        <button type="button" onclick="document.getElementById('modalNotificacion').classList.remove('hidden')"
                            class="w-full mt-3 bg-secondary hover:bg-hoverSecondary text-white py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            Notificar a un usuario
                        </button>
                    </div>

                    {{-- Timeline de trazabilidad --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="font-semibold text-gray-800 mb-4">Historial de cambios</h2>
                        @if($intercambio->trazabilidad->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-4">Sin cambios registrados aún.</p>
                        @else
                        <ol class="relative border-l border-gray-200 ml-2 space-y-5">
                            @foreach($intercambio->trazabilidad as $traza)
                            @php
                                $dot = match($traza->estado_nuevo) {
                                    'pendiente'    => 'bg-yellow-400',
                                    'aceptado'     => 'bg-green-500',
                                    'rechazado'    => 'bg-red-500',
                                    'contraoferta' => 'bg-orange-400',
                                    'en_envio'     => 'bg-blue-500',
                                    'completado'   => 'bg-emerald-500',
                                    'cancelado'    => 'bg-gray-400',
                                    default        => 'bg-gray-300',
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
            </div>
        </div>
    </div>
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
        <form method="POST" action="{{ route('admin.intercambios.tracking', $intercambio->id_negociacion) }}"
              id="formTracking">
            @csrf
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado del intercambio</label>
                    <select name="estado" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        @foreach($estados as $estado)
                        <option value="{{ $estado }}" {{ $estado === 'en_envio' && !$intercambio->tracking_url ? 'selected' : ($intercambio->estado === $estado && $intercambio->tracking_url ? 'selected' : '') }}>
                            {{ ucfirst($estado) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Código de rastreo</label>
                    <input type="text" name="tracking_code" required maxlength="100"
                           value="{{ $intercambio->tracking_code }}"
                           placeholder="Ej: 1Z999AA10123456784"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary">
                    <p class="text-xs text-gray-400 mt-1">Este código se añadirá a la URL base de rastreo.</p>
                </div>
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
            <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
            
            <div class="px-6 py-5 space-y-4">
                {{-- Destinatario --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Destinatario</label>
                    <select name="usuario_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary">
                        @if($intercambio->usuario)
                        <option value="{{ $intercambio->usuario->id }}">Emisor: {{ $intercambio->usuario->nombres }} ({{ $intercambio->usuario->email }})</option>
                        @endif
                        @if($intercambio->usuarioReceptor)
                        <option value="{{ $intercambio->usuarioReceptor->id }}">Receptor: {{ $intercambio->usuarioReceptor->nombres }} ({{ $intercambio->usuarioReceptor->email }})</option>
                        @endif
                    </select>
                </div>

                {{-- Tipo --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Tipo de notificación</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach([
                            'intercambio' => ['🤝', 'Intercambio'],
                            'general' => ['📢', 'General'],
                        ] as $key => [$icon, $label])
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="tipos[]" value="{{ $key }}" {{ $key === 'intercambio' ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-secondary focus:ring-secondary/30">
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

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('pageLoader').classList.add('hidden');
        document.getElementById('mainContent').classList.remove('hidden');

        // Spinner en submit de actualizar estado
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
</script>
@endpush
