<?php

namespace App\Http\Controllers;

use App\Models\ProveedorPago;
use Illuminate\Http\Request;

class ProveedorPagoController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => ProveedorPago::paginate(50),
            'message' => '',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'proveedor_pago' => 'required|string|max:100',
        ]);

        $proveedor = ProveedorPago::create($data);

        return response()->json([
            'success' => true,
            'data' => $proveedor,
            'message' => 'Proveedor de pago creado correctamente.',
        ], 201);
    }

    public function show(int $id)
    {
        return response()->json([
            'success' => true,
            'data' => ProveedorPago::findOrFail($id),
            'message' => '',
        ]);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'proveedor_pago' => 'required|string|max:100',
        ]);

        $proveedor = ProveedorPago::findOrFail($id);
        $proveedor->update($data);

        return response()->json([
            'success' => true,
            'data' => $proveedor,
            'message' => 'Proveedor de pago actualizado correctamente.',
        ]);
    }

    public function destroy(int $id)
    {
        ProveedorPago::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Proveedor de pago eliminado correctamente.',
        ]);
    }
}
