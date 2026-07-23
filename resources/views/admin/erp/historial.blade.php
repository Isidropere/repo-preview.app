@extends('layouts.admin')

@section('title', 'Historial ERP - Panel de Administración')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        {{-- Encabezado --}}
        <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Historial de Ventas e Intercambios</h1>
                <p class="text-sm text-gray-500 mt-1">Gestión y control de transacciones procesadas y terminadas.</p>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm flex items-center gap-4">
                <div class="bg-emerald-50 p-3 rounded-lg text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ventas Procesadas</p>
                    <p class="text-xl font-extrabold text-gray-900 mt-0.5">{{ $ventas->total() }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm flex items-center gap-4">
                <div class="bg-purple-50 p-3 rounded-lg text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4M5 17h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Intercambios Procesados</p>
                    <p class="text-xl font-extrabold text-gray-900 mt-0.5">{{ $intercambios->total() }}</p>
                </div>
            </div>
        </div>

        {{-- Contenido Principal --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {{-- Tabs --}}
            <div class="border-b border-gray-100">
                <nav class="flex">
                    <a href="{{ route('admin.erp.historial', ['tab' => 'ventas', 'buscar' => request('buscar'), 'fecha_desde' => request('fecha_desde'), 'fecha_hasta' => request('fecha_hasta'), 'estatus' => request('estatus')]) }}"
                       class="px-6 py-4 text-sm font-semibold border-b-2 transition-all flex items-center gap-2
                              {{ $tab === 'ventas' ? 'border-emerald-500 text-emerald-600 bg-emerald-50/20' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Ventas Procesadas
                        <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-0.5 rounded-full font-bold">{{ $ventas->total() }}</span>
                    </a>
                    <a href="{{ route('admin.erp.historial', ['tab' => 'intercambios', 'buscar' => request('buscar'), 'fecha_desde' => request('fecha_desde'), 'fecha_hasta' => request('fecha_hasta'), 'estatus' => request('estatus')]) }}"
                       class="px-6 py-4 text-sm font-semibold border-b-2 transition-all flex items-center gap-2
                              {{ $tab === 'intercambios' ? 'border-emerald-500 text-emerald-600 bg-emerald-50/20' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Intercambios Procesados
                        <span class="bg-purple-100 text-purple-800 text-xs px-2 py-0.5 rounded-full font-bold">{{ $intercambios->total() }}</span>
                    </a>
                </nav>
            </div>

            {{-- Buscador y Filtro por Fechas --}}
            <div class="p-4 bg-gray-50/50 border-b border-gray-100">
                <form method="GET" action="{{ route('admin.erp.historial') }}" class="flex flex-wrap gap-4 items-end">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    
                    <div class="flex-1 min-w-[250px]">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Buscar</label>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" 
                               placeholder="{{ $tab === 'ventas' ? 'ID de orden, cliente o email...' : 'ID de negociación, artículo o usuario...' }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Estado</label>
                        <select name="estatus" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white w-40">
                            <option value="">Todos los estados</option>
                            @if($tab === 'ventas')
                                <option value="aprobado" {{ request('estatus') === 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                                <option value="enviado" {{ request('estatus') === 'enviado' ? 'selected' : '' }}>Enviado</option>
                                <option value="entregado" {{ request('estatus') === 'entregado' ? 'selected' : '' }}>Entregado</option>
                            @else
                                <option value="aceptado" {{ request('estatus') === 'aceptado' ? 'selected' : '' }}>Aceptado</option>
                                <option value="en_envio" {{ request('estatus') === 'en_envio' ? 'selected' : '' }}>En Envío</option>
                                <option value="completado" {{ request('estatus') === 'completado' ? 'selected' : '' }}>Completado</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Desde</label>
                        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                               class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Hasta</label>
                        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                               class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg text-sm font-bold shadow-sm transition-all">
                            Filtrar
                        </button>
                        <a href="{{ route('admin.erp.historial.pdf', request()->query()) }}" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-bold shadow-sm transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Exportar PDF
                        </a>
                        @if(request('buscar') || request('fecha_desde') || request('fecha_hasta') || request('estatus'))
                            <a href="{{ route('admin.erp.historial', ['tab' => $tab]) }}" class="border border-gray-200 text-gray-600 hover:bg-gray-100 px-5 py-2 rounded-lg text-sm font-semibold transition-all">
                                Limpiar
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Contenido de Ventas --}}
            @if($tab === 'ventas')
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/70">
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Orden</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Cliente</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Detalle Pago</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-right">Monto Total</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Estatus</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($ventas as $pago)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        @if(!empty($pago->is_talent_registration))
                                            <p class="text-sm font-bold text-gray-800">{{ $pago->id_pago_compra }}</p>
                                            <p class="text-xs text-emerald-600 font-semibold mt-0.5">Registro de Talento-Servicio</p>
                                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $pago->fecha ? $pago->fecha->format('d/m/Y h:i A') : '-' }}</p>
                                            <p class="text-xs font-semibold text-gray-700 mt-1 truncate max-w-[180px]" title="{{ $pago->talent_name }}">
                                                Servicio: {{ $pago->talent_name }}
                                            </p>
                                        @else
                                            <p class="text-sm font-bold text-gray-800">#{{ Str::limit($pago->id_pago_compra, 8, '...') }}</p>
                                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $pago->fecha ? $pago->fecha->format('d/m/Y h:i A') : '-' }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-800">{{ $pago->carrito?->usuario?->nombres ?? 'Desconocido' }} {{ $pago->carrito?->usuario?->apellidos ?? '' }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $pago->carrito?->usuario?->email ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600">
                                        @if($pago->azul_response)
                                            @php
                                                $azul = $pago->azul_response;
                                                $cardNumber = $azul['CardNumber'] ?? '';
                                                $brand = $azul['DataVaultBrand'] ?? '';
                                                if (empty($brand) && !empty($cardNumber)) {
                                                    if (str_starts_with($cardNumber, '4')) {
                                                        $brand = 'VISA';
                                                    } elseif (str_starts_with($cardNumber, '5')) {
                                                        $brand = 'MASTERCARD';
                                                    } elseif (str_starts_with($cardNumber, '3')) {
                                                        $brand = 'AMEX';
                                                    }
                                                }
                                                
                                                $azulDate = null;
                                                $rawDate = $azul['DateTime'] ?? null;
                                                if ($rawDate && strlen($rawDate) === 14) {
                                                    $year = substr($rawDate, 0, 4);
                                                    $month = substr($rawDate, 4, 2);
                                                    $day = substr($rawDate, 6, 2);
                                                    $hour = substr($rawDate, 8, 2);
                                                    $min = substr($rawDate, 10, 2);
                                                    $sec = substr($rawDate, 12, 2);
                                                    $azulDate = "$day/$month/$year $hour:$min:$sec";
                                                }
                                            @endphp
                                            <div class="flex flex-col gap-1">
                                                <div>
                                                    @if($brand)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 mr-1">{{ strtoupper($brand) }}</span>
                                                    @endif
                                                    @if($cardNumber)
                                                        <span class="font-mono font-bold text-gray-800">{{ $cardNumber }}</span>
                                                    @endif
                                                </div>
                                                @if(!empty($azul['AzulOrderId']))
                                                    <p class="text-[10px] text-gray-500 mt-0.5">Azul Order ID: <span class="font-mono font-semibold text-gray-700">{{ $azul['AzulOrderId'] }}</span></p>
                                                @endif
                                                @if(!empty($azul['AuthorizationCode']))
                                                    <p class="text-[10px] text-gray-500">Aut: <span class="font-mono font-bold text-emerald-600 bg-emerald-50 px-1 rounded">{{ $azul['AuthorizationCode'] }}</span></p>
                                                @endif
                                                @if(!empty($azul['RRN']))
                                                    <p class="text-[10px] text-gray-500">RRN: <span class="font-mono text-gray-700">{{ $azul['RRN'] }}</span></p>
                                                @endif
                                                @if($azulDate)
                                                    <p class="text-[10px] text-gray-400">Fecha Azul: <span class="font-mono text-gray-600">{{ $azulDate }}</span></p>
                                                @endif
                                                <details class="mt-1 text-[10px] text-gray-500 bg-gray-50 rounded-lg p-1 border border-gray-200 cursor-pointer">
                                                    <summary class="font-semibold text-gray-600 hover:text-gray-800 focus:outline-none">Ver JSON Completo</summary>
                                                    <pre class="mt-1 font-mono text-[9px] overflow-auto whitespace-pre-wrap break-all bg-gray-900 text-emerald-400 p-2 rounded max-h-40">{{ json_encode($azul, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                </details>
                                            </div>
                                        @elseif($pago->tarjeta)
                                            <span class="font-medium capitalize">{{ $pago->tarjeta->tipo_tarjeta }}</span> terminada en <span class="font-mono font-bold">{{ $pago->tarjeta->last4 }}</span>
                                            @if($pago->autorizacion_pago)
                                                <p class="text-[10px] text-gray-400 mt-0.5 font-mono">Aut: {{ $pago->autorizacion_pago }}</p>
                                            @endif
                                        @else
                                            <span class="text-gray-400">Sin detalles de pago</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-right text-gray-800">
                                        RD$ {{ number_format($pago->total, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                            @if($pago->estatus === 'entregado') bg-emerald-100 text-emerald-800
                                            @elseif($pago->estatus === 'enviado') bg-blue-100 text-blue-800
                                            @elseif($pago->estatus === 'aprobado') bg-green-100 text-green-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ $pago->estatus }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            @if(!empty($pago->is_talent_registration))
                                                <a href="{{ route('producto.detalle', $pago->talent_id) }}" target="_blank"
                                                   class="text-emerald-600 hover:text-emerald-800 text-xs font-bold border border-emerald-100 hover:bg-emerald-50 px-2.5 py-1 rounded-lg transition-all">
                                                    Ver Servicio
                                                </a>
                                                <span class="text-xs text-gray-400 font-semibold px-2.5 py-1 border border-gray-100 bg-gray-50 rounded-lg">
                                                    N/A Envío
                                                </span>
                                            @else
                                                <a href="{{ route('admin.compras.show', $pago->id_pago_compra) }}" 
                                                   class="text-emerald-600 hover:text-emerald-800 text-xs font-bold border border-emerald-100 hover:bg-emerald-50 px-2.5 py-1 rounded-lg transition-all">
                                                    Ver
                                                </a>
                                                <a href="{{ route('admin.compras.pdf', $pago->id_pago_compra) }}" 
                                                   class="text-gray-600 hover:text-gray-800 text-xs font-bold border border-gray-200 hover:bg-gray-50 px-2.5 py-1 rounded-lg transition-all"
                                                   title="Descargar hoja de envío">
                                                    Envío PDF
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                        No se encontraron ventas finalizadas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($ventas->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                        {{ $ventas->links() }}
                    </div>
                @endif
            @endif

            {{-- Contenido de Intercambios --}}
            @if($tab === 'intercambios')
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/70">
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Intercambio</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Emisor (Propone)</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Receptor (Recibe)</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Pagos de Envío</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Estatus</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($intercambios as $neg)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-gray-800">#{{ \App\Helpers\HashIdHelper::encode($neg->id_negociacion) }}</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $neg->fecha_creacion ? $neg->fecha_creacion->format('d/m/Y h:i A') : '-' }}</p>
                                        <p class="text-xs font-medium text-gray-700 mt-1 truncate max-w-[180px]" title="{{ $neg->item->item ?? '' }}">
                                            Item: {{ $neg->item->item ?? 'Eliminado' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-800">{{ $neg->usuario?->nombres ?? 'Desconocido' }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $neg->usuario?->email ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-800">{{ $neg->usuarioReceptor?->nombres ?? 'Desconocido' }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $neg->usuarioReceptor?->email ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600">
                                        @php
                                            $pagoEmisorObj = $neg->pagoEnvios->firstWhere('id_user', $neg->usuario_emisor_id);
                                            $pagoReceptorObj = $neg->pagoEnvios->firstWhere('id_user', $neg->usuario_receptor_id);
                                        @endphp
                                        <div class="flex flex-col gap-2">
                                            <div class="border-b border-gray-100 pb-2 mb-2 last:border-0 last:pb-0 last:mb-0">
                                                <span class="font-medium text-gray-500">Emisor:</span>
                                                @if($pagoEmisorObj && $pagoEmisorObj->estado === 'pagado')
                                                    <span class="text-emerald-700 font-bold">RD$ {{ number_format($pagoEmisorObj->monto, 2) }}</span>
                                                    @if($pagoEmisorObj->azul_response)
                                                        @php
                                                            $azulEmisor = $pagoEmisorObj->azul_response;
                                                            $cardNumberEmisor = $azulEmisor['CardNumber'] ?? '';
                                                            $brandEmisor = $azulEmisor['DataVaultBrand'] ?? '';
                                                            if (empty($brandEmisor) && !empty($cardNumberEmisor)) {
                                                                if (str_starts_with($cardNumberEmisor, '4')) {
                                                                    $brandEmisor = 'VISA';
                                                                } elseif (str_starts_with($cardNumberEmisor, '5')) {
                                                                    $brandEmisor = 'MASTERCARD';
                                                                } elseif (str_starts_with($cardNumberEmisor, '3')) {
                                                                    $brandEmisor = 'AMEX';
                                                                }
                                                            }
                                                            
                                                            $azulDateEmisor = null;
                                                            $rawDateEmisor = $azulEmisor['DateTime'] ?? null;
                                                            if ($rawDateEmisor && strlen($rawDateEmisor) === 14) {
                                                                $year = substr($rawDateEmisor, 0, 4);
                                                                $month = substr($rawDateEmisor, 4, 2);
                                                                $day = substr($rawDateEmisor, 6, 2);
                                                                $hour = substr($rawDateEmisor, 8, 2);
                                                                $min = substr($rawDateEmisor, 10, 2);
                                                                $sec = substr($rawDateEmisor, 12, 2);
                                                                $azulDateEmisor = "$day/$month/$year $hour:$min:$sec";
                                                            }
                                                        @endphp
                                                        <div class="pl-2 mt-1 text-[10px] text-gray-500 border-l border-emerald-200">
                                                            @if($brandEmisor || $cardNumberEmisor)
                                                                <p class="font-mono font-semibold text-gray-800">
                                                                    @if($brandEmisor)
                                                                        <span class="bg-blue-50 text-blue-700 px-1 rounded text-[9px] mr-1">{{ $brandEmisor }}</span>
                                                                    @endif
                                                                    {{ $cardNumberEmisor }}
                                                                </p>
                                                            @endif
                                                            @if(!empty($azulEmisor['AzulOrderId']))
                                                                <p class="text-[9px] text-gray-400">ID: {{ $azulEmisor['AzulOrderId'] }}</p>
                                                            @endif
                                                            @if(!empty($azulEmisor['AuthorizationCode']))
                                                                <p>Aut: <span class="font-mono font-bold text-emerald-600 bg-emerald-50 px-1 rounded text-[9px]">{{ $azulEmisor['AuthorizationCode'] }}</span></p>
                                                            @endif
                                                            @if($azulDateEmisor)
                                                                <p class="text-[9px] text-gray-400">{{ $azulDateEmisor }}</p>
                                                            @endif
                                                            <details class="mt-1 text-[9px] bg-gray-50 rounded p-1 border border-gray-100 cursor-pointer">
                                                                <summary class="font-semibold text-gray-600 hover:text-gray-800 focus:outline-none">JSON Azul</summary>
                                                                <pre class="mt-1 font-mono text-[9px] overflow-auto whitespace-pre-wrap break-all bg-gray-900 text-emerald-400 p-1 rounded max-h-32">{{ json_encode($azulEmisor, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                            </details>
                                                        </div>
                                                    @endif
                                                @elseif($pagoEmisorObj && $pagoEmisorObj->estado === 'pagado_pull')
                                                    <span class="text-purple-700 font-bold">Pull</span>
                                                @else
                                                    <span class="text-gray-400">Pendiente</span>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-500">Receptor:</span>
                                                @if($pagoReceptorObj && $pagoReceptorObj->estado === 'pagado')
                                                    <span class="text-emerald-700 font-bold">RD$ {{ number_format($pagoReceptorObj->monto, 2) }}</span>
                                                    @if($pagoReceptorObj->azul_response)
                                                        @php
                                                            $azulReceptor = $pagoReceptorObj->azul_response;
                                                            $cardNumberReceptor = $azulReceptor['CardNumber'] ?? '';
                                                            $brandReceptor = $azulReceptor['DataVaultBrand'] ?? '';
                                                            if (empty($brandReceptor) && !empty($cardNumberReceptor)) {
                                                                if (str_starts_with($cardNumberReceptor, '4')) {
                                                                    $brandReceptor = 'VISA';
                                                                } elseif (str_starts_with($cardNumberReceptor, '5')) {
                                                                    $brandReceptor = 'MASTERCARD';
                                                                } elseif (str_starts_with($cardNumberReceptor, '3')) {
                                                                    $brandReceptor = 'AMEX';
                                                                }
                                                            }
                                                            
                                                            $azulDateReceptor = null;
                                                            $rawDateReceptor = $azulReceptor['DateTime'] ?? null;
                                                            if ($rawDateReceptor && strlen($rawDateReceptor) === 14) {
                                                                $year = substr($rawDateReceptor, 0, 4);
                                                                $month = substr($rawDateReceptor, 4, 2);
                                                                $day = substr($rawDateReceptor, 6, 2);
                                                                $hour = substr($rawDateReceptor, 8, 2);
                                                                $min = substr($rawDateReceptor, 10, 2);
                                                                $sec = substr($rawDateReceptor, 12, 2);
                                                                $azulDateReceptor = "$day/$month/$year $hour:$min:$sec";
                                                            }
                                                        @endphp
                                                        <div class="pl-2 mt-1 text-[10px] text-gray-500 border-l border-emerald-200">
                                                            @if($brandReceptor || $cardNumberReceptor)
                                                                <p class="font-mono font-semibold text-gray-800">
                                                                    @if($brandReceptor)
                                                                        <span class="bg-blue-50 text-blue-700 px-1 rounded text-[9px] mr-1">{{ $brandReceptor }}</span>
                                                                    @endif
                                                                    {{ $cardNumberReceptor }}
                                                                </p>
                                                            @endif
                                                            @if(!empty($azulReceptor['AzulOrderId']))
                                                                <p class="text-[9px] text-gray-400">ID: {{ $azulReceptor['AzulOrderId'] }}</p>
                                                            @endif
                                                            @if(!empty($azulReceptor['AuthorizationCode']))
                                                                <p>Aut: <span class="font-mono font-bold text-emerald-600 bg-emerald-50 px-1 rounded text-[9px]">{{ $azulReceptor['AuthorizationCode'] }}</span></p>
                                                            @endif
                                                            @if($azulDateReceptor)
                                                                <p class="text-[9px] text-gray-400">{{ $azulDateReceptor }}</p>
                                                            @endif
                                                            <details class="mt-1 text-[9px] bg-gray-50 rounded p-1 border border-gray-100 cursor-pointer">
                                                                <summary class="font-semibold text-gray-600 hover:text-gray-800 focus:outline-none">JSON Azul</summary>
                                                                <pre class="mt-1 font-mono text-[9px] overflow-auto whitespace-pre-wrap break-all bg-gray-900 text-emerald-400 p-1 rounded max-h-32">{{ json_encode($azulReceptor, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                            </details>
                                                        </div>
                                                    @endif
                                                @elseif($pagoReceptorObj && $pagoReceptorObj->estado === 'pagado_pull')
                                                    <span class="text-purple-700 font-bold">Pull</span>
                                                @else
                                                    <span class="text-gray-400">Pendiente</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                            @if($neg->estado === 'completado') bg-emerald-100 text-emerald-800
                                            @elseif($neg->estado === 'en_envio') bg-blue-100 text-blue-800
                                            @elseif($neg->estado === 'aceptado') bg-green-100 text-green-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ $neg->estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.intercambios.show', \App\Helpers\HashIdHelper::encode($neg->id_negociacion)) }}" 
                                               class="text-emerald-600 hover:text-emerald-800 text-xs font-bold border border-emerald-100 hover:bg-emerald-50 px-2.5 py-1.5 rounded-lg transition-all inline-block">
                                                Detalle
                                            </a>
                                            <a href="{{ route('admin.intercambios.pdf', \App\Helpers\HashIdHelper::encode($neg->id_negociacion)) }}" 
                                               class="text-gray-600 hover:text-gray-800 text-xs font-bold border border-gray-200 hover:bg-gray-50 px-2.5 py-1.5 rounded-lg transition-all"
                                               title="Descargar detalle en PDF">
                                                PDF
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                        No se encontraron intercambios finalizados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($intercambios->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                        {{ $intercambios->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</div>
@endsection
