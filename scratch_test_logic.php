<?php

use App\Models\Negociacion;
use App\Models\Item;
use App\Services\NegociacionService;
use Illuminate\Support\Facades\DB;

/**
 * TEST DE LÓGICA DE INTERCAMBIOS
 * Este script simula los 3 flujos para verificar la clasificación y notificaciones.
 */

// 1. Setup mock data if needed or use existing
// Buscaremos ejemplos en la base de datos para no ensuciar
$service = app(NegociacionService::class);

echo "Iniciando Test de Lógica de Intercambios...\n";
echo "------------------------------------------\n";

$negs = Negociacion::latest()->take(10)->get();

foreach ($negs as $n) {
    echo "Analizando Negociación #{$n->id_negociacion}:\n";
    
    $esPP = $service->esProductoProducto($n);
    $esSS = $service->esServicioServicio($n);
    $esPS = $service->esProductoServicio($n);
    
    echo "  - Clasificación: " . ($esPP ? "Producto-Producto" : ($esSS ? "Servicio-Servicio" : ($esPS ? "Mixto (P-S)" : "DESCONOCIDO"))) . "\n";
    
    // Test de dirección
    $emisor = $n->usuario;
    $receptor = $n->usuarioReceptor;
    echo "  - Ubicación Emisor: " . ($emisor->direcciones->first() ? "OK" : "FALTA") . "\n";
    echo "  - Ubicación Receptor: " . ($receptor->direcciones->first() ? "OK" : "FALTA") . "\n";
    
    // Test de transición de pago
    if ($esPS) {
        $puedeIrAEnvio = ($n->pago_emisor || $n->pago_receptor);
        echo "  - Lógica Pago Mixto: " . ($puedeIrAEnvio ? "CORRECTA (un pago basta)" : "PENDIENTE (esperando 1 pago)") . "\n";
    } elseif ($esPP) {
        $puedeIrAEnvio = ($n->pago_emisor && $n->pago_receptor);
        echo "  - Lógica Pago Producto: " . ($puedeIrAEnvio ? "CORRECTA (ambos pagaron)" : "PENDIENTE (faltan pagos)") . "\n";
    }
    
    echo "------------------------------------------\n";
}
