@extends('layouts.app')
@section('title', 'Login - Cambialord')
@section('content')
<main class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col items-center justify-center">

           @if (session('message'))
            <div class="w-full max-w-md mt-4 p-3 bg-yellow-100 text-yellow-800 rounded-lg text-center">
                {{ session('message') }}
            </div>
        @endif

     
      @error('credentials')
                <p class="text-red-500  text-center mt-2">{{ $message }}</p>
            @enderror

            @if(session('success'))
                <div class="mt-4 p-3 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
             <div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-4 sm:p-7" data-astro-cid-zp6vblvr>
                    <div class="text-center" data-astro-cid-zp6vblvr>
                        <h1 class="block text-2xl font-bold text-gray-800" data-astro-cid-zp6vblvr>Iniciar Sesión</h1>
                        <p class="mt-2 text-sm text-gray-600" data-astro-cid-zp6vblvr>
                            ¿Aún no tienes una cuenta?
                            <a class="text-primary decoration-2 hover:underline font-medium" href="/registro"
                                data-astro-cid-zp6vblvr
                                onclick="event.preventDefault(); if(confirm('¿Confirmas que eres mayor de 18 años?')) window.location.href='/registro';">
                                Registrate aquí
                            </a>
                        </p>
                    </div>
                    <div class="mt-5" data-astro-cid-zp6vblvr>
                        <a href="{{ route('social.login', 'google') }}"
                            class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none"
                            data-astro-cid-zp6vblvr> <svg class="w-4 h-auto" width="46" height="47" viewBox="0 0 46 47"
                                fill="none" data-astro-cid-zp6vblvr>
                                <path
                                    d="M46 24.0287C46 22.09 45.8533 20.68 45.5013 19.2112H23.4694V27.9356H36.4069C36.1429 30.1094 34.7347 33.37 31.5957 35.5731L31.5663 35.8669L38.5191 41.2719L38.9885 41.3306C43.4477 37.2181 46 31.1669 46 24.0287Z"
                                    fill="#4285F4" data-astro-cid-zp6vblvr></path>
                                <path
                                    d="M23.4694 47C29.8061 47 35.1161 44.9144 39.0179 41.3012L31.625 35.5437C29.6301 36.9244 26.9898 37.8937 23.4987 37.8937C17.2793 37.8937 12.0281 33.7812 10.1505 28.1412L9.88649 28.1706L2.61097 33.7812L2.52296 34.0456C6.36608 41.7125 14.287 47 23.4694 47Z"
                                    fill="#34A853" data-astro-cid-zp6vblvr></path>
                                <path
                                    d="M10.1212 28.1413C9.62245 26.6725 9.32908 25.1156 9.32908 23.5C9.32908 21.8844 9.62245 20.3275 10.0918 18.8588V18.5356L2.75765 12.8369L2.52296 12.9544C0.909439 16.1269 0 19.7106 0 23.5C0 27.2894 0.909439 30.8731 2.49362 34.0456L10.1212 28.1413Z"
                                    fill="#FBBC05" data-astro-cid-zp6vblvr></path>
                                <path
                                    d="M23.4694 9.07688C27.8699 9.07688 30.8622 10.9863 32.5344 12.5725L39.1645 6.11C35.0867 2.32063 29.8061 0 23.4694 0C14.287 0 6.36607 5.2875 2.49362 12.9544L10.0918 18.8588C11.9987 13.1894 17.25 9.07688 23.4694 9.07688Z"
                                    fill="#EB4335" data-astro-cid-zp6vblvr></path>
                            </svg>
                            Iniciar Sesión con Google
                        </a>
                        <div class="py-3 flex items-center text-xs text-gray-400 uppercase before:flex-1 before:border-t before:border-gray-200 before:me-6 after:flex-1 after:border-t after:border-gray-200 after:ms-6"
                            data-astro-cid-zp6vblvr>O</div> <!-- Form -->
                        <form data-astro-cid-zp6vblvr method="POST" action="{{ route('login.post') }}">
                            @csrf
                            <div class="grid gap-y-4" data-astro-cid-zp6vblvr> <!-- Form Group -->
                                <div data-astro-cid-zp6vblvr>
                                    <div class="relative" data-astro-cid-zp6vblvr>
                                        <div class="relative z-0 w-full mb-4"> <input type="email" placeholder="Correo Electrónico"
                                                required name="email"
                                                value="{{ old('email') }}"
                                                class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                data-astro-cid-zp6vblvr> 
                                        </div>
                                    </div>
                                </div>
                                <p class="hidden text-xs text-red-600 mt-2" id="email-error" data-astro-cid-zp6vblvr>
                                    Please include a valid email address so we can get back to you</p>
                            </div> <!-- End Form Group --> <!-- Form Group -->
                            <div data-astro-cid-zp6vblvr>
                                <div class="relative" data-astro-cid-zp6vblvr>
                                    <div class="relative z-0 w-full mb-4">
                                        <input type="password" placeholder="Contraseña" required
                                            name="password" id="passwordInput"
                                            class="relative pt-3 pb-2 block w-full px-0 pr-10 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                            data-astro-cid-zp6vblvr>
                                        <button type="button" onclick="togglePassword()" class="absolute right-0 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600" tabindex="-1">
                                            <svg id="eyeOpen" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg id="eyeClosed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        </button>
                                        <span class="text-sm text-red-600 hidden" id="error" data-astro-cid-zp6vblvr>Contraseña es requerida</span>
                                    </div>
                                </div>
                                <div class="flex justify-end items-center" data-astro-cid-zp6vblvr> <a
                                        class="text-sm text-primary decoration-2 hover:underline font-medium"
                                        href="{{ route('password.request') }}" data-astro-cid-zp6vblvr>¿Has olvidado tu contraseña?</a>
                                </div>
                            </div> <!-- End Form Group --> <!-- Checkbox -->
                            <div class="flex items-center" data-astro-cid-zp6vblvr>
                                <div class="flex" data-astro-cid-zp6vblvr> <input id="remember-me" name="remember-me"
                                        type="checkbox"
                                        class="shrink-0 mt-0.5 border-gray-200 rounded text-primary focus:ring-primary"
                                        data-astro-cid-zp6vblvr> </div>
                                <div class="m-3" data-astro-cid-zp6vblvr> <label for="remember-me" class="text-sm"
                                        data-astro-cid-zp6vblvr>Recordarme</label> </div>
                            </div> <!-- End Checkbox --> <button type="submit"
                                onclick="this.disabled=true;this.textContent='Ingresando...';this.form.submit();"
                                class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-secondary text-white hover:bg-hoverSecondary disabled:opacity-50 disabled:pointer-events-none"
                                data-astro-cid-zp6vblvr>Iniciar Sesión</button>
                        </form>
                    </div> <!-- End Form -->
                </div>
                   <a href="{{ route('home') }}" class="flex items-center my-4 gap-x-2 cursor-pointer"
                data-astro-cid-zp6vblvr> <svg class="h-4 w-4 fill-primary" data-astro-cid-zp6vblvr
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg> <span class="text-primary" data-astro-cid-zp6vblvr>Volver</span> </a>
        
            </div>
            
    </div>
     </div>
</main>
<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const eyeOpen = document.getElementById('eyeOpen');
    const eyeClosed = document.getElementById('eyeClosed');
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
    } else {
        input.type = 'password';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    }
}
</script>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.trackGoogleTagEvent === 'function') {
            window.trackGoogleTagEvent('login_page_view', {});
        }
    });
</script>
@endpush

