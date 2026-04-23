<div id="negociacionesNotificacionesModal"
     class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4"
     style="background:rgba(0,0,0,0.65);backdrop-filter:blur(4px);">

    <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl flex flex-col overflow-hidden" style="max-height:92vh;">

        {{-- Header con gradiente naranja --}}
        <div style="background:linear-gradient(135deg,#f58634 0%,#f58634 50%,#fb923c 100%);padding:1.25rem 1.5rem;flex-shrink:0;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <div style="width:2.5rem;height:2.5rem;background:rgba(255,255,255,0.25);border-radius:0.75rem;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
                        <svg style="width:1.25rem;height:1.25rem;color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-size:1rem;font-weight:800;color:#fff;margin:0;letter-spacing:-0.01em;">🤝 Negociación</h3>
                        <p style="font-size:0.75rem;color:rgba(255,255,255,0.85);margin:0.1rem 0 0;font-weight:500;">Intercambio con negociación</p>
                    </div>
                </div>
                <button onclick="cerrarNegociacionesModal()"
                        style="width:2rem;height:2rem;background:rgba(255,255,255,0.25);border:none;border-radius:50%;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1rem;transition:background .15s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.35)'"
                        onmouseout="this.style.background='rgba(255,255,255,0.25)'">✕</button>
            </div>

            {{-- Tarjeta del item negociado --}}
            <div id="negModalItemCard" style="display:none;align-items:center;gap:0.75rem;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);border-radius:0.75rem;padding:0.6rem 0.75rem;backdrop-filter:blur(4px);">
                <img id="negModalItemImg" src="" alt="" style="width:52px;height:52px;object-fit:cover;border-radius:0.5rem;flex-shrink:0;border:1px solid rgba(255,255,255,0.3);">
                <div style="min-width:0;flex:1;">
                    <p id="negModalItemNombre" style="font-size:0.8rem;font-weight:700;color:#fff;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></p>
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.2rem;flex-wrap:wrap;">
                        <span id="negModalItemPrecio" style="font-size:0.78rem;font-weight:800;color:#fff;display:none;"></span>
                        <span id="negModalItemBadge" style="font-size:0.68rem;font-weight:600;color:#fff;background:rgba(255,255,255,0.25);padding:0.1rem 0.45rem;border-radius:9999px;display:none;">Intercambio</span>
                        <span id="negModalItemSku" style="font-size:0.68rem;color:rgba(255,255,255,0.7);"></span>
                    </div>
                </div>
            </div>

            {{-- Placeholder mientras carga --}}
            <div id="negModalItemLoading" style="display:flex;align-items:center;gap:0.75rem;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);border-radius:0.75rem;padding:0.6rem 0.75rem;">
                <div style="width:52px;height:52px;background:rgba(255,255,255,0.2);border-radius:0.5rem;flex-shrink:0;animation:pulse 1.5s infinite;"></div>
                <div style="flex:1;">
                    <div style="height:12px;background:rgba(255,255,255,0.2);border-radius:4px;width:60%;margin-bottom:6px;animation:pulse 1.5s infinite;"></div>
                    <div style="height:10px;background:rgba(255,255,255,0.2);border-radius:4px;width:40%;animation:pulse 1.5s infinite;"></div>
                </div>
            </div>
        </div>

        {{-- Body scrollable --}}
        <div id="negociacionesBody" style="padding:1.25rem 1.5rem;overflow-y:auto;flex:1;font-size:0.85rem;">
            <p style="color:#94a3b8;text-align:center;">Selecciona una notificación</p>
        </div>

    </div>
</div>

<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
</style>
