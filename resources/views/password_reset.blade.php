@extends('layouts.app')

@section('title', 'Envio-password - Cambialord')

@section('content')
    <main>
        <div class="flex flex-col justify-center items-center min-h-screen bg-gray-100 p-4" data-astro-cid-zp6vblvr>
            <div class="my-8" data-astro-cid-zp6vblvr>
                <img src="{{ asset('/imgs/appLogo/logoFooter.png') }}" class="object-cover h-20" alt="Logo Cambialord" data-astro-cid-zp6vblvr width="160" height="80">
            </div>
            <div class="w-full max-w-md bg-white border border-gray-200 rounded-xl shadow-sm sm:w-11/12 lg:w-3/4 xl:w-2/3 2xl:w-1/2" data-astro-cid-zp6vblvr>
                <div class="p-4 sm:p-7" data-astro-cid-zp6vblvr>
                    <div class="text-center" data-astro-cid-zp6vblvr>
                        <h1 class="block text-2xl font-bold text-gray-800" data-astro-cid-zp6vblvr>Restablecer Contraseña</h1>
                        <p class="mt-2 text-sm text-gray-600" data-astro-cid-zp6vblvr>
                            Ingresa tu nueva contraseña para tu cuenta.
                        </p>
                    </div>
            <div class="mt-5" data-astro-cid-zp6vblvr>
                    
                        <form data-astro-cid-zp6vblvr method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <div class="grid gap-y-4" data-astro-cid-zp6vblvr>
                                <div data-astro-cid-zp6vblvr>
                                    <div class="relative" data-astro-cid-zp6vblvr>
                                        <div class="relative z-0 w-full mb-4">
                                            <input type="hidden" name="token" value="{{ $token }}">
                                        </div>
                                    </div>
                                </div>
                            
                            <!-- correo electrónico -->
                            <div data-astro-cid-zp6vblvr>
                                <div class="relative" data-astro-cid-zp6vblvr>
                                    <div class="relative z-0 w-full mb-4"> 
                                        {{-- <label for="email" class="block mb-1 text-gray-700 font-medium">Correo electrónico</label> --}}
                                        <input type="email" 
                                                required name="email"
                                               placeholder="Correo Electrónico"
                                                value="{{ old('email', $email) }}" required autofocus
                                                class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200" data-astro-cid-zp6vblvr>
                                      <!--  <label for="email" class="absolute duration-300 top-3 -z-1 origin-0 text-gray-500">Correo electrónico</label>-->
                                    </div>
                                </div>
                            </div>
                            <!-- contraseña -->
                            <div data-astro-cid-zp6vblvr>
                                <div class="relative" data-astro-cid-zp6vblvr>
                                    <div class="relative z-0 w-full mb-4">
                                        <input type="password" 
                                                placeholder="Nueva contraseña"
                                                required name="password"
                                                class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200" data-astro-cid-zp6vblvr>
                                      <!--  <label for="password" class="absolute duration-300 top-3 -z-1 origin-0 text-gray-500" data-astro-cid-zp6vblvr>Nueva contraseña</label>-->
                                        <span class="text-sm text-red-600 hidden" id="error" data-astro-cid-zp6vblvr>La contraseña debe tener al menos 8 caracteres.</span>
                                    </div>
                                </div>
                            </div>
                            <!-- confirmar contraseña -->
                            <div data-astro-cid-zp6vblvr>
                                <div class="relative" data-astro-cid-zp6vblvr>
                                    <div class="relative z-0 w-full mb-4">
                                        <input type="password"  placeholder="Confirmar contraseña"
                                                required name="password_confirmation"
                                                class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200" data-astro-cid-zp6vblvr>
                                        <!--<label for="password_confirmation" class="absolute duration-300 top-3 -z-1 origin-0 text-gray-500" data-astro-cid-zp6vblvr>Confirmar contraseña</label>-->
                                        <span class="text-sm text-red-600 hidden" id="error" data-astro-cid-zp6vblvr>Las contraseñas no coinciden.</span>
                                    </div>
                                </div>
                            </div>
                            <button type="submit"
                                class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-secondary text-white hover:bg-hoverSecondary disabled:opacity-50 disabled:pointer-events-none" data-astro-cid-zp6vblvr>
                                Restablecer Contraseña
                            </button>
                        </form>
                        </div>
                </div>
            </div>
            
            @if (session('status'))
                            <div class="mt-4 text-green-600 text-center">
                                {{ session('status') }}
                            </div>
                        @endif
                        <div class="mt-4 text-center">
                            <a href="{{ route('login') }}" class="text-primary hover:underline font-medium">Volver a iniciar sesión</a>
                        </div>
            </div>
    </main>
@endsection
