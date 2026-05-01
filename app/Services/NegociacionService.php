<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Message;
use App\Models\Negociacion;
use App\Models\Paquete;
use App\Models\PredefinedMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================
 * NegociacionService — Lógica de negocio de intercambios
 * ============================================================
 *
 * Flujo de estados:
 *   Inicial → aceptado → completado
 *           → contraoferta → aceptado / rechazado
 *           → rechazado
 *           → cancelado (por el emisor)
 *
 * Reglas:
 *   - Solo se puede negociar items con stock > 0
 *   - No se permiten negociaciones duplicadas (mismo emisor + mismo item)
 *   - Cada transición de estado valida el estado actual
 *   - Aceptar descuenta inventario dentro de una transacción DB
 *   - El paquete ofrecido debe pertenecer al emisor
 * ============================================================
 */
class NegociacionService
{
    // Estados que permiten cada acción
    private const ESTADOS_ACEPTAR     = ['Inicial', 'contraoferta'];
    private const ESTADOS_RECHAZAR    = ['Inicial', 'contraoferta'];
    private const ESTADOS_CONTRAOFERTA = ['Inicial'];
    private const ESTADOS_CANCELAR    = ['Inicial', 'contraoferta'];

    /**
     * Crea una nueva negociación.
     */
    public function crear(int $emisorId, array $datos): array
    {
        $receptorItem = Item::with('inventarios')->find($datos['item_id']);

        if (!$receptorItem) {
            return $this->error('El artículo no existe.');
        }

        if ($receptorItem->estatus != 1) {
            return $this->error('Este artículo no está disponible (pausado o inactivo).');
        }

        if ($receptorItem->id_user === $emisorId) {
            return $this->error('No puedes negociar contigo mismo.');
        }

        // Validar stock
        $stock = $receptorItem->inventarios?->cantidad ?? 0;
        if ($stock <= 0) {
            return $this->error('Este artículo está agotado y no se puede negociar.');
        }

        // Validar que no exista negociación activa del mismo emisor por el mismo item
        $existente = Negociacion::where('usuario_emisor_id', $emisorId)
            ->where('receptor_item_id', $receptorItem->id_item)
            ->whereNotIn('estado', ['rechazado', 'cancelado', 'completado'])
            ->exists();

        if ($existente) {
            return $this->error('Ya tienes una negociación activa por este artículo.');
        }

        // Validar que el paquete pertenezca al emisor
        $emisorPaquete = null;
        if (!empty($datos['paquete_id'])) {
            $emisorPaquete = Paquete::where('id_paquete', $datos['paquete_id'])
                ->where('id_user', $emisorId)
                ->first();

            if (!$emisorPaquete) {
                return $this->error('El paquete seleccionado no te pertenece.');
            }
        }

        $negociacion = Negociacion::create([
            'receptor_item_id'    => $receptorItem->id_item,
            'emisor_paquete_id'   => $emisorPaquete?->id_paquete,
            'usuario_emisor_id'   => $emisorId,
            'usuario_receptor_id' => $receptorItem->id_user,
            'mensaje_inicial'     => $datos['mensaje'],
            'monto_oferta'        => $datos['monto_oferta'] ?? null,
            'estado'              => 'Inicial',
            'fecha_creacion'      => now(),
            'items_ofrecidos'     => !empty($datos['items_ofrecidos']) ? $datos['items_ofrecidos'] : null,
        ]);

        $this->crearMensaje(
            $emisorId,
            $receptorItem->id_user,
            $receptorItem->id_item,
            $emisorPaquete?->id_paquete,
            $datos['mensaje']
        );

        // Notificar al receptor
        $emisor = \App\Models\User::find($emisorId);
        $textoNotif = "Tienes una nueva propuesta de intercambio por tu producto \"{$receptorItem->item}\" de {$emisor->nombres}.";
        event(new \App\Events\NuevaNotificacion($textoNotif, $receptorItem->id_user));

        return $this->ok('Negociación enviada correctamente.');
    }

    /**
     * Receptor acepta la negociación.
     * Descuenta inventario dentro de una transacción.
     */
    public function aceptar(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) {
            return $this->error('Negociación no encontrada.');
        }

        if ($userId != $neg->usuario_receptor_id) {
            return $this->error('No autorizado.');
        }

        if (!in_array($neg->estado, self::ESTADOS_ACEPTAR)) {
            return $this->error("No se puede aceptar una negociación en estado \"{$neg->estado}\".");
        }

        // Validar que el item siga activo
        $itemNeg = Item::find($neg->receptor_item_id);
        if (!$itemNeg || $itemNeg->estatus != 1) {
            $neg->update(['estado' => 'cancelado']);
            return $this->error('El artículo ya no está disponible. Negociación cancelada.');
        }

        // Transacción: cambiar estado + descontar inventario
        try {
            DB::transaction(function () use ($neg) {
                // Lock para evitar doble aceptación
                $neg = Negociacion::where('id_negociacion', $neg->id_negociacion)
                    ->lockForUpdate()
                    ->first();

                if (!in_array($neg->estado, self::ESTADOS_ACEPTAR)) {
                    throw new \RuntimeException('Estado ya cambió.');
                }

                $neg->update(['estado' => 'aceptado']);

                // Descontar inventario
                $item = Item::with('inventarios')->find($neg->receptor_item_id);
                if ($item && $item->inventarios && $item->inventarios->cantidad > 0) {
                    $item->inventarios->cantidad -= 1;
                    $item->inventarios->save();

                    // Si el stock llegó a 0, cancelar otras negociaciones activas por este item
                    if ($item->inventarios->cantidad <= 0) {
                        Negociacion::where('receptor_item_id', $item->id_item)
                            ->where('id_negociacion', '!=', $neg->id_negociacion)
                            ->whereIn('estado', ['Inicial', 'contraoferta'])
                            ->update(['estado' => 'cancelado']);
                    }
                }
            });

            // Notificar al emisor que fue aceptado
            $receptor = \App\Models\User::find($userId);
            event(new \App\Events\NuevaNotificacion(
                "¡Tu propuesta de intercambio fue aceptada por {$receptor->nombres}! Confirma para continuar.",
                $neg->usuario_emisor_id
            ));

            return $this->ok('Negociación aceptada.');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Error al aceptar negociación', ['id' => $negociacionId, 'error' => $e->getMessage()]);
            return $this->error('Error al procesar la aceptación.');
        }
    }

    /**
     * Emisor acepta una contraoferta del receptor.
     */
    public function aceptarComoEmisor(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) return $this->error('Negociación no encontrada.');

        if ($userId != $neg->usuario_emisor_id) {
            return $this->error('Solo el emisor puede aceptar la contraoferta.');
        }

        if ($neg->estado !== 'contraoferta') {
            return $this->error('No hay contraoferta activa para aceptar.');
        }

        try {
            DB::transaction(function () use ($neg) {
                $neg = Negociacion::where('id_negociacion', $neg->id_negociacion)->lockForUpdate()->first();
                if ($neg->estado !== 'contraoferta') throw new \RuntimeException('Estado ya cambió.');
                $neg->update(['estado' => 'aceptado']);

                $item = Item::with('inventarios')->find($neg->receptor_item_id);
                if ($item && $item->inventarios && $item->inventarios->cantidad > 0) {
                    $item->inventarios->cantidad -= 1;
                    $item->inventarios->save();
                    if ($item->inventarios->cantidad <= 0) {
                        Negociacion::where('receptor_item_id', $item->id_item)
                            ->where('id_negociacion', '!=', $neg->id_negociacion)
                            ->whereIn('estado', ['Inicial', 'contraoferta'])
                            ->update(['estado' => 'cancelado']);
                    }
                }
            });

            event(new \App\Events\NuevaNotificacion(
                "El emisor aceptó tu contraoferta en el intercambio #{$neg->id_negociacion}. Confirma para continuar.",
                $neg->usuario_receptor_id
            ));

            return $this->ok('Contraoferta aceptada. El intercambio está en estado aceptado.');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Error al aceptar contraoferta', ['id' => $negociacionId, 'error' => $e->getMessage()]);
            return $this->error('Error al procesar la aceptación.');
        }
    }

    /**
     * Receptor rechaza la negociación.
     */
    public function rechazar(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) {
            return $this->error('Negociación no encontrada.');
        }

        if ($userId != $neg->usuario_receptor_id) {
            return $this->error('No autorizado.');
        }

        if (!in_array($neg->estado, self::ESTADOS_RECHAZAR)) {
            return $this->error("No se puede rechazar una negociación en estado \"{$neg->estado}\".");
        }

        $neg->update(['estado' => 'rechazado']);

        // Notificar al emisor
        $receptor = \App\Models\User::find($userId);
        event(new \App\Events\NuevaNotificacion(
            "Tu propuesta de intercambio fue rechazada por {$receptor->nombres}.",
            $neg->usuario_emisor_id
        ));

        return $this->ok('Negociación rechazada.');
    }

    /**
     * Emisor cancela su propia negociación.
     */
    public function cancelar(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) {
            return $this->error('Negociación no encontrada.');
        }

        if ($userId != $neg->usuario_emisor_id) {
            return $this->error('No autorizado. Solo el emisor puede cancelar.');
        }

        if (!in_array($neg->estado, self::ESTADOS_CANCELAR)) {
            return $this->error("No se puede cancelar una negociación en estado \"{$neg->estado}\".");
        }

        $neg->update(['estado' => 'cancelado']);
        return $this->ok('Negociación cancelada.');
    }

    /**
     * Receptor envía contraoferta.
     */
    public function contraoferta(int $userId, int $negociacionId, array $datos): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) {
            return $this->error('Negociación no encontrada.');
        }

        if ($userId != $neg->usuario_receptor_id) {
            return $this->error('No autorizado.');
        }

        if (!in_array($neg->estado, self::ESTADOS_CONTRAOFERTA)) {
            return $this->error("No se puede hacer contraoferta en estado \"{$neg->estado}\".");
        }

        $neg->update([
            'monto_contra_oferta' => $datos['monto_contra_oferta'] ?? null,
            'estado'              => 'contraoferta',
        ]);

        if (!empty($datos['mensaje'])) {
            $this->crearMensaje(
                $userId,
                $neg->usuario_emisor_id,
                $neg->receptor_item_id,
                null,
                $datos['mensaje']
            );
        }

        return $this->ok('Contraoferta enviada.');
    }

    /**
     * Emisor confirma el intercambio después de que el receptor aceptó.
     * Una vez confirmado por ambos, se notifica a los administradores.
     */
    public function confirmarEmisor(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) {
            return $this->error('Negociación no encontrada.');
        }

        if ($userId != $neg->usuario_emisor_id) {
            return $this->error('Solo el emisor puede confirmar.');
        }

        if ($neg->estado !== 'aceptado') {
            return $this->error('Solo se puede confirmar una negociación aceptada por el receptor.');
        }

        if ($neg->emisor_confirmado) {
            return $this->error('Ya confirmaste este intercambio.');
        }

        $neg->update(['emisor_confirmado' => true]);

        // Notificar al receptor que el emisor aprobó
        event(new \App\Events\NuevaNotificacion(
            "El emisor aprobó el intercambio #{$neg->id_negociacion}. Ahora aprueba tú para continuar.",
            $neg->usuario_receptor_id
        ));

        // Notificar a administradores
        $this->notificarAdmins($neg);

        return $this->ok('Confirmación registrada. Los administradores han sido notificados para gestionar el envío.');
    }

    /**
     * Receptor confirma el intercambio.
     */
    public function confirmarReceptor(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) {
            return $this->error('Negociación no encontrada.');
        }

        if ($userId != $neg->usuario_receptor_id) {
            return $this->error('Solo el receptor puede confirmar.');
        }

        if ($neg->estado !== 'aceptado') {
            return $this->error('La negociación no está en estado aceptado.');
        }

        if ($neg->receptor_confirmado) {
            return $this->error('Ya confirmaste este intercambio.');
        }

        $neg->update(['receptor_confirmado' => true]);

        // Notificar al emisor que el receptor aprobó
        event(new \App\Events\NuevaNotificacion(
            "El receptor aprobó el intercambio #{$neg->id_negociacion}. Ambos han confirmado.",
            $neg->usuario_emisor_id
        ));

        // Si ambos confirmaron, notificar admins
        if ($neg->emisor_confirmado) {
            $this->notificarAdminsCompletado($neg);
        }

        return $this->ok('Has aprobado el intercambio.');
    }

    /**
     * Notifica a todos los administradores que un intercambio está listo para envío.
     */
    private function notificarAdmins(Negociacion $neg): void
    {
        try {
            $admins = \App\Models\User::where('isAdmin', true)->get();
            $texto = "Intercambio #{$neg->id_negociacion} confirmado por ambas partes. Gestiona el envío.";
            foreach ($admins as $admin) {
                event(new \App\Events\NuevaNotificacion($texto, $admin->id));
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar a admins', ['error' => $e->getMessage()]);
        }
    }

    public function notificarAdminsCompletado(Negociacion $neg): void
    {
        try {
            $admins = \App\Models\User::where('isAdmin', true)->get();
            $texto  = "✅ Intercambio #{$neg->id_negociacion} completado. Ambos usuarios pagaron. Procede con el envío.";
            foreach ($admins as $admin) {
                event(new \App\Events\NuevaNotificacion($texto, $admin->id));
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar a admins (completado)', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Marca una negociación aceptada como completada (intercambio realizado).
     */
    public function completar(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::find($negociacionId);
        if (!$neg) {
            return $this->error('Negociación no encontrada.');
        }

        // Cualquiera de las dos partes puede marcar como completado
        if ($userId != $neg->usuario_emisor_id && $userId != $neg->usuario_receptor_id) {
            return $this->error('No autorizado.');
        }

        if ($neg->estado !== 'aceptado') {
            return $this->error('Solo se pueden completar negociaciones aceptadas.');
        }

        $neg->update(['estado' => 'completado']);
        return $this->ok('Intercambio marcado como completado.');
    }

    // ───────────────────────────────────────────────────────
    // Consultas
    // ───────────────────────────────────────────────────────

    /**
     * Historial de negociaciones de un artículo para el usuario.
     */
    public function obtenerNegociaciones(int $userId, int $itemId): array
    {
        // Obtener mensajes reales de la tabla messages (no de negociaciones)
        $mensajes = Message::where('id_oferta', $itemId)
            ->where(fn($q) => $q->where('id_emisor', $userId)->orWhere('id_receptor', $userId))
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($msg) => [
                'texto'  => $msg->mensaje,
                'fecha'  => optional($msg->created_at)->format('d/m/Y H:i'),
                'propio' => $msg->id_emisor == $userId,
            ]);

        $paquetes = DB::table('paquetes')
            ->where('id_user', $userId)
            ->select('id_paquete as id', 'nombre_paquete as nombre')
            ->get();

        return [
            'mensajes'             => $mensajes,
            'paquetes'             => $paquetes,
            'accion'               => PredefinedMessage::select('tipo')->distinct()->get(),
            'mensajesPredefinidos' => PredefinedMessage::select('titulo', 'mensaje', 'rol')->get(),
        ];
    }

    /**
     * Mensajes entre dos usuarios filtrados por item negociado.
     */
    public function obtenerMensajes(int $userId, int $idEmisor, int $idReceptor): array
    {
        $rawMensajes = Message::where(function ($q) use ($idEmisor, $idReceptor) {
                $q->where('id_emisor', $idEmisor)->where('id_receptor', $idReceptor);
            })
            ->orWhere(function ($q) use ($idEmisor, $idReceptor) {
                $q->where('id_emisor', $idReceptor)->where('id_receptor', $idEmisor);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $itemId = $rawMensajes->whereNotNull('id_oferta')->first()?->id_oferta;
        if ($itemId) {
            $rawMensajes = $rawMensajes->where('id_oferta', $itemId)->values();
        }

        $mensajes = $rawMensajes->map(fn($msg) => [
            'id'      => $msg->id,
            'mensaje' => $msg->mensaje,
            'fecha'   => optional($msg->created_at)->format('d/m/Y H:i'),
            'propio'  => $msg->id_emisor == $userId,
        ]);

        return [
            'mensajes'             => $mensajes,
            'mensajesPredefinidos' => PredefinedMessage::select('titulo', 'mensaje', 'rol')->get(),
            'accion'               => PredefinedMessage::select('tipo')->distinct()->get(),
            'item_id'              => $itemId,
        ];
    }

    // ───────────────────────────────────────────────────────
    // Helpers privados
    // ───────────────────────────────────────────────────────

    public function crearMensaje(int $emisorId, int $receptorId, ?int $itemId, ?int $paqueteId, string $texto): void
    {
        try {
            Message::create([
                'id_emisor'   => $emisorId,
                'id_receptor' => $receptorId,
                'id_oferta'   => $itemId,
                'id_paquete'  => $paqueteId,
                'mensaje'     => $texto,
                'leido'       => 0,
            ]);
        } catch (\Exception $e) {
            Log::warning('NegociacionService: no se pudo guardar mensaje', ['error' => $e->getMessage()]);
        }
    }

    private function ok(string $message): array
    {
        return ['success' => true, 'message' => $message];
    }

    private function error(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}
