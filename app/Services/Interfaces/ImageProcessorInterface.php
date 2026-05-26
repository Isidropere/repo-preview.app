<?php

namespace App\Services\Interfaces;

use App\Models\ImageFile;

interface ImageProcessorInterface
{
    /**
     * Process an uploaded image: orientate, resize, generate variants, store and return a DB record.
     *
     * @param string $absolutePath   Absolute path to the temporary uploaded file.
     * @param int    $userId         ID of the user that uploaded the image.
     * @return ImageFile
     */
    public function process(string $absolutePath, int $userId): ImageFile;
}
