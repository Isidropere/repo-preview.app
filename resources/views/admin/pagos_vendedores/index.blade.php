@extends('layouts.admin')

@section('title', 'Pagos a Vendedores - Panel Admin')

@section('content')
<style>
    @media print {
        body * { visibility: hidden; }
        #print-area, #print-area * { visibility: visible; }
        #print-area { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
        .admin-sidebar { display: none !important; }
    }
</style>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @include('components.btn-volver', ['backUrl' => route('admin.index')])

        <div class="mb-6 flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pagos a Vendedores</h1>
                <p class="text-sm text-gray-500 mt-1">Gestión de retiros y transferencias de ganancias a los usuarios.</p>
            </div>
            <button onclick="window.print()" class="no-print bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2">
                <i class="fas fa-print"></i> Imprimir Lista
            </button>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 mb-6">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-6">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-6">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6" id="print-area">
            <div class="p-4 bg-gray-50 border-b border-gray-100 no-print">
                <form action="{{ route('admin.pagos_vendedores.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 w-full">
                    <div class="flex-1 flex flex-col">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Filtrar estado:</label>
                        <select name="estado" class="h-10 px-3 border border-gray-300 rounded-lg text-sm w-full focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>Todos</option>
                            <option value="pendiente" {{ request('estado', 'pendiente') == 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                            <option value="procesando" {{ request('estado') == 'procesando' ? 'selected' : '' }}>Procesando</option>
                            <option value="pagado" {{ request('estado') == 'pagado' ? 'selected' : '' }}>Pagados</option>
                            <option value="rechazado" {{ request('estado') == 'rechazado' ? 'selected' : '' }}>Rechazados</option>
                        </select>
                    </div>
                    <div class="flex-1 flex flex-col">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Fecha Inicio:</label>
                        <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="h-10 px-3 border border-gray-300 rounded-lg text-sm w-full focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div class="flex-1 flex flex-col">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Fecha Fin:</label>
                        <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="h-10 px-3 border border-gray-300 rounded-lg text-sm w-full focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div class="flex-1 flex flex-col">
                        <!-- Etiqueta invisible con las mismas clases exactas para igualar la altura -->
                        <label class="block text-xs font-bold text-transparent mb-1 select-none pointer-events-none">Filtro</label>
                        <button type="submit" class="h-10 px-4 bg-gray-800 text-white rounded-lg text-sm font-bold hover:bg-gray-900 w-full transition-colors">
                            Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">ID</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">Usuario / Vendedor</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">Monto</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">Cuenta Destino</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">Estado</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b text-center no-print">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($retiros as $retiro)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-bold text-gray-800">#{{ $retiro->id }}</td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-800">{{ $retiro->usuario->nombres }} {{ $retiro->usuario->apellidos }}</p>
                                    <p class="text-xs text-gray-500">{{ $retiro->usuario->email }}</p>
                                    <p class="text-xs text-gray-500">{{ $retiro->usuario->telefono }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-lg font-black text-green-700">RD$ {{ number_format($retiro->monto, 2) }}</span>
                                    <p class="text-xs text-gray-500">{{ $retiro->created_at->format('d/m/Y h:i A') }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    @if($retiro->cuentaBancaria)
                                        <p><strong>Banco:</strong> {{ $retiro->cuentaBancaria->banco }}</p>
                                        <p><strong>Cuenta:</strong> {{ $retiro->cuentaBancaria->numero_cuenta }} ({{ ucfirst($retiro->cuentaBancaria->tipo_cuenta) }})</p>
                                        <p><strong>Titular:</strong> {{ $retiro->cuentaBancaria->titular }}</p>
                                        <p><strong>Cédula:</strong> {{ $retiro->cuentaBancaria->cedula_titular }}</p>
                                    @else
                                        <span class="text-red-500 italic">Cuenta bancaria eliminada o no disponible</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($retiro->estado == 'pendiente')
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">Pendiente</span>
                                    @elseif($retiro->estado == 'pagado')
                                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Pagado</span>
                                        @if($retiro->comprobante_url)
                                            <a href="{{ $retiro->comprobante_url }}" target="_blank" class="block mt-2 text-xs text-blue-600 hover:underline">Ver Comprobante</a>
                                        @endif
                                    @elseif($retiro->estado == 'rechazado')
                                        <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">Rechazado</span>
                                        <p class="text-xs mt-1 text-red-500 max-w-[150px] truncate" title="{{ $retiro->notas }}">{{ $retiro->notas }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center no-print">
                                    @if($retiro->estado == 'pendiente' || $retiro->estado == 'procesando')
                                        <button type="button" onclick="document.getElementById('modal-pagar-{{ $retiro->id }}').classList.remove('hidden')" class="px-3 py-1 bg-green-600 text-white text-xs font-bold rounded hover:bg-green-700 mb-2 w-full">Registrar Pago</button>
                                        
                                        <button type="button" onclick="document.getElementById('modal-rechazar-{{ $retiro->id }}').classList.remove('hidden')" class="px-3 py-1 bg-red-100 text-red-600 text-xs font-bold rounded hover:bg-red-200 w-full">Rechazar</button>
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Modal Pagar -->
                            @if($retiro->estado == 'pendiente' || $retiro->estado == 'procesando')
                            <div id="modal-pagar-{{ $retiro->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                                <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 text-left">
                                    <h3 class="text-lg font-bold text-gray-900 mb-4">Registrar Transferencia / Pago</h3>
                                    <form action="{{ route('admin.pagos_vendedores.pagar', $retiro->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-4">
                                            <p class="text-sm text-gray-600 mb-2">Sube el comprobante de pago de la transferencia (Obligatorio).</p>
                                            <input type="file" name="comprobante" accept="image/*" required class="w-full text-sm">
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Referencia / Notas (Opcional)</label>
                                            <textarea name="notas" rows="2" class="w-full border border-gray-300 rounded p-2 text-sm"></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" onclick="document.getElementById('modal-pagar-{{ $retiro->id }}').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded font-bold text-sm">Cancelar</button>
                                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded font-bold text-sm">Confirmar Pago</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Modal Rechazar -->
                            <div id="modal-rechazar-{{ $retiro->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                                <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 text-left">
                                    <h3 class="text-lg font-bold text-red-600 mb-4">Rechazar Solicitud de Retiro</h3>
                                    <form action="{{ route('admin.pagos_vendedores.rechazar', $retiro->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-4">
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Motivo de Rechazo (Obligatorio)</label>
                                            <textarea name="notas" rows="3" required class="w-full border border-gray-300 rounded p-2 text-sm" placeholder="Ej: Cuenta bancaria inválida..."></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" onclick="document.getElementById('modal-rechazar-{{ $retiro->id }}').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded font-bold text-sm">Cancelar</button>
                                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded font-bold text-sm">Confirmar Rechazo</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endif

                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    No hay solicitudes de retiro en este estado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
</div>
@endsection
