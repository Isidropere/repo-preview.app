# Convenciones Backend — CambialoRD

## Arquitectura
- **Controllers**: Solo reciben requests, validan con Form Requests, delegan a Services, retornan respuestas.
- **Services**: Toda la lógica de negocio. Retornan `['success' => bool, 'message' => string, 'data' => mixed]`.
- **Models**: Acceso a datos, relaciones, scopes, casts. Sin lógica de negocio.
- **Form Requests**: Validación y autorización. Un request por acción (Store, Update).

## Naming
- Servicios: `{Entidad}Service` (ej: `CarritoService`, `CheckoutService`)
- Form Requests: `{Accion}{Entidad}Request` (ej: `ProcesarPagoRequest`, `StoreTarjetaRequest`)
- Métodos de dominio en español (ej: `agregarItem`, `calcularTotal`, `marcarPredeterminada`)
- Métodos CRUD en inglés estándar Laravel (ej: `index`, `store`, `update`, `destroy`)

## Servicios
- Inyección por constructor con `private` promotion
- DB::transaction() para operaciones multi-tabla
- Log solo errores y eventos críticos (no logging excesivo)
- Usar Eloquent en vez de DB::table() siempre que sea posible

## Controllers
- Constructor injection del Service
- Máximo ~5-10 líneas por método
- No try/catch genéricos (dejar que Laravel maneje excepciones)
- Verificar propiedad del recurso (prevenir IDOR)

## Seguridad
- Siempre verificar `auth()->id()` vs `id_user` del recurso
- Form Requests con `authorize()` → `auth()->check()`
- Bloquear escalación de privilegios (ej: tipo_usuario = admin)
- Sanitizar inputs en `prepareForValidation()` del Form Request

## Respuesta JSON estándar
```php
['success' => true, 'data' => $data, 'message' => 'Operación exitosa']
['success' => false, 'message' => 'Descripción del error']
```

## Servicios existentes
| Servicio | Responsabilidad |
|---|---|
| CheckoutService | Flujo completo de pago |
| CarritoService | Gestión del carrito |
| NegociacionService | Intercambios entre usuarios |
| TarjetaService | CRUD tarjetas de pago |
| PaqueteService | Paquetes de items para intercambio |
| AdminComprasService | Panel admin: compras, ventas, intercambios |
| DeliveryService | Zonas, cálculo de envío, config |
| DireccionService | CRUD direcciones de usuario |
| PagoService | Integración con proveedores de pago |
| ImageToWebP | Conversión de imágenes |
