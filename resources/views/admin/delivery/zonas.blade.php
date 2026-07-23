@extends('layouts.admin')

@section('title', 'Zonas de Envío')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Gestión de Envíos por Provincia y Municipio</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Controla a qué lugares de República Dominicana se permite solicitar envíos. 
                    <strong>Si apagas una Provincia, ningún municipio de esa provincia estará disponible.</strong>
                </p>
            </div>
            
            <div class="border-t border-gray-200">
                <ul role="list" class="divide-y divide-gray-200">
                    @foreach($provincias as $provincia)
                        <li class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center cursor-pointer" onclick="toggleAccordion('provincia-{{ $provincia->id_provincia }}', 'icon-{{ $provincia->id_provincia }}')">
                                    <svg id="icon-{{ $provincia->id_provincia }}" class="w-5 h-5 text-gray-400 mr-2 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    <div class="text-sm font-bold text-gray-900">{{ $provincia->provincia }}</div>
                                    <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $provincia->municipios->count() }} municipios
                                    </span>
                                </div>
                                
                                <div class="flex items-center">
                                    <span class="mr-3 text-sm font-medium text-gray-900">Provincia Activa</span>
                                    <input type="checkbox" data-id="{{ $provincia->id_provincia }}" class="switch-provincia" style="width: 20px; height: 20px; cursor: pointer;" {{ $provincia->activo_entrega ? 'checked' : '' }}>
                                </div>
                            </div>
                            
                            <!-- Lista de municipios desplegable -->
                            <div id="provincia-{{ $provincia->id_provincia }}" class="mt-4 pl-7 pr-4 pb-2 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4" style="display: none;">
                                @foreach($provincia->municipios as $municipio)
                                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg bg-gray-50 hover:bg-gray-100 transition">
                                        <div class="text-sm text-gray-700">{{ $municipio->municipio }}</div>
                                        <input type="checkbox" data-id="{{ $municipio->id_municipio }}" class="switch-municipio ml-3" style="width: 18px; height: 18px; cursor: pointer;" {{ $municipio->activo_entrega ? 'checked' : '' }}>
                                    </div>
                                @endforeach
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Tabla de Peticiones Rechazadas -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Solicitudes de Zonas No Contempladas</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Usuarios que intentaron solicitar un envío a una zona inactiva o no registrada.</p>
            </div>
            <div class="border-t border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pueblo / Municipio Solicitado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($peticionesNoCubiertas as $peticion)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $peticion->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        @if($peticion->user)
                                            {{ $peticion->user->nombres }} {{ $peticion->user->apellidos }}
                                        @else
                                            Usuario Anónimo
                                        @endif
                                    </div>
                                    @if($peticion->user)
                                    <div class="text-sm text-gray-500">{{ $peticion->user->email }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        {{ $peticion->pueblo }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                    No hay registros de zonas no contempladas.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleAccordion(contentId, iconId) {
        const content = document.getElementById(contentId);
        const icon = document.getElementById(iconId);
        
        if (content.style.display === 'none') {
            content.style.display = 'grid';
            icon.style.transform = 'rotate(90deg)';
        } else {
            content.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        
        // Manejar Provincia Toggle
        const provinciaSwitches = document.querySelectorAll('.switch-provincia');
        provinciaSwitches.forEach(toggle => {
            toggle.addEventListener('change', function () {
                const provId = this.getAttribute('data-id');
                const isChecked = this.checked;
                
                fetch(`/admin/delivery/zonas/provincia/${provId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ activo: isChecked })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        this.checked = !isChecked; // revert
                        alert('Error al actualizar: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    this.checked = !isChecked; // revert
                    console.error('Error:', error);
                    alert('Ocurrió un error en la conexión.');
                });
            });
        });

        // Manejar Municipio Toggle
        const municipioSwitches = document.querySelectorAll('.switch-municipio');
        municipioSwitches.forEach(toggle => {
            toggle.addEventListener('change', function () {
                const munId = this.getAttribute('data-id');
                const isChecked = this.checked;
                
                fetch(`/admin/delivery/zonas/municipio/${munId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ activo: isChecked })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        this.checked = !isChecked; // revert
                        alert('Error al actualizar: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    this.checked = !isChecked; // revert
                    console.error('Error:', error);
                    alert('Ocurrió un error en la conexión.');
                });
            });
        });

    });
</script>
@endpush

