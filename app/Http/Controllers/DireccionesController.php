<?php

namespace App\Http\Controllers;

use App\Models\Direcciones;
use App\Services\DireccionService;
use Illuminate\Http\Request;

/**
 * ============================================================
 * DireccionesController — Gestión de direcciones del usuario
 * ============================================================
 *
 * CRUD completo de direcciones de envío. Cada usuario puede
 * tener múltiples direcciones y marcar una como predeterminada
 * (usada automáticamente en checkout y cálculo de delivery).
 *
 * Toda la lógica está delegada a DireccionService.
 *
 * Rutas: /direcciones (resource)
 * Middleware: auth
 * ============================================================
 */
class DireccionesController extends Controller
{
    public function __construct(
        private DireccionService $direccionService,
    ) {}

    public function index(Request $request)
    {
        $direcciones = $this->direccionService->listar(auth()->id());
        $returnUrl = $request->query('return_url');
        return view('direcciones.direcciones', compact('direcciones', 'returnUrl'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'calle'              => 'required|string|max:255',
            'N_casa_edificio'    => 'required|string|max:20',
            'apto'               => 'nullable|string|max:20',
            'id_provincia'       => 'required|exists:provincias,id_provincia',
            'id_municipio'       => 'required|exists:municipios,id_municipio',
            'geolocalizacion'    => 'nullable|string|max:255',
            'sector'             => 'nullable|string|max:255',
            'telefono_contacto'  => 'nullable|string|max:13',
        ]);

        $resultado = $this->direccionService->crear(auth()->id(), $validated);

        return response()->json($resultado, 201);
    }

    public function edit(Direcciones $direccion)
    {
        if (!$direccion) {
            return response()->json(['error' => 'Dirección no encontrada'], 404);
        }
        return response()->json($direccion);
    }

    public function marcarComoPredeterminada($id)
    {
        $resultado = $this->direccionService->marcarPredeterminada(auth()->id(), $id);
        return response()->json($resultado);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'calle'              => 'sometimes|string|max:255',
            'N_casa_edificio'    => 'sometimes|string|max:20',
            'apto'               => 'nullable|string|max:20',
            'id_provincia'       => 'sometimes|exists:provincias,id_provincia',
            'id_municipio'       => 'sometimes|exists:municipios,id_municipio',
            'geolocalizacion'    => 'nullable|string|max:255',
            'sector'             => 'nullable|string|max:255',
            'telefono_contacto'  => 'nullable|string|max:13',
        ]);

        $resultado = $this->direccionService->actualizar(auth()->id(), $id, $validated);

        return response()->json($resultado);
    }

    public function show($id)
    {
        $direccion = Direcciones::where('id_user', auth()->id())
            ->with(['provincia', 'municipio'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $direccion,
            'message' => '',
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $this->direccionService->eliminar(auth()->id(), (int) $id);
        $returnUrl = $request->query('return_url');
        
        if ($returnUrl) {
            return redirect()->to(route('direcciones.index') . '?return_url=' . urlencode($returnUrl))
                ->with('success', 'Dirección eliminada correctamente');
        }
        
        return redirect()->route('direcciones.index')->with('success', 'Dirección eliminada correctamente');
    }
}
