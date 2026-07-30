@extends('layouts.admin')

@section('title', 'Aprobaciones de prod y serv - talen')

@section('content')
<div class="min-h-screen bg-slate-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Centro de Aprobaciones</h1>
                <p class="text-sm text-slate-500 mt-1">Revisa y aprueba o rechaza imágenes de artículos, fotos de perfil y carga de nuevos productos.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-8">
            {{-- ── IMÁGENES DE ARTÍCULOS ── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden transition-shadow hover:shadow-md">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800">Imágenes de Artículos</h2>
                        @if($imagenesItems->count() > 0)
                            <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2.5 py-1 rounded-full border border-amber-200">
                                {{ $imagenesItems->count() }} pendiente{{ $imagenesItems->count() !== 1 ? 's' : '' }}
                            </span>
                        @endif
                    </div>
                    @if($imagenesItems->count() > 0)
                        <form method="POST" action="{{ route('admin.imagenes.items.aprobarTodas') }}" onsubmit="return confirm('¿Aprobar todas las imágenes de artículos pendientes?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Aprobar todas
                            </button>
                        </form>
                    @endif
                </div>

                @if($imagenesItems->isEmpty())
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-slate-500 font-medium">No hay imágenes de artículos pendientes de aprobación.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                                    <th class="px-6 py-4 font-semibold">Imagen</th>
                                    <th class="px-6 py-4 font-semibold">Artículo</th>
                                    <th class="px-6 py-4 font-semibold">Usuario</th>
                                    <th class="px-6 py-4 font-semibold">Tipo</th>
                                    <th class="px-6 py-4 font-semibold text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($imagenesItems as $img)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="h-16 w-16 rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-slate-100">
                                            <img src="{{ asset($img->ruta . '/' . ($img->nombre ?? '')) }}" alt="Imagen" class="h-full w-full object-cover" loading="lazy" onerror="this.src='{{ asset('images/placeholder.png') }}'">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">{{ $img->item->item ?? '—' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500">{{ $img->item->id_user ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-500">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-800">
                                            {{ $img->tipo ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <form method="POST" action="{{ route('admin.imagenes.items.aprobar', $img->id_imagen) }}">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors shadow-sm">
                                                    Aprobar
                                                </button>
                                            </form>
                                            <details class="relative inline-block">
                                                <summary class="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors shadow-sm cursor-pointer list-none">
                                                    Rechazar
                                                </summary>
                                                <div class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-slate-200 p-4 z-10">
                                                    <form method="POST" action="{{ route('admin.imagenes.items.rechazar', $img->id_imagen) }}">
                                                        @csrf
                                                        <label class="block text-xs font-bold text-slate-700 mb-2">Motivo del rechazo <span class="text-red-500">*</span></label>
                                                        <textarea name="motivo_rechazo" required rows="3" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm p-2" placeholder="Describe el motivo..."></textarea>
                                                        <button type="submit" class="mt-3 w-full px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                                            Confirmar
                                                        </button>
                                                    </form>
                                                </div>
                                            </details>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- ── FOTOS DE PERFIL ── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden transition-shadow hover:shadow-md">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800">Fotos de Perfil</h2>
                        @if($fotosUsuarios->count() > 0)
                            <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2.5 py-1 rounded-full border border-amber-200">
                                {{ $fotosUsuarios->count() }} pendiente{{ $fotosUsuarios->count() !== 1 ? 's' : '' }}
                            </span>
                        @endif
                    </div>
                    @if($fotosUsuarios->count() > 0)
                        <form method="POST" action="{{ route('admin.imagenes.perfiles.aprobarTodas') }}" onsubmit="return confirm('¿Aprobar todas las fotos de perfil pendientes?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Aprobar todas
                            </button>
                        </form>
                    @endif
                </div>

                @if($fotosUsuarios->isEmpty())
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-slate-500 font-medium">No hay fotos de perfil pendientes de aprobación.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                                    <th class="px-6 py-4 font-semibold">Foto</th>
                                    <th class="px-6 py-4 font-semibold">Usuario</th>
                                    <th class="px-6 py-4 font-semibold">Email</th>
                                    <th class="px-6 py-4 font-semibold text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($fotosUsuarios as $user)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="h-14 w-14 rounded-full overflow-hidden border-2 border-slate-200 shadow-sm bg-slate-100">
                                            <img src="{{ \App\Helpers\ImageHelper::urlPerfil($user->profile_photo_path ?? $user->foto_perfil) }}" alt="Perfil" class="h-full w-full object-cover" loading="lazy" onerror="this.src='{{ asset('imgs/defaults/profile_default.svg') }}'">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">{{ $user->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500">{{ $user->email }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <form method="POST" action="{{ route('admin.imagenes.perfiles.aprobar', $user->id) }}">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors shadow-sm">
                                                    Aprobar
                                                </button>
                                            </form>
                                            <details class="relative inline-block">
                                                <summary class="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors shadow-sm cursor-pointer list-none">
                                                    Rechazar
                                                </summary>
                                                <div class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-slate-200 p-4 z-10">
                                                    <form method="POST" action="{{ route('admin.imagenes.perfiles.rechazar', $user->id) }}">
                                                        @csrf
                                                        <label class="block text-xs font-bold text-slate-700 mb-2">Motivo del rechazo <span class="text-red-500">*</span></label>
                                                        <textarea name="motivo_rechazo" required rows="3" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm p-2" placeholder="Describe el motivo..."></textarea>
                                                        <button type="submit" class="mt-3 w-full px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                                            Confirmar
                                                        </button>
                                                    </form>
                                                </div>
                                            </details>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- ── CARGA DE PRODUCTOS ── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden transition-shadow hover:shadow-md">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800">Aprobación de Carga de Productos</h2>
                        @if($productosPendientes->count() > 0)
                            <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2.5 py-1 rounded-full border border-amber-200">
                                {{ $productosPendientes->count() }} pendiente{{ $productosPendientes->count() !== 1 ? 's' : '' }}
                            </span>
                        @endif
                    </div>
                </div>

                @if($productosPendientes->isEmpty())
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-slate-500 font-medium">No hay cargas de productos pendientes de aprobación.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                                    <th class="px-6 py-4 font-semibold w-1/4">Producto y Usuario</th>
                                    <th class="px-6 py-4 font-semibold w-1/2">Detalles a Aprobar</th>
                                    <th class="px-6 py-4 font-semibold text-center w-1/4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($productosPendientes as $producto)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-6 align-top">
                                        <div class="flex flex-col gap-1">
                                            <strong class="text-slate-900 text-sm">{{ $producto->item }}</strong>
                                            <span class="text-xs text-slate-500 font-mono bg-slate-100 px-2 py-0.5 rounded w-max">ID: {{ $producto->id_item }}</span>
                                            <span class="text-sm text-slate-600 mt-2 flex items-center gap-1">
                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                {{ $producto->usuario->name ?? 'N/A' }}
                                            </span>
                                            <span class="text-sm text-slate-600 flex items-center gap-1">
                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                {{ \Carbon\Carbon::parse($producto->fecha)->format('d/m/Y') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 align-top">
                                        <form id="form-aprobar-{{ $producto->id_item }}" method="POST" action="{{ route('admin.imagenes.productos.aprobar', $producto->id_item) }}" class="space-y-4">
                                            @csrf
                                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-600 mb-1">Categoría</label>
                                                    <select name="id_categoria_item" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                                        @foreach($categorias as $categoria)
                                                            <option value="{{ $categoria->id_categoria_item }}" {{ $producto->id_categoria_item == $categoria->id_categoria_item ? 'selected' : '' }}>
                                                                {{ $categoria->categoria }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-600 mb-1">Peso (lbs)</label>
                                                    <input type="number" step="0.01" name="peso_lbs" value="{{ $producto->peso_lbs }}" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-600 mb-1">Alto (cm)</label>
                                                    <input type="number" step="0.01" name="alto_cm" value="{{ $producto->alto_cm }}" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-600 mb-1">Ancho (cm)</label>
                                                    <input type="number" step="0.01" name="ancho_cm" value="{{ $producto->ancho_cm }}" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-600 mb-1">Profundo (cm)</label>
                                                    <input type="number" step="0.01" name="profundo_cm" value="{{ $producto->profundo_cm }}" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-600 mb-1">Descripción / Presentación</label>
                                                <textarea name="presentacion" rows="3" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm resize-y">{{ $producto->presentacion }}</textarea>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="px-6 py-6 align-top">
                                        <div class="flex flex-col gap-2 w-full max-w-[160px] mx-auto">
                                            <button type="button" onclick="document.getElementById('form-aprobar-{{ $producto->id_item }}').submit()" class="w-full px-4 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-sm transition-colors flex items-center justify-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Aprobar
                                            </button>
                                            
                                            <details class="relative w-full">
                                                <summary class="w-full px-4 py-2 text-sm font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg shadow-sm transition-colors cursor-pointer list-none flex items-center justify-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    Rechazar
                                                </summary>
                                                <div class="absolute right-0 top-full mt-2 w-[300px] bg-white rounded-xl shadow-xl border border-slate-200 p-4 z-20">
                                                    <form method="POST" action="{{ route('admin.imagenes.productos.rechazar', $producto->id_item) }}">
                                                        @csrf
                                                        <label class="block text-xs font-bold text-slate-700 mb-2">Motivo del rechazo <span class="text-red-500">*</span></label>
                                                        <textarea name="motivo_rechazo" required rows="3" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm p-2" placeholder="Describe el motivo..."></textarea>
                                                        <button type="submit" class="mt-3 w-full px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                                            Confirmar rechazo
                                                        </button>
                                                    </form>
                                                </div>
                                            </details>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<style>
/* Ocultar la flecha por defecto de details/summary en navegadores Webkit y Firefox */
details > summary {
    list-style: none;
}
details > summary::-webkit-details-marker {
    display: none;
}
</style>
@endsection
