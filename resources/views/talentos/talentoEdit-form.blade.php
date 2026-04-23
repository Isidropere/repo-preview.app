@extends('layouts.app')

@section('title', 'Cambialord - Editar Talento')

@section('content')
<div class="min-h-screen bg-gray-50 py-5">
    <div class="max-w-xl mx-auto px-4">
        @include('components.btn-volver', ['backUrl' => route('items.admintalento')])

        {{-- Header --}}
        <div class="text-center mb-5">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 mb-3">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Editar Talento</h1>
            <p class="text-gray-500 mt-1">Actualiza la información de tu talento o servicio</p>
        </div>

        {{-- Stepper --}}
        <div class="flex items-center justify-center mb-5 gap-0">
            <div class="flex items-center">
                <div id="step-icon-1" class="w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold shadow">1</div>
                <span class="ml-2 text-sm font-medium text-primary hidden sm:inline">Info</span>
            </div>
            <div class="w-12 sm:w-20 h-0.5 bg-gray-300 mx-2" id="step-line-1"></div>
            <div class="flex items-center">
                <div id="step-icon-2" class="w-9 h-9 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-bold">2</div>
                <span class="ml-2 text-sm font-medium text-gray-400 hidden sm:inline">Multimedia</span>
            </div>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
            <div class="flex items-center mb-1"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg><span class="font-semibold">Corrige los siguientes errores:</span></div>
            <ul class="list-disc list-inside text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-4">{{ session('error') }}</div>
        @endif

        <form action="{{ route('items.talentoupdate', $item->slug) }}" method="POST" enctype="multipart/form-data" id="productForm">
            @csrf
            @method('PUT')

            {{-- Campos fijos para talentos --}}
            <input type="hidden" name="condicion" value="1">
            <input type="hidden" name="id_tipo_item" value="2">
            <input type="hidden" name="peso_lbs" value="{{ $item->peso_lbs ?? 0 }}">
            <input type="hidden" name="alto_cm" value="{{ $item->alto_cm ?? 0 }}">
            <input type="hidden" name="ancho_cm" value="{{ $item->ancho_cm ?? 0 }}">
            <input type="hidden" name="profundo_cm" value="{{ $item->profundo_cm ?? 0 }}">
            <input type="hidden" name="id_categoria_item" value="{{ $item->id_categoria_item }}">

            {{-- ═══ PASO 1: Información ═══ --}}
            <div id="step-1" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">1</span>
                    Información del talento
                </h2>

                <div class="space-y-2.5">
                    {{-- Nombre --}}
                    <div>
                        <label for="item" class="block text-xs font-medium text-gray-700 mb-0.5">Nombre del talento <span class="text-red-500">*</span></label>
                        <input type="text" id="item" name="item" required value="{{ old('item', $item->item) }}" placeholder="Ej: Clases de guitarra, Diseño gráfico"
                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                        @error('item')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                    </div>

                    {{-- Precio y Modalidad --}}
                    <div class="grid grid-cols-2 gap-3" style="align-items:end">
                        <div>
                            <label for="valor" class="block text-xs font-medium text-gray-700 mb-0.5">Precio (DOP) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">RD$</span>
                                <input type="text" id="valor" name="valor" required value="{{ old('valor', number_format($item->valor, 2)) }}" placeholder="0.00" inputmode="decimal" oninput="formatPrice(this)"
                                       class="w-full pr-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors" style="padding-left:3rem">
                            </div>
                            @error('valor')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label for="tipo_trans" class="block text-xs font-medium text-gray-700 mb-0.5">Modalidad <span class="text-red-500">*</span></label>
                            <select id="tipo_trans" name="tipo_trans" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors bg-white">
                                <option value="3" {{ old('tipo_trans', $item->tipo_trans) == 3 ? 'selected' : '' }}>Venta o Intercambio</option>
                                <option value="2" {{ old('tipo_trans', $item->tipo_trans) == 2 ? 'selected' : '' }}>Intercambio</option>
                                <option value="1" {{ old('tipo_trans', $item->tipo_trans) == 1 ? 'selected' : '' }}>Venta</option>
                            </select>
                            @error('tipo_trans')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- Descuento --}}
                    <div>
                        <label for="descuento" class="block text-xs font-medium text-gray-700 mb-0.5">Descuento <span class="text-gray-400">(opcional)</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">%</span>
                            <input type="number" id="descuento" name="descuento" value="{{ old('descuento', $item->descuento ?? 0) }}" min="0" max="100" placeholder="0"
                                   class="w-full pr-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors" style="padding-left:1.75rem">
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label for="presentacion" class="block text-xs font-medium text-gray-700 mb-0.5">Descripción del talento <span class="text-red-500">*</span></label>
                        <textarea id="presentacion" name="presentacion" rows="2" required maxlength="250" placeholder="Describe tu talento o servicio: experiencia, qué incluye, horarios disponibles, etc."
                                  oninput="contarCaracteres(this,'contadorTalento')"
                                  class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors resize-none">{{ old('presentacion', $item->presentacion) }}</textarea>
                        <div class="flex justify-between items-center mt-0.5">
                            <span id="msgTalento" class="text-xs text-red-500 hidden">Máximo 250 caracteres</span>
                            <span id="contadorTalento" class="text-xs text-gray-400 ml-auto">{{ strlen(old('presentacion', $item->presentacion ?? '')) }}/250</span>
                        </div>
                        @error('presentacion')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                    </div>

                    {{-- Estatus --}}
                    <div>
                        <label for="estatus" class="block text-xs font-medium text-gray-700 mb-0.5">Estatus</label>
                        <select id="estatus" name="estatus" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors bg-white">
                            <option value="1" {{ old('estatus', $item->estatus) == 1 ? 'selected' : '' }}>Activo</option>
                            <option value="2" {{ old('estatus', $item->estatus) == 2 ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="button" onclick="validarPaso1Talento()" class="px-5 py-2 bg-primary text-white rounded-lg hover:bg-hoverPrimary transition-colors font-medium">
                        Siguiente <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- ═══ PASO 2: Multimedia ═══ --}}
            @php $imagenPrincipal = $item->imagenes->firstWhere('orden_visualizacion', 1); @endphp
            @php $imagenesSecundarias = $item->imagenes->sortBy('orden_visualizacion')->where('orden_visualizacion', '>', 1)->values(); @endphp
            <div id="step-2" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4 hidden">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">2</span>
                    Imágenes y video
                </h2>

                {{-- Imagen principal --}}
                <div class="mb-6">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Imagen o video principal</label>
                    @error('imagen_principal')<span class="text-red-500 text-xs mb-2 block">{{ $message }}</span>@enderror
                    <div id="imgPrincipalContenedor" class="relative w-full rounded-xl overflow-hidden border-2 border-dashed border-gray-300 bg-gray-50 hover:border-primary/50 hover:bg-primary/5 transition-all" style="min-height:180px;">
                        <div id="imgPrincipalPlaceholder" class="flex flex-col items-center justify-center py-10 text-center pointer-events-none {{ $imagenPrincipal ? 'hidden' : '' }}">
                            <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                                <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm text-gray-600 font-medium">Arrastra o haz clic para cambiar</p>
                            <p class="text-xs text-gray-400 mt-1">JPEG, PNG, WebP o MP4 (Máx. 10MB)</p>
                        </div>
                        @if($imagenPrincipal)
                            <img id="imagen_principal_preview" src="{{ \App\Helpers\ImageHelper::urlMedia($imagenPrincipal->ruta, $imagenPrincipal->nombre) }}" class="w-full rounded-xl object-cover" style="max-height:320px;" alt="Vista previa"/>
                        @else
                            <img id="imagen_principal_preview" class="hidden w-full rounded-xl object-cover" style="max-height:320px;" alt="Vista previa"/>
                        @endif
                        <video id="video_principal_preview" class="hidden w-full rounded-xl" style="max-height:320px;" controls></video>
                        <div id="imgPrincipalActions" class="{{ $imagenPrincipal ? '' : 'hidden' }} flex items-center justify-between px-3 py-2 bg-white/90 border-t border-gray-200">
                            <span id="imagen_principal_filename" class="text-xs text-gray-600 truncate max-w-[70%]">{{ $imagenPrincipal ? $imagenPrincipal->nombre : '' }}</span>
                            <button type="button" id="btnRemoveImgPrincipal" class="text-red-500 hover:text-red-700 text-xs font-semibold flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Eliminar
                            </button>
                        </div>
                        <label for="imagen_principal" class="absolute inset-0 cursor-pointer" id="labelImgPrincipal" {{ $imagenPrincipal ? 'style=pointer-events:none' : '' }}></label>
                        <input id="imagen_principal" name="imagen_principal" type="file" class="hidden imagen-principal-input" accept="image/jpeg,image/png,image/webp,video/mp4">
                    </div>
                </div>

                {{-- Imágenes adicionales --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Imágenes adicionales <span class="text-gray-400">(opcional)</span></label>
                    @error('imagenes.*')<span class="text-red-500 text-xs mb-2 block">{{ $message }}</span>@enderror
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="image-upload-container">
                        @for($i = 0; $i < 4; $i++)
                        @php $imagen = $imagenesSecundarias[$i] ?? null; @endphp
                        <label class="block h-24 border-2 border-dashed rounded-xl cursor-pointer bg-gray-50 border-gray-300 hover:border-primary/50 hover:bg-primary/5 overflow-hidden relative group transition-all">
                            @if($imagen)<input type="hidden" name="imagenes_existentes[]" value="{{ $imagen->id_imagen }}">@endif
                            <span class="file-name text-xs text-gray-700 absolute bottom-1 left-1 bg-white/80 px-1 rounded max-w-[90%] truncate {{ $imagen ? '' : 'hidden' }}">{{ $imagen ? $imagen->nombre : '' }}</span>
                            <input type="file" name="imagenes[]" accept="image/jpeg,image/png,image/webp" class="hidden imagen-input" data-index="{{ $i }}">
                            <div class="flex flex-col items-center justify-center h-full pointer-events-none text-center preview-default p-2 {{ $imagen ? 'hidden' : '' }}">
                                <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                <p class="text-xs text-gray-400">Imagen {{ $i + 1 }}</p>
                            </div>
                            <img class="preview-image {{ $imagen ? '' : 'hidden' }} absolute inset-0 w-full h-full object-cover rounded-xl"
                                 src="{{ $imagen ? \App\Helpers\ImageHelper::urlMedia($imagen->ruta, $imagen->nombre) : '' }}" alt="Vista previa"/>
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center preview-actions {{ $imagen ? '' : 'hidden' }}">
                                <button type="button" class="text-white bg-red-500 rounded-full p-1.5 hover:bg-red-600 transition-colors" data-action="remove">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </label>
                        @endfor
                    </div>
                </div>

                <div class="flex justify-between mt-4">
                    <button type="button" onclick="goToStep(1)" class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Anterior
                    </button>
                    <button type="submit" id="submitBtn" class="px-6 py-2 bg-secondary text-white rounded-lg hover:bg-hoverSecondary transition-colors font-medium shadow-sm flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ═══ Stepper ═══
let currentStep = 1;
function goToStep(step) {
    document.getElementById('step-' + currentStep).classList.add('hidden');
    document.getElementById('step-' + step).classList.remove('hidden');
    for (let i = 1; i <= 2; i++) {
        const icon = document.getElementById('step-icon-' + i);
        if (i <= step) { icon.className = 'w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold shadow'; }
        else { icon.className = 'w-9 h-9 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-bold'; }
    }
    const line = document.getElementById('step-line-1');
    line.className = step > 1 ? 'w-12 sm:w-20 h-0.5 bg-primary mx-2' : 'w-12 sm:w-20 h-0.5 bg-gray-300 mx-2';
    currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
@if($errors->any())
document.querySelectorAll('[id^="step-"]').forEach(el => el.classList.remove('hidden'));
const submitBtn = document.getElementById('submitBtn');
if (submitBtn) {
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Guardar cambios';
}
@endif

function formatPrice(input) {
    let v = input.value.replace(/[^0-9.]/g, '');
    input.value = v.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function contarCaracteres(textarea, contadorId) {
    const max = 250;
    const len = textarea.value.length;
    const contador = document.getElementById(contadorId);
    const msg = document.getElementById(contadorId.replace('contador', 'msg'));
    if (contador) contador.textContent = len + '/' + max;
    if (len >= max) {
        if (contador) contador.classList.replace('text-gray-400', 'text-red-500');
        if (msg) msg.classList.remove('hidden');
    } else {
        if (contador) contador.classList.replace('text-red-500', 'text-gray-400');
        if (msg) msg.classList.add('hidden');
    }
}

function validarPaso1Talento() {
    const campos = [
        { id: 'item', label: 'Nombre del talento' },
        { id: 'valor', label: 'Precio' },
        { id: 'presentacion', label: 'Descripción' },
    ];
    let valido = true;
    campos.forEach(c => {
        const el = document.getElementById(c.id);
        if (!el) return;
        if (!el.value.trim()) {
            valido = false;
            el.classList.add('border-red-400');
            let msg = el.parentElement.querySelector('.campo-error');
            if (!msg) {
                msg = document.createElement('span');
                msg.className = 'campo-error text-red-500 text-xs mt-1 block';
                el.parentElement.appendChild(msg);
            }
            msg.textContent = c.label + ' es obligatorio.';
        } else {
            el.classList.remove('border-red-400');
            const msg = el.parentElement.querySelector('.campo-error');
            if (msg) msg.remove();
        }
    });
    if (valido) goToStep(2);
}

document.addEventListener('DOMContentLoaded', function() {
    // ═══ Imagen principal ═══
    const inp = document.getElementById('imagen_principal');
    const placeholder = document.getElementById('imgPrincipalPlaceholder');
    const prevImg = document.getElementById('imagen_principal_preview');
    const prevVid = document.getElementById('video_principal_preview');
    const fname = document.getElementById('imagen_principal_filename');
    const actions = document.getElementById('imgPrincipalActions');
    const labelClick = document.getElementById('labelImgPrincipal');

    function mostrarPreviewPrincipal(file) {
        placeholder.classList.add('hidden');
        labelClick.style.pointerEvents = 'none';
        if (file.type.startsWith('image/')) {
            prevVid.classList.add('hidden');
            const r = new FileReader();
            r.onload = e => { prevImg.src = e.target.result; prevImg.classList.remove('hidden'); };
            r.readAsDataURL(file);
        } else {
            prevImg.classList.add('hidden');
            prevVid.src = URL.createObjectURL(file);
            prevVid.classList.remove('hidden');
        }
        fname.textContent = file.name;
        actions.classList.remove('hidden');
    }

    function limpiarPreviewPrincipal() {
        inp.value = '';
        prevImg.src = ''; prevImg.classList.add('hidden');
        prevVid.src = ''; prevVid.classList.add('hidden');
        placeholder.classList.remove('hidden');
        actions.classList.add('hidden');
        labelClick.style.pointerEvents = '';
    }

    inp.addEventListener('change', function(e) {
        const file = e.target.files[0]; if (!file) return;
        const valid = ['image/jpeg','image/png','image/webp','video/mp4'];
        if (!valid.includes(file.type)) { alert('Solo JPEG, PNG, WebP o MP4'); this.value=''; return; }
        if (file.size > 10*1024*1024) { alert('Máximo 10MB'); this.value=''; return; }
        mostrarPreviewPrincipal(file);
    });

    document.getElementById('btnRemoveImgPrincipal').addEventListener('click', function(e) {
        e.preventDefault(); e.stopPropagation();
        limpiarPreviewPrincipal();
    });

    // ═══ Imágenes adicionales ═══
    document.querySelectorAll('.imagen-input').forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0]; const label = input.closest('label'); if (!label || !file) return;
            const pi = label.querySelector('.preview-image'), pd = label.querySelector('.preview-default'), fn = label.querySelector('.file-name'), pa = label.querySelector('.preview-actions');
            const ok = ['image/jpeg','image/png','image/webp'];
            if (!ok.includes(file.type)) { alert('Solo JPG, PNG o WebP'); this.value=''; return; }
            if (file.size > 2*1024*1024) { alert('Máximo 2MB'); this.value=''; return; }
            const r = new FileReader();
            r.onload = e => { if(pd) pd.classList.add('hidden'); if(pi) { pi.src=e.target.result; pi.classList.remove('hidden'); } if(fn) { fn.textContent=file.name; fn.classList.remove('hidden'); } if(pa) pa.classList.remove('hidden'); };
            r.readAsDataURL(file);
        });
    });

    document.querySelectorAll('#image-upload-container [data-action="remove"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault(); e.stopPropagation();
            const label = this.closest('label'); if (!label) return;
            const inp = label.querySelector('.imagen-input'), pi = label.querySelector('.preview-image'), pd = label.querySelector('.preview-default'), fn = label.querySelector('.file-name'), pa = label.querySelector('.preview-actions');
            if (inp) inp.value=''; if (pi) { pi.src=''; pi.classList.add('hidden'); } if (pd) pd.classList.remove('hidden'); if (fn) fn.classList.add('hidden'); if (pa) pa.classList.add('hidden');
        });
    });
});

document.getElementById('productForm').addEventListener('submit', function(e) {
    let v = document.getElementById('valor'); v.value = v.value.replace(/,/g, '');
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Guardando...';
});
</script>
@endpush
