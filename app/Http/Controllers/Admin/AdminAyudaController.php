<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AyudaPagina;
use App\Models\AyudaPaso;
use Illuminate\Support\Facades\File;

class AdminAyudaController extends Controller
{
    public function index()
    {
        $paginas = AyudaPagina::orderBy('id', 'asc')->get();
        return view('admin.ayuda.index', compact('paginas'));
    }

    public function editPage($id)
    {
        $pagina = AyudaPagina::with('pasos')->findOrFail($id);
        return view('admin.ayuda.edit', compact('pagina'));
    }

    public function updatePage(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
        ]);

        $pagina = AyudaPagina::findOrFail($id);
        $pagina->update($request->only('titulo', 'descripcion'));

        return redirect()->route('admin.ayuda.edit_page', $id)
            ->with('success', 'Página de ayuda actualizada con éxito.');
    }

    public function createStep($pageId)
    {
        $pagina = AyudaPagina::findOrFail($pageId);
        $siguienteOrden = ($pagina->pasos()->max('orden') ?? 0) + 1;
        return view('admin.ayuda.pasos.create', compact('pagina', 'siguienteOrden'));
    }

    public function storeStep(Request $request, $pageId)
    {
        $request->validate([
            'orden' => 'required|integer|min:1',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'imagen' => 'nullable|image|max:4096', 
        ]);

        $pagina = AyudaPagina::findOrFail($pageId);

        $data = $request->only('orden', 'titulo', 'descripcion');
        $data['ayuda_pagina_id'] = $pageId;

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('imgs/tutorial/uploads'), $filename);
            $data['imagen'] = '/imgs/tutorial/uploads/' . $filename;
        }

        AyudaPaso::create($data);

        return redirect()->route('admin.ayuda.edit_page', $pageId)
            ->with('success', 'Paso de ayuda creado con éxito.');
    }

    public function editStep($stepId)
    {
        $paso = AyudaPaso::with('pagina')->findOrFail($stepId);
        return view('admin.ayuda.pasos.edit', compact('paso'));
    }

    public function updateStep(Request $request, $stepId)
    {
        $request->validate([
            'orden' => 'required|integer|min:1',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'imagen' => 'nullable|image|max:4096',
        ]);

        $paso = AyudaPaso::findOrFail($stepId);
        $data = $request->only('orden', 'titulo', 'descripcion');

        if ($request->hasFile('imagen')) {
            if ($paso->imagen && str_contains($paso->imagen, '/imgs/tutorial/uploads/')) {
                $oldPath = public_path($paso->imagen);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $file = $request->file('imagen');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('imgs/tutorial/uploads'), $filename);
            $data['imagen'] = '/imgs/tutorial/uploads/' . $filename;
        }

        $paso->update($data);

        return redirect()->route('admin.ayuda.edit_page', $paso->ayuda_pagina_id)
            ->with('success', 'Paso de ayuda actualizado con éxito.');
    }

    public function destroyStep($stepId)
    {
        $paso = AyudaPaso::findOrFail($stepId);
        $pageId = $paso->ayuda_pagina_id;

        if ($paso->imagen && str_contains($paso->imagen, '/imgs/tutorial/uploads/')) {
            $path = public_path($paso->imagen);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $paso->delete();

        return redirect()->route('admin.ayuda.edit_page', $pageId)
            ->with('success', 'Paso de ayuda eliminado con éxito.');
    }
}
