@extends('layouts.app')

@section('title', 'Panel de Administración')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        {{-- Encabezado --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Panel de Administración</h1>
                <p class="text-sm text-gray-500 mt-1">Gestiona compras, ventas e intercambios de la plataforma.</p>
            </div>
            @if(auth()->user()->isSuperAdminUser())
            <a href="{{ route('admin.stats.index') }}"
               class="inline-flex items-center gap-2 bg-primary hover:bg-hoverPrimary text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Estadísticas
            </a>
            @endif
            <a href="{{ route('admin.mensajes.index') }}"
               class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                Mensajes predefinidos
            </a>
            <a href="{{ route('admin.imagenes.index') }}"
               class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Aprobación de Imágenes
            </a>
            <a href="{{ route('admin.notificaciones.index') }}"
               class="inline-flex items-center gap-2 bg-secondary hover:bg-hoverSecondary text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                Notificaciones
            </a>
        </div>

        {{-- Accesos Rápidos ERP (Solo Super Admin) --}}
        @if(auth()->user()->isSuperAdminUser())
        <div class="mb-8">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Gestión Empresarial</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.erp.contabilidad') }}" 
                   class="flex items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border border-gray-100 hover:border-primary hover:shadow-md transition-all group">
                    <div class="bg-blue-50 p-3 rounded-xl text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18 18.247 18.477 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800">Contabilidad</p>
                        <p class="text-xs text-gray-400">Libro diario y cuentas</p>
                    </div>
                </a>
                <a href="{{ route('admin.erp.inventario') }}" 
                   class="flex items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border border-gray-100 hover:border-primary hover:shadow-md transition-all group">
                    <div class="bg-emerald-50 p-3 rounded-xl text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800">Inventario</p>
                        <p class="text-xs text-gray-400">Stock y Almacén</p>
                    </div>
                </a>
                <a href="{{ route('admin.erp.caja') }}" 
                   class="flex items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border border-gray-100 hover:border-primary hover:shadow-md transition-all group">
                    <div class="bg-orange-50 p-3 rounded-xl text-orange-600 group-hover:bg-orange-600 group-hover:text-white transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800">Cuadre de Caja</p>
                        <p class="text-xs text-gray-400">Apertura y cierre diario</p>
                    </div>
                </a>
                <a href="{{ route('admin.erp.transporte.index') }}" 
                   class="flex items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border border-gray-100 hover:border-primary hover:shadow-md transition-all group">
                    <div class="bg-indigo-50 p-3 rounded-xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4M5 17h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800">Transporte</p>
                        <p class="text-xs text-gray-400">Solicitudes de mudanza</p>
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

            {{-- KPIs --}}
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Compras</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($totalCompras, 0) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-blue-600 uppercase tracking-wide">Ventas</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($totalVentas, 0) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-purple-600 uppercase tracking-wide">Intercambios</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($totalIntercambios, 0) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-orange-500 uppercase tracking-wide">Int. Compra</p>
                    <p class="text-2xl font-bold text-orange-500 mt-1">{{ number_format($totalIntencionCompra, 0) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                    <p class="text-xs text-pink-500 uppercase tracking-wide">Int. Intercambio</p>
                    <p class="text-2xl font-bold text-pink-500 mt-1">{{ number_format($totalIntencionIntercambio, 0) }}</p>
                </div>
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
                            🤝 Intercambios Confirmados
                            @if(isset($intercambiosConfirmados) && $intercambiosConfirmados->total() > 0)
                            <span class="ml-1.5 bg-emerald-100 text-emerald-700 text-xs px-2 py-0.5 rounded-full font-bold">{{ $intercambiosConfirmados->total() }}</span>
                            @endif
                        </a>
                    </nav>
                </div>

                {{-- Filtros --}}
                @if($tab !== 'envio')
                <div class="p-4 border-b border-gray-50 bg-gray-50">
                    <form method="GET" action="{{ route('admin.index') }}" class="flex flex-col sm:flex-row gap-3">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                               placeholder="Buscar..."
                               class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">

                        @if($tab === 'compras')
                        <select name="estatus" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Todos los estados</option>
                            @foreach($estadosCompra as $e)
                                <option value="{{ $e }}" {{ request('estatus') === $e ? 'selected' : '' }}>{{ ucfirst($e) }}</option>
                            @endforeach
                        </select>
                        @elseif($tab === 'intercambios')
                        <select name="estatus" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Todos los estados</option>
                            @foreach($estadosIntercambio as $e)
                                <option value="{{ $e }}" {{ request('estatus') === $e ? 'selected' : '' }}>{{ ucfirst($e) }}</option>
                            @endforeach
                        </select>
                        @endif

                        <button type="submit" class="bg-primary hover:bg-hoverPrimary text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                            Filtrar
                        </button>
                        @if(request('buscar') || request('estatus'))
                        <a href="{{ route('admin.index', ['tab' => $tab]) }}"
                           class="border border-gray-200 text-gray-600 hover:bg-gray-100 px-5 py-2 rounded-lg text-sm font-medium transition-colors text-center">
                            Limpiar
                        </a>
                        @endif
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
                    @include('admin.partials.tabla-intercambios-confirmados', ['intercambiosConfirmados' => $intercambiosConfirmados])
                @elseif($tab === 'envio')
                    <div id="envio-tab-content" class="p-5">
                        <p class="text-xs text-gray-400 mb-4">Tarifas y porcentajes actuales de envío. Para modificarlos, ve a Estadísticas.</p>
                        <div id="envio-zonas" class="text-sm text-gray-500">Cargando...</div>
                    </div>
                @endif

            </div>{{-- /card --}}

        </div>{{-- /mainContent --}}
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('pageLoader').classList.add('hidden');
        document.getElementById('mainContent').classList.remove('hidden');

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
