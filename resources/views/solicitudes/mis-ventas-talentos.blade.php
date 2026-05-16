@extends('layouts.app')
@section('title', 'Mis Ventas de Talentos - Cambialord')

@section('content')
<div class="min-h-screen bg-gray-50 py-5">
    <div class="max-w-3xl mx-auto px-4">

        @include('components.btn-volver', ['backUrl' => route('tu_cuenta')])

        <div class="flex items-center justify-between mb-5">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Mis Ventas de Talentos
            </h1>
        </div>

        <div id="toast-mvt" style="display:none;position:fixed;top:1.25rem;right:1.25rem;z-index:9999;padding:0.75rem 1.25rem;border-radius:0.75rem;font-size:0.85rem;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,.15);transition:opacity .3s;"></div>

        {{-- Tabs --}}
        <div class="flex gap-2 mb-5 border-b border-gray-200">
            <button onclick="mostrarTabSol('pendientes')" id="tab-pendientes"
                class="px-4 py-2 text-sm font-medium border-b-2 border-primary text-primary transition-all">
                Pendientes
                @if($pendientes->count() > 0)
                <span id="badge-pendientes-count" style="background:#fef3c7;color:#92400e;font-size:0.7rem;font-weight:700;padding:2px 8px;border-radius:999px;margin-left:4px;">{{ $pendientes->count() }}</span>
                @endif
            </button>
            <button onclick="mostrarTabSol('procesadas')" id="tab-procesadas"
                class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-primary transition-all">
                Procesadas
                <span id="badge-procesadas-count" style="background:#f3f4f6;color:#6b7280;font-size:0.7rem;font-weight:600;padding:2px 8px;border-radius:999px;margin-left:4px;">{{ $procesadas->count() }}</span>
            </button>
        </div>

        {{-- Panel Pendientes --}}
        <div id="panel-pendientes">
            @forelse($pendientes as $sol)
                @include('solicitudes.partials.tarjeta-solicitud', ['sol' => $sol, 'mostrarAcciones' => true])
            @empty
            <div style="text-align:center;padding:3rem;color:#9ca3af;" id="empty-pendientes">
                <svg style="width:3rem;height:3rem;margin:0 auto 0.75rem;color:#d1d5db;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                <p class="text-sm">No tienes solicitudes pendientes.</p>
            </div>
            @endforelse
        </div>

        {{-- Panel Procesadas --}}
        <div id="panel-procesadas" class="hidden">
            @forelse($procesadas as $sol)
                @include('solicitudes.partials.tarjeta-solicitud', ['sol' => $sol, 'mostrarAcciones' => false])
            @empty
            <div style="text-align:center;padding:3rem;color:#9ca3af;">
                <p class="text-sm">No hay solicitudes procesadas.</p>
            </div>
            @endforelse
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function mostrarTabSol(tab) {
    var tabs = ['pendientes', 'procesadas'];
    tabs.forEach(function(t) {
        document.getElementById('panel-' + t).classList.add('hidden');
        var btn = document.getElementById('tab-' + t);
        btn.classList.remove('border-primary', 'text-primary');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    document.getElementById('panel-' + tab).classList.remove('hidden');
    var btn = document.getElementById('tab-' + tab);
    btn.classList.add('border-primary', 'text-primary');
    btn.classList.remove('border-transparent', 'text-gray-500');
}

function mostrarToast(msg, tipo) {
    var t = document.getElementById('toast-mvt');
    t.textContent = msg;
    t.style.background = tipo === 'success' ? '#d1fae5' : '#fee2e2';
    t.style.color       = tipo === 'success' ? '#065f46' : '#991b1b';
    t.style.display = 'block';
    t.style.opacity = '1';
    setTimeout(function() {
        t.style.opacity = '0';
        setTimeout(function() { t.style.display = 'none'; }, 300);
    }, 3500);
}

function accionSolicitud(id, accion, cardEl) {
    var url = '/solicitudes-servicio/' + id + '/' + accion + '-json';
    var csrf = document.querySelector('meta[name="csrf-token"]')?.content
            || window.csrfToken
            || '';

    // Deshabilitar botones
    cardEl.querySelectorAll('button[data-accion]').forEach(function(b) { b.disabled = true; });

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
    })
    .then(function(r) {
        if (!r.ok && r.status !== 422) {
            return r.text().then(function(txt) {
                throw new Error('HTTP ' + r.status + ': ' + txt.substring(0, 200));
            });
        }
        return r.json();
    })
    .then(function(data) {
        if (data.success) {
            mostrarToast(data.message, 'success');

            // Actualizar badge de estado en la tarjeta
            var badgeEl = cardEl.querySelector('.estado-badge');
            if (badgeEl) {
                if (accion === 'aprobar') {
                    badgeEl.textContent = 'Aprobada';
                    badgeEl.style.cssText = 'background:#d1fae5;color:#065f46;font-size:0.7rem;font-weight:700;padding:3px 10px;border-radius:999px;flex-shrink:0;';
                } else {
                    badgeEl.textContent = 'Rechazada';
                    badgeEl.style.cssText = 'background:#fee2e2;color:#991b1b;font-size:0.7rem;font-weight:700;padding:3px 10px;border-radius:999px;flex-shrink:0;';
                }
            }

            // Quitar botones de acción
            var accionesEl = cardEl.querySelector('.acciones-solicitud');
            if (accionesEl) accionesEl.remove();

            // Actualizar contadores de tabs
            var pendCount = document.querySelectorAll('#panel-pendientes .solicitud-card').length;
            var badge = document.getElementById('badge-pendientes-count');
            if (badge) {
                if (pendCount <= 0) badge.style.display = 'none';
                else badge.textContent = pendCount;
            }

            // Si no quedan pendientes, mostrar mensaje vacío
            if (pendCount === 0) {
                var panel = document.getElementById('panel-pendientes');
                if (panel && !panel.querySelector('#empty-pendientes')) {
                    panel.innerHTML = '<div id="empty-pendientes" style="text-align:center;padding:3rem;color:#9ca3af;"><p class="text-sm">No tienes solicitudes pendientes.</p></div>';
                }
            }
        } else {
            mostrarToast(data.message || 'Error al procesar.', 'error');
            cardEl.querySelectorAll('button[data-accion]').forEach(function(b) { b.disabled = false; });
        }
    })
    .catch(function(err) {
        console.error('accionSolicitud error:', err);
        mostrarToast('Error: ' + err.message, 'error');
        cardEl.querySelectorAll('button[data-accion]').forEach(function(b) { b.disabled = false; });
    });
}
</script>
@endpush
