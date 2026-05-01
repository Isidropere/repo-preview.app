@extends('layouts.app')

@section('title', $item->item . ' | CambialóRD')

@php
    $stock         = $item->inventarios?->cantidad ?? 0;
    $esVenta       = in_array($item->tipo_trans, [1, 3]);
    $esIntercambio = in_array($item->tipo_trans, [2, 3]);
    $condicionTexto = match((int)$item->condicion) {
        1 => 'Nuevo', 2 => 'Usado — Como nuevo',
        3 => 'Usado — Buen estado', 4 => 'Usado — Aceptable',
        default => 'No especificado'
    };
    $imagenes     = $item->imagenes->where('estado', 'aprobado')->sortBy('orden_visualizacion');
    $imgPrincipal = $imagenes->first();
    $colores      = $item->colors ?? collect();
    $configTarifa29 = $configTarifa29 ?? null;
@endphp

@section('content')
<div style="min-height:100vh;background:#f1f5f9;padding:2rem 0;">
<div style="max-width:1140px;margin:0 auto;padding:0 1.25rem;">

    @include('components.btn-volver', ['backUrl' => route('home')])

    {{-- Breadcrumb --}}
    <nav style="display:flex;align-items:center;gap:0.4rem;font-size:0.78rem;color:#94a3b8;margin-bottom:1.25rem;flex-wrap:wrap;">
        <a href="{{ route('home') }}" style="color:#94a3b8;text-decoration:none;transition:color .15s;" onmouseover="this.style.color='#3b82f6'" onmouseout="this.style.color='#94a3b8'">Inicio</a>
        <span style="color:#cbd5e1;">›</span>
        @if($item->categoria)
        <span style="color:#94a3b8;">{{ $item->categoria->categoria ?? $item->categoria->nombre ?? 'Categoría' }}</span>
        <span style="color:#cbd5e1;">›</span>
        @endif
        <span style="color:#475569;font-weight:500;">{{ Str::limit($item->item, 55) }}</span>
    </nav>

    {{-- ═══════════════════════════════════════════════════
         CARD PRINCIPAL: Galería + Detalles
    ════════════════════════════════════════════════════ --}}
    <div style="background:#fff;border-radius:1.25rem;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,.05);overflow:hidden;margin-bottom:1.25rem;">
        <div style="display:grid;grid-template-columns:1fr;gap:0;" class="md:grid-cols-product-detail">

            {{-- ══ GALERÍA ══ --}}
            <div style="border-right:1px solid #f1f5f9;padding:1.25rem;display:flex;flex-direction:column;gap:0.75rem;background:#fafbfc;">

                {{-- Imagen principal --}}
                <div style="position:relative;background:#f8fafc;border-radius:0.875rem;overflow:hidden;height:290px;display:flex;align-items:center;justify-content:center;border:1px solid #f1f5f9;">
                    @if($imgPrincipal)
                        @if(($imgPrincipal->tipo ?? 'imagen') === 'video')
                        <video id="mainMedia" src="{{ \App\Helpers\ImageHelper::urlMedia($imgPrincipal->ruta, $imgPrincipal->nombre) }}"
                               style="width:100%;height:100%;object-fit:contain;" controls></video>
                        @else
                        <img id="mainMedia"
                             src="{{ \App\Helpers\ImageHelper::urlMedia($imgPrincipal->ruta, $imgPrincipal->nombre) }}"
                             alt="{{ $item->item }}"
                             style="width:100%;height:100%;object-fit:contain;cursor:zoom-in;"
                             onclick="openZoom(this.src)">
                        @endif
                    @else
                        <div style="display:flex;flex-direction:column;align-items:center;color:#cbd5e1;gap:0.5rem;">
                            <svg style="width:3rem;height:3rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span style="font-size:0.75rem;">Sin imagen</span>
                        </div>
                    @endif
                    {{-- Badges tipo --}}
                    <div style="position:absolute;top:0.6rem;left:0.6rem;display:flex;flex-direction:column;gap:0.3rem;">
                        @if($esVenta)<span style="padding:0.2rem 0.55rem;background:#3b82f6;color:#fff;font-size:0.62rem;font-weight:700;border-radius:9999px;letter-spacing:.03em;">VENTA</span>@endif
                        @if($esIntercambio)<span style="padding:0.2rem 0.55rem;background:#10b981;color:#fff;font-size:0.62rem;font-weight:700;border-radius:9999px;letter-spacing:.03em;">INTERCAMBIO</span>@endif
                    </div>
                    @if($imgPrincipal && ($imgPrincipal->tipo ?? 'imagen') !== 'video')
                    <button onclick="openZoom(document.getElementById('mainMedia').src)"
                            style="position:absolute;bottom:0.6rem;right:0.6rem;background:rgba(255,255,255,.92);border:none;border-radius:9999px;padding:0.35rem;cursor:pointer;box-shadow:0 1px 4px rgba(0,0,0,.12);">
                        <svg style="width:0.85rem;height:0.85rem;" fill="none" stroke="#64748b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    @endif
                </div>

                {{-- Thumbnails --}}
                @if($imagenes->count() > 1)
                <div style="display:flex;gap:0.4rem;overflow-x:auto;padding-bottom:2px;">
                    @foreach($imagenes as $img)
                    @php $isVid = ($img->tipo ?? 'imagen') === 'video'; @endphp
                    <button onclick="switchMedia('{{ \App\Helpers\ImageHelper::urlMedia($img->ruta, $img->nombre) }}', {{ $isVid ? 'true':'false' }})"
                            style="flex-shrink:0;width:52px;height:52px;border-radius:0.5rem;border:2px solid #e2e8f0;overflow:hidden;cursor:pointer;padding:0;background:none;transition:border-color .15s;"
                            data-src="{{ \App\Helpers\ImageHelper::urlMedia($img->ruta, $img->nombre) }}" class="thumb-btn"
                            onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor=this.dataset.active?'#3b82f6':'#e2e8f0'">
                        @if($isVid)
                        <div style="width:100%;height:100%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">
                            <svg style="width:1rem;height:1rem;color:#94a3b8;" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                        </div>
                        @else
                        <img src="{{ \App\Helpers\ImageHelper::urlMedia($img->ruta, $img->nombre) }}" alt="min" style="width:100%;height:100%;object-fit:cover;" loading="lazy" width="52" height="52">
                        @endif
                    </button>
                    @endforeach
                </div>
                @endif

                {{-- Badges confianza (debajo de galería) --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.4rem;margin-top:0.25rem;">
                    @foreach([
                        ['#22c55e','M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z','Compra protegida'],
                        ['#3b82f6','M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z','Pago seguro'],
                        ['#8b5cf6','M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10','Envío nacional'],
                        ['#f97316','M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z','Soporte 24/7'],
                    ] as [$color, $path, $label])
                    <div style="display:flex;align-items:center;gap:0.35rem;background:#f8fafc;border-radius:0.5rem;padding:0.35rem 0.5rem;">
                        <svg style="width:0.85rem;height:0.85rem;flex-shrink:0;" fill="none" stroke="{{ $color }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/></svg>
                        <span style="font-size:0.68rem;color:#64748b;font-weight:500;">{{ $label }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ══ DETALLES ══ --}}
            <div style="padding:1.5rem 1.75rem;display:flex;flex-direction:column;gap:0;">

                {{-- Sección 1: Título + SKU + Condición --}}
                <div style="padding-bottom:1.1rem;border-bottom:1px solid #f1f5f9;">
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.4rem;flex-wrap:wrap;">
                        <span style="font-size:0.68rem;color:#94a3b8;background:#f8fafc;border:1px solid #e2e8f0;padding:0.15rem 0.5rem;border-radius:9999px;">SKU #{{ $item->id_item }}</span>
                        <span style="font-size:0.68rem;color:#64748b;background:#f1f5f9;border:1px solid #e2e8f0;padding:0.15rem 0.5rem;border-radius:9999px;">{{ $condicionTexto }}</span>
                        @if($item->categoria)
                        <span style="font-size:0.68rem;color:#64748b;background:#f1f5f9;border:1px solid #e2e8f0;padding:0.15rem 0.5rem;border-radius:9999px;">{{ $item->categoria->categoria ?? $item->categoria->nombre ?? '' }}</span>
                        @endif
                    </div>
                    <h1 style="font-size:1.45rem;font-weight:800;color:#0f172a;margin:0;line-height:1.3;">{{ $item->item }}</h1>
                </div>

                {{-- Sección 2: Precio + Stock --}}
                <div style="padding:1rem 0;border-bottom:1px solid #f1f5f9;">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
                        <div style="display:flex;align-items:baseline;gap:0.6rem;flex-wrap:wrap;">
                            @if($esVenta && $item->valor)
                                <span style="font-size:1.9rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;">RD$ {{ number_format($item->valor, 2) }}</span>
                                @if($item->descuento && $item->descuento > 0)
                                @php $orig = $item->valor + $item->descuento; @endphp
                                <span style="font-size:0.95rem;color:#94a3b8;text-decoration:line-through;">RD$ {{ number_format($orig, 2) }}</span>
                                <span style="padding:0.2rem 0.55rem;background:#fef2f2;color:#dc2626;font-size:0.68rem;font-weight:700;border-radius:9999px;border:1px solid #fecaca;">-{{ number_format(($item->descuento/$orig)*100,0) }}%</span>
                                @endif
                            @elseif($esIntercambio && !$esVenta)
                                <span style="padding:0.35rem 0.9rem;background:#d1fae5;color:#065f46;font-size:0.85rem;font-weight:600;border-radius:9999px;border:1px solid #a7f3d0;">Solo intercambio</span>
                            @endif
                        </div>
                        <div style="display:flex;align-items:center;gap:0.4rem;background:{{ $stock > 0 ? '#f0fdf4' : '#fef2f2' }};border:1px solid {{ $stock > 0 ? '#bbf7d0' : '#fecaca' }};border-radius:9999px;padding:0.3rem 0.75rem;">
                            <span style="width:7px;height:7px;border-radius:50%;background:{{ $stock > 0 ? '#22c55e' : '#f87171' }};display:inline-block;"></span>
                            <span style="font-size:0.75rem;font-weight:600;color:{{ $stock > 0 ? '#15803d' : '#dc2626' }};">
                                {{ $stock > 0 ? $stock.' en stock' : 'Agotado' }}
                            </span>
                        </div>
                    </div>

                    {{-- Mensaje de descuento por volumen (categoría 29) --}}
                    @if(!empty($configTarifa29))
                    <div style="margin-top:10px;background:#fefce8;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:8px;">
                        <span style="font-size:1.1rem;">🏷️</span>
                        <span style="font-size:.85rem;color:#92400e;font-weight:500;">
                            Compra {{ $configTarifa29->cantidad_minima_descuento }} o más y obtén <strong>{{ number_format($configTarifa29->descuento_venta_masiva, 0) }}% de descuento</strong>
                        </span>
                    </div>
                    @endif
                </div>

                {{-- Sección 3: Colores --}}
                @if($colores->count() > 0)
                <div style="padding:1rem 0;border-bottom:1px solid #f1f5f9;">
                    <p style="font-size:0.75rem;font-weight:700;color:#374151;margin:0 0 0.6rem;text-transform:uppercase;letter-spacing:.04em;">
                        Color: <span id="selectedColorName" style="font-weight:500;color:#6b7280;text-transform:none;">{{ $colores->first()->nombre }}</span>
                    </p>
                    <div style="display:flex;flex-wrap:wrap;gap:0.45rem;">
                        @foreach($colores as $color)
                        <button onclick="selectColor('{{ $color->nombre }}', this)"
                                style="width:2.1rem;height:2.1rem;border-radius:50%;border:2.5px solid {{ $loop->first ? '#3b82f6' : '#e2e8f0' }};background:{{ $color->codigo ?? '#ccc' }};cursor:pointer;transition:transform .15s,border-color .15s;box-shadow:0 1px 3px rgba(0,0,0,.1);"
                                title="{{ $color->nombre }} ({{ $color->pivot->stock ?? 0 }} uds)"
                                class="color-btn"
                                onmouseover="this.style.transform='scale(1.18)'" onmouseout="this.style.transform='scale(1)'">
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Sección 4: Cantidad --}}
                @if($esVenta && $stock > 0)
                <div style="padding:1rem 0;border-bottom:1px solid #f1f5f9;">
                    <p style="font-size:0.75rem;font-weight:700;color:#374151;margin:0 0 0.6rem;text-transform:uppercase;letter-spacing:.04em;">Cantidad</p>
                    <div style="display:inline-flex;border:1.5px solid #e2e8f0;border-radius:0.6rem;overflow:hidden;background:#fff;">
                        <button onclick="changeQty(-1)" style="width:2.4rem;height:2.4rem;background:#f8fafc;border:none;cursor:pointer;font-size:1.15rem;color:#475569;transition:background .15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">−</button>
                        <input type="number" id="quantity" value="1" min="1" max="{{ $stock }}"
                               style="width:3.25rem;height:2.4rem;text-align:center;border:none;border-left:1.5px solid #e2e8f0;border-right:1.5px solid #e2e8f0;font-size:0.9rem;font-weight:700;color:#0f172a;outline:none;background:#fff;">
                        <button onclick="changeQty(1)" style="width:2.4rem;height:2.4rem;background:#f8fafc;border:none;cursor:pointer;font-size:1.15rem;color:#475569;transition:background .15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">+</button>
                    </div>
                </div>
                @endif

                {{-- Sección 5: Botones de acción --}}
                <div style="padding:1rem 0;border-bottom:1px solid #f1f5f9;display:flex;gap:0.6rem;flex-wrap:wrap;">
                    @if($esVenta && $stock > 0)
                    <button id="add-to-cart-{{ $item->id_item }}" onclick="agregarAlCarrito({{ $item->id_item }})"
                            style="flex:1;min-width:150px;display:flex;align-items:center;justify-content:center;gap:0.45rem;background:#3b82f6;color:#fff;border:none;border-radius:0.65rem;padding:0.7rem 1.1rem;font-size:0.85rem;font-weight:700;cursor:pointer;transition:background .15s,transform .1s;box-shadow:0 2px 8px rgba(59,130,246,.25);"
                            onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'"
                            onmousedown="this.style.transform='scale(.98)'" onmouseup="this.style.transform='scale(1)'">
                        <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="button-text">Agregar al carrito</span>
                        <svg class="loading" style="display:none;width:0.9rem;height:0.9rem;animation:spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                    </button>
                    @elseif($esVenta && $stock <= 0)
                    <button disabled style="flex:1;min-width:150px;background:#f1f5f9;color:#94a3b8;border:1.5px solid #e2e8f0;border-radius:0.65rem;padding:0.7rem 1.1rem;font-size:0.85rem;font-weight:700;cursor:not-allowed;">Agotado</button>
                    @endif
                    @if($esIntercambio && $item->id_user != auth()->id())
                    <button onclick="abrirModalIntercambio({{ $item->id_item }}, '{{ addslashes($item->item) }}')"
                            style="flex:1;min-width:130px;display:flex;align-items:center;justify-content:center;gap:0.45rem;background:#fff7ed;color:#c2410c;border:1.5px solid #f58634;border-radius:0.65rem;padding:0.65rem 1rem;font-size:0.85rem;font-weight:700;cursor:pointer;transition:background .15s;"
                            onmouseover="this.style.background='#fed7aa'" onmouseout="this.style.background='#fff7ed'">
                        <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        Intercambio sin Negociación
                    </button>
                    @endif
                </div>

                {{-- Botón ver perfil del proveedor (solo servicios/talentos) --}}
                @if((int)$item->id_categoria_item === 29 && isset($hojaVidaProveedor) && $hojaVidaProveedor)
                <div style="margin-top:0.75rem;">
                    <button onclick="document.getElementById('modalHojaVida').style.display='flex'"
                            style="width:100%;display:flex;align-items:center;justify-content:center;gap:0.5rem;background:#eff6ff;color:#1d4ed8;border:1.5px solid #bfdbfe;border-radius:0.65rem;padding:0.6rem 1rem;font-size:0.82rem;font-weight:700;cursor:pointer;transition:background .15s;"
                            onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                        <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Ver perfil del proveedor
                    </button>
                </div>
                @endif

                {{-- Sección 6: Descripción corta (si existe) --}}
                @if($item->presentacion)
                <div style="padding:1rem 0;">
                    <p style="font-size:0.75rem;font-weight:700;color:#374151;margin:0 0 0.5rem;text-transform:uppercase;letter-spacing:.04em;">Descripción</p>
                    <div style="font-size:0.82rem;color:#64748b;line-height:1.65;max-height:80px;overflow:hidden;position:relative;" id="descCorta">
                        {!! Str::limit(strip_tags($item->presentacion), 200) !!}
                    </div>
                    <button onclick="document.getElementById('descCorta').style.maxHeight='none';this.style.display='none';"
                            style="font-size:0.75rem;color:#3b82f6;background:none;border:none;cursor:pointer;padding:0.25rem 0;margin-top:0.2rem;">Ver más ›</button>
                </div>
                @endif

            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════════
         FILA INFERIOR: Descripción completa + Especificaciones
    ════════════════════════════════════════════════════ --}}
    <div style="display:grid;grid-template-columns:1fr;gap:1.1rem;margin-bottom:1.25rem;" class="md:grid-cols-desc-specs">

        {{-- Descripción completa --}}
        <div style="background:#fff;border-radius:1.1rem;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden;">
            <div style="padding:0.85rem 1.25rem;border-bottom:1px solid #f1f5f9;background:#fafbfc;display:flex;align-items:center;gap:0.5rem;">
                <svg style="width:0.9rem;height:0.9rem;color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <h2 style="font-size:0.85rem;font-weight:700;color:#0f172a;margin:0;">Descripción del producto</h2>
            </div>
            <div style="padding:1.25rem;font-size:0.83rem;color:#475569;line-height:1.75;">
                {!! $item->presentacion !!}
            </div>
        </div>

        {{-- Especificaciones --}}
        <div style="background:#fff;border-radius:1.1rem;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden;align-self:start;">
            <div style="padding:0.85rem 1.25rem;border-bottom:1px solid #f1f5f9;background:#fafbfc;display:flex;align-items:center;gap:0.5rem;">
                <svg style="width:0.9rem;height:0.9rem;color:#8b5cf6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <h2 style="font-size:0.85rem;font-weight:700;color:#0f172a;margin:0;">Especificaciones</h2>
            </div>
            <div style="padding:0.5rem 0;">
                @php
                $specs = array_filter([
                    'Condición'   => $condicionTexto,
                    'Tipo'        => $item->tipo_trans == 1 ? 'Venta' : ($item->tipo_trans == 2 ? 'Intercambio' : 'Venta e Intercambio'),
                    'Categoría'   => $item->categoria->categoria ?? $item->categoria->nombre ?? null,
                    'Peso'        => $item->peso_lbs    ? $item->peso_lbs.' lbs'  : null,
                    'Alto'        => $item->alto_cm     ? $item->alto_cm.' cm'    : null,
                    'Ancho'       => $item->ancho_cm    ? $item->ancho_cm.' cm'   : null,
                    'Profundidad' => $item->profundo_cm ? $item->profundo_cm.' cm': null,
                ]);
                @endphp
                @foreach($specs as $label => $value)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.55rem 1.25rem;{{ !$loop->last ? 'border-bottom:1px solid #f8fafc;' : '' }}{{ $loop->even ? 'background:#fafbfc;' : '' }}">
                    <span style="font-size:0.77rem;color:#94a3b8;font-weight:500;">{{ $label }}</span>
                    <span style="font-size:0.77rem;font-weight:700;color:#334155;">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         PRODUCTOS RELACIONADOS
    ════════════════════════════════════════════════════ --}}
    @if($relatedItems->count() > 0)
    <div style="background:#fff;border-radius:1.1rem;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden;">
        <div style="padding:0.85rem 1.25rem;border-bottom:1px solid #f1f5f9;background:#fafbfc;display:flex;align-items:center;gap:0.5rem;">
            <svg style="width:0.9rem;height:0.9rem;color:#f97316;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            <h2 style="font-size:0.85rem;font-weight:700;color:#0f172a;margin:0;">Productos relacionados</h2>
        </div>
        <div style="padding:1rem;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0.75rem;" class="sm:grid-cols-related-products">
            @foreach($relatedItems as $rel)
            @php
                $ri  = $rel->imagenes->where('estado', 'aprobado')->first();
                $riu = $ri ? \App\Helpers\ImageHelper::urlMedia($ri->ruta, $ri->nombre) : null;
            @endphp
            <a href="{{ route('producto.detalle', $rel->slug) }}"
               style="background:#f8fafc;border:1px solid #f1f5f9;border-radius:0.6rem;overflow:hidden;text-decoration:none;display:block;transition:box-shadow .2s,transform .2s;"
               onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.1)';this.style.transform='translateY(-2px)'"
               onmouseout="this.style.boxShadow='none';this.style.transform='none'">
                <div style="height:76px;overflow:hidden;background:#f1f5f9;">
                    @if($riu)
                    <img src="{{ $riu }}" alt="{{ $rel->item }}" style="width:100%;height:100%;object-fit:cover;" loading="lazy" width="200" height="76" onerror="this.src='/imgs/producto_defaul.png'">
                    @else
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#e2e8f0;">
                        <svg style="width:1.25rem;height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    @endif
                </div>
                <div style="padding:0.4rem 0.5rem;">
                    <p style="font-size:0.65rem;color:#334155;margin:0 0 2px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;font-weight:500;">{{ $rel->item }}</p>
                    @if($rel->valor)
                    <p style="font-size:0.68rem;font-weight:800;color:#0f172a;margin:0;">RD$ {{ number_format($rel->valor, 2) }}</p>
                    @else
                    <p style="font-size:0.65rem;color:#059669;font-weight:600;margin:0;">Intercambio</p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
</div>


{{-- MODAL ZOOM --}}
<div id="zoomModal" onclick="closeZoom()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;align-items:center;justify-content:center;padding:1rem;">
    <button onclick="closeZoom()" style="position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.8);cursor:pointer;font-size:1.25rem;width:2.25rem;height:2.25rem;border-radius:50%;display:flex;align-items:center;justify-content:center;">✕</button>
    <img id="zoomImg" src="" alt="Zoom" style="max-width:100%;max-height:90vh;object-fit:contain;border-radius:0.5rem;" onclick="event.stopPropagation()">
</div>

{{-- MODAL NEGOCIACIONES --}}
@auth

<style>
#mensajesContainer::-webkit-scrollbar { width: 3px; }
#mensajesContainer::-webkit-scrollbar-thumb { background-color: #a0aec0; border-radius: 8px; }
#mensajesContainer::-webkit-scrollbar-track { background: #edf2f7; border-radius: 8px; }
</style>

<div id="negociacionesModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;align-items:center;justify-content:center;z-index:9999;background:rgba(0,0,0,.55);padding:1rem;">
    <div style="background:#fff;border-radius:1.25rem;width:100%;max-width:500px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.2);">

        {{-- Header --}}
        <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;background:#f8fafc;">
            {{-- Fila título + cerrar --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
                <div style="display:flex;align-items:center;gap:0.5rem;">
                    <div style="width:1.75rem;height:1.75rem;background:#d1fae5;border-radius:0.45rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg style="width:0.9rem;height:0.9rem;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <h3 style="font-size:0.88rem;font-weight:700;color:#0f172a;margin:0;">Proponer intercambio</h3>
                </div>
                <button id="closeNegModal" style="background:#f1f5f9;border:none;color:#64748b;cursor:pointer;font-size:1rem;width:2rem;height:2rem;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:background .15s;flex-shrink:0;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">✕</button>
            </div>
            {{-- Tarjeta del item negociado --}}
            <div style="display:flex;align-items:center;gap:0.75rem;background:#fff;border:1px solid #e2e8f0;border-radius:0.75rem;padding:0.6rem 0.75rem;">
                @php $imgNeg = $imagenes->first(); @endphp
                @if($imgNeg)
                <img src="{{ \App\Helpers\ImageHelper::urlMedia($imgNeg->ruta, $imgNeg->nombre) }}"
                     alt="{{ $item->item }}"
                     style="width:52px;height:52px;object-fit:cover;border-radius:0.5rem;flex-shrink:0;border:1px solid #f1f5f9;">
                @else
                <div style="width:52px;height:52px;background:#f1f5f9;border-radius:0.5rem;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:1.25rem;height:1.25rem;color:#cbd5e1;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                @endif
                <div style="min-width:0;flex:1;">
                    <p style="font-size:0.8rem;font-weight:700;color:#0f172a;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $item->item }}</p>
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.2rem;flex-wrap:wrap;">
                        @if($esVenta && $item->valor)
                        <span style="font-size:0.78rem;font-weight:800;color:#2563eb;">RD$ {{ number_format($item->valor, 2) }}</span>
                        @endif
                        @if($esIntercambio)
                        <span style="font-size:0.68rem;font-weight:600;color:#059669;background:#d1fae5;padding:0.1rem 0.45rem;border-radius:9999px;">Intercambio</span>
                        @endif
                        <span style="font-size:0.68rem;color:#94a3b8;">SKU #{{ $item->id_item }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mensajes previos --}}
        <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
            <p style="font-size:0.7rem;font-weight:700;color:#374151;margin:0 0 0.5rem;text-transform:uppercase;letter-spacing:.04em;">Historial</p>
            <div id="mensajesContainer"
                 style="height:130px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:0.6rem;padding:0.5rem;background:#f8fafc;scrollbar-width:thin;scrollbar-color:#a0aec0 #edf2f7;">
                <p style="text-align:center;color:#94a3b8;font-size:0.78rem;margin:0;">Cargando mensajes...</p>
            </div>
        </div>

        {{-- Formulario --}}
        <div style="padding:1rem 1.25rem;display:flex;flex-direction:column;gap:0.75rem;">

            {{-- Acción --}}
            <div>
                <label style="display:block;font-size:0.72rem;font-weight:700;color:#374151;margin-bottom:0.3rem;text-transform:uppercase;letter-spacing:.04em;">Acción a realizar</label>
                <select id="negAccion" style="width:100%;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:0.82rem;padding:0.45rem 0.6rem;background:#fff;color:#374151;outline:none;">
                    <option value="">-- Seleccione una acción --</option>
                </select>
            </div>

            {{-- Mensaje predefinido --}}
            <div>
                <label style="display:block;font-size:0.72rem;font-weight:700;color:#374151;margin-bottom:0.3rem;text-transform:uppercase;letter-spacing:.04em;">Mensaje predefinido</label>
                <select id="negMensajePredefinido" style="width:100%;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:0.82rem;padding:0.45rem 0.6rem;background:#fff;color:#374151;outline:none;">
                    <option value="">-- Selecciona --</option>
                </select>
            </div>

            {{-- Textarea --}}
            <div>
                <label style="display:block;font-size:0.72rem;font-weight:700;color:#374151;margin-bottom:0.3rem;text-transform:uppercase;letter-spacing:.04em;">
                    Mensaje
                    <span style="font-size:0.68rem;font-weight:400;color:#94a3b8;text-transform:none;margin-left:0.3rem;">(selecciona un mensaje predefinido)</span>
                </label>
                <textarea id="negMensaje" rows="2" readonly
                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:0.5rem;padding:0.45rem 0.6rem;font-size:0.82rem;resize:none;box-sizing:border-box;outline:none;color:#475569;background:#f1f5f9;cursor:not-allowed;"
                    placeholder="Se llenará al seleccionar un mensaje predefinido..."></textarea>
            </div>

            {{-- Paquete --}}
            <div>
                <label style="display:block;font-size:0.72rem;font-weight:700;color:#374151;margin-bottom:0.3rem;text-transform:uppercase;letter-spacing:.04em;">Paquete a ofrecer</label>
                <select id="negPaquete" style="width:100%;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:0.82rem;padding:0.45rem 0.6rem;background:#fff;color:#374151;outline:none;">
                    <option value="">-- Selecciona un paquete existente --</option>
                </select>
                <div style="display:flex;gap:0.75rem;margin-top:0.35rem;">
                    <button id="negBtnVerPaquetes" style="font-size:0.72rem;color:#3b82f6;background:none;border:none;cursor:pointer;padding:0;text-decoration:underline;">📦 Ver mis paquetes</button>
                    <button id="negCrearPaqueteBtn" style="font-size:0.72rem;color:#3b82f6;background:none;border:none;cursor:pointer;padding:0;text-decoration:underline;">+ Crear nuevo paquete</button>
                </div>
                <div id="negContenedorPaquetes" style="margin-top:0.4rem;display:none;flex-wrap:wrap;gap:0.4rem;padding:0.5rem;border:1px solid #e2e8f0;border-radius:0.5rem;background:#f8fafc;min-height:2.5rem;">
                    <p style="color:#94a3b8;font-size:0.72rem;margin:auto;">Sin paquetes cargados.</p>
                </div>
            </div>

            {{-- Monto --}}
            <div>
                <label style="display:block;font-size:0.72rem;font-weight:700;color:#374151;margin-bottom:0.3rem;text-transform:uppercase;letter-spacing:.04em;">Monto de la oferta <span style="font-weight:400;text-transform:none;">(opcional)</span></label>
                <input type="number" id="negMonto" placeholder="Ej: 1500.00"
                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:0.5rem;padding:0.45rem 0.6rem;font-size:0.82rem;box-sizing:border-box;outline:none;color:#334155;">
            </div>
        </div>

        {{-- Footer --}}
        <div style="padding:0.85rem 1.25rem;background:#f8fafc;display:flex;justify-content:flex-end;gap:0.5rem;border-top:1px solid #f1f5f9;">
            <button id="negCancelarBtn" style="padding:0.5rem 1rem;background:none;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:0.82rem;color:#64748b;cursor:pointer;transition:background .15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'">Cancelar</button>
            <button id="negEnviarBtn" style="padding:0.5rem 1.25rem;background:#10b981;color:#fff;border:none;border-radius:0.5rem;font-size:0.82rem;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(16,185,129,.25);transition:background .15s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">Enviar propuesta</button>
        </div>
    </div>
</div>

{{-- Modal Crear/Editar Paquete --}}
<div id="crearPaqueteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);align-items:center;justify-content:center;z-index:10000;padding:1rem;">
    <div style="background:#fff;border-radius:1.25rem;width:100%;max-width:480px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.2);">

        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;background:#f8fafc;">
            <div style="display:flex;align-items:center;gap:0.6rem;">
                <div style="width:2rem;height:2rem;background:#dbeafe;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:1rem;height:1rem;color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                </div>
                <h3 id="tituloPaqueteModal" style="font-size:0.9rem;font-weight:700;color:#0f172a;margin:0;">Crear nuevo paquete</h3>
            </div>
            <button id="closePaqueteModal" style="background:#f1f5f9;border:none;color:#64748b;cursor:pointer;font-size:1rem;width:2rem;height:2rem;border-radius:50%;display:flex;align-items:center;justify-content:center;">✕</button>
        </div>

        {{-- Body --}}
        <div style="padding:1rem 1.25rem;display:flex;flex-direction:column;gap:0.75rem;">
            <div>
                <label style="display:block;font-size:0.72rem;font-weight:700;color:#374151;margin-bottom:0.3rem;text-transform:uppercase;letter-spacing:.04em;">Nombre del paquete</label>
                <input type="text" id="negNombrePaquete" maxlength="20" placeholder="Ej: Paquete oferta especial"
                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:0.5rem;padding:0.45rem 0.6rem;font-size:0.82rem;box-sizing:border-box;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:0.72rem;font-weight:700;color:#374151;margin-bottom:0.3rem;text-transform:uppercase;letter-spacing:.04em;">Selecciona tus items</label>
                <div id="negListaItemsUsuario"
                    style="height:140px;overflow-y:auto;border:1.5px solid #e2e8f0;border-radius:0.5rem;padding:0.4rem;background:#f8fafc;font-size:0.8rem;scrollbar-width:thin;">
                    <p style="text-align:center;color:#94a3b8;font-size:0.78rem;">Cargando items...</p>
                </div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;background:#f1f5f9;border-radius:0.5rem;padding:0.5rem 0.75rem;">
                <span style="font-size:0.78rem;color:#64748b;font-weight:600;">Valor total del paquete</span>
                <span id="negValorTotalPaquete" style="font-size:0.95rem;font-weight:800;color:#0f172a;">0.00</span>
            </div>
        </div>

        {{-- Footer --}}
        <div style="padding:0.85rem 1.25rem;background:#f8fafc;display:flex;justify-content:flex-end;gap:0.5rem;border-top:1px solid #f1f5f9;">
            <button id="negCancelarPaqueteBtn" style="padding:0.5rem 1rem;background:none;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:0.82rem;color:#64748b;cursor:pointer;">Cancelar</button>
            <button id="negGuardarPaqueteBtn" style="padding:0.5rem 1.25rem;background:#3b82f6;color:#fff;border:none;border-radius:0.5rem;font-size:0.82rem;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(59,130,246,.25);">Guardar paquete</button>
        </div>
    </div>
</div>

<textarea id="mensajeOferta" style="display:none;"></textarea>
@endauth

{{-- Modal intercambio se carga via JS estático --}}

{{-- Modal Hoja de Vida del Proveedor --}}
@if((int)$item->id_categoria_item === 29 && isset($hojaVidaProveedor) && $hojaVidaProveedor)
<div id="modalHojaVida" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:1rem;"
     onclick="if(event.target===this)this.style.display='none'">
    <div style="background:#fff;border-radius:1.25rem;width:100%;max-width:28rem;box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden;max-height:calc(100vh - 2rem);display:flex;flex-direction:column;">
        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#2563eb,#3b82f6);padding:1.25rem 1.5rem;flex-shrink:0;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:2.5rem;height:2.5rem;background:rgba(255,255,255,0.2);border-radius:0.75rem;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:1.25rem;height:1.25rem;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h3 style="color:#fff;font-size:1rem;font-weight:700;margin:0;">Perfil del Proveedor</h3>
                    <p style="color:rgba(255,255,255,0.8);font-size:0.75rem;margin:0;">Informacion profesional</p>
                </div>
            </div>
            <button onclick="document.getElementById('modalHojaVida').style.display='none'"
                    style="width:1.9rem;height:1.9rem;background:rgba(255,255,255,0.2);border:none;border-radius:50%;color:#fff;font-size:1.1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">x</button>
        </div>
        {{-- Body --}}
        <div style="padding:1.25rem 1.5rem;overflow-y:auto;flex:1;">
            <div style="text-align:center;margin-bottom:1rem;">
                <div style="width:3.5rem;height:3.5rem;background:#eff6ff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:0.5rem;">
                    <span style="font-size:1.25rem;font-weight:800;color:#2563eb;">{{ strtoupper(substr($hojaVidaProveedor->nombres,0,1)) }}{{ strtoupper(substr($hojaVidaProveedor->apellidos,0,1)) }}</span>
                </div>
                <h4 style="font-size:1rem;font-weight:700;color:#111827;margin:0;">{{ $hojaVidaProveedor->nombres }} {{ $hojaVidaProveedor->apellidos }}</h4>
                <p style="font-size:0.82rem;color:#3b82f6;font-weight:600;margin:0.25rem 0 0;">{{ $hojaVidaProveedor->titulo_profesional }}</p>
                <p style="font-size:0.75rem;color:#6b7280;margin:0.25rem 0 0;">{{ $hojaVidaProveedor->ubicacion }}</p>
            </div>

            <div style="space-y:0.75rem;">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:0.5rem;padding:0.75rem;margin-bottom:0.5rem;">
                    <p style="font-size:0.7rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.04em;margin:0 0 0.35rem;">Descripcion</p>
                    <p style="font-size:0.8rem;color:#475569;line-height:1.5;margin:0;">{{ $hojaVidaProveedor->descripcion_bio }}</p>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:0.5rem;padding:0.75rem;margin-bottom:0.5rem;">
                    <p style="font-size:0.7rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.04em;margin:0 0 0.35rem;">Habilidades</p>
                    <p style="font-size:0.8rem;color:#475569;line-height:1.5;margin:0;">{{ $hojaVidaProveedor->habilidades }}</p>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:0.5rem;padding:0.75rem;margin-bottom:0.5rem;">
                    <p style="font-size:0.7rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.04em;margin:0 0 0.35rem;">Experiencia</p>
                    <p style="font-size:0.8rem;color:#475569;line-height:1.5;margin:0;">{{ $hojaVidaProveedor->experiencia }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<div id="modalIntercambio" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.65);backdrop-filter:blur(4px);">
<div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl flex flex-col overflow-hidden" style="max-height:92vh;">
<div style="background:linear-gradient(135deg,#ea580c 0%,#f58634 60%,#fb923c 100%);padding:1.25rem 1.5rem;flex-shrink:0;">
<div style="display:flex;align-items:center;justify-content:space-between;">
<div style="display:flex;align-items:center;gap:0.75rem;">
<div style="width:2.5rem;height:2.5rem;background:rgba(255,255,255,0.25);border-radius:0.75rem;display:flex;align-items:center;justify-content:center;">
<svg style="width:1.25rem;height:1.25rem;color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
</div>
<div><h3 style="font-size:1rem;font-weight:800;color:#fff;margin:0;">Intercambio con Negociación</h3>
<p id="modalIntercambioItemNombre" style="font-size:0.75rem;color:rgba(255,255,255,0.85);margin:0.1rem 0 0;font-weight:500;"></p></div>
</div>
<button onclick="cerrarModalIntercambio()" style="width:2rem;height:2rem;background:rgba(255,255,255,0.25);border:none;border-radius:50%;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1rem;">✕</button>
</div></div>
<div style="padding:1.25rem 1.5rem;overflow-y:auto;flex:1;">
<div id="modalIntercambioError" class="hidden" style="background:#fef2f2;border:1.5px solid #fca5a5;border-radius:0.75rem;padding:0.75rem 1rem;margin-bottom:1rem;color:#dc2626;font-size:0.82rem;font-weight:600;"></div>
<div style="margin-bottom:1.25rem;">
<p style="font-size:0.82rem;font-weight:700;color:#374151;margin-bottom:0.6rem;">Selecciona los productos que ofreces a cambio:</p>
<div id="misProductosLista" style="overflow-y:auto;max-height:200px;min-height:60px;border:2px solid #fff7ed;border-radius:1rem;padding:0.5rem;background:#fff7ed;display:flex;flex-direction:column;gap:0.4rem;">
<p style="text-align:center;color:#9ca3af;font-size:0.82rem;padding:1rem 0;">Cargando...</p>
</div></div>
<div><p style="font-size:0.82rem;font-weight:700;color:#374151;margin-bottom:0.6rem;">Mensaje de propuesta <span style="color:#ef4444;">*</span></p>
<textarea id="modalIntercambioMensaje" rows="3" maxlength="500" readonly style="width:100%;border:2px solid #fff7ed;border-radius:0.75rem;padding:0.75rem 1rem;font-size:0.85rem;resize:none;outline:none;background:#fff7ed;color:#374151;box-sizing:border-box;cursor:not-allowed;"></textarea>
<p style="font-size:0.72rem;color:#9ca3af;text-align:right;margin-top:0.25rem;"><span id="modalIntercambioCharCount">0</span>/500</p>
</div></div>
<div style="padding:1rem 1.5rem;border-top:1px solid #f0fdf4;display:flex;gap:0.75rem;flex-shrink:0;background:#fafafa;">
<button type="button" onclick="cerrarModalIntercambio()" style="flex:1;border:2px solid #e5e7eb;background:#fff;color:#6b7280;border-radius:0.875rem;padding:0.75rem;font-size:0.85rem;font-weight:700;cursor:pointer;">Cancelar</button>
<button type="button" id="btnEnviarIntercambio" onclick="enviarPropuestaIntercambio()" style="flex:2;background:linear-gradient(135deg,#ea580c,#f58634);color:#fff;border:none;border-radius:0.875rem;padding:0.75rem 1.25rem;font-size:0.9rem;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:0.5rem;box-shadow:0 4px 14px rgba(245,134,52,0.4);">
<svg style="width:1.1rem;height:1.1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
Enviar propuesta</button>
</div></div></div>
<script>
window._urlLogin = "{{ route('login') }}";
window._urlItemsUsuario = "{{ route('carrito.items_usuario') }}";
window._urlNegStore = "{{ route('negociaciones.store') }}";
</script>
<script src="{{ asset('js/modal-intercambio.js') }}"></script>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
<script>
function switchMedia(src, isVideo) {
    if (isVideo) {
        const wrap = document.getElementById('mainMedia').parentElement;
        wrap.innerHTML = `<video src="${src}" style="width:100%;height:100%;object-fit:contain;" controls autoplay></video>`;
    } else {
        const img = document.getElementById('mainMedia');
        if (img) img.src = src;
    }
    document.querySelectorAll('.thumb-btn').forEach(b => {
        b.style.borderColor = b.dataset.src === src ? '#3b82f6' : '#e2e8f0';
        b.dataset.active = b.dataset.src === src ? '1' : '';
    });
}

function openZoom(src) {
    const m = document.getElementById('zoomModal');
    document.getElementById('zoomImg').src = src;
    m.style.display = 'flex';
}
function closeZoom() {
    document.getElementById('zoomModal').style.display = 'none';
}

function changeQty(d) {
    const i = document.getElementById('quantity');
    if (!i) return;
    i.value = Math.min(Math.max(1, parseInt(i.value||1)+d), parseInt(i.max)||99);
}
const qi = document.getElementById('quantity');
if (qi) qi.addEventListener('input', function() {
    const max = parseInt(this.max)||99;
    if (parseInt(this.value)>max) this.value=max;
    if (parseInt(this.value)<1||!this.value) this.value=1;
});

function selectColor(name, el) {
    document.getElementById('selectedColorName').textContent = name;
    document.querySelectorAll('.color-btn').forEach(b => b.style.borderColor = '#e2e8f0');
    el.style.borderColor = '#3b82f6';
}

function mostrarModalOferta() {
    @guest window.location.href='{{ route("login") }}'; return; @endguest
    const m = document.getElementById('negociacionesModal');
    if (!m) return;
    m.style.display = 'flex';
    negCargarDatos({{ $item->id_item }});
}
function cerrarModalOferta() {
    const m = document.getElementById('negociacionesModal');
    if (m) m.style.display = 'none';
}

// ── Variables globales del modal ──
window._negPaqueteId = null;
window._negItemsSeleccionados = [];
window._negTotalPaquete = 0;

async function negCargarDatos(itemId) {
    const mensajesContainer = document.getElementById('mensajesContainer');
    const accionSelect      = document.getElementById('negAccion');
    const predSelect        = document.getElementById('negMensajePredefinido');
    const paqueteSelect     = document.getElementById('negPaquete');

    if (mensajesContainer) mensajesContainer.innerHTML = '<p style="text-align:center;color:#9ca3af;font-size:0.8rem;">Cargando mensajes...</p>';

    try {
        const res  = await fetch(`/carrito/getnegociaciones/${itemId}`, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();

        // Mensajes
        if (mensajesContainer) {
            mensajesContainer.innerHTML = data.mensajes?.length
                ? data.mensajes.map(msg => {
                    const align   = msg.propio ? 'flex-end' : 'flex-start';
                    const bg      = msg.propio ? '#3b82f6' : '#f3f4f6';
                    const color   = msg.propio ? '#fff'    : '#111827';
                    return `<div style="display:flex;justify-content:${align};margin-bottom:0.4rem;">
                        <div style="max-width:75%;padding:0.4rem 0.7rem;border-radius:0.5rem;background:${bg};color:${color};font-size:0.78rem;word-break:break-word;">
                            <p style="margin:0;">${msg.texto}</p>
                            <small style="display:block;text-align:right;color:${msg.propio?'rgba(255,255,255,.7)':'#9ca3af'};font-size:0.65rem;margin-top:2px;">${msg.fecha}</small>
                        </div>
                    </div>`;
                }).join('')
                : '<p style="text-align:center;color:#9ca3af;font-size:0.8rem;">Sin mensajes aún.</p>';
            mensajesContainer.scrollTop = mensajesContainer.scrollHeight;
        }

        // Acciones
        if (accionSelect) {
            accionSelect.innerHTML = '<option value="">-- Seleccione una acción --</option>';
            (data.accion || []).forEach(a => {
                accionSelect.innerHTML += `<option value="${a.tipo}">${a.tipo.charAt(0).toUpperCase()+a.tipo.slice(1)}</option>`;
            });
        }

        // Mensajes predefinidos
        if (predSelect) {
            predSelect.innerHTML = '<option value="">-- Selecciona --</option>';
            (data.mensajesPredefinidos || []).forEach(m => {
                predSelect.innerHTML += `<option value="${m.mensaje}">${m.titulo}</option>`;
            });
        }

        // Paquetes
        if (paqueteSelect) {
            paqueteSelect.innerHTML = '<option value="">-- Selecciona un paquete existente --</option>';
            (data.paquetes || []).forEach(p => {
                paqueteSelect.innerHTML += `<option value="${p.id}">${p.nombre}</option>`;
            });
        }

    } catch(e) {
        if (mensajesContainer) mensajesContainer.innerHTML = '<p style="text-align:center;color:#ef4444;font-size:0.8rem;">Error al cargar.</p>';
    }
}

// Sincronizar mensaje predefinido → textarea
document.getElementById('negMensajePredefinido')?.addEventListener('change', function() {
    const ta = document.getElementById('negMensaje');
    if (ta) ta.value = this.value;
});

// Cerrar modal
['closeNegModal','negCancelarBtn'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', cerrarModalOferta);
});
document.getElementById('negociacionesModal')?.addEventListener('click', function(e) {
    if (e.target === this) cerrarModalOferta();
});

// Enviar negociación
document.getElementById('negEnviarBtn')?.addEventListener('click', async function() {
    const mensaje  = document.getElementById('negMensaje')?.value.trim();
    const paquete  = document.getElementById('negPaquete')?.value || null;
    const monto    = document.getElementById('negMonto')?.value || null;
    const itemId   = {{ $item->id_item }};

    if (!mensaje) { showNotification('Escribe un mensaje antes de enviar.', 'warning'); return; }

    this.disabled = true;
    this.textContent = 'Enviando...';

    try {
        const res = await fetch('{{ route("carrito.save_negociaciones") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ item_id: itemId, mensaje, paquete_id: paquete, monto_oferta: monto })
        });

        if (res.status === 401 || res.redirected) { window.location.href = '{{ route("login") }}'; return; }

        const data = await res.json();
        if (data.status === 'ok') {
            cerrarModalOferta();
            showNotification('Negociación enviada correctamente.', 'success');
        } else {
            showNotification(data.message || 'Error al enviar.', 'error');
        }
    } catch(e) {
        showNotification('Error de conexión.', 'error');
    } finally {
        this.disabled = false;
        this.textContent = 'Enviar';
    }
});

// ── Ver paquetes ──
document.getElementById('negBtnVerPaquetes')?.addEventListener('click', async function() {
    const contenedor = document.getElementById('negContenedorPaquetes');
    if (!contenedor) return;
    // Mostrar/ocultar toggle
    if (contenedor.style.display === 'flex') {
        contenedor.style.display = 'none';
        this.textContent = '📦 Ver mis paquetes';
        return;
    }
    contenedor.style.display = 'flex';
    this.textContent = '📦 Ocultar paquetes';
    contenedor.innerHTML = '<p style="color:#9ca3af;font-size:0.75rem;margin:auto;">Cargando...</p>';
    try {
        const res = await fetch('/carrito/listarPaquetes');
        const paquetes = await res.json();
        if (!paquetes.length) { contenedor.innerHTML = '<p style="color:#9ca3af;font-size:0.75rem;margin:auto;">Sin paquetes.</p>'; return; }
        contenedor.innerHTML = '';
        paquetes.forEach(p => {
            const div = document.createElement('div');
            div.style.cssText = 'background:#f3f4f6;padding:0.5rem 0.75rem;border-radius:0.5rem;font-size:0.75rem;cursor:pointer;white-space:nowrap;border:1px solid #e5e7eb;';
            div.innerHTML = `<strong>${p.nombre_paquete}</strong><br><span style="color:#9ca3af;">#${p.id_paquete}</span>`;
            div.addEventListener('click', () => {
                const sel = document.getElementById('negPaquete');
                if (sel) {
                    sel.value = p.id_paquete;
                    // Resaltar seleccionado
                    contenedor.querySelectorAll('div').forEach(d => d.style.background = '#f3f4f6');
                    div.style.background = '#dbeafe';
                }
            });
            contenedor.appendChild(div);
        });
    } catch(e) {
        contenedor.innerHTML = '<p style="color:#ef4444;font-size:0.75rem;margin:auto;">Error al cargar.</p>';
    }
});

// ── Crear paquete ──
document.getElementById('negCrearPaqueteBtn')?.addEventListener('click', async function() {
    const modal = document.getElementById('crearPaqueteModal');
    if (!modal) return;
    modal.style.display = 'flex';
    window._negPaqueteId = null;
    window._negItemsSeleccionados = [];
    window._negTotalPaquete = 0;
    document.getElementById('negNombrePaquete').value = '';
    document.getElementById('negValorTotalPaquete').textContent = '0.00';
    document.getElementById('tituloPaqueteModal').textContent = 'Crear nuevo paquete';
    document.getElementById('negGuardarPaqueteBtn').textContent = 'Guardar paquete';

    const lista = document.getElementById('negListaItemsUsuario');
    lista.innerHTML = '<p style="text-align:center;color:#9ca3af;">Cargando items...</p>';
    try {
        const res   = await fetch('/carrito/items-usuario');
        const items = await res.json();
        lista.innerHTML = '';
        items.forEach(it => {
            lista.innerHTML += `<div style="display:flex;justify-content:space-between;align-items:center;padding:0.3rem 0;border-bottom:1px solid #f3f4f6;">
                <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;font-size:0.78rem;">
                    <input type="checkbox" class="neg-item-cb" data-id="${it.id_item}" data-valor="${it.valor ?? 0}">
                    <span>${it.item}</span>
                </label>
                <span style="font-size:0.75rem;color:#2563eb;font-weight:600;">${parseFloat(it.valor??0).toFixed(2)}</span>
            </div>`;
        });
        lista.querySelectorAll('.neg-item-cb').forEach(cb => {
            cb.addEventListener('change', function() {
                const v = parseFloat(this.dataset.valor), id = parseInt(this.dataset.id);
                if (this.checked) { window._negItemsSeleccionados.push(id); window._negTotalPaquete += v; }
                else { window._negItemsSeleccionados = window._negItemsSeleccionados.filter(i=>i!==id); window._negTotalPaquete -= v; }
                document.getElementById('negValorTotalPaquete').textContent = window._negTotalPaquete.toFixed(2);
            });
        });
    } catch(e) {
        lista.innerHTML = '<p style="text-align:center;color:#ef4444;">Error al cargar items.</p>';
    }
});

['closePaqueteModal','negCancelarPaqueteBtn'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', () => {
        document.getElementById('crearPaqueteModal').style.display = 'none';
    });
});

document.getElementById('negGuardarPaqueteBtn')?.addEventListener('click', async function() {
    const nombre = document.getElementById('negNombrePaquete')?.value.trim();
    if (!nombre) { showNotification('Escribe un nombre para el paquete.', 'warning'); return; }
    if (!window._negPaqueteId && !window._negItemsSeleccionados.length) { showNotification('Selecciona al menos un item.', 'warning'); return; }

    const url    = window._negPaqueteId ? `/carrito/editarPaquete/${window._negPaqueteId}` : '/carrito/crearPaquete';
    const method = window._negPaqueteId ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ nombre, items: window._negItemsSeleccionados })
        });
        if (!res.ok) throw new Error();
        showNotification('Paquete guardado.', 'success');
        document.getElementById('crearPaqueteModal').style.display = 'none';
        // Actualizar select de paquetes
        const r2 = await fetch('/carrito/listarPaquetes');
        const ps = await r2.json();
        const sel = document.getElementById('negPaquete');
        if (sel) {
            sel.innerHTML = '<option value="">-- Selecciona un paquete existente --</option>';
            ps.forEach(p => sel.innerHTML += `<option value="${p.id_paquete}">${p.nombre_paquete}</option>`);
        }
    } catch(e) {
        showNotification('No se pudo guardar el paquete.', 'error');
    }
});

window.updateCartCounter = function(c) {
    const el = document.getElementById('cart-counter');
    if (el) { el.textContent=c; el.style.animation='none'; setTimeout(()=>el.style.animation='',10); }
};

window.agregarAlCarrito = async function(id_item) {
    const url   = window.urlAgregarCarrito||'{{ route("carrito.agregar") }}';
    const token = window.csrfToken||document.querySelector('meta[name="csrf-token"]')?.content;
    const btn   = document.querySelector(`#add-to-cart-${id_item}`);
    const qty   = parseInt(document.getElementById('quantity')?.value||1);
    if (btn) btn.disabled=true;
    const bt = btn?.querySelector('.button-text');
    const ld = btn?.querySelector('.loading');
    if (bt) bt.textContent='Agregando...';
    if (ld) ld.style.display='inline-block';
    try {
        const res = await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},body:JSON.stringify({id_item,cantidad:qty})});
        if (res.status===401||res.redirected){window.location.href='{{ route("login") }}';return;}
        const data = await res.json();
        if (!res.ok) throw new Error(data.message||'Error');
        showNotification('Producto agregado al carrito','success');
        if (data.cart_count !== undefined) window.updateCartBadge(data.cart_count);
        if (window.syncCartIndicators) window.syncCartIndicators();
    } catch(e) { showNotification(e.message,'error'); }
    finally {
        if (btn) btn.disabled=false;
        if (bt) bt.textContent='Agregar al carrito';
        if (ld) ld.style.display='none';
    }
};

window.showNotification = function(msg, type) {
    const c = {success:'#22c55e',error:'#ef4444',warning:'#f59e0b'};
    const n = document.createElement('div');
    n.style.cssText = `position:fixed;bottom:1.25rem;right:1.25rem;z-index:99999;background:${c[type]||c.success};color:#fff;padding:0.65rem 1rem;border-radius:0.6rem;box-shadow:0 4px 12px rgba(0,0,0,.15);font-size:0.85rem;font-weight:600;display:flex;align-items:center;gap:0.4rem;`;
    n.textContent = msg;
    document.body.appendChild(n);
    setTimeout(()=>{ n.style.opacity='0'; n.style.transition='opacity .3s'; setTimeout(()=>n.remove(),300); },3000);
};
</script>
@endpush
