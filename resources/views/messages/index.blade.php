@extends('layouts.app')

@section('title', 'Mensajes - Cambialord')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    @include('components.btn-volver', ['backUrl' => route('tu_cuenta')])
    <h1 class="text-2xl font-bold mb-6">Mis Mensajes</h1>

    {{-- Mensajes Recibidos --}}
    <div class="mb-8">
        <h2 class="text-lg font-semibold mb-3">Recibidos</h2>
        @forelse($receivedMessages as $msg)
            <div class="bg-white rounded-lg shadow p-4 mb-3 flex items-start gap-3">
                <div class="flex-1">
                    <p class="text-sm text-gray-500">De: {{ $msg->sender->nombres ?? 'Usuario' }}</p>
                    <p class="mt-1">{{ Str::limit($msg->message, 100) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $msg->created_at->diffForHumans() }}</p>
                </div>
                <a href="{{ route('messages.show', $msg->id) }}" class="text-blue-600 text-sm hover:underline">Ver</a>
            </div>
        @empty
            <p class="text-gray-500">No tienes mensajes recibidos.</p>
        @endforelse
        {{ $receivedMessages->links() }}
    </div>

    {{-- Mensajes Enviados --}}
    <div>
        <h2 class="text-lg font-semibold mb-3">Enviados</h2>
        @forelse($sentMessages as $msg)
            <div class="bg-white rounded-lg shadow p-4 mb-3 flex items-start gap-3">
                <div class="flex-1">
                    <p class="text-sm text-gray-500">Para: {{ $msg->receiver->nombres ?? 'Usuario' }}</p>
                    <p class="mt-1">{{ Str::limit($msg->message, 100) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $msg->created_at->diffForHumans() }}</p>
                </div>
                <a href="{{ route('messages.show', $msg->id) }}" class="text-blue-600 text-sm hover:underline">Ver</a>
            </div>
        @empty
            <p class="text-gray-500">No tienes mensajes enviados.</p>
        @endforelse
        {{ $sentMessages->links() }}
    </div>
</div>
@endsection
