@extends('layouts.app')

@section('title', 'Detalle de Intercambio #' . $intercambio->id_negociacion)

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

        @include('components.btn-volver', ['backUrl' => route('admin.index', ['tab' => 'intercambios'])])

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
                
                $emisor = $intercambio->usuario;
                $receptor = $intercambio->usuarioReceptor;
                $itemSolicitado = $intercambio->item;

                $txtIntercambio  = "Detalle de Intercambio #{$intercambio->id_negociacion}\n";
                $txtIntercambio .= "Emisor: " . ($emisor?->nombres ?? '') . " " . ($emisor?->apellidos ?? '') . " (" . ($emisor?->email ?? 'N/A') . ")\n";
                $txtIntercambio .= "Receptor: " . ($receptor?->nombres ?? '') . " " . ($receptor?->apellidos ?? '') . " (" . ($receptor?->email ?? 'N/A') . ")\n";
                $txtIntercambio .= "Fecha de creación: " . ($intercambio->fecha_creacion ? \Carbon\Carbon::parse($intercambio->fecha_creacion)->format('d/m/Y H:i') : 'N/A') . "\n";
                $txtIntercambio .= "Estado: " . ucfirst($intercambio->estado ?? '') . "\n";
                $txtIntercambio .= "Modo de Entrega: " . ucfirst($intercambio->modo_entrega ?? 'No seleccionado') . "\n";
                
                $txtIntercambio .= "\n--- FLUJO DE INTERCAMBIO ---\n";
                $txtIntercambio .= "Artículo/Servicio Solicitado: " . ($itemSolicitado?->item ?? 'Eliminado') . " | Valor: RD$ " . number_format($itemSolicitado?->valor ?? 0, 2) . "\n";
                $txtIntercambio .= "Ofrecido por el Emisor:\n";
                if ($itemsOfrecidos->count()) {
                    foreach ($itemsOfrecidos as $io) {
                        $txtIntercambio .= "- {$io->item} | Valor: RD$ " . number_format($io->valor ?? 0, 2) . "\n";
                    }
                } else {
                    $txtIntercambio .= "- Solo oferta económica\n";
                }
                $txtIntercambio .= "Monto de oferta: RD$ " . number_format($intercambio->monto_oferta ?? 0, 2) . "\n";
                if ($intercambio->monto_contra_oferta) {
                    $txtIntercambio .= "Monto de contraoferta: RD$ " . number_format($intercambio->monto_contra_oferta ?? 0, 2) . "\n";
                }

                // Direcciones
                $dirEmisor = $emisor?->direcciones->firstWhere('es_predeterminada', 1) ?? $emisor?->direcciones->first();
                $dirReceptor = $receptor?->direcciones->firstWhere('es_predeterminada', 1) ?? $receptor?->direcciones->first();
                
                if ($dirEmisor) {
                    $txtIntercambio .= "\nDirección de Envío Emisor (Recibe el artículo solicitado):\n";
                    $txtIntercambio .= "Calle: {$dirEmisor->calle}";
                    $txtIntercambio .= $dirEmisor->N_casa_edificio ? " #{$dirEmisor->N_casa_edificio}" : '';
                    $txtIntercambio .= $dirEmisor->apto ? ", Apto {$dirEmisor->apto}" : '';
                    $txtIntercambio .= "\n";
                    $txtIntercambio .= $dirEmisor->sector ? "Sector: {$dirEmisor->sector}\n" : '';
                    $txtIntercambio .= $dirEmisor->municipio ? "Municipio: " . ($dirEmisor->municipio->municipio ?? $dirEmisor->id_municipio) . "\n" : '';
                    $txtIntercambio .= $dirEmisor->provincia ? "Provincia: " . ($dirEmisor->provincia->provincia ?? $dirEmisor->id_provincia) . "\n" : '';
                    $txtIntercambio .= $dirEmisor->telefono_contacto ? "Tel. contacto: {$dirEmisor->telefono_contacto}\n" : '';
                }

                if ($dirReceptor) {
                    $txtIntercambio .= "\nDirección de Envío Receptor (Recibe los artículos ofrecidos):\n";
                    $txtIntercambio .= "Calle: {$dirReceptor->calle}";
                    $txtIntercambio .= $dirReceptor->N_casa_edificio ? " #{$dirReceptor->N_casa_edificio}" : '';
                    $txtIntercambio .= $dirReceptor->apto ? ", Apto {$dirReceptor->apto}" : '';
                    $txtIntercambio .= "\n";
                    $txtIntercambio .= $dirReceptor->sector ? "Sector: {$dirReceptor->sector}\n" : '';
                    $txtIntercambio .= $dirReceptor->municipio ? "Municipio: " . ($dirReceptor->municipio->municipio ?? $dirReceptor->id_municipio) . "\n" : '';
                    $txtIntercambio .= $dirReceptor->provincia ? "Provincia: " . ($dirReceptor->provincia->provincia ?? $dirReceptor->id_provincia) . "\n" : '';
                    $txtIntercambio .= $dirReceptor->telefono_contacto ? "Tel. contacto: {$dirReceptor->telefono_contacto}\n" : '';
                }

                $emailSubject = "Detalle de Intercambio #{$intercambio->id_negociacion}";
                $mailtoUrl = "mailto:?subject=" . rawurlencode($emailSubject) . "&body=" . rawurlencode($txtIntercambio);
            @endphp

            {{-- Encabezado --}}
            <div id="sec-intercambio" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-gray-950 text-lg sm:text-xl">
                                Intercambio <span class="font-mono text-base">#{{ $intercambio->id_negociacion }}</span>
                            </h1>
                            <a href="{!! $mailtoUrl !!}"
                               class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Enviar por Email
                            </a>
                            {!! btnCopiar('intercambio', 'intercambio') !!}
                        </div>
                        <p class="text-sm text-gray-500">
                            {{ $intercambio->usuario?->nombres }} &rarr; {{ $intercambio->usuarioReceptor?->nombres ?? 'Sin receptor' }}
                        </p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $badgeClass }} self-start sm:self-auto">
                        {{ ucfirst($intercambio->estado ?? 'sin estado') }}
                    </span>
                </div>
            </div>

            {{-- Datos ocultos para copiar --}}
            <div id="data-intercambio" style="display:none">{{ $txtIntercambio }}</div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                {{-- Columna izquierda --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- Flujo de Intercambio (Artículos / Servicios) --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-bold text-gray-950 text-base mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            Flujo de Intercambio (Artículos / Servicios)
                        </h2>
                        
                        <div class="flex flex-col md:flex-row items-stretch gap-6 justify-between">
                            
                            {{-- LADO RECEPTOR (Artículo Solicitado) --}}
                            @php
                                $itemSolicitado = $intercambio->item;
                                $imgSol = $itemSolicitado?->imagenes?->first();
                            @endphp
                            <div class="w-full md:w-[45%] bg-purple-50/20 border border-purple-100 rounded-xl p-4 flex flex-col items-center text-center shadow-sm">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-100 text-purple-700 mb-3">
                                    {{ $itemSolicitado && $itemSolicitado->id_categoria_item == 29 ? 'Servicio Solicitado' : 'Artículo Solicitado' }}
                                </span>
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
                                <h3 class="font-bold text-gray-950 text-sm line-clamp-2 min-h-[40px]">{{ $itemSolicitado?->item ?? 'Artículo/Servicio eliminado' }}</h3>
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
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-orange-100 text-orange-700 mb-3">
                                    @if($itemsOfrecidos->count() > 1)
                                        {{ $itemsOfrecidos->contains('id_categoria_item', 29) ? 'Servicios Ofrecidos' : 'Artículos Ofrecidos' }}
                                    @else
                                        {{ $itemsOfrecidos->contains('id_categoria_item', 29) ? 'Servicio Ofrecido' : 'Artículo Ofrecido' }}
                                    @endif
                                </span>
                                @if($itemsOfrecidos->isEmpty())
                                    <div class="flex flex-col items-center justify-center h-full py-8 text-gray-400">
                                        <svg class="w-10 h-10 mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                        </svg>
                                        <p class="text-xs">Solo oferta económica</p>
                                    </div>
                                @else
                                    <div class="w-full space-y-6">
                                        @foreach($itemsOfrecidos as $io)
                                            @php $imgIo = $io->imagenes?->first(); @endphp
                                            <div class="flex flex-col items-center text-center w-full">
                                                <div class="w-24 h-24 rounded-lg overflow-hidden bg-gray-100 border border-gray-100 mb-3 shadow-sm flex-shrink-0">
                                                    @if($imgIo)
                                                        <img src="{{ \App\Helpers\ImageHelper::urlMedia($imgIo->ruta, $imgIo->nombre) }}"
                                                             alt="{{ $io->item }}"
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
                                                <h3 class="font-bold text-gray-950 text-sm line-clamp-2 min-h-[40px]">{{ $io->item }}</h3>
                                                <div class="text-[11px] text-gray-500 mt-2 space-y-0.5 text-left w-full border-t border-orange-100/50 pt-2">
                                                    <p>Dueño: <span class="font-medium text-gray-700">{{ $intercambio->usuario?->nombres ?? 'N/A' }}</span></p>
                                                    <p>Categoría: <span class="font-medium text-gray-700">{{ $io->categoria?->categoria ?? 'N/A' }}</span></p>
                                                </div>
                                                <p class="text-sm font-extrabold text-primary mt-3">RD$ {{ number_format($io->valor ?? 0, 2) }}</p>
                                            </div>
                                            @if(!$loop->last)
                                                <div class="border-t border-orange-100/30 my-4 w-full"></div>
                                            @endif
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

                    {{-- Direcciones de Envío --}}
                    @if($dirEmisor || $dirReceptor)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Direcciones de Envío
                            </h2>
                            {!! btnCopiar('direcciones', 'direcciones de envío') !!}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Emisor --}}
                            <div class="bg-orange-50/10 border border-orange-100/50 rounded-xl p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-orange-100 text-orange-700 mb-3 inline-block">Emisor (Recibe Solicitado)</span>
                                @if($dirEmisor)
                                    <dl class="space-y-2 text-xs text-gray-700">
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Calle / Dirección</dt>
                                            <dd class="font-medium mt-0.5 text-gray-900">{{ $dirEmisor->calle }}@if($dirEmisor->N_casa_edificio) #{{ $dirEmisor->N_casa_edificio }}@endif @if($dirEmisor->apto), Apto {{ $dirEmisor->apto }}@endif</dd>
                                        </div>
                                        @if($dirEmisor->sector)
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Sector</dt>
                                            <dd class="font-medium mt-0.5 text-gray-900">{{ $dirEmisor->sector }}</dd>
                                        </div>
                                        @endif
                                        <div class="grid grid-cols-2 gap-2">
                                            @if($dirEmisor->municipio)
                                            <div>
                                                <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Municipio</dt>
                                                <dd class="font-medium mt-0.5 text-gray-900">{{ $dirEmisor->municipio->municipio ?? $dirEmisor->id_municipio }}</dd>
                                            </div>
                                            @endif
                                            @if($dirEmisor->provincia)
                                            <div>
                                                <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Provincia</dt>
                                                <dd class="font-medium mt-0.5 text-gray-900">{{ $dirEmisor->provincia->provincia ?? $dirEmisor->id_provincia }}</dd>
                                            </div>
                                            @endif
                                        </div>
                                        @if($dirEmisor->telefono_contacto)
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Teléfono de contacto</dt>
                                            <dd class="font-medium mt-0.5 text-gray-900">{{ $dirEmisor->telefono_contacto }}</dd>
                                        </div>
                                        @endif
                                    </dl>
                                @else
                                    <p class="text-xs text-gray-400">Sin dirección registrada</p>
                                @endif
                            </div>

                            {{-- Receptor --}}
                            <div class="bg-purple-50/10 border border-purple-100/50 rounded-xl p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-purple-100 text-purple-700 mb-3 inline-block">Receptor (Recibe Ofrecidos)</span>
                                @if($dirReceptor)
                                    <dl class="space-y-2 text-xs text-gray-700">
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Calle / Dirección</dt>
                                            <dd class="font-medium mt-0.5 text-gray-900">{{ $dirReceptor->calle }}@if($dirReceptor->N_casa_edificio) #{{ $dirReceptor->N_casa_edificio }}@endif @if($dirReceptor->apto), Apto {{ $dirReceptor->apto }}@endif</dd>
                                        </div>
                                        @if($dirReceptor->sector)
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Sector</dt>
                                            <dd class="font-medium mt-0.5 text-gray-900">{{ $dirReceptor->sector }}</dd>
                                        </div>
                                        @endif
                                        <div class="grid grid-cols-2 gap-2">
                                            @if($dirReceptor->municipio)
                                            <div>
                                                <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Municipio</dt>
                                                <dd class="font-medium mt-0.5 text-gray-900">{{ $dirReceptor->municipio->municipio ?? $dirReceptor->id_municipio }}</dd>
                                            </div>
                                            @endif
                                            @if($dirReceptor->provincia)
                                            <div>
                                                <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Provincia</dt>
                                                <dd class="font-medium mt-0.5 text-gray-900">{{ $dirReceptor->provincia->provincia ?? $dirReceptor->id_provincia }}</dd>
                                            </div>
                                            @endif
                                        </div>
                                        @if($dirReceptor->telefono_contacto)
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Teléfono de contacto</dt>
                                            <dd class="font-medium mt-0.5 text-gray-900">{{ $dirReceptor->telefono_contacto }}</dd>
                                        </div>
                                        @endif
                                    </dl>
                                @else
                                    <p class="text-xs text-gray-400">Sin dirección registrada</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @php
                        $txtDirecciones  = "Direcciones de Envío — Intercambio #{$intercambio->id_negociacion}\n\n";
                        if ($dirEmisor) {
                            $txtDirecciones .= "[Emisor (Recibe Solicitado)]\n";
                            $txtDirecciones .= "Calle: {$dirEmisor->calle}" . ($dirEmisor->N_casa_edificio ? " #{$dirEmisor->N_casa_edificio}" : '') . ($dirEmisor->apto ? ", Apto {$dirEmisor->apto}" : '') . "\n";
                            $txtDirecciones .= "Sector: " . ($dirEmisor->sector ?? 'N/A') . "\n";
                            $txtDirecciones .= "Municipio: " . ($dirEmisor->municipio->municipio ?? $dirEmisor->id_municipio) . "\n";
                            $txtDirecciones .= "Provincia: " . ($dirEmisor->provincia->provincia ?? $dirEmisor->id_provincia) . "\n";
                            $txtDirecciones .= "Tel. contacto: " . ($dirEmisor->telefono_contacto ?? 'N/A') . "\n\n";
                        }
                        if ($dirReceptor) {
                            $txtDirecciones .= "[Receptor (Recibe Ofrecidos)]\n";
                            $txtDirecciones .= "Calle: {$dirReceptor->calle}" . ($dirReceptor->N_casa_edificio ? " #{$dirReceptor->N_casa_edificio}" : '') . ($dirReceptor->apto ? ", Apto {$dirReceptor->apto}" : '') . "\n";
                            $txtDirecciones .= "Sector: " . ($dirReceptor->sector ?? 'N/A') . "\n";
                            $txtDirecciones .= "Municipio: " . ($dirReceptor->municipio->municipio ?? $dirReceptor->id_municipio) . "\n";
                            $txtDirecciones .= "Provincia: " . ($dirReceptor->provincia->provincia ?? $dirReceptor->id_provincia) . "\n";
                            $txtDirecciones .= "Tel. contacto: " . ($dirReceptor->telefono_contacto ?? 'N/A') . "\n";
                        }
                    @endphp
                    <div id="data-direcciones" style="display:none">{{ $txtDirecciones }}</div>
                    @endif

                    {{-- Información de Pago de Envíos --}}
                    @php
                        $pagoEmisorObj = $intercambio->pagoEnvios->firstWhere('id_user', $intercambio->usuario_emisor_id);
                        $pagoReceptorObj = $intercambio->pagoEnvios->firstWhere('id_user', $intercambio->usuario_receptor_id);
                    @endphp
                    @if($pagoEmisorObj || $pagoReceptorObj)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                Información de Pago de Envíos
                            </h2>
                            {!! btnCopiar('pagos_envio', 'información de pagos') !!}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Emisor Pago --}}
                            <div class="bg-orange-50/10 border border-orange-100/50 rounded-xl p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-orange-100 text-orange-700 mb-3 inline-block">Pago del Emisor</span>
                                @if($pagoEmisorObj)
                                    <dl class="space-y-2 text-xs text-gray-700">
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Estado</dt>
                                            <dd class="font-semibold mt-0.5">
                                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] {{ $pagoEmisorObj->estado === 'pagado' || $pagoEmisorObj->estado === 'pagado_pull' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                    {{ ucfirst($pagoEmisorObj->estado) }}
                                                </span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Monto</dt>
                                            <dd class="font-semibold mt-0.5 text-gray-900 font-bold">RD$ {{ number_format($pagoEmisorObj->monto, 2) }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Método / Canal</dt>
                                            <dd class="font-medium mt-0.5 uppercase text-gray-800">{{ $pagoEmisorObj->tipo_pago === 'pull' ? 'Descuento de Pull (Servicios)' : 'Tarjeta de Crédito' }}</dd>
                                        </div>
                                        @if($pagoEmisorObj->transaction_id)
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">ID Transacción</dt>
                                            <dd class="font-mono mt-0.5 text-gray-600 break-all">{{ $pagoEmisorObj->transaction_id }}</dd>
                                        </div>
                                        @endif
                                        @if($pagoEmisorObj->approval_code)
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Autorización</dt>
                                            <dd class="font-mono font-medium mt-0.5 text-gray-800">{{ $pagoEmisorObj->approval_code }}</dd>
                                        </div>
                                        @endif
                                        @if($pagoEmisorObj->tarjeta)
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Tarjeta</dt>
                                            <dd class="font-medium mt-0.5 text-gray-800">**** {{ $pagoEmisorObj->tarjeta->last4 }} ({{ strtoupper($pagoEmisorObj->tarjeta->tipo_tarjeta) }})</dd>
                                        </div>
                                        @endif
                                    </dl>
                                @else
                                    <p class="text-xs text-gray-400">Pago pendiente / No registrado</p>
                                @endif
                            </div>

                            {{-- Receptor Pago --}}
                            <div class="bg-purple-50/10 border border-purple-100/50 rounded-xl p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-purple-100 text-purple-700 mb-3 inline-block">Pago del Receptor</span>
                                @if($pagoReceptorObj)
                                    <dl class="space-y-2 text-xs text-gray-700">
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Estado</dt>
                                            <dd class="font-semibold mt-0.5">
                                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] {{ $pagoReceptorObj->estado === 'pagado' || $pagoReceptorObj->estado === 'pagado_pull' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                    {{ ucfirst($pagoReceptorObj->estado) }}
                                                </span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Monto</dt>
                                            <dd class="font-semibold mt-0.5 text-gray-900 font-bold">RD$ {{ number_format($pagoReceptorObj->monto, 2) }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Método / Canal</dt>
                                            <dd class="font-medium mt-0.5 uppercase text-gray-800">{{ $pagoReceptorObj->tipo_pago === 'pull' ? 'Descuento de Pull (Servicios)' : 'Tarjeta de Crédito' }}</dd>
                                        </div>
                                        @if($pagoReceptorObj->transaction_id)
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">ID Transacción</dt>
                                            <dd class="font-mono mt-0.5 text-gray-600 break-all">{{ $pagoReceptorObj->transaction_id }}</dd>
                                        </div>
                                        @endif
                                        @if($pagoReceptorObj->approval_code)
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Autorización</dt>
                                            <dd class="font-mono font-medium mt-0.5 text-gray-800">{{ $pagoReceptorObj->approval_code }}</dd>
                                        </div>
                                        @endif
                                        @if($pagoReceptorObj->tarjeta)
                                        <div>
                                            <dt class="text-[10px] text-gray-400 uppercase tracking-wide">Tarjeta</dt>
                                            <dd class="font-medium mt-0.5 text-gray-800">**** {{ $pagoReceptorObj->tarjeta->last4 }} ({{ strtoupper($pagoReceptorObj->tarjeta->tipo_tarjeta) }})</dd>
                                        </div>
                                        @endif
                                    </dl>
                                @else
                                    <p class="text-xs text-gray-400">Pago pendiente / No registrado</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @php
                        $txtPagos  = "Información de Pagos — Intercambio #{$intercambio->id_negociacion}\n\n";
                        if ($pagoEmisorObj) {
                            $txtPagos .= "[Pago Emisor]\n";
                            $txtPagos .= "Estado: " . ucfirst($pagoEmisorObj->estado) . "\n";
                            $txtPagos .= "Monto: RD$ " . number_format($pagoEmisorObj->monto, 2) . "\n";
                            $txtPagos .= "Canal: " . ($pagoEmisorObj->tipo_pago === 'pull' ? 'Descuento de Pull' : 'Tarjeta') . "\n";
                            if ($pagoEmisorObj->transaction_id) $txtPagos .= "Transacción ID: {$pagoEmisorObj->transaction_id}\n";
                            if ($pagoEmisorObj->approval_code) $txtPagos .= "Autorización: {$pagoEmisorObj->approval_code}\n";
                            $txtPagos .= "\n";
                        }
                        if ($pagoReceptorObj) {
                            $txtPagos .= "[Pago Receptor]\n";
                            $txtPagos .= "Estado: " . ucfirst($pagoReceptorObj->estado) . "\n";
                            $txtPagos .= "Monto: RD$ " . number_format($pagoReceptorObj->monto, 2) . "\n";
                            $txtPagos .= "Canal: " . ($pagoReceptorObj->tipo_pago === 'pull' ? 'Descuento de Pull' : 'Tarjeta') . "\n";
                            if ($pagoReceptorObj->transaction_id) $txtPagos .= "Transacción ID: {$pagoReceptorObj->transaction_id}\n";
                            if ($pagoReceptorObj->approval_code) $txtPagos .= "Autorización: {$pagoReceptorObj->approval_code}\n";
                        }
                    @endphp
                    <div id="data-pagos_envio" style="display:none">{{ $txtPagos }}</div>
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

<div id="copyToast" style="display:none;position:fixed;bottom:24px;right:24px;background:#1f2937;color:#fff;padding:8px 16px;border-radius:8px;font-size:13px;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,.2)">
    ✓ Copiado al portapapeles
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
