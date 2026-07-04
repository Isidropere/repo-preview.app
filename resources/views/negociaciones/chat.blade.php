@extends('layouts.app')
@section('title', 'Chat de Intercambio - Cambialord')

@push('head_styles')
<style>
    #btnEnviar {
        background-color: #f58634 !important;
        color: #ffffff !important;
        border: none !important;
        cursor: pointer;
        min-width: 100px;
        transition: background-color 0.2s ease-in-out;
    }
    #btnEnviar:hover {
        background-color: #e27526 !important;
    }
    #btnEnviar:disabled {
        background-color: #fdba74 !important;
        color: #ffffff !important;
        cursor: not-allowed;
        opacity: 0.8;
    }

    /* Estilos para ajustar a una sola página (sin scroll vertical en desktop) */
    @media (min-width: 768px) {
        .chat-outer-wrapper {
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
            min-height: auto !important;
            height: calc(100vh - 80px) !important;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .chat-outer-wrapper > div {
            width: 100%;
        }
        .chat-grid-container {
            margin-top: 0.5rem !important;
            align-items: stretch;
        }
        .left-panel-fixed, .chat-panel-height {
            height: calc(100vh - 180px) !important;
            min-height: 380px !important;
            max-height: 560px !important;
        }
        .left-panel-fixed {
            overflow-y: auto;
        }
        body {
            overflow: hidden !important;
        }
        footer {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-6 chat-outer-wrapper">
    <div class="max-w-5xl mx-auto px-4">

        @include('components.btn-volver', ['backUrl' => route('negociaciones.mis')])

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4 chat-grid-container">
            
            {{-- Panel Izquierdo: Información del Intercambio --}}
            <div class="md:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col left-panel-fixed">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Detalle del Intercambio</h3>
                
                @php
                    $imgNombre = $negociacion->item?->imagenes?->where('estado','aprobado')->first()?->nombre;
                    $imgSrc = $imgNombre ? \App\Helpers\ImageHelper::urlMedia('imgs/articulos/items', $imgNombre) : asset('imgs/defaults/producto_default.svg');
                @endphp
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ $imgSrc }}" alt="{{ $negociacion->item?->item }}" class="w-16 h-16 rounded-xl object-cover border border-gray-100 flex-shrink-0" loading="lazy">
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm leading-snug">{{ $negociacion->item?->item ?? 'Producto eliminado' }}</h4>
                        <p class="text-xs font-bold mt-0.5" style="color: #ea580c;">
                            @if($negociacion->item?->valor) RD$ {{ number_format($negociacion->item->valor, 2) }} @else Solo intercambio @endif
                        </p>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-3 mt-1 space-y-2.5">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-400">Estado:</span>
                        @php
                            $estadoBg = match($negociacion->estado) {
                                'Inicial'     => 'bg-yellow-100 text-yellow-800',
                                'contraoferta'=> 'bg-blue-100 text-blue-800',
                                'aceptado'    => 'bg-green-100 text-green-800',
                                'en_envio'    => 'bg-blue-100 text-blue-800',
                                'completado'  => 'bg-emerald-100 text-emerald-800',
                                'rechazado'   => 'bg-red-100 text-red-800',
                                'cancelado'   => 'bg-gray-100 text-gray-500',
                                default       => 'bg-gray-100 text-gray-500',
                            };
                            $estadoLabel = match($negociacion->estado) {
                                'Inicial'      => 'Propuesta enviada',
                                'en_envio'     => 'En envío',
                                'contraoferta' => 'Contraoferta',
                                'aceptado'     => 'Aceptado',
                                default        => ucfirst($negociacion->estado),
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] {{ $estadoBg }}">{{ $estadoLabel }}</span>
                    </div>

                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400">Otro participante:</span>
                        <span class="font-semibold text-gray-700">{{ $otroUsuario?->nombres }} {{ $otroUsuario?->apellidos }}</span>
                    </div>

                    @if($negociacion->monto_oferta)
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400">Monto adicional:</span>
                        <span class="font-bold text-emerald-600">RD$ {{ number_format($negociacion->monto_oferta, 2) }}</span>
                    </div>
                    @endif
                </div>

                {{-- Productos ofrecidos a cambio --}}
                @if($negociacion->items_ofrecidos && count($negociacion->items_ofrecidos))
                <div class="border-t border-gray-100 pt-3 mt-3">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Ofrecido a cambio:</p>
                    <div class="flex flex-col gap-1.5">
                        @foreach(\App\Models\Item::whereIn('id_item', $negociacion->items_ofrecidos)->get() as $io)
                        @php $cantidadIo = $negociacion->getCantidadOfrecida($io->id_item); @endphp
                        <span class="bg-blue-50 text-blue-700 text-xs px-2.5 py-1.5 rounded-lg border border-blue-100 font-medium leading-tight flex items-center gap-1.5">
                            {{ $io->item }}
                            @if($cantidadIo > 1)
                            <span class="inline-flex items-center justify-center bg-blue-600 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5 min-w-[20px]">× {{ $cantidadIo }}</span>
                            @endif
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <a href="{{ route('negociaciones.mis') }}" class="w-full text-center mt-5 text-xs font-bold text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 py-2.5 rounded-xl transition-colors">
                    Ver en Mis Intercambios
                </a>
            </div>

            {{-- Panel Derecho: Chat Principal --}}
            <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col overflow-hidden chat-panel-height" style="height: 600px;">
                
                {{-- Header del Chat --}}
                <div class="p-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-extrabold text-sm uppercase" style="background: #ffedd5; color: #ea580c;">
                            {{ substr($otroUsuario?->nombres ?? 'O', 0, 1) }}{{ substr($otroUsuario?->apellidos ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm leading-tight">{{ $otroUsuario?->nombres }} {{ $otroUsuario?->apellidos }}</h3>
                            <p class="text-[10px] text-gray-400 font-medium">Chat de negociación</p>
                        </div>
                    </div>
                </div>

                {{-- Mensajes Container --}}
                <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-3" style="background-image: radial-gradient(rgba(0,0,0,0.03) 1px, transparent 0); background-size: 16px 16px;">
                    @forelse($mensajes as $m)
                        @php
                            $align = $m['propio'] ? 'justify-end' : 'justify-start';
                            $bg = $m['propio'] ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800';
                            $radius = $m['propio'] ? 'rounded-2xl rounded-tr-none' : 'rounded-2xl rounded-tl-none';
                        @endphp
                        <div class="flex {{ $align }}">
                            <div class="max-w-[75%] {{ $bg }} {{ $radius }} px-4 py-2.5 shadow-sm">
                                <p class="text-sm leading-snug whitespace-pre-wrap word-break-break-word">{{ $m['mensaje'] }}</p>
                                <p class="text-[9px] mt-1 text-right opacity-70">{{ $m['fecha'] ?? '' }}</p>
                            </div>
                        </div>
                    @empty
                        <p id="noMessagesPlaceholder" class="text-center text-gray-400 text-xs py-8">No hay mensajes en este chat. ¡Comiencen a conversar!</p>
                    @endforelse
                </div>

                {{-- Controles del Formulario de Enviar --}}
                <div class="p-4 border-t border-gray-100 bg-white">
                    <div class="flex flex-col gap-3">
                        
                        {{-- Selectores de mensajes predefinidos --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <select id="tipoAccion" onchange="filtrarMensajes()" 
                                        style="width:100%;border:2px solid #fed7aa;border-radius:0.75rem;padding:0.5rem 0.75rem;font-size:0.78rem;background:#fff7ed;outline:none;"
                                        onfocus="this.style.borderColor='#f58634'" onblur="this.style.borderColor='#fed7aa'">
                                    <option value="">-- Acción / Filtro --</option>
                                    @foreach($accionesPredefinidas as $tipo)
                                    <option value="{{ $tipo }}">{{ ucfirst($tipo) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <select id="msgPredefinido" onchange="previsualizarMensaje()" 
                                        style="width:100%;border:2px solid #fed7aa;border-radius:0.75rem;padding:0.5rem 0.75rem;font-size:0.78rem;background:#fff7ed;outline:none;"
                                        onfocus="this.style.borderColor='#f58634'" onblur="this.style.borderColor='#fed7aa'">
                                    <option value="">-- Mensaje predefinido --</option>
                                </select>
                            </div>
                        </div>

                        {{-- Textarea de previsualización y botón enviar --}}
                        <div class="flex gap-3 items-end">
                            <div class="flex-1">
                                <textarea id="chatInput" rows="2" 
                                          style="width:100%;border:2px solid #fed7aa;border-radius:0.75rem;padding:0.5rem 0.75rem;font-size:0.85rem;background:#fcf8f2;outline:none;resize:none;box-sizing:border-box;" 
                                          placeholder="Escribe un mensaje aquí o selecciona uno predefinido arriba..."></textarea>
                            </div>
                            <button type="button" id="btnEnviar" onclick="enviarMensaje()" 
                                    class="rounded-xl px-5 py-3 text-sm font-bold shadow-md flex items-center gap-1.5 self-stretch justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Enviar
                            </button>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var _allPredefinedMessages = @json($mensajesPredefinidos ?? []);
    var _negId = "{{ $negociacion->id_negociacion }}";
    var _emisorId = "{{ $negociacion->usuario_emisor_id }}";
    var _receptorId = "{{ $negociacion->usuario_receptor_id }}";
    var _rol = "{{ $rol }}";

    // Hacer scroll al final al cargar la página y precargar mensajes predefinidos
    document.addEventListener("DOMContentLoaded", function() {
        var container = document.getElementById('chatMessages');
        container.scrollTop = container.scrollHeight;
        
        // Precargar todos los mensajes predefinidos aplicables al rol
        filtrarMensajes();

        // Auto actualizar chat cada 5 segundos
        setInterval(refrescarMensajes, 5000);
    });

    function escapeHtml(text) {
        var d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    function filtrarMensajes() {
        var tipoSelect = document.getElementById('tipoAccion');
        var msgSelect = document.getElementById('msgPredefinido');
        var preview = document.getElementById('chatInput');
        if (!tipoSelect || !msgSelect) return;

        var tipoSeleccionado = tipoSelect.value;
        msgSelect.innerHTML = '<option value="">-- Mensaje predefinido --</option>';
        if (preview && tipoSeleccionado) preview.value = '';

        _allPredefinedMessages.forEach(function(pm) {
            var matchTipo = !tipoSeleccionado || pm.tipo === tipoSeleccionado;
            var matchRol = pm.rol === 'general' || pm.rol === _rol;
            if (matchTipo && matchRol) {
                var opt = document.createElement('option');
                opt.value = pm.mensaje;
                opt.textContent = pm.titulo;
                opt.setAttribute('data-tipo', pm.tipo || '');
                msgSelect.appendChild(opt);
            }
        });
    }

    function previsualizarMensaje() {
        var msgSelect = document.getElementById('msgPredefinido');
        var preview = document.getElementById('chatInput');
        var tipoSelect = document.getElementById('tipoAccion');
        if (!msgSelect || !preview) return;
        
        if (msgSelect.value) {
            preview.value = msgSelect.value;
            
            // Si el usuario selecciona una pregunta directa, auto-sincronizar el filtro de acción
            var selectedOpt = msgSelect.options[msgSelect.selectedIndex];
            if (selectedOpt) {
                var tipo = selectedOpt.getAttribute('data-tipo');
                if (tipo && tipoSelect && !tipoSelect.value) {
                    tipoSelect.value = tipo;
                }
            }
        }
    }

    function enviarMensaje() {
        var input = document.getElementById('chatInput');
        var btn = document.getElementById('btnEnviar');
        var tipoSelect = document.getElementById('tipoAccion');
        if (!input || !input.value.trim() || btn.disabled) return;

        var mensaje = input.value.trim();
        var tipoAccion = tipoSelect ? tipoSelect.value : '';

        btn.disabled = true;
        btn.textContent = 'Enviando...';

        fetch('/negociaciones/' + _negId + '/mensaje', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ mensaje: mensaje, tipo_accion: tipoAccion || null })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Enviar`;
            if (data.success) {
                // Agregar burbuja local
                var container = document.getElementById('chatMessages');
                var placeholder = document.getElementById('noMessagesPlaceholder');
                if (placeholder) placeholder.remove();

                var div = document.createElement('div');
                div.className = 'flex justify-end';
                div.innerHTML = '<div class="max-w-[75%] bg-indigo-600 text-white rounded-2xl rounded-tr-none px-4 py-2.5 shadow-sm">' +
                    '<p class="text-sm leading-snug whitespace-pre-wrap word-break-break-word">' + escapeHtml(mensaje) + '</p>' +
                    '<p class="text-[9px] mt-1 text-right opacity-70">Ahora</p>' +
                    '</div>';
                container.appendChild(div);
                container.scrollTop = container.scrollHeight;

                // Reset form completely
                input.value = '';
                var msgSelect = document.getElementById('msgPredefinido');
                if (msgSelect) msgSelect.value = '';
                if (tipoSelect) {
                    tipoSelect.value = '';
                    filtrarMensajes(); // Repopular todo
                }
            } else {
                alert(data.message || 'Error al enviar mensaje.');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Enviar`;
            alert('Error de conexión al enviar mensaje.');
        });
    }

    function refrescarMensajes() {
        var container = document.getElementById('chatMessages');
        if (!container) return;

        fetch('/carrito/negociaciones/mensajes/' + _emisorId + '/' + _receptorId, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var msgs = data.mensajes || [];
            if (msgs.length === 0) return;

            var html = '';
            var placeholder = document.getElementById('noMessagesPlaceholder');
            if (placeholder && msgs.length > 0) placeholder.remove();

            msgs.forEach(function(m) {
                var align = m.propio ? 'justify-end' : 'justify-start';
                var bg = m.propio ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800';
                var radius = m.propio ? 'rounded-2xl rounded-tr-none' : 'rounded-2xl rounded-tl-none';
                
                html += '<div class="flex ' + align + '">';
                html += '<div class="max-w-[75%] ' + bg + ' ' + radius + ' px-4 py-2.5 shadow-sm">';
                html += '<p class="text-sm leading-snug whitespace-pre-wrap word-break-break-word">' + escapeHtml(m.mensaje) + '</p>';
                html += '<p class="text-[9px] mt-1 text-right opacity-70">' + (m.fecha || '') + '</p>';
                html += '</div></div>';
            });

            // Solo actualizar y hacer scroll si ha cambiado el número de elementos o longitud
            var oldScrollHeight = container.scrollHeight;
            var oldScrollTop = container.scrollTop;
            var wasAtBottom = (container.scrollHeight - container.clientHeight <= container.scrollTop + 50);

            container.innerHTML = html;

            if (wasAtBottom) {
                container.scrollTop = container.scrollHeight;
            } else {
                container.scrollTop = oldScrollTop;
            }
        })
        .catch(function() {
            console.log('Error al refrescar mensajes.');
        });
    }
</script>
@endpush
