@extends('layouts.app')

@section('title', 'Mantenimiento de Motivos de Devolución')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Motivos de Devolución</h1>
                <p class="text-sm text-gray-500 mt-1">Configura las opciones que se mostrarán al usuario al solicitar reembolsos.</p>
            </div>
            <a href="{{ route('admin.motivos_devolucion.create') }}"
               class="inline-flex items-center gap-2 bg-primary hover:bg-hoverPrimary text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Motivo
            </a>
        </div>

        @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Motivo</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha Creación</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($motivos as $m)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-400">#{{ $m->id }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ $m->motivo }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $m->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $m->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $m->created_at ? $m->created_at->format('d/m/Y h:i A') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.motivos_devolucion.edit', $m->id) }}" class="text-primary hover:text-hoverPrimary transition-colors">Editar</a>
                                    <form action="{{ route('admin.motivos_devolucion.destroy', $m->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este motivo? Si ya ha sido utilizado en transacciones, se desactivará en su lugar.')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 transition-colors">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500 italic">No hay motivos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($motivos->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $motivos->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
