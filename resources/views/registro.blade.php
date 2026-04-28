@extends('layouts.app')

@section('title', 'Registrarse - Cambialord')

@section('content')
<div class="min-h-screen bg-gray-50 py-5">
    <div class="max-w-xl mx-auto px-4">

        {{-- Header --}}
        <div class="text-center mb-5">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 mb-3">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Crear cuenta</h1>
            <p class="text-gray-500 mt-1">
                ¿Ya tienes una cuenta?
                <a href="{{ route('login') }}" class="text-primary hover:underline font-medium">Iniciar Sesión</a>
            </p>
        </div>

        {{-- Google --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4 text-center">
            <a href="{{ route('social.login', 'google') }}"
               class="w-full py-2.5 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50">
                <svg class="w-4 h-4" viewBox="0 0 46 47" fill="none">
                    <path d="M46 24.0287C46 22.09 45.8533 20.68 45.5013 19.2112H23.4694V27.9356H36.4069C36.1429 30.1094 34.7347 33.37 31.5957 35.5731L31.5663 35.8669L38.5191 41.2719L38.9885 41.3306C43.4477 37.2181 46 31.1669 46 24.0287Z" fill="#4285F4"/>
                    <path d="M23.4694 47C29.8061 47 35.1161 44.9144 39.0179 41.3012L31.625 35.5437C29.6301 36.9244 26.9898 37.8937 23.4987 37.8937C17.2793 37.8937 12.0281 33.7812 10.1505 28.1412L9.88649 28.1706L2.61097 33.7812L2.52296 34.0456C6.36608 41.7125 14.287 47 23.4694 47Z" fill="#34A853"/>
                    <path d="M10.1212 28.1413C9.62245 26.6725 9.32908 25.1156 9.32908 23.5C9.32908 21.8844 9.62245 20.3275 10.0918 18.8588V18.5356L2.75765 12.8369L2.52296 12.9544C0.909439 16.1269 0 19.7106 0 23.5C0 27.2894 0.909439 30.8731 2.49362 34.0456L10.1212 28.1413Z" fill="#FBBC05"/>
                    <path d="M23.4694 9.07688C27.8699 9.07688 30.8622 10.9863 32.5344 12.5725L39.1645 6.11C35.0867 2.32063 29.8061 0 23.4694 0C14.287 0 6.36607 5.2875 2.49362 12.9544L10.0918 18.8588C11.9987 13.1894 17.25 9.07688 23.4694 9.07688Z" fill="#EB4335"/>
                </svg>
                Regístrate con Google
            </a>
            <div class="flex items-center my-3">
                <div class="flex-grow border-t border-gray-200"></div>
                <span class="mx-3 text-xs text-gray-400">completa el formulario</span>
                <div class="flex-grow border-t border-gray-200"></div>
            </div>
        </div>

        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-4">
            <p class="font-semibold">{{ session('error') }}</p>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-4">
            <div class="flex items-center mb-1">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span class="font-semibold">Corrige los siguientes errores:</span>
            </div>
            <ul class="list-disc list-inside text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <form id="registroForm" action="{{ route('registro.usuario') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Datos personales --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">1</span>
                    Datos personales
                </h2>
                <div class="space-y-2.5">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="nombres" class="block text-xs font-medium text-gray-700 mb-0.5">Nombres <span class="text-red-500">*</span></label>
                            <input type="text" id="nombres" name="nombres" required value="{{ old('nombres') }}" placeholder="Ej: Juan Carlos"
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                            @error('nombres')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label for="apellidos" class="block text-xs font-medium text-gray-700 mb-0.5">Apellidos <span class="text-red-500">*</span></label>
                            <input type="text" id="apellidos" name="apellidos" required value="{{ old('apellidos') }}" placeholder="Ej: Pérez García"
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                            @error('apellidos')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div>
                        <label for="nombre_usuario" class="block text-xs font-medium text-gray-700 mb-0.5">Nombre de usuario</label>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" readonly
                               class="w-full px-3 py-1.5 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500">
                    </div>
                    <div>
                        <label for="email" class="block text-xs font-medium text-gray-700 mb-0.5">Correo electrónico <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" required value="{{ old('email') }}" placeholder="correo@ejemplo.com"
                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                        @error('email')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="telefono" class="block text-xs font-medium text-gray-700 mb-0.5">Teléfono <span class="text-red-500">*</span></label>
                        <input type="tel" id="telefono" name="telefono" required value="{{ old('telefono') }}" placeholder="809-000-0000"
                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                        @error('telefono')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            {{-- Cuenta y seguridad --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">2</span>
                    Cuenta y seguridad
                </h2>
                <div class="space-y-2.5">
                    <div>
                        <label for="tipos_usuario_id" class="block text-xs font-medium text-gray-700 mb-0.5">Tipo de transacción <span class="text-red-500">*</span></label>
                        <select name="tipos_usuario_id" id="tipos_usuario_id" required
                                class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors bg-white">
                            <option value="" disabled selected>-- Selecciona --</option>
                            @foreach ($tipos_usuarios as $tipo)
                                <option value="{{ $tipo->id_tipo_usuario }}" {{ old('tipos_usuario_id') == $tipo->id_tipo_usuario ? 'selected' : '' }}>{{ $tipo->tipo }}</option>
                            @endforeach
                        </select>
                        @error('tipos_usuario_id')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="password" class="block text-xs font-medium text-gray-700 mb-0.5">Contraseña <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required placeholder="Mínimo 8 caracteres"
                                       class="w-full px-3 py-1.5 pr-9 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                                <button type="button" onclick="togglePwd('password')" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" tabindex="-1">
                                    <svg class="w-4 h-4 eye-closed" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M3 3l18 18"/></svg>
                                    <svg class="w-4 h-4 eye-open hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                            @error('password')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs font-medium text-gray-700 mb-0.5">Confirmar <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Repetir contraseña"
                                       class="w-full px-3 py-1.5 pr-9 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                                <button type="button" onclick="togglePwd('password_confirmation')" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" tabindex="-1">
                                    <svg class="w-4 h-4 eye-closed" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M3 3l18 18"/></svg>
                                    <svg class="w-4 h-4 eye-open hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Foto de perfil --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">3</span>
                    Foto de perfil <span class="text-xs text-gray-400 font-normal">(opcional)</span>
                </h2>
                <div>
                    <div id="fotoPerfilContenedor" class="relative w-full rounded-xl overflow-hidden border-2 border-dashed border-gray-300 bg-gray-50 hover:border-primary/50 hover:bg-primary/5 transition-all cursor-pointer" style="min-height:120px;" onclick="document.getElementById('profile_photo').click()">
                        <div id="fotoPerfilPlaceholder" class="flex flex-col items-center justify-center py-6 text-center pointer-events-none">
                            <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <p class="text-xs text-gray-500">Haz clic para subir tu foto</p>
                            <p class="text-xs text-gray-400">JPG, PNG o WEBP (máx. 2MB)</p>
                        </div>
                        <img id="fotoPerfilPreview" class="hidden w-full rounded-xl object-cover" style="max-height:220px;" alt="Vista previa"/>
                        <div id="fotoPerfilActions" class="hidden flex items-center justify-between px-3 py-2 bg-white/90 border-t border-gray-200">
                            <span id="fotoPerfilFilename" class="text-xs text-gray-600 truncate max-w-[70%]"></span>
                            <button type="button" id="btnRemoveFotoPerfil" class="text-red-500 hover:text-red-700 text-xs font-semibold flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Eliminar
                            </button>
                        </div>
                    </div>
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/webp" class="hidden">
                </div>
            </div>

            {{-- Términos y botón --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <div class="flex items-start mb-4">
                    <input id="terms" name="terms" type="checkbox" required
                           class="shrink-0 mt-0.5 border-gray-200 rounded text-primary focus:ring-primary">
                    <label for="terms" class="ms-3 text-sm text-gray-600">
                        Acepto los <a href="#" class="text-primary hover:underline font-medium">Términos y Condiciones</a>
                    </label>
                </div>
                <button type="submit" id="submitButton"
                        onclick="this.disabled=true;this.textContent='Creando cuenta...';this.form.submit();"
                        class="w-full py-2.5 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-secondary text-white hover:bg-hoverSecondary transition-colors">
                    Crear mi cuenta
                </button>
            </div>
        </form>

        <div class="text-center mt-4 mb-8">
            <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-primary inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al inicio
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("input", function () {
    let nombre   = document.querySelector('input[name="nombres"]')?.value || '';
    let apellido = document.querySelector('input[name="apellidos"]')?.value || '';
    let campo = document.getElementById("nombre_usuario");
    if (campo) campo.value = (nombre + apellido).replace(/\s+/g, '').toLowerCase();
});

function togglePwd(id) {
    const input = document.getElementById(id);
    const btn = input.parentElement.querySelector('button');
    const closed = btn.querySelector('.eye-closed');
    const open = btn.querySelector('.eye-open');
    if (input.type === 'password') {
        input.type = 'text';
        closed.classList.add('hidden');
        open.classList.remove('hidden');
    } else {
        input.type = 'password';
        closed.classList.remove('hidden');
        open.classList.add('hidden');
    }
}

// Foto de perfil preview
(function() {
    var inp = document.getElementById('profile_photo');
    var placeholder = document.getElementById('fotoPerfilPlaceholder');
    var preview = document.getElementById('fotoPerfilPreview');
    var actions = document.getElementById('fotoPerfilActions');
    var fname = document.getElementById('fotoPerfilFilename');
    var contenedor = document.getElementById('fotoPerfilContenedor');

    if (!inp) return;

    inp.addEventListener('change', function() {
        var file = this.files[0];
        if (!file) return;
        var valid = ['image/jpeg','image/png','image/webp'];
        if (!valid.includes(file.type)) {
            alert('Solo JPG, PNG o WEBP.');
            this.value = '';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('La imagen no debe exceder 2MB.');
            this.value = '';
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            placeholder.classList.add('hidden');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            fname.textContent = file.name;
            actions.classList.remove('hidden');
            contenedor.style.cursor = 'default';
            contenedor.onclick = null;
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('btnRemoveFotoPerfil').addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        inp.value = '';
        preview.src = '';
        preview.classList.add('hidden');
        actions.classList.add('hidden');
        placeholder.classList.remove('hidden');
        contenedor.style.cursor = 'pointer';
        contenedor.onclick = function() { inp.click(); };
    });
})();
</script>
@endpush
