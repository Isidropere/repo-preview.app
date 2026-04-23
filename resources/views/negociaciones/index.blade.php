@extends('layouts.app')
@section('title', 'Negociar - ' . $itemModel->item)

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
<div class="max-w-2xl mx-auto px-4">

    @include('components.btn-volver', ['backUrl' => route('producto.detalle', $itemModel->slug)])

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Header naranja --}}
        <div style="background:linear-gradient(135deg,#ea580c 0%,#f58634 60%,#fb923c 100%);padding:1.25rem 1.5rem;">
            <div class="flex items-center gap-3">
                <div style="width:2.5rem;height:2.5rem;background:rgba(255,255,255,0.2);border-radius:0.75rem;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:1.25rem;height:1.25rem;color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div>
                    <h2 style="font-size:1rem;font-weight:800;color:#fff;margin:0;">🤝 Negociación de Intercambio</h2>
                    <p style="font-size:0.75rem;color:rgba(255,255,255,0.85);margin:0.1rem 0 0;">{{ $itemModel->item }}</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm mb-4">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm mb-4">{{ session('error') }}</div>
            @endif

            {{-- Info del producto --}}
            @php
                $imgNombre = $itemModel->imagenes->where('estado','aprobado')->first()?->nombre;
                $imgSrc = $imgNombre ? \App\Helpers\ImageHelper::urlMedia('imgs/articulos/items', $imgNombre) : asset('imgs/defaults/producto_default.svg');
            @endphp
            <div class="flex items-center gap-4 p-4 bg-orange-50 border border-orange-200 rounded-xl mb-6">
                <img src="{{ $imgSrc }}" alt="{{ $itemModel->item }}" class="w-16 h-16 rounded-xl object-cover border border-orange-100 flex-shrink-0">
                <div>
                    <p class="font-bold text-gray-800">{{ $itemModel->item }}</p>
                    <p class="text-sm text-orange-600 font-semibold">
                        @if($itemModel->valor) RD$ {{ number_format($itemModel->valor, 2) }} @else Solo intercambio @endif
                    </p>
                    <p class="text-xs text-gray-400">Dueño: {{ $itemModel->usuario?->nombres }} {{ $itemModel->usuario?->apellidos }}</p>
                </div>
            </div>

            {{-- Formulario de negociación --}}
            <form id="formNegociacion" class="space-y-4">
                @csrf
                <input type="hidden" id="negItemId" value="{{ $itemModel->id_item }}">

                {{-- Mensaje predefinido --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Mensaje predefinido</label>
                    <select id="mensajePredefinidoNeg"
                            style="width:100%;border:2px solid #fed7aa;border-radius:0.65rem;padding:0.5rem 0.75rem;font-size:0.85rem;background:#fff7ed;outline:none;"
                            onfocus="this.style.borderColor='#f58634'" onblur="this.style.borderColor='#fed7aa'">
                        <option value="">-- Selecciona un mensaje --</option>
                        @foreach($mensajesPredefinidos->where('activo', true)->whereIn('rol', ['emisor','general']) as $msg)
                        <option value="{{ $msg->mensaje }}">{{ $msg->titulo }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Mensaje --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tu mensaje <span class="text-red-500">*</span></label>
                    <textarea id="mensajeNeg" rows="3"
                              style="width:100%;border:2px solid #fed7aa;border-radius:0.65rem;padding:0.6rem 0.75rem;font-size:0.85rem;resize:none;background:#fff7ed;outline:none;box-sizing:border-box;"
                              onfocus="this.style.borderColor='#f58634'" onblur="this.style.borderColor='#fed7aa'"
                              placeholder="Describe tu propuesta..."></textarea>
                </div>

                {{-- Paquete --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Paquete a ofrecer</label>
                    <select id="paqueteNeg"
                            style="width:100%;border:2px solid #fed7aa;border-radius:0.65rem;padding:0.5rem 0.75rem;font-size:0.85rem;background:#fff7ed;outline:none;"
                            onfocus="this.style.borderColor='#f58634'" onblur="this.style.borderColor='#fed7aa'">
                        <option value="">-- Sin paquete --</option>
                        @foreach($todoLosPaquetes as $pkg)
                        <option value="{{ $pkg->id_paquete }}">{{ $pkg->nombre_paquete }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Monto --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Monto adicional <span class="text-gray-400 font-normal">(opcional)</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">RD$</span>
                        <input type="number" id="montoNeg" min="0" placeholder="0.00"
                               style="width:100%;border:2px solid #fed7aa;border-radius:0.65rem;padding:0.5rem 0.75rem 0.5rem 2.75rem;font-size:0.85rem;background:#fff7ed;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#f58634'" onblur="this.style.borderColor='#fed7aa'">
                    </div>
                </div>

                <div id="negError" class="hidden bg-red-50 border border-red-200 text-red-700 rounded-xl p-3 text-sm"></div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('producto.detalle', $itemModel->slug) }}"
                       class="flex-1 text-center border-2 border-gray-200 text-gray-600 hover:bg-gray-50 py-3 rounded-xl text-sm font-semibold transition-colors">
                        Cancelar
                    </a>
                    <button type="button" onclick="enviarNegociacion()"
                            style="flex:2;background:linear-gradient(135deg,#ea580c,#f58634);color:#fff;border:none;border-radius:0.875rem;padding:0.75rem;font-size:0.9rem;font-weight:800;cursor:pointer;box-shadow:0 4px 14px rgba(245,134,52,0.4);display:flex;align-items:center;justify-content:center;gap:0.5rem;">
                        <svg style="width:1rem;height:1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Enviar propuesta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('mensajePredefinidoNeg')?.addEventListener('change', function() {
    if (this.value) document.getElementById('mensajeNeg').value = this.value;
});

async function enviarNegociacion() {
    var mensaje = document.getElementById('mensajeNeg').value.trim();
    var paquete = document.getElementById('paqueteNeg').value;
    var monto   = document.getElementById('montoNeg').value;
    var itemId  = document.getElementById('negItemId').value;
    var errDiv  = document.getElementById('negError');

    errDiv.classList.add('hidden');
    if (!mensaje) { errDiv.textContent = 'El mensaje es obligatorio.'; errDiv.classList.remove('hidden'); return; }

    try {
        var resp = await fetch('{{ route("negociaciones.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
            body: JSON.stringify({ item_id: itemId, mensaje: mensaje, paquete_id: paquete || null, monto_oferta: monto || null })
        });
        var data = await resp.json();
        if (data.status === 'ok' || data.success) {
            window.location.href = '{{ route("negociaciones.mis") }}';
        } else {
            errDiv.textContent = data.message || 'Error al enviar.';
            errDiv.classList.remove('hidden');
        }
    } catch(e) {
        errDiv.textContent = 'Error de conexión.';
        errDiv.classList.remove('hidden');
    }
}
</script>
@endpush
