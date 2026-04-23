<div id="modalIntercambio" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.65);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl flex flex-col overflow-hidden" style="max-height:92vh;">

        {{-- Header con gradiente --}}
        <div style="background:linear-gradient(135deg,#f58634 0%,#f58634 50%,#fb923c 100%);padding:1.25rem 1.5rem;flex-shrink:0;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <div style="width:2.5rem;height:2.5rem;background:rgba(255,255,255,0.25);border-radius:0.75rem;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
                        <svg style="width:1.25rem;height:1.25rem;color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-size:1rem;font-weight:800;color:#fff;margin:0;letter-spacing:-0.01em;">🤝 Intercambio sin Negociación</h3>
                        <p id="modalIntercambioItemNombre" style="font-size:0.75rem;color:rgba(255,255,255,0.85);margin:0.1rem 0 0;font-weight:500;"></p>
                    </div>
                </div>
                <button onclick="cerrarModalIntercambio()"
                        style="width:2rem;height:2rem;background:rgba(255,255,255,0.25);border:none;border-radius:50%;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1rem;transition:background .15s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.35)'"
                        onmouseout="this.style.background='rgba(255,255,255,0.25)'">✕</button>
            </div>
        </div>

        {{-- Body --}}
        <div style="padding:1.25rem 1.5rem;overflow-y:auto;flex:1;">

            {{-- Error --}}
            <div id="modalIntercambioError" class="hidden" style="background:#fef2f2;border:1.5px solid #fca5a5;border-radius:0.75rem;padding:0.75rem 1rem;margin-bottom:1rem;color:#dc2626;font-size:0.82rem;font-weight:600;"></div>

            {{-- Mis productos --}}
            <div style="margin-bottom:1.25rem;">
                <p style="font-size:0.82rem;font-weight:700;color:#374151;margin-bottom:0.6rem;display:flex;align-items:center;gap:0.4rem;">
                    <span style="width:1.25rem;height:1.25rem;background:#f58634;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:0.65rem;font-weight:800;flex-shrink:0;">1</span>
                    Selecciona los productos que ofreces a cambio:
                </p>
                <div id="misProductosLista"
                     style="overflow-y:auto;max-height:200px;min-height:60px;border:2px solid #fff7ed;border-radius:1rem;padding:0.5rem;background:#fff7ed;display:flex;flex-direction:column;gap:0.4rem;">
                    <p style="text-align:center;color:#9ca3af;font-size:0.82rem;padding:1rem 0;">Cargando...</p>
                </div>
            </div>

            {{-- Mensaje --}}
            <div>
                <p style="font-size:0.82rem;font-weight:700;color:#374151;margin-bottom:0.6rem;display:flex;align-items:center;gap:0.4rem;">
                    <span style="width:1.25rem;height:1.25rem;background:#f58634;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:0.65rem;font-weight:800;flex-shrink:0;">2</span>
                    Mensaje de propuesta <span style="color:#ef4444;">*</span>
                </p>
                <textarea id="modalIntercambioMensaje" rows="3" maxlength="500" readonly
                          style="width:100%;border:2px solid #fff7ed;border-radius:0.75rem;padding:0.75rem 1rem;font-size:0.85rem;resize:none;outline:none;background:#fff7ed;color:#374151;box-sizing:border-box;cursor:not-allowed;"></textarea>
                <p style="font-size:0.72rem;color:#9ca3af;text-align:right;margin-top:0.25rem;"><span id="modalIntercambioCharCount">0</span>/500</p>
            </div>
        </div>

        {{-- Footer --}}
        <div style="padding:1rem 1.5rem;border-top:1px solid #fff7ed;display:flex;gap:0.75rem;flex-shrink:0;background:#fafafa;">
            <button type="button" onclick="cerrarModalIntercambio()"
                    style="flex:1;border:2px solid #e5e7eb;background:#fff;color:#6b7280;border-radius:0.875rem;padding:0.75rem;font-size:0.85rem;font-weight:700;cursor:pointer;transition:all .15s;"
                    onmouseover="this.style.background='#f9fafb';this.style.borderColor='#d1d5db'"
                    onmouseout="this.style.background='#fff';this.style.borderColor='#e5e7eb'">
                Cancelar
            </button>
            <button type="button" id="btnEnviarIntercambio" onclick="enviarPropuestaIntercambio()"
                    style="flex:2;background:linear-gradient(135deg,#f58634,#f58634);color:#fff;border:none;border-radius:0.875rem;padding:0.75rem 1.25rem;font-size:0.9rem;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:0.5rem;box-shadow:0 4px 14px rgba(245,134,52,0.4);transition:all .15s;letter-spacing:-0.01em;"
                    onmouseover="this.style.boxShadow='0 6px 20px rgba(245,134,52,0.5)';this.style.transform='translateY(-1px)'"
                    onmouseout="this.style.boxShadow='0 4px 14px rgba(245,134,52,0.4)';this.style.transform='translateY(0)'">
                <svg style="width:1.1rem;height:1.1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Enviar propuesta
            </button>
        </div>
    </div>
</div>
