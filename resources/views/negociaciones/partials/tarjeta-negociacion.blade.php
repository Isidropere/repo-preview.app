@php
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
        'aceptado'    => $neg->emisor_confirmado ? '✅ En proceso' : 'Aceptado',
        'completado'  => '✅ Completado',
        'rechazado'   => 'Rechazado',
        'cancelado'   => 'Cancelado',
        default       => ucfirst($neg->estado),
    };
    $imgNombre = $neg->item?->imagenes?->where('estado','aprobado')->first()?->nombre;
    $imgSrc = $imgNombre
        ? \App\Helpers\ImageHelper::urlMedia('imgs/articulos/items', $imgNombre)
        : asset('imgs/defaults/producto_default.svg');
    $otroUsuario = $rol === 'receptor' ? $neg->usuario : $neg->usuarioReceptor;
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4 {{ $neg->estado === 'aceptado' && $neg->emisor_confirmado ? 'border-emerald-300' : '' }}">

    {{-- Cabecera --}}
    <div class="flex items-start justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
            <img src="{{ $imgSrc }}" alt="{{ $neg->item?->item }}"
                 class="w-14 h-14 rounded-xl object-cover border border-gray-100 flex-shrink-0">
            <div>
                <p class="font-semibold text-gray-800 text-sm">{{ $neg->item?->item ?? 'Producto eliminado' }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $rol === 'receptor' ? 'Propuesta de: ' : 'Para: ' }}
                    <span class="font-medium text-gray-600">{{ $otroUsuario?->nombres }} {{ $otroUsuario?->apellidos }}</span>
                </p>
                <p class="text-xs text-gray-400">{{ $neg->fecha_creacion ? \Carbon\Carbon::parse($neg->fecha_creacion)->diffForHumans() : '' }}</p>
            </div>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-semibold flex-shrink-0 {{ $estadoColor }}">{{ $estadoLabel }}</span>
    </div>

    {{-- Mensaje inicial --}}
    @if($neg->mensaje_inicial)
    <div class="bg-gray-50 rounded-xl px-4 py-3 mb-4 text-sm text-gray-700 italic">
        "{{ $neg->mensaje_inicial }}"
    </div>
    @endif

    {{-- Productos ofrecidos --}}
    @if($neg->items_ofrecidos && count($neg->items_ofrecidos))
    <div class="mb-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Productos ofrecidos a cambio:</p>
        <div class="flex flex-wrap gap-2">
            @foreach(\App\Models\Item::whereIn('id_item', $neg->items_ofrecidos)->get() as $io)
            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-xs px-2.5 py-1 rounded-lg border border-blue-100">
                {{ $io->item }}
                @if($io->valor) · RD$ {{ number_format($io->valor, 0) }} @endif
            </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Monto --}}
    @if($neg->monto_oferta)
    <p class="text-xs text-gray-500 mb-4">Monto adicional: <span class="font-bold text-blue-700">RD$ {{ number_format($neg->monto_oferta, 2) }}</span></p>
    @endif

    {{-- ACCIONES --}}
    <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">

        {{-- RECEPTOR: aceptar / rechazar --}}
        @if($rol === 'receptor' && in_array($neg->estado, ['Inicial','contraoferta']))
        <form action="{{ route('negociaciones.aceptar', $neg->id_negociacion) }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors">
                ✓ Aceptar propuesta
            </button>
        </form>
        <form action="{{ route('negociaciones.rechazar', $neg->id_negociacion) }}" method="POST"
              onsubmit="return confirm('¿Rechazar esta propuesta?')">
            @csrf
            <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-lg transition-colors">
                ✕ Rechazar
            </button>
        </form>
        @endif

        {{-- EMISOR: confirmar después de que receptor aceptó --}}
        @if($rol === 'emisor' && $neg->estado === 'aceptado' && !$neg->emisor_confirmado)
        <div class="w-full bg-emerald-50 border border-emerald-200 rounded-xl p-3 mb-2">
            <p class="text-sm text-emerald-700 font-semibold mb-2">🎉 ¡Tu propuesta fue aceptada! Confirma para continuar.</p>
            <form action="{{ route('negociaciones.confirmar_emisor', $neg->id_negociacion) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg">
                    ✅ Confirmar intercambio
                </button>
            </form>
        </div>
        @endif

        {{-- PAGO: cuando ambos confirmaron --}}
        @if($neg->estado === 'aceptado' && $neg->emisor_confirmado)
        <div class="w-full bg-blue-50 border border-blue-200 rounded-xl p-4">
            <p class="text-sm font-semibold text-blue-800 mb-1">💳 Intercambio confirmado — Procede con el pago</p>
            <p class="text-xs text-blue-600 mb-3">Ambas partes deben realizar el pago para completar el intercambio.</p>
            <a href="{{ route('negociaciones.pago', $neg->id_negociacion) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors">
                💳 Realizar pago
            </a>
        </div>
        @endif

        {{-- EMISOR: cancelar --}}
        @if($rol === 'emisor' && in_array($neg->estado, ['Inicial','contraoferta']))
        <form action="{{ route('negociaciones.cancelar', $neg->id_negociacion) }}" method="POST"
              onsubmit="return confirm('¿Cancelar esta propuesta?')">
            @csrf
            <button type="submit" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-lg">
                Cancelar propuesta
            </button>
        </form>
        @endif

    </div>
</div>
