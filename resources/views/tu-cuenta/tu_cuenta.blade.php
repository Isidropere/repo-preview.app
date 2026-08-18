@extends('layouts.app')

@section('title', 'Cambialord - Tu cuenta')

@section('content')
<main class="min-h-screen">
    <div>
        <section class="section mx-auto lg:max-w-[1250px] md:max-w-[750px] max-w-[325px] py-6">
            @auth
            @include('components.btn-volver', ['backUrl' => route('home')])

            <h1 class="text-4xl text-secondary font-semibold mb-6">
                Hola, {{ ucfirst(Auth::user()->nombres) }} {{ ucfirst(Auth::user()->apellidos) }}
            </h1>

            {{-- Foto de perfil --}}
            <div class="flex items-center gap-6 mb-8 p-4 bg-white border border-gray-200 rounded-xl shadow-sm">
                <div style="position:relative;width:64px;height:64px;flex-shrink:0;">
                    @php
                        $photoPath = Auth::user()->profile_photo_path;
                        $fotoAprobada = (Auth::user()->foto_perfil_estado ?? 'pendiente') === 'aprobado';
                        $defaultAvatar = asset('imgs/defaults/profile_default.svg');
                        $photoUrl = ($photoPath && $fotoAprobada) ? asset($photoPath) : $defaultAvatar;
                    @endphp
                    <img id="preview-foto"
                         src="{{ $photoUrl }}"
                         alt="Foto de perfil"
                         onerror="this.onerror=null;this.src='{{ $defaultAvatar }}'"
                         style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid #f58634;display:block;">

                    {{-- Botón animado encima de la foto --}}
                    <label for="foto-input" id="foto-label"
                           style="position:absolute;inset:0;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0);transition:background 0.3s;cursor:pointer;">
                        <svg id="camara-icon" style="width:22px;height:22px;color:white;opacity:0;transition:opacity 0.3s;stroke:white;"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </label>
                </div>

                <div class="flex-1">
                    <p class="font-semibold text-gray-800 text-lg">{{ Auth::user()->nombres }} {{ Auth::user()->apellidos }}</p>
                    <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>

                    {{-- Formulario de subida --}}
                    <form id="form-foto" action="{{ route('update-profile') }}" method="POST" enctype="multipart/form-data" class="mt-2">
                        @csrf
                        @method('PUT')
                        <input type="file" id="foto-input" name="profile_photo" accept="image/*" class="hidden">
                        <button type="submit" id="btn-guardar-foto"
                                class="hidden mt-2 px-4 py-1.5 text-sm bg-primary text-white rounded-lg hover:bg-orange-500 transition-all duration-300 animate-pulse">
                            Guardar foto
                        </button>
                    </form>
                </div>
            </div>

            {{-- Grid de opciones --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 my-4">

                <a href="{{ route('items.talento_create') }}"
                   class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                    <img src="/imgs/icons/agregartalentos.svg" alt="Agregar talento" loading="lazy" width="48" height="48">
                    <div>
                        <h2 class="text-xl font-medium">Agregar Talento o Servicio</h2>
                        <p class="text-sm text-gray-500">Publica y ofrece tus habilidades</p>
                    </div>
                </a>

                <a href="{{ route('hoja-vida.form') }}"
                   class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                    <svg class="w-12 h-12 text-secondary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <div>
                        <h2 class="text-xl font-medium">Mi Hoja de Vida (CV)</h2>
                        <p class="text-sm text-gray-500">{{ auth()->user()->hojaVida ? 'Edita tu perfil profesional y laboral' : 'Completa tu perfil profesional y laboral' }}</p>
                    </div>
                </a>

                <a href="{{ route('items.admintalento') }}"
                   class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                    <img src="/imgs/icons/talentos.svg" alt="Talentos" loading="lazy" width="48" height="48">
                    <div>
                        <h2 class="text-xl font-medium">Administrar Talentos</h2>
                        <p class="text-sm text-gray-500">Edita o actualiza tus servicios ofrecidos</p>
                    </div>
                </a>

                <a href="{{ route('solicitudes.index') }}"
                   class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                    <svg class="w-12 h-12 text-secondary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <div>
                        <h2 class="text-xl font-medium">Mis Ventas de Servicios</h2>
                        <p class="text-sm text-gray-500">Gestiona tus solicitudes de servicio recibidas</p>
                    </div>
                </a>

                <a href="{{ route('items.create') }}"
                   class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                    <img src="/imgs/icons/addProduct.svg" alt="Agregar producto" loading="lazy" width="48" height="48">
                    <div>
                        <h2 class="text-xl font-medium">Agregar Producto</h2>
                        <p class="text-sm text-gray-500">Publica un artículo para venta o intercambio</p>
                    </div>
                </a>

                <a href="{{ route('items.user') }}"
                   class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                    <img src="/imgs/icons/editProduct.svg" alt="Gestionar productos" loading="lazy" width="48" height="48">
                    <div>
                        <h2 class="text-xl font-medium">Administrar Productos</h2>
                        <p class="text-sm text-gray-500">Edita, pausa o elimina tus artículos</p>
                    </div>
                </a>

                <a href="{{ route('direcciones.index') }}"
                   class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                    <img src="/imgs/icons/EditLocation.svg" alt="Direcciones" loading="lazy" width="48" height="48">
                    <div>
                        <h2 class="text-xl font-medium">Mis Direcciones</h2>
                        <p class="text-sm text-gray-500">Administra tus direcciones de envío</p>
                    </div>
                </a>

                <a href="{{ route('contraseña') }}"
                   class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                    <img src="/imgs/icons/ShieldPlus.svg" alt="Seguridad" loading="lazy" width="48" height="48">
                    <div>
                        <h2 class="text-xl font-medium">Seguridad y Contraseña</h2>
                        <p class="text-sm text-gray-500">Cambia tu contraseña de manera segura</p>
                    </div>
                </a>

                <!-- Enlace a Mi Billetera -->
                <a href="{{ route('billetera.index') }}"
                   class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                    <img src="/imgs/icons/premium.svg" alt="Mi Billetera" loading="lazy" width="48" height="48">
                    <div>
                        <h2 class="text-xl font-medium">Mi Billetera</h2>
                        <p class="text-sm text-gray-500">Gestiona tus ganancias y retiros</p>
                    </div>
                </a>

                <a href="{{ route('historial') }}"
                   style="grid-column: span 1 / span 1;"
                   class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                    <img src="/imgs/icons/History.svg" alt="Historial" loading="lazy" width="48" height="48">
                    <div>
                        <h2 class="text-xl font-medium">Historial General</h2>
                        <p class="text-sm text-gray-500">Revisa tus compras, ventas e intercambios</p>
                    </div>
                </a>

                {{-- Opción premium inhabilitada temporalmente por solicitud del usuario --}}
                {{-- <a href="{{ route('usuario.tipo.edit') }}"
                   class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                    <img src="/imgs/icons/premium.svg" alt="Premium" loading="lazy" width="48" height="48">
                    <div>
                        <h2 class="text-xl font-medium">Cambiar cuenta a premium</h2>
                        <p class="text-sm text-gray-500">Descubre los beneficios premium</p>
                    </div>
                </a> --}}

            </div>
            @else
                <a href="{{ route('login') }}">Iniciar sesion</a>
            @endauth
        </section>
    </div>
</main>

@push('scripts')
<script>
    const fotoLabel = document.getElementById('foto-label');
    const camaraIcon = document.getElementById('camara-icon');

    fotoLabel.addEventListener('mouseover', function() {
        this.style.background = 'rgba(0,0,0,0.45)';
        camaraIcon.style.opacity = '1';
    });
    fotoLabel.addEventListener('mouseout', function() {
        this.style.background = 'rgba(0,0,0,0)';
        camaraIcon.style.opacity = '0';
    });

    document.getElementById('foto-input').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('preview-foto').src = e.target.result;
        };
        reader.readAsDataURL(file);
        document.getElementById('btn-guardar-foto').classList.remove('hidden');
    });
</script>
@endpush

@endsection
