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

    $ambosConfirmados = $neg->emisor_confirmado && $neg->receptor_confirmado;

    $estadoLabel = match(true) {
        $neg->estado === 'completado' => '✅ Completado',
        $neg->estado === 'rechazado'  => 'Rechazado',
        $neg->estado === 'cancelado'  => 'Cancelado',
        $ambosConfirmados             => '💳 Listo para pago',
        $neg->estado === 'aceptado'   => 'Aceptado — Pendiente aprobación',
        $neg->estado === 'contraoferta' => 'Contraoferta',
        $neg->estado === 'Inicial'    => 'Propuesta enviada',
        default                       => ucfirst($neg->estado),
    };

    $imgNombre = $neg->item?->imagenes?->where('estado','aprobado')->first()?->nombre;
    $imgSrc = $imgNombre
        ? \App\Helpers\ImageHelper::urlMedia('imgs/articulos/items', $imgNombre)
        : asset('imgs/defaults/producto_default.svg');
    $otroUsuario = $rol === 'receptor' ? $neg->usuario : $neg->usuarioReceptor;
    $miPago = $rol === 'emisor' ? $neg->pago_emisor : $neg->pago_receptor;
    $otroPago = $rol === 'emisor' ? $neg->pago_receptor : $neg->pago_emisor;
    $miConfirmado = $rol === 'emisor' ? $neg->emisor_confirmado : $neg->receptor_confirmado;
    $otroConfirmado = $rol === 'emisor' ? $neg->receptor_confirmado : $neg->emisor_confirmado;
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4 {{ $ambosConfirmados ? 'border-emerald-300' : '' }}">

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

    {{-- Mensaje --}}
    @if($neg->mensaje_inicial)
    <div class="bg-gray-50 rounded-xl px-4 py-3 mb-4 text-sm text-gray-700 italic">"{{ $neg->mensaje_inicial }}"</div>
    @endif

    {{-- Productos ofrecidos --}}
    @if($neg->items_ofrecidos && count($neg->items_ofrecidos))
    <div class="mb-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Productos ofrecidos a cambio:</p>
        <div class="flex flex-wrap gap-2">
            @foreach(\App\Models\Item::whereIn('id_item', $neg->items_ofrecidos)->get() as $io)
            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-xs px-2.5 py-1 rounded-lg border border-blue-100">
                {{ $io->item }} @if($io->valor) · RD$ {{ number_format($io->valor, 0) }} @endif
            </span>
            @endforeach
        </div>
    </div>
    @endif

    @if($neg->monto_oferta)
    <p class="text-xs text-gray-500 mb-4">Monto adicional: <span class="font-bold text-blue-700">RD$ {{ number_format($neg->monto_oferta, 2) }}</span></p>
    @endif

    {{-- Estado de aprobaciones --}}
    @if($neg->estado === 'aceptado')
    <div class="mb-4 p-3 rounded-xl border" style="background:#f0fdf4;border-color:#bbf7d0;">
        <p class="text-xs font-semibold text-gray-600 mb-2">Estado de aprobaciones:</p>
        <div class="flex gap-4">
            <div class="flex items-center gap-1.5 text-xs">
                @if($neg->emisor_confirmado)
                    <span style="color:#16a34a;">✅</span> <span class="text-gray-700">Emisor aprobó</span>
                @else
                    <span style="color:#d1d5db;">⏳</span> <span class="text-gray-400">Emisor pendiente</span>
                @endif
            </div>
            <div class="flex items-center gap-1.5 text-xs">
                @if($neg->receptor_confirmado)
                    <span style="color:#16a34a;">✅</span> <span class="text-gray-700">Receptor aprobó</span>
                @else
                    <span style="color:#d1d5db;">⏳</span> <span class="text-gray-400">Receptor pendiente</span>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ACCIONES --}}
    <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">

        {{-- RECEPTOR: aceptar / rechazar (estado Inicial o contraoferta) --}}
        @if($rol === 'receptor' && in_array($neg->estado, ['Inicial','contraoferta']))
        <form action="{{ route('negociaciones.aceptar', $neg->id_negociacion) }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#16a34a;">✓ Aceptar propuesta</button>
        </form>
        <form action="{{ route('negociaciones.rechazar', $neg->id_negociacion) }}" method="POST" onsubmit="return confirm('¿Rechazar esta propuesta?')">
            @csrf
            <button type="submit" class="px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#ef4444;">✕ Rechazar</button>
        </form>
        @endif

        {{-- APROBAR: ambos roles pueden aprobar cuando estado es aceptado y aún no han aprobado --}}
        @if($neg->estado === 'aceptado' && !$miConfirmado)
        <div class="w-full p-3 rounded-xl border mb-2" style="background:#eff6ff;border-color:#bfdbfe;">
            <p class="text-sm font-semibold mb-2" style="color:#1e40af;">
                🤝 {{ $rol === 'emisor' ? '¡Tu propuesta fue aceptada!' : '¡Aceptaste la propuesta!' }} Aprueba para continuar.
            </p>
            <form action="{{ route($rol === 'emisor' ? 'negociaciones.confirmar_emisor' : 'negociaciones.confirmar_receptor', $neg->id_negociacion) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#f58634;">
                    ✅ Aprobar intercambio
                </button>
            </form>
        </div>
        @endif

        {{-- YA APROBÉ: mostrar que estoy esperando al otro --}}
        @if($neg->estado === 'aceptado' && $miConfirmado && !$otroConfirmado)
        <div class="w-full p-3 rounded-xl border" style="background:#fefce8;border-color:#fde68a;">
            <p class="text-sm" style="color:#92400e;">⏳ Ya aprobaste. Esperando que {{ $rol === 'emisor' ? 'el receptor' : 'el emisor' }} apruebe...</p>
        </div>
        @endif

        {{-- PAGO: cuando ambos aprobaron --}}
        @if($ambosConfirmados && !$miPago)
        <div class="w-full p-4 rounded-xl border" style="background:#fff7ed;border-color:#fed7aa;">
            <p class="text-sm font-semibold mb-1" style="color:#c2410c;">💳 Ambos aprobaron — Procede con el pago del envío</p>
            <p class="text-xs mb-3" style="color:#9a3412;">Realiza el pago para completar el intercambio.</p>
            <button type="button" onclick="abrirModalPagoIntercambio({{ $neg->id_negociacion }})"
                    class="inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#f58634;">
                💳 Realizar pago de envío
            </button>
        </div>
        @endif

        {{-- YA PAGUÉ: esperando al otro --}}
        @if($ambosConfirmados && $miPago && !$otroPago)
        <div class="w-full p-3 rounded-xl border" style="background:#f0fdf4;border-color:#bbf7d0;">
            <p class="text-sm" style="color:#166534;">✅ Tu pago fue registrado. Esperando pago de {{ $rol === 'emisor' ? 'el receptor' : 'el emisor' }}.</p>
        </div>
        @endif

        {{-- EMISOR: cancelar --}}
        @if($rol === 'emisor' && in_array($neg->estado, ['Inicial','contraoferta']))
        <form action="{{ route('negociaciones.cancelar', $neg->id_negociacion) }}" method="POST" onsubmit="return confirm('¿Cancelar esta propuesta?')">
            @csrf
            <button type="submit" class="px-3 py-2 text-gray-600 text-xs font-semibold rounded-lg" style="background:#f1f5f9;">Cancelar propuesta</button>
        </form>
        @endif
    </div>
</div>
