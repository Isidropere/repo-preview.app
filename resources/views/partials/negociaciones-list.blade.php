@if($negociaciones->isEmpty())
    <p class="text-center text-gray-400">No hay negociaciones activas para este producto.</p>
@else
    <ul class="divide-y divide-gray-200">
        @foreach($negociaciones as $neg)
            <li class="py-4">
                {{-- Usuario y estado de la negociación --}}
                <div class="flex justify-between items-center mb-2">
                    <p class="font-medium text-gray-800">{{ $neg->usuario->name }}</p>
                    <span class="text-xs px-2 py-1 rounded-full 
                        @if($neg->estado == 'pendiente') bg-yellow-100 text-yellow-800
                        @elseif($neg->estado == 'aceptada') bg-green-100 text-green-800
                        @elseif($neg->estado == 'rechazada') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-500
                        @endif
                    ">{{ ucfirst($neg->estado) }}</span>
                </div>

                {{-- Mensaje inicial --}}
                <p class="text-sm text-gray-600 mb-2">{{ $neg->mensaje_inicial }}</p>

                {{-- Lista de items y monto ofrecido por el usuario que hace la oferta --}}
                <div class="mb-2 p-2 bg-gray-50 rounded border border-gray-200">
                    <p class="font-semibold text-gray-700 mb-1">Oferta de {{ $neg->usuario->name }}:</p>
                    @if($neg->items_oferta->isNotEmpty())
                        <ul class="list-disc list-inside text-gray-600">
                            @foreach($neg->items_oferta as $item)
                                <li>{{ $item->nombre }} (Cantidad: {{ $item->cantidad }})</li>
                            @endforeach
                        </ul>
                    @endif
                    @if($neg->monto_oferta)
                        <p class="text-gray-600 mt-1">Monto: ${{ number_format($neg->monto_oferta, 2) }}</p>
                    @endif
                </div>

                {{-- Lista de items y monto del usuario que recibe la oferta --}}
                @if($neg->items_contra_oferta || $neg->monto_contra_oferta)
                    <div class="mb-2 p-2 bg-blue-50 rounded border border-blue-200">
                        <p class="font-semibold text-gray-700 mb-1">Contraoferta de {{ $neg->usuario_receptor->name }}:</p>
                        @if($neg->items_contra_oferta->isNotEmpty())
                            <ul class="list-disc list-inside text-gray-600">
                                @foreach($neg->items_contra_oferta as $item)
                                    <li>{{ $item->nombre }} (Cantidad: {{ $item->cantidad }})</li>
                                @endforeach
                            </ul>
                        @endif
                        @if($neg->monto_contra_oferta)
                            <p class="text-gray-600 mt-1">Monto adicional solicitado: ${{ number_format($neg->monto_contra_oferta, 2) }}</p>
                        @endif
                    </div>
                @endif

                {{-- Motivos de rechazo --}}
                @if($neg->motivos_rechazo->isNotEmpty())
                    <div class="p-2 bg-red-50 rounded border border-red-200 mt-2">
                        <p class="font-semibold text-red-700 mb-1">Motivos de rechazo:</p>
                        <ul class="list-disc list-inside text-red-600">
                            @foreach($neg->motivos_rechazo as $motivo)
                                <li>{{ $motivo->descripcion }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Acciones según estado --}}
                @if($neg->estado == 'pendiente' && auth()->id() == $neg->usuario_receptor_id)
                    <div class="mt-2 flex gap-2">
                        <form action="{{ route('negociaciones.aceptar', $neg->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">Aceptar</button>
                        </form>

                        <form action="{{ route('negociaciones.rechazar', $neg->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">Rechazar</button>
                        </form>

                        <a href="{{ route('negociaciones.contraoferta', $neg->id) }}" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Hacer contraoferta</a>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
@endif
