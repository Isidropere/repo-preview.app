<?php

$baseUrl = "https://labservicios.cardnet.com.do/api/payment";
$variants = [
    "/idempotency-keys",
    "/idempotencykeys",
    "/idempotency-key",
    "/idempotencykey",
    "/idenpotency-keys",
    "/idenpotencykeys",
    "/idenpotency-key",
    "/idenpotencykey",
];

foreach ($variants as $v) {
    $url = $baseUrl . $v;
    echo "Probando POST a: $url ... ";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: text/plain']);
    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Short timeout to avoid hanging
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "HTTP $httpCode\n";
    if ($httpCode != 404 && $response) {
        echo "   Respuesta: " . substr($response, 0, 50) . "\n";
    }
    curl_close($ch);
}
