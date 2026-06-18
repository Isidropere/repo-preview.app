@extends('layouts.app')

@section('title', 'Recursos Humanos | CambialóRD')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestión de Recursos Humanos</h1>
                <p class="text-sm text-gray-500 mt-1">Crea, edita y administra las ofertas de empleo/vacantes que se visualizan en el portal.</p>
            </div>
            <a href="{{ route('admin.recursos-humanos.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-hoverPrimary text-white text-xs font-semibold rounded-xl transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva Vacante
            </a>
        </div>

        @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium animate-fade-in">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Título</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Instrucciones de Postulación</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($empleos as $emp)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-gray-800">{{ $emp->titulo }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-500 max-w-sm truncate">{{ Str::limit($emp->descripcion, 90) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-500 max-w-sm truncate">{{ Str::limit($emp->requisitos, 90) }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($emp->activo)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                    Activo
                                </span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                    Inactivo
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold space-x-2">
                                <a href="{{ route('admin.recursos-humanos.edit', $emp->id) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-primary hover:text-white text-gray-700 rounded-lg transition">
                                    Editar
                                </a>
                                <form action="{{ route('admin.recursos-humanos.destroy', $emp->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar esta vacante de empleo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white rounded-lg transition">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v1m16 0h.01M9 16h.01"/></svg>
                                    <span>No hay vacantes de empleo registradas en el momento.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($empleos->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $empleos->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
