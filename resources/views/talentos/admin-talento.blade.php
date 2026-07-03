@extends('layouts.app')

@section('title', 'Cambialord - Mis talentos')

@section('content')
<main class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4">
        @auth
        <div class="mb-4">
            <a href="javascript:history.back()" class="inline-flex items-center text-sm text-gray-500 hover:text-primary">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver
            </a>
        </div>
        @endauth

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Mis Talentos</h1>
                <p class="text-sm text-gray-500 mt-1">Gestiona tus servicios y habilidades</p>
            </div>
            <a href="{{ route('items.talento_create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo talento
            </a>
        </div>

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-5 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-5 text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            {{-- Filtros --}}
            <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="searchInput" placeholder="Buscar talento..." class="w-full pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary bg-gray-50" style="padding-left:2.5rem;">
                </div>
                <select id="statusFilter" class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600 bg-gray-50">
                    <option value="all">Todos</option>
                    <option value="1">Activos</option>
                    <option value="2">Inactivos</option>
                </select>
                <select id="typeFilter" class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600 bg-gray-50">
                    <option value="all">Tipo</option>
                    <option value="1">Venta</option>
                    <option value="2">Intercambio</option>
                    <option value="3">Ambos</option>
                </select>
            </div>

            {{-- Lista --}}
            <div class="divide-y divide-gray-100">
                @forelse($items as $item)
                @continue($item->id_categoria_item != 29)
                <div class="product-item p-4 hover:bg-gray-50/50 transition-colors" data-status="{{ $item->estatus }}" data-type="{{ $item->tipo_trans }}">
                    <div class="flex items-center gap-4">
                        {{-- Imagen --}}
                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0 cursor-pointer border border-gray-200" onclick="abrirImagen(this.querySelector('img')?.src)">
                            @if($item->todasLasImagenes->isNotEmpty())
                                @php
                                    $image = $item->todasLasImagenes->firstWhere('orden_visualizacion', 1) ?? $item->todasLasImagenes->first();
                                    $imageUrl = (!empty($image->ruta) && !empty($image->nombre))
                                        ? \App\Helpers\ImageHelper::urlMedia(trim(str_replace('\\', '/', $image->ruta), '/'), $image->nombre)
                                        : asset('imgs/defaults/servicio_default.svg');
                                @endphp
                                <img src="{{ $imageUrl }}" alt="{{ $item->item }}" class="w-full h-full object-cover" loading="lazy" width="200" height="200" onerror="this.onerror=null;this.src='{{ asset('imgs/defaults/servicio_default.svg') }}'">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <h3 class="text-sm font-semibold text-gray-800 truncate">{{ $item->item }}</h3>
                                <span class="flex-shrink-0 px-2 py-0.5 text-[10px] font-semibold rounded-full
                                    {{ $item->estatus == 1 ? 'bg-green-100 text-green-700' : ($item->estatus == 2 ? 'bg-red-100 text-red-700' : ($item->estatus == 0 ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700')) }}">
                                    {{ $item->estatus == 1 ? 'Activo' : ($item->estatus == 2 ? 'Inactivo' : ($item->estatus == 0 ? 'Pendiente de pago' : 'Pausado')) }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-0.5 text-xs text-gray-500">
                                <span class="font-semibold text-secondary text-sm">RD$ {{ number_format($item->valor, 2) }}</span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    {{ $item->views_count ?? 0 }}
                                </span>
                                <span>{{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : '' }}</span>
                            </div>
                        </div>

                        {{-- Acciones --}}
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            @if($item->estatus == 0)
                            <a href="{{ route('talento.pago.iniciar', $item->id_item) }}" class="inline-flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition-all" title="Pagar Registro">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                Pagar
                            </a>
                            @endif
                            <a href="{{ route('items.VerDetalle', $item->slug) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Ver">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('items.talentoedit', $item->slug) }}" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form action="{{ route('items.destroy', $item->slug) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="button" onclick="confirmDelete(this)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <h3 class="text-base font-semibold text-gray-700">No tienes talentos publicados</h3>
                    <p class="text-sm text-gray-400 mt-1 mb-4">Comienza ofreciendo tus habilidades</p>
                    <a href="{{ route('items.talento_create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-blue-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nuevo Talento
                    </a>
                </div>
                @endforelse
            </div>

            @if($items->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $items->appends(request()->except('page'))->links('vendor.pagination.custom') }}
            </div>
            @endif
        </div>
    </div>
</main>

{{-- Lightbox --}}
<div id="imgLightbox" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-8 cursor-pointer" onclick="this.classList.add('hidden')">
    <img id="imgLightboxSrc" src="" alt="" class="max-w-sm max-h-[60vh] rounded-xl shadow-2xl object-contain bg-white p-1">
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const search = document.getElementById('searchInput');
    const status = document.getElementById('statusFilter');
    const type = document.getElementById('typeFilter');
    const items = document.querySelectorAll('.product-item');
    function normalizar(texto) {
        return (texto || '').normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
    }
    function filter() {
        const q = normalizar(search.value), s = status.value, t = type.value;
        items.forEach(el => {
            const title = normalizar(el.querySelector('h3').textContent);
            el.style.display = (title.includes(q) && (s === 'all' || el.dataset.status === s) && (t === 'all' || el.dataset.type === t)) ? '' : 'none';
        });
    }
    search.addEventListener('input', filter);
    status.addEventListener('change', filter);
    type.addEventListener('change', filter);
});
function confirmDelete(btn) {
    window._deleteForm = btn.closest('form');
    document.getElementById('modalEliminarTalento').classList.remove('hidden');
}
function abrirImagen(src) { if (!src) return; document.getElementById('imgLightboxSrc').src = src; document.getElementById('imgLightbox').classList.remove('hidden'); }
</script>

{{-- Modal confirmación eliminar talento --}}
<div id="modalEliminarTalento" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800">¿Eliminar este talento?</h3>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 text-sm text-amber-800 leading-relaxed">
            <p class="font-semibold mb-1">⚠️ Aviso importante</p>
            <p>Cambialord RD <strong>no se hace responsable</strong> de los talentos o servicios eliminados. Si se borra el talento y le queda inventario, el mismo no se podrá restablecer ni se hará una devolución del dinero.</p>
            <p class="mt-2">Esta acción es <strong>irreversible</strong>.</p>
        </div>

        <div class="flex gap-3">
            <button type="button"
                onclick="document.getElementById('modalEliminarTalento').classList.add('hidden')"
                class="flex-1 border-2 border-gray-200 text-gray-600 hover:bg-gray-50 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                Cancelar
            </button>
            <button type="button"
                onclick="document.getElementById('modalEliminarTalento').classList.add('hidden'); window._deleteForm.submit();"
                class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-xl text-sm font-bold transition-colors">
                Sí, eliminar
            </button>
        </div>
    </div>
</div>
@endpush
