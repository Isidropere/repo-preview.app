<?php

namespace App\Services;

use App\Models\ConfigTarifaCategoria29;
use App\Models\Item;
use App\Models\PagoRegistroTalento;
use App\Models\TarjetaPago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Servicio que orquesta el pago con Cardnet y la creación del talento (categoría 29).
 *
 * Flujo:
 *   1. Recuperar datos de sesión (talento_pendiente_data / talento_pendiente_files)
 *   2. Obtener tarjeta del usuario
 *   3. Cobrar con PagoService (Cardnet)
 *   4. Si aprobado: crear Item, mover archivos temp, crear PagoRegistroTalento, limpiar sesión
 *   5. Si rechazado: retornar error sin crear nada
 */
class TalentoRegistroPagoService
{
    public function __construct(
        private PagoService $pagoService,
    ) {}

    /**
     * Procesa el pago y, si es aprobado, guarda el talento.
     *
     * @return array{success: bool, item?: Item, error?: string}
     */
    public function procesarPagoYGuardarTalento(
        int $userId,
        string $idTarjeta,
        ?string $cvv,
        string $clientIp
    ): array {
        // 1. Recuperar datos de sesión
        $datosTalento = session('talento_pendiente_data');
        if (empty($datosTalento)) {
            return ['success' => false, 'error' => 'Los datos del formulario han expirado. Por favor vuelve a completar el formulario.'];
        }

        // 2. Obtener tarjeta del usuario
        $tarjeta = TarjetaPago::where('id_tarjeta', $idTarjeta)
            ->where('id_user', $userId)
            ->where('estatus', 1)
            ->first();

        if (!$tarjeta) {
            return ['success' => false, 'error' => 'Tarjeta no válida o no pertenece a tu cuenta.'];
        }

        // 3. Obtener monto de configuración
        $config = ConfigTarifaCategoria29::vigente();
        $monto = (float) $config->monto_registro;

        // 4. Preparar datos de tarjeta y cobrar
        $datosTarjeta = $tarjeta->datosCardnet($cvv);
        $opciones = [
            'client_ip'        => $clientIp,
            'invoice_number'   => 'TAL' . Str::random(10),
            'reference_number' => 'talento_' . $userId . '_' . time(),
        ];

        $resultado = $this->pagoService->cobrarTarjeta($monto, '214', $datosTarjeta, $opciones);

        Log::info('[TalentoRegistroPago] Resultado cobro', [
            'user_id' => $userId,
            'success' => $resultado['success'],
            'status'  => $resultado['status'] ?? null,
        ]);

        if (!$resultado['success']) {
            return ['success' => false, 'error' => $resultado['error'] ?? 'El pago fue rechazado. Intenta con otra tarjeta.'];
        }

        // 5. Pago aprobado — guardar talento en transacción
        try {
            $item = DB::transaction(function () use ($userId, $datosTalento, $resultado, $monto) {
                // Crear el ítem
                $item = Item::create(array_merge($datosTalento, [
                    'id_user'    => $userId,
                    'estatus'    => 1,
                    'fecha'      => now(),
                    'tiene_video'=> false,
                ]));

                // Mover archivos temporales
                $archivosTemp = session('talento_pendiente_files', []);
                $this->moverArchivosTemp($archivosTemp, $item->id_item);

                // Registrar pago
                $pagoTalento = PagoRegistroTalento::create([
                    'id_item'        => $item->id_item,
                    'id_user'        => $userId,
                    'transaction_id' => $resultado['transaction_id'],
                    'monto_pagado'   => $monto,
                    'estatus'        => 'aprobado',
                ]);

                // Generar Contabilidad (Asiento y Caja)
                app(ERPService::class)->procesarRegistroTalentoAprobado($pagoTalento);

                // Limpiar sesión
                session()->forget(['talento_pendiente_data', 'talento_pendiente_files', 'talento_pendiente_uuid']);

                // Invalidar cache del home
                \Illuminate\Support\Facades\Cache::forget('home_intercambio');
                \Illuminate\Support\Facades\Cache::forget('home_venta');

                return $item;
            });

            return ['success' => true, 'item' => $item];

        } catch (\Throwable $e) {
            Log::error('[TalentoRegistroPago] Error al guardar talento tras cobro aprobado', [
                'error'          => $e->getMessage(),
                'transaction_id' => $resultado['transaction_id'],
                'user_id'        => $userId,
            ]);

            // Intentar anular la transacción
            try {
                $this->pagoService->anularTransaccion($resultado['transaction_id'], $monto, [
                    'client_ip' => $clientIp,
                ]);
            } catch (\Throwable $anulacionError) {
                Log::error('[TalentoRegistroPago] No se pudo anular la transacción', [
                    'transaction_id' => $resultado['transaction_id'],
                    'error'          => $anulacionError->getMessage(),
                ]);
            }

            return ['success' => false, 'error' => 'Ocurrió un error al registrar tu talento. El cobro será revertido. Contacta soporte si el problema persiste.'];
        }
    }

    /**
     * Mueve archivos temporales al directorio definitivo del ítem.
     */
    private function moverArchivosTemp(array $archivosTemp, int $itemId): void
    {
        if (empty($archivosTemp)) {
            return;
        }

        $destDir = 'imgs/articulos/items';

        // Imagen principal
        if (!empty($archivosTemp['imagen_principal'])) {
            $tempPath = $archivosTemp['imagen_principal'];
            if (Storage::disk('local')->exists($tempPath)) {
                $ext      = pathinfo($tempPath, PATHINFO_EXTENSION);
                $isVideo  = in_array(strtolower($ext), ['mp4', 'mov', 'm4v']);
                $dir      = $isVideo ? 'imgs/videos/items' : $destDir;
                $prefix   = $isVideo ? 'video_' : 'item_';
                $fileName = $prefix . $itemId . '_' . now()->format('YmdHis') . '_' . Str::random(10) . '.' . $ext;

                $contenido = Storage::disk('local')->get($tempPath);
                \App\Helpers\ImageHelper::guardarContenido($contenido, $dir, $fileName);
                Storage::disk('local')->delete($tempPath);

                DB::table('imagenes_item')->insert([
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
            if (Storage::disk('local')->exists($tempPath)) {
                $ext      = pathinfo($tempPath, PATHINFO_EXTENSION);
                $fileName = 'item_' . $itemId . '_' . now()->format('YmdHis') . '_' . Str::random(8) . '.' . $ext;

                $contenido = Storage::disk('local')->get($tempPath);
                \App\Helpers\ImageHelper::guardarContenido($contenido, $destDir, $fileName);
                Storage::disk('local')->delete($tempPath);

                DB::table('imagenes_item')->insert([
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
            Storage::disk('local')->deleteDirectory('temp/' . $uuid);
        }
    }
}
