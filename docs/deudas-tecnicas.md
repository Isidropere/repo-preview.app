# CambialóRD — Deudas Técnicas

> Auditoría completa del código. Fecha: 9 abril 2026

---

## Resumen Ejecutivo

| Severidad | Cantidad | Descripción |
|-----------|----------|-------------|
| 🚨 Crítica | 5 | Seguridad, datos sensibles, controlador gigante |
| ⚠️ Alta | 8 | Lógica duplicada, validaciones inconsistentes, sin tests |
| 🟡 Media | 10 | Performance, modelos incompletos, config hardcodeada |
| 🔵 Baja | 6 | Naming, organización, mejoras de UX |

---

## 🚨 CRÍTICAS (resolver antes de producción)

### DT-01: ItemController tiene 1,966 líneas (controlador gigante)

`app/Http/Controllers/ItemController.php` contiene lógica de:
- Creación de productos y talentos con pago inline
- Procesamiento de imágenes y videos
- Gestión de inventario
- Búsqueda y filtrado
- Edición y eliminación
- Listados por categoría

Debe dividirse en:
- `TalentoController` (crear, editar, listar talentos)
- `ProductoController` (crear, editar, listar productos)
- `CatalogoController` (búsqueda, categorías, detalle público)
- `ImagenItemService` (procesamiento de imágenes/videos)

### DT-02: Credenciales CardNet hardcodeadas como defaults

```php
// config/services.php
'merchant_id' => env('CARDNET_MERCHANT_ID', '349041263'),
'terminal_id' => env('CARDNET_TERMINAL_ID', '77777777'),
'token'       => env('CARDNET_TOKEN', '454500350001'),
```

Si `.env` no tiene estas variables, se usan credenciales de QA en producción. Deben ser `env('...', '')` sin defaults.

### DT-03: Sin tests para flujos críticos de pago

No existen tests para:
- `CheckoutService` (procesamiento de pagos)
- `CarritoService` (carrito de compras)
- `NegociacionService` (intercambios)
- `CardnetProvider` / `StripeProvider`
- Autenticación y autorización

Solo hay 3 tests de feature y 1 test unitario placeholder.

### DT-04: Validación de tarjetas sin Luhn check

`StoreTarjetaRequest` acepta cualquier string de 13-19 dígitos como número de tarjeta. No valida:
- Algoritmo de Luhn (checksum)
- Que el año de expiración no haya pasado
- Que mes+año combinados sean futuro

### DT-05: Reglas de validación duplicadas e inconsistentes

`AddTalento()` y `store()` en ItemController tienen reglas de validación inline casi idénticas (~40 líneas cada una) con diferencias sutiles:
- `AddTalento`: `condicion in:1,2,3,4`
- `ItemRequest`: `condicion in:0,1`
- `store()`: `condicion in:1,2,3,4`

`ItemRequest.php` existe pero no se usa en ningún controlador. Los campos no coinciden con los modelos.


---

## ⚠️ ALTAS (resolver en próximas iteraciones)

### DT-06: Lógica de stock duplicada en 3 lugares

La validación de stock se repite en:
1. `CarritoService::agregarItem()` — verifica stock al agregar
2. `CheckoutService::verificarStock()` — verifica stock al pagar
3. `NegociacionService::crear()` — verifica stock al negociar

Debe extraerse a un método compartido en el modelo `Item` o en un `InventarioService`.

### DT-07: Modelos sin casts numéricos

| Modelo | Campo | Tipo BD | Cast faltante |
|--------|-------|---------|---------------|
| Item | valor | decimal(12,2) | `'decimal:2'` |
| Item | descuento | decimal(4,2) | `'decimal:2'` |
| Negociacion | monto_oferta | decimal(12,2) | `'decimal:2'` |
| Negociacion | monto_contra_oferta | decimal(12,2) | `'decimal:2'` |

Sin casts, las comparaciones numéricas pueden fallar por tipos string vs float.

### DT-08: N+1 queries en listados

- `Item::direccionPredeterminada()` usa `hasOne` con `where` — ineficiente en loops
- `PagoCompra::getCompradorAttribute()` accede a `carrito->usuario` sin garantía de eager loading
- `porCategoria()` no carga `inventarios` (ya corregido parcialmente)
- Home page carga productos sin `inventarios` para el carrusel

### DT-09: Relaciones fantasma en Negociacion

```php
public function itemsOferta() { ... }        // tabla pivot NO EXISTE
public function itemsContraOferta() { ... }  // tabla pivot NO EXISTE
```

Estas relaciones están definidas pero las tablas `negociacion_items_oferta` y `negociacion_items_contraoferta` nunca se crearon. Deben eliminarse o crear las migraciones.

### DT-10: Sin soft deletes en modelos críticos

Ningún modelo usa `SoftDeletes`. Si se elimina un item, usuario o negociación, se pierde permanentemente. Modelos que deberían tener soft delete:
- `Item` (productos referenciados en órdenes históricas)
- `User` (usuarios con historial de compras)
- `Negociacion` (historial de intercambios)
- `TarjetaPago` (ya tiene `estatus` como soft delete manual)

### DT-11: .env.example incompleto

Variables faltantes:
```
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
INSTAGRAM_CLIENT_ID=
INSTAGRAM_CLIENT_SECRET=
IMGBB_API_KEY=
```

### DT-12: AgregarItemCarritoRequest permite cantidad hasta 1,000,000

```php
'cantidad' => 'required|integer|min:1|max:1000000',
```

Debería ser `max:999` o `max:9999` como máximo razonable.

### DT-13: Rutas duplicadas

- `/items/search_header` y `/buscar` apuntan al mismo controlador
- Rutas de negociación definidas en dos grupos separados (dentro de `carrito` y en `negociaciones`)
- API routes sin versionado (`/api/` en vez de `/api/v1/`)


---

## 🟡 MEDIAS (planificar para próximo sprint)

### DT-14: Videos almacenados en public/ sin protección

Los videos se guardan en `public/imgs/videos/items/` accesibles directamente por URL. Riesgos:
- Hotlinking desde otros sitios
- Descarga directa sin autenticación
- Sin CDN ni optimización de entrega

Migrar a `storage/app/public/` con symlink o a S3/CDN con presigned URLs.

### DT-15: Sin rotación de logs

`cabialoErrores.log` crece indefinidamente (actualmente 11MB). Configurar:
- Rotación diaria en `config/logging.php`
- Retención máxima (ej: 14 días)
- Alertas cuando el log supera cierto tamaño

### DT-16: Sin auditoría de transacciones de pago

No se guardan:
- `idempotency_key` de CardNet en `pagos_compra`
- Request/response completo de cada transacción
- Logs separados para CardNet y Stripe

Crear tablas `logs_cardnet` y `logs_stripe` para trazabilidad completa.

### DT-17: Encoding mojibake en ItemController

Múltiples strings con caracteres corruptos:
```php
// Punto 2: ValidaciÃ³n de datos  (debería ser: Validación)
// CreaciÃ³n del Ã­tem             (debería ser: Creación del ítem)
```

El archivo fue editado con encoding incorrecto en algún momento. Los mensajes de validación tienen caracteres rotos que el usuario podría ver.

### DT-18: Sin caché en consultas frecuentes

Consultas que se ejecutan en cada request sin caché:
- `ConfigTarifaCategoria29::vigente()` — se llama en cada vista de talento
- `PredefinedMessage::all()` — se carga en cada modal de negociación
- `CategoriaItem::all()` — se carga en formularios de creación

### DT-19: DeliveryService sin caché

`DeliveryService::calcular()` consulta la BD en cada llamada. Las zonas de delivery cambian raramente — cachear por 1 hora mínimo.

### DT-20: Sin queue/jobs para procesos lentos

Todo se ejecuta sincrónicamente:
- Llamadas a CardNet (10-15 segundos)
- Procesamiento de imágenes
- Envío de emails de confirmación
- Reembolsos automáticos

Implementar Laravel Queues para:
- `ProcessPaymentJob`
- `SendOrderConfirmationJob`
- `ProcessRefundJob`
- `ModerateImageJob`

### DT-21: Modelo Negociacion documenta estado "pendiente" que no se usa

El docblock del modelo lista 7 estados pero el servicio solo usa 5:
- Documentados: Inicial, pendiente, contraoferta, aceptado, completado, rechazado, cancelado
- Usados: Inicial, contraoferta, aceptado, rechazado, cancelado, completado

El estado "pendiente" nunca se asigna. Limpiar documentación.

### DT-22: Ruta `/historial` usa closure en vez de controlador

```php
Route::get('/historial', function () { ... })->name('historial');
```

Contiene ~20 líneas de lógica con queries complejas. Debe moverse a un `HistorialController`.

### DT-23: Sin validación de tarjeta expirada al cobrar

`TarjetaPago::datosCardnet()` envía la fecha de expiración a CardNet sin verificar si ya pasó. CardNet la rechazará, pero el error no es claro para el usuario.


---

## 🔵 BAJAS (backlog)

### DT-24: Naming inconsistente en métodos

| Método | Convención esperada |
|--------|-------------------|
| `AddTalento()` | `storeTalento()` (camelCase, verbo REST) |
| `VerDetalle()` | `showDetail()` (ya existe duplicado) |
| `userItemstalento()` | `userTalentos()` |
| `getItemIds()` | `itemIds()` |

### DT-25: Tabla `tipos_item` vs `tipos_items` (duplicada)

Existen dos tablas con el mismo propósito:
- `tipos_item` — legacy, sin AUTO_INCREMENT, sin timestamps
- `tipos_items` — nueva, con AUTO_INCREMENT y timestamps

Una debe eliminarse y las referencias unificarse.

### DT-26: Tabla `miembros` legacy sin uso

La tabla `miembros` fue reemplazada por `users` pero sigue existiendo. Algunas tablas legacy (`facturas_transporte_transaccion`, `ratings`) aún referencian `id_miembro`. Limpiar o migrar referencias.

### DT-27: Sin exportación PDF de facturas

El historial muestra órdenes pero no permite:
- Exportar factura en PDF
- Reenviar email de confirmación
- Reimprimir comprobante

### DT-28: OAuth redirect URIs hardcodeadas

```php
'redirect' => env('GOOGLE_REDIRECT_URI', 'http://localhost:8080/auth/google/callback'),
```

El default apunta a `localhost:8080`. En producción sin `.env` configurado, el OAuth fallaría silenciosamente.

### DT-29: Sin rate limiting por IP en endpoints públicos

Los endpoints de búsqueda (`/items/search`, `/buscar`) tienen throttle de 30/min, pero los listados públicos (`/compras`, `/intercambio`, categorías) no tienen límite. Un bot podría scrapear todo el catálogo.

---

## Plan de Acción Recomendado

### Sprint 1 (Inmediato — antes de producción)
1. ~~DT-02: Quitar defaults de credenciales CardNet~~ ✅
2. ~~DT-04: Agregar validación Luhn + expiración en tarjetas~~ ✅
3. ~~DT-05: Unificar reglas de validación en FormRequests~~ ✅
4. ~~DT-11: Completar .env.example~~ ✅
5. DT-17: Corregir encoding mojibake en ItemController (parcial — requiere reescritura manual del archivo)

### Sprint 2 (Siguiente iteración)
6. DT-01: Dividir ItemController en 3-4 controladores (pendiente — requiere refactor mayor)
7. DT-03: Escribir tests para CheckoutService y CarritoService (pendiente)
8. ~~DT-06: Extraer lógica de stock a métodos en Item model~~ ✅ (`tieneStock()`, `estaDisponible()`, `stock` accessor)
9. ~~DT-07: Agregar casts decimales a modelos~~ ✅ (Item, Negociacion)
10. ~~DT-12: Corregir max de cantidad en carrito~~ ✅ (max:999)

### Sprint 3 (Estabilización)
11. DT-08: Resolver N+1 queries con eager loading (parcial — porCategoria ya corregido)
12. DT-10: Implementar SoftDeletes en modelos críticos (pendiente)
13. DT-14: Migrar videos a storage protegido (pendiente)
14. ~~DT-15: Configurar rotación de logs~~ ✅ (daily, 14 días)
15. DT-16: Crear tablas de auditoría de pagos (pendiente)

### Sprint 4 (Escalabilidad)
16. ~~DT-18: Implementar caché en ConfigTarifaCategoria29~~ ✅ (1 hora)
17. DT-20: Implementar Laravel Queues (pendiente)
18. DT-22: Mover closures de rutas a controladores (pendiente)
19. DT-25/26: Limpiar tablas legacy duplicadas (pendiente)

### Extras resueltos
- ~~DT-09: Eliminar relaciones fantasma de Negociacion~~ ✅
- ~~DT-13: Consolidar ruta /buscar duplicada~~ ✅
- ~~DT-21: Limpiar docblock de Negociacion~~ ✅
- ~~DT-23: Validar tarjeta expirada al cobrar~~ ✅
- ~~DT-28: Quitar OAuth redirect URIs hardcodeadas~~ ✅
- ~~DT-29: Rate limiting en endpoints públicos~~ ✅ (60/min)
