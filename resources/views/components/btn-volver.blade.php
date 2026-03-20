{{-- Botón discreto "Volver" — uso: @include('components.btn-volver', ['backUrl' => route('home')]) --}}
<div style="margin-bottom: 1rem;">
    <a href="{{ $backUrl ?? route('home') }}"
       style="display: inline-flex; align-items: center; gap: 6px; color: #666; font-size: 0.9rem; text-decoration: none; padding: 6px 12px; border-radius: 6px; transition: background 0.2s, color 0.2s;"
       onmouseover="this.style.background='#f0f0f0'; this.style.color='#333';"
       onmouseout="this.style.background='transparent'; this.style.color='#666';"
       aria-label="Volver a la página anterior">
        <i class="fa-solid fa-arrow-left" style="font-size: 0.85rem;"></i>
        Volver
    </a>
</div>
