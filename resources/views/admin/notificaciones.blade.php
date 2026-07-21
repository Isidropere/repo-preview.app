@extends('layouts.admin')

@section('title', 'Enviar Notificación - Admin')

@section('content')
<div class="min-h-screen bg-gray-50 py-5">
    <div class="max-w-xl mx-auto px-4">

        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-800">Enviar Notificación</h1>
                <p class="text-xs text-gray-500">Notifica a usuarios sobre ventas, compras, intercambios o servicios</p>
            </div>
        </div>

        @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-3 rounded-lg mb-4 text-sm">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded-lg mb-4 text-sm">
            {{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded-lg mb-4 text-sm">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <form action="{{ url('/admin/notificaciones/enviar') }}" method="POST">
                @csrf

                {{-- Tipo --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de notificación</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach([
                            'venta' => ['🛒', 'Venta'],
                            'compra' => ['💳', 'Compra'],
                            'intercambio' => ['🔄', 'Intercambio'],
                            'producto' => ['📦', 'Producto'],
                            'servicio' => ['🎯', 'Servicio'],
                            'general' => ['📢', 'General'],
                        ] as $key => [$icon, $label])
                        <label class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="tipos[]" value="{{ $key }}" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/30">
                            <span>{{ $icon }}</span>
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Selecciona uno o varios</p>
                </div>

                {{-- Destino --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Enviar a</label>
                    <select name="destino" id="selectDestino" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="usuario">Un usuario específico</option>
                        <option value="todos_vendedores">Todos los vendedores</option>
                        <option value="todos_compradores">Todos los compradores</option>
                        <option value="todos">Todos los usuarios</option>
                    </select>
                </div>

                {{-- Buscar usuario --}}
                <div class="mb-4" id="divUsuario">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar usuario</label>
                    <input type="text" id="inputBuscar" placeholder="Nombre, email o usuario..." autocomplete="off"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <input type="hidden" name="usuario_id" id="hiddenUserId">
                    <div id="divSeleccionado" class="hidden mt-2 flex items-center gap-2 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                        <span id="spanNombre" class="text-sm font-medium"></span>
                        <span id="spanEmail" class="text-xs text-gray-500"></span>
                        <button type="button" id="btnLimpiar" class="ml-auto text-red-500 text-xs font-bold">✕</button>
                    </div>
                    <div id="divResultados" class="hidden mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto"></div>
                </div>

                {{-- Mensaje --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mensaje</label>
                    <textarea name="mensaje" rows="3" required maxlength="500" placeholder="Escribe el mensaje..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none"></textarea>
                </div>

                {{-- Enviar vía (Canales) --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Enviar vía</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="canales[]" value="web" checked class="w-4 h-4 rounded border-gray-300 text-secondary focus:ring-secondary/30">
                            <span class="text-sm text-gray-700">Notificación Web/Móvil</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="canales[]" value="email" checked class="w-4 h-4 rounded border-gray-300 text-secondary focus:ring-secondary/30">
                            <span class="text-sm text-gray-700">Correo Electrónico</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 text-sm font-semibold rounded-lg bg-secondary text-white hover:bg-hoverSecondary flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Enviar notificación
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    var sel = document.getElementById('selectDestino');
    var divU = document.getElementById('divUsuario');
    var inp = document.getElementById('inputBuscar');
    var res = document.getElementById('divResultados');
    var hid = document.getElementById('hiddenUserId');
    var divS = document.getElementById('divSeleccionado');
    var timer;

    sel.addEventListener('change', function(){
        divU.style.display = this.value === 'usuario' ? 'block' : 'none';
        if(this.value !== 'usuario') hid.value = '';
    });

    inp.addEventListener('input', function(){
        clearTimeout(timer);
        var q = this.value.trim();
        if(q.length < 2){ res.classList.add('hidden'); return; }
        timer = setTimeout(function(){
            fetch('/admin/notificaciones/buscar-usuarios?q=' + encodeURIComponent(q))
            .then(function(r){ return r.json(); })
            .then(function(users){
                if(!users.length){
                    res.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">Sin resultados</div>';
                } else {
                    res.innerHTML = '';
                    users.forEach(function(u){
                        var d = document.createElement('div');
                        d.className = 'px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm border-b';
                        d.textContent = u.nombres + ' ' + u.apellidos + ' — ' + u.email;
                        d.addEventListener('click', function(){
                            hid.value = u.id;
                            document.getElementById('spanNombre').textContent = u.nombres + ' ' + u.apellidos;
                            document.getElementById('spanEmail').textContent = u.email;
                            divS.classList.remove('hidden');
                            inp.style.display = 'none';
                            res.classList.add('hidden');
                        });
                        res.appendChild(d);
                    });
                }
                res.classList.remove('hidden');
            });
        }, 300);
    });

    document.getElementById('btnLimpiar').addEventListener('click', function(){
        hid.value = '';
        inp.value = '';
        inp.style.display = '';
        divS.classList.add('hidden');
    });
})();
</script>
@endpush
