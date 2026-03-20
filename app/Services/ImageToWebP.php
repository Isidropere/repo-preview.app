<?php

namespace App\Services;

use Imagick;
use Exception;

class ImageToWebP
{
    // Ruta predeterminada donde se guardarán las imágenes convertidas
    protected $outputPath;

    // Constructor: puedes pasar la ruta de salida o usar la predeterminada
    public function __construct($outputPath = null)
    {
        $this->outputPath = $outputPath ?: public_path('imagenes_convertidas');  // Usará la ruta predeterminada si no se proporciona
    }

    // Método para convertir una sola imagen
    public function convertImageToWebP($inputImage, $width = 800, $height = 600, $quality = 85)
    {
        try {
            $image = new Imagick($inputImage);

            // Redimensionar la imagen si es necesario
            $image->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);

            // Generar el nombre de la imagen convertida
            $outputFileName = pathinfo($inputImage, PATHINFO_FILENAME) . '.webp';
            $outputFilePath = $this->outputPath . DIRECTORY_SEPARATOR . $outputFileName;

            // Convertir y guardar la imagen en formato WebP
            $image->setImageFormat('webp');
            $image->setImageCompressionQuality($quality);
            $image->writeImage($outputFilePath);  // Guarda la imagen convertida

            $image->clear();
            $image->destroy();

            return $outputFilePath; // Devuelve la ruta donde se guardó la imagen

        } catch (Exception $e) {
            return 'Error al convertir la imagen: ' . $e->getMessage();
        }
    }
}
