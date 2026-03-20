<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PredefinedMessage;
use Illuminate\Http\Request;

class AdminMensajesController extends Controller
{
    public function index()
    {
        $mensajes = PredefinedMessage::orderBy('rol')->orderBy('tipo')->orderBy('id')->get();
        return view('admin.mensajes.index', compact('mensajes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo'  => 'required|string|max:100',
            'mensaje' => 'required|string|max:500',
            'tipo'    => 'required|string|max:50',
            'rol'     => 'required|in:emisor,receptor,general',
        ]);

        PredefinedMessage::create($request->only('titulo', 'mensaje', 'tipo', 'rol'));

        return back()->with('success', 'Mensaje creado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo'  => 'required|string|max:100',
            'mensaje' => 'required|string|max:500',
            'tipo'    => 'required|string|max:50',
            'rol'     => 'required|in:emisor,receptor,general',
            'activo'  => 'nullable|boolean',
        ]);

        $msg = PredefinedMessage::findOrFail($id);
        $msg->update([
            'titulo'  => $request->titulo,
            'mensaje' => $request->mensaje,
            'tipo'    => $request->tipo,
            'rol'     => $request->rol,
            'activo'  => $request->boolean('activo', true),
        ]);

        return back()->with('success', 'Mensaje actualizado.');
    }

    public function destroy($id)
    {
        PredefinedMessage::findOrFail($id)->delete();
        return back()->with('success', 'Mensaje eliminado.');
    }

    public function toggleActivo($id)
    {
        $msg = PredefinedMessage::findOrFail($id);
        $msg->activo = !$msg->activo;
        $msg->save();
        return response()->json(['activo' => $msg->activo]);
    }
}
