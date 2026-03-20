<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Message;
use App\Models\Negociacion;
use App\Models\Paquete;
use App\Models\PredefinedMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NegociacionService
{
    /**
     * Crea una nueva negociación (intercambio).
     */
    public function crear(int $emisorId, array $datos): array
    {
        $receptorItem = Item::findOrFail($datos['item_id']);

        if ($receptorItem->id_user === $emisorId) {
            return ['success' => false, 'message' => 'No puedes negociar contigo mismo.'];
        }

        $emisorPaquete = !empty($datos['paquete_id'])
            ? Paquete::find($datos['paquete_id'])
            : null;

        $negociacion = Negociacion::create([
            'receptor_item_id'    => $receptorItem->id_item,
            'emisor_paquete_id'   => $emisorPaquete?->id_paquete,
            'usuario_emisor_id'   => $emisorId,
            'usuario_receptor_id' => $receptorItem->id_user,
            'mensaje_inicial'     => $datos['mensaje'],
            'monto_oferta'        => $datos['monto_oferta'] ?? null,
            'estado'              => 'Inicial',
            'fecha_creacion'      => now(),
        ]);

        $this->crearMensaje(
            $emisorId,
            $receptorItem->id_user,
            $receptorItem->id_item,
            $emisorPaquete?->id_paquete,
            $datos['mensaje']
        );

        return ['success' => true, 'message' => 'Negociación enviada correctamente.'];
    }

    /**
     * Historial de negociaciones de un artículo para el usuario.
     */
    public function obtenerNegociaciones(int $userId, int $itemId): array
    {
        $negociaciones = Negociacion::where('receptor_item_id', $itemId)
            ->where(fn($q) => $q->where('usuario_emisor_id', $userId)
                                ->orWhere('usuario_receptor_id', $userId))
            ->orderBy('fecha_creacion', 'asc')
            ->get();

        $mensajes = $negociaciones->map(fn($n) => [
            'texto'  => $n->mensaje_inicial,
            'fecha'  => $n->fecha_creacion,
            'propio' => $n->usuario_emisor_id == $userId,
            'estado' => $n->estado,
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

    /**
     * Receptor acepta la negociación.
     */
    public function aceptar(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::findOrFail($negociacionId);

        if ($userId != $neg->usuario_receptor_id) {
            return ['success' => false, 'message' => 'No autorizado.'];
        }

        $neg->update(['estado' => 'aceptado']);
        return ['success' => true, 'message' => 'Negociación aceptada.'];
    }

    /**
     * Receptor rechaza la negociación.
     */
    public function rechazar(int $userId, int $negociacionId): array
    {
        $neg = Negociacion::findOrFail($negociacionId);

        if ($userId != $neg->usuario_receptor_id) {
            return ['success' => false, 'message' => 'No autorizado.'];
        }

        $neg->update(['estado' => 'rechazado']);
        return ['success' => true, 'message' => 'Negociación rechazada.'];
    }

    /**
     * Receptor envía contraoferta.
     */
    public function contraoferta(int $userId, int $negociacionId, array $datos): array
    {
        $neg = Negociacion::findOrFail($negociacionId);

        if ($userId != $neg->usuario_receptor_id) {
            return ['success' => false, 'message' => 'No autorizado.'];
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

        return ['success' => true, 'message' => 'Contraoferta enviada.'];
    }

    // ───────────────────────────────────────────────────────
    // Helper privado
    // ───────────────────────────────────────────────────────

    private function crearMensaje(int $emisorId, int $receptorId, ?int $itemId, ?int $paqueteId, string $texto): void
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
            Log::warning('NegociacionService: no se pudo guardar mensaje', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
