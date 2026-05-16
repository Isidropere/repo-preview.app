<?php

// Script de diagnóstico v3
$hosts = [
    "https://labservicios.cardnet.com.do",
    "https://lab.cardnet.com.do",
];

$paths = [
    "/api/payment/idempotency-keys",
    "/api/payment/idenpotency-keys",
    "/api/idempotency-keys",
    "/api/idenpotency-keys",
    "/idempotency-keys",
    "/idenpotency-keys",
    "/api/payment/transactions/sales",
];

echo "=== DIAGNÓSTICO DE CONEXIÓN CARDNET V3 ===\n\n";

foreach ($hosts as $host) {
    foreach ($paths as $path) {
        $url = $host . $path;
        echo "Probando POST a: $url ... ";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        // Intentamos enviar SIN Content-Type primero, solo Accept
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/plain',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        echo "HTTP $httpCode\n";
        if ($httpCode >= 200 && $httpCode < 300) {
            echo "   ✅ ¡ÉXITO! Respuesta: $response\n";
        } elseif ($httpCode != 404) {
             echo "   ⚠️ Respuesta: " . substr(strip_tags($response), 0, 80) . "...\n";
        }
        curl_close($ch);
    }
}
