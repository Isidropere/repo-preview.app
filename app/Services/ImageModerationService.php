<?php

namespace App\Services;

use App\Events\NuevaNotificacion;
use App\Models\ImagenItem;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImageModerationService
{
    // ── Item images ───────────────────────────────────────────────

    public function aprobarImagenItem(int $idImagen): ImagenItem
    {
        $imagen = ImagenItem::with('item')->findOrFail($idImagen);
        $imagen->update(['estado' => 'aprobado', 'motivo_rechazo' => null]);

        if ($imagen->item) {
            $item = $imagen->item;
            $nombre = $item->item ?? 'tu artículo';

            $tieneOtrasNoAprobadas = $item->todasLasImagenes()
                ->where('id_imagen', '!=', $idImagen)
                ->where('estado', '!=', 'aprobado')
                ->exists();

            if (!$tieneOtrasNoAprobadas) {
                $item->update([
                    'estatus' => 1,
                    'estado_aprobacion' => 'aprobado',
                ]);

                $this->notificar(
                    $item->id_user,
                    "Tu producto '{$nombre}' ha sido aprobado y ya está visible en la plataforma."
                );
            } else {
                $this->notificar(
                    $item->id_user,
                    "Tu imagen del artículo '{$nombre}' ha sido aprobada."
                );
            }
        }

        Cache::forget('home_intercambio');
        Cache::forget('home_venta');
        Cache::flush();

        return $imagen;
    }

    public function rechazarImagenItem(int $idImagen, string $motivo): ImagenItem
    {
        $imagen = ImagenItem::with('item')->findOrFail($idImagen);
        $imagen->update(['estado' => 'rechazado', 'motivo_rechazo' => $motivo]);

        if ($imagen->item) {
            $item = $imagen->item;
            $nombre = $item->item ?? 'tu artículo';

            $item->update([
                'estatus' => 0,
                'estado_aprobacion' => 'rechazado',
            ]);

            $this->notificar(
                $item->id_user,
                "Tu imagen del artículo '{$nombre}' fue rechazada. Motivo: {$motivo}"
            );
        }

        Cache::forget('home_intercambio');
        Cache::forget('home_venta');
        Cache::flush();

        return $imagen;
    }

    public function aprobarTodasImagenesItem(): int
    {
        $pendientes = ImagenItem::with('item')->where('estado', 'pendiente')->get();

        $itemsAfectados = [];

        foreach ($pendientes as $imagen) {
            $imagen->update(['estado' => 'aprobado', 'motivo_rechazo' => null]);

            if ($imagen->item) {
                $itemsAfectados[$imagen->item->id_item] = $imagen->item;
            }
        }

        foreach ($itemsAfectados as $item) {
            $tieneOtrasNoAprobadas = $item->todasLasImagenes()
                ->where('estado', '!=', 'aprobado')
                ->exists();

            $nombre = $item->item ?? 'tu artículo';

            if (!$tieneOtrasNoAprobadas) {
                $item->update([
                    'estatus' => 1,
                    'estado_aprobacion' => 'aprobado',
                ]);

                $this->notificar(
                    $item->id_user,
                    "Tu producto '{$nombre}' ha sido aprobado y ya está visible en la plataforma."
                );
            } else {
                $this->notificar(
                    $item->id_user,
                    "Imágenes de tu artículo '{$nombre}' han sido aprobadas."
                );
            }
        }

        Cache::forget('home_intercambio');
        Cache::forget('home_venta');
        Cache::flush();

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
        $pendientes = User::where(function ($q) {
                $q->whereNotNull('foto_perfil')->orWhereNotNull('profile_photo_path');
            })
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
