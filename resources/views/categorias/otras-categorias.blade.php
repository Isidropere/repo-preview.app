@extends('layouts.app')

@section('title', 'Otras Categorías - Cambialord')

@section('content')
<div style="max-width:1200px;margin:0 auto;padding:1.5rem 1rem;">

    {{-- Breadcrumbs + Título --}}
    <div style="margin-bottom:1.5rem;">
        <nav style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;color:#6b7280;margin-bottom:0.75rem;">
            <a href="{{ route('home') }}" style="color:#6b7280;text-decoration:none;">Inicio</a>
            <span>/</span>
            <span style="color:#111827;font-weight:500;">Otras Categorías</span>
        </nav>
        
        <h1 class="text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-bold my-2">Otras Categorías</h1>
        <p style="color:#6b7280;margin-top:0.25rem;font-size:0.9rem;">Explora más categorías de productos y servicios</p>
    </div>

    {{-- Grid de categorías --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
        @foreach($categorias as $cat)
        <a href="{{ route('categorias.show', $cat->slug) }}"
           style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.25rem;text-decoration:none;text-align:center;transition:box-shadow .3s,transform .2s,border-color .2s;display:flex;flex-direction:column;align-items:center;gap:0.5rem;"
           onmouseover="this.style.boxShadow='0 8px 25px rgba(0,0,0,.1)';this.style.transform='translateY(-3px)';this.style.borderColor='#479bd5'"
           onmouseout="this.style.boxShadow='none';this.style.transform='none';this.style.borderColor='#e5e7eb'">
            <div style="width:48px;height:48px;background:#f0f9ff;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                <svg style="width:24px;height:24px;color:#479bd5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <span style="font-size:0.9rem;font-weight:500;color:#111827;">{{ $cat->categoria }}</span>
            <span style="font-size:0.75rem;color:#9ca3af;">{{ $cat->items()->where('estatus', 1)->count() }} productos</span>
        </a>
        @endforeach
    </div>

    @if($categorias->isEmpty())
    <div style="text-align:center;padding:3rem;color:#6b7280;">
        <p>No hay categorías adicionales disponibles.</p>
    </div>
    @endif
</div>
@endsection
