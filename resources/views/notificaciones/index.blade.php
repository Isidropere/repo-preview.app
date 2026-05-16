@extends('layouts.app')

@section('title', 'Mis Notificaciones - Cambialord')

@section('content')
<div class="min-h-screen bg-gray-50 py-5">
    <div class="max-w-2xl mx-auto px-4">

        @include('components.btn-volver', ['backUrl' => route('home')])

        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Mis Notificaciones</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $mensajes->total() }} notificación(es)</p>
            </div>
            @if($mensajes->where('leido', 0)->count() > 0)
            <form action="{{ route('notificaciones.leerTodas') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm text-primary hover:underline font-medium">
                    Marcar todas como leídas
                </button>
            </form>
            @endif
        </div>

        @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-3 rounded-lg mb-4 text-sm">
            {{ session('success') }}
        </div>
        @endif

        @if($mensajes->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-gray-500">No tienes notificaciones</p>
        </div>
        @else
        <div class="space-y-2">
            @foreach($mensajes as $msg)
                @php
                    $msgStr = $msg->mensaje ?? '';
                    $esServicio = Str::contains($msgStr, ['[Servicio]', 'talento', 'servicio']);
                    $esIntercambio = Str::contains($msgStr, ['[Intercambio]', 'intercambio', 'negociaci', 'propuesta']);
                    $esCompra = Str::contains($msgStr, ['[Compra]', 'Tu orden #']);
                    $esVenta = Str::contains($msgStr, ['[Venta]']) || (Str::contains($msgStr, 'orden #') && !Str::contains($msgStr, '[Compra]'));

                    $destino = '/mis-notificaciones';
                    if ($esServicio) $destino = route('solicitudes.index');
                    elseif ($esIntercambio) $destino = route('negociaciones.mis');
                    elseif ($esCompra) $destino = route('historial', ['tab' => 'compras']);
                    elseif ($esVenta) $destino = route('historial', ['tab' => 'ventas']);
                @endphp
                <div class="bg-white rounded-xl shadow-sm border {{ $msg->leido ? 'border-gray-100' : 'border-primary/30 bg-primary/5' }} p-4 transition-all">
                    <div class="flex items-start gap-3">
                        {{-- Icono según tipo --}}
                        <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-lg
                            {{ $msg->id_emisor ? 'bg-blue-100' : 'bg-orange-100' }}">
                            @if($esServicio) ⭐
                            @elseif($esIntercambio) 🔄
                            @elseif($esCompra) 💳
                            @elseif($esVenta) 🛒
                            @elseif(!$msg->id_emisor) 📢
                            @else 💬 @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                @if(!$msg->id_emisor)
                                    <span class="text-xs font-semibold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">Sistema</span>
                                @else
                                    <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">
                                        {{ $msg->sender?->nombres ?? 'Usuario' }}
                                    </span>
                                @endif
                                @if(!$msg->leido)
                                    <span class="w-2 h-2 rounded-full bg-primary flex-shrink-0"></span>
                                @endif
                                <span class="text-xs text-gray-400 ml-auto flex-shrink-0">{{ $msg->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-700 break-words mb-2">{{ $msg->mensaje }}</p>
                            
                            @if($destino !== '/mis-notificaciones')
                                <a href="{{ $destino }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                                    Ver detalle
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endif
                        </div>

                        @if(!$msg->leido)
                        <form action="{{ route('notificaciones.leido', $msg->id) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-primary p-1" title="Marcar como leída">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $mensajes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
