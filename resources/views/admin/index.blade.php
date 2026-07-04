@extends('layouts.app')

@section('title', 'Panel de Administración')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        {{-- Encabezado --}}
        <div class="mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    Panel de Administración
                </h1>
                <p class="text-xs text-gray-500 mt-0.5">Gestión centralizada de compras, ventas, intercambios y servicios.</p>
            </div>
            <a href="/" target="_blank" class="self-start md:self-auto inline-flex items-center gap-1.5 text-xs text-primary hover:underline font-semibold">
                Ver Sitio Web
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>

        {{-- Herramientas de Control (Solo Admin o Super Admin) --}}
        @if(auth()->user()->isAdmin || auth()->user()->isSuperAdminUser())
        <div class="mb-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                @if(auth()->user()->isSuperAdminUser())
                <a href="{{ route('admin.stats.index') }}"
                   class="flex items-center gap-2.5 bg-white border border-gray-200 hover:border-primary/40 hover:bg-primary/5 p-3 rounded-xl text-xs font-semibold text-gray-700 hover:text-primary transition-all group shadow-sm">
                    <div class="text-gray-400 group-hover:text-primary transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <span>Estadísticas</span>
                </a>
                @endif
                <a href="{{ route('admin.imagenes.index') }}"
                   class="flex items-center gap-2.5 bg-white border border-gray-200 hover:border-primary/40 hover:bg-primary/5 p-3 rounded-xl text-xs font-semibold text-gray-700 hover:text-primary transition-all group shadow-sm">
                    <div class="text-gray-400 group-hover:text-primary transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span>Aprobación de Fotos</span>
                </a>
                <a href="{{ route('admin.notificaciones.categorias') }}"
                   class="flex items-center gap-2.5 bg-white border border-gray-200 hover:border-primary/40 hover:bg-primary/5 p-3 rounded-xl text-xs font-semibold text-gray-700 hover:text-primary transition-all group shadow-sm">
                    <div class="text-gray-400 group-hover:text-primary transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                    </div>
                    <span>Notificar Categorías</span>
                </a>
                <a href="{{ route('admin.notificaciones.index') }}"
                   class="flex items-center gap-2.5 bg-white border border-gray-200 hover:border-primary/40 hover:bg-primary/5 p-3 rounded-xl text-xs font-semibold text-gray-700 hover:text-primary transition-all group shadow-sm">
                    <div class="text-gray-400 group-hover:text-primary transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <span>Notificaciones</span>
                </a>
                <a href="{{ route('admin.mensajes.index') }}"
                   class="flex items-center gap-2.5 bg-white border border-gray-200 hover:border-primary/40 hover:bg-primary/5 p-3 rounded-xl text-xs font-semibold text-gray-700 hover:text-primary transition-all group shadow-sm">
                    <div class="text-gray-400 group-hover:text-primary transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <span>Mensajes Predefinidos</span>
                </a>
                <a href="{{ route('admin.ayuda.index') }}"
                   class="flex items-center gap-2.5 bg-white border border-gray-200 hover:border-primary/40 hover:bg-primary/5 p-3 rounded-xl text-xs font-semibold text-gray-700 hover:text-primary transition-all group shadow-sm">
                    <div class="text-gray-400 group-hover:text-primary transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span>Páginas de Ayuda</span>
                </a>
                <a href="{{ route('admin.recursos-humanos.index') }}"
                   class="flex items-center gap-2.5 bg-white border border-gray-200 hover:border-primary/40 hover:bg-primary/5 p-3 rounded-xl text-xs font-semibold text-gray-700 hover:text-primary transition-all group shadow-sm">
                    <div class="text-gray-400 group-hover:text-primary transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span>Recursos Humanos</span>
                </a>
                @if(auth()->user()->isSuperAdminUser())
                <a href="{{ route('admin.motivos_devolucion.index') }}"
                   class="flex items-center gap-2.5 bg-white border border-gray-200 hover:border-primary/40 hover:bg-primary/5 p-3 rounded-xl text-xs font-semibold text-gray-700 hover:text-primary transition-all group shadow-sm">
                    <div class="text-gray-400 group-hover:text-primary transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H18v3zM7 11V7a2 2 0 012-2h6a2 2 0 012 2v4M5 19v-2a2 2 0 012-2h10a2 2 0 012 2v2"/>
                        </svg>
                    </div>
                    <span>Motivos Devolución</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Accesos Rápidos ERP (Solo Super Admin o Contable) --}}
        @if(auth()->user()->isSuperAdminUser() || auth()->user()->isContableUser())
        <div class="mb-6 bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Gestión Empresarial (ERP)</h2>
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                <a href="{{ route('admin.erp.contabilidad') }}" 
                   class="flex items-center gap-3 bg-gray-50/50 p-3 rounded-xl border border-gray-100 hover:border-primary/30 hover:bg-white hover:shadow transition-all group">
                    <div class="bg-primary/10 p-2.5 rounded-lg text-primary group-hover:bg-primary group-hover:text-white transition-all flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18 18.247 18.477 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800 text-xs truncate">Contabilidad</p>
                        <p class="text-[9px] text-gray-400 truncate mt-0.5">Libro diario y cuentas</p>
                    </div>
                </a>
                <a href="{{ route('admin.erp.inventario') }}" 
                   class="flex items-center gap-3 bg-gray-50/50 p-3 rounded-xl border border-gray-100 hover:border-secondary/30 hover:bg-white hover:shadow transition-all group">
                    <div class="bg-secondary/10 p-2.5 rounded-lg text-secondary group-hover:bg-secondary group-hover:text-white transition-all flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800 text-xs truncate">Inventario</p>
                        <p class="text-[9px] text-gray-400 truncate mt-0.5">Stock y Almacén</p>
                    </div>
                </a>
                <a href="{{ route('admin.erp.caja') }}" 
                   class="flex items-center gap-3 bg-gray-50/50 p-3 rounded-xl border border-gray-100 hover:border-amber-500/30 hover:bg-white hover:shadow transition-all group">
                    <div class="bg-amber-50 p-2.5 rounded-lg text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-all flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800 text-xs truncate">Cuadre de Caja</p>
                        <p class="text-[9px] text-gray-400 truncate mt-0.5">Apertura y cierre diario</p>
                    </div>
                </a>
                <a href="{{ route('admin.erp.transporte.index') }}" 
                   class="flex items-center gap-3 bg-gray-50/50 p-3 rounded-xl border border-gray-100 hover:border-indigo-500/30 hover:bg-white hover:shadow transition-all group">
                    <div class="bg-indigo-50 p-2.5 rounded-lg text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4M5 17h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800 text-xs truncate">Transporte</p>
                        <p class="text-[9px] text-gray-400 truncate mt-0.5">Solicitudes de mudanza</p>
                    </div>
                </a>
                <a href="{{ route('admin.erp.historial') }}" 
                   class="flex items-center gap-3 bg-gray-50/50 p-3 rounded-xl border border-gray-100 hover:border-emerald-500/30 hover:bg-white hover:shadow transition-all group">
                    <div class="bg-emerald-50 p-2.5 rounded-lg text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800 text-xs truncate">Ventas e Intercambios</p>
                        <p class="text-[9px] text-gray-400 truncate mt-0.5">Historial de procesados</p>
                    </div>
                </a>
            </div>
        </div>
        @endif

        {{-- Spinner --}}
        <div id="pageLoader" class="flex flex-col items-center justify-center py-16 gap-3">
            <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
            </svg>
            <span class="text-gray-500 text-sm">Cargando...</span>
        </div>

        <div id="mainContent" class="hidden">

            @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
            @endif

            {{-- KPIs y Tablas Generales (Solo Admin o Super Admin) --}}
            @if(auth()->user()->isAdmin || auth()->user()->isSuperAdminUser())
            {{-- KPIs --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 mb-5">
                <a href="{{ route('admin.index', ['tab' => 'compras']) }}"
                   class="bg-white rounded-xl shadow-sm p-2.5 border border-l-4 {{ $tab === 'compras' ? 'border-primary ring-1 ring-primary/20 bg-primary/5 shadow' : 'border-gray-100 border-l-primary' }} hover:border-primary/50 hover:shadow-md hover:scale-[1.01] transition-all cursor-pointer block">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider truncate">Compras</p>
                    <p class="text-base font-black text-gray-900 mt-0.5">{{ number_format($totalCompras, 0) }}</p>
                </a>
                <a href="{{ route('admin.index', ['tab' => 'ventas']) }}"
                   class="bg-white rounded-xl shadow-sm p-2.5 border border-l-4 {{ $tab === 'ventas' ? 'border-secondary ring-1 ring-secondary/20 bg-secondary/5 shadow' : 'border-gray-100 border-l-secondary' }} hover:border-secondary/50 hover:shadow-md hover:scale-[1.01] transition-all cursor-pointer block">
                    <p class="text-[9px] font-bold text-secondary uppercase tracking-wider truncate">Ventas</p>
                    <p class="text-base font-black text-secondary mt-0.5">{{ number_format($totalVentas, 0) }}</p>
                </a>
                <a href="{{ route('admin.index', ['tab' => 'intercambios']) }}"
                   class="bg-white rounded-xl shadow-sm p-2.5 border border-l-4 {{ $tab === 'intercambios' ? 'border-purple-500 ring-1 ring-purple-500/20 bg-purple-50/30 shadow' : 'border-gray-100 border-l-purple-500' }} hover:border-purple-500/50 hover:shadow-md hover:scale-[1.01] transition-all cursor-pointer block">
                    <p class="text-[9px] font-bold text-purple-600 uppercase tracking-wider truncate">Intercambios</p>
                    <p class="text-base font-black text-purple-600 mt-0.5">{{ number_format($totalIntercambios, 0) }}</p>
                </a>
                <a href="{{ route('admin.index', ['tab' => 'intencion_compra']) }}"
                   class="bg-white rounded-xl shadow-sm p-2.5 border border-l-4 {{ $tab === 'intencion_compra' ? 'border-amber-500 ring-1 ring-amber-500/20 bg-amber-50/30 shadow' : 'border-gray-100 border-l-amber-500' }} hover:border-amber-500/50 hover:shadow-md hover:scale-[1.01] transition-all cursor-pointer block">
                    <p class="text-[9px] font-bold text-amber-600 uppercase tracking-wider truncate">Int. Compra</p>
                    <p class="text-base font-black text-amber-600 mt-0.5">{{ number_format($totalIntencionCompra, 0) }}</p>
                </a>
                <a href="{{ route('admin.index', ['tab' => 'intencion_intercambio']) }}"
                   class="bg-white rounded-xl shadow-sm p-2.5 border border-l-4 {{ $tab === 'intencion_intercambio' ? 'border-pink-500 ring-1 ring-pink-500/20 bg-pink-50/30 shadow' : 'border-gray-100 border-l-pink-500' }} hover:border-pink-500/50 hover:shadow-md hover:scale-[1.01] transition-all cursor-pointer block">
                    <p class="text-[9px] font-bold text-pink-600 uppercase tracking-wider truncate">Int. Intercambio</p>
                    <p class="text-base font-black text-pink-600 mt-0.5">{{ number_format($totalIntencionIntercambio, 0) }}</p>
                </a>
            </div>

            {{-- Tabs --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 overflow-x-auto">
                    <nav class="flex -mb-px min-w-max">
                        <a href="{{ route('admin.index', ['tab' => 'compras']) }}"
                           class="px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                                  {{ $tab === 'compras' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Compras
                            <span class="ml-1.5 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ $compras->total() }}</span>
                        </a>
                        <a href="{{ route('admin.index', ['tab' => 'ventas']) }}"
                           class="px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                                  {{ $tab === 'ventas' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Ventas
                            <span class="ml-1.5 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ $ventas->total() }}</span>
                        </a>
                        <a href="{{ route('admin.index', ['tab' => 'intercambios']) }}"
                           class="px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                                  {{ $tab === 'intercambios' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Intercambios
                            <span class="ml-1.5 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ $intercambios->total() }}</span>
                        </a>
                        <a href="{{ route('admin.index', ['tab' => 'intencion_compra']) }}"
                           class="px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                                  {{ $tab === 'intencion_compra' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Intención de Compra
                            <span class="ml-1.5 bg-orange-100 text-orange-600 text-xs px-2 py-0.5 rounded-full">{{ $intencionCompra->total() }}</span>
                        </a>
                        <a href="{{ route('admin.index', ['tab' => 'intencion_intercambio']) }}"
                           class="px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                                  {{ $tab === 'intencion_intercambio' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Intención de Intercambio
                            <span class="ml-1.5 bg-pink-100 text-pink-600 text-xs px-2 py-0.5 rounded-full">{{ $intencionIntercambio->total() }}</span>
                        </a>
                        <a href="{{ route('admin.index', ['tab' => 'envio']) }}"
                           class="px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                                  {{ $tab === 'envio' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Envío
                        </a>
                        <a href="{{ route('admin.index', ['tab' => 'intercambios_confirmados']) }}"
                           class="px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                                  {{ $tab === 'intercambios_confirmados' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            🤝 Trabajo y Envíos
                            @if(isset($intercambiosConfirmados) && $intercambiosConfirmados->total() > 0)
                            <span class="ml-1.5 bg-emerald-100 text-emerald-700 text-xs px-2 py-0.5 rounded-full font-bold">{{ $intercambiosConfirmados->total() }}</span>
                            @endif
                        </a>
                    </nav>
                       {{-- Filtros --}}
                @if($tab !== 'envio')
                <div class="p-4 border-b border-gray-50 bg-gray-50">
                    <form method="GET" action="{{ route('admin.index') }}" class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        
                        <div class="flex-1">
                            <input type="text" name="buscar" value="{{ request('buscar') }}"
                                   placeholder="Buscar..."
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        @if($tab === 'compras')
                        <div>
                            <select name="estatus" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Todos los estados</option>
                                @foreach($estadosCompra as $e)
                                    <option value="{{ $e }}" {{ request('estatus') === $e ? 'selected' : '' }}>{{ ucfirst($e) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @elseif($tab === 'intercambios')
                        <div>
                            <select name="estatus" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Todos los estados</option>
                                @foreach($estadosIntercambio as $e)
                                    <option value="{{ $e }}" {{ request('estatus') === $e ? 'selected' : '' }}>{{ ucfirst($e) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        @if($tab !== 'intencion_compra')
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Desde:</span>
                            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Hasta:</span>
                            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        @endif

                        <div class="flex gap-2">
                            <button type="submit" class="bg-primary hover:bg-hoverPrimary text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                                Filtrar
                            </button>
                            @if(request('buscar') || request('estatus') || request('fecha_desde') || request('fecha_hasta'))
                            <a href="{{ route('admin.index', ['tab' => $tab]) }}"
                               class="border border-gray-200 text-gray-600 hover:bg-gray-100 px-5 py-2 rounded-lg text-sm font-medium transition-colors text-center flex items-center justify-center">
                                Limpiar
                            </a>
                            @endif
                        </div>
                    </form>
                </div>
                @endif

                {{-- Contenido del tab activo --}}
                @if($tab === 'compras')
                    @include('admin.partials.tabla-compras', ['compras' => $compras])
                @elseif($tab === 'ventas')
                    @include('admin.partials.tabla-ventas', ['ventas' => $ventas])
                @elseif($tab === 'intercambios')
                    @include('admin.partials.tabla-intercambios', ['intercambios' => $intercambios])
                @elseif($tab === 'intencion_compra')
                    @include('admin.partials.tabla-intencion-compra', ['intencionCompra' => $intencionCompra])
                @elseif($tab === 'intencion_intercambio')
                    @include('admin.partials.tabla-intencion-intercambio', ['intencionIntercambio' => $intencionIntercambio])
                @elseif($tab === 'intercambios_confirmados')
                    @include('admin.partials.tabla-intercambios-confirmados', [
                        'intercambiosConfirmados' => $intercambiosConfirmados,
                        'ventasConfirmadas'        => $ventasConfirmadas
                    ])
                @elseif($tab === 'envio')
                    <div id="envio-tab-content" class="p-5">
                        <p class="text-xs text-gray-400 mb-4">Tarifas y porcentajes actuales de envío. Para modificarlos, ve a Estadísticas.</p>
                        <div id="envio-zonas" class="text-sm text-gray-500">Cargando...</div>
                    </div>
                @endif

            </div>{{-- /card --}}
            @endif

        </div>{{-- /mainContent --}}
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('pageLoader').classList.add('hidden');
        document.getElementById('mainContent').classList.remove('hidden');

        @if(session('download_pdf_url'))
            window.open("{{ session('download_pdf_url') }}", "_blank");
        @endif

        @if($tab === 'envio')
        // Cargar zonas de envío (solo lectura)
        Promise.all([
            fetch('/api/delivery/zonas').then(r => r.json()),
            fetch('/api/delivery/config').then(r => r.json())
        ]).then(([zonas, config]) => {
            const el = document.getElementById('envio-zonas');
            if (!el) return;
            const colores = {corta:'#10b981', larga:'#3b82f6', especial:'#f59e0b', chequeado:'#8b5cf6'};
            const cfgMap = {};
            if (config.success && config.data) {
                config.data.forEach(c => { cfgMap[c.clave] = c; });
            }
            const zonasData = zonas.success ? zonas.data : [];
            if (!zonasData.length) { el.innerHTML = '<p class="text-gray-400">Sin datos.</p>'; return; }

            // Group by tipo
            const grupos = {};
            zonasData.forEach(z => {
                if (!grupos[z.tipo]) grupos[z.tipo] = [];
                grupos[z.tipo].push(z);
            });
            const claveLabel = {corta:'Rutas cortas', larga:'Rutas largas', especial:'Rutas especiales', chequeado:'Bultos chequeados'};

            let html = '';
            Object.entries(grupos).forEach(([tipo, zs]) => {
                const c = colores[tipo] || '#64748b';
                const cfg = cfgMap[tipo + 's'] || (tipo === 'chequeado' ? cfgMap['chequeados'] : null) || cfgMap[tipo] || null;
                html += `<div style="margin-bottom:20px;">
                    <div style="font-size:.8rem;font-weight:700;color:${c};text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;padding-bottom:4px;border-bottom:2px solid ${c}22;">
                        ${claveLabel[tipo] || tipo}
                        ${cfg ? `<span style="font-weight:400;color:#64748b;margin-left:8px;">Ganancia: ${cfg.porcentaje}% | Plataforma: ${cfg.porcentaje_plataforma}% | Seguro: ${cfg.porcentaje_seguro}% | Manejo: ${cfg.porcentaje_manejo}%</span>` : ''}
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px;">
                        ${zs.map(z => `
                        <div style="padding:10px 12px;background:#f8fafc;border-radius:8px;border-left:3px solid ${c};">
                            <div style="font-size:.82rem;font-weight:600;color:#1e293b;">${z.zona}</div>
                            <div style="font-size:.75rem;color:#64748b;margin-top:2px;">Base persona: RD$ ${new Intl.NumberFormat('es-DO', { minimumFractionDigits: 2 }).format(z.precio_persona || 0)}</div>
                            <div style="font-size:.75rem;color:#64748b;">Base empresa: RD$ ${new Intl.NumberFormat('es-DO', { minimumFractionDigits: 2 }).format(z.precio_empresa || 0)}</div>
                            <div style="font-size:.7rem;color:#94a3b8;margin-top:3px;">${z.dias_entrega||''}</div>
                        </div>`).join('')}
                    </div>
                </div>`;
            });
            el.innerHTML = html;
        }).catch(() => {
            const el = document.getElementById('envio-zonas');
            if (el) el.innerHTML = '<p class="text-red-400 text-sm">Error al cargar datos de envío.</p>';
        });
        @endif
    });
</script>
@endpush
