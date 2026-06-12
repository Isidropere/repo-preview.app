@php
    $estadoColor = match($neg->estado) {
        'Inicial'     => 'bg-yellow-100 text-yellow-800',
        'contraoferta'=> 'bg-blue-100 text-blue-800',
        'aceptado'    => 'bg-green-100 text-green-800',
        'en_envio'    => 'bg-blue-100 text-blue-800',
        'completado'  => 'bg-emerald-100 text-emerald-800',
        'rechazado'   => 'bg-red-100 text-red-800',
        'cancelado'   => 'bg-gray-100 text-gray-500',
        default       => 'bg-gray-100 text-gray-500',
    };

    $ambosConfirmados = $neg->emisor_confirmado && ($neg->receptor_confirmado ?? false);

    $negService = app(\App\Services\NegociacionService::class);
    $esServicioServicio = $negService->esServicioServicio($neg);
    $esProductoServicio = $negService->esProductoServicio($neg);
    $esProductoProducto = $negService->esProductoProducto($neg);

    // ¿Requiere pago este usuario?
    if ($esServicioServicio) {
        $requierePago = false;
        $pagoOpcional = false;
        $montoEnvio = 0;
    } elseif ($esProductoServicio) {
        $requierePago = false; 
        $pagoOpcional = true; 
        $montoEnvio = $costoEnvioPorNeg[$neg->id_negociacion] ?? 0;
    } else {
        // Producto ↔ Producto: ambos pagan
        $requierePago = true;
        $pagoOpcional = false;
        $montoEnvio = $costoEnvioPorNeg[$neg->id_negociacion] ?? 0;
    }

    $estadoLabel = match(true) {
        $neg->estado === 'completado'                              => '✅ Completado',
        $neg->estado === 'en_envio'                               => '🚚 En envío',
        $neg->estado === 'rechazado'                               => 'Rechazado',
        $neg->estado === 'cancelado'                               => 'Cancelado',
        $ambosConfirmados && ($esServicioServicio || $esProductoServicio) => '✅ Confirmado',
        $ambosConfirmados                                          => '💳 Listo para pago',
        $neg->estado === 'aceptado'                                => 'Aceptado — Pendiente aprobación',
        $neg->estado === 'contraoferta'                            => 'Contraoferta',
        $neg->estado === 'Inicial'                                 => 'Propuesta enviada',
        default                                                    => ucfirst($neg->estado),
    };

    $imgNombre = $neg->item?->imagenes?->where('estado','aprobado')->first()?->nombre;
    $imgSrc = $imgNombre
        ? \App\Helpers\ImageHelper::urlMedia('imgs/articulos/items', $imgNombre)
        : asset('imgs/defaults/producto_default.svg');
    $otroUsuario = $rol === 'receptor' ? $neg->usuario : $neg->usuarioReceptor;
    $miPago = $rol === 'emisor' ? ($neg->pago_emisor ?? false) : ($neg->pago_receptor ?? false);
    $otroPago = $rol === 'emisor' ? ($neg->pago_receptor ?? false) : ($neg->pago_emisor ?? false);
    $miConfirmado = $rol === 'emisor' ? ($neg->emisor_confirmado ?? false) : ($neg->receptor_confirmado ?? false);
    $otroConfirmado = $rol === 'emisor' ? ($neg->receptor_confirmado ?? false) : ($neg->emisor_confirmado ?? false);

    // Verificar si el usuario tiene dirección
    $tieneDireccion = \App\Models\Direcciones::where('id_user', auth()->id())->exists();
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4 {{ $ambosConfirmados ? 'border-emerald-300' : '' }}">

    {{-- Cabecera --}}
    <div class="flex items-start justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
            <img src="{{ $imgSrc }}" alt="{{ $neg->item?->item }}"
                 class="w-14 h-14 rounded-xl object-cover border border-gray-100 flex-shrink-0" loading="lazy" width="56" height="56">
            <div>
                <p class="font-semibold text-gray-800 text-sm">{{ $neg->item?->item ?? 'Producto eliminado' }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $rol === 'receptor' ? 'Propuesta de: ' : 'Para: ' }}
                    <span class="font-medium text-gray-600">{{ $otroUsuario?->nombres }} {{ $otroUsuario?->apellidos }}</span>
                </p>
                <p class="text-xs text-gray-400">{{ $neg->fecha_creacion ? \Carbon\Carbon::parse($neg->fecha_creacion)->diffForHumans() : '' }}</p>
            </div>
        </div>
        <div class="flex flex-col items-end gap-1">
            @php
                $tipoLabel = match(true) {
                    $esServicioServicio => 'Servicio vs Servicio',
                    $esProductoServicio => 'Producto vs Servicio',
                    default             => 'Producto vs Producto',
                };
                $tipoBg = match(true) {
                    $esServicioServicio => 'bg-purple-100 text-purple-700 border-purple-200',
                    $esProductoServicio => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                    default             => 'bg-gray-100 text-gray-700 border-gray-200',
                };
            @endphp
            <span class="text-[10px] uppercase tracking-wider px-2 py-0.5 rounded border font-bold {{ $tipoBg }}">
                {{ $tipoLabel }}
            </span>
            <span class="text-xs px-2.5 py-1 rounded-full font-semibold flex-shrink-0 {{ $estadoColor }}">{{ $estadoLabel }}</span>
        </div>
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
                {{ $io->item }} @if($io->valor) · RD$ {{ number_format($io->valor, 2) }} @endif
            </span>
            @endforeach
        </div>
    </div>
    @endif

    @if($neg->monto_oferta)
    <p class="text-xs text-gray-500 mb-4">Monto adicional: <span class="font-bold text-blue-700">RD$ {{ number_format($neg->monto_oferta, 2) }}</span></p>
    @endif

    {{-- CHAT COLAPSABLE: Deshabilitado temporalmente a solicitud --}}
    {{-- 
    @if(in_array($neg->estado, ['Inicial', 'aceptado', 'contraoferta']))
    ... (chat code) ...
    @endif 
    --}}

    {{-- Estado de aprobaciones (y ubicación para servicio↔servicio) --}}
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
                @if($neg->receptor_confirmado ?? false)
                    <span style="color:#16a34a;">✅</span> <span class="text-gray-700">Receptor aprobó</span>
                @else
                    <span style="color:#d1d5db;">⏳</span> <span class="text-gray-400">Receptor pendiente</span>
                @endif
            </div>
        </div>

        {{-- Ubicación de la otra parte (solo para servicios o mixto) --}}
        @if($ambosConfirmados && !$esProductoProducto)
        @php
            // Mostrar la ubicación de la OTRA parte (no la mía)
            if ($rol === 'receptor') {
                $dirOtro = $neg->usuario?->direcciones?->where('es_predeterminada', 1)->first()
                         ?? $neg->usuario?->direcciones?->first();
            } else {
                $dirOtro = $neg->usuarioReceptor?->direcciones?->where('es_predeterminada', 1)->first()
                         ?? $neg->usuarioReceptor?->direcciones?->first();
            }
            $municipioOtro  = $dirOtro?->municipio?->municipio  ?? null;
            $provinciaOtro  = $dirOtro?->provincia?->provincia  ?? null;
            $nombreOtro     = $otroUsuario?->nombres . ' ' . $otroUsuario?->apellidos;
            
            // Texto dinámico según el tipo
            $tituloUbicacion = match(true) {
                $esServicioServicio => "📍 Ubicación del prestador del servicio",
                $esProductoServicio => "📍 Ubicación para entrega/retiro",
                default             => "📍 Ubicación de la otra parte"
            };
        @endphp
        <div class="mt-3 pt-3 border-t border-green-100">
            <p class="text-xs font-bold mb-1" style="color:#166534;">{{ $tituloUbicacion }} ({{ trim($nombreOtro) ?: 'la otra parte' }})</p>
            @if($municipioOtro || $provinciaOtro)
            <p class="text-sm font-semibold text-gray-800">
                {{ $municipioOtro }}@if($municipioOtro && $provinciaOtro), @endif{{ $provinciaOtro }}
            </p>
            <p class="text-xs mt-0.5 text-gray-500">Usa esta información para coordinar el encuentro o el envío.</p>
            @else
            <p class="text-xs text-gray-400 italic">La otra parte aún no tiene dirección registrada.</p>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- ACCIONES --}}
    <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">

        {{-- RECEPTOR: aceptar / rechazar (estado Inicial o contraoferta) --}}
        @if($rol === 'receptor' && in_array($neg->estado, ['Inicial','contraoferta']))
        <form action="{{ route('negociaciones.aceptar', $neg->id_negociacion) }}" method="POST">
            @csrf
            <button type="submit" onclick="this.disabled=true;this.textContent='Aceptando...';this.form.submit();" class="px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#16a34a;">✓ Aceptar propuesta</button>
        </form>
        <form action="{{ route('negociaciones.rechazar', $neg->id_negociacion) }}" method="POST" onsubmit="return confirm('¿Rechazar esta propuesta?')">
            @csrf
            <button type="submit" onclick="this.disabled=true;this.textContent='Rechazando...';this.form.submit();" class="px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#ef4444;">✕ Rechazar</button>
        </form>
        @endif

        {{-- EMISOR: aceptar contraoferta del receptor --}}
        @if($rol === 'emisor' && $neg->estado === 'contraoferta')
        <div class="w-full p-3 rounded-xl border mb-2" style="background:#eff6ff;border-color:#bfdbfe;">
            <p class="text-xs font-semibold mb-2" style="color:#1e40af;">
                💬 El receptor envió una contraoferta
                @if($neg->monto_contra_oferta)
                    — Monto propuesto: <strong>RD$ {{ number_format($neg->monto_contra_oferta, 2) }}</strong>
                @endif
            </p>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <form action="{{ route('negociaciones.aceptar_contraoferta', $neg->id_negociacion) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="this.disabled=true;this.textContent='Aceptando...';this.form.submit();" class="px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#16a34a;">✓ Aceptar contraoferta</button>
                </form>
                <form action="{{ route('negociaciones.rechazar', $neg->id_negociacion) }}" method="POST" onsubmit="return confirm('¿Rechazar esta contraoferta?')">
                    @csrf
                    <button type="submit" onclick="this.disabled=true;this.textContent='Rechazando...';this.form.submit();" class="px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#ef4444;">✕ Rechazar</button>
                </form>
            </div>
        </div>
        @endif

        {{-- APROBAR: ambos roles pueden aprobar cuando estado es aceptado y aún no han aprobado --}}
        @if($neg->estado === 'aceptado' && !$miConfirmado)
        <div class="w-full p-3 rounded-xl border mb-2" style="background:#eff6ff;border-color:#bfdbfe;">
            <p class="text-sm font-semibold mb-2" style="color:#1e40af;">
                🤝 {{ $rol === 'emisor' ? '¡Tu propuesta fue aceptada!' : '¡Aceptaste la propuesta!' }} Aprueba para continuar.
            </p>
            <form action="{{ route($rol === 'emisor' ? 'negociaciones.confirmar_emisor' : 'negociaciones.confirmar_receptor', $neg->id_negociacion) }}" method="POST">
                @csrf
                <button type="submit" onclick="this.disabled=true;this.textContent='Aprobando...';this.form.submit();" class="px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#f58634;">
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

        {{-- PAGO: cuando ambos aprobaron y requiere pago obligatorio (producto↔producto) --}}
        @if($ambosConfirmados && $requierePago && !$miPago)
            @if(!$tieneDireccion)
            <div class="w-full p-4 rounded-xl border" style="background:#fef2f2;border-color:#fecaca;">
                <p class="text-sm font-semibold mb-2" style="color:#dc2626;">⚠️ Necesitas una dirección de envío</p>
                <p class="text-xs mb-3" style="color:#991b1b;">Debes registrar tu dirección antes de realizar el pago.</p>
                <a href="{{ route('direcciones.index', ['return_url' => url()->full()]) }}" class="inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#dc2626;text-decoration:none;">
                    ➕ Crear dirección
                </a>
            </div>
            @else
            <div class="w-full p-4 rounded-xl border" style="background:{{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '#fffbeb' : '#fff7ed' }};border-color:{{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '#fde68a' : '#fed7aa' }};">
                <p class="text-sm font-semibold mb-1" style="color:{{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '#b45309' : '#c2410c' }};">
                    {{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '⚠️ Esperando definición de costo de envío' : '💳 Ambos aprobaron — Procede con el pago del envío' }}
                </p>
                @if($montoEnvio === 'MISSING_DELIVERY_TARIFF')
                    <p class="text-xs mb-3" style="color:#b45309;">El sistema espera por una definición para el cálculo de Análisis de costos de envío. No se puede realizar el pago hasta que el administrador configure la tarifa.</p>
                @endif
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.25rem;">
                    <p class="text-xs" style="color:{{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '#b45309' : '#9a3412' }};margin:0;">
                        Monto a pagar: 
                        <span id="monto-envio-{{ $neg->id_negociacion }}" style="font-weight:800;{{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? 'color:#e11d48;' : '' }}">
                            {{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? 'No se pudo calcular el envío' : 'RD$ ' . number_format($montoEnvio, 2) }}
                        </span>
                    </p>
                    <button type="button" onclick="recalcularEnvio({{ $neg->id_negociacion }})"
                            style="background:none;border:1px solid {{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '#fde68a' : '#fed7aa' }};border-radius:4px;padding:1px 6px;font-size:0.7rem;color:{{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '#b45309' : '#c2410c' }};cursor:pointer;" title="Recalcular envío">🔄</button>
                </div>
                <p class="text-xs mb-3" style="color:{{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '#b45309' : '#9a3412' }};">Artículo: {{ $neg->item?->item ?? 'N/A' }}</p>
                @if($montoEnvio === 'MISSING_DELIVERY_TARIFF')
                    <button type="button" disabled
                            class="inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#cbd5e1;cursor:not-allowed;">
                        💳 Esperando por el administrador para el costo de envío
                    </button>
                @else
                    <button type="button" onclick="abrirModalPagoIntercambio({{ $neg->id_negociacion }}, {{ $montoEnvio }}, '{{ addslashes($neg->item?->item ?? 'Intercambio') }}')"
                            id="btn-pago-{{ $neg->id_negociacion }}"
                            class="inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#f58634;">
                        💳 Realizar pago de envío
                    </button>
                @endif
            </div>
            @endif
        @endif

        {{-- PAGO OPCIONAL: producto↔servicio, yo soy el del producto --}}
        @if($ambosConfirmados && $pagoOpcional && !$miPago)
        @php
            // Determinar si YO soy el dueño del producto
            $duenioProductoId = (!$solicitadoEsServicio && $todosOfrecidosServicio)
                ? $neg->usuario_receptor_id   // receptor tiene el producto
                : $neg->usuario_emisor_id;    // emisor tiene el producto
            $soyDuenioProducto = auth()->id() == $duenioProductoId;
            $modoYaElegido = !empty($neg->modo_entrega);
        @endphp

            @if($soyDuenioProducto && !$modoYaElegido)
            {{-- Dueño del producto elige cómo entregarlo --}}
            <div class="w-full p-4 rounded-xl border" style="background:{{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '#fffbeb' : '#fff7ed' }};border-color:{{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '#fde68a' : '#fed7aa' }};">
                <p class="text-sm font-semibold mb-1" style="color:{{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '#b45309' : '#c2410c' }};">📦 ¿Cómo entregarás tu producto?</p>
                <p class="text-xs mb-3" style="color:{{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '#b45309' : '#9a3412' }};">
                    {{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? 'El sistema espera por una definición para el cálculo de Análisis de costos de envío. No puedes seleccionar envío hasta que se configure la tarifa.' : 'Elige si envías el producto o si la otra parte lo retira en persona.' }}
                </p>
                <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                    {{-- Enviar: abre modal de pago directamente --}}
                    @if($montoEnvio === 'MISSING_DELIVERY_TARIFF')
                        <button type="button" disabled
                                class="inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#cbd5e1;cursor:not-allowed;" title="Tarifa de envío no configurada">
                            🚚 Enviar (Tarifa no calculable)
                        </button>
                    @else
                        <button type="button"
                                onclick="abrirModalPagoIntercambio({{ $neg->id_negociacion }}, {{ $montoEnvio }}, '{{ addslashes($neg->item?->item ?? 'Intercambio') }}', 'envio')"
                                class="inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#f58634;">
                            🚚 Enviar y pagar
                        </button>
                    @endif
                    {{-- Retiro: POST directo sin pago --}}
                    <form action="{{ route('negociaciones.modo_entrega', $neg->id_negociacion) }}" method="POST">
                        @csrf
                        <input type="hidden" name="modo" value="retiro">
                        <button type="submit" onclick="this.disabled=true;this.textContent='Procesando...';this.form.submit();"
                                class="inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#16a34a;">
                            🤝 Retiro en persona
                        </button>
                    </form>
                </div>
            </div>

            @elseif($soyDuenioProducto && $modoYaElegido)
            {{-- Dueño del producto ya eligió, mostrar estado --}}
            <div class="w-full p-3 rounded-xl border" style="background:{{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '#fffbeb' : '#f0fdf4' }};border-color:{{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '#fde68a' : '#bbf7d0' }};">
                @if($neg->modo_entrega === 'envio')
                    <p class="text-sm" style="color:{{ $montoEnvio === 'MISSING_DELIVERY_TARIFF' ? '#b45309' : '#166534' }};">🚚 Elegiste enviar el producto. Los administradores gestionarán el envío.</p>
                    @if(!$miPago)
                        @if($montoEnvio === 'MISSING_DELIVERY_TARIFF')
                            <p class="text-xs mb-3 mt-1" style="color:#b45309;">El sistema espera por una definición para el cálculo de Análisis de costos de envío. No se puede realizar el pago hasta que el administrador configure la tarifa.</p>
                            <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.5rem;margin-bottom:0.75rem;">
                                <p class="text-xs" style="color:#b45309;margin:0;">Costo de envío: <span id="monto-envio-{{ $neg->id_negociacion }}" style="font-weight:800;color:#e11d48;">No se pudo calcular el envío</span></p>
                                <button type="button" onclick="recalcularEnvio({{ $neg->id_negociacion }})"
                                        style="background:none;border:1px solid #fde68a;border-radius:4px;padding:1px 6px;font-size:0.7rem;color:#b45309;cursor:pointer;">🔄</button>
                            </div>
                            <button type="button" disabled
                                    class="inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#cbd5e1;cursor:not-allowed;">
                                💳 Esperando por el administrador para el costo de envío
                            </button>
                        @else
                            <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.5rem;">
                                <p class="text-xs" style="color:#9a3412;margin:0;">Costo de envío: <span id="monto-envio-{{ $neg->id_negociacion }}" style="font-weight:800;">RD$ {{ number_format($montoEnvio, 2) }}</span></p>
                                <button type="button" onclick="recalcularEnvio({{ $neg->id_negociacion }})"
                                        style="background:none;border:1px solid #fed7aa;border-radius:4px;padding:1px 6px;font-size:0.7rem;color:#c2410c;cursor:pointer;">🔄</button>
                            </div>
                            <button type="button" onclick="abrirModalPagoIntercambio({{ $neg->id_negociacion }}, {{ $montoEnvio }}, '{{ addslashes($neg->item?->item ?? 'Intercambio') }}')"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-lg mt-2" style="background:#f58634;">
                                💳 Pagar envío
                            </button>
                        @endif
                    @else
                        <p class="text-xs mt-1" style="color:#166534;">✅ Pago de envío registrado.</p>
                    @endif
                @else
                    <p class="text-sm" style="color:#166534;">🤝 Elegiste retiro en persona. Coordina con la otra parte para el retiro.</p>
                @endif
            </div>

            @elseif(!$soyDuenioProducto && !$modoYaElegido)
            {{-- Quien recibe espera que el dueño elija --}}
            <div class="w-full p-3 rounded-xl border" style="background:#fefce8;border-color:#fde68a;">
                <p class="text-sm" style="color:#92400e;">⏳ Esperando que el dueño del producto elija el modo de entrega (envío o retiro en persona).</p>
            </div>

            @elseif(!$soyDuenioProducto && $modoYaElegido)
            {{-- Quien recibe ve el modo elegido y puede confirmar entrega --}}
            <div class="w-full p-4 rounded-xl border" style="background:#f0fdf4;border-color:#bbf7d0;">
                @if($neg->modo_entrega === 'envio')
                    <p class="text-sm font-semibold mb-1" style="color:#166534;">🚚 El producto será enviado</p>
                    <p class="text-xs mb-3" style="color:#166534;">Los administradores gestionarán el envío. Cuando recibas el producto, confírmalo aquí.</p>
                    @if(!$neg->entrega_confirmada)
                    <form action="{{ route('negociaciones.confirmar_entrega', $neg->id_negociacion) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('¿Confirmas que recibiste el producto?') && (this.disabled=true, this.textContent='Confirmando...', true);"
                                class="px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#16a34a;">
                            ✅ Confirmar recepción del producto
                        </button>
                    </form>
                    @else
                    <p class="text-xs" style="color:#166534;">✅ Ya confirmaste la recepción.</p>
                    @endif
                @else
                    <p class="text-sm font-semibold mb-1" style="color:#166534;">🤝 Retiro en persona</p>
                    <p class="text-xs mb-3" style="color:#166534;">Coordina con el dueño del producto para el retiro. Cuando lo retires, confírmalo aquí.</p>
                    @if(!$neg->entrega_confirmada)
                    <form action="{{ route('negociaciones.confirmar_entrega', $neg->id_negociacion) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('¿Confirmas que retiraste el producto?') && (this.disabled=true, this.textContent='Confirmando...', true);"
                                class="px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#16a34a;">
                            ✅ Confirmar retiro del producto
                        </button>
                    </form>
                    @else
                    <p class="text-xs" style="color:#166534;">✅ Ya confirmaste el retiro.</p>
                    @endif
                @endif
            </div>
            @endif
        @endif

        {{-- SOY DEL SERVICIO en producto↔servicio: solo informar, no requiere acción --}}
        @if($ambosConfirmados && $esProductoServicio && !$pagoOpcional && !$requierePago)
        @php
            $modoYaElegidoServ = !empty($neg->modo_entrega);
        @endphp
        <div class="w-full p-3 rounded-xl border" style="background:#f0fdf4;border-color:#bbf7d0;">
            <p class="text-sm font-semibold" style="color:#166534;">✅ Tu parte es un servicio — no requiere pago de envío</p>
            @if(!$modoYaElegidoServ)
                <p class="text-xs mt-1" style="color:#166534;">Esperando que el dueño del producto elija el modo de entrega.</p>
            @elseif($neg->modo_entrega === 'envio')
                <p class="text-xs mt-1" style="color:#166534;">El dueño del producto eligió envío. Los administradores gestionarán la logística.</p>
            @else
                <p class="text-xs mt-1" style="color:#166534;">El dueño del producto eligió retiro en persona. Coordinen directamente.</p>
            @endif
            @if($neg->entrega_confirmada)
                <p class="text-xs mt-1 font-semibold" style="color:#166534;">✅ Entrega confirmada. Intercambio completado.</p>
            @endif
        </div>
        @endif

        {{-- EN ENVÍO: notificar al usuario que el admin gestiona el envío --}}
        @if($neg->estado === 'en_envio')
        <div class="w-full p-4 rounded-xl border" style="background:#eff6ff;border-color:#bfdbfe;">
            <p class="text-sm font-semibold mb-1" style="color:#1e40af;">🚚 ¡Pagos registrados! En proceso de envío</p>
            <p class="text-xs" style="color:#3730a3;">Ambos pagos fueron registrados correctamente. El equipo de administración está gestionando el envío de los productos. Recibirás una notificación cuando el proceso esté completado.</p>
        </div>
        @endif

        {{-- SERVICIO ↔ SERVICIO: no requiere pago, auto-completar --}}
        @if($ambosConfirmados && $esServicioServicio && $neg->estado !== 'completado')
        <div class="w-full p-4 rounded-xl border" style="background:#f0fdf4;border-color:#bbf7d0;">
            <p class="text-sm font-semibold mb-2" style="color:#166534;">🎉 Intercambio de servicios confirmado</p>
            <p class="text-xs mb-3" style="color:#166534;">No requiere pago de envío. Coordinen directamente la prestación de servicios.</p>
            <form action="{{ route('negociaciones.completar', $neg->id_negociacion) }}" method="POST">
                @csrf
                <button type="submit" onclick="this.disabled=true;this.textContent='Completando...';this.form.submit();" class="px-4 py-2 text-white text-xs font-bold rounded-lg" style="background:#16a34a;">
                    ✅ Marcar como completado
                </button>
            </form>
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
            <button type="submit" onclick="this.disabled=true;this.textContent='Cancelando...';this.form.submit();" class="px-3 py-2 text-gray-600 text-xs font-semibold rounded-lg" style="background:#f1f5f9;">Cancelar propuesta</button>
        </form>
        @endif
    </div>

    {{-- PUNTUACIÓN: cuando el intercambio está completado --}}
    @if($neg->estado === 'completado')
    @php
        $otroUserId = $rol === 'emisor' ? $neg->usuario_receptor_id : $neg->usuario_emisor_id;
        try {
            $yaCalifique = \App\Models\Rating::where('id_usuario', auth()->id())
                ->where('id_miembro', $otroUserId)
                ->exists();
        } catch (\Throwable $e) {
            $yaCalifique = false;
        }
    @endphp
    <div class="mt-3 pt-3 border-t border-gray-100">
        @if($yaCalifique)
        <p class="text-xs" style="color:#16a34a;">⭐ Ya calificaste este intercambio. ¡Gracias!</p>
        @else
        <div class="p-3 rounded-xl" style="background:#fefce8;border:1px solid #fde68a;">
            <p class="text-xs font-semibold mb-2" style="color:#92400e;">⭐ ¿Cómo fue tu experiencia? (opcional)</p>
            <form action="{{ route('rating.store') }}" method="POST" style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                @csrf
                <input type="hidden" name="id_miembro" value="{{ $otroUserId }}">
                <div style="display:flex;gap:2px;" id="stars-{{ $neg->id_negociacion }}">
                    @for($s = 1; $s <= 5; $s++)
                    <label style="cursor:pointer;font-size:1.25rem;color:#d1d5db;transition:color .15s;"
                           onmouseover="highlightStars({{ $neg->id_negociacion }}, {{ $s }})"
                           onmouseout="resetStars({{ $neg->id_negociacion }})">
                        <input type="radio" name="rating" value="{{ $s }}" style="display:none;" onclick="selectStar({{ $neg->id_negociacion }}, {{ $s }})">★
                    </label>
                    @endfor
                </div>
                <button type="submit" class="px-3 py-1.5 text-white text-xs font-bold rounded-lg" style="background:#f58634;">Enviar</button>
            </form>
        </div>
        @endif
    </div>
    @endif
</div>


