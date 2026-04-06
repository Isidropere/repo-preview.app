<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Helper centralizado para guardar y resolver rutas de imágenes.
 * Usa public_path() directamente para compatibilidad con hosting compartido (MochaHost).
 * No depende de symlinks ni Storage::disk('public').
 */
class ImageHelper
{
    // Imágenes por defecto según tipo
    const DEFAULT_PRODUCTO  = '/imgs/defaults/producto_default.svg';
    const DEFAULT_SERVICIO  = '/imgs/defaults/servicio_default.svg';
    const DEFAULT_PROFILE   = '/imgs/defaults/profile_default.svg';

    /**
     * Guarda un archivo en public_path directamente.
     * Retorna la ruta relativa desde public/ (ej: "imgs/articulos/items/item_1_xxx.jpg")
     */
    public static function guardar(UploadedFile $file, string $directory, string $prefix, int $id): array
    {
        $isVideo = str_starts_with($file->getMimeType(), 'video/');
        $ext = $file->extension();
        $fileName = $prefix . $id . '_' . now()->format('YmdHis') . '_' . Str::random(10) . '.' . $ext;

        $destino = public_path($directory);
        if (!file_exists($destino)) {
            mkdir($destino, 0755, true);
        }

        $file->move($destino, $fileName);

        return [
            'fileName' => $fileName,
            'path'     => $directory . '/' . $fileName,
            'is_video' => $isVideo,
            'ruta'     => $directory,
        ];
    }

    /**
     * Guarda contenido raw (string) en public_path.
     * Usado para mover archivos desde temp (Storage::disk('local')).
     */
    public static function guardarContenido(string $contenido, string $directory, string $fileName): string
    {
        $destino = public_path($directory);
        if (!file_exists($destino)) {
            mkdir($destino, 0755, true);
        }

        file_put_contents($destino . '/' . $fileName, $contenido);

        return $directory . '/' . $fileName;
    }

    /**
     * Elimina un archivo de public_path.
     */
    public static function eliminar(string $rutaRelativa): void
    {
        $fullPath = public_path($rutaRelativa);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }

    /**
     * Resuelve la URL de una imagen de item.
     * Si no existe, retorna la imagen por defecto según categoría.
     */
    public static function urlItem(?object $imagen, int $categoriaId = 0): string
    {
        if ($imagen && $imagen->nombre) {
            $ruta = ($imagen->ruta ?? 'imgs/articulos/items') . '/' . $imagen->nombre;
            if (file_exists(public_path($ruta))) {
                return asset($ruta);
            }
            // Fallback: buscar en storage/
            $rutaStorage = 'storage/' . $ruta;
            if (file_exists(public_path($rutaStorage))) {
                return asset($rutaStorage);
            }
        }

        // Imagen por defecto según categoría
        return asset($categoriaId == 29 ? self::DEFAULT_SERVICIO : self::DEFAULT_PRODUCTO);
    }

    /**
     * Resuelve la URL de una imagen/video usando ruta y nombre.
     * Busca primero en public_path directo, luego en storage/.
     */
    public static function urlMedia(string $ruta, string $nombre): string
    {
        // Primero buscar directo en public/
        $directPath = $ruta . '/' . $nombre;
        if (file_exists(public_path($directPath))) {
            return asset($directPath);
        }
        // Fallback: buscar en public/storage/
        $storagePath = 'storage/' . $directPath;
        if (file_exists(public_path($storagePath))) {
            return asset($storagePath);
        }
        // Retornar la ruta directa aunque no exista (para que el onerror del img maneje)
        return asset($directPath);
    }

    /**
     * Resuelve la URL de una foto de perfil.
     */
    public static function urlPerfil(?string $fotoPath): string
    {
        if ($fotoPath && file_exists(public_path($fotoPath))) {
            return asset($fotoPath);
        }
        return asset(self::DEFAULT_PROFILE);
    }
}
