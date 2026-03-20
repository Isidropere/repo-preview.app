<div id="negociacionesNotificacionesModal"
     class="fixed inset-0 bg-black/50 z-[9999] hidden flex items-center justify-center">

    <div class="bg-white w-full max-w-lg rounded-xl shadow-xl relative" style="max-height:90vh;display:flex;flex-direction:column;">

        <!-- Header fijo -->
        <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;background:#f8fafc;flex-shrink:0;">
            <!-- Título + cerrar -->
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
                <div style="display:flex;align-items:center;gap:0.5rem;">
                    <div style="width:1.75rem;height:1.75rem;background:#d1fae5;border-radius:0.45rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg style="width:0.9rem;height:0.9rem;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <h2 style="font-size:0.9rem;font-weight:700;color:#0f172a;margin:0;">Negociación</h2>
                </div>
                <button onclick="cerrarNegociacionesModal()"
                        style="background:#f1f5f9;border:none;color:#64748b;cursor:pointer;font-size:1rem;width:2rem;height:2rem;border-radius:50%;display:flex;align-items:center;justify-content:center;">✕</button>
            </div>
            <!-- Tarjeta del item negociado (se rellena por JS) -->
            <div id="negModalItemCard" style="display:none;align-items:center;gap:0.75rem;background:#fff;border:1px solid #e2e8f0;border-radius:0.75rem;padding:0.6rem 0.75rem;">
                <img id="negModalItemImg" src="" alt="" style="width:52px;height:52px;object-fit:cover;border-radius:0.5rem;flex-shrink:0;border:1px solid #f1f5f9;">
                <div style="min-width:0;flex:1;">
                    <p id="negModalItemNombre" style="font-size:0.8rem;font-weight:700;color:#0f172a;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></p>
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.2rem;flex-wrap:wrap;">
                        <span id="negModalItemPrecio" style="font-size:0.78rem;font-weight:800;color:#2563eb;display:none;"></span>
                        <span id="negModalItemBadge" style="font-size:0.68rem;font-weight:600;color:#059669;background:#d1fae5;padding:0.1rem 0.45rem;border-radius:9999px;display:none;">Intercambio</span>
                        <span id="negModalItemSku" style="font-size:0.68rem;color:#94a3b8;"></span>
                    </div>
                </div>
            </div>
            <!-- Placeholder mientras carga -->
            <div id="negModalItemLoading" style="display:flex;align-items:center;gap:0.75rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:0.75rem;padding:0.6rem 0.75rem;">
                <div style="width:52px;height:52px;background:#e2e8f0;border-radius:0.5rem;flex-shrink:0;animation:pulse 1.5s infinite;"></div>
                <div style="flex:1;">
                    <div style="height:12px;background:#e2e8f0;border-radius:4px;width:60%;margin-bottom:6px;animation:pulse 1.5s infinite;"></div>
                    <div style="height:10px;background:#e2e8f0;border-radius:4px;width:40%;animation:pulse 1.5s infinite;"></div>
                </div>
            </div>
        </div>

        <!-- Body scrollable -->
        <div id="negociacionesBody" style="padding:1rem 1.25rem;overflow-y:auto;flex:1;font-size:0.85rem;">
            <p style="color:#94a3b8;text-align:center;">Selecciona una notificación</p>
        </div>

    </div>
</div>

<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
</style>
