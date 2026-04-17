@extends('layouts.app')

@section('title', 'Otras Categorías - Cambialord')

@section('content')
@php
    // Mapeo ID categoría → icono SVG (mismo que el menú lateral)
    $iconos = [
        1  => '/imgs/icons/side-bar-icons/instrumentos.svg',
        2  => '/imgs/icons/side-bar-icons/electrodomestico.svg',
        3  => '/imgs/icons/side-bar-icons/electronico.svg',
        5  => '/imgs/icons/side-bar-icons/muebles.svg',
        6  => '/imgs/icons/side-bar-icons/vehiculos.svg',
        7  => '/imgs/icons/side-bar-icons/herramientas.svg',
        8  => '/imgs/icons/side-bar-icons/joya.svg',
        9  => '/imgs/icons/side-bar-icons/lecciones.svg',
        10 => '/imgs/icons/side-bar-icons/otros.svg',
        11 => '/imgs/icons/side-bar-icons/age-limit.svg',
        13 => '/imgs/icons/side-bar-icons/cuidadoPersonal.svg',
        14 => '/imgs/icons/side-bar-icons/decoraciones.svg',
        15 => '/imgs/icons/side-bar-icons/deportes.svg',
        17 => '/imgs/icons/side-bar-icons/jardin.svg',
        21 => '/imgs/icons/side-bar-icons/Antiguedades.svg',
        22 => '/imgs/icons/side-bar-icons/niños.svg',
        23 => '/imgs/icons/side-bar-icons/mascotas.svg',
        24 => '/imgs/icons/side-bar-icons/tecnología.svg',
        25 => '/imgs/icons/side-bar-icons/librería.svg',
        28 => '/imgs/icons/side-bar-icons/oficina.svg',
    ];
@endphp

<div style="max-width:1200px;margin:0 auto;padding:1.5rem 1rem;">

    <div style="margin-bottom:1.5rem;">
        <nav style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;color:#6b7280;margin-bottom:0.75rem;">
            <a href="{{ route('home') }}" style="color:#6b7280;text-decoration:none;">Inicio</a>
            <span>/</span>
            <span style="color:#111827;font-weight:500;">Otras Categorías</span>
        </nav>
        <h1 style="font-size:1.5rem;font-weight:700;color:#111827;margin:0.5rem 0;" class="text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-bold my-2">Otras Categorías</h1>
        <p style="color:#6b7280;margin-top:0.25rem;font-size:0.9rem;">Explora más categorías de productos y servicios</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;">
        @foreach($categorias as $cat)
        {{-- Adultos (ID 11): solo visible si está logueado --}}
        @if($cat->id_categoria_item == 11 && !auth()->check())
            @continue
        @endif
        <a href="{{ route('categorias.show', $cat->slug) }}"
           style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.25rem 1rem;text-decoration:none;text-align:center;transition:box-shadow .3s,transform .2s,border-color .2s;display:flex;flex-direction:column;align-items:center;gap:0.5rem;"
           onmouseover="this.style.boxShadow='0 8px 25px rgba(0,0,0,.1)';this.style.transform='translateY(-3px)';this.style.borderColor='#479bd5'"
           onmouseout="this.style.boxShadow='none';this.style.transform='none';this.style.borderColor='#e5e7eb'">
            <img src="{{ $iconos[$cat->id_categoria_item] ?? '/imgs/icons/side-bar-icons/otros.svg' }}"
                 alt="{{ $cat->categoria }}"
                 style="width:48px;height:48px;object-fit:contain;">
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
