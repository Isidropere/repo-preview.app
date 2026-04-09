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
   <title>@yield('title', 'Cambialord - Login')</title>
    <link rel="stylesheet" href="{{ asset('js/hoisted.D4SCdckR.js') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/keen-slider@latest/keen-slider.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/keen-slider@latest/keen-slider.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.0/dist/tailwind.min.css" rel="stylesheet">

    <style>
        .-z-1[data-astro-cid-zp6vblvr] {
            z-index: -1
        }

        .origin-0[data-astro-cid-zp6vblvr] {
            transform-origin: 0%
        }

        input[data-astro-cid-zp6vblvr]:not(:-moz-placeholder-shown)~label[data-astro-cid-zp6vblvr],
        textarea[data-astro-cid-zp6vblvr]:not(:-moz-placeholder-shown)~label[data-astro-cid-zp6vblvr] {
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

        input[data-astro-cid-zp6vblvr]:focus~label[data-astro-cid-zp6vblvr],
        input[data-astro-cid-zp6vblvr]:not(:placeholder-shown)~label[data-astro-cid-zp6vblvr],
        textarea[data-astro-cid-zp6vblvr]:focus~label[data-astro-cid-zp6vblvr],
        textarea[data-astro-cid-zp6vblvr]:not(:placeholder-shown)~label[data-astro-cid-zp6vblvr],
        select[data-astro-cid-zp6vblvr]:focus~label[data-astro-cid-zp6vblvr],
        select[data-astro-cid-zp6vblvr]:not([value=""]):valid~label[data-astro-cid-zp6vblvr] {
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

        input[data-astro-cid-zp6vblvr]:focus~label[data-astro-cid-zp6vblvr],
        select[data-astro-cid-zp6vblvr]:focus~label[data-astro-cid-zp6vblvr] {
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
    <script type="module" src="/_astro/hoisted.Oozc_hRb.js"></script>
</head>
<body class="">
<main class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col items-center justify-center">
            <!-- Logo -->
            <br>
            <div class="mb-8">
                <img src="{{ asset('imgs/logoTypes/logoFooter.png') }}" class="h-24 object-contain" alt="Logo Cambialord">
            </div>

            <!-- Contenedor del formulario -->
            <div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 sm:p-8">
                    <!-- Encabezado -->
                    <div class="text-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-800">Iniciar Sesión</h1>
                        <p class="mt-2 text-gray-600">
                            ¿Aún no tienes una cuenta?
                            <a href="{{ route('registro') }}" class="text-primary hover:underline font-medium"
                                onclick="event.preventDefault(); if(confirm('¿Confirmas que eres mayor de 18 años?')) window.location.href='{{ route('registro') }}';">
                                Regístrate aquí
                            </a>
                        </p>
                    </div>

                    <!-- Botón de Google -->
                    <button type="button" class="w-full py-3 px-4 flex justify-center items-center gap-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 mb-4">
                        <svg class="w-5 h-5" viewBox="0 0 46 47" fill="none">
                            <path d="M46 24.0287C46 22.09 45.8533 20.68 45.5013 19.2112H23.4694V27.9356H36.4069C36.1429 30.1094 34.7347 33.37 31.5957 35.5731L31.5663 35.8669L38.5191 41.2719L38.9885 41.3306C43.4477 37.2181 46 31.1669 46 24.0287Z" fill="#4285F4"/>
                            <path d="M23.4694 47C29.8061 47 35.1161 44.9144 39.0179 41.3012L31.625 35.5437C29.6301 36.9244 26.9898 37.8937 23.4987 37.8937C17.2793 37.8937 12.0281 33.7812 10.1505 28.1412L9.88649 28.1706L2.61097 33.7812L2.52296 34.0456C6.36608 41.7125 14.287 47 23.4694 47Z" fill="#34A853"/>
                            <path d="M10.1212 28.1413C9.62245 26.6725 9.32908 25.1156 9.32908 23.5C9.32908 21.8844 9.62245 20.3275 10.0918 18.8588V18.5356L2.75765 12.8369L2.52296 12.9544C0.909439 16.1269 0 19.7106 0 23.5C0 27.2894 0.909439 30.8731 2.49362 34.0456L10.1212 28.1413Z" fill="#FBBC05"/>
                            <path d="M23.4694 9.07688C27.8699 9.07688 30.8622 10.9863 32.5344 12.5725L39.1645 6.11C35.0867 2.32063 29.8061 0 23.4694 0C14.287 0 6.36607 5.2875 2.49362 12.9544L10.0918 18.8588C11.9987 13.1894 17.25 9.07688 23.4694 9.07688Z" fill="#EB4335"/>
                        </svg>
                        Iniciar Sesión con Google
                    </button>

                    <div class="flex items-center my-4">
                        <div class="flex-grow border-t border-gray-200"></div>
                        <span class="mx-4 text-gray-400 text-sm">O</span>
                        <div class="flex-grow border-t border-gray-200"></div>
                    </div>

                    <!-- Formulario -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        <!-- Grupo de campos mejorados -->
                        <div class="space-y-6">
                            <!-- Campo Email -->
                             <div class="relative" data-astro-cid-zp6vblvr>
                                        <div class="relative z-0 w-full mb-4"> <input type="text" placeholder=""
                                                required name="email"
                                                value="{{ old('email') }}"
                                                class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                data-astro-cid-zp6vblvr> <label for="email"
                                                class="absolute duration-300 top-3 -z-1 origin-0 text-gray-500"
                                                data-astro-cid-zp6vblvr>Correo Electrónico</label> </div>
                                    </div>
                            <!-- Campo Contraseña -->
                               <div class="relative" data-astro-cid-zp6vblvr>
                                    <div class="relative z-0 w-full mb-4"> <input type="password" placeholder="" required
                                            name="password"
                                            class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                            data-astro-cid-zp6vblvr> <label for="password"
                                            class="absolute duration-300 top-3 -z-1 origin-0 text-gray-500"
                                            data-astro-cid-zp6vblvr>Contraseña</label> <span
                                            class="text-sm text-red-600 hidden" id="error"
                                            data-astro-cid-zp6vblvr>Contraseña es requerida</span> </div>
                                </div>

                            <!-- Recordarme y Olvidé contraseña -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input id="remember" name="remember" type="checkbox"
                                        class="w-4 h-4 border-gray-300 rounded text-primary focus:ring-primary">
                                    <label for="remember" class="ml-2 text-sm text-gray-700">
                                        Recordarme
                                    </label>
                                </div>
                                <div class="text-sm">
                                    <a href="{{ route('password.request') }}" class="text-primary hover:underline">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Botón de Inicio de Sesión -->
                        <button type="submit" class="w-full py-3 px-4 bg-secondary text-white font-semibold rounded-lg hover:bg-hoverSecondary transition-colors">
                            Iniciar Sesión
                        </button>

                        <!-- Botón Volver -->
                        <a href="{{ route('home') }}" class="flex items-center justify-center gap-2 text-primary mt-4 w-full">
                            <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                            </svg>
                            <span>Volver</span>
                        </a>
                    </form>

                    <!-- Mensajes de error generales -->
                    @error('credentials')
                        <div class="mt-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <br>
</main>
</body>

</html>
