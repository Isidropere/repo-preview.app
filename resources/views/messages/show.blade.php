@extends('layouts.app')

@section('title', 'Mensaje - Cambialord')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <a href="{{ route('messages.index') }}" class="text-blue-600 hover:underline text-sm mb-4 inline-block">&larr; Volver a mensajes</a>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-sm text-gray-500">De: <span class="font-medium">{{ $message->sender->nombres ?? 'Usuario' }}</span></p>
                <p class="text-sm text-gray-500">Para: <span class="font-medium">{{ $message->receiver->nombres ?? 'Usuario' }}</span></p>
            </div>
            <span class="text-xs text-gray-400">{{ $message->created_at->format('d/m/Y H:i') }}</span>
        </div>

        @if($message->item)
            <div class="bg-gray-50 rounded p-3 mb-4">
                <p class="text-sm text-gray-600">Artículo: <a href="{{ route('producto.detalle', $message->item->slug) }}" class="text-blue-600 hover:underline">{{ $message->item->item }}</a></p>
            </div>
        @endif

        <div class="prose max-w-none">
            <p>{{ $message->message }}</p>
        </div>
    </div>
</div>
@endsection
