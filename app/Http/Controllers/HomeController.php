<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\HomeCarousel;
use App\Models\HomeCarouselConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the application home page.
     */
    public function index()
    {
        // 1. Productos para Intercambio (Cache 10 min)
        $productosIntercambio = Cache::remember('home_intercambio', 600, function () {
            return Item::with([
                    'imagenes:id_imagen,id_item,nombre,ruta,estado',
                    'direccionPredeterminada.municipio:id_municipio,municipio',
                ])
                ->select('id_item', 'item', 'condicion', 'tipo_trans', 'estatus', 'id_user', 'fecha', 'id_categoria_item')
                ->whereIn('tipo_trans', [2, 3])
                ->where('estatus', 1)
                ->latest('fecha')
                ->limit(8)
                ->get();
        });

        // 2. Productos para Venta (Cache 10 min)
        $productosVenta = Cache::remember('home_venta', 600, function () {
            return Item::with([
                    'imagenes:id_imagen,id_item,nombre,ruta,estado',
                    'direccionPredeterminada.municipio:id_municipio,municipio',
                ])
                ->select('id_item', 'item', 'valor', 'condicion', 'tipo_trans', 'estatus', 'id_user', 'fecha', 'id_categoria_item')
                ->where('tipo_trans', 1)
                ->where('estatus', 1)
                ->latest('fecha')
                ->limit(8)
                ->get();
        });

        // 3. Diapositivas y Configuración de Velocidad del Carrusel
        // Consultar directamente de BD para que los cambios (pausar, activar, reordenar) se reflejen al instante
        $carouselSlides = HomeCarousel::activos()->get();

        $carouselSpeedSeconds = (int) HomeCarouselConfig::get('velocidad_segundos', 4);
        $carouselSpeedMs = max(1000, $carouselSpeedSeconds * 1000);

        $carouselAutoplay = (bool) HomeCarouselConfig::get('is_autoplay', 1);

        return view('home', compact(
            'productosIntercambio',
            'productosVenta',
            'carouselSlides',
            'carouselSpeedMs',
            'carouselAutoplay'
        ));
    }
}
