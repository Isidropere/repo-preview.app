var _intercambioItemId = null;
var _intercambioItemNombre = null;

function abrirModalIntercambio(itemId, itemNombre) {
    if (!window._usuarioAutenticado) {
        window.location.href = window._urlLogin || '/login';
        return;
    }
    _intercambioItemId = itemId;
    _intercambioItemNombre = itemNombre;

    // Mensaje predefinido
    document.getElementById('modalIntercambioItemNombre').textContent = 'Para: ' + itemNombre;
    document.getElementById('modalIntercambioMensaje').value = 'Propongo intercambiar tu producto/servicio: ' + itemNombre;
    document.getElementById('modalIntercambioCharCount').textContent = ('Propongo intercambiar tu producto/servicio: ' + itemNombre).length;
    document.getElementById('modalIntercambioError').classList.add('hidden');
    document.getElementById('modalIntercambio').classList.remove('hidden');
    cargarMisProductosIntercambio();
}

function cerrarModalIntercambio() {
    document.getElementById('modalIntercambio').classList.add('hidden');
    _intercambioItemId = null;
}

document.addEventListener('DOMContentLoaded', function() {
    var m = document.getElementById('modalIntercambio');
    if (m) m.addEventListener('click', function(e) {
        if (e.target === this) cerrarModalIntercambio();
    });
    var ta = document.getElementById('modalIntercambioMensaje');
    if (ta) ta.addEventListener('input', function() {
        document.getElementById('modalIntercambioCharCount').textContent = this.value.length;
    });
});

async function cargarMisProductosIntercambio() {
    var lista = document.getElementById('misProductosLista');
    lista.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">Cargando tus productos...</p>';
    try {
        var resp = await fetch(window._urlItemsUsuario, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            }
        });
        var items = await resp.json();
        if (!Array.isArray(items)) items = items.items || [];
        var list = items.filter(function(i) {
            return [1, 2, 3].includes(parseInt(i.tipo_trans));
        });
        if (!list.length) {
            lista.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">No tienes productos disponibles para intercambiar.</p>';
            return;
        }
        lista.innerHTML = list.map(function(i) {
            return '<label style="display:flex;align-items:center;gap:0.75rem;padding:0.65rem 0.75rem;border:2px solid #e5e7eb;border-radius:0.75rem;cursor:pointer;background:#fff;transition:all .15s;" ' +
                'onmouseover="this.style.borderColor=\'#10b981\';this.style.background=\'#f0fdf4\'" ' +
                'onmouseout="this.style.borderColor=\'#e5e7eb\';this.style.background=\'#fff\'">' +
                '<input type="checkbox" value="' + i.id_item + '" class="chk-intercambio" style="width:1.1rem;height:1.1rem;accent-color:#f58634;flex-shrink:0;cursor:pointer;">' +
                '<div style="flex:1;min-width:0;">' +
                '<p style="font-size:0.85rem;font-weight:700;color:#111827;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + i.item + '</p>' +
                '<p style="font-size:0.72rem;color:#059669;font-weight:600;margin:0.1rem 0 0;">' + (i.valor ? 'RD$ ' + parseFloat(i.valor).toLocaleString('es-DO') : 'Solo intercambio') + '</p>' +
                '</div>' +
                '<span style="font-size:0.65rem;background:#d1fae5;color:#065f46;padding:0.2rem 0.5rem;border-radius:9999px;font-weight:700;flex-shrink:0;">OFRECER</span>' +
                '</label>';
        }).join('');
    } catch (e) {
        lista.innerHTML = '<p class="text-center text-red-400 text-sm py-4">Error al cargar productos.</p>';
    }
}

async function enviarPropuestaIntercambio() {
    var mensaje = document.getElementById('modalIntercambioMensaje').value.trim();
    var checks = Array.from(document.querySelectorAll('.chk-intercambio:checked')).map(function(c) {
        return c.value;
    });
    var errDiv = document.getElementById('modalIntercambioError');
    var btn = document.getElementById('btnEnviarIntercambio');

    errDiv.classList.add('hidden');
    if (!mensaje) {
        errDiv.textContent = 'El mensaje es obligatorio.';
        errDiv.classList.remove('hidden');
        return;
    }
    if (!checks.length) {
        errDiv.textContent = 'Selecciona al menos un producto para ofrecer.';
        errDiv.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Enviando...';

    try {
        var resp = await fetch(window._urlNegStore, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({
                item_id: _intercambioItemId,
                mensaje: mensaje,
                items_ofrecidos: checks
            })
        });
        var data = await resp.json();
        if (data.status === 'ok' || data.success) {
            cerrarModalIntercambio();
            var toast = document.createElement('div');
            toast.className = 'fixed bottom-6 right-6 bg-emerald-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-semibold z-[9999] flex items-center gap-2';
            toast.innerHTML = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Propuesta enviada correctamente';
            document.body.appendChild(toast);
            if (typeof actualizarBadgeIntercambios === 'function') actualizarBadgeIntercambios();
            setTimeout(function() {
                toast.remove();
            }, 3500);
        } else {
            errDiv.textContent = data.message || 'Error al enviar la propuesta.';
            errDiv.classList.remove('hidden');
        }
    } catch (e) {
        errDiv.textContent = 'Error de conexión. Intenta de nuevo.';
        errDiv.classList.remove('hidden');
    }
    btn.disabled = false;
    btn.innerHTML = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg> Enviar propuesta';
}