<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MotivoDevolucion;

class AdminMotivosDevolucionController extends Controller
{
    public function index()
    {
        $motivos = MotivoDevolucion::orderBy('id', 'asc')->paginate(15);
        return view('admin.motivos_devolucion.index', compact('motivos'));
    }

    public function create()
    {
        return view('admin.motivos_devolucion.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'motivo' => 'required|string|max:255',
            'activo' => 'required|boolean',
        ]);

        MotivoDevolucion::create($request->only('motivo', 'activo'));

        return redirect()->route('admin.motivos_devolucion.index')
            ->with('success', 'Motivo de devolución creado con éxito.');
    }

    public function edit($id)
    {
        $motivo = MotivoDevolucion::findOrFail($id);
        return view('admin.motivos_devolucion.edit', compact('motivo'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'required|string|max:255',
            'activo' => 'required|boolean',
        ]);

        $motivo = MotivoDevolucion::findOrFail($id);
        $motivo->update($request->only('motivo', 'activo'));

        return redirect()->route('admin.motivos_devolucion.index')
            ->with('success', 'Motivo de devolución actualizado con éxito.');
    }

    public function destroy($id)
    {
        $motivo = MotivoDevolucion::findOrFail($id);
        
        // Antes de eliminar, comprobar si ya fue referenciado en pagos_compra
        $estaReferenciado = \App\Models\PagoCompra::where('id_motivo_devolucion', $id)->exists();
        
        if ($estaReferenciado) {
            $motivo->update(['activo' => false]);
            return redirect()->route('admin.motivos_devolucion.index')
                ->with('success', 'El motivo está referenciado en transacciones históricas. Se ha desactivado en lugar de eliminarse físicamente.');
        }

        $motivo->delete();

        return redirect()->route('admin.motivos_devolucion.index')
            ->with('success', 'Motivo de devolución eliminado con éxito.');
    }
}
