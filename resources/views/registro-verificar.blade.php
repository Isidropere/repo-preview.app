@extends('layouts.app')

@section('title', 'Verificar correo - Cambialord')

@section('content')
<div class="min-h-screen bg-gray-50 py-5">
    <div class="max-w-md mx-auto px-4">

        <div class="text-center mb-5">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-primary/10 mb-3">
                <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Verifica tu correo</h1>
            <p class="text-gray-500 mt-2 text-sm">
                Enviamos un código de 6 dígitos a <span class="font-semibold text-gray-700">{{ $emailOculto }}</span>
            </p>
        </div>

        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-4">
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
        @endif

        @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-4">
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">
            <form action="{{ route('registro.verificar') }}" method="POST" id="codigoForm">
                @csrf

                <label class="block text-sm font-medium text-gray-700 mb-2 text-center">Ingresa el código</label>

                <div class="flex justify-center gap-2 mb-4" id="codeInputs">
                    @for($i = 0; $i < 6; $i++)
                    <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                           class="code-digit w-11 h-12 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                           data-index="{{ $i }}" autocomplete="off">
                    @endfor
                </div>

                <input type="hidden" name="codigo" id="codigoHidden">

                <button type="submit" id="btnVerificar"
                        class="w-full py-2.5 px-4 text-sm font-semibold rounded-lg bg-secondary text-white hover:bg-hoverSecondary transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    Verificar y crear cuenta
                </button>
            </form>

            <div class="mt-4 text-center">
                <p class="text-xs text-gray-400 mb-2">¿No recibiste el código?</p>
                <form action="{{ route('registro.reenviar') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-primary hover:underline font-medium">
                        Reenviar código
                    </button>
                </form>
            </div>

            <div class="mt-3 text-center">
                <p class="text-xs text-gray-400">El código expira en 10 minutos</p>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('registro') }}" class="text-sm text-gray-500 hover:text-primary inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al registro
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const digits = document.querySelectorAll('.code-digit');
    const hidden = document.getElementById('codigoHidden');
    const btn = document.getElementById('btnVerificar');
    const form = document.getElementById('codigoForm');

    function updateHidden() {
        let code = '';
        digits.forEach(d => code += d.value);
        hidden.value = code;
        btn.disabled = code.length < 6;
    }

    digits.forEach((input, idx) => {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value && idx < 5) digits[idx + 1].focus();
            updateHidden();
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && idx > 0) {
                digits[idx - 1].focus();
                digits[idx - 1].value = '';
                updateHidden();
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            for (let i = 0; i < Math.min(text.length, 6); i++) {
                digits[i].value = text[i];
            }
            if (text.length >= 6) digits[5].focus();
            else if (text.length > 0) digits[Math.min(text.length, 5)].focus();
            updateHidden();
        });
    });

    digits[0].focus();

    form.addEventListener('submit', function() {
        btn.disabled = true;
        btn.textContent = 'Verificando...';
    });
});
</script>
@endpush
