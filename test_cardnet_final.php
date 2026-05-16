<?php

// Script de diagnóstico FINAL
$baseUrl = "https://labservicios.cardnet.com.do";
$prefixes = ["/api/payment", "/api", ""];
$endpoints = [
    "/idempotency-keys",
    "/idenpotency-keys",
    "/idempotencykeys",
    "/idenpotencykeys",
    "/idempotency-key",
    "/idenpotency-key",
    "/transactions/sales",
    "/transactions/sale",
];

echo "=== DIAGNÓSTICO MASIVO CARDNET ===\n\n";

foreach ($prefixes as $prefix) {
    foreach ($endpoints as $endpoint) {
        $url = $baseUrl . $prefix . $endpoint;
        echo "Probando POST a: $url ... ";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/plain',
            'Content-Type: application/json',
            'X-Requested-With: XMLHttpRequest'
        ]);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        echo "HTTP $httpCode\n";
        if ($httpCode >= 200 && $httpCode < 300) {
            echo "   ✅ ¡ENCONTRADO! Respuesta: $response\n";
            exit;
        }
        curl_close($ch);
    }
}

echo "\nNo se encontró el endpoint. Probando si el servidor responde a un GET simple en la raíz...\n";
$ch = curl_init($baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Raíz: HTTP $httpCode\n";
curl_close($ch);
