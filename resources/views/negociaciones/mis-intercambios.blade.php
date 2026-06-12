@extends('layouts.app')
@section('title', 'Mis Intercambios - Cambialord')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
<div class="max-w-4xl mx-auto px-4">

    @include('components.btn-volver', ['backUrl' => route('home')])

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            Mis Intercambios
        </h1>
        <a href="{{ route('historial') }}?tab=intercambios"
           style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.4rem 0.8rem;font-size:0.78rem;font-weight:600;color:#6b7280;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:0.5rem;text-decoration:none;transition:background .2s;"
           onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Historial
        </a>
    </div>

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
            <div id="listaTarjetasPagoIntercambio" style="max-height:180px;overflow-y:auto;margin-bottom:0.75rem;">
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
                <p id="sinTarjetasMsgInt" style="text-align:center;color:#94a3b8;font-size:0.85rem;padding:0.5rem 0;">No tienes tarjetas guardadas.</p>
                @endforelse
            </div>

            {{-- Botón agregar tarjeta --}}
            <button type="button" onclick="toggleFormTarjetaInt()" id="btnToggleTarjetaInt"
                    style="width:100%;display:flex;align-items:center;justify-content:center;gap:0.5rem;border:2px dashed #fed7aa;background:#fff7ed;color:#c2410c;border-radius:0.75rem;padding:0.6rem;font-size:0.82rem;font-weight:600;cursor:pointer;margin-bottom:1rem;">
                + Agregar nueva tarjeta
            </button>

            {{-- Formulario nueva tarjeta (oculto) --}}
            <div id="formNuevaTarjetaInt" style="display:none;border:2px solid #fff7ed;border-radius:0.75rem;padding:1rem;margin-bottom:1rem;background:#fffbf5;">
                <p style="font-size:0.82rem;font-weight:700;color:#374151;margin:0 0 0.75rem;">Nueva tarjeta</p>
                <div id="tarjetaIntError" style="display:none;background:#fef2f2;border:1px solid #fca5a5;border-radius:0.5rem;padding:0.5rem;margin-bottom:0.75rem;color:#dc2626;font-size:0.78rem;"></div>
                <div style="display:grid;gap:0.5rem;">
                    <input id="ntIntNombre" type="text" placeholder="Nombre del titular" style="border:1.5px solid #e5e7eb;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.82rem;width:100%;box-sizing:border-box;">
                    <input id="ntIntNumero" type="text" placeholder="Número de tarjeta" maxlength="19" style="border:1.5px solid #e5e7eb;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.82rem;font-family:monospace;letter-spacing:0.1em;width:100%;box-sizing:border-box;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
                        <input id="ntIntMes" type="text" placeholder="MM" maxlength="2" style="border:1.5px solid #e5e7eb;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.82rem;text-align:center;width:100%;box-sizing:border-box;">
                        <input id="ntIntAnio" type="text" placeholder="YYYY" maxlength="4" style="border:1.5px solid #e5e7eb;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.82rem;text-align:center;width:100%;box-sizing:border-box;">
                    </div>
                </div>
                <div style="display:flex;gap:0.5rem;margin-top:0.75rem;">
                    <button type="button" onclick="guardarTarjetaInt()" id="btnGuardarTarjetaInt" style="flex:1;background:#f58634;color:#fff;border:none;border-radius:0.5rem;padding:0.5rem;font-size:0.82rem;font-weight:700;cursor:pointer;">Guardar</button>
                    <button type="button" onclick="toggleFormTarjetaInt()" style="flex:1;background:#f1f5f9;color:#475569;border:none;border-radius:0.5rem;padding:0.5rem;font-size:0.82rem;cursor:pointer;">Cancelar</button>
                </div>
            </div>

            <div id="seccionCvvInt" style="{{ $tarjetas->count() ? '' : 'display:none;' }}margin-bottom:1rem;">
                <label style="display:block;font-size:0.82rem;font-weight:700;color:#374151;margin-bottom:0.4rem;">CVV</label>
                <input type="password" id="cvvPagoIntercambio" maxlength="4" placeholder="3-4 dígitos"
                       style="width:7rem;border:2px solid #fff7ed;border-radius:0.75rem;padding:0.5rem 0.75rem;font-size:1rem;text-align:center;letter-spacing:0.2em;font-family:monospace;outline:none;background:#fff7ed;">
            </div>
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
var _pagoModoEntrega = null; // 'envio' cuando viene del botón "Enviar y pagar"

function abrirModalPagoIntercambio(negId, monto, itemNombre, modoEntrega) {
    _pagoNegId = negId;
    _pagoModoEntrega = modoEntrega || null;
    var m = document.getElementById('modalPagoIntercambio');
    m.style.display = 'flex';
    document.getElementById('pagoIntercambioError').style.display = 'none';
    document.getElementById('pagoIntercambioItem').textContent = itemNombre || 'Intercambio';

    // Actualizar título del modal si es "enviar y pagar"
    var tituloModal = document.querySelector('#modalPagoIntercambio h3');
    var subtituloModal = document.querySelector('#modalPagoIntercambio h3 + p');
    if (modoEntrega === 'envio') {
        if (tituloModal) tituloModal.textContent = '🚚 Enviar y pagar';
        if (subtituloModal) subtituloModal.textContent = 'Paga el envío para que los administradores gestionen la entrega';
        document.getElementById('btnConfirmarPagoIntercambio').textContent = '💳 Confirmar envío y pagar';
    } else {
        if (tituloModal) tituloModal.textContent = '💳 Pago de envío';
        if (subtituloModal) subtituloModal.textContent = 'Pago para completar el intercambio';
        document.getElementById('btnConfirmarPagoIntercambio').textContent = 'Pagar y completar';
    }

    var montoNum = parseFloat(monto || 0);
    var montoEl = document.getElementById('pagoIntercambioMonto');

    if (montoNum > 0) {
        montoEl.textContent = 'RD$ ' + montoNum.toLocaleString('es-DO', {minimumFractionDigits: 2});
    } else {
        montoEl.textContent = 'Calculando...';
        calcularMontoEnvioIntercambio(negId, montoEl);
    }
}

function calcularMontoEnvioIntercambio(negId, montoEl) {
    var municipio = window._municipioUsuario || '';

    function fetchCosto(mun) {
        fetch('/delivery/calcular?pueblo=' + encodeURIComponent(mun) + '&tipo_destinatario=persona&valor_articulo=0', {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var costo = parseFloat(d.costo_envio_total || 0);
            if (d.success && costo > 0) {
                montoEl.textContent = 'RD$ ' + costo.toLocaleString('es-DO', {minimumFractionDigits: 2});
                var spanMonto = document.getElementById('monto-envio-' + negId);
                if (spanMonto) spanMonto.textContent = 'RD$ ' + costo.toLocaleString('es-DO', {minimumFractionDigits: 2});
            } else {
                if (d.error_code === 'MISSING_DELIVERY_TARIFF') {
                    alert('El sistema espera por una definición para el cálculo de Análisis de costos de envío.');
                    window.location.reload();
                } else {
                    montoEl.textContent = d.message || 'Municipio sin zona de delivery configurada';
                }
            }
        })
        .catch(function() { montoEl.textContent = 'Error al calcular envío'; });
    }

    if (municipio) {
        fetchCosto(municipio);
    } else {
        // Obtener municipio del usuario desde el servidor
        fetch('/usuario/municipio', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.municipio) {
                window._municipioUsuario = d.municipio;
                fetchCosto(d.municipio);
            } else {
                montoEl.textContent = 'Registra una dirección para calcular el envío';
            }
        })
        .catch(function() { montoEl.textContent = 'Registra una dirección para calcular el envío'; });
    }
}

function cerrarModalPagoIntercambio() {
    document.getElementById('modalPagoIntercambio').style.display = 'none';
    _pagoNegId = null;
    _pagoModoEntrega = null;
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
        // Si viene del botón "Enviar y pagar", primero guardar modo_entrega=envio
        if (_pagoModoEntrega === 'envio') {
            var modoResp = await fetch('/negociaciones/' + _pagoNegId + '/modo-entrega', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ modo: 'envio' })
            });
            var modoData = await modoResp.json();
            if (!modoData.success) {
                errDiv.textContent = modoData.message || 'Error al registrar modo de entrega.';
                errDiv.style.display = 'block';
                btn.disabled = false;
                btn.textContent = textoOrig;
                return;
            }
        }

        // Procesar el pago
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
// Tarjeta nueva en modal de pago
function toggleFormTarjetaInt() {
    var f = document.getElementById('formNuevaTarjetaInt');
    f.style.display = f.style.display === 'none' ? 'block' : 'none';
    document.getElementById('tarjetaIntError').style.display = 'none';
}

// Formato número tarjeta
document.addEventListener('input', function(e) {
    if (e.target.id === 'ntIntNumero') {
        var v = e.target.value.replace(/\D/g, '').substring(0, 16);
        e.target.value = v.replace(/(.{4})/g, '$1 ').trim();
    }
});

async function guardarTarjetaInt() {
    var errDiv = document.getElementById('tarjetaIntError');
    var btn = document.getElementById('btnGuardarTarjetaInt');
    errDiv.style.display = 'none';

    var nombre = document.getElementById('ntIntNombre').value.trim();
    var numero = document.getElementById('ntIntNumero').value.replace(/\s/g, '');
    var mes = document.getElementById('ntIntMes').value.trim();
    var anio = document.getElementById('ntIntAnio').value.trim();

    if (!nombre || !numero || numero.length < 13 || !mes) {
        errDiv.textContent = 'Completa nombre, número y mes.';
        errDiv.style.display = 'block';
        return;
    }

    btn.disabled = true; btn.textContent = 'Guardando...';
    try {
        var fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('nombre_titular', nombre);
        fd.append('no_tarjeta', numero);
        fd.append('mes_expiracion', mes);
        if (anio) fd.append('anio_expiracion', anio);

        var resp = await fetch('{{ route("carrito.tarjetas_store") }}', {
            method: 'POST', body: fd, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        var data = await resp.json();

        if (!resp.ok && data.errors) {
            errDiv.textContent = Object.values(data.errors).flat()[0] || 'Datos inválidos.';
            errDiv.style.display = 'block';
            btn.disabled = false; btn.textContent = 'Guardar';
            return;
        }

        if (data.success) {
            var t = data.data;
            var last4 = t.last4 || numero.slice(-4);
            // Quitar mensaje "sin tarjetas"
            var msg = document.getElementById('sinTarjetasMsgInt');
            if (msg) msg.remove();
            // Agregar tarjeta a la lista
            var html = '<label style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;border:2px solid #e5e7eb;border-radius:0.75rem;cursor:pointer;margin-bottom:0.5rem;" onclick="this.querySelector(\'input\').checked=true;document.querySelectorAll(\'#listaTarjetasPagoIntercambio label\').forEach(l=>l.style.borderColor=\'#e5e7eb\');this.style.borderColor=\'#f58634\';">' +
                '<input type="radio" name="tarjeta_intercambio" value="' + t.id_tarjeta + '" style="accent-color:#f58634;">' +
                '<div style="flex:1;"><p style="font-size:0.85rem;font-weight:600;color:#1e293b;margin:0;">**** ' + last4 + '</p><p style="font-size:0.72rem;color:#94a3b8;margin:0;">' + nombre + '</p></div></label>';
            document.getElementById('listaTarjetasPagoIntercambio').insertAdjacentHTML('beforeend', html);
            // Seleccionar la nueva
            var labels = document.querySelectorAll('#listaTarjetasPagoIntercambio label');
            var ultima = labels[labels.length - 1];
            if (ultima) ultima.click();
            // Mostrar CVV
            document.getElementById('seccionCvvInt').style.display = '';
            // Limpiar y cerrar form
            ['ntIntNombre','ntIntNumero','ntIntMes','ntIntAnio'].forEach(function(id) { document.getElementById(id).value = ''; });
            toggleFormTarjetaInt();
        } else {
            errDiv.textContent = data.message || 'Error al guardar.';
            errDiv.style.display = 'block';
        }
    } catch (e) {
        errDiv.textContent = 'Error de conexión.';
        errDiv.style.display = 'block';
    }
    btn.disabled = false; btn.textContent = 'Guardar';
}

var _selectedStars = {};

// ============================================================
// CHAT DE NEGOCIACIONES
// ============================================================
var _allPredefinedMessages = @json($mensajesPredefinidos ?? []);
var _chatLoaded = {};

function toggleChat(negId) {
    var chatDiv = document.getElementById('chat-' + negId);
    if (!chatDiv) return;
    var isHidden = chatDiv.style.display === 'none';
    chatDiv.style.display = isHidden ? 'block' : 'none';
    if (isHidden && !_chatLoaded[negId]) {
        cargarMensajesChat(negId);
    }
}

function cargarMensajesChat(negId) {
    var container = document.getElementById('mensajes-' + negId);
    if (!container) return;
    var chatBtn = container.closest('[id^="chat-"]');
    var emisorId = chatBtn ? chatBtn.getAttribute('data-emisor') : null;
    var receptorId = chatBtn ? chatBtn.getAttribute('data-receptor') : null;
    if (!emisorId || !receptorId) {
        // Fallback: get from data attributes on the chat div
        var chatEl = document.getElementById('chat-' + negId);
        emisorId = chatEl.dataset.emisor;
        receptorId = chatEl.dataset.receptor;
    }
    container.innerHTML = '<p style="text-align:center;color:#94a3b8;font-size:0.78rem;">Cargando mensajes...</p>';
    fetch('/carrito/negociaciones/mensajes/' + emisorId + '/' + receptorId, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        _chatLoaded[negId] = true;
        var msgs = data.mensajes || [];
        if (msgs.length === 0) {
            container.innerHTML = '<p style="text-align:center;color:#94a3b8;font-size:0.78rem;">No hay mensajes aún.</p>';
            return;
        }
        var html = '';
        msgs.forEach(function(m) {
            var align = m.propio ? 'flex-end' : 'flex-start';
            var bg = m.propio ? '#dbeafe' : '#f1f5f9';
            var borderColor = m.propio ? '#93c5fd' : '#e2e8f0';
            html += '<div style="display:flex;justify-content:' + align + ';margin-bottom:0.4rem;">';
            html += '<div style="max-width:80%;background:' + bg + ';border:1px solid ' + borderColor + ';border-radius:0.5rem;padding:0.4rem 0.6rem;">';
            html += '<p style="font-size:0.78rem;color:#1e293b;margin:0;word-break:break-word;">' + escapeHtml(m.mensaje) + '</p>';
            html += '<p style="font-size:0.65rem;color:#94a3b8;margin:0.15rem 0 0;text-align:right;">' + (m.fecha || '') + '</p>';
            html += '</div></div>';
        });
        container.innerHTML = html;
        container.scrollTop = container.scrollHeight;
    })
    .catch(function() {
        container.innerHTML = '<p style="text-align:center;color:#ef4444;font-size:0.78rem;">Error al cargar mensajes. Intenta de nuevo.</p>';
    });
}

function escapeHtml(text) {
    var d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}

function filtrarMensajesPredefinidos(negId) {
    var tipoSelect = document.getElementById('tipo-accion-' + negId);
    var msgSelect = document.getElementById('msg-predefinido-' + negId);
    var preview = document.getElementById('preview-msg-' + negId);
    if (!tipoSelect || !msgSelect) return;

    var tipoSeleccionado = tipoSelect.value;
    // Determine user role for this negotiation
    var chatEl = document.getElementById('chat-' + negId);
    var userRol = chatEl ? chatEl.dataset.rol : 'general';

    msgSelect.innerHTML = '<option value="">-- Mensaje predefinido --</option>';
    if (preview) preview.value = '';

    _allPredefinedMessages.forEach(function(pm) {
        var matchTipo = !tipoSeleccionado || pm.tipo === tipoSeleccionado;
        var matchRol = pm.rol === 'general' || pm.rol === userRol;
        if (matchTipo && matchRol) {
            var opt = document.createElement('option');
            opt.value = pm.mensaje;
            opt.textContent = pm.titulo;
            opt.setAttribute('data-tipo', pm.tipo || '');
            msgSelect.appendChild(opt);
        }
    });
}

function previsualizarMensaje(negId) {
    var msgSelect = document.getElementById('msg-predefinido-' + negId);
    var preview = document.getElementById('preview-msg-' + negId);
    if (!msgSelect || !preview) return;
    preview.value = msgSelect.value;
}

function enviarMensajeChat(negId) {
    var preview = document.getElementById('preview-msg-' + negId);
    var tipoSelect = document.getElementById('tipo-accion-' + negId);
    var btn = document.getElementById('btn-enviar-' + negId);
    if (!preview || !preview.value.trim()) return;

    var mensaje = preview.value.trim();
    var tipoAccion = tipoSelect ? tipoSelect.value : '';

    btn.disabled = true;
    btn.textContent = 'Enviando...';

    fetch('/negociaciones/' + negId + '/mensaje', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ mensaje: mensaje, tipo_accion: tipoAccion || null })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.textContent = 'Enviar';
        if (data.success) {
            // Add the message bubble to the chat
            var container = document.getElementById('mensajes-' + negId);
            // Remove "no messages" placeholder if present
            var placeholder = container.querySelector('p[style*="text-align:center"]');
            if (placeholder && container.children.length === 1) container.innerHTML = '';
            var div = document.createElement('div');
            div.style.cssText = 'display:flex;justify-content:flex-end;margin-bottom:0.4rem;';
            div.innerHTML = '<div style="max-width:80%;background:#dbeafe;border:1px solid #93c5fd;border-radius:0.5rem;padding:0.4rem 0.6rem;">' +
                '<p style="font-size:0.78rem;color:#1e293b;margin:0;word-break:break-word;">' + escapeHtml(mensaje) + '</p>' +
                '<p style="font-size:0.65rem;color:#94a3b8;margin:0.15rem 0 0;text-align:right;">Ahora</p>' +
                '</div>';
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
            // Reset form
            preview.value = '';
            var msgSelect = document.getElementById('msg-predefinido-' + negId);
            if (msgSelect) msgSelect.value = '';
        } else {
            alert(data.message || 'Error al enviar mensaje.');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.textContent = 'Enviar';
        alert('Error de conexión al enviar mensaje.');
    });
}
// ============================================================

async function recalcularEnvio(negId) {
    var spanMonto = document.getElementById('monto-envio-' + negId);
    if (!spanMonto) { window.location.reload(); return; }
    spanMonto.textContent = 'Calculando...';
    calcularMontoEnvioIntercambio(negId, spanMonto);
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
