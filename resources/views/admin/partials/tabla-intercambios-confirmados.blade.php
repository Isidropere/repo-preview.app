@php
    $totalPendientesPago = 0;
    $totalListosEnvio = 0;
    $totalEnEnvio = 0;
    $totalFinalizados = 0;

    // Estadísticas de trueques
    foreach($intercambiosConfirmados as $neg) {
        if ($neg->estado === 'completado') {
            $totalFinalizados++;
        } elseif ($neg->estado === 'en_envio') {
            $totalEnEnvio++;
        } elseif ($neg->pago_emisor && $neg->pago_receptor) {
            $totalListosEnvio++;
        } else {
            $totalPendientesPago++;
        }
    }

    // Estadísticas de ventas
    foreach($ventasConfirmadas as $compra) {
        if ($compra->estatus === 'entregado') {
            $totalFinalizados++;
        } elseif ($compra->estatus === 'enviado') {
            $totalEnEnvio++;
        } elseif ($compra->estatus === 'aprobado') {
            $totalListosEnvio++;
        } else {
            $totalPendientesPago++;
        }
    }
@endphp

<div class="p-6 bg-white border-b border-gray-100">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                📦 Tablero de Gestión de Envíos (Ventas e Intercambios)
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">Gestione y procese la logística de entrega tanto para compras directas aprobadas como para intercambios completados.</p>
        </div>
        <div>
            <button onclick="openFlowModal()" 
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg shadow-sm transition-all hover:scale-[1.02] border-0 cursor-pointer">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; display: inline-block;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Ver Flujo de Estados
            </button>
        </div>
    </div>

    {{-- KPI Cards Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 flex items-center gap-3 shadow-sm">
            <div class="bg-amber-100 text-amber-600 p-2.5 rounded-lg flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Esperando Pago / Proceso</p>
                <p class="text-lg font-black text-gray-900 mt-0.5">{{ $totalPendientesPago }}</p>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-center gap-3 shadow-sm ring-2 ring-blue-500/10">
            <div class="bg-blue-500 text-white p-2.5 rounded-lg flex-shrink-0 animate-pulse">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 001.414 0l6.586-6.586"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Listos para Envío</p>
                <p class="text-lg font-black text-blue-900 mt-0.5">{{ $totalListosEnvio }}</p>
            </div>
        </div>

        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex items-center gap-3 shadow-sm">
            <div class="bg-indigo-100 text-indigo-600 p-2.5 rounded-lg flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m9-1h1m-1 0a1 1 0 011 1v.5a1.5 1.5 0 01-3 0v-.5a1 1 0 011-1zm0-8h3l4 4v4a1 1 0 01-1 1h-1m-6 0h-2"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">En Tránsito</p>
                <p class="text-lg font-black text-indigo-900 mt-0.5">{{ $totalEnEnvio }}</p>
            </div>
        </div>

        <div class="bg-green-50 border border-green-100 rounded-xl p-4 flex items-center gap-3 shadow-sm">
            <div class="bg-green-100 text-green-600 p-2.5 rounded-lg flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-green-600 uppercase tracking-wider">Entregas Completadas</p>
                <p class="text-lg font-black text-green-900 mt-0.5">{{ $totalFinalizados }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Sub-tab navigation bar --}}
<div class="px-6 py-4 bg-gray-50/70 border-b border-gray-100 flex gap-2">
    <button onclick="switchShippingTab('ventas')" id="btn-tab-ventas"
            class="px-4 py-2 text-xs font-bold rounded-lg border-0 transition-all cursor-pointer shadow-sm bg-blue-600 text-white">
        📦 Envíos de Ventas Directas ({{ $ventasConfirmadas->total() }})
    </button>
    <button onclick="switchShippingTab('intercambios')" id="btn-tab-intercambios"
            class="px-4 py-2 text-xs font-bold rounded-lg border-0 transition-all cursor-pointer shadow-sm bg-white text-gray-700 hover:bg-gray-100">
        🤝 Envíos de Intercambios ({{ $intercambiosConfirmados->total() }})
    </button>
</div>

{{-- VENTAS DIRECTAS SECTION --}}
<div id="shipping-ventas-section">
    @if($ventasConfirmadas->isEmpty())
    <div class="p-16 text-center text-gray-400 bg-white rounded-b-xl">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <p class="text-sm font-semibold text-gray-500">No hay registros de envíos de ventas directas en esta sección.</p>
    </div>
    @else
    <div class="overflow-x-auto bg-white">
        <table class="w-full text-sm border-collapse">
            <thead class="bg-gray-50/50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">ID Venta</th>
                    <th class="text-left px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Comprador</th>
                    <th class="text-left px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Productos / Vendedor</th>
                    <th class="text-left px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Dirección de Despacho</th>
                    <th class="text-left px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Total Cobrado</th>
                    <th class="text-left px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Estado Logístico</th>
                    <th class="text-left px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Fecha Aprobación</th>
                    <th class="text-center px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($ventasConfirmadas as $compra)
                @php
                    $isReadyVenta = ($compra->estatus === 'aprobado');
                @endphp
                <tr class="hover:bg-gray-50/50 transition-colors {{ $isReadyVenta ? 'bg-blue-50/10' : '' }}">
                    {{-- ID Venta --}}
                    <td class="px-5 py-4">
                        <span class="font-mono text-xs text-gray-600 bg-gray-100 px-2 py-1 rounded-md font-bold">#{{ $compra->id_pago_compra }}</span>
                    </td>

                    {{-- Comprador --}}
                    <td class="px-5 py-4">
                        <div class="flex items-start gap-2">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold">C</span>
                            <div>
                                <p class="text-xs font-bold text-gray-800">{{ $compra->carrito->usuario->nombres ?? 'Usuario' }} {{ $compra->carrito->usuario->apellidos ?? '' }}</p>
                                <p class="text-[10px] text-gray-400">{{ $compra->carrito->usuario->telefono ?? 'S/T' }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Productos / Vendedor --}}
                    <td class="px-5 py-4 text-xs">
                        <div class="space-y-2">
                            @foreach($compra->pagoItems as $pi)
                            <div>
                                <span class="font-semibold text-gray-800">{{ $pi->item->item ?? 'Producto' }}</span>
                                <div class="flex flex-wrap gap-1 mt-0.5 items-center">
                                    <span class="text-[9px] bg-gray-100 px-1 py-0.2 rounded text-gray-500">Precio: RD$ {{ number_format($pi->precio, 2) }}</span>
                                    @if($pi->item && $pi->item->usuario)
                                    <span class="text-[9px] text-indigo-600 font-medium">Vendió: {{ $pi->item->usuario->nombres }}</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </td>

                    {{-- Dirección de Despacho --}}
                    <td class="px-5 py-4 text-xs text-gray-600 max-w-[200px] truncate-2-lines">
                        @if($compra->direccion)
                            <p class="font-bold text-gray-800">{{ $compra->direccion->direccion }}</p>
                            <p class="text-[10px] text-gray-400">{{ $compra->direccion->municipio->municipio ?? '' }}, {{ $compra->direccion->provincia->provincia ?? '' }}</p>
                        @else
                            <span class="text-gray-400 italic">No especificada</span>
                        @endif
                    </td>

                    {{-- Total Cobrado --}}
                    <td class="px-5 py-4 text-xs font-bold text-gray-800">
                        RD$ {{ number_format($compra->monto, 2) }}
                    </td>

                    {{-- Estado Logístico --}}
                    <td class="px-5 py-4 text-xs">
                        @if($compra->estatus === 'entregado')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-green-500 rounded-full"></span>
                            Entregado
                        </span>
                        @elseif($compra->estatus === 'enviado')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 animate-pulse">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-indigo-500 rounded-full"></span>
                            En Tránsito
                        </span>
                        @elseif($compra->estatus === 'aprobado')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 ring-2 ring-blue-500/10">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-blue-500 rounded-full"></span>
                            Listo para Envío
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                            {{ ucfirst($compra->estatus) }}
                        </span>
                        @endif
                    </td>

                    {{-- Fecha Aprobación --}}
                    <td class="px-5 py-4 text-xs text-gray-500 font-medium">
                        {{ $compra->created_at ? $compra->created_at->format('d/m/Y H:i') : '-' }}
                    </td>

                    {{-- Acciones --}}
                    <td class="px-5 py-4 text-center">
                        <div class="inline-flex flex-col gap-1.5 items-stretch min-w-[130px]">
                            @if($isReadyVenta)
                            <form action="{{ route('admin.compras.estado', $compra->id_pago_compra) }}" method="POST"
                                  onsubmit="return confirm('¿Confirmar el despacho de este pedido? Se generará la hoja de envío PDF automáticamente.')">
                                @csrf
                                <input type="hidden" name="estatus" value="enviado">
                                <input type="hidden" name="nota" value="Envío del pedido de venta iniciado.">
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm hover:shadow transition-all hover:scale-[1.02] cursor-pointer border-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    Marcar para Envío
                                </button>
                            </form>
                            @elseif($compra->estatus === 'enviado')
                            <form action="{{ route('admin.compras.estado', $compra->id_pago_compra) }}" method="POST"
                                  onsubmit="return confirm('¿Confirmar que el pedido fue entregado y marcar como completado?')">
                                @csrf
                                <input type="hidden" name="estatus" value="entregado">
                                <input type="hidden" name="nota" value="Pedido entregado al comprador de forma exitosa.">
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg shadow-sm hover:shadow transition-all hover:scale-[1.02] cursor-pointer border-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Marcar completado
                                </button>
                            </form>
                            <a href="{{ route('admin.compras.pdf', $compra->id_pago_compra) }}"
                               class="inline-flex items-center justify-center gap-1 text-[10px] text-indigo-600 hover:text-indigo-800 hover:underline font-bold mt-1">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; display: inline-block;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Descargar Hoja PDF
                            </a>
                            @elseif($compra->estatus === 'entregado')
                            <span class="inline-flex items-center justify-center text-xs text-green-600 font-bold gap-1 py-1">
                                <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Finalizado
                            </span>
                            <a href="{{ route('admin.compras.pdf', $compra->id_pago_compra) }}"
                               class="inline-flex items-center justify-center gap-1 text-[10px] text-gray-500 hover:text-gray-700 hover:underline font-bold">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; display: inline-block;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Ver PDF
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($ventasConfirmadas->hasPages())
    <div class="px-5 py-4.5 border-t border-gray-100 bg-gray-50/50 rounded-b-xl">
        {{ $ventasConfirmadas->appends(request()->except('page_vc'))->links('vendor.pagination.custom') }}
    </div>
    @endif
    @endif
</div>

{{-- INTERCAMBIOS SECTION --}}
<div id="shipping-intercambios-section" class="hidden">
    @if($intercambiosConfirmados->isEmpty())
    <div class="p-16 text-center text-gray-400 bg-white rounded-b-xl">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <p class="text-sm font-semibold text-gray-500">No hay registros de intercambios en esta sección.</p>
    </div>
    @else
    <div class="overflow-x-auto bg-white rounded-b-xl">
        <table class="w-full text-sm border-collapse">
            <thead class="bg-gray-50/70 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">ID</th>
                    <th class="text-left px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Participantes</th>
                    <th class="text-left px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Intercambio Propuesto</th>
                    <th class="text-left px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Monto Extra</th>
                    <th class="text-left px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Pagos Envío</th>
                    <th class="text-left px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Estado</th>
                    <th class="text-left px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Fecha Aceptado</th>
                    <th class="text-center px-5 py-4.5 text-xs font-bold text-gray-400 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($intercambiosConfirmados as $neg)
                @php
                    $isReady = ($neg->estado === 'aceptado' && $neg->pago_emisor && $neg->pago_receptor);
                @endphp
                <tr class="hover:bg-gray-50/50 transition-colors {{ $isReady ? 'bg-blue-50/10' : '' }}">
                    {{-- ID --}}
                    <td class="px-5 py-4">
                        <span class="font-mono text-xs text-gray-600 bg-gray-100 px-2 py-1 rounded-md font-bold">#{{ $neg->id_negociacion }}</span>
                    </td>

                    {{-- Participantes --}}
                    <td class="px-5 py-4">
                        <div class="space-y-3">
                            <div class="flex items-start gap-2">
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold">E</span>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">{{ $neg->usuario->nombres ?? 'Usuario' }} {{ $neg->usuario->apellidos ?? '' }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $neg->usuario->telefono ?? 'S/T' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">R</span>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">{{ $neg->usuarioReceptor->nombres ?? 'Usuario' }} {{ $neg->usuarioReceptor->apellidos ?? '' }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $neg->usuarioReceptor->telefono ?? 'S/T' }}</p>
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Intercambio Propuesto --}}
                    <td class="px-5 py-4">
                        <div class="space-y-2">
                            <div class="text-xs">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Solicita:</span>
                                @if($neg->item)
                                <span class="font-semibold text-gray-800">{{ $neg->item->item }}</span>
                                <span class="text-[10px] text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100">Cat: {{ $neg->item->categoria->categoria ?? '-' }}</span>
                                @else
                                <span class="text-gray-300 italic">No disponible</span>
                                @endif
                            </div>
                            <div class="text-xs">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">A cambio de:</span>
                                @if($neg->items_ofrecidos)
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach(\App\Models\Item::whereIn('id_item', $neg->items_ofrecidos)->get() as $io)
                                        @php $cantIo = $neg->getCantidadOfrecida($io->id_item); @endphp
                                        <span class="inline-flex items-center gap-1 text-[10px] bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded font-medium border border-blue-100">
                                            {{ $io->item }}
                                            @if($cantIo > 1)<span class="bg-blue-600 text-white text-[9px] font-bold rounded-full px-1 ml-0.5">× {{ $cantIo }}</span>@endif
                                        </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Monto Extra --}}
                    <td class="px-5 py-4 text-xs">
                        @if($neg->monto_oferta)
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold border border-blue-100">RD$ {{ number_format($neg->monto_oferta, 2) }}</span>
                        @else
                        <span class="text-gray-400 italic">Ninguno</span>
                        @endif
                    </td>

                    {{-- Pagos Envío --}}
                    <td class="px-5 py-4 text-xs">
                        <div class="flex flex-col gap-1.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $neg->pago_emisor ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                                <span class="w-1.5 h-1.5 mr-1.5 rounded-full {{ $neg->pago_emisor ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                                Emisor: {{ $neg->pago_emisor ? 'PAGADO' : 'PENDIENTE' }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $neg->pago_receptor ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                                <span class="w-1.5 h-1.5 mr-1.5 rounded-full {{ $neg->pago_receptor ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                                Receptor: {{ $neg->pago_receptor ? 'PAGADO' : 'PENDIENTE' }}
                            </span>
                        </div>
                    </td>

                    {{-- Estado --}}
                    <td class="px-5 py-4 text-xs">
                        @if($neg->estado === 'completado')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-green-500 rounded-full"></span>
                            Entregado
                        </span>
                        @elseif($neg->estado === 'en_envio')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 animate-pulse">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-indigo-500 rounded-full"></span>
                            En Tránsito
                        </span>
                        @elseif($neg->pago_emisor && $neg->pago_receptor)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 ring-2 ring-blue-500/10">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-blue-500 rounded-full"></span>
                            Listo para Envío
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-amber-500 rounded-full"></span>
                            Esperando Pagos
                        </span>
                        @endif
                    </td>

                    {{-- Fecha Aceptado --}}
                    <td class="px-5 py-4 text-xs text-gray-500 font-medium">
                        {{ $neg->fecha_creacion ? \Carbon\Carbon::parse($neg->fecha_creacion)->format('d/m/Y H:i') : '-' }}
                    </td>

                    {{-- Acciones --}}
                    <td class="px-5 py-4 text-center">
                        <div class="inline-flex flex-col gap-1.5 items-stretch min-w-[130px]">
                            @if($isReady)
                            <form action="{{ route('admin.intercambios.estado', $neg->id_negociacion) }}" method="POST"
                                  onsubmit="return confirm('¿Confirmar que el intercambio está listo para envío? Se generará la hoja de envío en PDF automáticamente.')">
                                @csrf
                                <input type="hidden" name="estado" value="en_envio">
                                <input type="hidden" name="nota" value="Envío iniciado y guía de ruta generada.">
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm hover:shadow transition-all hover:scale-[1.02] cursor-pointer border-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    Marcar para Envío
                                </button>
                            </form>
                            @elseif($neg->estado === 'en_envio')
                            <form action="{{ route('admin.intercambios.estado', $neg->id_negociacion) }}" method="POST"
                                  onsubmit="return confirm('¿Confirmar que el intercambio fue entregado y marcar como completado?')">
                                @csrf
                                <input type="hidden" name="estado" value="completado">
                                <input type="hidden" name="nota" value="Intercambio entregado y completado.">
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg shadow-sm hover:shadow transition-all hover:scale-[1.02] cursor-pointer border-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Marcar completado
                                </button>
                            </form>
                            <a href="{{ route('admin.intercambios.pdf', \App\Helpers\HashIdHelper::encode($neg->id_negociacion)) }}"
                               class="inline-flex items-center justify-center gap-1 text-[10px] text-indigo-600 hover:text-indigo-800 hover:underline font-bold mt-1">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; display: inline-block;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Descargar Hoja PDF
                            </a>
                            @elseif($neg->estado === 'completado')
                            <span class="inline-flex items-center justify-center text-xs text-green-600 font-bold gap-1 py-1">
                                <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Finalizado
                            </span>
                            <a href="{{ route('admin.intercambios.pdf', \App\Helpers\HashIdHelper::encode($neg->id_negociacion)) }}"
                               class="inline-flex items-center justify-center gap-1 text-[10px] text-gray-500 hover:text-gray-700 hover:underline font-bold">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; display: inline-block;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Ver PDF
                            </a>
                            @else
                            <span class="text-xs text-gray-400 font-medium italic py-2">⏳ Esperando pagos</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($intercambiosConfirmados->hasPages())
    <div class="px-5 py-4.5 border-t border-gray-100 bg-gray-50/50 rounded-b-xl">
        {{ $intercambiosConfirmados->appends(request()->except('page_ic2'))->links('vendor.pagination.custom') }}
    </div>
    @endif
    @endif
</div>

{{-- Modal de Flujo de Estados --}}
<div id="flowModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div onclick="closeFlowModal()" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2" id="modal-title">
                        🔄 Flujo de Estados del Proceso de Logística
                    </h3>
                    <button onclick="closeFlowModal()" class="text-gray-400 hover:text-gray-500 border-0 bg-transparent cursor-pointer">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; display: inline-block;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Inner Tabs --}}
                <div class="flex border-b border-gray-100 gap-4 px-1 mt-3">
                    <button onclick="switchModalFlowTab('ventas')" id="modal-tab-ventas"
                            class="pb-2 text-xs font-bold border-0 border-b-2 border-blue-600 text-blue-600 bg-transparent cursor-pointer">
                        📦 Flujo de Ventas Directas
                    </button>
                    <button onclick="switchModalFlowTab('intercambios')" id="modal-tab-intercambios"
                            class="pb-2 text-xs font-bold border-0 border-b-2 border-transparent text-gray-500 hover:text-gray-700 bg-transparent cursor-pointer">
                        🤝 Flujo de Intercambios (Trueques)
                    </button>
                </div>

                {{-- VENTAS DIRECTAS FLOW --}}
                <div id="modal-flow-ventas" class="mt-6 space-y-6 relative before:absolute before:top-0 before:bottom-0 before:left-5 before:w-0.5 before:bg-gray-100">
                    {{-- Paso 1 --}}
                    <div class="flex gap-4 relative">
                        <div class="flex-shrink-0 z-10 w-11 h-11 rounded-full bg-amber-50 border-2 border-amber-300 flex items-center justify-center text-amber-700 font-bold text-sm shadow-sm">
                            1
                        </div>
                        <div class="flex-1 rounded-xl p-4" style="background-color: rgba(253, 230, 138, 0.15); border: 1px solid rgba(252, 211, 77, 0.3);">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-amber-800 uppercase tracking-wide">Compra del Producto</h4>
                                <span class="text-[9px] font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">Usuario</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">El comprador selecciona un producto en la plataforma, realiza el checkout y efectúa el pago del artículo y el costo de envío.</p>
                        </div>
                    </div>

                    {{-- Paso 2 --}}
                    <div class="flex gap-4 relative">
                        <div class="flex-shrink-0 z-10 w-11 h-11 rounded-full bg-blue-50 border-2 border-blue-300 flex items-center justify-center text-blue-700 font-bold text-sm shadow-sm">
                            2
                        </div>
                        <div class="flex-1 rounded-xl p-4" style="background-color: rgba(191, 219, 254, 0.15); border: 1px solid rgba(147, 197, 253, 0.3);">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-blue-800 uppercase tracking-wide">Pago Aprobado (Procesado)</h4>
                                <span class="text-[9px] font-bold bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">Sistema</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">El sistema verifica y aprueba la transacción financiera. La venta cambia automáticamente al estado de <strong class="text-blue-700 font-bold">Aprobado</strong>, activando la preparación del despacho.</p>
                        </div>
                    </div>

                    {{-- Paso 3 --}}
                    <div class="flex gap-4 relative">
                        <div class="flex-shrink-0 z-10 w-11 h-11 rounded-full bg-indigo-50 border-2 border-indigo-400 flex items-center justify-center text-indigo-700 font-bold text-sm shadow-sm">
                            3
                        </div>
                        <div class="flex-1 rounded-xl p-4 ring-2 ring-indigo-500/10" style="background-color: rgba(199, 210, 254, 0.25); border: 1px solid rgba(165, 180, 252, 0.4);">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-indigo-800 uppercase tracking-wide">Listo para Envío (Acción del Administrador)</h4>
                                <span class="text-[9px] font-bold bg-indigo-600 text-white px-2 py-0.5 rounded-full">Admin</span>
                            </div>
                            <p class="text-xs text-gray-700 font-medium mt-1">El pedido pagado aparece en el tablero de envíos de ventas. El administrador prepara la caja física.</p>
                            <div class="mt-2 text-[10px] text-indigo-700 flex items-center gap-1.5">
                                <svg fill="currentColor" viewBox="0 0 20 20" style="width: 14px; height: 14px; flex-shrink: 0; display: inline-block;">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <span>Al hacer click en <strong class="underline">"Marcar para Envío"</strong>, el estado cambia a "Enviado" y se descarga el PDF de ruta.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Paso 4 --}}
                    <div class="flex gap-4 relative">
                        <div class="flex-shrink-0 z-10 w-11 h-11 rounded-full bg-purple-50 border-2 border-purple-300 flex items-center justify-center text-purple-700 font-bold text-sm shadow-sm">
                            4
                        </div>
                        <div class="flex-1 rounded-xl p-4" style="background-color: rgba(233, 213, 255, 0.15); border: 1px solid rgba(216, 180, 254, 0.3);">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-purple-800 uppercase tracking-wide">En Tránsito (Despachado)</h4>
                                <span class="text-[9px] font-bold bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full">Transporte</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">El transportista retira el paquete del almacén o del vendedor y lo traslada a la dirección de entrega del comprador.</p>
                        </div>
                    </div>

                    {{-- Paso 5 --}}
                    <div class="flex gap-4 relative">
                        <div class="flex-shrink-0 z-10 w-11 h-11 rounded-full bg-green-50 border-2 border-green-300 flex items-center justify-center text-green-700 font-bold text-sm shadow-sm">
                            5
                        </div>
                        <div class="flex-1 rounded-xl p-4" style="background-color: rgba(167, 243, 208, 0.15); border: 1px solid rgba(110, 231, 183, 0.3);">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-green-800 uppercase tracking-wide">Completado (Entregado)</h4>
                                <span class="text-[9px] font-bold bg-green-100 text-green-800 px-2 py-0.5 rounded-full">Admin / Transporte</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">Al entregarse el paquete, el administrador confirma en este panel presionando <strong class="text-green-700 font-bold">"Marcar completado"</strong>, actualizando la compra al estado final de <strong class="text-green-700 font-bold">Entregado</strong>.</p>
                        </div>
                    </div>
                </div>

                {{-- INTERCAMBIOS FLOW (Initially Hidden) --}}
                <div id="modal-flow-intercambios" class="mt-6 space-y-6 relative before:absolute before:top-0 before:bottom-0 before:left-5 before:w-0.5 before:bg-gray-100 hidden">
                    {{-- Paso 1 --}}
                    <div class="flex gap-4 relative">
                        <div class="flex-shrink-0 z-10 w-11 h-11 rounded-full bg-amber-50 border-2 border-amber-300 flex items-center justify-center text-amber-700 font-bold text-sm shadow-sm">
                            1
                        </div>
                        <div class="flex-1 rounded-xl p-4" style="background-color: rgba(253, 230, 138, 0.15); border: 1px solid rgba(252, 211, 77, 0.3);">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-amber-800 uppercase tracking-wide">Propuesta Inicial / Contraoferta</h4>
                                <span class="text-[9px] font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">Usuario</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">El emisor propone un intercambio de bienes. El receptor puede aceptar, rechazar o realizar una contraoferta (añadiendo dinero extra u otros artículos).</p>
                        </div>
                    </div>

                    {{-- Paso 2 --}}
                    <div class="flex gap-4 relative">
                        <div class="flex-shrink-0 z-10 w-11 h-11 rounded-full bg-blue-50 border-2 border-blue-300 flex items-center justify-center text-blue-700 font-bold text-sm shadow-sm">
                            2
                        </div>
                        <div class="flex-1 rounded-xl p-4" style="background-color: rgba(191, 219, 254, 0.15); border: 1px solid rgba(147, 197, 253, 0.3);">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-blue-800 uppercase tracking-wide">Aceptado / Esperando Pagos de Envío</h4>
                                <span class="text-[9px] font-bold bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">Usuario</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">Ambas partes aceptan el trueque en la app. El sistema calcula la tarifa de envío de la plataforma. Tanto el emisor como el receptor deben pagar sus costos de despacho para avanzar.</p>
                        </div>
                    </div>

                    {{-- Paso 3 --}}
                    <div class="flex gap-4 relative">
                        <div class="flex-shrink-0 z-10 w-11 h-11 rounded-full bg-indigo-50 border-2 border-indigo-400 flex items-center justify-center text-indigo-700 font-bold text-sm shadow-sm">
                            3
                        </div>
                        <div class="flex-1 rounded-xl p-4 ring-2 ring-indigo-500/10" style="background-color: rgba(199, 210, 254, 0.25); border: 1px solid rgba(165, 180, 252, 0.4);">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-indigo-800 uppercase tracking-wide">Listo para Envío (Acción del Administrador)</h4>
                                <span class="text-[9px] font-bold bg-indigo-600 text-white px-2 py-0.5 rounded-full">Admin</span>
                            </div>
                            <p class="text-xs text-gray-700 font-medium mt-1">Cuando ambos pagos de despacho están confirmados, el trueque aparece en este panel listo para recolectar y empacar.</p>
                            <div class="mt-2 text-[10px] text-indigo-700 flex items-center gap-1.5">
                                <svg fill="currentColor" viewBox="0 0 20 20" style="width: 14px; height: 14px; flex-shrink: 0; display: inline-block;">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <span>Al hacer click en <strong class="underline">"Marcar para Envío"</strong>, el estado pasa a "En Envío" y se descarga la guía PDF de ruta.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Paso 4 --}}
                    <div class="flex gap-4 relative">
                        <div class="flex-shrink-0 z-10 w-11 h-11 rounded-full bg-purple-50 border-2 border-purple-300 flex items-center justify-center text-purple-700 font-bold text-sm shadow-sm">
                            4
                        </div>
                        <div class="flex-1 rounded-xl p-4" style="background-color: rgba(233, 213, 255, 0.15); border: 1px solid rgba(216, 180, 254, 0.3);">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-purple-800 uppercase tracking-wide">En Tránsito (En Envío)</h4>
                                <span class="text-[9px] font-bold bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full">Transporte</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">El transportista ejecuta la recolección física y traslada cruzadamente los artículos intercambiados entre los respectivos domicilios.</p>
                        </div>
                    </div>

                    {{-- Paso 5 --}}
                    <div class="flex gap-4 relative">
                        <div class="flex-shrink-0 z-10 w-11 h-11 rounded-full bg-green-50 border-2 border-green-300 flex items-center justify-center text-green-700 font-bold text-sm shadow-sm">
                            5
                        </div>
                        <div class="flex-1 rounded-xl p-4" style="background-color: rgba(167, 243, 208, 0.15); border: 1px solid rgba(110, 231, 183, 0.3);">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-green-800 uppercase tracking-wide">Completado (Entregado)</h4>
                                <span class="text-[9px] font-bold bg-green-100 text-green-800 px-2 py-0.5 rounded-full">Admin / Transporte</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">El administrador confirma que ambos usuarios recibieron sus respectivos paquetes haciendo click en <strong class="text-green-700 font-bold">"Marcar completado"</strong>, finalizando la negociación.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button type="button" onclick="closeFlowModal()" 
                        class="px-4 py-2 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-50 transition-all cursor-pointer border-0 shadow-sm">
                    Entendido, Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openFlowModal() {
        const modal = document.getElementById('flowModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closeFlowModal() {
        const modal = document.getElementById('flowModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    function switchModalFlowTab(type) {
        const flowVentas = document.getElementById('modal-flow-ventas');
        const flowIntercambios = document.getElementById('modal-flow-intercambios');
        const tabVentas = document.getElementById('modal-tab-ventas');
        const tabIntercambios = document.getElementById('modal-tab-intercambios');

        if (type === 'ventas') {
            if (flowVentas) flowVentas.classList.remove('hidden');
            if (flowIntercambios) flowIntercambios.classList.add('hidden');
            
            tabVentas.className = "pb-2 text-xs font-bold border-0 border-b-2 border-blue-600 text-blue-600 bg-transparent cursor-pointer";
            tabIntercambios.className = "pb-2 text-xs font-bold border-0 border-b-2 border-transparent text-gray-500 hover:text-gray-700 bg-transparent cursor-pointer";
        } else {
            if (flowVentas) flowVentas.classList.add('hidden');
            if (flowIntercambios) flowIntercambios.classList.remove('hidden');
            
            tabVentas.className = "pb-2 text-xs font-bold border-0 border-b-2 border-transparent text-gray-500 hover:text-gray-700 bg-transparent cursor-pointer";
            tabIntercambios.className = "pb-2 text-xs font-bold border-0 border-b-2 border-blue-600 text-blue-600 bg-transparent cursor-pointer";
        }
    }

    function switchShippingTab(type) {
        const sectionVentas = document.getElementById('shipping-ventas-section');
        const sectionIntercambios = document.getElementById('shipping-intercambios-section');
        const btnVentas = document.getElementById('btn-tab-ventas');
        const btnIntercambios = document.getElementById('btn-tab-intercambios');
        
        if (type === 'ventas') {
            if (sectionVentas) sectionVentas.classList.remove('hidden');
            if (sectionIntercambios) sectionIntercambios.classList.add('hidden');
            
            btnVentas.className = "px-4 py-2 text-xs font-bold rounded-lg border-0 transition-all cursor-pointer shadow-sm bg-blue-600 text-white";
            btnIntercambios.className = "px-4 py-2 text-xs font-bold rounded-lg border-0 transition-all cursor-pointer shadow-sm bg-white text-gray-700 hover:bg-gray-100";
        } else {
            if (sectionVentas) sectionVentas.classList.add('hidden');
            if (sectionIntercambios) sectionIntercambios.classList.remove('hidden');
            
            btnVentas.className = "px-4 py-2 text-xs font-bold rounded-lg border-0 transition-all cursor-pointer shadow-sm bg-white text-gray-700 hover:bg-gray-100";
            btnIntercambios.className = "px-4 py-2 text-xs font-bold rounded-lg border-0 transition-all cursor-pointer shadow-sm bg-blue-600 text-white";
        }
    }
</script>
