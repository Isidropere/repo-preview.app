@extends('layouts.app')

@section('title', 'Nuevo Mensaje - Cambialord')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    @include('components.btn-volver', ['backUrl' => route('messages.index')])
    <h1 class="text-2xl font-bold mb-6">Enviar Mensaje</h1>

    <form action="{{ route('messages.store') }}" method="POST" class="space-y-4">
        @csrf

        @if($item)
            <input type="hidden" name="item_id" value="{{ $item->id_item }}">
            <input type="hidden" name="receiver_id" value="{{ $item->id_user }}">
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-sm text-gray-600">Artículo: <span class="font-medium">{{ $item->item }}</span></p>
                <p class="text-sm text-gray-600">Para: <span class="font-medium">{{ $receiver->nombres ?? 'Usuario' }}</span></p>
            </div>
        @endif

        <div>
            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Mensaje</label>
            <textarea name="message" id="message" rows="5" required maxlength="1000"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Escribe tu mensaje...">{{ old('message') }}</textarea>
            @error('message')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Enviar</button>
            <a href="{{ route('messages.index') }}" class="px-6 py-2 rounded-lg border hover:bg-gray-50">Cancelar</a>
        </div>
    </form>
</div>
@endsection
