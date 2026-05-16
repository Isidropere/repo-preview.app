<?php
$baseUrl = 'http://127.0.0.1:8000';
$cookieFileJuan = __DIR__ . '/cookie_juan.txt';

function request($url, $method = 'GET', $data = [], $cookieFile = null) {
    global $baseUrl;
    $ch = curl_init($baseUrl . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);
    
    return ['code' => $httpCode, 'headers' => $headers, 'body' => $body];
}

$res = request('/carrito', 'GET', [], $cookieFileJuan);
preg_match('/meta name="csrf-token" content="(.*?)"/', $res['body'], $matches);
$csrfJuan = $matches[1] ?? '';

$res = request('/carrito/checkout?tipo=servicio', 'GET', [], $cookieFileJuan);
preg_match('/value="([a-f0-9\-]{36})"/', $res['body'], $matches);
$idTarjeta = $matches[1] ?? null;

if (!$idTarjeta) {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $tarjeta = \App\Models\TarjetaPago::where('id_user', 8)->first();
    $idTarjeta = $tarjeta->id_tarjeta ?? null;
}

if ($idTarjeta) {
    echo "Enviando confirmación de solicitud a /carrito/pago...\n";
    $data = [
        '_token' => $csrfJuan,
        'id_tarjeta' => $idTarjeta,
        'cvv' => '123'
    ];
    
    $resPost = request('/carrito/pago', 'POST', $data, $cookieFileJuan);
    echo "Respuesta POST checkout: {$resPost['code']}\n";
    if ($resPost['code'] == 500) {
        echo "ERROR 500. Extracto del body:\n";
        echo strip_tags(substr($resPost['body'], 0, 3000));
    }
}
