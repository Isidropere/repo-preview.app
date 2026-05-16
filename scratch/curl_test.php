<?php
$baseUrl = 'http://127.0.0.1:8000';
$cookieFileJuan = __DIR__ . '/cookie_juan.txt';
$cookieFileJulio = __DIR__ . '/cookie_julio.txt';

if (file_exists($cookieFileJuan)) unlink($cookieFileJuan);
if (file_exists($cookieFileJulio)) unlink($cookieFileJulio);

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

function login($email, $password, $cookieFile) {
    echo "Iniciando sesión con $email...\n";
    $res = request('/login', 'GET', [], $cookieFile);
    preg_match('/name="_token" value="(.*?)"/', $res['body'], $matches);
    $csrfToken = $matches[1] ?? '';
    
    $res = request('/login', 'POST', [
        '_token' => $csrfToken,
        'email' => $email,
        'password' => $password
    ], $cookieFile);
    
    return $csrfToken;
}

// 1. Login Juan (Comprador)
$csrfJuan = login('juan.guzman@gmail.com', 'password123', $cookieFileJuan);
echo "Juan logueado.\n";

// 2. Juan añade el talento 142 al carrito
// Necesito obtener un nuevo CSRF token de la home porque la sesión cambió tras el login
$res = request('/home', 'GET', [], $cookieFileJuan);
preg_match('/meta name="csrf-token" content="(.*?)"/', $res['body'], $matches);
$csrfJuan = $matches[1] ?? $csrfJuan;

echo "Añadiendo talento 142 al carrito...\n";
$res = request('/carrito/agregar', 'POST', [
    '_token' => $csrfJuan,
    'id_item' => 142,
    'cantidad' => 1
], $cookieFileJuan);

echo "Respuesta agregar carrito: {$res['code']}\n";

// 3. Juan selecciona el item en el carrito para ir al checkout
// Para seleccionar, necesitamos el id_cart_item. Primero vemos el carrito
$res = request('/carrito', 'GET', [], $cookieFileJuan);
// Extraemos el id_cart_item
preg_match('/name="ids\[\]" value="(.*?)"/', $res['body'], $matches);
$idCartItem = $matches[1] ?? null;

if ($idCartItem) {
    echo "Item en carrito encontrado: ID $idCartItem\n";
    // Marcar como seleccionado
    $res = request('/carrito/seleccionar', 'POST', [
        '_token' => $csrfJuan,
        'ids' => [$idCartItem]
    ], $cookieFileJuan);
    
    echo "Respuesta seleccionar: {$res['code']}\n";
    
    // Ir a checkout
    $res = request('/checkout', 'GET', [], $cookieFileJuan);
    echo "Respuesta ir a checkout: {$res['code']}\n";
} else {
    echo "No se pudo encontrar el item en el carrito.\n";
}

echo "\n--- FLUJO COMPLETADO SIMULADO ---\n";
