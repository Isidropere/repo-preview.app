@if($negociaciones->isEmpty())
    <p class="text-center text-gray-400 py-6">No hay negociaciones activas para este producto.</p>
@else
    <ul class="divide-y divide-gray-200">
        @foreach($negociaciones as $neg)
        @php
            $esEmisor   = auth()->id() == $neg->usuario_emisor_id;
            $esReceptor = auth()->id() == $neg->usuario_receptor_id;
            $estadoColor = match($neg->estado) {
                'Inicial'     => 'bg-yellow-100 text-yellow-800',
                'contraoferta'=> 'bg-blue-100 text-blue-800',
                'aceptado'    => 'bg-green-100 text-green-800',
                'completado'  => 'bg-emerald-100 text-emerald-800',
                'rechazado'   => 'bg-red-100 text-red-800',
                'cancelado'   => 'bg-gray-100 text-gray-500',
                default       => 'bg-gray-100 text-gray-500',
            };
            $estadoLabel = match($neg->estado) {
                'Inicial'     => 'Propuesta enviada',
                'contraoferta'=> 'Contraoferta',
                'aceptado'    => $neg->emisor_confirmado ? '✅ En proceso de intercambio' : 'Aceptado — pendiente confirmación',
                'completado'  => '✅ Completado',
                'rechazado'   => 'Rechazado',
                'cancelado'   => 'Cancelado',
                default       => ucfirst($neg->estado),
            };
        @endphp
        <li class="py-4 {{ $neg->estado === 'aceptado' && $neg->emisor_confirmado ? 'bg-green-50 rounded-xl px-3' : '' }}">

            {{-- Cabecera --}}
            <div class="flex justify-between items-center mb-2">
                <p class="font-medium text-gray-800 text-sm">
                    {{ $neg->usuario->nombres ?? 'Usuario' }}
                    <span class="text-gray-400 font-normal">→</span>
                    {{ $neg->usuarioReceptor->nombres ?? 'Receptor' }}
                </p>
                <span class="text-xs px-2 py-1 rounded-full font-semibold {{ $estadoColor }}">
                    {{ $estadoLabel }}
                </span>
            </div>

            {{-- Mensaje inicial --}}
            @if($neg->mensaje_inicial)
            <p class="text-sm text-gray-600 mb-2 italic">"{{ $neg->mensaje_inicial }}"</p>
            @endif

            {{-- Items ofrecidos --}}
            @if($neg->items_ofrecidos)
            <div class="mb-2 p-2 bg-gray-50 rounded-lg border border-gray-200 text-xs">
                <p class="font-semibold text-gray-700 mb-1">Productos ofrecidos:</p>
                @foreach(\App\Models\Item::whereIn('id_item', $neg->items_ofrecidos)->get() as $itemOfrecido)
                <span class="inline-block bg-white border border-gray-200 rounded px-2 py-0.5 mr-1 mb-1">{{ $itemOfrecido->item }}</span>
                @endforeach
            </div>
            @endif

            {{-- Monto oferta / contraoferta --}}
            @if($neg->monto_oferta)
            <p class="text-xs text-gray-500 mb-1">Monto oferta: <span class="font-semibold text-blue-700">RD$ {{ number_format($neg->monto_oferta, 2) }}</span></p>
            @endif
            @if($neg->monto_contra_oferta)
            <p class="text-xs text-gray-500 mb-1">Contraoferta: <span class="font-semibold text-orange-600">RD$ {{ number_format($neg->monto_contra_oferta, 2) }}</span></p>
            @endif

            {{-- ACCIONES --}}
            <div class="mt-3 flex flex-wrap gap-2">

                {{-- Receptor: aceptar / rechazar / contraoferta --}}
                @if(in_array($neg->estado, ['Inicial','contraoferta']) && $esReceptor)
                <form action="{{ route('negociaciones.aceptar', $neg->id_negociacion) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded-lg">✓ Aceptar</button>
                </form>
                <form action="{{ route('negociaciones.rechazar', $neg->id_negociacion) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded-lg">✕ Rechazar</button>
                </form>
                <a href="{{ route('negociaciones.contraoferta', $neg->id_negociacion) }}"
                   class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg">↩ Contraoferta</a>
                @endif

                {{-- Emisor: confirmar después de que receptor aceptó --}}
                @if($neg->estado === 'aceptado' && $esEmisor && !$neg->emisor_confirmado)
                <form action="{{ route('negociaciones.confirmar_emisor', $neg->id_negociacion) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow">
                        ✅ Confirmar intercambio
                    </button>
                </form>
                <p class="text-xs text-gray-500 self-center">El receptor aceptó tu propuesta. Confirma para continuar.</p>
                @endif

                {{-- Ambos: pago habilitado cuando emisor confirmó --}}
                @if($neg->estado === 'aceptado' && $neg->emisor_confirmado)
                <div class="w-full bg-green-50 border border-green-200 rounded-lg p-3 text-sm">
                    <p class="text-green-700 font-semibold mb-2">🎉 Intercambio confirmado por ambas partes</p>
                    @if($neg->monto_oferta || $neg->monto_contra_oferta)
                    <a href="{{ route('carrito.checkout') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg">
                        💳 Proceder al pago
                    </a>
                    @else
                    <p class="text-green-600 text-xs">Los administradores han sido notificados para gestionar el envío.</p>
                    @endif
                </div>
                @endif

                {{-- Emisor: cancelar --}}
                @if(in_array($neg->estado, ['Inicial','contraoferta']) && $esEmisor)
                <form action="{{ route('negociaciones.cancelar', $neg->id_negociacion) }}" method="POST"
                      onsubmit="return confirm('¿Cancelar esta negociación?')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-lg">Cancelar</button>
                </form>
                @endif

            </div>
        </li>
        @endforeach
    </ul>
@endif
