@extends('layouts.app')

@section('title', 'Verificar correo - CambialoRD')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-10 px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">

        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-orange-100 mb-4">
            <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>

        <h2 class="text-xl font-bold text-gray-800 mb-2">Verifica tu correo electrónico</h2>

        <p class="text-sm text-gray-500 mb-1">
            Hemos enviado un enlace de verificación a:
        </p>
        <p class="text-sm font-semibold text-gray-800 mb-4">
            {{ auth()->user()->email ?? '' }}
        </p>

        <p class="text-xs text-gray-400 mb-6">
            Revisa tu bandeja de entrada (y la carpeta de spam). Haz clic en el enlace del correo para activar tu cuenta.
        </p>

        @if (session('message'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-medium">
                {{ session('message') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit"
                    class="w-full py-2.5 px-4 bg-secondary text-white text-sm font-semibold rounded-lg hover:bg-hoverSecondary transition-colors">
                Reenviar correo de verificación
            </button>
        </form>

        <div class="mt-4 pt-4 border-t border-gray-100">
            <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-primary inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al inicio
            </a>
        </div>
    </div>
</div>
@endsection
