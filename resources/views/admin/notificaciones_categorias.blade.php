@extends('layouts.app')

@section('title', 'Notificar por Categorías - Admin')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        {{-- Encabezado --}}
        <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Notificar por Categorías</h1>
                    <p class="text-sm text-gray-500 mt-1">Filtra productos/servicios y notifica directamente a sus dueños vía web/móvil o correo.</p>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Panel de Filtro --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
            <form method="GET" action="{{ route('admin.notificaciones.categorias') }}" class="flex flex-col sm:flex-row items-end gap-4">
                <div class="flex-1 w-full">
                    <label for="id_categoria_item" class="block text-sm font-medium text-gray-700 mb-2">Selecciona una Categoría</label>
                    <select name="id_categoria_item" id="id_categoria_item" onchange="this.form.submit()"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Todas las categorías</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id_categoria_item }}" {{ $idCategoria == $cat->id_categoria_item ? 'selected' : '' }}>
                                {{ $cat->categoria }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($idCategoria)
                <a href="{{ route('admin.notificaciones.categorias') }}"
                   class="w-full sm:w-auto border border-gray-200 text-gray-600 hover:bg-gray-100 px-6 py-2.5 rounded-xl text-sm font-medium transition-colors text-center">
                    Limpiar Filtro
                </a>
                @endif
            </form>
        </div>

        {{-- Resultados --}}
        @if($items && $items->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                <th class="px-6 py-4">Artículo / Servicio</th>
                                <th class="px-6 py-4">Dueño</th>
                                <th class="px-6 py-4">Contacto</th>
                                <th class="px-6 py-4">Dirección Predeterminada</th>
                                <th class="px-6 py-4">Monto</th>
                                <th class="px-6 py-4">Stock</th>
                                <th class="px-6 py-4 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @foreach($items as $item)
                                @php
                                    $usuario = $item->usuario;
                                    $direccion = $item->direccionPredeterminada;
                                @endphp
                                <tr class="hover:bg-gray-50/55 transition-colors">
                                    {{-- Artículo / Servicio --}}
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">{{ $item->item }}</div>
                                        <div class="mt-1">
                                            @if($item->tipo_trans == 1)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-100">Venta</span>
                                            @elseif($item->tipo_trans == 2)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100">Intercambio</span>
                                            @elseif($item->tipo_trans == 3)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">Venta/Intercambio</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-50 text-gray-700 border border-gray-100">Otro</span>
                                            @endif
                                        </div>
                                    </td>
                                    {{-- Dueño --}}
                                    <td class="px-6 py-4">
                                        @if($usuario)
                                            <div class="font-medium text-gray-800">{{ $usuario->nombres }} {{ $usuario->apellidos }}</div>
                                            <div class="text-xs text-gray-400 mt-0.5">&#64;{{ $usuario->nombre_usuario }}</div>
                                        @else
                                            <span class="text-gray-400 italic">Desconocido</span>
                                        @endif
                                    </td>
                                    {{-- Contacto --}}
                                    <td class="px-6 py-4">
                                        @if($usuario)
                                            <div class="flex flex-col gap-0.5 text-xs">
                                                <a href="mailto:{{ $usuario->email }}" class="text-primary hover:underline font-medium">{{ $usuario->email }}</a>
                                                @if($usuario->telefono)
                                                    <span class="text-gray-500">{{ $usuario->telefono }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    {{-- Dirección --}}
                                    <td class="px-6 py-4 max-w-xs">
                                        @if($direccion)
                                            <div class="text-xs text-gray-600">
                                                {{ $direccion->calle }}{{ $direccion->N_casa_edificio ? ', #' . $direccion->N_casa_edificio : '' }}
                                                @if($direccion->sector), {{ $direccion->sector }}@endif
                                                @if($direccion->municipio || $direccion->provincia)
                                                    <div class="text-gray-400 mt-0.5 font-medium">
                                                        {{ $direccion->municipio->municipio ?? '' }}{{ $direccion->municipio && $direccion->provincia ? ', ' : '' }}{{ $direccion->provincia->provincia ?? '' }}
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400 text-xs italic">No registrada</span>
                                        @endif
                                    </td>
                                    {{-- Monto --}}
                                    <td class="px-6 py-4 font-mono font-medium text-gray-900">
                                        RD$ {{ number_format($item->valor, 2) }}
                                    </td>
                                    {{-- Stock --}}
                                    <td class="px-6 py-4">
                                        @php $stock = $item->stock; @endphp
                                        @if($stock > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">{{ $stock }} en stock</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100">Agotado</span>
                                        @endif
                                    </td>
                                    {{-- Acción --}}
                                    <td class="px-6 py-4 text-right">
                                        @if($usuario)
                                            <button type="button" 
                                                    onclick="abrirModalNotificacion({{ $usuario->id }}, '{{ $usuario->nombres }} {{ $usuario->apellidos }}', '{{ $usuario->email }}')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary hover:bg-hoverPrimary text-white rounded-lg text-xs font-semibold shadow-sm transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                </svg>
                                                Notificar
                                            </button>
                                        @else
                                            <button disabled class="px-3 py-1.5 bg-gray-100 text-gray-400 rounded-lg text-xs font-semibold cursor-not-allowed">
                                                Notificar
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($items->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-12 px-4 text-center">
                <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
                <p class="text-gray-500 font-medium text-sm">No se encontraron artículos o servicios.</p>
            </div>
        @endif


    </div>
</div>

{{-- Modal de Notificación --}}
<div id="modalNotificarDirecto" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,.45)">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="modalContainer">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800">Enviar Notificación Directa</h3>
            <button type="button" onclick="cerrarModalNotificacion()"
                class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.notificaciones.enviarDirecta') }}">
            @csrf
            <input type="hidden" name="usuario_id" id="modalUsuarioId">
            
            <div class="px-6 py-5 space-y-4">
                {{-- Info Usuario Destinatario --}}
                <div class="p-4 bg-primary/5 border border-primary/10 rounded-xl">
                    <p class="text-xs text-primary uppercase tracking-wider font-semibold mb-1">Destinatario</p>
                    <p class="text-sm font-bold text-gray-800" id="modalNombreUsuario"></p>
                    <p class="text-xs text-gray-500 mt-0.5" id="modalEmailUsuario"></p>
                </div>

                {{-- Canales de Envío --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Enviar vía</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2.5 cursor-pointer p-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 flex-1 justify-center transition-colors">
                            <input type="checkbox" name="canales[]" value="web" checked
                                   class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/30">
                            <span class="text-xs font-medium text-gray-700">Web / Móvil</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer p-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 flex-1 justify-center transition-colors">
                            <input type="checkbox" name="canales[]" value="email" checked
                                   class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/30">
                            <span class="text-xs font-medium text-gray-700">Correo</span>
                        </label>
                    </div>
                </div>

                {{-- Mensaje --}}
                <div>
                    <label for="modalMensaje" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Mensaje</label>
                    <textarea name="mensaje" id="modalMensaje" rows="4" required maxlength="500" 
                              placeholder="Escribe el mensaje para el usuario..."
                              class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex gap-3 justify-end bg-gray-50">
                <button type="button" onclick="cerrarModalNotificacion()"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-100 transition-colors font-medium">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-5 py-2 text-sm font-semibold bg-primary hover:bg-hoverPrimary text-white rounded-xl shadow-sm transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Enviar Notificación
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function abrirModalNotificacion(userId, name, email) {
        document.getElementById('modalUsuarioId').value = userId;
        document.getElementById('modalNombreUsuario').textContent = name;
        document.getElementById('modalEmailUsuario').textContent = email;
        document.getElementById('modalMensaje').value = '';

        const modal = document.getElementById('modalNotificarDirecto');
        const container = document.getElementById('modalContainer');

        modal.classList.remove('hidden');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function cerrarModalNotificacion() {
        const modal = document.getElementById('modalNotificarDirecto');
        const container = document.getElementById('modalContainer');

        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Cerrar modal al hacer click fuera del contenedor
    document.getElementById('modalNotificarDirecto').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalNotificacion();
        }
    });
</script>
@endpush
