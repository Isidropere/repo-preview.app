@extends('layouts.app')

@section('title', 'Contabilidad - Panel ERP')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Módulo de Contabilidad</h1>
                <p class="text-sm text-gray-500 mt-1">Gestión de catálogo de cuentas y asientos de diario.</p>
            </div>
            <div>
                <a href="{{ route('admin.erp.contabilidad.reportes') }}" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-hoverPrimary transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Reportes Financieros
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Catálogo de Cuentas --}}
            <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Catálogo de Cuentas</h2>
                    <button onclick="abrirModalCuenta()" class="text-primary hover:text-hoverPrimary text-sm font-semibold flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Nueva
                    </button>
                </div>
                <div class="space-y-2 overflow-y-auto max-h-[600px] pr-2">
                    @foreach($cuentas as $cuenta)
                        <div class="p-2 hover:bg-gray-50 rounded-lg border border-transparent hover:border-gray-100 transition-all cursor-pointer group">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-mono text-gray-500">{{ $cuenta->codigo }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $cuenta->tipo === 'activo' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ ucfirst($cuenta->tipo) }}
                                    </span>
                                    <div class="hidden group-hover:flex items-center gap-1">
                                        <button onclick="editarCuenta({{ $cuenta }})" class="text-gray-400 hover:text-primary"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                        <button onclick="eliminarCuenta({{ $cuenta->id }})" class="text-gray-400 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        <a href="{{ route('admin.erp.contabilidad.cuentas.mayor', $cuenta->id) }}" title="Ver Libro Mayor" class="text-gray-400 hover:text-blue-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></a>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm font-semibold text-gray-800 mt-1">{{ $cuenta->nombre }}</p>
                            
                            @if($cuenta->hijos->count() > 0)
                                <div class="ml-4 mt-2 space-y-1 border-l-2 border-gray-100 pl-3">
                                    @foreach($cuenta->hijos as $hijo)
                                        <div class="text-xs py-1 group/hijo flex justify-between items-center">
                                            <a href="{{ route('admin.erp.contabilidad.cuentas.mayor', $hijo->id) }}" class="text-gray-600 hover:text-primary transition-colors flex-1">
                                                <span class="font-mono text-gray-400">{{ $hijo->codigo }}</span> {{ $hijo->nombre }}
                                            </a>
                                            <div class="hidden group-hover/hijo:flex items-center gap-1">
                                                <button onclick="editarCuenta({{ $hijo }})" class="text-gray-400 hover:text-primary"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                                <button onclick="eliminarCuenta({{ $hijo->id }})" class="text-gray-400 hover:text-red-500"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Libro Diario --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800">Entradas de Diario</h2>
                    <button onclick="document.getElementById('modalNuevoAsiento').style.display='flex'" 
                        class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-hoverPrimary transition-all">
                        + Nuevo Asiento
                    </button>
                </div>
...
{{-- Modal Nuevo Asiento --}}
<div id="modalNuevoAsiento" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-4xl w-full mx-4 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900">Nueva Entrada de Diario</h3>
            <button onclick="document.getElementById('modalNuevoAsiento').style.display='none'" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('admin.erp.contabilidad.asiento') }}" method="POST" id="formAsiento">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha</label>
                    <input type="date" name="fecha" value="{{ date('Y-m-d') }}" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Concepto General</label>
                    <input type="text" name="concepto" required placeholder="Ej: Pago de renta, Ajuste de inventario..."
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20">
                </div>
            </div>

            <div class="overflow-x-auto mb-6">
                <table class="w-full text-left" id="tablaDetalles">
                    <thead>
                        <tr class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                            <th class="pb-3 w-1/2">Cuenta Contable</th>
                            <th class="pb-3 text-right">Debe</th>
                            <th class="pb-3 text-right">Haber</th>
                            <th class="pb-3"></th>
                        </tr>
                    </thead>
                    <tbody id="filasDetalles">
                        <tr class="detalle-row">
                            <td class="py-2 pr-4">
                                <select name="detalles[0][id_cuenta]" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                                    <option value="">Seleccionar cuenta...</option>
                                    @foreach($todasCuentas as $cta)
                                        <option value="{{ $cta->id }}">{{ $cta->codigo }} - {{ $cta->nombre }}</p>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-2 px-2">
                                <input type="number" name="detalles[0][debe]" value="0.00" step="0.01" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm text-right font-mono input-monto input-debe">
                            </td>
                            <td class="py-2 px-2">
                                <input type="number" name="detalles[0][haber]" value="0.00" step="0.01" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm text-right font-mono input-monto input-haber">
                            </td>
                            <td class="py-2 pl-4">
                                <button type="button" class="text-red-400 hover:text-red-600 transition-colors remove-row">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                        <tr class="detalle-row">
                            <td class="py-2 pr-4">
                                <select name="detalles[1][id_cuenta]" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                                    <option value="">Seleccionar cuenta...</option>
                                    @foreach($todasCuentas as $cta)
                                        <option value="{{ $cta->id }}">{{ $cta->codigo }} - {{ $cta->nombre }}</p>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-2 px-2">
                                <input type="number" name="detalles[1][debe]" value="0.00" step="0.01" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm text-right font-mono input-monto input-debe">
                            </td>
                            <td class="py-2 px-2">
                                <input type="number" name="detalles[1][haber]" value="0.00" step="0.01" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm text-right font-mono input-monto input-haber">
                            </td>
                            <td class="py-2 pl-4">
                                <button type="button" class="text-red-400 hover:text-red-600 transition-colors remove-row">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="font-black text-gray-800 bg-gray-50 border-t-2 border-gray-200">
                            <td class="py-3 px-4 text-sm uppercase">Total Cuadre</td>
                            <td class="py-3 px-2 text-right font-mono text-sm" id="totalDebe">0.00</td>
                            <td class="py-3 px-2 text-right font-mono text-sm" id="totalHaber">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex justify-between items-center">
                <button type="button" onclick="agregarFila()" class="text-primary hover:text-hoverPrimary text-sm font-bold flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Agregar Cuenta
                </button>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modalNuevoAsiento').style.display='none'"
                        class="px-6 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition-all">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-primary text-white px-8 py-2.5 rounded-xl font-bold hover:bg-hoverPrimary shadow-lg shadow-primary/20 transition-all disabled:opacity-50" id="btnGuardar">
                        Guardar Asiento
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    let rowCount = 2;

    function agregarFila() {
        const tbody = document.getElementById('filasDetalles');
        const newRow = document.createElement('tr');
        newRow.className = 'detalle-row';
        newRow.innerHTML = `
            <td class="py-2 pr-4">
                <select name="detalles[${rowCount}][id_cuenta]" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="">Seleccionar cuenta...</option>
                    @foreach($todasCuentas as $cta)
                        <option value="{{ $cta->id }}">{{ $cta->codigo }} - {{ $cta->nombre }}</option>
                    @endforeach
                </select>
            </td>
            <td class="py-2 px-2">
                <input type="number" name="detalles[${rowCount}][debe]" value="0.00" step="0.01" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm text-right font-mono input-monto input-debe">
            </td>
            <td class="py-2 px-2">
                <input type="number" name="detalles[${rowCount}][haber]" value="0.00" step="0.01" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm text-right font-mono input-monto input-haber">
            </td>
            <td class="py-2 pl-4">
                <button type="button" class="text-red-400 hover:text-red-600 transition-colors remove-row">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </td>
        `;
        tbody.appendChild(newRow);
        rowCount++;
        vincularEventos();
    }

    function vincularEventos() {
        document.querySelectorAll('.input-monto').forEach(input => {
            input.oninput = recalcularTotales;
        });
        document.querySelectorAll('.remove-row').forEach(btn => {
            btn.onclick = function() {
                if (document.querySelectorAll('.detalle-row').length > 2) {
                    this.closest('tr').remove();
                    recalcularTotales();
                }
            };
        });
    }

    function recalcularTotales() {
        let totalDebe = 0;
        let totalHaber = 0;

        document.querySelectorAll('.input-debe').forEach(i => totalDebe += parseFloat(i.value || 0));
        document.querySelectorAll('.input-haber').forEach(i => totalHaber += parseFloat(i.value || 0));

        document.getElementById('totalDebe').innerText = totalDebe.toFixed(2);
        document.getElementById('totalHaber').innerText = totalHaber.toFixed(2);

        const btn = document.getElementById('btnGuardar');
        if (Math.abs(totalDebe - totalHaber) < 0.01 && totalDebe > 0) {
            btn.disabled = false;
            document.getElementById('totalDebe').classList.add('text-emerald-600');
            document.getElementById('totalHaber').classList.add('text-emerald-600');
        } else {
            btn.disabled = true;
            document.getElementById('totalDebe').classList.remove('text-emerald-600');
            document.getElementById('totalHaber').classList.remove('text-emerald-600');
        }
    }

    vincularEventos();
</script>
                <div class="p-4 bg-gray-50 border-b border-gray-100">
                    <form action="{{ route('admin.erp.contabilidad') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Desde</label>
                            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Hasta</label>
                            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Concepto</label>
                            <input type="text" name="concepto" value="{{ request('concepto') }}" placeholder="Buscar en concepto..." class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div class="w-32">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Ref ID</label>
                            <input type="number" name="referencia_id" value="{{ request('referencia_id') }}" placeholder="ID" class="w-full px-3 py-1.5 rounded-lg border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-gray-800 text-white px-4 py-1.5 rounded-lg text-sm font-semibold hover:bg-gray-900 transition-colors">Filtrar</button>
                            <a href="{{ route('admin.erp.contabilidad') }}" class="bg-gray-200 text-gray-700 px-4 py-1.5 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors">Limpiar</a>
                        </div>
                    </form>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase border-b">Fecha</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase border-b">Concepto</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-right border-b">Debe</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-right border-b">Haber</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-center border-b">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($asientos as $asiento)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $asiento->fecha }}</td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-800">{{ $asiento->concepto }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">Ref: {{ $asiento->referencia_tipo }} #{{ $asiento->referencia_id }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right font-mono text-gray-600">{{ number_format($asiento->total_debe, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-mono text-gray-600">{{ number_format($asiento->total_haber, 2) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <button onclick="verDetalleAsiento({{ $asiento->id }})" class="text-primary hover:text-hoverPrimary text-sm font-semibold flex items-center justify-center gap-1 w-full">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg> Ver
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                        No hay asientos registrados aún.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($asientos->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $asientos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- Modal Crear/Editar Cuenta -->
<div id="modalCuenta" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900" id="modalCuentaTitulo">Nueva Cuenta</h3>
            <button onclick="cerrarModalCuenta()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="formCuenta" method="POST" action="{{ route('admin.erp.contabilidad.cuentas.store') }}">
            @csrf
            <input type="hidden" name="_method" id="metodoCuenta" value="POST">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Código</label>
                    <input type="text" name="codigo" id="cuentaCodigo" required class="w-full px-3 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="nombre" id="cuentaNombre" required class="w-full px-3 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo</label>
                        <select name="tipo" id="cuentaTipo" required class="w-full px-3 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="activo">Activo</option>
                            <option value="pasivo">Pasivo</option>
                            <option value="capital">Capital</option>
                            <option value="ingreso">Ingreso</option>
                            <option value="gasto">Gasto</option>
                            <option value="costo">Costo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nivel</label>
                        <input type="number" name="nivel" id="cuentaNivel" value="1" min="1" required class="w-full px-3 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Cuenta Padre (Opcional)</label>
                    <select name="id_padre" id="cuentaPadre" class="w-full px-3 py-2 rounded-lg border border-gray-200 outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="">Ninguna (Cuenta Principal)</option>
                        @foreach($cuentas as $cta)
                            <option value="{{ $cta->id }}">{{ $cta->codigo }} - {{ $cta->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <input type="hidden" name="permite_movimiento" value="0">
                    <input type="checkbox" name="permite_movimiento" id="cuentaMovimiento" value="1" checked class="w-4 h-4 text-primary rounded border-gray-300">
                    <label for="cuentaMovimiento" class="text-sm font-medium text-gray-700">Permite movimientos (Cuenta de detalle)</label>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="cerrarModalCuenta()" class="px-4 py-2 text-gray-600 font-semibold hover:bg-gray-100 rounded-lg">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white font-semibold rounded-lg hover:bg-hoverPrimary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="formEliminarCuenta" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Modal Detalles de Asiento -->
<div id="modalDetalleAsiento" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-3xl w-full mx-4 shadow-2xl">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <div>
                <h3 class="text-xl font-bold text-gray-900" id="detalleTitulo">Detalle de Asiento</h3>
                <p class="text-sm text-gray-500 mt-1" id="detalleSubtitulo"></p>
            </div>
            <button onclick="document.getElementById('modalDetalleAsiento').style.display='none'" class="text-gray-400 hover:text-gray-600 bg-gray-100 p-2 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="overflow-x-auto mb-4">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-y border-gray-200">
                    <tr>
                        <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase">Cuenta</th>
                        <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase text-right">Debe</th>
                        <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase text-right">Haber</th>
                    </tr>
                </thead>
                <tbody id="detalleCuerpo" class="divide-y divide-gray-100">
                    <!-- Contenido dinámico -->
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200 font-bold">
                    <tr>
                        <td class="py-3 px-4 text-right text-sm">TOTALES</td>
                        <td class="py-3 px-4 text-right font-mono text-emerald-600" id="detalleTotalDebe">0.00</td>
                        <td class="py-3 px-4 text-right font-mono text-emerald-600" id="detalleTotalHaber">0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="text-xs text-gray-400 text-right" id="detalleUsuario"></div>
    </div>
</div>

<script>
    function abrirModalCuenta() {
        document.getElementById('modalCuentaTitulo').innerText = 'Nueva Cuenta';
        document.getElementById('formCuenta').action = "{{ route('admin.erp.contabilidad.cuentas.store') }}";
        document.getElementById('metodoCuenta').value = 'POST';
        document.getElementById('formCuenta').reset();
        document.getElementById('modalCuenta').style.display = 'flex';
    }

    function editarCuenta(cuenta) {
        document.getElementById('modalCuentaTitulo').innerText = 'Editar Cuenta';
        document.getElementById('formCuenta').action = "/admin/erp/contabilidad/cuentas/" + cuenta.id;
        document.getElementById('metodoCuenta').value = 'PUT';
        
        document.getElementById('cuentaCodigo').value = cuenta.codigo;
        document.getElementById('cuentaNombre').value = cuenta.nombre;
        document.getElementById('cuentaTipo').value = cuenta.tipo;
        document.getElementById('cuentaNivel').value = cuenta.nivel;
        document.getElementById('cuentaPadre').value = cuenta.id_padre || '';
        document.getElementById('cuentaMovimiento').checked = cuenta.permite_movimiento == 1;
        
        document.getElementById('modalCuenta').style.display = 'flex';
    }

    function cerrarModalCuenta() {
        document.getElementById('modalCuenta').style.display = 'none';
    }

    function eliminarCuenta(id) {
        if(confirm('¿Estás seguro de que deseas eliminar esta cuenta?')) {
            const form = document.getElementById('formEliminarCuenta');
            form.action = "/admin/erp/contabilidad/cuentas/" + id;
            form.submit();
        }
    }

    function verDetalleAsiento(id) {
        fetch(`/admin/erp/contabilidad/asientos/${id}/detalle`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('detalleTitulo').innerText = `Asiento #${data.id}`;
                document.getElementById('detalleSubtitulo').innerText = `${data.fecha} - ${data.concepto}`;
                
                let tbody = '';
                data.detalles.forEach(d => {
                    let debe = parseFloat(d.debe).toFixed(2);
                    let haber = parseFloat(d.haber).toFixed(2);
                    tbody += `
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <p class="text-sm font-semibold text-gray-800">${d.cuenta.nombre}</p>
                                <p class="text-xs font-mono text-gray-500">${d.cuenta.codigo}</p>
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-sm text-gray-700">${debe > 0 ? debe : ''}</td>
                            <td class="py-3 px-4 text-right font-mono text-sm text-gray-700">${haber > 0 ? haber : ''}</td>
                        </tr>
                    `;
                });
                
                document.getElementById('detalleCuerpo').innerHTML = tbody;
                document.getElementById('detalleTotalDebe').innerText = parseFloat(data.total_debe).toFixed(2);
                document.getElementById('detalleTotalHaber').innerText = parseFloat(data.total_haber).toFixed(2);
                document.getElementById('detalleUsuario').innerText = `Registrado por: ${data.usuario ? data.usuario.nombres : 'Sistema'}`;
                
                document.getElementById('modalDetalleAsiento').style.display = 'flex';
            })
            .catch(err => {
                console.error(err);
                alert('Error al cargar los detalles del asiento.');
            });
    }
</script>
@endsection
