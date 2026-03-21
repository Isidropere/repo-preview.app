<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CuentaBancoEmpresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCuentaBancoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CuentaBancoEmpresa::orderBy('id', 'desc')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'banco'          => 'required|string|max:100',
            'numero_cuenta'  => 'required|string|max:50',
            'tipo_cuenta'    => 'required|in:ahorro,corriente,otro',
            'titular'        => 'required|string|max:150',
            'descripcion'    => 'nullable|string',
        ]);

        $cuenta = CuentaBancoEmpresa::create($data);

        return response()->json(['success' => true, 'data' => $cuenta], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $cuenta = CuentaBancoEmpresa::findOrFail($id);

        $data = $request->validate([
            'banco'          => 'required|string|max:100',
            'numero_cuenta'  => 'required|string|max:50',
            'tipo_cuenta'    => 'required|in:ahorro,corriente,otro',
            'titular'        => 'required|string|max:150',
            'descripcion'    => 'nullable|string',
        ]);

        $cuenta->update($data);

        return response()->json(['success' => true, 'data' => $cuenta]);
    }

    public function destroy(int $id): JsonResponse
    {
        CuentaBancoEmpresa::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    public function toggleActivo(int $id): JsonResponse
    {
        $cuenta = CuentaBancoEmpresa::findOrFail($id);
        $cuenta->activo = !$cuenta->activo;
        $cuenta->save();

        return response()->json(['success' => true, 'activo' => $cuenta->activo]);
    }
}
