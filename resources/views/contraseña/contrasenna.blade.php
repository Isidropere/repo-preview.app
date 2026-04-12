@extends('layouts.app')
@section('title', 'Cambiar contraseña - Cambialord')

@section('content')
<main class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col items-center justify-center">

            @if(session('success'))
            <div class="w-full max-w-md mt-4 p-3 bg-green-100 text-green-700 rounded-lg text-center">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="w-full max-w-md mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-center">{{ session('error') }}</div>
            @endif
            @if($errors->any())
            <div class="w-full max-w-md mt-4 p-3 bg-red-50 text-red-700 rounded-lg">
                @foreach($errors->all() as $error)<p class="text-sm">{{ $error }}</p>@endforeach
            </div>
            @endif

            <div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden mt-4">
                <div class="p-6">
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 mb-3">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-800">Cambiar contraseña</h1>
                        <p class="text-sm text-gray-500 mt-1">Ingresa tu contraseña actual para verificar tu identidad</p>
                    </div>

                    {{-- Info del usuario --}}
                    @auth
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl mb-5">
                        <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->nombres, 0, 1)) }}{{ strtoupper(substr(auth()->user()->apellidos, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}</p>
                            <p class="text-xs text-gray-400">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    @endauth

                    <form method="POST" action="{{ route('password.update.profile') }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="current_password" class="block text-xs font-medium text-gray-700 mb-1">Contraseña actual</label>
                                <div class="relative">
                                    <input type="password" id="current_password" name="current_password" required placeholder="Tu contraseña actual"
                                           class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                    <button type="button" onclick="togglePwd('current_password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" tabindex="-1">
                                        <svg class="w-4 h-4 eye-closed" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M3 3l18 18"/></svg>
                                        <svg class="w-4 h-4 eye-open hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-xs font-medium text-gray-700 mb-1">Nueva contraseña</label>
                                <div class="relative">
                                    <input type="password" id="password" name="password" required placeholder="Mínimo 8 caracteres"
                                           class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                    <button type="button" onclick="togglePwd('password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" tabindex="-1">
                                        <svg class="w-4 h-4 eye-closed" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M3 3l18 18"/></svg>
                                        <svg class="w-4 h-4 eye-open hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-xs font-medium text-gray-700 mb-1">Confirmar nueva contraseña</label>
                                <div class="relative">
                                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Repetir nueva contraseña"
                                           class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                    <button type="button" onclick="togglePwd('password_confirmation')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" tabindex="-1">
                                        <svg class="w-4 h-4 eye-closed" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M3 3l18 18"/></svg>
                                        <svg class="w-4 h-4 eye-open hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            </div>

                            <button type="submit"
                                    class="w-full py-2.5 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-secondary text-white hover:bg-hoverSecondary transition-colors">
                                Cambiar contraseña
                            </button>
                        </div>
                    </form>
                </div>

                <div class="text-center pb-5">
                    <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-primary inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
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
</script>
@endsection
