@if($intencionIntercambio->isEmpty())
<div class="flex flex-col items-center justify-center py-20 text-gray-400">
    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
    </svg>
    <p class="text-sm font-medium">No hay intercambios pendientes</p>
</div>
@else
<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">ID</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Artículo</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Emisor</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Receptor</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Monto oferta</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($intencionIntercambio as $neg)
            @php
                $imagen = $neg->item?->imagenes?->first();
                $badgeClass = match($neg->estado) {
                    'Inicial'      => 'bg-blue-100 text-blue-700',
                    'pendiente'    => 'bg-yellow-100 text-yellow-700',
                    'contraoferta' => 'bg-orange-100 text-orange-700',
                    default        => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 font-mono text-xs text-gray-700">#{{ $neg->id_negociacion }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                            @if($imagen)
                                <img src="{{ asset('storage/' . trim($imagen->ruta, '/') . '/' . $imagen->nombre) }}"
                                     alt="{{ $neg->item?->item }}"
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
                        <span class="font-medium text-gray-800 truncate max-w-[140px]">{{ $neg->item?->item ?? 'Sin artículo' }}</span>
                    </div>
                </td>
                <td class="px-4 py-3">
                    @if($neg->usuario)
                        <p class="font-medium text-gray-800">{{ $neg->usuario->nombres }}</p>
                        <p class="text-xs text-gray-400">{{ $neg->usuario->email }}</p>
                    @else
                        <span class="text-gray-400 text-xs">Sin datos</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($neg->usuarioReceptor)
                        <p class="font-medium text-gray-800">{{ $neg->usuarioReceptor->nombres }}</p>
                        <p class="text-xs text-gray-400">{{ $neg->usuarioReceptor->email }}</p>
                    @else
                        <span class="text-gray-400 text-xs">Sin datos</span>
                    @endif
                </td>
                <td class="px-4 py-3 font-semibold text-gray-800">
                    {{ $neg->monto_oferta ? 'RD$ ' . number_format($neg->monto_oferta, 2) : '—' }}
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                        {{ $neg->estado }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('admin.intercambios.show', $neg->id_negociacion) }}"
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
@if($intencionIntercambio->hasPages())
<div class="px-4 py-3 border-t border-gray-100">
    {{ $intencionIntercambio->appends(request()->except('page'))->links('vendor.pagination.custom') }}
</div>
@endif
@endif
