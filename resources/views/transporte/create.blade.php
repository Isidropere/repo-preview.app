@extends('layouts.app')
@section('title', 'Solicitar Transporte y Mudanza')

@push('head_styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map-container { height: 350px; width: 100%; border-radius: 12px; z-index: 1; border: 1px solid #e5e7eb; }
    .form-input { w-full; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem; outline: none; transition: border-color 0.2s; box-sizing: border-box; width: 100%; }
    .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
    .form-label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .btn-gps { background: #0ea5e9; color: #fff; border: none; border-radius: 8px; padding: 10px 16px; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; }
    .btn-gps:hover { background: #0284c7; }
    .btn-gps:disabled { background: #94a3b8; cursor: not-allowed; }
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
                <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Información Personal</h2>
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
                    <div class="md:col-span-2">
                        <label class="form-label">Correo Electrónico <span class="text-red-500">*</span></label>
                        <input type="email" name="correo" value="{{ old('correo', Auth::check() ? Auth::user()->email : '') }}" required class="form-input">
                    </div>
                </div>

                <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Detalles del Servicio</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="form-label">Fecha del Servicio <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_servicio" value="{{ old('fecha_servicio') }}" required min="{{ date('Y-m-d') }}" class="form-input">
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Dirección Exacta (Origen o Destino principal) <span class="text-red-500">*</span></label>
                        <input type="text" name="direccion" value="{{ old('direccion') }}" required class="form-input" placeholder="Ej: Av. Winston Churchill esq. Gustavo Mejía Ricart, Piantini">
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Dimensiones y Detalles de la Carga <span class="text-red-500">*</span></label>
                        <textarea name="dimensiones_carga" required class="form-input" rows="4" placeholder="Describe qué objetos deseas mover, peso aproximado, tamaño o cantidad de cajas...">{{ old('dimensiones_carga') }}</textarea>
                    </div>
                </div>

                <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Ubicación Geográfica</h2>
                <div class="mb-8">
                    <p class="text-sm text-gray-500 mb-4">Ayúdanos a localizar el punto exacto marcándolo en el mapa o utilizando tu GPS.</p>
                    
                    <div class="flex gap-4 mb-4">
                        <input type="text" id="coordenadas" name="ubicacion_geologica" value="{{ old('ubicacion_geologica') }}" readonly class="form-input bg-gray-50" placeholder="Haz clic en el mapa para capturar las coordenadas">
                        <button type="button" id="btn-gps" class="btn-gps">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Usar mi GPS
                        </button>
                    </div>
                    <div id="map-container"></div>
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
    let marker = null;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const inputCoords = document.getElementById('coordenadas');
    
    // Si ya hay coordenadas, poner el marcador
    if (inputCoords.value) {
        let parts = inputCoords.value.split(',');
        if (parts.length === 2) {
            let lat = parseFloat(parts[0]);
            let lng = parseFloat(parts[1]);
            marker = L.marker([lat, lng]).addTo(map);
            map.setView([lat, lng], 15);
        }
    }

    // Evento al hacer clic en el mapa
    map.on('click', function(e) {
        let lat = e.latlng.lat.toFixed(6);
        let lng = e.latlng.lng.toFixed(6);
        inputCoords.value = lat + ', ' + lng;
        
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
    });

    // Botón GPS
    document.getElementById('btn-gps').addEventListener('click', function() {
        const btn = this;
        if (!navigator.geolocation) {
            alert('Geolocalización no soportada en este navegador.');
            return;
        }
        
        btn.disabled = true;
        btn.innerHTML = 'Buscando...';
        
        navigator.geolocation.getCurrentPosition(function(pos) {
            let lat = pos.coords.latitude.toFixed(6);
            let lng = pos.coords.longitude.toFixed(6);
            inputCoords.value = lat + ', ' + lng;
            
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lng]).addTo(map);
            map.setView([lat, lng], 16);
            
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Usar mi GPS';
        }, function(err) {
            alert('Error obteniendo ubicación. Verifica los permisos.');
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Usar mi GPS';
        });
    });
});
</script>
@endpush
