@extends('layouts.app')

@section('title', 'Solicitudes de Transporte - Panel Admin')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        <div class="mb-6 flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Solicitudes de Transporte y Mudanza</h1>
                <p class="text-sm text-gray-500 mt-1">Gestión de envíos y mudanzas solicitadas por los clientes.</p>
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
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">ID / Fecha Serv.</th>
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
                                    <span class="font-bold text-gray-800">#{{ $sol->id }}</span>
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
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
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
</div>
@endsection
