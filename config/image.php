<?php
return [
    // Máxima anchura/altura antes de generar variantes (px)
    'max_width'   => env('IMAGE_MAX_WIDTH', 2000),
    'max_height'  => env('IMAGE_MAX_HEIGHT', 2000),

    // Tamaños de las variantes (anchura, altura)
    'sizes' => [
        'thumb'  => [200, 200],
        'medium' => [800, 800],
        'large'  => [1200, 1200],
    ],

    // Calidad de compresión WebP (0‑100)
    'quality' => env('IMAGE_QUALITY', 85),

    // Tipos MIME permitidos
    'allowed_mime' => [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/gif',
        'image/webp',
    ],

    // Disco de almacenamiento (solo local)
    'disk' => env('IMAGE_DISK', 'public'),

    // Generar AVIF (desactivado por ahora)
    'generate_avif' => env('IMAGE_GENERATE_AVIF', false),
];
