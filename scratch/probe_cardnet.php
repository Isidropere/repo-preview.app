<?php

$baseUrl = "https://labservicios.cardnet.com.do";
$paths = [
    "/api/payment/idempotency-keys",
    "/api/payment/idenpotency-keys",
    "/api/idempotency-keys",
    "/idempotency-keys",
    "/api/payment/transactions/sales",
];

foreach ($paths as $path) {
    $url = $baseUrl . $path;
    echo "Probando POST a: $url ... ";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: text/plain', 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "HTTP $httpCode\n";
    if ($httpCode != 404 && $response) {
        echo "   Respuesta: " . substr($response, 0, 100) . "\n";
    }
    
    curl_close($ch);
}

echo "\nProbando GET a la raíz: $baseUrl ... ";
$ch = curl_init($baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP $httpCode\n";
curl_close($ch);
