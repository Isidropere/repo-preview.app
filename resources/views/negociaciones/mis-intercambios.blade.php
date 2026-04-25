@extends('layouts.app')
@section('title', 'Mis Intercambios - Cambialord')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
<div class="max-w-4xl mx-auto px-4">

    @include('components.btn-volver', ['backUrl' => route('home')])

    <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        Mis Intercambios
    </h1>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm mb-4">{{ session('error') }}</div>
    @endif

    {{-- Tabs --}}
    <div class="flex gap-2 mb-6 border-b border-gray-200">
        <button onclick="mostrarTab('recibidas')" id="tab-recibidas"
                class="tab-btn px-4 py-2 text-sm font-semibold border-b-2 border-emerald-600 text-emerald-700 -mb-px">
            📥 Recibidas
            @if($comoReceptor->whereIn('estado',['Inicial','contraoferta'])->count() > 0)
            <span class="ml-1 bg-emerald-600 text-white text-xs px-1.5 py-0.5 rounded-full">{{ $comoReceptor->whereIn('estado',['Inicial','contraoferta'])->count() }}</span>
            @endif
        </button>
        <button onclick="mostrarTab('enviadas')" id="tab-enviadas"
                class="tab-btn px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 -mb-px">
            📤 Enviadas
        </button>
    </div>

    {{-- TAB: Recibidas (como receptor) --}}
    <div id="panel-recibidas">
        @forelse($comoReceptor as $neg)
        @include('negociaciones.partials.tarjeta-negociacion', ['neg' => $neg, 'rol' => 'receptor'])
        @empty
        <div class="text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            <p class="text-sm">No has recibido propuestas de intercambio aún.</p>
        </div>
        @endforelse
    </div>

    {{-- TAB: Enviadas (como emisor) --}}
    <div id="panel-enviadas" class="hidden">
        @forelse($comoEmisor as $neg)
        @include('negociaciones.partials.tarjeta-negociacion', ['neg' => $neg, 'rol' => 'emisor'])
        @empty
        <div class="text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            <p class="text-sm">No has enviado propuestas de intercambio aún.</p>
        </div>
        @endforelse
    </div>

</div>
</div>

{{-- MODAL PAGO ENVÍO INTERCAMBIO --}}
<div id="modalPagoIntercambio" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.65);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:1.5rem;width:100%;max-width:28rem;box-shadow:0 20px 60px rgba(0,0,0,.2);overflow:hidden;max-height:calc(100vh - 2rem);display:flex;flex-direction:column;margin:auto;">
        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#f58634,#fb923c);padding:1.25rem 1.5rem;flex-shrink:0;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <div style="width:2.5rem;height:2.5rem;background:rgba(255,255,255,0.25);border-radius:0.75rem;display:flex;align-items:center;justify-content:center;">
                        <svg style="width:1.25rem;height:1.25rem;color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size:1rem;font-weight:800;color:#fff;margin:0;">💳 Pago de envío</h3>
                        <p style="font-size:0.75rem;color:rgba(255,255,255,0.85);margin:0;">Pago para completar el intercambio</p>
                    </div>
                </div>
                <button onclick="cerrarModalPagoIntercambio()" style="width:2rem;height:2rem;background:rgba(255,255,255,0.25);border:none;border-radius:50%;color:#fff;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;">✕</button>
            </div>
        </div>
        {{-- Body --}}
        <div style="padding:1.25rem 1.5rem;overflow-y:auto;flex:1;min-height:0;">
            <div id="pagoIntercambioError" style="display:none;background:#fef2f2;border:1.5px solid #fca5a5;border-radius:0.75rem;padding:0.75rem 1rem;margin-bottom:1rem;color:#dc2626;font-size:0.82rem;font-weight:600;"></div>

            {{-- Resumen del pago --}}
            <div id="resumenPagoIntercambio" style="background:#fff7ed;border:1.5px solid #fed7aa;border-radius:0.75rem;padding:1rem;margin-bottom:1rem;">
                <p style="font-size:0.72rem;color:#9a3412;text-transform:uppercase;letter-spacing:0.05em;margin:0 0 0.4rem;font-weight:700;">Resumen del intercambio</p>
                <p id="pagoIntercambioItem" style="font-size:0.85rem;font-weight:600;color:#1e293b;margin:0 0 0.5rem;"></p>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:0.82rem;color:#64748b;">Monto de envío</span>
                    <span id="pagoIntercambioMonto" style="font-size:1.15rem;font-weight:800;color:#c2410c;"></span>
                </div>
            </div>

            <p style="font-size:0.85rem;font-weight:700;color:#374151;margin-bottom:0.75rem;">Selecciona una tarjeta</p>
            <div id="listaTarjetasPagoIntercambio" style="max-height:180px;overflow-y:auto;margin-bottom:1rem;">
                @forelse($tarjetas as $tarjeta)
                <label style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;border:2px solid #e5e7eb;border-radius:0.75rem;cursor:pointer;margin-bottom:0.5rem;transition:all .15s;"
                       onclick="this.querySelector('input').checked=true;document.querySelectorAll('#listaTarjetasPagoIntercambio label').forEach(l=>l.style.borderColor='#e5e7eb');this.style.borderColor='#f58634';">
                    <input type="radio" name="tarjeta_intercambio" value="{{ $tarjeta->id_tarjeta }}" style="accent-color:#f58634;">
                    <div style="flex:1;min-width:0;">
                        <p style="font-size:0.85rem;font-weight:600;color:#1e293b;margin:0;">**** {{ $tarjeta->last4 ?? substr($tarjeta->no_tarjeta ?? '', -4) }}</p>
                        <p style="font-size:0.72rem;color:#94a3b8;margin:0;">{{ $tarjeta->nombre_titular ?? 'Titular' }}</p>
                    </div>
                </label>
                @empty
                <p style="text-align:center;color:#94a3b8;font-size:0.85rem;padding:1rem 0;">No tienes tarjetas guardadas. Agrega una desde el checkout.</p>
                @endforelse
            </div>

            @if($tarjetas->count())
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:0.82rem;font-weight:700;color:#374151;margin-bottom:0.4rem;">CVV</label>
                <input type="password" id="cvvPagoIntercambio" maxlength="4" placeholder="3-4 dígitos"
                       style="width:7rem;border:2px solid #fff7ed;border-radius:0.75rem;padding:0.5rem 0.75rem;font-size:1rem;text-align:center;letter-spacing:0.2em;font-family:monospace;outline:none;background:#fff7ed;">
            </div>
            @endif
        </div>
        {{-- Footer --}}
        <div style="padding:1rem 1.5rem;border-top:1px solid #fff7ed;flex-shrink:0;background:#fafafa;display:flex;gap:0.75rem;">
            <button onclick="cerrarModalPagoIntercambio()" style="flex:1;border:2px solid #e5e7eb;background:#fff;color:#6b7280;border-radius:0.875rem;padding:0.75rem;font-size:0.85rem;font-weight:700;cursor:pointer;">Cancelar</button>
            <button id="btnConfirmarPagoIntercambio" onclick="procesarPagoIntercambio()"
                    style="flex:2;background:#f58634;color:#fff;border:none;border-radius:0.875rem;padding:0.75rem;font-size:0.9rem;font-weight:800;cursor:pointer;box-shadow:0 4px 14px rgba(245,134,52,0.4);">
                Pagar y completar
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
window._municipioUsuario = @json($costoEnvioPorNeg['_municipio'] ?? '');
var _pagoNegId = null;

function abrirModalPagoIntercambio(negId, monto, itemNombre) {
    _pagoNegId = negId;
    var m = document.getElementById('modalPagoIntercambio');
    m.style.display = 'flex';
    document.getElementById('pagoIntercambioError').style.display = 'none';
    document.getElementById('pagoIntercambioItem').textContent = itemNombre || 'Intercambio';
    document.getElementById('pagoIntercambioMonto').textContent = 'RD$ ' + parseFloat(monto || 0).toLocaleString('es-DO', {minimumFractionDigits: 2});
}

function cerrarModalPagoIntercambio() {
    document.getElementById('modalPagoIntercambio').style.display = 'none';
    _pagoNegId = null;
}

document.getElementById('modalPagoIntercambio')?.addEventListener('click', function(e) {
    if (e.target === this) cerrarModalPagoIntercambio();
});

async function procesarPagoIntercambio() {
    var errDiv = document.getElementById('pagoIntercambioError');
    var btn = document.getElementById('btnConfirmarPagoIntercambio');
    errDiv.style.display = 'none';

    var sel = document.querySelector('input[name="tarjeta_intercambio"]:checked');
    if (!sel) { errDiv.textContent = 'Selecciona una tarjeta.'; errDiv.style.display = 'block'; return; }

    var cvv = document.getElementById('cvvPagoIntercambio')?.value.trim();
    if (!cvv || cvv.length < 3) { errDiv.textContent = 'Ingresa el CVV.'; errDiv.style.display = 'block'; return; }

    btn.disabled = true;
    var textoOrig = btn.textContent;
    btn.textContent = 'Procesando...';

    try {
        var resp = await fetch('/negociaciones/' + _pagoNegId + '/pago', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ id_tarjeta: sel.value, cvv: cvv })
        });
        var data = await resp.json();
        if (data.success || resp.ok) {
            btn.textContent = '✅ Pago registrado';
            btn.style.background = '#16a34a';
            setTimeout(function() { window.location.reload(); }, 1200);
        } else {
            errDiv.textContent = data.message || data.error || 'Error al procesar el pago.';
            errDiv.style.display = 'block';
            btn.disabled = false;
            btn.textContent = textoOrig;
        }
    } catch (e) {
        errDiv.textContent = 'Error de conexión.';
        errDiv.style.display = 'block';
        btn.disabled = false;
        btn.textContent = textoOrig;
    }
}

function mostrarTab(tab) {
    document.getElementById('panel-recibidas').classList.toggle('hidden', tab !== 'recibidas');
    document.getElementById('panel-enviadas').classList.toggle('hidden', tab !== 'enviadas');
    document.getElementById('tab-recibidas').className = tab === 'recibidas'
        ? 'tab-btn px-4 py-2 text-sm font-semibold border-b-2 border-emerald-600 text-emerald-700 -mb-px'
        : 'tab-btn px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 -mb-px';
    document.getElementById('tab-enviadas').className = tab === 'enviadas'
        ? 'tab-btn px-4 py-2 text-sm font-semibold border-b-2 border-emerald-600 text-emerald-700 -mb-px'
        : 'tab-btn px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 -mb-px';
}

// Star rating helpers
var _selectedStars = {};

async function recalcularEnvio(negId) {
    // Recargar la página para recalcular desde el servidor
    window.location.reload();
}
function highlightStars(negId, count) {
    var container = document.getElementById('stars-' + negId);
    if (!container) return;
    var labels = container.querySelectorAll('label');
    labels.forEach(function(l, i) { l.style.color = i < count ? '#f59e0b' : '#d1d5db'; });
}
function resetStars(negId) {
    var sel = _selectedStars[negId] || 0;
    highlightStars(negId, sel);
}
function selectStar(negId, count) {
    _selectedStars[negId] = count;
    highlightStars(negId, count);
}
</script>
@endpush
