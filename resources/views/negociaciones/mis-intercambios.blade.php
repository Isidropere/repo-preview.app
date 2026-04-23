@extends('layouts.app')
@section('title', 'Mis Intercambios - Cambialord')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
<div class="max-w-4xl mx-auto px-4">

    @include('components.btn-volver', ['backUrl' => route('home')])

    <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        Mis Intercambios
    </h1>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm mb-4">{{ session('error') }}</div>
    @endif

    {{-- Tabs --}}
    <div class="flex gap-2 mb-6 border-b border-gray-200">
        <button onclick="mostrarTab('recibidas')" id="tab-recibidas"
                class="tab-btn px-4 py-2 text-sm font-semibold border-b-2 border-emerald-600 text-emerald-700 -mb-px">
            📥 Recibidas
            @if($comoReceptor->whereIn('estado',['Inicial','contraoferta'])->count() > 0)
            <span class="ml-1 bg-emerald-600 text-white text-xs px-1.5 py-0.5 rounded-full">{{ $comoReceptor->whereIn('estado',['Inicial','contraoferta'])->count() }}</span>
            @endif
        </button>
        <button onclick="mostrarTab('enviadas')" id="tab-enviadas"
                class="tab-btn px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 -mb-px">
            📤 Enviadas
        </button>
    </div>

    {{-- TAB: Recibidas (como receptor) --}}
    <div id="panel-recibidas">
        @forelse($comoReceptor as $neg)
        @include('negociaciones.partials.tarjeta-negociacion', ['neg' => $neg, 'rol' => 'receptor'])
        @empty
        <div class="text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            <p class="text-sm">No has recibido propuestas de intercambio aún.</p>
        </div>
        @endforelse
    </div>

    {{-- TAB: Enviadas (como emisor) --}}
    <div id="panel-enviadas" class="hidden">
        @forelse($comoEmisor as $neg)
        @include('negociaciones.partials.tarjeta-negociacion', ['neg' => $neg, 'rol' => 'emisor'])
        @empty
        <div class="text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            <p class="text-sm">No has enviado propuestas de intercambio aún.</p>
        </div>
        @endforelse
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
function mostrarTab(tab) {
    document.getElementById('panel-recibidas').classList.toggle('hidden', tab !== 'recibidas');
    document.getElementById('panel-enviadas').classList.toggle('hidden', tab !== 'enviadas');
    document.getElementById('tab-recibidas').className = tab === 'recibidas'
        ? 'tab-btn px-4 py-2 text-sm font-semibold border-b-2 border-emerald-600 text-emerald-700 -mb-px'
        : 'tab-btn px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 -mb-px';
    document.getElementById('tab-enviadas').className = tab === 'enviadas'
        ? 'tab-btn px-4 py-2 text-sm font-semibold border-b-2 border-emerald-600 text-emerald-700 -mb-px'
        : 'tab-btn px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 -mb-px';
}
</script>
@endpush
