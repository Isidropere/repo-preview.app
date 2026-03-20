<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Plan::paginate(50),
            'message' => '',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plan'  => 'required|string|max:100',
            'valor' => 'required|numeric|min:0',
        ]);

        $plan = Plan::create($data);

        return response()->json([
            'success' => true,
            'data' => $plan,
            'message' => 'Plan creado correctamente.',
        ], 201);
    }

    public function show(int $id)
    {
        return response()->json([
            'success' => true,
            'data' => Plan::findOrFail($id),
            'message' => '',
        ]);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'plan'  => 'sometimes|string|max:100',
            'valor' => 'sometimes|numeric|min:0',
        ]);

        $plan = Plan::findOrFail($id);
        $plan->update($data);

        return response()->json([
            'success' => true,
            'data' => $plan,
            'message' => 'Plan actualizado correctamente.',
        ]);
    }

    public function destroy(int $id)
    {
        Plan::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Plan eliminado correctamente.',
        ]);
    }
}
