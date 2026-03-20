@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto mt-10 bg-white rounded-lg shadow-lg p-8">
    <h2 class="text-2xl font-semibold mb-4">Verifica tu correo electrónico</h2>
    <p>Por favor, revisa tu correo y haz clic en el enlace de verificación.</p>
    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="mt-4 bg-primary text-white px-4 py-2 rounded">Reenviar correo de verificación</button>
    </form>
    @if (session('message'))
        <div class="mt-4 text-green-600">{{ session('message') }}</div>
    @endif
</div>
@endsection
