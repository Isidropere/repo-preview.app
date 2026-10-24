<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\HomeCarousel;
use App\Models\HomeCarouselConfig;
use Illuminate\Http\JsonResponse;

class CarruselApiController extends Controller
{
    /**
     * Retorna las diapositivas activas y la configuración del carrusel para móvil y web.
     */
    public function index(): JsonResponse
    {
        $slides = HomeCarousel::activos()->get();

        $velocidadSegundos = (int) HomeCarouselConfig::get('velocidad_segundos', 4);
        $isAutoplay = (bool) HomeCarouselConfig::get('is_autoplay', 1);

        return response()->json([
            'success'            => true,
            'is_autoplay'        => $isAutoplay,
            'velocidad_segundos' => $velocidadSegundos,
            'velocidad_ms'       => max(1000, $velocidadSegundos * 1000),
            'total'              => $slides->count(),
            'slides'             => $slides,
        ]);
    }
}
