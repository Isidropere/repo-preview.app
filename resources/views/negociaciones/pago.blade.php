@extends('layouts.app')
@section('title', 'Pago de Intercambio')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
<div class="max-w-lg mx-auto px-4">

    @include('components.btn-volver', ['backUrl' => route('negociaciones.mis')])

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-gray-800">Pago de intercambio</h2>
                <p class="text-xs text-gray-400">Intercambio #{{ $neg->id_negociacion }} · {{ $neg->item?->item }}</p>
            </div>
        </div>

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-3 mb-4 text-sm">{{ session('error') }}</div>
        @endif

        @php $userId = auth()->id(); $yaPague = ($userId == $neg->usuario_emisor_id && $neg->pago_emisor) || ($userId == $neg->usuario_receptor_id && $neg->pago_receptor); @endphp

        @if($yaPague)
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <p class="text-green-700 font-semibold">✅ Ya realizaste tu pago</p>
            <p class="text-xs text-green-600 mt-1">Esperando el pago de la otra parte.</p>
        </div>
        @elseif($monto > 0)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5 flex justify-between items-center">
            <span class="text-blue-700 font-semibold text-sm">Monto a pagar</span>
            <span class="text-xl font-bold text-blue-700">RD$ {{ number_format($monto, 2) }}</span>
        </div>

        <form action="{{ route('negociaciones.pago.procesar', $neg->id_negociacion) }}" method="POST">
            @csrf
            <div class="mb-4">
                <p class="text-sm font-semibold text-gray-700 mb-2">Selecciona una tarjeta</p>
                @forelse($tarjetas as $tarjeta)
                <label class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer mb-2 hover:border-blue-300 transition-all {{ $loop->first ? 'border-blue-400 bg-blue-50' : 'border-gray-200' }}">
                    <input type="radio" name="id_tarjeta" value="{{ $tarjeta->id_tarjeta }}" {{ $loop->first ? 'checked' : '' }} class="h-4 w-4 text-blue-600">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">**** **** **** {{ $tarjeta->last4 }}</p>
                        <p class="text-xs text-gray-400">{{ $tarjeta->nombre_titular }}</p>
                    </div>
                </label>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">No tienes tarjetas guardadas. <a href="{{ route('carrito.checkout') }}" class="text-blue-600 underline">Agregar tarjeta</a></p>
                @endforelse
            </div>

            @if($tarjetas->isNotEmpty())
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-1">CVV</label>
                <input type="password" name="cvv" maxlength="4" placeholder="•••"
                       class="w-28 border-2 border-gray-200 rounded-xl px-4 py-2.5 text-center text-lg tracking-widest font-mono focus:outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold text-sm shadow-md transition-all">
                💳 Confirmar pago
            </button>
            @endif
        </form>
        @else
        <form action="{{ route('negociaciones.pago.procesar', $neg->id_negociacion) }}" method="POST">
            @csrf
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-4 text-center">
                <p class="text-emerald-700 font-semibold">Este intercambio no requiere pago monetario.</p>
                <p class="text-xs text-emerald-600 mt-1">Solo confirma tu participación.</p>
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl font-bold text-sm">
                ✅ Confirmar participación
            </button>
        </form>
        @endif
    </div>
</div>
</div>
@endsection
