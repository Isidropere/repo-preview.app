<?php

namespace App\Http\Controllers;

use App\Models\ConfigTarifaCategoria29;
use App\Models\TarjetaPago;
use App\Services\TalentoRegistroPagoService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class TalentoRegistroPagoController extends Controller
{
    public function __construct(
        private TalentoRegistroPagoService $pagoService,
    ) {}

    /**
     * Muestra la vista de pago con tarjeta para registrar el talento.
     * Redirige al flujo de AZUL.
     */
    public function mostrarPago(): RedirectResponse
    {
        $datosTalento = session('talento_pendiente_data');
        if (empty($datosTalento)) {
            return redirect()->route('items.talento_create')
                ->with('error', 'Los datos del formulario han expirado. Por favor vuelve a completar el formulario.');
        }

        $userId = auth()->id();
        $config = ConfigTarifaCategoria29::vigente();
        $cantidad = (int) ($datosTalento['cantidad'] ?? 1);
        $monto = (float) $config->monto_registro * $cantidad;

        // Crear el item de forma inactiva (estatus = 0) para el flujo de AZUL
        $item = \App\Models\Item::create(array_merge($datosTalento, [
            'id_user'     => $userId,
            'estatus'     => 0,
            'fecha'       => now(),
            'tiene_video' => false,
        ]));

        // Crear registro en el inventario
        \App\Models\Inventario::create([
            'id_item'  => $item->id_item,
            'cantidad' => $cantidad,
            'fecha'    => now(),
        ]);

        // Mover archivos temporales
        $archivosTemp = session('talento_pendiente_files', []);
        if (!empty($archivosTemp)) {
            $this->moverArchivosSession($archivosTemp, $item->id_item);
        }

        // Limpiar sesión
        session()->forget(['talento_pendiente_data', 'talento_pendiente_files', 'talento_pendiente_uuid']);

        if ($monto > 0) {
            return redirect()->route('talento.pago.iniciar', $item->id_item);
        }

        // Si es tarifa cero, activar de una vez
        $item->update(['estatus' => 1]);
        \App\Models\PagoRegistroTalento::create([
            'id_item'        => $item->id_item,
            'id_user'        => $userId,
            'transaction_id' => 'GRATIS_' . time(),
            'monto_pagado'   => 0,
            'estatus'        => 'aprobado',
            'notas'          => 'Publicado automáticamente con tarifa cero.',
        ]);

        return redirect()->route('items.admintalento')->with('success', 'Talento publicado correctamente.');
    }

    private function moverArchivosSession(array $archivosTemp, int $itemId): void
    {
        if (empty($archivosTemp)) {
            return;
        }

        $destDir = 'imgs/articulos/items';

        // Imagen principal
        if (!empty($archivosTemp['imagen_principal'])) {
            $tempPath = $archivosTemp['imagen_principal'];
            if (\Illuminate\Support\Facades\Storage::disk('local')->exists($tempPath)) {
                $ext      = pathinfo($tempPath, PATHINFO_EXTENSION);
                $isVideo  = in_array(strtolower($ext), ['mp4', 'mov', 'm4v']);
                $dir      = $isVideo ? 'imgs/videos/items' : $destDir;
                $prefix   = $isVideo ? 'video_' : 'item_';
                $fileName = $prefix . $itemId . '_' . now()->format('YmdHis') . '_' . \Illuminate\Support\Str::random(10) . '.' . $ext;

                $contenido = \Illuminate\Support\Facades\Storage::disk('local')->get($tempPath);
                \App\Helpers\ImageHelper::guardarContenido($contenido, $dir, $fileName);
                \Illuminate\Support\Facades\Storage::disk('local')->delete($tempPath);

                \DB::table('imagenes_item')->insert([
                    'nombre'              => $fileName,
                    'extension'           => $ext,
                    'id_item'             => $itemId,
                    'orden_visualizacion' => 1,
                    'ruta'                => $dir,
                    'tipo'                => $isVideo ? 'video' : 'imagen',
                ]);
            }
        }

        // Imágenes adicionales
        foreach ($archivosTemp['imagenes'] ?? [] as $orden => $tempPath) {
            if (\Illuminate\Support\Facades\Storage::disk('local')->exists($tempPath)) {
                $ext      = pathinfo($tempPath, PATHINFO_EXTENSION);
                $fileName = 'item_' . $itemId . '_' . now()->format('YmdHis') . '_' . \Illuminate\Support\Str::random(8) . '.' . $ext;

                $contenido = \Illuminate\Support\Facades\Storage::disk('local')->get($tempPath);
                \App\Helpers\ImageHelper::guardarContenido($contenido, $destDir, $fileName);
                \Illuminate\Support\Facades\Storage::disk('local')->delete($tempPath);

                \DB::table('imagenes_item')->insert([
                    'nombre'              => $fileName,
                    'extension'           => $ext,
                    'id_item'             => $itemId,
                    'orden_visualizacion' => $orden + 2,
                    'ruta'                => $destDir,
                    'tipo'                => 'imagen',
                ]);
            }
        }

        // Limpiar directorio temp
        $uuid = session('talento_pendiente_uuid');
        if ($uuid) {
            \Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory('temp/' . $uuid);
        }
    }

    /**
     * Procesa el pago con Cardnet y, si es aprobado, crea el talento.
     */
    public function procesarPago(Request $request)
    {
        $request->validate([
            'id_tarjeta' => 'required|string|exists:tarjetas_pagos,id_tarjeta',
            'cvv'        => 'nullable|string|max:4',
        ]);

        $resultado = $this->pagoService->procesarPagoYGuardarTalento(
            userId:    auth()->id(),
            idTarjeta: $request->input('id_tarjeta'),
            cvv:       $request->input('cvv'),
            clientIp:  $request->ip(),
        );

        if ($request->expectsJson()) {
            if (!$resultado['success']) {
                return response()->json(['success' => false, 'message' => $resultado['error']], 422);
            }
            return response()->json([
                'success'  => true,
                'message'  => 'Tu talento fue publicado exitosamente.',
                'redirect' => route('items.admintalento'),
            ]);
        }

        if (!$resultado['success']) {
            return redirect()->back()->with('error', $resultado['error']);
        }

        return redirect()->route('historial')
            ->with('success', 'Tu talento fue publicado exitosamente.')
            ->with('order_completed_id', 'TAL-' . ($resultado['item']->id_item ?? '') . '-' . ($resultado['pago_talento_id'] ?? ''));
    }
}
