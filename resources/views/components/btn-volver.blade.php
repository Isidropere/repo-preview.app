{{-- Botón "Volver" — usa history.back() si hay historial, sino va a $backUrl --}}
<div style="margin-bottom: 1rem;">
    <a href="{{ $backUrl ?? route('home') }}"
       onclick="let useDef = true; if (document.referrer && document.referrer !== window.location.href) { try { const ref = new URL(document.referrer); const cur = new URL(window.location.href); if (ref.pathname !== cur.pathname) { if (window.history.length > 1) { useDef = false; event.preventDefault(); history.back(); } else if (ref.host === cur.host) { useDef = false; event.preventDefault(); window.location.href = document.referrer; } } } catch(e) {} }"
       style="display: inline-flex; align-items: center; gap: 6px; color: #666; font-size: 0.9rem; text-decoration: none; padding: 6px 12px; border-radius: 6px; transition: background 0.2s, color 0.2s;"
       onmouseover="this.style.background='#f0f0f0'; this.style.color='#333';"
       onmouseout="this.style.background='transparent'; this.style.color='#666';"
       aria-label="Volver a la página anterior">
        <i class="fa-solid fa-arrow-left" style="font-size: 0.85rem;"></i>
        Volver
    </a>
</div>
