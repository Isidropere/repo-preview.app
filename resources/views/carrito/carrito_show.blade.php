@extends('layouts.app')
@section('title', 'Carrito de Compras - Cambialord')
@section('stile')
    <!-- Aquí tus estilos CSS -->
    <style>
        #negociacionesModal {
            position: fixed; /* importante para que quede encima del header */
            top: 0; /* ajusta según necesites */
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999; /* debe ser mayor que el del header */
            background-color: rgba(0,0,0,0.5); /* fondo semitransparente opcional */
        }
        #crearPaqueteModal {
            position: fixed;
            z-index: 10000; /* un poco más que el otro modal si se superponen */
        }

        /* 🔹 Scrollbar moderno y pequeño */
        #mensajesContainer::-webkit-scrollbar {
            width: 3px;
        }

        #mensajesContainer::-webkit-scrollbar-thumb {
            background-color: #a0aec0;
            border-radius: 8px;
        }

        #mensajesContainer::-webkit-scrollbar-track {
            background: #edf2f7;
            border-radius: 8px;
        }
    </style>
@endsection

@section('content')
<main class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-4 lg:px-8">
        @include('components.btn-volver', ['backUrl' => route('home')])
        <!-- Select All -->
        <div class="flex items-center mb-4">
            <input type="checkbox" id="selectAll" class="mr-2">
            <label for="selectAll" class="text-sm text-gray-700 font-medium">Seleccionar todos</label>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Lista de ítems -->
        <div class="lg:col-span-2 space-y-6">
          @csrf <!-- Para que tu AJAX tenga el token -->

@php
    // Combinar items de todos los carritos
    $todosLosItems = $carritos->flatMap(fn($c) => $c->itemsIntencionCompra);
@endphp

@if($todosLosItems->isEmpty())
<div class="bg-white shadow rounded-2xl p-8 text-center text-gray-400">
    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
    <p class="text-sm">Tu carrito está vacío</p>
</div>
@endif

{{-- Productos --}}
@php $itemsProducto = $todosLosItems->filter(fn($i) => (int)($i->item?->id_categoria_item ?? 0) !== 29); @endphp
@if($itemsProducto->isNotEmpty())
<div class="mb-2 flex items-center gap-2">
    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
    <span class="text-sm font-bold text-gray-700">Productos ({{ $itemsProducto->count() }})</span>
</div>
@endif
@foreach($itemsProducto as $item)
    <div class="bg-white shadow rounded-2xl p-4 sm:p-6 flex flex-col sm:flex-row gap-6">
        <div class="flex items-start mr-0">
            <input type="checkbox" 
                   class="item-checkbox item-seleccionado mt-1" 
                   value="{{ $item->id_item_intencion_compra }}" 
                   data-id="{{ $item->id_item_intencion_compra }}" 
                   {{ $item->es_seleccionado ? 'checked' : '' }}>
        </div>

        <!-- Imagen -->
        <div class="w-28 h-28 sm:w-32 sm:h-32 flex-shrink-0 mx-auto sm:mx-0">
            @if($item->imagenes->first())
                <img src="{{ \App\Helpers\ImageHelper::urlMedia('imgs/articulos/items', $item->imagenes->first()->nombre) }}" 
                     alt="{{ $item->item->item }}" 
                     class="w-full h-full object-cover rounded-xl border">
            @else
                <img src="{{ asset('storage/imgs/producto_defaul.png') }}" 
                     alt="Sin imagen" 
                     class="w-full h-full object-cover rounded-xl border">
            @endif
        </div>

        <!-- Información -->
        <div class="flex-1 flex flex-col justify-between">
            <div class="flex justify-between items-center bg-gray-100 rounded px-2 py-1">
                <span class="text-gray-800 text-sm font-semibold">
                    {{ $item->item->item }}
                </span>
                <a href="{{ route('categorias.show', $item->item->categoria->id_categoria_item) }}"
                   class="inline-block text-white text-xs px-2 py-1 rounded uppercase text-center"
                   style="background-color:#f99955;">
                    {{ $item->item->categoria->categoria }}
                </a>
            </div>

            <p class="text-gray-500 text-sm mb-1">
                {{ $item->item->presentacion }}
            </p>

            <div class="mt-2 text-sm text-gray-600">

                @if(in_array($item->item->tipo_trans, [2, 3]))
                <button onclick="listarPaquetes()"
                    class="text-orange-600 hover:underline text-xs open-negociaciones font-semibold" 
                    data-id="{{ $item->item->id_item }}">
                      🤝 Negociar con el vendedor
                </button>
                @endif

                <!-- Incrementar/Decrementar -->
                <div class="flex items-center justify-start sm:justify-end gap-2 mt-3">
                    <form action="{{ route('carrito.carrito_update', $item->id_item_intencion_compra) }}" 
                          method="POST" class="flex items-center gap-1">
                        @csrf
                        @method('PATCH')

                        <button type="submit" name="accion" value="decrementar" 
                                class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">-</button>

                        <span class="px-2 py-1 border rounded bg-white text-sm">{{ $item->cantidad }}</span>

                        <button type="submit" name="accion" value="incrementar" 
                                class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">+</button>
                    </form>
                </div>
            </div>
        </div>
       

        <!-- Precios y eliminar -->
        <div class="text-right flex flex-col justify-between">
            <div>
                <p class="text-blue-600 font-bold text-base sm:text-lg">
                    ${{ number_format($item->item->valor * $item->cantidad, 2) }}
                </p>
                <p class="text-red-500 text-xs sm:text-sm">
                    Ahorras: ${{ number_format($item->descuento, 2) }}
                </p>
            </div>

            <form action="{{ route('carrito.eliminarItem', $item->id_item) }}" method="POST" 
                  onsubmit="return confirmarEliminar();">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="mt-3 sm:mt-4 text-xs sm:text-sm text-red-600 hover:underline">
                    Eliminar
                </button>
            </form>
            <script>
                function confirmarEliminar() {
                    return confirm("¿Seguro que deseas eliminar este ítem del carrito?");
                }
            </script>

        </div>
    </div>
@endforeach

{{-- Servicios / Talentos --}}
@php $itemsServicio = $todosLosItems->filter(fn($i) => (int)($i->item?->id_categoria_item ?? 0) === 29); @endphp
@if($itemsServicio->isNotEmpty())
<div class="mt-6 mb-2 flex items-center gap-2">
    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
    <span class="text-sm font-bold text-gray-700">Servicios / Talentos ({{ $itemsServicio->count() }})</span>
</div>
@foreach($itemsServicio as $item)
    <div class="bg-orange-50 border border-orange-200 shadow-sm rounded-2xl p-4 sm:p-6 flex flex-col sm:flex-row gap-6 mb-4">
        <div class="flex items-start mr-0">
            <input type="checkbox"
                   class="item-checkbox rounded border-gray-300 text-orange-500 focus:ring-orange-500"
                   data-id="{{ $item->id_item_intencion_compra }}"
                   {{ $item->es_seleccionado ? 'checked' : '' }}>
        </div>
        <div class="flex-1">
            <div class="flex items-start gap-4">
                @php
                    $imgSrc = $item->item?->imagenes?->where('estado','aprobado')->first()?->nombre;
                    $imgUrl = $imgSrc ? \App\Helpers\ImageHelper::urlMedia('imgs/articulos/items', $imgSrc) : asset('imgs/defaults/servicio_default.svg');
                @endphp
                <img src="{{ $imgUrl }}" alt="{{ $item->item?->item }}"
                     class="w-20 h-20 rounded-xl object-cover border border-orange-100 flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-800 text-sm truncate">{{ $item->item?->item ?? 'Servicio' }}</h3>
                    <p class="text-xs text-orange-600 font-medium mt-0.5">⭐ Servicio / Talento</p>
                    <p class="text-sm font-bold text-gray-800 mt-1">RD$ {{ number_format($item->item?->valor ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-400">Cantidad: {{ $item->cantidad }}</p>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 mt-3">
                <form action="{{ route('carrito.eliminarItem', $item->id_item) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar este servicio del carrito?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endif

        </div>

        <!-- Resumen y métodos de pago -->
        <div class="space-y-6">
            <!-- Resumen -->
            <div class="bg-white shadow rounded-2xl p-6 space-y-4 sticky top-4">
                <h5 class="font-semibold text-gray-800 mb-3">Resumen del Pedido</h5>

                <div class="flex justify-between text-sm text-gray-600">
                    <span>Total de artículos:</span>
                    <span class="total_articulos">{{ number_format($totales['total_articulos'] ?? 0, 2) }}</span>
                </div>

                <div class="flex justify-between text-sm text-gray-600">
                    <span>Descuento:</span>
                    <span class="text-red-500 total_descuento">-{{ number_format($totales['total_descuento'] ?? 0, 2) }}</span>
                </div>

                <div class="flex justify-between text-sm text-gray-600">
                    <span>Envío:</span>
                    <span id="carrito-envio-costo" class="text-gray-400 text-xs">Calculando...</span>
                </div>
                <div id="carrito-envio-dias" class="text-right text-xs text-gray-400 hidden"></div>

                <hr class="my-3">

                <div class="flex justify-between font-bold text-lg text-gray-800">
                    <span>Total estimado:</span>
                    <span id="total_estimado">{{ number_format($totales['total_estimado'] ?? 0, 2) }}</span>
                </div>
            @php
                $carritoProducto = $carritos->firstWhere('tipo', 'producto');
                $carritoServicio = $carritos->firstWhere('tipo', 'servicio');
                $tieneAmbos = $carritoProducto && $carritoServicio 
                    && $carritoProducto->itemsIntencionCompra->isNotEmpty() 
                    && $carritoServicio->itemsIntencionCompra->isNotEmpty();
            @endphp

            @if($tieneAmbos)
            {{-- Selector de carrito cuando hay ambos tipos --}}
            <div class="mt-4 space-y-2">
                <p class="text-sm font-semibold text-gray-700 mb-2">¿Qué deseas pagar?</p>
                <form action="{{ route('carrito.checkout_index') }}" method="GET">
                    <input type="hidden" name="tipo" value="producto">
                    <button type="submit"
                        style="width:100%;display:flex;align-items:center;justify-content:center;gap:0.5rem;background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;border:none;border-radius:0.75rem;padding:0.75rem;font-size:0.88rem;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(37,99,235,0.3);margin-bottom:0.5rem;">
                        <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Pagar Productos ({{ $carritoProducto->itemsIntencionCompra->count() }})
                    </button>
                </form>
                <form action="{{ route('carrito.checkout_index') }}" method="GET">
                    <input type="hidden" name="tipo" value="servicio">
                    <button type="submit"
                        style="width:100%;display:flex;align-items:center;justify-content:center;gap:0.5rem;background:linear-gradient(135deg,#ea580c,#f58634);color:#fff;border:none;border-radius:0.75rem;padding:0.75rem;font-size:0.88rem;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(245,134,52,0.3);">
                        <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        Pagar Servicios ({{ $carritoServicio->itemsIntencionCompra->count() }})
                    </button>
                </form>
            </div>
            @else
            <form action="{{ route('carrito.checkout_index') }}" method="GET">
                <button type="submit" 
                    class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-medium shadow">
                    Proceder al Pago
                </button>
            </form>
            @endif
                        </div>

            <!-- Métodos de pago -->
           <div class="bg-white shadow rounded-2xl p-6 space-y-4">
    <h5 class="font-semibold text-gray-800">Para compra directa:</h5>
    <p class="text-sm text-gray-500">
        Los items se reservarán temporalmente hasta confirmar el pago.
    </p>

    <h5 class="font-semibold text-gray-800">Para intercambios:</h5>
    <p class="text-sm text-gray-500">
        Los items o servicios se reservarán temporalmente hasta confirmar el pago entre ambas partes
    </p>

    <h6 class="font-semibold text-gray-800 mb-2 text-lg">Medios de pago aceptados:</h6>

    <!-- Logos de medios de pago -->
   <div class="mt-6 flex items-center gap-4">
    <img src="{{ asset('https://images.seeklogo.com/logo-png/14/1/visa-logo-png_seeklogo-149684.png') }}" alt="Visa" class="h-8">
  
</div>
</div>

        </div>
    </div>

</main>

    <!-- Modal negociaciones -->
<div id="negociacionesModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:1rem;">

    <div style="background:#fff;border-radius:1.25rem;box-shadow:0 20px 60px rgba(0,0,0,.3);width:100%;max-width:480px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden;">

        <!-- Header con gradiente naranja -->
        <div style="background:linear-gradient(135deg,#ea580c 0%,#f58634 60%,#fb923c 100%);padding:1.1rem 1.4rem;flex-shrink:0;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:0.65rem;">
                <div style="width:2.2rem;height:2.2rem;background:rgba(255,255,255,0.2);border-radius:0.65rem;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:1.1rem;height:1.1rem;color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div>
                    <h4 style="font-size:0.95rem;font-weight:800;color:#fff;margin:0;letter-spacing:-0.01em;">🤝 Negociación de Intercambio</h4>
                    <p style="font-size:0.7rem;color:rgba(255,255,255,0.85);margin:0.1rem 0 0;">Propón tu oferta al vendedor</p>
                </div>
            </div>
            <button id="closeModal"
                style="width:1.9rem;height:1.9rem;background:rgba(255,255,255,0.2);border:none;border-radius:50%;color:#fff;font-size:1.1rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s;"
                onmouseover="this.style.background='rgba(255,255,255,0.35)'"
                onmouseout="this.style.background='rgba(255,255,255,0.2)'">&times;</button>
        </div>

        <!-- Body scrollable -->
        <div id="negociacionesContent" style="padding:1rem 1.25rem;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:0.85rem;">

            <!-- 📨 Mensajes previos -->
            <div id="mensajesContainer"
                style="height:220px;max-height:220px;overflow-y:auto;border:2px solid #fed7aa;border-radius:0.75rem;padding:0.6rem;background:#fff7ed;font-size:0.82rem;color:#374151;scrollbar-width:thin;">
                <p style="text-align:center;color:#9ca3af;font-size:0.82rem;">Cargando mensajes...</p>
            </div>

            <!-- 🧩 Acción predefinida -->
            <div>
                <label style="display:block;font-size:0.78rem;font-weight:700;color:#374151;margin-bottom:0.3rem;">Acción a realizar</label>
                <select id="AccionPredefinido"
                    style="width:100%;border:2px solid #fed7aa;border-radius:0.65rem;font-size:0.82rem;padding:0.5rem 0.75rem;background:#fff7ed;color:#374151;outline:none;transition:border-color .15s;"
                    onfocus="this.style.borderColor='#f58634'" onblur="this.style.borderColor='#fed7aa'">
                    <option value="">-- Seleccione una acción --</option>
                    @foreach ($accion as $msg1Accion)
                        <option value="{{ $msg1Accion->tipo }}" data-tipo-accion="{{ $msg1Accion->tipo }}">
                            {{ ucfirst($msg1Accion->tipo) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="accionInput" id="accionInput">

            <!-- 🧩 Mensaje predefinido -->
            <div>
                <label style="display:block;font-size:0.78rem;font-weight:700;color:#374151;margin-bottom:0.3rem;">Mensaje predefinido</label>
                <select id="mensajePredefinido"
                    style="width:100%;border:2px solid #fed7aa;border-radius:0.65rem;font-size:0.82rem;padding:0.5rem 0.75rem;background:#fff7ed;color:#374151;outline:none;transition:border-color .15s;"
                    onfocus="this.style.borderColor='#f58634'" onblur="this.style.borderColor='#fed7aa'">
                    <option value="">-- Selecciona --</option>
                    @foreach ($mensajesPredefinidos->where('activo', true)->whereIn('rol', ['emisor','general']) as $msg)
                        <option value="{{ $msg->mensaje }}" data-tipo="{{ $msg->tipo }}" data-rol="{{ $msg->rol }}">
                            {{ $msg->titulo }} ({{ ucfirst($msg->tipo) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- ✏️ Mensaje -->
            <div>
                <label style="display:block;font-size:0.78rem;font-weight:700;color:#374151;margin-bottom:0.3rem;">Mensaje</label>
                <textarea id="mensaje" rows="2" readonly
                    style="width:100%;border:2px solid #fed7aa;border-radius:0.65rem;padding:0.5rem 0.75rem;font-size:0.82rem;resize:none;box-sizing:border-box;background:#fff7ed;outline:none;"
                    placeholder="Selecciona un mensaje predefinido..."></textarea>
            </div>

            <!-- 📦 Seleccionar paquete -->
            <div>
                <label style="display:block;font-size:0.78rem;font-weight:700;color:#374151;margin-bottom:0.3rem;">Seleccionar paquete</label>
                <select id="paquete"
                    style="width:100%;border:2px solid #fed7aa;border-radius:0.65rem;font-size:0.82rem;padding:0.5rem 0.75rem;background:#fff7ed;color:#374151;outline:none;transition:border-color .15s;"
                    onfocus="this.style.borderColor='#f58634'" onblur="this.style.borderColor='#fed7aa'">
                    <option value="">-- Selecciona un paquete existente --</option>
                    @foreach ($todoLosPaquetes as $pkg)
                        <option value="{{ $pkg->id_paquete }}" data-tipo="{{ $pkg->nombre_paquete }}">
                            {{ $pkg->nombre_paquete }} ({{ $pkg->id_paquete }})
                        </option>
                    @endforeach
                </select>
                <button id="btnListaPaquetes" onclick="listarPaquetes()"
                    style="font-size:0.75rem;color:#f58634;background:none;border:none;cursor:pointer;margin-top:0.35rem;padding:0;font-weight:600;">
                    📦 Ver mis paquetes
                </button>
                <div id="contenedorPaquetes"
                    style="margin-top:0.4rem;display:flex;gap:0.5rem;overflow-x:auto;padding:0.4rem;border:1px solid #fed7aa;border-radius:0.65rem;white-space:nowrap;min-height:2rem;background:#fff7ed;">
                    <p style="color:#9ca3af;font-size:0.78rem;">Esperando paquetes...</p>
                </div>
                <button id="crearPaqueteBtn" type="button"
                    style="font-size:0.75rem;color:#f58634;background:none;border:none;cursor:pointer;margin-top:0.35rem;padding:0;font-weight:600;">
                    + Crear nuevo paquete
                </button>
            </div>

            <!-- 💰 Monto de oferta -->
            <div>
                <label style="display:block;font-size:0.78rem;font-weight:700;color:#374151;margin-bottom:0.3rem;">Monto de la oferta <span style="font-weight:400;color:#9ca3af;">(opcional)</span></label>
                <div style="position:relative;">
                    <span style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.82rem;pointer-events:none;">RD$</span>
                    <input type="number" id="montoOferta"
                        style="width:100%;border:2px solid #fed7aa;border-radius:0.65rem;padding:0.5rem 0.75rem 0.5rem 2.75rem;font-size:0.82rem;box-sizing:border-box;background:#fff7ed;outline:none;transition:border-color .15s;"
                        onfocus="this.style.borderColor='#f58634'" onblur="this.style.borderColor='#fed7aa'"
                        placeholder="0.00">
                </div>
            </div>

            <!-- 🔘 Botones -->
            <div style="display:flex;gap:0.65rem;padding-top:0.25rem;flex-shrink:0;">
                <button id="cancelarBtn"
                    style="flex:1;padding:0.65rem 1rem;border:2px solid #e5e7eb;border-radius:0.75rem;background:#fff;color:#6b7280;font-size:0.85rem;font-weight:700;cursor:pointer;transition:all .15s;"
                    onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                    Cancelar
                </button>
                <button id="enviarNegociacionBtn"
                    style="flex:2;padding:0.65rem 1rem;border:none;border-radius:0.75rem;background:linear-gradient(135deg,#ea580c,#f58634);color:#fff;font-size:0.88rem;font-weight:800;cursor:pointer;box-shadow:0 4px 14px rgba(245,134,52,0.4);transition:all .15s;display:flex;align-items:center;justify-content:center;gap:0.4rem;"
                    onmouseover="this.style.boxShadow='0 6px 20px rgba(245,134,52,0.5)';this.style.transform='translateY(-1px)'"
                    onmouseout="this.style.boxShadow='0 4px 14px rgba(245,134,52,0.4)';this.style.transform='translateY(0)'">
                    <svg style="width:1rem;height:1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Enviar propuesta
                </button>
            </div>

        </div>{{-- /body --}}
    </div>
</div>

<!-- 🆕 Modal Crear/Editar Paquete -->
<div id="crearPaqueteModal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-60">

    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-5 relative text-sm">
        <!-- Botón cerrar -->
        <button id="closeCrearPaqueteModal"
            class="absolute top-2 right-2 text-red-600 hover:text-red-800 text-2xl font-bold">
            &times;
        </button>
        <br>

        <h3 id="tituloPaqueteModal" class="text-lg font-bold text-gray-800 mb-3">
            Crear nuevo paquete
        </h3>

        <!-- Nombre del paquete -->
        <div class="space-y-1.5 mb-3">
            <label for="nombrePaquete" class="text-gray-700 font-semibold text-sm">
                Nombre del paquete
            </label>
            <input type="text" id="nombrePaquete" maxlength="20"
                class="w-full border rounded-md p-1.5 text-sm focus:ring focus:ring-blue-100"
                placeholder="Ej: Paquete oferta especial">
        </div>

        <!-- Lista de items del usuario -->
        <div class="space-y-1.5 mb-3">
            <label class="text-gray-700 font-semibold text-sm">Selecciona tus items</label>
            <div id="listaItemsUsuario"
                class="overflow-y-auto border rounded-md p-2 bg-gray-50 text-sm text-gray-800 scroll-smooth"
                style="
                    height: 135px;
                    max-height: 135px;
                    scrollbar-width: thin;
                    scrollbar-color: #a0aec0 #edf2f7;
                ">
                <p class="text-center text-gray-400">Cargando items...</p>
            </div>
        </div>

        <!-- Resumen -->
        <div class="space-y-1.5 mb-3">
            <label class="text-gray-700 font-semibold text-sm">Valor total del paquete</label>
            <p id="valorTotalPaquete" class="text-base font-bold text-blue-600">0.00</p>
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-3 pt-3">
            <button id="cancelarCrearPaqueteBtn"
                class="px-4 py-1.5 rounded-md border text-gray-700 hover:bg-gray-100 text-sm">
                Cancelar
            </button>
            <button id="guardarPaqueteBtn"
                class="px-4 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                Guardar paquete
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
window.paqueteId = null;
window.itemsSeleccionados = [];
window.totalPaquete = 0;

// =============================
// 🔹 FUNCIÓN PARA MOSTRAR ESPERA
// =============================
window.conProcesando = async function(func) {
    try {
        document.body.classList.add('cursor-wait'); // Cambia cursor
        await func();
    } finally {
        document.body.classList.remove('cursor-wait');
    }
};
// =============================
// 🔹 ACTUALIZAR ITEMS SELECCIONADOS
// =============================
window.actualizarItemsSeleccionados = function() {
    try {
        const checkboxes = document.querySelectorAll(".itemCheckbox");
        let total = 0;
        const seleccionados = [];

        checkboxes.forEach(cb => {
            if (cb.checked) {
                seleccionados.push(parseInt(cb.value));
                total += parseFloat(cb.dataset.valor);
            }
        });

        window.itemsSeleccionados = seleccionados;
        window.totalPaquete = total;

        if (valorTotalPaquete) valorTotalPaquete.textContent = total.toFixed(2);
    } catch (error) {
        console.error("Error actualizando items seleccionados:", error);
    }
};

// =============================
// 🔹 LISTAR PAQUETES
// =============================
window.listarPaquetes = async function() {
    const contenedor = document.getElementById("contenedorPaquetes");
    if (!contenedor) return;
    contenedor.innerHTML = `<p class="text-center text-gray-400">Cargando paquetes...</p>`;

    try {
        const res = await fetch('/carrito/listarPaquetes');
        if (!res.ok) throw new Error('Error al cargar paquetes');
        const paquetes = await res.json();

        if (!paquetes.length) {
            contenedor.innerHTML = '<p class="text-center text-gray-400">No tienes paquetes aún.</p>';
            return;
        }

        contenedor.innerHTML = "";
        paquetes.forEach(p => {
            const div = document.createElement("div");
            div.className = "bg-gray-100 p-3 rounded-lg shadow text-center min-w-[150px] hover:bg-blue-100 cursor-pointer relative";
            div.innerHTML = `
                <div class="relative p-3 border rounded-md">
                    <strong>${p.nombre_paquete}</strong><br>
                    <span class="text-xs text-gray-400">#${p.id_paquete}</span>
                    <button class="absolute bottom-1 left-0 bg-red-500 text-white hover:bg-red-600 border rounded p-0.5 shadow text-xs" title="Eliminar paquete">🗑️</button>
                </div>
            `;

            // Click en la tarjeta (excepto eliminar)
            div.addEventListener('click', async (e) => {
                if (e.target.tagName.toLowerCase() === 'button') return;
                const contenidoOriginal = div.innerHTML;
                div.innerHTML = `<span class="text-gray-400">Cargando paquete...</span>`;
                try {
                    await window.obtenerPaquete(p.id_paquete);
                } catch (err) {
                    div.innerHTML = `<span class="text-red-500">Error al cargar</span>`;
                    console.error(err);
                } finally {
                    div.innerHTML = contenidoOriginal;
                }
            });

            // Click en eliminar paquete
            const eliminarBtn = div.querySelector('button');
            eliminarBtn.addEventListener('click', async (e) => {
                e.stopPropagation();
                if (!confirm(`¿Eliminar paquete "${p.nombre_paquete}"?`)) return;
                try {
                    const token = document.querySelector('input[name="_token"]').value;
                    const res = await fetch(`/carrito/eliminarPaquete/${p.id_paquete}`, {
                        method: 'DELETE',
                        headers: { "X-CSRF-TOKEN": token, "Accept": "application/json" }
                    });
                    if (!res.ok) throw new Error('Error al eliminar paquete');
                    alert('Paquete eliminado');
                    div.remove();
                } catch (err) {
                    alert('No se pudo eliminar el paquete');
                    console.error(err);
                }
            });

            contenedor.appendChild(div);
        });
    } catch (error) {
        console.error(error);
        contenedor.innerHTML = '<p class="text-center text-red-500">Error al cargar paquetes.</p>';
    }
};

// Botón de listar paquetes
document.getElementById('btnListaPaquetes')?.addEventListener('click', listarPaquetes);

// =============================
// 🔹 OBTENER DETALLE DE PAQUETE
// =============================
window.obtenerPaquete = async function(id) {
    try {
        const resp = await fetch(`/carrito/obtenerPaquete/${id}`);
        if (!resp.ok) throw new Error('Error al obtener paquete');
        const data = await resp.json();

        const paquete = data.paquete;
        const items = data.items;
        const todosItems = data.todosItems;

        window.itemsSeleccionados = items.map(i => i.id_item);
        window.paqueteId = paquete.id_paquete;

        if (tituloPaqueteModal) tituloPaqueteModal.textContent = `Editar paquete: ${paquete.nombre_paquete}, #${paquete.id_paquete}`;
        if (nombrePaquete) nombrePaquete.value = paquete.nombre_paquete || '';

        if (listaItemsUsuario) {
            listaItemsUsuario.innerHTML = '';
            todosItems.forEach(it => {
                const seleccionado = items.some(i => i.id_item === it.id_item);
                listaItemsUsuario.innerHTML += `
                    <div class="flex justify-between items-center border-b py-1">
                        <label class="flex items-center space-x-2 w-full cursor-pointer">
                            <input type="checkbox" value="${it.id_item}" data-valor="${it.valor}" class="itemCheckbox" ${seleccionado ? 'checked' : ''}>
                            <span>${it.item}</span>
                        </label>
                        <span class="text-blue-600 font-semibold">$${parseFloat(it.valor).toFixed(2)}</span>
                    </div>
                `;
            });

            // Inicializar total y items seleccionados
            actualizarItemsSeleccionados();

            // Escuchar cambios en los checkboxes
            document.querySelectorAll(".itemCheckbox").forEach(cb => {
                cb.addEventListener("change", actualizarItemsSeleccionados);
            });
        }

        // Cambiar botón a "Editar paquete"
        const btn = document.getElementById('guardarPaqueteBtn');
        if (btn) btn.textContent = 'Editar paquete';

        // Mostrar modales
        const modalNegociaciones = document.getElementById('negociacionesModal');
        const modalPaquete = document.getElementById('crearPaqueteModal');
        if (modalNegociaciones) { modalNegociaciones.style.display = 'flex'; modalNegociaciones.style.zIndex = "40"; }
        if (modalPaquete) { modalPaquete.classList.remove('hidden'); modalPaquete.style.zIndex = "50"; }

    } catch (error) {
        console.error('Error al obtener paquete:', error);
        alert('No se pudo cargar el paquete.');
    }
};

// =============================
// 🔄 ACTUALIZAR LISTA DE PAQUETES EN EL SELECT
// =============================
window.actualizarSelectPaquetes = async function() {
    const select = document.getElementById("paquete");
    if (!select) return;

    try {
         await conProcesando(async () => {
        const res = await fetch('/carrito/listarPaquetes');
        if (!res.ok) throw new Error('Error al cargar los paquetes');

        const paquetes = await res.json();

        select.innerHTML = `<option value="">-- Selecciona un paquete existente --</option>`;

        paquetes.forEach(p => {
            const option = document.createElement("option");
            option.value = p.id_paquete;
            option.dataset.tipo = p.nombre_paquete;
            option.textContent = `${p.nombre_paquete} (${p.id_paquete})`;
            select.appendChild(option);
        });

         });
    } catch (error) {
        console.error("Error actualizando select de paquetes:", error);
    }
};




</script>


<script>


document.addEventListener("DOMContentLoaded", () => {
    // =============================
    // ✅ SELECCIÓN DE ITEMS EN CARRITO
    // =============================
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.item-checkbox');

    async function marcarSeleccionado(id, estado) {
        try {
            await conProcesando(async () => { 
            const res = await fetch("{{ route('carrito.marcarSeleccionados') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id_item: id, es_seleccionado: estado })
            });

            const data = await res.json();
            if (data.status === 'ok' && data.totales) {
                document.querySelector('.total_articulos').textContent = parseFloat(data.totales.total_articulos).toFixed(2);
                document.querySelector('.total_descuento').textContent = '-' + parseFloat(data.totales.total_descuento).toFixed(2);
                document.getElementById('total_estimado').textContent = parseFloat(data.totales.total_estimado).toFixed(2);
                // Recalcular envío con el nuevo total
                if (typeof recalcularEnvio === 'function') recalcularEnvio();
            }
            });
        } catch (err) {
            console.error("Error al marcar seleccionado:", err);
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            marcarSeleccionado(this.value, this.checked);
            if (selectAllCheckbox)
                selectAllCheckbox.checked = Array.from(checkboxes).every(c => c.checked);
        });
    });

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                marcarSeleccionado(cb.value, cb.checked);
            });
        });
    }

    // =============================
    // 🟦 MODAL DE NEGOCIACIONES
    // =============================
    const modal = document.getElementById("negociacionesModal");
    const closeModal = document.getElementById("closeModal");
    const cancelarBtn = document.getElementById("cancelarBtn");
    const enviarBtn = document.getElementById("enviarNegociacionBtn");
    const mensajesContainer = document.getElementById("mensajesContainer");
    const mensajeInput = document.getElementById("mensaje");
    const paqueteSelect = document.getElementById("paquete");
    const montoInput = document.getElementById("montoOferta");
    const mensajePredefinidoSelect = document.getElementById("mensajePredefinido");
   const AccionPredefinidoSelect = document.getElementById("AccionPredefinido");

    if (mensajePredefinidoSelect) {
        mensajePredefinidoSelect.addEventListener("change", () => {
            mensajeInput.value = mensajePredefinidoSelect.value;
        });
    }

    if (AccionPredefinidoSelect) {
        AccionPredefinidoSelect.addEventListener("change", () => {
            if (document.getElementById("accionInput")) {
                document.getElementById("accionInput").value = AccionPredefinidoSelect.value;
            }
        });
    }

    document.querySelectorAll(".open-negociaciones").forEach(btn => {
        btn.addEventListener("click", async () => {
            const itemId = btn.dataset.id;
            modal.style.display = 'flex';
            modal.dataset.itemId = itemId;
            mensajesContainer.innerHTML = `<p style="text-align:center;color:#9ca3af;font-size:0.82rem;">Cargando negociaciones...</p>`;

            try {
              await conProcesando(async () => {
                const res = await fetch(`/carrito/getnegociaciones/${itemId}`, {
                    headers: { "Accept": "application/json" }
                });
                if (!res.ok) throw new Error("Error al obtener negociaciones");
                const data = await res.json();

                mensajesContainer.innerHTML = data.mensajes?.length
                    ? data.mensajes.map(msg => {
                        const esPropio = msg.propio;
                        const align = esPropio ? "justify-end" : "justify-start";
                        const bgColor = esPropio ? "bg-blue-500" : "bg-gray-100";
                        const textColor = esPropio ? "text-black" : "text-gray-900";
                        return `
                            <div class="flex ${align} mb-2">
                                <div class="max-w-xs md:max-w-md px-4 py-2 rounded-lg ${bgColor} ${textColor} shadow break-words">
                                    <p>${msg.texto}</p>
                                    <small class="block text-right text-gray-400 text-xs mt-1">${msg.fecha}</small>
                                </div>
                            </div>
                        `;
                    }).join('')
                    : `<p class="text-center text-gray-400">Sin mensajes aún.</p>`;

                paqueteSelect.innerHTML = `<option value="">-- Selecciona un paquete existente --</option>` +
                    (data.paquetes || []).map(p => `<option value="${p.id}">${p.nombre}</option>`).join('');

                    // 🔽 Desplazar automáticamente al final
                            mensajesContainer.scrollTop = mensajesContainer.scrollHeight;
            });
            } catch (error) {
                mensajesContainer.innerHTML = `<p class="text-red-500 text-center">Error al cargar las negociaciones.</p>`;
            }
        });
    });

    [closeModal, cancelarBtn].forEach(b => b?.addEventListener("click", () => { modal.style.display = 'none'; }));
    modal.addEventListener("click", e => { if (e.target === modal) modal.style.display = 'none'; });

// =============================
// 🔴 VALIDACIÓN VISUAL INLINE
// =============================
function mostrarErrorModal(campo, msg) {
    limpiarErrorModal(campo);
    if (!campo.dataset.originalBorder) {
        campo.dataset.originalBorder = campo.style.border || '';
    }
    campo.style.border = '2px solid #ef4444';
    const span = document.createElement('span');
    span.className = 'modal-error-msg';
    span.style.cssText = 'color:#ef4444;font-size:0.75rem;margin-top:0.25rem;display:block;';
    span.textContent = msg;
    campo.parentNode.insertBefore(span, campo.nextSibling);
}

function limpiarErrorModal(campo) {
    campo.style.border = campo.dataset.originalBorder || '';
    const siguiente = campo.nextElementSibling;
    if (siguiente && siguiente.classList.contains('modal-error-msg')) {
        siguiente.remove();
    }
}

// Limpiar errores al escribir/cambiar
mensajeInput?.addEventListener('input', () => limpiarErrorModal(mensajeInput));
mensajePredefinidoSelect?.addEventListener('change', () => limpiarErrorModal(mensajePredefinidoSelect));

enviarBtn?.addEventListener("click", async () => {
    const itemId = modal.dataset.itemId;
    const mensaje = mensajeInput.value.trim();
    const paquete = paqueteSelect.value || null;
    const monto = montoInput.value || null;

    // Validación inline
    let hayError = false;

    if (!itemId) {
        console.error('item_id no definido. modal.dataset:', modal.dataset);
        mostrarErrorModal(mensajeInput, 'Error: no se identificó el producto. Cierra y abre el modal de nuevo.');
        return;
    }

    if (!mensajePredefinidoSelect.value) {
        mostrarErrorModal(mensajePredefinidoSelect, 'Selecciona un mensaje predefinido');
        hayError = true;
    }

    if (!mensaje) {
        mostrarErrorModal(mensajeInput, 'El mensaje es obligatorio');
        hayError = true;
    }

    if (hayError) return;

    try {
        await conProcesando(async () => {
            const res = await fetch(`{{ route('carrito.save_negociaciones') }}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ 
                    item_id: itemId, 
                    mensaje, 
                    paquete_id: paquete, 
                    monto_oferta: monto,
                    accionInput: document.getElementById("accionInput")?.value || null
                })
            });

            if (!res.ok) {
                const errText = await res.text();
                throw new Error(`Error ${res.status}: ${errText.substring(0, 100)}`);
            }

            const data = await res.json();

            alert(data.status === "ok" 
                ? "✅ Negociación enviada correctamente." 
                : "⚠️ " + (data.message || "Algo salió mal al guardar la negociación.")
            );

            mensajeInput.value = "";
            montoInput.value = "";
            paqueteSelect.value = "";
            mensajePredefinidoSelect.value = "";
            AccionPredefinidoSelect.value = "";
            if (document.getElementById("accionInput")) document.getElementById("accionInput").value = "";
            modal.style.display = 'none';
        });
    } catch (err) {
        console.error("Error al enviar negociación:", err);
        alert("❌ Error de conexión al enviar la negociación. Verifica tu sesión e intenta de nuevo.");
    }
});

    // =============================
    // 🟪 MODAL CREAR / EDITAR PAQUETE
    // =============================
    const crearPaqueteBtn = document.getElementById("crearPaqueteBtn");
    const crearPaqueteModal = document.getElementById("crearPaqueteModal");
    const closeCrearPaqueteModal = document.getElementById("closeCrearPaqueteModal");
    const cancelarCrearPaqueteBtn = document.getElementById("cancelarCrearPaqueteBtn");
    const guardarPaqueteBtn = document.getElementById("guardarPaqueteBtn");
    const listaItemsUsuario = document.getElementById("listaItemsUsuario");
    const valorTotalPaquete = document.getElementById("valorTotalPaquete");
    const nombrePaquete = document.getElementById("nombrePaquete");
    const tituloPaqueteModal = document.getElementById("tituloPaqueteModal");

    async function abrirModal(paquete = null) {
        crearPaqueteModal.classList.remove("hidden");
        crearPaqueteModal.style.zIndex = 9999;
        window.itemsSeleccionados = [];
        window.totalPaquete = 0;
        valorTotalPaquete.textContent = "0.00";
        listaItemsUsuario.innerHTML = `<p class="text-center text-gray-400">Cargando items...</p>`;

        const btn = guardarPaqueteBtn;

        if (paquete) {
            window.paqueteId = paquete.paquete_id;
            nombrePaquete.value = paquete.nombre_paquete;
            tituloPaqueteModal.textContent = "Editar paquete";
            btn.textContent = 'Editar paquete';
        } else {
            window.paqueteId = null;
            nombrePaquete.value = "";
            tituloPaqueteModal.textContent = "Crear nuevo paquete";
            btn.textContent = 'Agregar paquete';
        }

        try {
            const res = await fetch(`/carrito/items-usuario`);
            const items = await res.json();
            listaItemsUsuario.innerHTML = "";

            items.forEach(it => {
                const checked = paquete?.items?.includes(it.id_item) ? "checked" : "";
                if (checked) {
                    window.itemsSeleccionados.push(it.id_item);
                    window.totalPaquete += parseFloat(it.valor);
                }

                listaItemsUsuario.innerHTML += `
                    <div class="flex items-center justify-between p-2 border-b">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" class="item-checkbox-usuario" data-id="${it.id_item}" data-valor="${it.valor}" ${checked}>
                            <span>${it.item}</span>
                        </label>
                        <span class="text-gray-600 text-sm">${parseFloat(it.valor).toFixed(2)} USD</span>
                    </div>`;
            });

            valorTotalPaquete.textContent = window.totalPaquete.toFixed(2);

            document.querySelectorAll('.item-checkbox-usuario').forEach(cb => {
                cb.addEventListener('change', function() {
                    const valor = parseFloat(this.dataset.valor);
                    const id = parseInt(this.dataset.id);

                    if (this.checked) {
                        if (!window.itemsSeleccionados.includes(id)) {
                            window.itemsSeleccionados.push(id);
                            window.totalPaquete += valor;
                        }
                    } else {
                        window.itemsSeleccionados = window.itemsSeleccionados.filter(i => i !== id);
                        window.totalPaquete -= valor;
                    }

                    valorTotalPaquete.textContent = window.totalPaquete.toFixed(2);
                });
            });

        } catch (error) {
            listaItemsUsuario.innerHTML = `<p class="text-center text-red-500">Error al cargar items.</p>`;
        }
    }

    crearPaqueteBtn?.addEventListener("click", () => abrirModal());
    [closeCrearPaqueteModal, cancelarCrearPaqueteBtn].forEach(btn => btn?.addEventListener("click", () => {
        crearPaqueteModal.classList.add("hidden");
        window.itemsSeleccionados = [];
        window.totalPaquete = 0;
    }));
   
    guardarPaqueteBtn?.addEventListener("click", async () => {
    
        const nombre = nombrePaquete.value.trim();
        if (!nombre) return alert("Escribe un nombre para el paquete");
        if (!window.paqueteId && window.itemsSeleccionados.length === 0) return alert("Selecciona al menos un item");

       
        const url = window.paqueteId ? `/carrito/editarPaquete/${window.paqueteId}` : `/carrito/crearPaquete`;
        const method = window.paqueteId ? "PUT" : "POST";

        try {
              await conProcesando(async () => { 
            const res = await fetch(url, {
                method,
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ nombre, items: window.itemsSeleccionados })
            });

            if (!res.ok) throw new Error("Error al guardar paquete");
            //alert("✅ Paquete guardado correctamente.");

             // ✅ ACTUALIZA EL SELECT AUTOMÁTICAMENTE
            await window.actualizarSelectPaquetes();


            crearPaqueteModal.classList.add("hidden");
            window.itemsSeleccionados = [];
            window.totalPaquete = 0;
            nombrePaquete.value = "";
            valorTotalPaquete.textContent = "0.00";
            await window.listarPaquetes();
             });
        } catch (error) {
            alert("❌ No se pudo guardar el paquete. Intenta nuevamente.");
        }
         
    });
});
</script>

<script>
// Cálculo de envío en carrito
window.costoEnvioActual = 0;
const municipioCarrito = @json($municipioDefault ?? '');

function agregarDiasHabiles(desde, dias) {
    const fecha = new Date(desde);
    let agregados = 0;
    while (agregados < dias) {
        fecha.setDate(fecha.getDate() + 1);
        const dow = fecha.getDay();
        if (dow !== 0 && dow !== 6) agregados++;
    }
    return fecha;
}

function formatearFecha(fecha) {
    const opciones = { weekday: 'long', day: 'numeric', month: 'long' };
    return fecha.toLocaleDateString('es-DO', opciones);
}

window.recalcularEnvio = function() {
    const elCosto = document.getElementById('carrito-envio-costo');
    const elDias  = document.getElementById('carrito-envio-dias');
    const totalEstEl = document.getElementById('total_estimado');
    const totalSinEnvio = parseFloat(totalEstEl?.textContent?.replace(/,/g,'') || 0);

    if (!municipioCarrito || totalSinEnvio <= 0) {
        window.costoEnvioActual = 0;
        if (elCosto) { elCosto.textContent = 'Gratis'; elCosto.style.color = '#16a34a'; }
        if (elDias) elDias.classList.add('hidden');
        totalEstEl.textContent = totalSinEnvio.toFixed(2);
        return;
    }

    fetch('/delivery/calcular?pueblo=' + encodeURIComponent(municipioCarrito) + '&valor_articulo=' + totalSinEnvio)
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(d => {
            if (!elCosto) return;
            const costo = parseFloat(d.costo_envio_total ?? 0);
            if (d.success && costo > 0) {
                window.costoEnvioActual = costo;
                elCosto.textContent = 'RD$ ' + costo.toLocaleString('es-DO', {minimumFractionDigits:2});
                elCosto.style.color = '#374151';
                if (elDias && d.dias_habiles) {
                    const fechaEntrega = agregarDiasHabiles(new Date(), d.dias_habiles);
                    elDias.textContent = '🚚 Entrega estimada: ' + formatearFecha(fechaEntrega) + ' (~' + d.dias_habiles + ' días hábiles)';
                    elDias.classList.remove('hidden');
                }
            } else {
                window.costoEnvioActual = 0;
                elCosto.textContent = 'Gratis';
                elCosto.style.color = '#16a34a';
                if (elDias) elDias.classList.add('hidden');
            }
            // Sumar envío al total estimado
            totalEstEl.textContent = (totalSinEnvio + window.costoEnvioActual).toFixed(2);
        })
        .catch(() => {
            window.costoEnvioActual = 0;
            if (elCosto) {
                elCosto.textContent = 'No se pudo calcular el envío';
                elCosto.style.color = '#ef4444';
            }
            if (elDias) elDias.classList.add('hidden');
            totalEstEl.textContent = totalSinEnvio.toFixed(2);
        });
};

// Ejecutar al cargar
recalcularEnvio();
</script>

@endpush






