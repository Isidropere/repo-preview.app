@if($intencionCompra->isEmpty())
<div class="flex flex-col items-center justify-center py-20 text-gray-400">
    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
    <p class="text-sm font-medium">No hay artículos en carritos pendientes</p>
</div>
@else
<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Artículo</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Vendedor</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Comprador (carrito)</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Precio</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Envío est.</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Cantidad</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($intencionCompra as $ic)
            @php
                $imagen = $ic->item?->imagenes?->first();
            @endphp
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                            @if($imagen)
                                <img src="{{ \App\Helpers\ImageHelper::urlMedia($imagen->ruta, $imagen->nombre) }}"
                                     alt="{{ $ic->item?->item }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null;this.src='/imgs/no-product.jpg'">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <span class="font-medium text-gray-800 truncate max-w-[160px]">{{ $ic->item?->item ?? 'Sin artículo' }}</span>
                    </div>
                </td>
                <td class="px-4 py-3">
                    @if($ic->item?->usuario)
                        <p class="font-medium text-gray-800">{{ $ic->item->usuario->nombres }}</p>
                        <p class="text-xs text-gray-400">{{ $ic->item->usuario->email }}</p>
                    @else
                        <span class="text-gray-400 text-xs">Sin datos</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($ic->carrito?->usuario)
                        <p class="font-medium text-gray-800">{{ $ic->carrito->usuario->nombres }}</p>
                        <p class="text-xs text-gray-400">{{ $ic->carrito->usuario->email }}</p>
                    @else
                        <span class="text-gray-400 text-xs">Sin datos</span>
                    @endif
                </td>
                <td class="px-4 py-3 font-semibold text-gray-800">
                    RD$ {{ number_format($ic->item?->valor ?? 0, 2) }}
                </td>
                <td class="px-4 py-3">
                    @php
                        $municipioIc = $ic->carrito?->usuario?->direcciones?->where('es_predeterminada', 1)->first()?->municipio?->municipio ?? '';
                    @endphp
                    @if($municipioIc)
                        <span class="delivery-cost-ic text-xs text-blue-600 font-medium"
                              data-pueblo="{{ $municipioIc }}"
                              data-valor="{{ $ic->item?->valor ?? 0 }}">
                            <span class="animate-pulse">...</span>
                        </span>
                    @else
                        <span class="text-xs text-gray-400">Sin dirección</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $ic->cantidad ?? 1 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@if($intencionCompra->hasPages())
<div class="px-4 py-3 border-t border-gray-100">
    {{ $intencionCompra->appends(request()->except('page'))->links('vendor.pagination.custom') }}
</div>
@endif
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delivery-cost-ic').forEach(function (el) {
        const pueblo = el.dataset.pueblo;
        const valor  = el.dataset.valor || 0;
        if (!pueblo) return;
        fetch('/api/delivery/calcular?pueblo=' + encodeURIComponent(pueblo) + '&tipo_destinatario=persona&valor_articulo=' + valor)
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    el.innerHTML = 'RD$ ' + Number(d.costo_envio_total).toLocaleString('es-DO', {minimumFractionDigits: 2});
                    el.title = 'Zona: ' + d.zona + ' (' + d.dias_entrega + ')';
                } else {
                    el.innerHTML = '<span class="text-gray-400">N/D</span>';
                }
            })
            .catch(() => { el.innerHTML = '<span class="text-gray-400">Error</span>'; });
    });
});
</script>
