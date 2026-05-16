<?php

// Script de diagnóstico v4 - Siguiendo redirecciones
$hosts = [
    "https://labservicios.cardnet.com.do",
    "https://lab.cardnet.com.do",
];

$paths = [
    "/api/payment/idempotency-keys",
    "/api/idempotency-keys",
    "/idempotency-keys",
    "/idenpotency-keys",
];

echo "=== DIAGNÓSTICO DE CONEXIÓN CARDNET V4 (FOLLOW REDIRECTS) ===\n\n";

foreach ($hosts as $host) {
    foreach ($paths as $path) {
        $url = $host . $path;
        echo "Probando POST a: $url ... ";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // <--- SEGUIR REDIRECCIONES
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/plain',
            'User-Agent: Mozilla/5.0'
        ]);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        
        echo "HTTP $httpCode (Final URL: $finalUrl)\n";
        if ($httpCode >= 200 && $httpCode < 300) {
            echo "   ✅ ¡ÉXITO! Respuesta: $response\n";
        } else {
             echo "   ⚠️ Respuesta: " . substr(strip_tags($response), 0, 80) . "...\n";
        }
        curl_close($ch);
    }
}
