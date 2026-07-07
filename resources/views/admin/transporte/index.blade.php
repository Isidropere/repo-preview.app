@extends('layouts.app')

@section('title', 'Solicitudes de Transporte - Panel Admin')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        <div class="mb-6 flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Solicitudes de Transporte y Mudanza</h1>
                <p class="text-sm text-gray-500 mt-1">Gestión de envíos, mudanzas y catálogo de artículos.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 mb-6">
                {{ session('success') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4 mb-6 flex items-center gap-2">
                <svg class="w-6 h-6 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{!! session('warning') !!}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-6">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Pestañas de Navegación -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button type="button" onclick="switchTab('solicitudes')" id="tab-solicitudes" class="border-blue-600 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm flex items-center gap-2 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    Solicitudes de Servicio
                </button>
                <button type="button" onclick="switchTab('catalogo')" id="tab-catalogo" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm flex items-center gap-2 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Catálogo de Artículos
                </button>
                <button type="button" onclick="switchTab('configuracion')" id="tab-configuracion" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm flex items-center gap-2 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Configuraciones Globales
                </button>
            </nav>
        </div>

        <!-- SECCIÓN 1: SOLICITUDES DE SERVICIO -->
        <div id="section-solicitudes" class="transition-all">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="p-4 bg-gray-50 border-b border-gray-100">
                    <form action="{{ route('admin.erp.transporte.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, correo, cédula, teléfono o ID..." class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div class="w-full md:w-48">
                            <select name="estado" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                                <option value="">Todos los estados</option>
                                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="aprobada" {{ request('estado') == 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                                <option value="rechazada" {{ request('estado') == 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="date" name="desde" value="{{ request('desde') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary" title="Fecha desde">
                            <span class="text-gray-400 text-xs">a</span>
                            <input type="date" name="hasta" value="{{ request('hasta') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary" title="Fecha hasta">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-hoverPrimary">
                                Filtrar
                            </button>
                            @if(request('buscar') || request('estado') || request('desde') || request('hasta'))
                                <a href="{{ route('admin.erp.transporte.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg text-sm font-bold hover:bg-gray-300 text-center">
                                    Limpiar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">ID / Servicio</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">Cliente</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">Contacto</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">Ubicación</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">Estado</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($solicitudes as $sol)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-800">#{{ $sol->id }}</span>
                                            @if($sol->tipo_servicio == 'mudanza')
                                                <span class="px-2 py-0.5 bg-purple-50 text-purple-700 text-[10px] font-bold rounded-full border border-purple-100">Mudanza</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-bold rounded-full border border-indigo-100">Transporte</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">{{ $sol->fecha_servicio->format('d/m/Y') }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-800">{{ $sol->nombre }} {{ $sol->apellido }}</p>
                                        <p class="text-xs text-gray-500 mt-1">Cédula: {{ $sol->cedula }}</p>
                                        @if($sol->id_usuario)
                                            <span class="inline-block mt-1 px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-bold rounded-full">Usuario Registrado</span>
                                        @else
                                            <span class="inline-block mt-1 px-2 py-0.5 bg-gray-100 text-gray-500 text-[10px] font-bold rounded-full">Invitado</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <p>{{ $sol->telefono }}</p>
                                        <p class="text-xs mt-1 truncate max-w-[150px]" title="{{ $sol->correo }}">{{ $sol->correo }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600">
                                        <p class="font-semibold text-gray-800">{{ $sol->direccion }}</p>
                                        @if($sol->ubicacion_geologica)
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ str_replace(' ', '', $sol->ubicacion_geologica) }}" target="_blank" class="inline-flex items-center gap-1 mt-1 text-blue-600 hover:underline">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                Ver en GPS
                                            </a>
                                        @else
                                            <span class="text-gray-400 italic">Sin GPS</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($sol->estado == 'pendiente')
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">Pendiente</span>
                                        @elseif($sol->estado == 'aprobada')
                                            <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Aprobada</span>
                                        @else
                                            <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">Rechazada</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Botón interactivo para ver lista de artículos -->
                                            <button type="button" onclick="toggleDetalles({{ $sol->id }})" class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors" title="Ver Detalles de Artículos">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                            </button>

                                            <a href="{{ route('admin.erp.transporte.pdf', $sol->id) }}" class="p-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200" title="Ver Detalles (PDF)">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </a>

                                            @if($sol->estado == 'pendiente')
                                                <form action="{{ route('admin.erp.transporte.aprobar', $sol->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('¿Aprobar esta solicitud?')" class="p-2 bg-green-50 text-green-600 rounded-lg hover:bg-green-100" title="Aprobar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.erp.transporte.rechazar', $sol->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('¿Rechazar esta solicitud?')" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100" title="Rechazar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                <!-- Detalle Colapsable -->
                                <tr id="detalles-{{ $sol->id }}" class="hidden bg-gray-50/50">
                                    <td colspan="6" class="px-6 py-4 border-b">
                                        <div class="p-5 bg-white rounded-xl border border-gray-200 shadow-sm max-w-5xl mx-auto">
                                            <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2 border-b pb-2">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                Artículos Seleccionados para el Servicio ({{ count($sol->articulos) }} registrados)
                                            </h4>
                                            
                                            @if(count($sol->articulos) > 0)
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mb-4">
                                                    @foreach($sol->articulos as $art)
                                                        @php
                                                            $dimsObj = json_decode($art->pivot->dimensiones, true);
                                                            $isJson = is_array($dimsObj);
                                                        @endphp
                                                        <div class="flex flex-col p-3 bg-gray-50 rounded-lg border border-gray-100 shadow-sm">
                                                            <div class="flex justify-between items-center mb-2">
                                                                <span class="text-xs font-semibold text-gray-800">{{ $art->nombre }}</span>
                                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">x{{ $art->pivot->cantidad }}</span>
                                                            </div>
                                                            <div class="flex flex-col gap-1 text-[10px] text-gray-500">
                                                                @if($isJson)
                                                                    @foreach($dimsObj as $sizeKey => $data)
                                                                        <p><strong class="capitalize">{{ $sizeKey }}:</strong> {{ $data['cantidad'] }} u. (RD$ {{ number_format($data['precio'], 2) }})</p>
                                                                    @endforeach
                                                                @else
                                                                    @if($art->pivot->dimensiones)
                                                                        <p><strong>Dim:</strong> {{ $art->pivot->dimensiones }}</p>
                                                                    @endif
                                                                @endif
                                                                
                                                                @if($art->pivot->peso)
                                                                    <p><strong>Peso:</strong> {{ $art->pivot->peso }} kg</p>
                                                                @endif
                                                                @if($art->pivot->precio_unitario > 0)
                                                                    <p class="text-blue-600 font-bold mt-1"><strong>Subtotal:</strong> RD$ {{ number_format($art->pivot->subtotal, 2) }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="flex items-center gap-2 p-3 bg-yellow-50 text-yellow-800 rounded-lg border border-yellow-100 text-xs mb-4">
                                                    <svg class="w-5 h-5 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <span>No se marcaron artículos específicos del checklist. La descripción está basada en el cuadro de texto libre abajo.</span>
                                                </div>
                                            @endif

                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-gray-600 bg-gray-50 p-4 rounded-lg border border-gray-100">
                                                <div>
                                                    <p class="mb-1"><strong>Dirección:</strong> {{ $sol->direccion }}</p>
                                                    <div class="flex gap-4 mb-1">
                                                        <p><strong>Recogida (GPS):</strong> {{ $sol->punto_recogida ?? 'N/A' }}</p>
                                                        <p class="text-blue-600"><strong>Piso:</strong> {{ $sol->piso_origen ?? '0' }}</p>
                                                    </div>
                                                    <div class="flex gap-4">
                                                        <p><strong>Entrega (GPS):</strong> {{ $sol->punto_entrega ?? 'N/A' }}</p>
                                                        <p class="text-red-600"><strong>Piso:</strong> {{ $sol->piso_destino ?? '0' }}</p>
                                                    </div>
                                                </div>
                                                <div class="border-l border-r border-gray-200 px-4">
                                                    <p class="mb-1"><strong>Distancia:</strong> {{ $sol->distancia_km ?? '0' }} km</p>
                                                    <p class="text-lg font-black text-green-700 mt-2">RD$ {{ number_format($sol->precio_estimado_total ?? 0, 2) }}</p>
                                                    <p class="text-[10px] text-gray-400">Total Estimado</p>
                                                </div>
                                                <div>
                                                    <p><strong>Descripción de Carga Libre:</strong></p>
                                                    <p class="mt-1 text-gray-700 italic bg-white p-2 rounded border border-gray-200">{{ $sol->dimensiones_carga }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                        No hay solicitudes de transporte registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($solicitudes->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $solicitudes->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- SECCIÓN 2: CATÁLOGO DE ARTÍCULOS (CRUD) -->
        <div id="section-catalogo" class="hidden transition-all">
            <!-- Formulario para agregar artículo -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Agregar Nuevo Artículo al Catálogo
                </h3>
                <form action="{{ route('admin.erp.transporte.articulos.store') }}" method="POST" style="display: flex !important; flex-wrap: wrap !important; align-items: flex-end !important; gap: 16px !important; width: 100% !important;">
                    @csrf
                    <div style="flex: 1 1 200px !important; min-width: 200px !important;">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5" style="white-space: nowrap !important; display: block !important;">Nombre<span class="text-red-500" style="margin-left: 2px !important; color: #ef4444 !important;">*</span></label>
                        <input type="text" name="nombre" required placeholder="Ej: Sofá, Nevera..." class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500 h-[32px]" style="height: 32px !important;">
                    </div>
                    <div style="width: 180px !important; flex-shrink: 0 !important;">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5" style="white-space: nowrap !important; display: block !important;">Categoría<span class="text-red-500" style="margin-left: 2px !important; color: #ef4444 !important;">*</span></label>
                        <select name="categoria" required class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500 h-[32px]" style="height: 32px !important;">
                            <option value="ambos">Ambos</option>
                            <option value="mudanza">Solo Mudanza</option>
                            <option value="transporte">Solo Transporte</option>
                        </select>
                    </div>
                    <div style="width: 90px !important; flex-shrink: 0 !important;">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5" style="white-space: nowrap !important; display: block !important;">Pequeño</label>
                        <input type="number" name="precio_pequeno" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500 h-[32px]" style="height: 32px !important;">
                    </div>
                    <div style="width: 90px !important; flex-shrink: 0 !important;">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5" style="white-space: nowrap !important; display: block !important;">Mediano</label>
                        <input type="number" name="precio_mediano" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500 h-[32px]" style="height: 32px !important;">
                    </div>
                    <div style="width: 90px !important; flex-shrink: 0 !important;">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5" style="white-space: nowrap !important; display: block !important;">Grande</label>
                        <input type="number" name="precio_grande" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500 h-[32px]" style="height: 32px !important;">
                    </div>
                    <div style="width: 150px !important; flex-shrink: 0 !important;">
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-700 transition-colors flex items-center justify-center gap-1.5 shadow" style="height: 32px !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Agregar Artículo
                        </button>
                    </div>
                </form>
            </div>

            <!-- Listado de artículos con edición inline -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Catálogo Activo</h3>
                    <span class="text-xs font-bold px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full">{{ count($articulos) }} Artículos</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b w-24">ID</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">Artículo</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b w-32">Peq (RD$)</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b w-32">Med (RD$)</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b w-32">Gra (RD$)</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">Categoría</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">Estatus</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b text-center w-36">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($articulos as $art)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-bold text-gray-800">#{{ $art->id }}</td>
                                    <td class="px-6 py-4">
                                        <form id="edit-form-{{ $art->id }}" action="{{ route('admin.erp.transporte.articulos.update', $art->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="nombre" value="{{ $art->nombre }}" required class="px-3 py-1 border border-transparent hover:border-gray-300 rounded focus:border-blue-500 text-sm font-semibold text-gray-800 bg-transparent focus:bg-white transition-all w-full max-w-xs">
                                    </td>
                                    <td class="px-6 py-4">
                                            <input type="number" name="precio_pequeno" step="0.01" min="0" value="{{ $art->precio_pequeno }}" class="px-3 py-1 border border-transparent hover:border-gray-300 rounded focus:border-blue-500 text-sm font-semibold text-gray-800 bg-transparent focus:bg-white transition-all w-full max-w-[120px]">
                                    </td>
                                    <td class="px-6 py-4">
                                            <input type="number" name="precio_mediano" step="0.01" min="0" value="{{ $art->precio_mediano }}" class="px-3 py-1 border border-transparent hover:border-gray-300 rounded focus:border-blue-500 text-sm font-semibold text-gray-800 bg-transparent focus:bg-white transition-all w-full max-w-[120px]">
                                    </td>
                                    <td class="px-6 py-4">
                                            <input type="number" name="precio_grande" step="0.01" min="0" value="{{ $art->precio_grande }}" class="px-3 py-1 border border-transparent hover:border-gray-300 rounded focus:border-blue-500 text-sm font-semibold text-gray-800 bg-transparent focus:bg-white transition-all w-full max-w-[120px]">
                                    </td>
                                    <td class="px-6 py-4">
                                            <select name="categoria" class="px-2 py-1 border border-transparent hover:border-gray-300 rounded focus:border-blue-500 text-xs font-semibold text-gray-700 bg-transparent focus:bg-white transition-all">
                                                <option value="ambos" {{ $art->categoria == 'ambos' ? 'selected' : '' }}>Ambos</option>
                                                <option value="mudanza" {{ $art->categoria == 'mudanza' ? 'selected' : '' }}>Solo Mudanza</option>
                                                <option value="transporte" {{ $art->categoria == 'transporte' ? 'selected' : '' }}>Solo Transporte</option>
                                            </select>
                                    </td>
                                    <td class="px-6 py-4">
                                            <select name="estatus" class="px-2 py-1 border border-transparent hover:border-gray-300 rounded focus:border-blue-500 text-xs font-bold bg-transparent focus:bg-white transition-all {{ $art->estatus ? 'text-green-700' : 'text-red-700' }}">
                                                <option value="1" {{ $art->estatus ? 'selected' : '' }} class="text-green-700">Activo</option>
                                                <option value="0" {{ !$art->estatus ? 'selected' : '' }} class="text-red-700">Inactivo</option>
                                            </select>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Guardar edición inline -->
                                            <button type="submit" class="p-2 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition-colors" title="Guardar Cambios">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        </form>

                                        <!-- Eliminar artículo -->
                                        <form action="{{ route('admin.erp.transporte.articulos.destroy', $art->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este artículo del catálogo? Esto borrará el artículo pero conservará su registro histórico en solicitudes antiguas.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors" title="Eliminar del Catálogo">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                        No hay artículos registrados en el catálogo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 3: CONFIGURACIONES GLOBALES -->
        <div id="section-configuracion" class="hidden transition-all">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-2xl mx-auto">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2 border-b pb-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Ajustes de Precios y Lógicas
                </h3>
                
                <form action="{{ route('admin.erp.transporte.config.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Precio por KM (Transporte) <span class="text-red-500">*</span></label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">RD$</span>
                                <input type="number" name="precio_km_transporte" step="0.01" value="{{ $config['precio_km_transporte'] }}" required class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <p class="mt-1 text-[10px] text-gray-400 italic">Tarifa base para servicios de carga ligera.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Precio por KM (Mudanza) <span class="text-red-500">*</span></label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">RD$</span>
                                <input type="number" name="precio_km_mudanza" step="0.01" value="{{ $config['precio_km_mudanza'] }}" required class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <p class="mt-1 text-[10px] text-gray-400 italic">Tarifa para servicios que requieren manejo de mudanza.</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Límite de Artículos para Auto-Mudanza <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-4">
                            <input type="number" name="limite_articulos_mudanza" value="{{ $config['limite_articulos_mudanza'] }}" required class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                            <span class="text-xs text-gray-500">Si el cliente marca más de este número de artículos, se cobrará automáticamente como <strong>Mudanza</strong>.</span>
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Guardar Configuraciones
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Manejo del sistema de pestañas (Tabs)
    function switchTab(tab) {
        const tabSol = document.getElementById('tab-solicitudes');
        const tabCat = document.getElementById('tab-catalogo');
        const tabConf = document.getElementById('tab-configuracion');
        
        const secSol = document.getElementById('section-solicitudes');
        const secCat = document.getElementById('section-catalogo');
        const secConf = document.getElementById('section-configuracion');
 
        // Reset all
        [tabSol, tabCat, tabConf].forEach(t => {
            if (t) {
                t.classList.remove('border-blue-600', 'text-blue-600', 'font-bold');
                t.classList.add('border-transparent', 'text-gray-500', 'font-semibold');
            }
        });
        [secSol, secCat, secConf].forEach(s => { if (s) s.classList.add('hidden'); });

        // Activate selected
        if (tab === 'solicitudes') {
            tabSol.classList.add('border-blue-600', 'text-blue-600', 'font-bold');
            tabSol.classList.remove('border-transparent', 'text-gray-500', 'font-semibold');
            secSol.classList.remove('hidden');
        } else if (tab === 'catalogo') {
            tabCat.classList.add('border-blue-600', 'text-blue-600', 'font-bold');
            tabCat.classList.remove('border-transparent', 'text-gray-500', 'font-semibold');
            secCat.classList.remove('hidden');
        } else if (tab === 'configuracion') {
            tabConf.classList.add('border-blue-600', 'text-blue-600', 'font-bold');
            tabConf.classList.remove('border-transparent', 'text-gray-500', 'font-semibold');
            secConf.classList.remove('hidden');
        }
    }

    // Toggle de filas de detalles colapsables
    function toggleDetalles(id) {
        const detailRow = document.getElementById('detalles-' + id);
        if (detailRow.classList.contains('hidden')) {
            detailRow.classList.remove('hidden');
        } else {
            detailRow.classList.add('hidden');
        }
    }
</script>
@endpush
