<?php

namespace App\Services\Implementations;

use App\Models\ImageFile;
use App\Services\Interfaces\ImageProcessorInterface;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Illuminate\Support\Str;

class ImageProcessor implements ImageProcessorInterface
{
    /**
     * Process an uploaded image: orientate, resize, generate variants, store and return a DB record.
     */
    public function process(string $absolutePath, int $userId): ImageFile
    {
        // Generar un UUID para el conjunto de imágenes
        $uuid = (string) Str::uuid();
        $disk = config('image.disk');
        $baseDir = "images/{$uuid}"; // carpeta dentro del disco public

        // Cargar la imagen con Intervention y corregir orientación EXIF
        $img = Image::make($absolutePath)->orientate();

        // Obtener MIME original
        $mime = $img->mime();

        // Guardar la versión original (sin cambios) en el mismo directorio
        $originalExtension = $img->extension ?: 'jpg';
        $originalName = "original.{$originalExtension}";
        Storage::disk($disk)->put("{$baseDir}/{$originalName}", (string) $img->encode());

        // Generar variantes según config/image.php
        $variants = [];
        $sizes = config('image.sizes');
        $quality = config('image.quality');

        foreach ($sizes as $variant => $dims) {
            [$w, $h] = $dims;
            $clone = clone $img;
            $clone->fit($w, $h, function ($constraint) {
                $constraint->upsize();
            });
            // Convertir a WebP con calidad configurada
            $variantName = "{$variant}.webp";
            $clone->encode('webp', $quality);
            Storage::disk($disk)->put("{$baseDir}/{$variantName}", (string) $clone);
            $variants[$variant] = Storage::url("{$baseDir}/{$variantName}");
        }

        // Optimizar la imagen original y variantes usando spatie optimizer
        $optimizer = OptimizerChainFactory::create();
        // Optimizar original
        $originalPath = Storage::disk($disk)->path("{$baseDir}/{$originalName}");
        $optimizer->optimize($originalPath);
        // Optimizar cada variante
        foreach (array_keys($sizes) as $variant) {
            $variantPath = Storage::disk($disk)->path("{$baseDir}/{$variant}.webp");
            $optimizer->optimize($variantPath);
        }

        // Registrar en la base de datos
        $imageRecord = ImageFile::create([
            'user_id'       => $userId,
            'original_path' => Storage::url("{$baseDir}/{$originalName}"),
            'variants'      => $variants,
            'mime'          => $mime,
            'size'          => filesize($absolutePath),
        ]);

        return $imageRecord;
    }
}
