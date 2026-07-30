@extends('layouts.admin')

@section('title', 'Panel de Administración')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        <div class="mb-5">
            <h1 class="text-2xl font-bold text-gray-900">Resumen de Operaciones</h1>
            <p class="text-sm text-gray-500 mt-1">Monitorea en tiempo real tus compras, ventas e intercambios.</p>
        </div>

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
                    <form method="GET" action="{{ route('admin.index') }}" class="flex flex-col gap-4">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        
                        {{-- Row 1: Buscar --}}
                        <div class="w-full">
                            <input type="text" name="buscar" value="{{ request('buscar') }}"
                                   placeholder="Buscar..."
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        {{-- Row 2: Filtros y Botones --}}
                        <div class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center flex-wrap">

                        @if(in_array($tab, ['compras', 'ventas']))
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

                        <div class="flex items-center gap-2 min-w-[150px]">
                            <select name="provincia" id="provincia-filter" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Todas las provincias</option>
                                @foreach($provincias ?? [] as $prov)
                                    <option value="{{ $prov->id_provincia }}" {{ request('provincia') == $prov->id_provincia ? 'selected' : '' }}>
                                        {{ $prov->provincia }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-2 min-w-[150px]">
                            <select name="municipio" id="municipio-filter" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" {{ request('provincia') ? '' : 'disabled' }}>
                                <option value="">Todos los municipios</option>
                                @if(request('provincia') && isset($provincias))
                                    @php
                                        $provinciaObj = collect($provincias)->firstWhere('id_provincia', request('provincia'));
                                    @endphp
                                    @if($provinciaObj)
                                        @foreach($provinciaObj->municipios as $mun)
                                            <option value="{{ $mun->id_municipio }}" {{ request('municipio') == $mun->id_municipio ? 'selected' : '' }}>
                                                {{ $mun->municipio }}
                                            </option>
                                        @endforeach
                                    @endif
                                @endif
                            </select>
                        </div>

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
                            @if(request('buscar') || request('estatus') || request('fecha_desde') || request('fecha_hasta') || request('provincia') || request('municipio'))
                            <a href="{{ route('admin.index', ['tab' => $tab]) }}"
                               class="border border-gray-200 text-gray-600 hover:bg-gray-100 px-5 py-2 rounded-lg text-sm font-medium transition-colors text-center flex items-center justify-center">
                                Limpiar
                            </a>
                            @endif
                        </div>
                        </div> {{-- /Row 2 --}}
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

        // Lógica para el filtro de provincia y municipio
        const provinciaSelect = document.getElementById('provincia-filter');
        const municipioSelect = document.getElementById('municipio-filter');
        const provinciasData = @json($provincias ?? []);

        if (provinciaSelect && municipioSelect) {
            provinciaSelect.addEventListener('change', function() {
                const provId = this.value;
                municipioSelect.innerHTML = '<option value="">Todos los municipios</option>';
                
                if (!provId) {
                    municipioSelect.disabled = true;
                    return;
                }

                municipioSelect.disabled = false;
                const selectedProv = provinciasData.find(p => p.id_provincia == provId);
                
                if (selectedProv && selectedProv.municipios) {
                    selectedProv.municipios.forEach(mun => {
                        const option = document.createElement('option');
                        option.value = mun.id_municipio;
                        option.textContent = mun.municipio;
                        // No need to set selected here since this is a dynamic change triggered by user
                        municipioSelect.appendChild(option);
                    });
                }
            });
        }

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
