# Integración CardNet / Ztrans — CambialoRD

## Resumen

CardNet es el procesador de pagos con tarjeta utilizado en la plataforma. La integración usa la API REST de CardNet con protocolo TLS 1.2.

---

## Configuración (.env)

```env
PAYMENT_DRIVER=cardnet
CARDNET_ENV=QA                    # QA = pruebas | production = producción
CARDNET_MERCHANT_ID=349041263     # Número de afiliado (15 chars)
CARDNET_TERMINAL_ID=77777777      # Terminal ID (8 chars)
CARDNET_TOKEN=454500350001        # Token de la aplicación
CARDNET_ENVIRONMENT=ECommerce     # Tipo de ambiente
```

### URLs según ambiente
| Ambiente    | URL Base                                          |
|-------------|---------------------------------------------------|
| QA          | `https://labservicios.cardnet.com.do/api/payment` |
| Producción  | `https://ecommerce.cardnet.com.do/api/payment`    |

---

## Archivos involucrados

### Configuración
| Archivo | Descripción |
|---------|-------------|
| `.env` | Variables CARDNET_* y PAYMENT_DRIVER |
| `config/services.php` | Lee las variables de .env, define el driver activo |

### Contratos e interfaces
| Archivo | Descripción |
|---------|-------------|
| `app/Contracts/PaymentProviderInterface.php` | Interfaz que implementan todos los proveedores (cobrar, anular, reembolsar) |

### Proveedores de pago
| Archivo | Descripción |
|---------|-------------|
| `app/Services/Payments/CardnetProvider.php` | Implementación de CardNet (cobro, anulación, reembolso) |
| `app/Services/Payments/StripeProvider.php` | Implementación alternativa de Stripe |
| `app/Services/PagoService.php` | Servicio central que resuelve el driver activo y delega las operaciones |

### Modelo de tarjetas
| Archivo | Descripción |
|---------|-------------|
| `app/Models/TarjetaPago.php` | Modelo de tarjetas guardadas. Encripta números con AES-256-CBC (APP_KEY). Método `datosCardnet($cvv)` formatea datos para la API |

### Controladores que procesan pagos
| Archivo | Método | Flujo |
|---------|--------|-------|
| `app/Http/Controllers/PagoController.php` | `procesar()` | Checkout de carrito → `CheckoutService` → `PagoService` → CardNet |
| `app/Http/Controllers/ItemController.php` | `AddTalento()` | Publicar talento (cat 29) → cobra registro → `PagoService` → CardNet |
| `app/Http/Controllers/TalentoRegistroPagoController.php` | `procesarPago()` | Flujo alternativo de pago talento → `TalentoRegistroPagoService` → CardNet |
| `app/Http/Controllers/NegociacionController.php` | `procesarPago()` | Pago de envío en intercambios → `PagoService` → CardNet |

### Servicios de negocio
| Archivo | Descripción |
|---------|-------------|
| `app/Services/CheckoutService.php` | Orquesta el checkout: valida stock, cobra, registra pago, reembolsa si falla |
| `app/Services/TalentoRegistroPagoService.php` | Orquesta el pago de registro de talento |

---

## Flujo de cobro CardNet

```
1. Obtener idempotency-key
   POST {baseUrl}/idenpotency-keys
   → Retorna: "ikey:XXXXXXXX"

2. Procesar venta
   POST {baseUrl}/transactions/sales
   Body: {
     idempotency-key, merchant-id, terminal-id, token,
     card-number, expiration-date, cvv,
     amount, currency (214 = DOP),
     environment, invoice-number, reference-number,
     client-ip, tax, tip
   }
   → Retorna: { response-code, internal-response-code, pnRef, approval-code, ... }

3. Verificar respuesta
   Aprobada: response-code === "00" AND internal-response-code === "0000"
```

### Timeouts y reintentos
- Timeout: 60 segundos
- Reintentos: 2 (con 1 segundo entre cada uno)
- El servidor `php artisan serve` es single-threaded, lo que puede causar "Host unreachable" durante llamadas largas a CardNet

---

## Operaciones disponibles

### Cobro (Sale)
```php
$pagoService = app(PagoService::class);
$resultado = $pagoService->cobrarTarjeta(
    monto: 1500.00,
    currency: '214',           // DOP
    datosTarjeta: $tarjeta->datosCardnet($cvv),
    opciones: [
        'client_ip' => $request->ip(),
        'invoice_number' => 'INV123456',
    ]
);
// $resultado['success'] → bool
// $resultado['transaction_id'] → pnRef
// $resultado['error'] → mensaje si falló
```

### Anulación (Void)
Solo antes del cierre diario (7PM).
```php
$resultado = $pagoService->anularTransaccion($transactionId, $monto, [
    'pn_ref' => $pnRef,
    'client_ip' => $request->ip(),
]);
```

### Reembolso (Refund)
Parcial o total, después del cierre.
```php
$resultado = $pagoService->reembolsar($transactionId, $monto, [
    'tx_token' => $txToken,
    'client_ip' => $request->ip(),
]);
```

---

## Almacenamiento de tarjetas

- Tabla: `tarjetas_pagos`
- PK: `id_tarjeta` (UUID)
- El número de tarjeta se encripta con `Crypt::encryptString()` (AES-256-CBC usando APP_KEY)
- Solo se expone `last4` en las vistas
- Al cobrar, se desencripta con `getNumeroDesencriptado()`
- La expiración se valida antes de enviar a CardNet

### Columnas principales
| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_tarjeta` | UUID | PK |
| `no_tarjeta` | string (encriptado) | Número completo encriptado |
| `last4` | string | Últimos 4 dígitos (para mostrar) |
| `tipo_tarjeta` | string | visa, mastercard, amex |
| `mes_expiracion` | int | MM |
| `año_expiracion` | int | YYYY |
| `cvv` | — | NO se almacena, se pide en cada transacción |
| `nombre_titular` | string | Nombre en la tarjeta |
| `id_user` | int | FK a users |
| `estatus` | int | 1=activa |

---

## Moneda

- Código ISO: `214` (Peso Dominicano / DOP)
- Todos los montos se envían como float sin formato

---

## Códigos de respuesta comunes

| Código | Descripción |
|--------|-------------|
| 00 | Aprobada |
| 51 | Fondos insuficientes |
| 54 | Tarjeta expirada |
| 91 | Host unreachable (problema de CardNet, no del código) |
| 05 | No autorizada |

---

## Rutas relacionadas

| Ruta | Método | Descripción |
|------|--------|-------------|
| `POST /carrito/pago` | `PagoController@procesar` | Pago de checkout |
| `POST /talento/pago` | `TalentoRegistroPagoController@procesarPago` | Pago registro talento |
| `POST /negociaciones/{id}/pago` | `NegociacionController@procesarPago` | Pago envío intercambio |
| `POST /carrito/tarjetas` | `TarjetaPagoController@store` | Guardar nueva tarjeta |
| `DELETE /carrito/tarjetas/{id}` | `TarjetaPagoController@destroy` | Eliminar tarjeta |

---

## Notas importantes

1. **CVV nunca se almacena** — se pide al usuario en cada transacción
2. **APP_KEY es crítico** — si cambia, las tarjetas encriptadas no se podrán desencriptar
3. **QA vs Producción** — cambiar `CARDNET_ENV=production` en .env y actualizar credenciales
4. **Single-threaded** — `php artisan serve` puede dar timeout con CardNet; en producción usar Apache/Nginx
5. **Idempotency key** — se obtiene una nueva antes de cada transacción para evitar cobros duplicados
