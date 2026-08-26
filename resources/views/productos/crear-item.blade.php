@extends('layouts.app')

@section('content')
<div class="container py-5">
    @include('components.btn-volver', ['backUrl' => route('items.user')])
    <h1 class="mb-4">Crear Nuevo Item</h1>
    
    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Información básica -->
                        <div class="mb-3">
                            <label for="item" class="form-label d-flex align-items-center gap-1">Nombre del Item<span class="text-danger ms-1">*</span></label>
                            <input type="text" class="form-control" id="item" name="item" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="id_categoria_item" class="form-label d-flex align-items-center gap-1">Categoría<span class="text-danger ms-1">*</span></label>
                            <select class="form-select" id="id_categoria_item" name="id_categoria_item" required>
                                <option value="">Seleccione una categoría</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id_categoria_item }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tipo" class="form-label d-flex align-items-center gap-1">Tipo<span class="text-danger ms-1">*</span></label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="">Seleccione un tipo</option>
                                <option value="nuevo">Nuevo</option>
                                <option value="usado">Usado</option>
                                <option value="reacondicionado">Reacondicionado</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="valor" class="form-label d-flex align-items-center gap-1">Valor<span class="text-danger ms-1">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="valor" name="valor" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Detalles adicionales -->
                        <div class="mb-3">
                            <label for="presentacion" class="form-label d-flex align-items-center gap-1">Presentación</label>
                            <textarea class="form-control" id="presentacion" name="presentacion" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="condicion" class="form-label d-flex align-items-center gap-1">Condición</label>
                            <input type="text" class="form-control" id="condicion" name="condicion">
                        </div>
                        
                        <div class="mb-3">
                            <label for="tipo_trans" class="form-label">Tipo de Transacción</label>
                            <select class="form-select" id="tipo_trans" name="tipo_trans">
                                <option value="venta">Venta</option>
                                <option value="trueque">Trueque</option>
                                <option value="ambos">Venta o Trueque</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Dimensiones y peso -->
                <h5 class="mt-4 mb-3">Dimensiones y Peso</h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="peso_lbs" class="form-label">Peso (lbs)</label>
                            <input type="number" class="form-control" id="peso_lbs" name="peso_lbs" step="0.01" min="0" value="1">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="alto_cm" class="form-label">Alto (cm)</label>
                            <input type="number" class="form-control" id="alto_cm" name="alto_cm" step="0.1" min="0" value="1">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="ancho_cm" class="form-label">Ancho (cm)</label>
                            <input type="number" class="form-control" id="ancho_cm" name="ancho_cm" step="0.1" min="0" value="1">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="profundo_cm" class="form-label">Profundidad (cm)</label>
                            <input type="number" class="form-control" id="profundo_cm" name="profundo_cm" step="0.1" min="0" value="1">
                        </div>
                    </div>
                </div>
                
                <!-- Imágenes -->
                <h5 class="mt-4 mb-3">Imágenes del Producto</h5>
                <div class="mb-3">
                    <label for="imagenes" class="form-label">Subir imágenes (Máx. 5)</label>
                    <input type="file" class="form-control" id="imagenes" name="imagenes[]" multiple accept="image/*">
                    <small class="text-muted">Formatos aceptados: JPEG, PNG, JPG, GIF. Tamaño máximo: 2MB por imagen.</small>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button type="submit" class="btn btn-primary">Guardar Item</button>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
