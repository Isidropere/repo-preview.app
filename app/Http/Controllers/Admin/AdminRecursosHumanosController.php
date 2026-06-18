<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empleo;

class AdminRecursosHumanosController extends Controller
{
    public function index()
    {
        $empleos = Empleo::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.recursos-humanos.index', compact('empleos'));
    }

    public function create()
    {
        return view('admin.recursos-humanos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'requisitos' => 'required|string',
            'activo' => 'nullable|boolean'
        ]);

        Empleo::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'requisitos' => $request->requisitos,
            'activo' => $request->has('activo') ? (bool) $request->activo : true
        ]);

        return redirect()->route('admin.recursos-humanos.index')
            ->with('success', 'Vacante de empleo creada con éxito.');
    }

    public function edit($id)
    {
        $empleo = Empleo::findOrFail($id);
        return view('admin.recursos-humanos.edit', compact('empleo'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'requisitos' => 'required|string',
            'activo' => 'nullable|boolean'
        ]);

        $empleo = Empleo::findOrFail($id);
        $empleo->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'requisitos' => $request->requisitos,
            'activo' => $request->has('activo') ? (bool) $request->activo : false
        ]);

        return redirect()->route('admin.recursos-humanos.index')
            ->with('success', 'Vacante de empleo actualizada con éxito.');
    }

    public function destroy($id)
    {
        $empleo = Empleo::findOrFail($id);
        $empleo->delete();

        return redirect()->route('admin.recursos-humanos.index')
            ->with('success', 'Vacante de empleo eliminada con éxito.');
    }
}
