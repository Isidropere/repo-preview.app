<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Cambialord - Tienda Online">
    <meta name="viewport" content="width=device-width">
    <link rel="icon" type="image/svg+xml" href="{{ asset('imgs/logoTypes/logoFooter.png') }}">
    <link rel="stylesheet" href="{{ asset('css/_astro/index.D-AOIgCY.css') }}">
       <link rel="stylesheet" href="{{ asset('css/_astro/index.BneVErea.css') }}">
   <title>@yield('title', 'Cambialord - Registrarse')</title>
    <link rel="stylesheet" href="{{ asset('js/hoisted.D4SCdckR.js') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/keen-slider@latest/keen-slider.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/keen-slider@latest/keen-slider.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.0/dist/tailwind.min.css" rel="stylesheet">

    <style>
        .-z-1[data-astro-cid-phpud7wc] {
            z-index: -1
        }

        .origin-0[data-astro-cid-phpud7wc] {
            transform-origin: 0%
        }

        input[data-astro-cid-phpud7wc]:not(:-moz-placeholder-shown)~label[data-astro-cid-phpud7wc],
        textarea[data-astro-cid-phpud7wc]:not(:-moz-placeholder-shown)~label[data-astro-cid-phpud7wc] {
            --tw-translate-x: 0;
            --tw-translate-y: 0;
            --tw-rotate: 0;
            --tw-skew-x: 0;
            --tw-skew-y: 0;
            transform: translate(var(--tw-translate-x)) translateY(var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
            --tw-scale-x: .75;
            --tw-scale-y: .75;
            --tw-translate-y: -1.5rem
        }

        input[data-astro-cid-phpud7wc]:focus~label[data-astro-cid-phpud7wc],
        input[data-astro-cid-phpud7wc]:not(:placeholder-shown)~label[data-astro-cid-phpud7wc],
        textarea[data-astro-cid-phpud7wc]:focus~label[data-astro-cid-phpud7wc],
        textarea[data-astro-cid-phpud7wc]:not(:placeholder-shown)~label[data-astro-cid-phpud7wc],
        select[data-astro-cid-phpud7wc]:focus~label[data-astro-cid-phpud7wc],
        select[data-astro-cid-phpud7wc]:not([value=""]):valid~label[data-astro-cid-phpud7wc] {
            --tw-translate-x: 0;
            --tw-translate-y: 0;
            --tw-rotate: 0;
            --tw-skew-x: 0;
            --tw-skew-y: 0;
            transform: translate(var(--tw-translate-x)) translateY(var(--tw-translate-y)) rotate(var(--tw-rotate)) skew(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
            --tw-scale-x: .75;
            --tw-scale-y: .75;
            --tw-translate-y: -1.5rem
        }

        input[data-astro-cid-phpud7wc]:focus~label[data-astro-cid-phpud7wc],
        select[data-astro-cid-phpud7wc]:focus~label[data-astro-cid-phpud7wc] {
            --tw-text-opacity: 1;
            color: #479bd5;
            left: 0
        }

        .astro-route-announcer {
            position: absolute;
            left: 0;
            top: 0;
            clip: rect(0 0 0 0);
            clip-path: inset(50%);
            overflow: hidden;
            white-space: nowrap;
            width: 1px;
            height: 1px
        }
    </style>
    <!--<script
         type="module" src="{{ asset('js/hoisted.Oozc_hRb.js') }}">
    </script>-->
</head>
<body class="">
<main class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col items-center justify-center">
           <br>
            <!-- Logo -->
            <div class="mb-8">
                <img src="{{ asset('imgs/logoTypes/logoFooter.png') }}" class="h-24 object-contain" alt="Logo Cambialord" width="200" height="96">
            </div>

            <!-- Contenedor del formulario -->
            <div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 sm:p-8">
                    <!-- Encabezado -->
                    <div class="text-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-800">Registrarse</h1>
                        <p class="mt-2 text-gray-600">
                            ¿Ya tienes una cuenta?
                            <a href="{{ route('login') }}" class="text-primary hover:underline font-medium">
                                Iniciar Sesión
                            </a>
                        </p>
                    </div>

                    <!-- Botón de Google -->
                    <a href="{{ route('social.login', 'google') }}" class="w-full py-3 px-4 flex justify-center items-center gap-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 mb-4">
                        <svg class="w-5 h-5" viewBox="0 0 46 47" fill="none">
                                <path d="M46 24.0287C46 22.09 45.8533 20.68 45.5013 19.2112H23.4694V27.9356H36.4069C36.1429 30.1094 34.7347 33.37 31.5957 35.5731L31.5663 35.8669L38.5191 41.2719L38.9885 41.3306C43.4477 37.2181 46 31.1669 46 24.0287Z" fill="#4285F4"/>
                                <path d="M23.4694 47C29.8061 47 35.1161 44.9144 39.0179 41.3012L31.625 35.5437C29.6301 36.9244 26.9898 37.8937 23.4987 37.8937C17.2793 37.8937 12.0281 33.7812 10.1505 28.1412L9.88649 28.1706L2.61097 33.7812L2.52296 34.0456C6.36608 41.7125 14.287 47 23.4694 47Z" fill="#34A853"/>
                                <path d="M10.1212 28.1413C9.62245 26.6725 9.32908 25.1156 9.32908 23.5C9.32908 21.8844 9.62245 20.3275 10.0918 18.8588V18.5356L2.75765 12.8369L2.52296 12.9544C0.909439 16.1269 0 19.7106 0 23.5C0 27.2894 0.909439 30.8731 2.49362 34.0456L10.1212 28.1413Z" fill="#FBBC05"/>
                                <path d="M23.4694 9.07688C27.8699 9.07688 30.8622 10.9863 32.5344 12.5725L39.1645 6.11C35.0867 2.32063 29.8061 0 23.4694 0C14.287 0 6.36607 5.2875 2.49362 12.9544L10.0918 18.8588C11.9987 13.1894 17.25 9.07688 23.4694 9.07688Z" fill="#EB4335"/>
                        </svg>
                        Regístrate con Google
                    </a>

                    <div class="flex items-center my-4">
                        <div class="flex-grow border-t border-gray-200"></div>
                        <span class="mx-4 text-gray-400 text-sm">O</span>
                        <div class="flex-grow border-t border-gray-200"></div>
                    </div>
                    <form id="registroForm" action="{{ route('registro.post') }}" method="POST" enctype="multipart/form-data" data-astro-cid-phpud7wc>
                                @csrf <!-- Token de seguridad para Laravel -->
                                <div class="grid gap-y-4" data-astro-cid-phpud7wc>
                                    <!-- Campo nombres -->
                                    <div data-astro-cid-phpud7wc>
                                        <div class="relative" data-astro-cid-phpud7wc>
                                            <div class="relative z-0 w-full mb-4">
                                                <input type="text" placeholder="" required name="nombres"
                                                    class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                    data-astro-cid-phpud7wc>
                                                <label for="nombres"
                                                    class="absolute duration-300 top-3 -z-1 origin-0 text-gray-500"
                                                    data-astro-cid-phpud7wc>Nombres</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campo apellidos -->
                                    <div data-astro-cid-phpud7wc>
                                        <div class="relative" data-astro-cid-phpud7wc>
                                            <div class="relative z-0 w-full mb-4">
                                                <input type="text" placeholder="" required name="apellidos"
                                                    class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                    data-astro-cid-phpud7wc>
                                                <label for="apellidos"
                                                    class="absolute duration-300 top-3 -z-1 origin-0 text-gray-500"
                                                    data-astro-cid-phpud7wc>Apellidos</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campo telefono -->
                                    <div data-astro-cid-phpud7wc>
                                        <div class="relative" data-astro-cid-phpud7wc>
                                            <div class="relative z-0 w-full mb-4">
                                                <input type="tel" placeholder="" required name="telefono"
                                                    class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                    data-astro-cid-phpud7wc>
                                                <label for="telefono"
                                                    class="absolute duration-300 top-3 -z-1 origin-0 text-gray-500"
                                                    data-astro-cid-phpud7wc>Telefono</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campo email -->
                                    <div data-astro-cid-phpud7wc>
                                        <div class="relative" data-astro-cid-phpud7wc>
                                            <div class="relative z-0 w-full mb-4">
                                                <input type="email" placeholder="" required name="email"
                                                    class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                    data-astro-cid-phpud7wc>
                                                <label for="email"
                                                    class="absolute duration-300 top-3 -z-1 origin-0 text-gray-500"
                                                    data-astro-cid-phpud7wc>Correo Electrónico</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campo nombre_usuario -->
                                    <div data-astro-cid-phpud7wc>
                                        <div class="relative" data-astro-cid-phpud7wc>
                                            <div class="relative z-0 w-full mb-4">
                                                <input type="text" placeholder="" required name="nombre_usuario"
                                                    class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                    data-astro-cid-phpud7wc>
                                                <label for="nombre_usuario"
                                                    class="absolute duration-300 top-3 -z-1 origin-0 text-gray-500"
                                                    data-astro-cid-phpud7wc>Nombre Usuario</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campo clave -->
                                    <div data-astro-cid-phpud7wc>
                                        <div class="relative" data-astro-cid-phpud7wc>
                                            <div class="relative z-0 w-full mb-4">
                                                <input type="password" placeholder="" required name="password"
                                                    class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                    data-astro-cid-phpud7wc>
                                                <label for="clave"
                                                    class="absolute duration-300 top-3 -z-1 origin-0 text-gray-500"
                                                    data-astro-cid-phpud7wc>Contraseña</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Confirmar Contraseña -->
                                    <div data-astro-cid-phpud7wc>
                                        <div class="relative" data-astro-cid-phpud7wc>
                                            <div class="relative z-0 w-full mb-4">
                                                <input type="password" placeholder="" required name="password_confirmation"
                                                    class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                    data-astro-cid-phpud7wc>
                                                <label for="password_confirmation"
                                                    class="absolute duration-300 top-3 -z-1 origin-0 text-gray-500"
                                                    data-astro-cid-phpud7wc>Confirmar Contraseña</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campo id_tipo_usuario -->
                                    <div data-astro-cid-phpud7wc>
                                        <div class="relative" data-astro-cid-phpud7wc>
                                            <div class="relative z-0 w-full mb-4">
                                                <label for="tipo_usuario"
                                                    class="block text-sm font-medium text-gray-500 mb-1"
                                                    data-astro-cid-phpud7wc>Opcion de transaccion</label>
                                                <select name="id_tipo_usuario" id="tipo_usuario" required
                                                    class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                    data-astro-cid-phpud7wc>
                                                    <option value="" disabled selected>-- Selecciona una opción --</option>
                                                    @foreach ($tipos_usuario as $tipoUsuario)
                                                        <option value="{{ $tipoUsuario->id_tipo_usuario }}">{{ $tipoUsuario->tipo }}</option>
                                                    @endforeach
                                                </select>
                    
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campo foto_perfil -->
                                    <div data-astro-cid-phpud7wc>
                                        <div>
                                            <label for="foto_perfil" class="block text-sm font-medium text-gray-500 mb-1"
                                                data-astro-cid-phpud7wc>Foto de perfil</label>
                                            <div class="relative w-24 h-24 mx-auto mb-3 rounded-full overflow-hidden border-2 border-dashed border-gray-300 bg-gray-50 cursor-pointer hover:border-primary/50 transition-colors" onclick="document.getElementById('foto_perfil').click()">
                                                <img id="foto_perfil_preview" src="{{ asset('imgs/defaults/profile_default.svg') }}" class="w-full h-full object-cover" alt="Vista previa"/>
                                                <div id="foto_perfil_overlay" class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                </div>
                                            </div>
                                            <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*"
                                                class="hidden"
                                                data-astro-cid-phpud7wc>
                                            <p class="text-center text-xs text-gray-400" data-astro-cid-phpud7wc>Haz clic para subir (opcional)</p>
                                        </div>    
                                    </div>

                                    <!-- Checkbox para Términos y Condiciones -->
                                    <div class="flex items-center" data-astro-cid-phpud7wc>
                                        <div class="flex" data-astro-cid-phpud7wc>
                                            <input id="terms" name="terms" type="checkbox" required
                                                class="shrink-0 mt-0.5 border-gray-200 rounded text-primary focus:ring-primary"
                                                data-astro-cid-phpud7wc>
                                        </div>
                                        <div class="ms-3" data-astro-cid-phpud7wc>
                                            <label for="terms" class="text-sm" data-astro-cid-phpud7wc>
                                                Acepto los <a href="#" class="text-primary decoration-2 hover:underline font-medium" data-astro-cid-phpud7wc>Términos y Condiciones</a>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Botón de Registro -->
                                    <button  id="submitButton"  type="submit"
                                        onclick="this.disabled=true;this.textContent='Registrando...';this.form.submit();"
                                        class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-secondary text-white hover:bg-hoverSecondary disabled:opacity-50 disabled:pointer-events-none"
                                        data-astro-cid-phpud7wc>Registrarse</button>

                                              <a href="{{ route('home') }}" class="flex items-center my-4 gap-x-2 cursor-pointer text-blue-500">
                                            <svg class="h-4 w-4 fill-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                                            </svg>
                                            <span>Volver al inicio</span>
                                        </a>
                            
                                </div>
                            </form>
                   




                    <!-- Mensajes de error -->
                    @if($errors->any())
                        <div class="mt-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <br>
</main>

</body>

</html>

<!--@push('scripts')-->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registroForm');
    const inputs = form.querySelectorAll('input, select');

    const fileInput = form.querySelector('input[name="foto_perfil"]');
    const preview = document.getElementById('foto_perfil_preview');
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Por favor, sube una imagen válida (JPEG, PNG, JPG, GIF o WebP).');
                this.value = '';
                preview.src = '{{ asset("imgs/defaults/profile_default.svg") }}';
                return;
            }
            
            if (file.size > 2 * 1024 * 1024) {
                alert('La imagen no debe exceder los 2MB.');
                this.value = '';
                preview.src = '{{ asset("imgs/defaults/profile_default.svg") }}';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
    // Mejorar la interacción de los labels
    inputs.forEach(input => {
        // Saltar file inputs y checkboxes — no tienen label flotante
        if (input.type === 'file' || input.type === 'checkbox') return;

        // Ocultar label cuando el campo tiene valor
        input.addEventListener('input', function() {
            const label = this.parentElement.querySelector('label');
            if (!label) return;
            if (this.value) {
                label.classList.add('hidden');
            } else {
                label.classList.remove('hidden');
            }
        });
        
        // Mover label al hacer focus
        input.addEventListener('focus', function() {
            const label = this.parentElement.querySelector('label');
            if (!label) return;
            label.classList.add('text-primary', '-translate-y-6', 'scale-75');
            label.classList.remove('text-gray-500');
        });
        
        // Restaurar label al perder focus
        input.addEventListener('blur', function() {
            const label = this.parentElement.querySelector('label');
            if (!label) return;
            if (!this.value) {
                label.classList.remove('text-primary', '-translate-y-6', 'scale-75');
                label.classList.add('text-gray-500');
            }
        });
    });

    // Validación de contraseñas
    form.addEventListener('submit', function(event) {
        const password = form.querySelector('input[name="password"]').value;
        const confirmPassword = form.querySelector('input[name="password_confirmation"]').value;

        if (password !== confirmPassword) {
            event.preventDefault();
            alert('Las contraseñas no coinciden. Por favor, verifica.');
        }
    });
});

</script>

<!--@endpush-->
