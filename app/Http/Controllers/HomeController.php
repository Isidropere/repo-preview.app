<?php

namespace App\Http\Controllers;

use App\Models\Item;
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

        return view('home', compact('productosIntercambio', 'productosVenta'));
    }
}
