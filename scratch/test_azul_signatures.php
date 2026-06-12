<?php

/**
 * =========================================================================
 * Script de Verificación de Firmas AuthHash de AZUL
 * =========================================================================
 *
 * Valida la lógica de generación y validación de firmas HMAC-SHA512
 * utilizando codificación UTF-16LE de forma nativa e independiente.
 *
 * Ejecutar con: php scratch/test_azul_signatures.php
 */

// 1. Bootstrapear Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Services\Payments\AzulProvider;

function assertSignature(bool $condition, string $message) {
    if (!$condition) {
        throw new \Exception("ASERCION_FALLIDA: " . $message);
    }
    echo "✓ PASS: {$message}\n";
}

echo "=== Iniciando Pruebas de Firmas de AZUL ===\n";

try {
    // 2. Resolver el proveedor de pago AZUL
    $provider = app(AzulProvider::class);
    
    // Temporalmente establecer un AuthKey fijo para la prueba en caso de que esté vacío en .env
    $reflection = new \ReflectionClass($provider);
    $authKeyProperty = $reflection->getProperty('authKey');
    $authKeyProperty->setAccessible(true);
    
    $testKey = 'super_secret_auth_key_12345';
    $authKeyProperty->setValue($provider, $testKey);
    
    echo "AuthKey de pruebas configurada: '{$testKey}'\n\n";

    // -------------------------------------------------------------------------
    // TEST 1: Generación de Formulario y AuthHash de Solicitud (Request)
    // -------------------------------------------------------------------------
    echo "Ejecutando TEST 1: Generación de AuthHash para Formulario...\n";
    
    $monto = 150.75; // 15075 centavos
    $orderNumber = 'ORD-998877';
    $opciones = ['tax' => 20.50]; // 2050 centavos de ITBIS
    
    $formulario = $provider->generarCamposFormulario($monto, $orderNumber, $opciones);
    
    assertSignature(isset($formulario['url']), "La respuesta debe contener la URL del Payment Page.");
    assertSignature(isset($formulario['fields']['AuthHash']), "La respuesta debe contener el campo AuthHash.");
    assertSignature($formulario['fields']['Amount'] === '15075', "El Amount debe formatearse en centavos: '15075'.");
    assertSignature($formulario['fields']['ITBIS'] === '2050', "El ITBIS debe formatearse en centavos: '2050'.");
    
    // Validar manualmente la construcción del hash del request
    $expectedConcat = $formulario['fields']['MerchantId'] .
                      $formulario['fields']['MerchantName'] .
                      $formulario['fields']['MerchantType'] .
                      $formulario['fields']['CurrencyCode'] .
                      $formulario['fields']['OrderNumber'] .
                      $formulario['fields']['Amount'] .
                      $formulario['fields']['ITBIS'] .
                      $formulario['fields']['ApprovedUrl'] .
                      $formulario['fields']['DeclinedUrl'] .
                      $formulario['fields']['CancelUrl'] .
                      $formulario['fields']['UseCustomField1'] .
                      $formulario['fields']['CustomField1Label'] .
                      $formulario['fields']['CustomField1Value'] .
                      $formulario['fields']['UseCustomField2'] .
                      $formulario['fields']['CustomField2Label'] .
                      $formulario['fields']['CustomField2Value'] .
                      $testKey;
                      
    $expectedUtf16 = mb_convert_encoding($expectedConcat, 'UTF-16LE', 'UTF-8');
    $expectedHash = hash_hmac('sha512', $expectedUtf16, $testKey);
    
    assertSignature($formulario['fields']['AuthHash'] === $expectedHash, "El AuthHash generado coincide exactamente con la fórmula matemática esperada.");
    echo "TEST 1 Completado con éxito.\n\n";

    // -------------------------------------------------------------------------
    // TEST 2: Validación de Firma de Respuesta (Response) Válida
    // -------------------------------------------------------------------------
    echo "Ejecutando TEST 2: Validación de Firma de Respuesta Aprobada...\n";
    
    $responseParams = [
        'OrderNumber'       => $orderNumber,
        'Amount'            => '15075',
        'AuthorizationCode' => 'AUTH123456',
        'DateTime'          => '20260611170000',
        'ResponseCode'      => 'ISO8583',
        'IsoCode'           => '00',
        'ResponseMessage'   => 'APROBADA',
        'ErrorDescription'  => '',
        'RRN'               => '998877665544',
    ];
    
    // Generar el AuthHash esperado de la respuesta para el test
    $responseConcat = $responseParams['OrderNumber'] .
                      $responseParams['Amount'] .
                      $responseParams['AuthorizationCode'] .
                      $responseParams['DateTime'] .
                      $responseParams['ResponseCode'] .
                      $responseParams['IsoCode'] .
                      $responseParams['ResponseMessage'] .
                      $responseParams['ErrorDescription'] .
                      $responseParams['RRN'] .
                      $testKey;
                      
    $responseUtf16 = mb_convert_encoding($responseConcat, 'UTF-16LE', 'UTF-8');
    $responseHash = hash_hmac('sha512', $responseUtf16, $testKey);
    $responseParams['AuthHash'] = $responseHash;
    
    // Validar usando el proveedor
    $isValid = $provider->validarFirmaRespuesta($responseParams);
    assertSignature($isValid === true, "La firma de respuesta legítima fue validada como correcta.");
    echo "TEST 2 Completado con éxito.\n\n";

    // -------------------------------------------------------------------------
    // TEST 3: Detección y Rechazo de Respuestas Alteradas (Hackeo)
    // -------------------------------------------------------------------------
    echo "Ejecutando TEST 3: Detección de alteraciones en el payload...\n";
    
    // Caso A: El monto fue alterado maliciosamente en tránsito
    $alteredParamsA = $responseParams;
    $alteredParamsA['Amount'] = '50'; // Cambiar monto de 150.75 a 0.50
    $isValidA = $provider->validarFirmaRespuesta($alteredParamsA);
    assertSignature($isValidA === false, "Se detectó y rechazó correctamente la alteración del monto.");
    
    // Caso B: El AuthHash fue alterado por un atacante
    $alteredParamsB = $responseParams;
    $alteredParamsB['AuthHash'] = str_replace('a', 'b', $responseParams['AuthHash']);
    $isValidB = $provider->validarFirmaRespuesta($alteredParamsB);
    assertSignature($isValidB === false, "Se detectó y rechazó correctamente la alteración de la firma.");
    
    // Caso C: El OrderNumber fue modificado
    $alteredParamsC = $responseParams;
    $alteredParamsC['OrderNumber'] = 'ORD-666666';
    $isValidC = $provider->validarFirmaRespuesta($alteredParamsC);
    assertSignature($isValidC === false, "Se detectó y rechazó la alteración del código de orden.");
    
    echo "TEST 3 Completado con éxito.\n\n";
    
    echo "🎉 TODOS LOS TESTS DE FIRMA PASARON SATISFACTORIAMENTE.\n";

} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
