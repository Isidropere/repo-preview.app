@extends('layouts.app')

@section('title', 'Envio-password - Cambialord')

@section('content')
    <main>
        <div class="flex flex-col items-center justify-center " data-astro-cid-phpud7wc>
            <div class="my-8" data-astro-cid-phpud7wc> <img src="{{ asset('imgs/logoTypes/logoFooter.png') }}" class="object-cover h-28"
                    alt="" data-astro-cid-phpud7wc> </div>
            <div class="mt-0 bg-white border border-gray-200 rounded-xl shadow-sm max-w-md w-full mx-4"
                data-astro-cid-phpud7wc>
          
                <div class="p-4 sm:p-7" data-astro-cid-phpud7wc>
                    <div class="text-center" data-astro-cid-phpud7wc>
                        <h1 class="block text-2xl font-bold text-gray-800" data-astro-cid-phpud7wc>Registrarse</h1>
                        <p class="mt-2 text-sm text-gray-600" data-astro-cid-phpud7wc>
                            ¿Ya tienes una cuenta?
                            <a class="text-primary decoration-2 hover:underline font-medium" href="{{ route('login') }}"
                                data-astro-cid-phpud7wc>
                                Iniciar Sesión
                            </a>
                        </p>
                    </div>
                    <div class="mt-5" data-astro-cid-phpud7wc> <a href="{{ route('social.login', 'google') }}"
                            class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none"
                            data-astro-cid-phpud7wc> <svg class="w-4 h-auto" width="46" height="47" viewBox="0 0 46 47"
                                fill="none" data-astro-cid-phpud7wc>
                                <path
                                    d="M46 24.0287C46 22.09 45.8533 20.68 45.5013 19.2112H23.4694V27.9356H36.4069C36.1429 30.1094 34.7347 33.37 31.5957 35.5731L31.5663 35.8669L38.5191 41.2719L38.9885 41.3306C43.4477 37.2181 46 31.1669 46 24.0287Z"
                                    fill="#4285F4" data-astro-cid-phpud7wc></path>
                                <path
                                    d="M23.4694 47C29.8061 47 35.1161 44.9144 39.0179 41.3012L31.625 35.5437C29.6301 36.9244 26.9898 37.8937 23.4987 37.8937C17.2793 37.8937 12.0281 33.7812 10.1505 28.1412L9.88649 28.1706L2.61097 33.7812L2.52296 34.0456C6.36608 41.7125 14.287 47 23.4694 47Z"
                                    fill="#34A853" data-astro-cid-phpud7wc></path>
                                <path
                                    d="M10.1212 28.1413C9.62245 26.6725 9.32908 25.1156 9.32908 23.5C9.32908 21.8844 9.62245 20.3275 10.0918 18.8588V18.5356L2.75765 12.8369L2.52296 12.9544C0.909439 16.1269 0 19.7106 0 23.5C0 27.2894 0.909439 30.8731 2.49362 34.0456L10.1212 28.1413Z"
                                    fill="#FBBC05" data-astro-cid-phpud7wc></path>
                                <path
                                    d="M23.4694 9.07688C27.8699 9.07688 30.8622 10.9863 32.5344 12.5725L39.1645 6.11C35.0867 2.32063 29.8061 0 23.4694 0C14.287 0 6.36607 5.2875 2.49362 12.9544L10.0918 18.8588C11.9987 13.1894 17.25 9.07688 23.4694 9.07688Z"
                                    fill="#EB4335" data-astro-cid-phpud7wc></path>
                            </svg>
                            Regístrate con Google
                        </a>
                        <div class="py-3 flex items-center text-xs text-gray-400 uppercase before:flex-1 before:border-t before:border-gray-200 before:me-6 after:flex-1 after:border-t after:border-gray-200 after:ms-6"
                            data-astro-cid-phpud7wc>O</div>
                            <!-- Form --> 
                            <!-- Formulario de registro -->
                        <form id="registroForm" action="{{ route('registro.usuario') }}" method="POST" enctype="multipart/form-data" data-astro-cid-phpud7wc>
                        @csrf
                        {{-- Errores de validación --}}
                        @if ($errors->any())
                            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <ul class="text-sm text-red-600 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-600">
                                {{ session('success') }}
                            </div>
                        @endif
                        <div class="alert alert-danger">
                        </div>
                        <div class="grid gap-y-4" data-astro-cid-phpud7wc>
                            <!-- Campo Nombres -->
                            <div data-astro-cid-phpud7wc>
                                <div class="relative" data-astro-cid-phpud7wc>
                                    <div class="relative z-0 w-full mb-4">
                                        <input type="text" placeholder="Nombres" required name="nombres" value="{{ old('nombres') }}"
                                                class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                    data-astro-cid-phpud7wc>
                                    </div>
                                </div>
                            </div>

                            <!-- Campo Apellido Paterno -->
                            <div data-astro-cid-phpud7wc>
                                <div class="relative" data-astro-cid-phpud7wc>
                                    <div class="relative z-0 w-full mb-4">
                                        <input type="text" placeholder="Apellidos" required name="apellidos" value="{{ old('apellidos') }}"
                                                class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                data-astro-cid-phpud7wc>
                                   
                                    </div>
                                </div>
                            </div>

                            <div>
                                <input type="text" id="nombre_usuario" name="nombre_usuario" placeholder="Nombre de usuario"
                                       readonly class="relative pt-3 pb-2 block w-full border-b-2 border-gray-300">
                            </div>
                            <!-- Campo Correo Electrónico -->
                            <div data-astro-cid-phpud7wc>
                                <div class="relative" data-astro-cid-phpud7wc>
                                    <div class="relative z-0 w-full mb-4">
                                        <input type="email" placeholder="Correo Electrónico" required name="email" value="{{ old('email') }}"
                                                class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                data-astro-cid-phpud7wc>
                                      
                                    </div>
                                </div>
                            </div>

                            <!-- Telefono -->
                            <div data-astro-cid-phpud7wc>
                                <div class="relative" data-astro-cid-phpud7wc>
                                    <div class="relative z-0 w-full mb-4">
                                        <input type="tel" placeholder="Telefono" required name="telefono"
                                                class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                data-astro-cid-phpud7wc>
                                     
                                    </div>
                                </div>
                            </div>

                            <!-- Tipo de usuario -->
                            <div data-astro-cid-phpud7wc>
                                <div class="relative" data-astro-cid-phpud7wc>
                                    <div class="relative z-0 w-full mb-4">
                                        <label for="tipo_usuario"
                                                class="block text-sm font-medium text-gray-500 mb-1"
                                                data-astro-cid-phpud7wc>Opcion de transaccion</label>
                                        <select name="tipos_usuario_id" id="tipo_usuario" required
                                                class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                data-astro-cid-phpud7wc>
                                        <option value="" disabled selected>-- Selecciona una opción --</option>
                                            @foreach ($tipos_usuarios as $tipoUsuario)
                                                <option value="{{ $tipoUsuario->id_tipo_usuario }}">{{ $tipoUsuario->tipo }}</option>
                                            @endforeach
                                        </select>
                    
                                    </div>
                                </div>
                            </div>

                            <!-- Foto del usuario -->
                            <div data-astro-cid-phpud7wc>
                                <div class="relative" data-astro-cid-phpud7wc>
                                    <div class="relative z-0 w-full mb-4">
                                        <label for="foto_usuario" class="block text-sm font-medium text-gray-500 mb-1"
                                                data-astro-cid-phpud7wc>Foto de perfil</label>
                                        <input type="file" name="profile_photo" id="profile_photo" accept="image/*"
                                                class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                data-astro-cid-phpud7wc>
                                        <p class="mt-1 text-sm text-gray-500" data-astro-cid-phpud7wc>Sube una imagen de perfil (opcional)</p>
                                    </div>    
                                </div>
                            </div>

                            <!-- Campo Contraseña -->
                            <div data-astro-cid-phpud7wc>
                                <div class="relative" data-astro-cid-phpud7wc>
                                    <div class="relative z-0 w-full mb-4">
                                        <input type="password" placeholder="Contraseña" required name="password"
                                                class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                data-astro-cid-phpud7wc>
                                      
                                    </div>
                                </div>
                            </div>

                            <!-- Campo Confirmar Contraseña -->
                            <div data-astro-cid-phpud7wc>
                                <div class="relative" data-astro-cid-phpud7wc>
                                    <div class="relative z-0 w-full mb-4">
                                        <input type="password" placeholder="Confirmar Contraseña" required name="password_confirmation"
                                                class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                data-astro-cid-phpud7wc>
                                       
                                    </div>
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
            class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-secondary text-white hover:bg-hoverSecondary disabled:opacity-50 disabled:pointer-events-none"
            data-astro-cid-phpud7wc>Registrarse</button>

                 {{--  <button onclick="window.history.back();" class="flex items-center my-4 gap-x-2 cursor-pointer text-blue-500">
                <svg class="h-4 w-4 fill-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
                <span>Volver atras</span>
            </button> --}}
            
            
            <a class="flex items-center my-4 gap-x-2 cursor-pointer text-blue-500" href="{{ route('home') }}" data-astro-cid-phpud7wc>
                <svg class="h-4 w-4 fill-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
                Volver a pagina de inicio
            </a>
            
                            
                            
    </div>
        @if($errors->any())
            <div class="alert alert-danger mt-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
</form>

                    </div>
                </div>
            </div>
        </div>
        </main>

@endsection


@push('scripts')
<script>
document.addEventListener("input", function () {
    let nombre   = document.querySelector('input[name="nombres"]').value;
    let apellido = document.querySelector('input[name="apellidos"]').value;

    document.getElementById("nombre_usuario").value =
        (nombre + apellido).replace(/\s+/g, '').toLowerCase();
});
</script>
@endpush

