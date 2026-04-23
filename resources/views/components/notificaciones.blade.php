<div class="relative flex items-center">
    <button id="hs-dropdown-floating-dark"
        type="button"
        class="hs-dropdown-toggle relative flex items-center justify-center p-2 text-primary hover:text-hoverPrimary">

        <span class="absolute top-1 right-1 flex">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
            <span id="contadorNotificaciones"
                  class="relative inline-flex rounded-full px-1.5 text-white text-xs bg-secondary leading-none">0</span>
        </span>

        <svg class="h-7 w-7 fill-primary hover:fill-hoverPrimary"
             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
           <path d="M19 13.586V10c0-3.217-2.185-5.927-5.145-6.742C13.562 2.52 12.846 2 12 2s-1.562.52-1.855 1.258C7.185 4.074 5 6.783 5 10v3.586l-1.707 1.707A.996.996 0 0 0 3 16v2a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1v-2a.996.996 0 0 0-.293-.707L19 13.586zM19 17H5v-.586l1.707-1.707A.996.996 0 0 0 7 14v-4c0-2.757 2.243-5 5-5s5 2.243 5 5v4c0 .266.105.52.293.707L19 16.414V17zm-7 5a2.98 2.98 0 0 0 2.818-2H9.182A2.98 2.98 0 0 0 12 22z"></path>
        </svg>
    </button>

    <div id="panelNotificaciones"
        class="absolute right-0 mt-2 w-72 bg-white border border-gray-200 rounded-lg shadow-xl z-[9999] hidden">
        <div id="listaNotificaciones"
             class="max-h-64 overflow-y-auto divide-y divide-gray-100 p-2">
        </div>
    </div>
</div>

@push('scripts')
<script>
// ─────────────────────────────────────────────────────────────
// ESTADO GLOBAL DEL MODAL DE NEGOCIACIONES
// ─────────────────────────────────────────────────────────────
const NEG = {
    itemId:              null,   // id_item negociado
    receptorId:          null,   // id del otro usuario
    miId:                {{ Auth::id() ?? 'null' }},
    mensajesPredefinidos: [],    // todos los mensajes de la BD
    rolActual:           null,   // 'emisor' | 'receptor' (se determina al abrir)
};

// ─────────────────────────────────────────────────────────────
// PANEL DE NOTIFICACIONES (campana)
// ─────────────────────────────────────────────────────────────
const btnCampana = document.getElementById('hs-dropdown-floating-dark');
const panel      = document.getElementById('panelNotificaciones');

btnCampana?.addEventListener('click', (e) => {
    e.stopPropagation();
    panel.classList.toggle('hidden');
});

document.addEventListener('click', (e) => {
    if (!panel?.contains(e.target) && !btnCampana?.contains(e.target)) {
        panel?.classList.add('hidden');
    }
});

// ─────────────────────────────────────────────────────────────
// CARGAR NOTIFICACIONES
// ─────────────────────────────────────────────────────────────
async function cargarNotificaciones() {
    try {
        const resp = await fetch("{{ route('notificaciones.listar') }}");
        const data = await resp.json();
        const mensajes = data.mensajes || [];
        const lista    = document.getElementById('listaNotificaciones');
        const contador = document.getElementById('contadorNotificaciones');

        lista.innerHTML = '';

        if (!mensajes.length) {
            lista.innerHTML = `<div class="p-4 text-gray-500 text-center text-sm">No tienes mensajes nuevos</div>`;
            contador.textContent = 0;
            return;
        }

        contador.textContent = mensajes.filter(n => n.leido === 0).length;

        lista.innerHTML = mensajes.map(n => `
            <div class="mb-3 px-3">
                <div class="px-4 py-3 rounded-xl border ${n.leido === 0 ? 'bg-blue-50 border-blue-200' : 'bg-gray-100 border-gray-200'} cursor-pointer hover:bg-gray-50"
                     onclick="verMensajeRelacionado(${n.id}, ${n.id_emisor}, ${n.id_oferta ?? 'null'})">
                    <p class="text-sm font-semibold">
                        ${n.id_oferta ? 'Oferta #' + n.id_oferta : 'Mensaje nuevo'}
                        ${n.leido === 0 ? '<span class="ml-1 inline-block w-2 h-2 rounded-full bg-blue-500"></span>' : ''}
                    </p>
                    <p class="text-sm text-gray-700 truncate">${n.mensaje}</p>
                    <small class="text-xs text-gray-500">${new Date(n.created_at).toLocaleString()}</small>
                </div>
            </div>
        `).join('');

    } catch (e) {
        console.error('Error cargando notificaciones', e);
    }
}

// ─────────────────────────────────────────────────────────────
// CLICK EN UNA NOTIFICACIÓN
// ─────────────────────────────────────────────────────────────
async function verMensajeRelacionado(idNotificacion, idEmisor, itemId) {
    try {
        await fetch(`/notificaciones/leido/${idNotificacion}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        panel.classList.add('hidden');
        await cargarNotificaciones();
        abrirNegociacionRelacionada(idEmisor, itemId);
    } catch (e) {
        console.error(e);
    }
}

// ─────────────────────────────────────────────────────────────
// ABRIR MODAL DE NEGOCIACIÓN
// ─────────────────────────────────────────────────────────────
async function abrirNegociacionRelacionada(idEmisor, itemId) {
    abrirNegociacionesModal();

    NEG.itemId     = itemId ?? null;
    NEG.receptorId = idEmisor ?? null;
    NEG.rolActual  = null;

    // Renderizar estructura del body (una sola vez)
    document.getElementById('negociacionesBody').innerHTML = plantillaNegociacion();

    // Mostrar skeleton del item
    document.getElementById('negModalItemCard').style.display    = 'none';
    document.getElementById('negModalItemLoading').style.display = 'flex';

    const mensajesContainer = document.getElementById('mensajesContainer');

    try {
        const resp = await fetch(`/carrito/negociaciones/mensajes/${idEmisor}/${NEG.miId}`);
        const data = await resp.json();

        // Si no teníamos item_id desde la notificación, usarlo del historial
        if (!NEG.itemId && data.item_id) {
            NEG.itemId = data.item_id;
        }

        // Determinar rol: si el emisor de la notificación es distinto a mí,
        // yo soy el receptor (recibí la propuesta). Si soy el emisor, soy emisor.
        // La notificación llega al receptor, así que por defecto soy receptor.
        NEG.rolActual = (idEmisor !== NEG.miId) ? 'receptor' : 'emisor';

        // Cargar tarjeta del item en el header
        await cargarTarjetaItem(NEG.itemId);

        // Cargar mensajes predefinidos filtrados por rol (sin duplicar)
        NEG.mensajesPredefinidos = data.mensajesPredefinidos || [];
        poblarSelectPredefinidos(NEG.mensajesPredefinidos, NEG.rolActual);

        // Renderizar historial de mensajes
        const mensajes = data.mensajes || [];
        if (!mensajes.length) {
            mensajesContainer.innerHTML = `<p class="text-gray-400 text-center text-sm">No hay mensajes aún</p>`;
        } else {
            mensajesContainer.innerHTML = '';
            mensajes.forEach(msg => {
                const div = document.createElement('div');
                div.className = `mb-2 p-2 rounded-lg max-w-[75%] text-sm ${msg.propio ? 'ml-auto bg-blue-100' : 'mr-auto bg-gray-100'}`;
                div.innerHTML = `<p class="m-0">${msg.mensaje}</p><small class="block text-right text-gray-500 text-xs mt-1">${msg.fecha}</small>`;
                mensajesContainer.appendChild(div);
            });
            mensajesContainer.scrollTop = mensajesContainer.scrollHeight;
        }

    } catch (e) {
        mensajesContainer.innerHTML = `<p class="text-red-500 text-center text-sm">Error al cargar la negociación</p>`;
        document.getElementById('negModalItemLoading').style.display = 'none';
        console.error(e);
    }
}

// ─────────────────────────────────────────────────────────────
// CARGAR TARJETA DEL ITEM EN EL HEADER
// ─────────────────────────────────────────────────────────────
async function cargarTarjetaItem(itemId) {
    const itemCard    = document.getElementById('negModalItemCard');
    const itemLoading = document.getElementById('negModalItemLoading');

    if (!itemId) {
        itemLoading.style.display = 'none';
        return;
    }

    try {
        const resp = await fetch(`/items/info/${itemId}`);
        if (!resp.ok) throw new Error('No encontrado');
        const d = await resp.json();

        const img    = document.getElementById('negModalItemImg');
        const nombre = document.getElementById('negModalItemNombre');
        const precio = document.getElementById('negModalItemPrecio');
        const badge  = document.getElementById('negModalItemBadge');
        const sku    = document.getElementById('negModalItemSku');

        if (img)    { img.src = d.imagen ?? ''; img.alt = d.nombre ?? ''; }
        if (nombre) nombre.textContent = d.nombre ?? 'Producto';
        if (precio && d.valor) {
            precio.textContent = 'RD$ ' + parseFloat(d.valor).toLocaleString('es-DO', { minimumFractionDigits: 2 });
            precio.style.display = 'inline';
        }
        if (badge && [2, 3].includes(d.tipo_trans)) badge.style.display = 'inline';
        if (sku) sku.textContent = 'SKU #' + (d.id ?? itemId);

        itemLoading.style.display = 'none';
        itemCard.style.display    = 'flex';
    } catch (_) {
        itemLoading.style.display = 'none';
    }
}

// ─────────────────────────────────────────────────────────────
// POBLAR SELECT DE MENSAJES PREDEFINIDOS (sin duplicar)
// Filtra por rol: muestra los del rol actual + los 'general'
// ─────────────────────────────────────────────────────────────
function poblarSelectPredefinidos(mensajes, rol) {
    const select = document.getElementById('mensajePredefinido');
    if (!select) return;

    // Limpiar completamente antes de poblar
    select.innerHTML = '<option value="">-- Selecciona un mensaje --</option>';

    const filtrados = mensajes.filter(m => m.rol === rol || m.rol === 'general');

    filtrados.forEach(msg => {
        const opt = document.createElement('option');
        opt.value       = msg.mensaje;
        opt.textContent = msg.titulo;
        select.appendChild(opt);
    });
}

// ─────────────────────────────────────────────────────────────
// PLANTILLA HTML DEL BODY DEL MODAL
// ─────────────────────────────────────────────────────────────
function plantillaNegociacion() {
    return `
        <div id="mensajesContainer"
             style="height:14rem;overflow-y:auto;border:2px solid #fff7ed;border-radius:1rem;padding:0.75rem;background:#fff7ed;margin-bottom:1rem;">
            <p style="color:#9ca3af;text-align:center;font-size:0.85rem;">Cargando mensajes...</p>
        </div>

        <div style="margin-bottom:1rem;">
            <p style="font-size:0.82rem;font-weight:700;color:#374151;margin-bottom:0.6rem;display:flex;align-items:center;gap:0.4rem;">
                <span style="width:1.25rem;height:1.25rem;background:#f58634;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:0.65rem;font-weight:800;flex-shrink:0;">1</span>
                Mensaje predefinido
            </p>
            <select id="mensajePredefinido"
                    style="width:100%;border:2px solid #fff7ed;border-radius:0.75rem;padding:0.5rem 0.75rem;font-size:0.85rem;background:#fff7ed;outline:none;color:#374151;box-sizing:border-box;transition:border-color .15s;"
                    onfocus="this.style.borderColor='#f58634'" onblur="this.style.borderColor='#fff7ed'">
                <option value="">-- Selecciona un mensaje --</option>
            </select>
        </div>

        <div style="margin-bottom:1rem;">
            <p style="font-size:0.82rem;font-weight:700;color:#374151;margin-bottom:0.6rem;display:flex;align-items:center;gap:0.4rem;">
                <span style="width:1.25rem;height:1.25rem;background:#f58634;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:0.65rem;font-weight:800;flex-shrink:0;">2</span>
                Mensaje
                <span style="font-size:0.72rem;font-weight:400;color:#9ca3af;">(se llena al seleccionar)</span>
            </p>
            <textarea id="mensaje" rows="3" readonly
                      style="width:100%;resize:none;border:2px solid #fff7ed;border-radius:0.75rem;padding:0.75rem 1rem;font-size:0.85rem;background:#fff7ed;color:#374151;outline:none;box-sizing:border-box;cursor:not-allowed;"
                      placeholder="Se llenará al seleccionar un mensaje predefinido..."></textarea>
        </div>

        <div style="margin-bottom:1.25rem;">
            <p style="font-size:0.82rem;font-weight:700;color:#374151;margin-bottom:0.6rem;display:flex;align-items:center;gap:0.4rem;">
                <span style="width:1.25rem;height:1.25rem;background:#f58634;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:0.65rem;font-weight:800;flex-shrink:0;">3</span>
                Monto de la oferta <span style="font-size:0.72rem;font-weight:400;color:#9ca3af;">(opcional)</span>
            </p>
            <input type="number" id="montoOferta" min="0" step="0.01" placeholder="Ej. 1,000.00"
                   style="width:100%;border:2px solid #fff7ed;border-radius:0.75rem;padding:0.5rem 0.75rem;font-size:0.85rem;background:#fff7ed;outline:none;box-sizing:border-box;transition:border-color .15s;"
                   onfocus="this.style.borderColor='#f58634'" onblur="this.style.borderColor='#fff7ed'">
        </div>

        <div style="display:flex;gap:0.75rem;">
            <button onclick="cerrarNegociacionesModal()"
                    style="flex:1;border:2px solid #e5e7eb;background:#fff;color:#6b7280;border-radius:0.875rem;padding:0.75rem;font-size:0.85rem;font-weight:700;cursor:pointer;transition:all .15s;"
                    onmouseover="this.style.background='#f9fafb';this.style.borderColor='#d1d5db'"
                    onmouseout="this.style.background='#fff';this.style.borderColor='#e5e7eb'">
                Cancelar
            </button>
            <button id="enviarNegociacionBtn"
                    style="flex:2;background:linear-gradient(135deg,#f58634,#f58634);color:#fff;border:none;border-radius:0.875rem;padding:0.75rem 1.25rem;font-size:0.9rem;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:0.5rem;box-shadow:0 4px 14px rgba(245,134,52,0.4);transition:all .15s;letter-spacing:-0.01em;"
                    onmouseover="this.style.boxShadow='0 6px 20px rgba(245,134,52,0.5)';this.style.transform='translateY(-1px)'"
                    onmouseout="this.style.boxShadow='0 4px 14px rgba(245,134,52,0.4)';this.style.transform='translateY(0)'">
                <svg style="width:1.1rem;height:1.1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Enviar
            </button>
        </div>
    `;
}

// ─────────────────────────────────────────────────────────────
// SINCRONIZAR SELECT → TEXTAREA
// ─────────────────────────────────────────────────────────────
document.addEventListener('change', e => {
    if (e.target.id === 'mensajePredefinido' && e.target.value) {
        const textarea = document.getElementById('mensaje');
        if (textarea) textarea.value = e.target.value;
    }
});

// ─────────────────────────────────────────────────────────────
// ENVIAR NEGOCIACIÓN
// ─────────────────────────────────────────────────────────────
document.addEventListener('click', async function (e) {
    if (e.target.id !== 'enviarNegociacionBtn') return;

    const mensaje = document.getElementById('mensaje')?.value?.trim();
    const monto   = document.getElementById('montoOferta')?.value;

    if (!mensaje) {
        alert('Debes escribir un mensaje');
        return;
    }

    try {
        const resp = await fetch('{{ route('carrito.save_negociaciones') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                item_id:     NEG.itemId,
                receptor_id: NEG.receptorId,
                mensaje:     mensaje,
                monto_oferta: monto || null,
            })
        });

        const data = await resp.json();

        if (!resp.ok) {
            alert(data.message || 'Error al enviar');
            return;
        }

        // Limpiar campos
        document.getElementById('mensaje').value      = '';
        document.getElementById('montoOferta').value  = '';
        document.getElementById('mensajePredefinido').value = '';

        // Recargar mensajes del hilo
        await abrirNegociacionRelacionada(NEG.receptorId, NEG.itemId);

    } catch (err) {
        console.error(err);
        alert('Error de conexión');
    }
});

// ─────────────────────────────────────────────────────────────
// ABRIR / CERRAR MODAL
// ─────────────────────────────────────────────────────────────
function abrirNegociacionesModal() {
    document.getElementById('negociacionesNotificacionesModal')?.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function cerrarNegociacionesModal() {
    document.getElementById('negociacionesNotificacionesModal')?.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// ─────────────────────────────────────────────────────────────
// INICIALIZACIÓN
// ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', cargarNotificaciones);

if (!window._notifIntervalSet) {
    window._notifIntervalSet = true;
    setInterval(cargarNotificaciones, 60000);
}
</script>
@endpush
