@php
    $imgNombre = $sol->item?->imagenes?->where('estado','aprobado')->first()?->nombre;
    $imgSrc = $imgNombre ? \App\Helpers\ImageHelper::urlMedia('imgs/articulos/items', $imgNombre) : asset('imgs/defaults/servicio_default.svg');
    $compradorDir = $sol->comprador?->direcciones?->first();
    $compradorMunicipio = $compradorDir?->municipio?->municipio ?? 'Ubicacion no disponible';
    $estadoColor = match($sol->estado) {
        'pendiente_aprobacion' => 'background:#fef3c7;color:#92400e;',
        'aprobada' => 'background:#d1fae5;color:#065f46;',
        'rechazada' => 'background:#fee2e2;color:#991b1b;',
        'pagada' => 'background:#dbeafe;color:#1e40af;',
        default => 'background:#f3f4f6;color:#6b7280;',
    };
    $estadoTexto = match($sol->estado) {
        'pendiente_aprobacion' => 'Pendiente',
        'aprobada' => 'Aprobada',
        'rechazada' => 'Rechazada',
        'pagada' => 'Pagada',
        default => ucfirst($sol->estado),
    };
@endphp

<div class="solicitud-card" data-id="{{ $sol->id_solicitud }}"
     style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1rem;margin-bottom:0.75rem;box-shadow:0 1px 3px rgba(0,0,0,.05);">
    <div style="display:flex;align-items:flex-start;gap:0.75rem;">
        <img src="{{ $imgSrc }}" alt="{{ $sol->item?->item }}"
             style="width:56px;height:56px;border-radius:0.5rem;object-fit:cover;border:1px solid #f1f5f9;flex-shrink:0;" loading="lazy" width="56" height="56">
        <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;margin-bottom:0.25rem;">
                <span style="font-size:0.9rem;font-weight:600;color:#111827;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $sol->item?->item ?? 'Servicio' }}</span>
                <span class="estado-badge" style="font-size:0.7rem;font-weight:700;padding:3px 10px;border-radius:999px;flex-shrink:0;{{ $estadoColor }}">{{ $estadoTexto }}</span>
            </div>
            <div style="font-size:0.78rem;color:#6b7280;line-height:1.5;">
                <div>Comprador: <strong>{{ $sol->comprador?->nombres ?? '' }} {{ $sol->comprador?->apellidos ?? '' }}</strong></div>
                <div>Ubicacion: <strong>{{ $compradorMunicipio }}</strong></div>
                <div style="display:flex;align-items:center;gap:0.75rem;margin-top:0.25rem;">
                    <span>Monto: <strong style="color:#2563eb;">RD$ {{ number_format($sol->monto_total, 2) }}</strong></span>
                    <span>Cant: {{ $sol->cantidad }}</span>
                    <span>{{ $sol->fecha_creacion?->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($mostrarAcciones && $sol->estado === 'pendiente_aprobacion')
    <div class="acciones-solicitud" style="display:flex;gap:0.5rem;margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid #f3f4f6;">
        <button type="button"
            data-accion="aprobar"
            onclick="accionSolicitud({{ $sol->id_solicitud }}, 'aprobar', this.closest('.solicitud-card'))"
            style="flex:1;padding:0.5rem;background:#16a34a;color:#fff;border:none;border-radius:0.5rem;font-size:0.8rem;font-weight:700;cursor:pointer;">
            Aprobar
        </button>
        <button type="button"
            data-accion="rechazar"
            onclick="if(confirm('¿Rechazar esta solicitud?')) accionSolicitud({{ $sol->id_solicitud }}, 'rechazar', this.closest('.solicitud-card'))"
            style="flex:1;padding:0.5rem;background:#dc2626;color:#fff;border:none;border-radius:0.5rem;font-size:0.8rem;font-weight:700;cursor:pointer;">
            Rechazar
        </button>
    </div>
    @endif
</div>
