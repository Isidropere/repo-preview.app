@extends('layouts.app')
@section('title', 'Solicitar Transporte y Mudanza')

@push('head_styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map-container { height: 350px; width: 100%; border-radius: 12px; z-index: 1; border: 1px solid #e5e7eb; }
    .form-input { padding: 11px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem; outline: none; transition: all 0.2s ease-in-out; box-sizing: border-box; width: 100%; background-color: #fff; }
    .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
    .form-label { display: flex; align-items: center; gap: 4px; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .btn-gps { background: #0ea5e9; color: #fff; border: 1px solid transparent; border-radius: 8px; padding: 11px 20px; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; white-space: nowrap; height: auto; box-sizing: border-box; }
    .btn-gps:hover { background: #0284c7; }
    .btn-gps:disabled { background: #94a3b8; cursor: not-allowed; }
    .btn-active-map { ring: 2px solid #0ea5e9; background: #0284c7; }
    .geo-wrapper { position: relative; }
    .geo-suggestions { position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; background: #fff; border: 1px solid #d1d5db; border-top: none; border-radius: 0 0 8px 8px; max-height: 200px; overflow-y: auto; box-shadow: 0 8px 25px rgba(0,0,0,.12); display: none; }
    .geo-suggestions.active { display: block; }
    .geo-suggestion-item { padding: 10px 14px; font-size: 0.85rem; color: #374151; cursor: pointer; border-bottom: 1px solid #f3f4f6; display: flex; align-items: flex-start; gap: 8px; }
    .geo-suggestion-item:last-child { border-bottom: none; }
    .geo-suggestion-item:hover { background: #eff6ff; color: #1d4ed8; }
    .geo-suggestion-item svg { flex-shrink: 0; margin-top: 2px; }
    .geo-loading { padding: 12px 14px; font-size: 0.8rem; color: #9ca3af; text-align: center; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                <svg class="mr-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al inicio
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mt-4">Solicitud de Transporte y Mudanza</h1>
            <p class="text-gray-600 mt-2">Por favor, completa los datos de tu solicitud para que podamos enviarte un presupuesto o aprobar el servicio.</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 mb-8">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-8">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('transporte.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @csrf
            <div class="p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Información del Solicitante</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="form-label">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" value="{{ old('nombre', Auth::check() ? Auth::user()->nombres : '') }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Apellido <span class="text-red-500">*</span></label>
                        <input type="text" name="apellido" value="{{ old('apellido', Auth::check() ? Auth::user()->apellidos : '') }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Cédula <span class="text-red-500">*</span></label>
                        <input type="text" name="cedula" value="{{ old('cedula', Auth::check() ? Auth::user()->cedula : '') }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Teléfono <span class="text-red-500">*</span></label>
                        <input type="text" name="telefono" value="{{ old('telefono', Auth::check() ? Auth::user()->telefono : '') }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Correo Electrónico <span class="text-red-500">*</span></label>
                        <input type="email" name="correo" value="{{ old('correo', Auth::check() ? Auth::user()->email : '') }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Fecha del Servicio <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_servicio" value="{{ old('fecha_servicio') }}" required min="{{ date('Y-m-d') }}" class="form-input">
                    </div>
                </div>

                <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Detalles del Servicio</h2>
                <div class="grid grid-cols-1 gap-6 mb-8">
                    <div>
                        <label class="form-label">Tipo de Servicio <span class="text-red-500">*</span></label>
                        <select name="tipo_servicio" id="tipo_servicio" required class="form-input">
                            <option value="">-- Selecciona si es Transporte o Mudanza --</option>
                            <option value="transporte" {{ old('tipo_servicio') == 'transporte' ? 'selected' : '' }}>Transporte de Carga / Mercancía</option>
                            <option value="mudanza" {{ old('tipo_servicio') == 'mudanza' ? 'selected' : '' }}>Mudanza Residencial o Comercial</option>
                        </select>
                    </div>

                    <!-- Checklist Dinámico de Artículos -->
                    <div id="checklist-articulos-container" class="mt-2 hidden">
                        <label class="form-label text-gray-800 mb-2">Selecciona los artículos que deseas transportar / mudar <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mb-4 font-medium">Marca las casillas correspondientes y ajusta la cantidad de cada artículo.</p>
                        
                        <!-- Buscador de Artículos -->
                        <div class="mb-4">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                                <input type="text" id="search-articulos" placeholder="Buscar artículo (ej: Nevera, Cama, Caja...)" class="form-input pl-10 bg-white" autocomplete="off">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[350px] overflow-y-auto p-4 border border-gray-200 rounded-xl bg-gray-50" id="checklist-articulos-list">
                            @foreach($articulos as $art)
                                <div class="articulo-item flex flex-col gap-2 p-3 bg-white rounded-xl border border-gray-100 shadow-sm hover:border-blue-300 transition-all" 
                                     data-category="{{ $art->categoria }}" 
                                     data-precio-peq="{{ $art->precio_pequeno ?? 0 }}"
                                     data-precio-med="{{ $art->precio_mediano ?? 0 }}"
                                     data-precio-gra="{{ $art->precio_grande ?? 0 }}"
                                     style="display: none;">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <input type="checkbox" id="art-{{ $art->id }}" class="articulo-checkbox w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer flex-shrink-0" onchange="toggleArticuloSizes({{ $art->id }})">
                                        <label for="art-{{ $art->id }}" class="text-sm font-semibold text-gray-700 select-none cursor-pointer leading-snug" title="{{ $art->nombre }}">{{ $art->nombre }}</label>
                                    </div>
                                    
                                    <div class="flex flex-col gap-2 mt-2 pl-6 hidden" id="sizes-container-{{ $art->id }}">
                                        <!-- Pequeño -->
                                        <div class="flex items-center justify-between gap-1 bg-gray-50 px-2 py-1 rounded-lg border border-gray-100">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <input type="checkbox" name="articulos[{{ $art->id }}][pequeno]" id="size-peq-{{ $art->id }}" class="size-checkbox w-3.5 h-3.5 text-blue-500 rounded cursor-pointer" onchange="toggleSizeQuantity({{ $art->id }}, 'peq')">
                                                <span class="text-[11px] text-gray-700 font-medium">Peq</span>
                                            </div>
                                            <div class="flex items-center gap-1 flex-shrink-0">
                                                @if(($art->precio_pequeno ?? 0) > 0)
                                                    <span class="text-[9px] bg-green-100 text-green-700 px-1 py-0.5 rounded font-bold">RD$ {{ number_format($art->precio_pequeno, 2) }}</span>
                                                @endif
                                                <input type="number" name="cantidades[{{ $art->id }}][pequeno]" id="cant-peq-{{ $art->id }}" value="1" min="1" disabled class="w-12 px-1 py-0.5 border border-gray-300 rounded text-center text-xs focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:bg-gray-100" onchange="calcularTotal()">
                                            </div>
                                        </div>
                                        
                                        <!-- Mediano -->
                                        <div class="flex items-center justify-between gap-1 bg-gray-50 px-2 py-1 rounded-lg border border-gray-100">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <input type="checkbox" name="articulos[{{ $art->id }}][mediano]" id="size-med-{{ $art->id }}" class="size-checkbox w-3.5 h-3.5 text-blue-500 rounded cursor-pointer" onchange="toggleSizeQuantity({{ $art->id }}, 'med')">
                                                <span class="text-[11px] text-gray-700 font-medium">Med</span>
                                            </div>
                                            <div class="flex items-center gap-1 flex-shrink-0">
                                                @if(($art->precio_mediano ?? 0) > 0)
                                                    <span class="text-[9px] bg-green-100 text-green-700 px-1 py-0.5 rounded font-bold">RD$ {{ number_format($art->precio_mediano, 2) }}</span>
                                                @endif
                                                <input type="number" name="cantidades[{{ $art->id }}][mediano]" id="cant-med-{{ $art->id }}" value="1" min="1" disabled class="w-12 px-1 py-0.5 border border-gray-300 rounded text-center text-xs focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:bg-gray-100" onchange="calcularTotal()">
                                            </div>
                                        </div>
                                        
                                        <!-- Grande -->
                                        <div class="flex items-center justify-between gap-1 bg-gray-50 px-2 py-1 rounded-lg border border-gray-100">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <input type="checkbox" name="articulos[{{ $art->id }}][grande]" id="size-gra-{{ $art->id }}" class="size-checkbox w-3.5 h-3.5 text-blue-500 rounded cursor-pointer" onchange="toggleSizeQuantity({{ $art->id }}, 'gra')">
                                                <span class="text-[11px] text-gray-700 font-medium">Gra</span>
                                            </div>
                                            <div class="flex items-center gap-1 flex-shrink-0">
                                                @if(($art->precio_grande ?? 0) > 0)
                                                    <span class="text-[9px] bg-green-100 text-green-700 px-1 py-0.5 rounded font-bold">RD$ {{ number_format($art->precio_grande, 2) }}</span>
                                                @endif
                                                <input type="number" name="cantidades[{{ $art->id }}][grande]" id="cant-gra-{{ $art->id }}" value="1" min="1" disabled class="w-12 px-1 py-0.5 border border-gray-300 rounded text-center text-xs focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:bg-gray-100" onchange="calcularTotal()">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Dimensiones y Detalles de la Carga (Opcional)</label>
                        <textarea name="dimensiones_carga" class="form-input" rows="4" placeholder="Describe qué objetos deseas mover, peso aproximado, tamaño o cantidad de cajas...">{{ old('dimensiones_carga') }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6">
                        <div>
                            <label class="form-label text-gray-700">¿En qué piso están los artículos? (Origen) <span class="text-red-500">*</span></label>
                            <input type="text" name="piso_origen" value="{{ old('piso_origen', '0') }}" required class="form-input" placeholder="Ej: 1, 2, Sótano, PH...">
                        </div>
                        <div>
                            <label class="form-label text-gray-700">¿A qué piso se llevarán? (Destino) <span class="text-red-500">*</span></label>
                            <input type="text" name="piso_destino" value="{{ old('piso_destino', '0') }}" required class="form-input" placeholder="Ej: 1, 3, PH, etc.">
                        </div>
                    </div>
                </div>

                <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Ruta del Servicio</h2>
                <div class="mb-8 p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <p class="text-sm text-gray-500 mb-4">Selecciona en el mapa el punto de recogida local y el punto de entrega para estimar el costo de distancia.</p>
                    
                    <div class="flex flex-col md:flex-row gap-4 mb-4">
                        <div class="flex-1">
                            <label class="form-label text-blue-600">Punto A (Recogida) <span class="text-red-500">*</span></label>
                            <div class="geo-wrapper">
                                <input type="text" id="punto_recogida_search" autocomplete="off" class="form-input border-blue-300" placeholder="Escribe una dirección o haz clic en el mapa" value="{{ old('punto_recogida_address', '') }}">
                                <input type="hidden" id="punto_recogida" name="punto_recogida" value="{{ old('punto_recogida') }}" required>
                                <input type="hidden" name="punto_recogida_address" id="punto_recogida_address" value="{{ old('punto_recogida_address', '') }}">
                                <div class="geo-suggestions" id="suggestions-a"></div>
                            </div>

                            <button type="button" id="btn-set-a" class="mt-2 w-full py-2 bg-blue-100 text-blue-700 font-bold rounded-lg border border-blue-200 hover:bg-blue-200 text-sm">📍 Seleccionar Punto A en Mapa</button>
                        </div>
                        <div class="flex-1">
                            <label class="form-label text-red-600">Punto B (Entrega) <span class="text-red-500">*</span></label>
                            <div class="geo-wrapper">
                                <input type="text" id="punto_entrega_search" autocomplete="off" class="form-input border-red-300" placeholder="Escribe una dirección o haz clic en el mapa" value="{{ old('punto_entrega_address', '') }}">
                                <input type="hidden" id="punto_entrega" name="punto_entrega" value="{{ old('punto_entrega') }}" required>
                                <input type="hidden" name="punto_entrega_address" id="punto_entrega_address" value="{{ old('punto_entrega_address', '') }}">
                                <div class="geo-suggestions" id="suggestions-b"></div>
                            </div>

                            <button type="button" id="btn-set-b" class="mt-2 w-full py-2 bg-red-100 text-red-700 font-bold rounded-lg border border-red-200 hover:bg-red-200 text-sm">📍 Seleccionar Punto B en Mapa</button>
                        </div>
                    </div>
                    <div id="map-container" class="mb-4"></div>
                    
                    <div class="grid grid-cols-2 gap-4 text-center mt-4 p-4 bg-white rounded-xl shadow-sm border border-gray-200">
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase">Distancia Estimada</span>
                            <span id="distancia-display" class="text-xl font-black text-gray-900">0 km</span>
                            <input type="hidden" name="distancia_km" id="distancia_km" value="0">
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase">Precio Estimado (Ruta + Artículos)</span>
                            <span id="precio-display" class="text-xl font-black text-green-600">RD$ 0.00</span>
                            <input type="hidden" name="precio_estimado_total" id="precio_estimado_total" value="0">
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center text-balance">* El cálculo es una aproximación generada en línea recta entre ambos puntos y suma al costo de logística de los artículos de mudanza. Nos comunicaremos contigo para detalles adiciones.</p>
                </div>

                <div class="mt-10 flex justify-end">
                    <button type="submit" class="bg-primary text-white px-8 py-3 rounded-xl font-bold text-lg hover:bg-hoverPrimary shadow-lg transition-all">
                        Enviar Solicitud
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let map = L.map('map-container').setView([18.7357, -70.1627], 8); // Centro RD
    
    let markerA = null;
    let markerB = null;
    let settingPoint = 'A'; // 'A' or 'B'
    
    // Configuraciones dinámicas desde el backend
    const CONFIG = {
        precio_km_transporte: {{ $config['precio_km_transporte'] ?? 50 }},
        precio_km_mudanza: {{ $config['precio_km_mudanza'] ?? 100 }},
        limite_mudanza: {{ $config['limite_articulos_mudanza'] ?? 5 }}
    };

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const inputA = document.getElementById('punto_recogida');
    const inputB = document.getElementById('punto_entrega');
    const searchA = document.getElementById('punto_recogida_search');
    const searchB = document.getElementById('punto_entrega_search');
    const addressA = document.getElementById('punto_recogida_address');
    const addressB = document.getElementById('punto_entrega_address');
    const suggestionsA = document.getElementById('suggestions-a');
    const suggestionsB = document.getElementById('suggestions-b');
    const btnA = document.getElementById('btn-set-a');
    const btnB = document.getElementById('btn-set-b');
    
    const iconA = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
        iconSize: [25, 41], iconAnchor: [12, 41]
    });
    const iconB = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        iconSize: [25, 41], iconAnchor: [12, 41]
    });

    btnA.addEventListener('click', () => { 
        settingPoint = 'A'; 
        btnA.classList.add('ring-2', 'ring-offset-2', 'ring-blue-500');
        btnB.classList.remove('ring-2', 'ring-offset-2', 'ring-red-500');
    });
    
    btnB.addEventListener('click', () => { 
        settingPoint = 'B'; 
        btnB.classList.add('ring-2', 'ring-offset-2', 'ring-red-500');
        btnA.classList.remove('ring-2', 'ring-offset-2', 'ring-blue-500');
    });

    // ===== Geocoding con Nominatim =====
    let searchTimerA = null;
    let searchTimerB = null;

    function geocodeSearch(query, suggestionsEl, point) {
        if (query.length < 3) {
            suggestionsEl.classList.remove('active');
            suggestionsEl.innerHTML = '';
            return;
        }
        suggestionsEl.innerHTML = '<div class="geo-loading">Buscando...</div>';
        suggestionsEl.classList.add('active');

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&countrycodes=do&addressdetails=1`, {
            headers: { 'Accept-Language': 'es' }
        })
        .then(res => res.json())
        .then(results => {
            if (results.length === 0) {
                suggestionsEl.innerHTML = '<div class="geo-loading">No se encontraron resultados</div>';
                return;
            }
            suggestionsEl.innerHTML = '';
            results.forEach(r => {
                const item = document.createElement('div');
                item.className = 'geo-suggestion-item';
                item.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>${r.display_name}</span>`;
                item.addEventListener('click', () => {
                    selectGeoResult(r, point);
                    suggestionsEl.classList.remove('active');
                });
                suggestionsEl.appendChild(item);
            });
        })
        .catch(() => {
            suggestionsEl.innerHTML = '<div class="geo-loading">Error de conexión</div>';
        });
    }

    function selectGeoResult(result, point) {
        const lat = parseFloat(result.lat);
        const lng = parseFloat(result.lon);
        const address = result.display_name;

        if (point === 'A') {
            inputA.value = lat.toFixed(6) + ', ' + lng.toFixed(6);
            searchA.value = address;
            addressA.value = address;
            if (markerA) map.removeLayer(markerA);
            markerA = L.marker([lat, lng], {icon: iconA}).addTo(map).bindPopup('<b>Punto A</b><br>' + address).openPopup();
            map.setView([lat, lng], 15);
            settingPoint = 'B';
            btnB.click();
        } else {
            inputB.value = lat.toFixed(6) + ', ' + lng.toFixed(6);
            searchB.value = address;
            addressB.value = address;
            if (markerB) map.removeLayer(markerB);
            markerB = L.marker([lat, lng], {icon: iconB}).addTo(map).bindPopup('<b>Punto B</b><br>' + address).openPopup();
            map.setView([lat, lng], 15);
            btnB.classList.remove('ring-2', 'ring-offset-2', 'ring-red-500');
        }
        updateCalculations();
    }

    function reverseGeocode(lat, lng, callback) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`, {
            headers: { 'Accept-Language': 'es' }
        })
        .then(res => res.json())
        .then(data => {
            callback(data.display_name || (lat + ', ' + lng));
        })
        .catch(() => {
            callback(lat + ', ' + lng);
        });
    }

    // Eventos de búsqueda con debounce
    searchA.addEventListener('input', function() {
        clearTimeout(searchTimerA);
        searchTimerA = setTimeout(() => geocodeSearch(this.value.trim(), suggestionsA, 'A'), 400);
    });

    searchB.addEventListener('input', function() {
        clearTimeout(searchTimerB);
        searchTimerB = setTimeout(() => geocodeSearch(this.value.trim(), suggestionsB, 'B'), 400);
    });

    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.geo-wrapper')) {
            suggestionsA.classList.remove('active');
            suggestionsB.classList.remove('active');
        }
    });

    // Haversine formula
    function calcCrow(lat1, lon1, lat2, lon2) {
      var R = 6371; // km
      var dLat = toRad(lat2-lat1);
      var dLon = toRad(lon2-lon1);
      var lat1 = toRad(lat1);
      var lat2 = toRad(lat2);
      var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.sin(dLon/2) * Math.sin(dLon/2) * Math.cos(lat1) * Math.cos(lat2); 
      var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
      var d = R * c;
      return d;
    }
    function toRad(Value) { return Value * Math.PI / 180; }

    function updateCalculations() {
        // Distancia
        let dist = 0;
        if(markerA && markerB) {
            let llA = markerA.getLatLng();
            let llB = markerB.getLatLng();
            dist = calcCrow(llA.lat, llA.lng, llB.lat, llB.lng);
            
            // Dibujar linea si no existe
            if(window.routeLine) map.removeLayer(window.routeLine);
            window.routeLine = L.polyline([llA, llB], {color: 'purple', weight: 4, dashArray: '10, 10'}).addTo(map);
            map.fitBounds(window.routeLine.getBounds(), {padding: [50, 50]});
        }
        document.getElementById('distancia_km').value = dist.toFixed(2);
        document.getElementById('distancia-display').innerText = dist.toFixed(2) + ' km';

        window.calcularTotal(dist);
    }

    // Exponer calcularTotal para que sea llamado cuando cambian cantidades
    window.calcularTotal = function(distancia = null) {
        if(distancia === null) {
            distancia = parseFloat(document.getElementById('distancia_km').value) || 0;
        }
        
        let totalArticulos = 0;
        let countArticulos = 0;
        
        document.querySelectorAll('.size-checkbox:checked').forEach(cb => {
            let parent = cb.closest('.articulo-item');
            let sizeKey = cb.id.split('-')[1]; // 'peq', 'med', 'gra'
            let qtyInputId = 'cant-' + sizeKey + '-' + cb.id.split('-')[2];
            let cant = parseInt(document.getElementById(qtyInputId).value) || 1;
            
            let priceAttr = 'data-precio-' + sizeKey;
            let precio = parent.getAttribute(priceAttr) || 0;
            
            totalArticulos += (parseFloat(precio) * cant);
            countArticulos += cant;
        });

        // Auto-detección de Mudanza por cantidad de artículos
        const selectTipo = document.querySelector('select[name="tipo_servicio"]');
        let precioKM = CONFIG.precio_km_transporte;

        if (countArticulos > CONFIG.limite_mudanza) {
            if (selectTipo.value !== 'mudanza') {
                selectTipo.value = 'mudanza';
            }
            precioKM = CONFIG.precio_km_mudanza;
        } else {
            precioKM = (selectTipo.value === 'mudanza') ? CONFIG.precio_km_mudanza : CONFIG.precio_km_transporte;
        }

        let totalFinal = totalArticulos + (distancia * precioKM);
        
        document.getElementById('precio_estimado_total').value = totalFinal.toFixed(2);
        document.getElementById('precio-display').innerText = 'RD$ ' + totalFinal.toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        document.getElementById('precio-display').title = `Tarifa: RD$ ${precioKM}/km`;
    };

    // Clic en el mapa: poner marcador + reverse geocode para mostrar dirección
    map.on('click', function(e) {
        let lat = e.latlng.lat.toFixed(6);
        let lng = e.latlng.lng.toFixed(6);
        
        if (settingPoint === 'A') {
            inputA.value = lat + ', ' + lng;
            if (markerA) map.removeLayer(markerA);
            markerA = L.marker([lat, lng], {icon: iconA}).addTo(map);
            // Reverse geocode para mostrar la dirección
            reverseGeocode(lat, lng, function(address) {
                searchA.value = address;
                addressA.value = address;
                markerA.bindPopup('<b>Punto A</b><br>' + address).openPopup();
            });
            settingPoint = 'B';
            btnB.click();
        } else {
            inputB.value = lat + ', ' + lng;
            if (markerB) map.removeLayer(markerB);
            markerB = L.marker([lat, lng], {icon: iconB}).addTo(map);
            reverseGeocode(lat, lng, function(address) {
                searchB.value = address;
                addressB.value = address;
                markerB.bindPopup('<b>Punto B</b><br>' + address).openPopup();
            });
            btnB.classList.remove('ring-2', 'ring-offset-2', 'ring-red-500');
        }
        updateCalculations();
    });

    // Lógica de Filtrado, Paginación por Scroll y Reactividad para Transporte y Mudanzas
    const selectServicio = document.getElementById('tipo_servicio');
    const searchInput = document.getElementById('search-articulos');
    const container = document.getElementById('checklist-articulos-container');
    const listContainer = document.getElementById('checklist-articulos-list');
    const items = document.querySelectorAll('.articulo-item');

    let matchingItems = []; // Almacena los elementos que coinciden con los filtros actuales
    let visibleCount = 10;   // Cantidad inicial de artículos a mostrar

    function normalizar(texto) {
        return (texto || '').normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
    }

    function updateVisibleItems() {
        // Ocultar todos los artículos primero
        items.forEach(item => {
            item.style.display = 'none';
        });

        // Mostrar solo los primeros 'visibleCount' que coinciden
        const toShow = matchingItems.slice(0, visibleCount);
        toShow.forEach(item => {
            item.style.display = 'flex';
        });
    }

    function filterArticles() {
        const selectedValue = selectServicio.value;
        const searchTerm = normalizar(searchInput.value.trim());
        
        if (!selectedValue) {
            container.classList.add('hidden');
            items.forEach(item => {
                const checkbox = item.querySelector('.articulo-checkbox');
                checkbox.checked = false;
                window.toggleArticuloSizes(checkbox.id.split('-')[1]);
                item.style.display = 'none';
            });
            matchingItems = [];
            window.calcularTotal();
            return;
        }

        container.classList.remove('hidden');
        matchingItems = [];

        items.forEach(item => {
            const cat = item.getAttribute('data-category');
            const nombre = normalizar(item.querySelector('label').innerText);
            const checkbox = item.querySelector('.articulo-checkbox');
            
            const matchesCategory = (cat === selectedValue || cat === 'ambos');
            const matchesSearch = nombre.includes(searchTerm);

            if (matchesCategory) {
                if (matchesSearch) {
                    matchingItems.push(item);
                }
            } else {
                // Desmarcar si el artículo ya no pertenece a la categoría elegida
                if (checkbox.checked) {
                    checkbox.checked = false;
                    window.toggleArticuloSizes(checkbox.id.split('-')[1]);
                }
            }
        });

        visibleCount = 10; // Reiniciar contador al buscar o cambiar tipo
        updateVisibleItems();
        window.calcularTotal();
    }

    // Listener de scroll para el contenedor de artículos (Lazy Loading)
    listContainer.addEventListener('scroll', function() {
        if (listContainer.scrollTop + listContainer.clientHeight >= listContainer.scrollHeight - 50) {
            if (visibleCount < matchingItems.length) {
                visibleCount += 10;
                updateVisibleItems();
            }
        }
    });

    selectServicio.addEventListener('change', filterArticles);
    searchInput.addEventListener('input', filterArticles);
    if (selectServicio.value) filterArticles();
});

window.toggleArticuloSizes = function(id) {
    const mainCheckbox = document.getElementById('art-' + id);
    const container = document.getElementById('sizes-container-' + id);
    
    if (mainCheckbox.checked) {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
        // Uncheck all sizes and disable inputs
        const sizeCheckboxes = container.querySelectorAll('.size-checkbox');
        sizeCheckboxes.forEach(cb => {
            cb.checked = false;
            let sizeKey = cb.id.split('-')[1]; // 'peq', 'med', 'gra'
            let qtyInput = document.getElementById('cant-' + sizeKey + '-' + id);
            qtyInput.setAttribute('disabled', 'disabled');
            qtyInput.value = '1';
        });
    }
    window.calcularTotal();
};

window.toggleSizeQuantity = function(id, sizeKey) {
    const sizeCheckbox = document.getElementById('size-' + sizeKey + '-' + id);
    const qtyInput = document.getElementById('cant-' + sizeKey + '-' + id);
    
    if (sizeCheckbox.checked) {
        qtyInput.removeAttribute('disabled');
        qtyInput.focus();
    } else {
        qtyInput.setAttribute('disabled', 'disabled');
        qtyInput.value = '1';
    }
    window.calcularTotal();
};
</script>
@endpush
