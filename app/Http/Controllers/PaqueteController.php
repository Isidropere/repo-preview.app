<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditarPaqueteRequest;
use App\Http\Requests\StorePaqueteRequest;
use App\Services\PaqueteService;
use Illuminate\Http\Request;

class PaqueteController extends Controller
{
    public function __construct(
        private PaqueteService $paqueteService,
    ) {}

    public function index()
    {
        $resultado = $this->paqueteService->listar(auth()->id());
        return response()->json([
            'success' => true,
            'data' => $resultado['data'],
            'message' => '',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_paquete' => 'required|string|max:255',
        ]);

        $nombre = $request->nombre_paquete;
        $resultado = $this->paqueteService->crear(auth()->id(), $nombre, []);

        if (!$resultado['success']) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $resultado['message'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $resultado['data'],
            'message' => 'Paquete creado correctamente.',
        ], 201);
    }

    public function show($id)
    {
        $resultado = $this->paqueteService->obtener(auth()->id(), (int) $id);

        if (!$resultado['success']) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $resultado['message'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $resultado['data'],
            'message' => '',
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_paquete' => 'sometimes|required|string|max:255',
        ]);

        $resultado = $this->paqueteService->actualizarNombre(auth()->id(), (int) $id, $request->nombre_paquete);

        return response()->json([
            'success' => $resultado['success'],
            'data' => $resultado['data'] ?? null,
            'message' => $resultado['message'],
        ]);
    }

    public function destroy($id)
    {
        $resultado = $this->paqueteService->eliminar(auth()->id(), (int) $id);

        if (!$resultado['success']) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $resultado['message'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Paquete eliminado correctamente.',
        ]);
    }

    // ── Endpoints usados por el frontend JS ──

    public function listarPaquetes()
    {
        $resultado = $this->paqueteService->listar(auth()->id());
        return response()->json($resultado['data']);
    }

    public function obtenerPaquete($paqueteId)
    {
        $resultado = $this->paqueteService->obtener(auth()->id(), (int) $paqueteId);

        if (!$resultado['success']) {
            return response()->json(['error' => $resultado['message']], 404);
        }

        return response()->json($resultado['data']);
    }

    public function actualizarPaquete(Request $request, $id)
    {
        $request->validate([
            'nombre_paquete' => 'required|string|max:255',
        ]);

        $resultado = $this->paqueteService->actualizarNombre(auth()->id(), (int) $id, $request->nombre_paquete);

        return response()->json($resultado);
    }

    public function getItemsUsuario()
    {
        $resultado = $this->paqueteService->itemsDelUsuario(auth()->id());
        return response()->json($resultado['data']);
    }

    public function getPaquete($paqueteId)
    {
        $resultado = $this->paqueteService->obtenerLigero(auth()->id(), (int) $paqueteId);

        if (!$resultado['success']) {
            return response()->json(['error' => $resultado['message']], 404);
        }

        return response()->json($resultado['data']);
    }

    public function editarPaquete(EditarPaqueteRequest $request, $paqueteId)
    {
        $resultado = $this->paqueteService->editar(
            auth()->id(),
            (int) $paqueteId,
            $request->validated('nombre') ?? $request->validated('nombre_paquete'),
            $request->validated('items', [])
        );

        if (!$resultado['success']) {
            return response()->json(['error' => $resultado['message']], 404);
        }

        return response()->json([
            'message'    => $resultado['message'],
            'id_paquete' => $resultado['data']['id_paquete'],
        ]);
    }

    public function crearPaquete(StorePaqueteRequest $request)
    {
        $nombre = $request->validated('nombre_paquete')
            ?? $request->validated('nombre')
            ?? 'Paquete_' . now()->format('Ymd_His');

        $resultado = $this->paqueteService->crear(
            auth()->id(),
            $nombre,
            $request->validated('items')
        );

        if (!$resultado['success']) {
            return response()->json(['error' => $resultado['message']], 400);
        }

        return response()->json($resultado['data']);
    }

    public function eliminarPaquete($id)
    {
        $resultado = $this->paqueteService->eliminar(auth()->id(), (int) $id);

        if (!$resultado['success']) {
            return response()->json(['error' => $resultado['message']], 404);
        }

        return response()->json(['mensaje' => $resultado['message']]);
    }
}
