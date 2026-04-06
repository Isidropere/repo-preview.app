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

                    {{-- Artículo solicitado --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="font-semibold text-gray-800 mb-4">Artículo solicitado</h2>
                        @php $itemSolicitado = $intercambio->item; $imgSol = $itemSolicitado?->imagenes?->first(); @endphp
                        @if($itemSolicitado)
                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                @if($imgSol)
                                    <img src="{{ \App\Helpers\ImageHelper::urlMedia($imgSol->ruta, $imgSol->nombre) }}"
                                         alt="{{ $itemSolicitado->item }}"
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
                            <div>
                                <p class="font-semibold text-gray-800">{{ $itemSolicitado->item }}</p>
                                <p class="text-sm text-gray-500">Dueño: {{ $itemSolicitado->usuario?->nombres ?? 'N/A' }}</p>
                                @if($itemSolicitado->valor)
                                <p class="text-sm font-bold text-primary mt-1">${{ number_format($itemSolicitado->valor, 2) }}</p>
                                @endif
                            </div>
                        </div>
                        @else
                        <p class="text-sm text-gray-400">Artículo no disponible.</p>
                        @endif
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
                                    {{ $intercambio->monto_oferta ? '$' . number_format($intercambio->monto_oferta, 2) : '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Contraoferta</dt>
                                <dd class="font-semibold text-gray-800">
                                    {{ $intercambio->monto_contra_oferta ? '$' . number_format($intercambio->monto_contra_oferta, 2) : '—' }}
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

                </div>
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

        const form = document.getElementById('formEstado');
        if (form) {
            form.addEventListener('submit', function () {
                document.getElementById('btnTexto').textContent = 'Guardando...';
                document.getElementById('btnSpinner').classList.remove('hidden');
                document.getElementById('btnGuardar').disabled = true;
            });
        }
    });
</script>
@endpush

