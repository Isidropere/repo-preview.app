@if($intercambiosConfirmados->isEmpty())
<div class="p-10 text-center text-gray-400">
    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
    </svg>
    <p class="text-sm">No hay intercambios confirmados pendientes de envío.</p>
</div>
@else
<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Emisor</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Receptor</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Artículo solicitado</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Productos ofrecidos</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Monto</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Pagos</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Estado</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Acción</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($intercambiosConfirmados as $neg)
            <tr class="hover:bg-emerald-50 transition-colors">
                <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $neg->id_negociacion }}</td>

                {{-- Emisor --}}
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-800">{{ $neg->usuario->nombres ?? '-' }} {{ $neg->usuario->apellidos ?? '' }}</p>
                    <p class="text-xs text-gray-400">{{ $neg->usuario->email ?? '' }}</p>
                    <p class="text-xs text-gray-400">{{ $neg->usuario->telefono ?? '' }}</p>
                </td>

                {{-- Receptor --}}
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-800">{{ $neg->usuarioReceptor->nombres ?? '-' }} {{ $neg->usuarioReceptor->apellidos ?? '' }}</p>
                    <p class="text-xs text-gray-400">{{ $neg->usuarioReceptor->email ?? '' }}</p>
                    <p class="text-xs text-gray-400">{{ $neg->usuarioReceptor->telefono ?? '' }}</p>
                </td>

                {{-- Artículo solicitado --}}
                <td class="px-4 py-3">
                    @if($neg->item)
                    <p class="font-medium text-gray-800 text-xs">{{ $neg->item->item }}</p>
                    <p class="text-xs text-gray-400">Cat: {{ $neg->item->categoria->categoria ?? '-' }}</p>
                    @else
                    <span class="text-gray-400 text-xs">Sin datos</span>
                    @endif
                </td>

                {{-- Productos ofrecidos --}}
                <td class="px-4 py-3">
                    @if($neg->items_ofrecidos)
                        @foreach(\App\Models\Item::whereIn('id_item', $neg->items_ofrecidos)->get() as $io)
                        <span class="inline-block bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded mb-1">{{ $io->item }}</span>
                        @endforeach
                    @else
                        <span class="text-gray-400 text-xs">—</span>
                    @endif
                </td>

                {{-- Monto --}}
                <td class="px-4 py-3 text-xs">
                    @if($neg->monto_oferta)
                    <span class="text-blue-700 font-semibold">RD$ {{ number_format($neg->monto_oferta, 2) }}</span>
                    @else
                    <span class="text-gray-400">Solo intercambio</span>
                    @endif
                </td>

                {{-- Pagos --}}
                <td class="px-4 py-3 text-xs">
                    <div style="display:flex;flex-direction:column;gap:2px;">
                        <span style="color:{{ $neg->pago_emisor ? '#16a34a' : '#f59e0b' }};">
                            {{ $neg->pago_emisor ? '✅' : '⏳' }} Emisor
                        </span>
                        <span style="color:{{ $neg->pago_receptor ? '#16a34a' : '#f59e0b' }};">
                            {{ $neg->pago_receptor ? '✅' : '⏳' }} Receptor
                        </span>
                    </div>
                </td>

                {{-- Estado --}}
                <td class="px-4 py-3 text-xs">
                    @if($neg->estado === 'completado')
                    <span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:9999px;font-weight:600;">✅ Completado</span>
                    @elseif($neg->pago_emisor && $neg->pago_receptor)
                    <span style="background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:9999px;font-weight:600;">💳 Pagado — Pendiente envío</span>
                    @else
                    <span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:9999px;font-weight:600;">⏳ Esperando pagos</span>
                    @endif
                </td>

                {{-- Fecha --}}
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ $neg->fecha_creacion ? \Carbon\Carbon::parse($neg->fecha_creacion)->format('d/m/Y H:i') : '-' }}
                </td>

                {{-- Acción: marcar como enviado --}}
                <td class="px-4 py-3">
                    <form action="{{ route('negociaciones.completar', $neg->id_negociacion) }}" method="POST"
                          onsubmit="return confirm('¿Marcar este intercambio como completado/enviado?')">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors">
                            ✅ Marcar enviado
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($intercambiosConfirmados->hasPages())
<div class="px-4 py-3 border-t border-gray-100">
    {{ $intercambiosConfirmados->appends(request()->except('page'))->links('vendor.pagination.custom') }}
</div>
@endif
@endif
