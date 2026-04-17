@if($compras->isEmpty())
<div class="flex flex-col items-center justify-center py-20 text-gray-400">
    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <p class="text-sm font-medium">No se encontraron compras</p>
</div>
@else
<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">ID Orden</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Comprador</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Items</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Monto</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($compras as $compra)
            @php
                $items = $compra->pagoItems ?? collect();
                $badgeClass = match($compra->estatus) {
                    'pendiente'  => 'bg-yellow-100 text-yellow-700',
                    'aprobado'   => 'bg-green-100 text-green-700',
                    'rechazado'  => 'bg-red-100 text-red-700',
                    'enviado'    => 'bg-blue-100 text-blue-700',
                    'entregado'  => 'bg-emerald-100 text-emerald-700',
                    'cancelado'  => 'bg-gray-100 text-gray-600',
                    default      => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 font-mono text-xs text-gray-700">#{{ $compra->id_pago_compra }}</td>
                <td class="px-4 py-3">
                    @if($compra->comprador)
                        <p class="font-medium text-gray-800">{{ $compra->comprador->nombres }} {{ $compra->comprador->apellidos }}</p>
                        <p class="text-xs text-gray-400">{{ $compra->comprador->email }}</p>
                    @else
                        <span class="text-gray-400 text-xs">Sin datos</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @php
                        $primerItem = $items->first();
                        $primerImagen = $primerItem?->item?->imagenes?->first();
                        $imagenUrl = $primerItem?->imagen_url;
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                            @if($imagenUrl)
                                <img src="{{ $imagenUrl }}"
                                     alt="{{ $primerItem?->nombre_item }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null;this.src='/imgs/defaults/producto_default.svg'">
                            @elseif($primerImagen)
                                <img src="{{ \App\Helpers\ImageHelper::urlMedia($primerImagen->ruta, $primerImagen->nombre) }}"
                                     alt="{{ $primerItem?->nombre_item }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null;this.src='/imgs/defaults/producto_default.svg'">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <span class="text-gray-600 text-xs">
                            {{ $items->count() }} {{ $items->count() === 1 ? 'artículo' : 'artículos' }}
                        </span>
                    </div>
                </td>
                <td class="px-4 py-3 font-semibold text-gray-800">
                    RD$ {{ number_format($compra->total ?? 0, 2) }}
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                        {{ ucfirst($compra->estatus ?? 'sin estado') }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('admin.compras.show', $compra->id_pago_compra) }}"
                       class="inline-flex items-center gap-1 text-primary hover:text-hoverPrimary text-xs font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Ver detalle
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@if($compras->hasPages())
<div class="px-4 py-3 border-t border-gray-100">
    {{ $compras->appends(request()->except('page'))->links('vendor.pagination.custom') }}
</div>
@endif
@endif
