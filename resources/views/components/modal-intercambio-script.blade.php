<script>
var _intercambioItemId = null;
var _urlItemsUsuario = '{{ route("items_usuario") }}';
var _urlNegStore = '{{ route("negociaciones.store") }}';

function abrirModalIntercambio(itemId, itemNombre) {
    if (!window._usuarioAutenticado) { window.location.href = '{{ route("login") }}'; return; }
    _intercambioItemId = itemId;
    document.getElementById('modalIntercambioItemNombre').textContent = 'Para: ' + itemNombre;
    document.getElementById('modalIntercambioError').classList.add('hidden');
    document.getElementById('modalIntercambioMensaje').value = '';
    document.getElementById('modalIntercambioMonto').value = '';
    document.getElementById('modalIntercambioCharCount').textContent = '0';
    document.getElementById('modalIntercambio').classList.remove('hidden');
    cargarMisProductosIntercambio();
}

function cerrarModalIntercambio() {
    document.getElementById('modalIntercambio').classList.add('hidden');
    _intercambioItemId = null;
}

document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('modalIntercambio');
    if (modal) modal.addEventListener('click', function(e) { if (e.target === this) cerrarModalIntercambio(); });
    var ta = document.getElementById('modalIntercambioMensaje');
    if (ta) ta.addEventListener('input', function() {
        document.getElementById('modalIntercambioCharCount').textContent = this.value.length;
    });
});

async function cargarMisProductosIntercambio() {
    var lista = document.getElementById('misProductosLista');
    lista.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">Cargando...</p>';
    try {
        var resp = await fetch(_urlItemsUsuario, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.csrfToken } });
        var items = await resp.json();
        if (!Array.isArray(items)) items = items.items || [];
        var intercambiables = items.filter(function(i) { return [2,3].includes(parseInt(i.tipo_trans)); });
        if (!intercambiables.length) {
            lista.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">No tienes productos para intercambiar.</p>';
            return;
        }
        lista.innerHTML = intercambiables.map(function(i) {
            return '<label class="flex items-center gap-3 p-3 border-2 border-gray-100 rounded-xl cursor-pointer hover:border-emerald-300 hover:bg-emerald-50 transition-all">' +
                '<input type="checkbox" value="' + i.id_item + '" class="chk-intercambio h-4 w-4 text-emerald-600 rounded flex-shrink-0">' +
                '<div class="flex-1 min-w-0"><p class="text-sm font-semibold text-gray-800 truncate">' + i.item + '</p>' +
                '<p class="text-xs text-gray-400">' + (i.valor ? 'RD$ ' + parseFloat(i.valor).toLocaleString('es-DO') : 'Sin precio') + '</p></div></label>';
        }).join('');
    } catch(e) {
        lista.innerHTML = '<p class="text-center text-red-400 text-sm py-4">Error al cargar.</p>';
    }
}

async function enviarPropuestaIntercambio() {
    var mensaje = document.getElementById('modalIntercambioMensaje').value.trim();
    var monto   = document.getElementById('modalIntercambioMonto').value;
    var checks  = Array.from(document.querySelectorAll('.chk-intercambio:checked')).map(function(c) { return c.value; });
    var errDiv  = document.getElementById('modalIntercambioError');
    var btn     = document.getElementById('btnEnviarIntercambio');

    errDiv.classList.add('hidden');
    if (!mensaje) { errDiv.textContent = 'El mensaje es obligatorio.'; errDiv.classList.remove('hidden'); return; }
    if (!checks.length) { errDiv.textContent = 'Selecciona al menos un producto.'; errDiv.classList.remove('hidden'); return; }

    btn.disabled = true; btn.textContent = 'Enviando...';
    try {
        var resp = await fetch(_urlNegStore, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
            body: JSON.stringify({ item_id: _intercambioItemId, mensaje: mensaje, monto_oferta: monto || null, items_ofrecidos: checks })
        });
        var data = await resp.json();
        if (data.status === 'ok' || data.success) {
            cerrarModalIntercambio();
            var toast = document.createElement('div');
            toast.className = 'fixed bottom-6 right-6 bg-emerald-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-semibold z-[9999]';
            toast.textContent = '✅ Propuesta enviada';
            document.body.appendChild(toast);
            setTimeout(function() { toast.remove(); }, 3000);
        } else {
            errDiv.textContent = data.message || 'Error al enviar.';
            errDiv.classList.remove('hidden');
        }
    } catch(e) {
        errDiv.textContent = 'Error de conexión.';
        errDiv.classList.remove('hidden');
    }
    btn.disabled = false; btn.textContent = '🤝 Enviar propuesta';
}
</script>
