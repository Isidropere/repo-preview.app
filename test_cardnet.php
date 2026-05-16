<?php

// Script para que el USUARIO pruebe todas las variantes de CardNet en su entorno
$baseUrl = "https://labservicios.cardnet.com.do";
$variants = [
    "/api/payment/idempotency-keys",
    "/api/payment/idenpotency-keys",
    "/api/idempotency-keys",
    "/api/idenpotency-keys",
    "/idempotency-keys",
    "/idenpotency-keys",
];

$methods = ["POST", "GET"];

echo "=== DIAGNÓSTICO DE CONEXIÓN CARDNET ===\n\n";

foreach ($methods as $method) {
    foreach ($variants as $v) {
        $url = $baseUrl . $v;
        echo "Probando $method a: $url ... ";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method === "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: text/plain']);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            echo "ERROR CURL: " . curl_error($ch) . "\n";
        } else {
            echo "HTTP $httpCode\n";
            if ($httpCode >= 200 && $httpCode < 300) {
                echo "   ✅ ¡ÉXITO! Respuesta: $response\n";
            } elseif ($httpCode != 404) {
                echo "   ⚠️ Respuesta inesperada: " . substr(strip_tags($response), 0, 100) . "\n";
            }
        }
        curl_close($ch);
    }
}

echo "\nPrueba de URL raíz para descartar problemas de dominio:\n";
$ch = curl_init("https://labservicios.cardnet.com.do/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_exec($ch);
$rootCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Raíz (labservicios.cardnet.com.do): HTTP $rootCode\n";
curl_close($ch);
