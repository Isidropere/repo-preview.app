@extends('layouts.app')
@section('title', 'Cambiar tipo Usuario - Cambialord')
@section('content')

<div class="max-w-lg mx-auto mt-10 bg-white rounded-lg shadow-lg p-8">
    @include('components.btn-volver', ['backUrl' => route('tu_cuenta')])
    <h2 class="text-2xl font-semibold text-primary mb-6">Cambiar tipo de usuario</h2>

    <div class="mb-4">
        <span class="font-semibold text-gray-700">Tipo de usuario actual:</span>
        <span class="text-primary">
            {{ Auth::user()->tiposUsuario->tipo ?? 'No asignado' }}
        </span>
    </div>

    @if(session('success'))
        <div class="mb-4 text-green-600">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('usuario.tipo.update') }}">
        @csrf
        <div class="mb-4">
            <label for="id_tipo_usuario" class="block text-gray-700 mb-2">Selecciona tu tipo de usuario:</label>
            <select name="id_tipo_usuario" id="id_tipo_usuario" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-primary focus:ring-primary">
             @foreach($tipos as $tipo)
                    @if($tipo->id_tipo_usuario != 3)
                        <option value="{{ $tipo->id_tipo_usuario }}"
                            @if(Auth::user()->id_tipo_usuario == $tipo->id_tipo_usuario) selected @endif>
                            {{ $tipo->tipo }}
                        </option>
                    @endif
                @endforeach
            </select>
            @error('id_tipo_usuario')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-hoverPrimary transition-all">
            Guardar cambios
        </button>
    </form>
</div>
@endsection
