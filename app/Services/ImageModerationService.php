<?php

namespace App\Services;

use App\Events\NuevaNotificacion;
use App\Models\ImagenItem;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ImageModerationService
{
    // ── Item images ───────────────────────────────────────────────

    public function aprobarImagenItem(int $idImagen): ImagenItem
    {
        $imagen = ImagenItem::with('item')->findOrFail($idImagen);
        $imagen->update(['estado' => 'aprobado', 'motivo_rechazo' => null]);

        if ($imagen->item) {
            $nombre = $imagen->item->item ?? 'tu artículo';
            $this->notificar(
                $imagen->item->id_user,
                "Tu imagen del artículo '{$nombre}' ha sido aprobada."
            );
        }

        return $imagen;
    }

    public function rechazarImagenItem(int $idImagen, string $motivo): ImagenItem
    {
        $imagen = ImagenItem::with('item')->findOrFail($idImagen);
        $imagen->update(['estado' => 'rechazado', 'motivo_rechazo' => $motivo]);

        if ($imagen->item) {
            $nombre = $imagen->item->item ?? 'tu artículo';
            $this->notificar(
                $imagen->item->id_user,
                "Tu imagen del artículo '{$nombre}' fue rechazada. Motivo: {$motivo}"
            );
        }

        return $imagen;
    }

    public function aprobarTodasImagenesItem(): int
    {
        $pendientes = ImagenItem::with('item')->where('estado', 'pendiente')->get();

        foreach ($pendientes as $imagen) {
            $imagen->update(['estado' => 'aprobado', 'motivo_rechazo' => null]);

            if ($imagen->item) {
                $nombre = $imagen->item->item ?? 'tu artículo';
                $this->notificar(
                    $imagen->item->id_user,
                    "Tu imagen del artículo '{$nombre}' ha sido aprobada."
                );
            }
        }

        return $pendientes->count();
    }

    // ── Profile photos ────────────────────────────────────────────

    public function aprobarFotoPerfil(int $userId): User
    {
        $user = User::findOrFail($userId);
        $user->update(['foto_perfil_estado' => 'aprobado', 'foto_perfil_motivo_rechazo' => null]);

        $this->notificar($userId, 'Tu foto de perfil ha sido aprobada.');

        return $user;
    }

    public function rechazarFotoPerfil(int $userId, string $motivo): User
    {
        $user = User::findOrFail($userId);
        $user->update(['foto_perfil_estado' => 'rechazado', 'foto_perfil_motivo_rechazo' => $motivo]);

        $this->notificar($userId, "Tu foto de perfil fue rechazada. Motivo: {$motivo}");

        return $user;
    }

    public function aprobarTodasFotosPerfil(): int
    {
        $pendientes = User::whereNotNull('foto_perfil')
            ->where('foto_perfil_estado', 'pendiente')
            ->get();

        foreach ($pendientes as $user) {
            $user->update(['foto_perfil_estado' => 'aprobado', 'foto_perfil_motivo_rechazo' => null]);
            $this->notificar($user->id, 'Tu foto de perfil ha sido aprobada.');
        }

        return $pendientes->count();
    }

    // ── Private helpers ───────────────────────────────────────────

    private function notificar(int $userId, string $mensaje): void
    {
        try {
            Message::create([
                'id_emisor'  => null,
                'id_receptor' => $userId,
                'mensaje'    => $mensaje,
                'leido'      => false,
            ]);

            event(new NuevaNotificacion($mensaje, $userId));
        } catch (\Throwable $e) {
            Log::warning('[ImageModerationService] No se pudo enviar notificación', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
