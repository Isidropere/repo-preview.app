<?php

// Script de diagnóstico v5 - Añadiendo headers de API
$hosts = [
    "https://labservicios.cardnet.com.do",
];

$paths = [
    "/api/payment/idempotency-keys",
    "/api/payment/idenpotency-keys",
    "/api/idempotency-keys",
    "/idempotency-keys",
    "/idenpotency-keys",
];

echo "=== DIAGNÓSTICO DE CONEXIÓN CARDNET V5 (API HEADERS) ===\n\n";

foreach ($paths as $path) {
    foreach ($hosts as $host) {
        $url = $host . $path;
        echo "Probando POST a: $url ... ";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/plain',
            'Content-Type: application/json',
            'X-Requested-With: XMLHttpRequest',
            'User-Agent: CardNet-API-Integration/1.0'
        ]);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        echo "HTTP $httpCode\n";
        if ($httpCode >= 200 && $httpCode < 300) {
            echo "   ✅ ¡ÉXITO! Respuesta: $response\n";
        } else {
             echo "   ⚠️ Respuesta: " . substr(strip_tags($response), 0, 100) . "...\n";
        }
        curl_close($ch);
    }
}
