@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb & Regresar -->
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ url('/tu-cuenta') }}" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> 
                Volver a Tu Cuenta
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Mi Billetera</h1>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Columna Izquierda: Balance y Retiro -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Tarjeta de Balance -->
                <div class="rounded-2xl shadow-lg p-6 relative overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a, #1d4ed8); color: white;">
                    <div class="absolute top-0 right-0 opacity-10">
                        <svg class="w-48 h-48 -mr-10 -mt-10" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div class="relative z-10">
                        <p class="text-blue-100 text-sm font-semibold uppercase tracking-wider mb-1">Balance Disponible</p>
                        <h2 class="text-4xl font-black mb-4 text-white">RD$ {{ number_format($balanceDisponible, 2) }}</h2>
                        <p class="text-xs text-blue-200 leading-tight">Este balance refleja tus ventas entregadas menos los retiros en proceso.</p>
                    </div>
                </div>

                <!-- Formulario Solicitar Retiro -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> 
                        Solicitar Pago
                    </h3>
                    
                    <form action="{{ route('billetera.retiros.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cuenta Destino</label>
                            <select name="id_cuenta_bancaria" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                                @foreach($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}">
                                        {{ $cuenta->banco }} - {{ substr($cuenta->numero_cuenta, -4) }} ({{ $cuenta->nombre_titular }})
                                    </option>
                                @endforeach
                            </select>
                            @if($cuentas->isEmpty())
                                <p class="text-xs text-red-500 mt-1">Debes agregar una cuenta bancaria primero.</p>
                            @endif
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monto a Retirar (RD$)</label>
                            <input type="number" name="monto" step="0.01" min="500" max="{{ $balanceDisponible > 500 ? $balanceDisponible : '' }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="Ej. 1500.00" required>
                            <p class="text-xs text-gray-500 mt-1">Monto mínimo: RD$ 500.00</p>
                        </div>

                        <button type="submit" 
                            class="w-full font-bold py-3 px-4 rounded-lg transition-colors
                                {{ $balanceDisponible >= 500 && $cuentas->isNotEmpty() ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-200' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                            {{ $balanceDisponible < 500 || $cuentas->isEmpty() ? 'disabled' : '' }}>
                            @if($cuentas->isEmpty())
                                Agrega una cuenta
                            @elseif($balanceDisponible < 500)
                                Balance Insuficiente
                            @else
                                Solicitar Transferencia
                            @endif
                        </button>
                    </form>
                </div>
            </div>

            <!-- Columna Derecha: Cuentas e Historial -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Mis Cuentas Bancarias -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg> 
                            Mis Cuentas Bancarias
                        </h3>
                        <button onclick="document.getElementById('modal-agregar-cuenta').classList.remove('hidden')" class="text-sm bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            + Agregar Cuenta
                        </button>
                    </div>

                    @if($cuentas->isEmpty())
                        <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            Aún no has agregado ninguna cuenta bancaria.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($cuentas as $cuenta)
                                <div class="border border-gray-100 rounded-xl p-4 bg-gray-50 relative group">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-bold text-gray-900">{{ $cuenta->banco }}</p>
                                            <p class="text-sm text-gray-600 tracking-widest font-mono mt-1">{{ str_pad(substr($cuenta->numero_cuenta, -4), strlen($cuenta->numero_cuenta), "*", STR_PAD_LEFT) }}</p>
                                            <p class="text-xs text-gray-500 mt-2 uppercase">{{ $cuenta->tipo_cuenta }} &bull; {{ $cuenta->nombre_titular }}</p>
                                        </div>
                                        <form action="{{ route('billetera.cuentas.destroy', $cuenta->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta cuenta?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600 p-1">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Historial de Retiros -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> 
                        Historial de Retiros
                    </h3>
                    
                    @if($retiros->count() == 0)
                        <div class="text-center py-8 text-gray-500">
                            No has realizado ninguna solicitud de retiro aún.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-100">
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Fecha</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Monto</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Cuenta</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($retiros as $retiro)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $retiro->created_at->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm font-bold text-gray-900">RD$ {{ number_format($retiro->monto, 2) }}</td>
                                        <td class="px-4 py-3 text-xs text-gray-600">
                                            @if($retiro->cuentaBancaria)
                                                {{ $retiro->cuentaBancaria->banco }} (..{{ substr($retiro->cuentaBancaria->numero_cuenta, -4) }})
                                            @else
                                                Cuenta Eliminada
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($retiro->estado == 'pendiente' || $retiro->estado == 'procesando')
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">Procesando</span>
                                            @elseif($retiro->estado == 'pagado')
                                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Pagado</span>
                                                @if($retiro->comprobante_url)
                                                    <a href="{{ $retiro->comprobante_url }}" target="_blank" class="block text-xs text-blue-600 hover:underline mt-1">Ver Comprobante</a>
                                                @endif
                                            @elseif($retiro->estado == 'rechazado')
                                                <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full" title="{{ $retiro->notas }}">Rechazado</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Cuenta -->
<div id="modal-agregar-cuenta" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="bg-gray-900 px-6 py-4 flex justify-between items-center">
            <h3 class="text-white font-bold text-lg">Agregar Cuenta Bancaria</h3>
            <button onclick="document.getElementById('modal-agregar-cuenta').classList.add('hidden')" class="text-gray-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="{{ route('billetera.cuentas.store') }}" method="POST" class="p-6">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Banco</label>
                    <select name="banco" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                        <option value="">Selecciona un banco...</option>
                        <optgroup label="Bancos Múltiples">
                            <option value="Banreservas">Banreservas</option>
                            <option value="Scotiabank República Dominicana">Scotiabank República Dominicana</option>
                            <option value="Citibank, N.A.">Citibank, N.A.</option>
                            <option value="Banco Popular Dominicano">Banco Popular Dominicano</option>
                            <option value="Banco BHD">Banco BHD</option>
                            <option value="Banco Santa Cruz">Banco Santa Cruz</option>
                            <option value="Banco Caribe">Banco Caribe</option>
                            <option value="Banco BDI">Banco BDI</option>
                            <option value="Banco Vimenca">Banco Vimenca</option>
                            <option value="Banco López de Haro">Banco López de Haro</option>
                            <option value="Banco Promerica">Banco Promerica</option>
                            <option value="Banesco Banco Múltiple">Banesco Banco Múltiple</option>
                            <option value="Banco Ademi">Banco Ademi</option>
                            <option value="Banco Lafise">Banco Lafise</option>
                            <option value="JMMB Bank">JMMB Bank</option>
                            <option value="Qik Banco Digital">Qik Banco Digital</option>
                            <option value="Banco Múltiple Activo Dominicana">Banco Múltiple Activo Dominicana</option>
                        </optgroup>
                        <optgroup label="Asociaciones de ahorro y préstamos">
                            <option value="Asociación Popular de Ahorros y Préstamos (APAP)">Asociación Popular de Ahorros y Préstamos (APAP)</option>
                            <option value="Asociación Cibao de Ahorros y Préstamos">Asociación Cibao de Ahorros y Préstamos</option>
                            <option value="Asociación Peravia">Asociación Peravia</option>
                            <option value="Asociación La Vega Real (ALAVER)">Asociación La Vega Real (ALAVER)</option>
                            <option value="Asociación Mocana">Asociación Mocana</option>
                            <option value="Asociación Duarte">Asociación Duarte</option>
                            <option value="Asociación Bonao">Asociación Bonao</option>
                            <option value="Asociación La Nacional">Asociación La Nacional</option>
                        </optgroup>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Tipo de Cuenta</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="tipo_cuenta" value="ahorro" class="text-blue-600" required> Ahorro
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="tipo_cuenta" value="corriente" class="text-blue-600" required> Corriente
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Número de Cuenta</label>
                    <input type="text" name="numero_cuenta" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Ej. 123456789" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nombre del Titular</label>
                    <input type="text" name="titular" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Tal como aparece en el banco" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Cédula del Titular</label>
                    <input type="text" name="cedula_titular" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="000-0000000-0" required>
                </div>
            </div>
            <div class="mt-8 flex gap-3">
                <button type="button" onclick="document.getElementById('modal-agregar-cuenta').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-200 transition">Cancelar</button>
                <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition">Guardar Cuenta</button>
            </div>
        </form>
    </div>
</div>

@endsection
