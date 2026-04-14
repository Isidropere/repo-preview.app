# CambialóRD — Lógica de Negocio y Flujo del Sistema

> Versión 3.0 — Actualizada tras resolución de deudas técnicas (9 abril 2026)

## 1. Visión General

CambialóRD es un marketplace dominicano donde los usuarios pueden vender, comprar e intercambiar productos y servicios (talentos). Opera con moneda local (DOP / RD$) y procesa pagos a través de CardNet (producción) o Stripe (sandbox).

**Stack:** Laravel 11, Blade + Tailwind CSS, MySQL, CardNet/Stripe.

**Logging:** Monolog con rotación diaria (14 días retención), canal `error_tracking` para errores críticos, tabla `application_errors` para errores de usuario con UUID de referencia.

**Configuración:** Todas las credenciales externas (CardNet, Stripe, OAuth) se leen exclusivamente de `.env` sin valores por defecto hardcodeados. Si faltan, los servicios fallan explícitamente.

---

## 2. Tipos de Usuario

| Rol | Descripción | Middleware |
|-----|-------------|-----------|
| Usuario (Persona) | Publica, compra, vende, intercambia. Precios de delivery: `precio_persona` | `auth` |
| Usuario (Empresa) | Igual que Persona. Precios de delivery: `precio_empresa` | `auth` |
| Admin | Gestiona órdenes, modera imágenes, gestiona usuarios. No modifica OAuth ni config global | `auth`, `admin` |
| SuperAdmin | Acceso completo: estadísticas, configuración, OAuth, mensajes predefinidos | `auth`, `superadmin` |

**Autenticación:** email/contraseña o social (Google, Facebook, Instagram). Proveedores OAuth configurables exclusivamente por SuperAdmin (tabla `oauth_providers`). Redirect URIs sin defaults hardcodeados.

**Tipo de usuario:** Se define al registrarse via `tipos_usuarios`. Determina precios de delivery y permisos.

---

## 3. Catálogo de Artículos

### 3.1 Tipos de transacción

| tipo_trans | Modalidad | Acciones |
|------------|-----------|----------|
| 1 | Solo venta | Agregar al carrito, comprar |
| 2 | Solo intercambio | Negociar intercambio |
| 3 | Venta + Intercambio | Ambas opciones |

### 3.2 Categoría 29: Talentos (Servicios)

Diferencias con productos físicos:
- Requiere pago de registro para publicar (monto × cantidad de cupos)
- Excluida del cálculo de delivery
- No se puede mezclar con productos físicos en el mismo carrito
- Inventario = cupos disponibles del servicio
- Validación centralizada via `StoreTalentoRequest`

### 3.3 Inventario y disponibilidad

Cada artículo tiene un registro en `inventario_items`. El modelo `Item` expone:
- `$item->stock` — accessor que retorna la cantidad disponible
- `$item->tieneStock($cantidad)` — verifica si hay stock suficiente
- `$item->estaDisponible($cantidad)` — verifica estatus activo + stock

Cuando el stock llega a 0:
- Botones "Agregar al carrito" e "Intercambiar" se deshabilitan en todas las vistas
- Se muestra "Agotado"
- El carrito rechaza intentos de agregar (`CarritoService`)
- Las negociaciones activas se cancelan automáticamente (`NegociacionService`)
- El checkout rechaza items sin stock (`CheckoutService`)

### 3.4 Imágenes y multimedia

- Imagen/video principal (obligatorio) + hasta 4 imágenes adicionales
- Formatos: JPEG, PNG, WebP, MP4 (max 20MB)
- Almacenamiento: `public/imgs/articulos/items/` y `public/imgs/videos/items/`
- Moderación: `pendiente → aprobado / rechazado` (solo aprobadas se muestran)
- Resolución de URLs: `ImageHelper::urlMedia()` busca en `public/` y luego en `public/storage/`

### 3.5 Condiciones

| Valor | Significado |
|-------|-------------|
| 1 | Nuevo |
| 2 | Usado — Como nuevo |
| 3 | Usado — Buen estado |
| 4 | Usado — Aceptable |

### 3.6 Casts del modelo Item

Campos numéricos con cast `decimal:2`: `valor`, `descuento`, `peso_lbs`, `alto_cm`, `ancho_cm`, `profundo_cm`. Esto garantiza comparaciones numéricas correctas.


---

## 4. Flujo de Compra Directa

```
Navega catálogo → Detalle → Agrega al carrito → Checkout
→ Tarjeta + CVV → CardNet cobra → Registro atómico en BD → Historial
```

### 4.1 Carrito (`CarritoService`)

Relación 1:1 usuario-carrito. Validaciones al agregar:

| Validación | Descripción |
|------------|-------------|
| Stock | `$item->tieneStock($cantidad)` |
| Auto-compra | `$item->id_user !== $userId` |
| Carrito mixto | No mezclar cat 29 con productos físicos |
| Cantidad máxima | max 999 unidades (`AgregarItemCarritoRequest`) |

Items seleccionables/deseleccionables. Totales sobre items seleccionados:
- `total_articulos = Σ(valor × cantidad)`
- `total_descuento = Σ(descuento × cantidad)`
- `total_estimado = total_articulos − total_descuento`

**Precios:** Se toman del item al momento del checkout (precio actual), no al agregar.

### 4.2 Checkout (`CheckoutService`)

Flujo atómico con reembolso automático:

1. Cargar carrito con items seleccionados
2. Validar anti-autocompra
3. Validar dirección de envío predeterminada
4. Verificar stock + estatus activo de cada item
5. Calcular monto total con precios actuales
6. Validar tarjeta (incluyendo expiración)
7. Cobrar vía CardNet/Stripe (timeout 30s)
8. Registrar en BD (transacción con `lockForUpdate`):
   - `PagoCompra` (UUID, "aprobado", fecha automática)
   - `CompraTrazabilidad`
   - `PagoItem` por artículo (snapshot inmutable)
   - Descontar inventario
   - Eliminar items del carrito
9. Si falla paso 8 post-cobro → reembolso automático

### 4.3 Estados de orden

```
aprobado → enviado → entregado
         → cancelado
```

No existe "pendiente". La orden se crea solo cuando el pago es exitoso.

### 4.4 Snapshot (`pago_items`)

Copia inmutable al momento del pago: nombre, precio, cantidad, descuento, subtotal, imagen_url. El historial es correcto incluso si el item se modifica o elimina.

### 4.5 Delivery

- Municipio del comprador → `delivery_zonas` → precio persona/empresa
- Márgenes en `delivery_config` (plataforma, seguro, manejo)
- Cat 29 excluida. Carrito mixto bloqueado.
- Rate limiting: 60 req/min en endpoints públicos (`/compras`, `/intercambio`)

---

## 5. Flujo de Talentos (Categoría 29)

### 5.1 Publicación con pago

```
Formulario → Modal de pago → Archivos a memoria → CardNet cobra
→ Item + Inventario + Imágenes + PagoRegistroTalento
```

Validación centralizada en `StoreTalentoRequest`. Archivos leídos a memoria antes del cobro (PHP elimina tmp files durante requests largos). Limitación: videos >50MB.

**Monto:** `config_tarifa_categoria29.monto_registro × cantidad_cupos` (cacheado 1 hora).

### 5.2 Cupos

`cantidad` = cupos disponibles. Cada compra/intercambio consume 1. Al llegar a 0 → "Agotado".

### 5.3 Descuento por volumen

Solo tipo_trans=1. Si `descuento_venta_masiva > 0` y cantidad >= `cantidad_minima_descuento` → porcentaje sobre valor unitario.

---

## 6. Negociaciones (Intercambios)

### 6.1 Actores

- **Emisor:** quiere un artículo ajeno, propone intercambio
- **Receptor:** dueño del artículo, decide

### 6.2 Estados

```
Inicial ──→ aceptado ──→ completado
       ├──→ contraoferta ──→ aceptado / rechazado
       ├──→ rechazado
       └──→ cancelado (emisor o por falta de stock)
```

### 6.3 Reglas

| Regla | Implementación |
|-------|---------------|
| Stock > 0 | Validado al crear |
| Item activo | `estatus == 1` validado al crear y al aceptar |
| Sin duplicados | Un emisor, una negociación activa por item |
| Propiedad paquete | `paquetes.id_user = emisor_id` |
| No auto-negociación | `item.id_user != emisor_id` |
| Estado válido | Cada acción valida contra constantes de estados permitidos |
| Atomicidad | `lockForUpdate()` + transacción DB en aceptar |
| Inventario | `-1` al aceptar, dentro de la transacción |
| Auto-cancelación | Stock = 0 → cancela todas las negociaciones activas restantes |
| Item eliminado | Si el item se pausa/elimina entre crear y aceptar → negociación cancelada |

Casts: `monto_oferta` y `monto_contra_oferta` como `decimal:2`, `fecha_creacion` como `datetime`.

### 6.4 Mensajería

Tabla `messages`, vinculados al item via `id_oferta`. Historial muestra mensajes reales.


---

## 7. Tarjetas de Pago

### 7.1 Almacenamiento

| Campo | CardNet | Stripe |
|-------|---------|--------|
| Identificador | UUID (`id_tarjeta`) | UUID |
| Número | Encriptado AES-256-CBC | No aplica |
| Token | No aplica | `payment_method_id` (pm_xxx) |
| Últimos 4 | `last4` | `last4` |
| Expiración | `mes_expiracion` + `año_expiracion` | Desde Stripe |

### 7.2 Seguridad

- Número encriptado con `Crypt::encryptString()` (clave = APP_KEY)
- Encriptación automática en `creating` y `updating` del modelo
- Desencriptación solo al cobrar (`getNumeroDesencriptado()`)
- Fallback transparente para datos legacy sin encriptar
- Nunca expuesto en JSON (`$hidden`)
- CVV nunca almacenado
- Validación Luhn al registrar (`LuhnCheck` rule)
- Validación de expiración al registrar (`StoreTarjetaRequest::withValidator`)
- Validación de expiración al cobrar (`datosCardnet()` lanza RuntimeException)

### 7.3 Flujo CardNet

1. Obtener idempotency-key (timeout 30s)
2. Procesar venta (timeout 30s)
3. Éxito: `response-code = "00"` + `internal-response-code = "0000"`

---

## 8. Delivery

| Concepto | Detalle |
|----------|---------|
| Zonas | `delivery_zonas`: nombre, tipo (corta/larga/especial), pueblos (JSON), precios, días |
| Precios | Diferenciados persona/empresa según `tipos_usuarios` |
| Márgenes | `delivery_config`: plataforma, seguro, manejo |
| Exclusiones | Cat 29 sin delivery. Carrito mixto bloqueado |

---

## 9. Seguridad

| Medida | Estado |
|--------|--------|
| Autenticación Laravel Auth + Sanctum | ✅ |
| CSRF en formularios y AJAX | ✅ |
| Throttling: pago 5/min, búsqueda 30/min, catálogo 60/min | ✅ |
| Middleware `auth`, `admin`, `superadmin` | ✅ |
| Anti-autocompra (carrito + checkout) | ✅ |
| Anti-autonegociación | ✅ |
| Carrito no mixto (cat 29 vs físicos) | ✅ |
| Tarjetas encriptadas AES-256-CBC | ✅ |
| Validación Luhn + expiración en tarjetas | ✅ |
| Mass assignment: todos los modelos con `$fillable` | ✅ |
| Items inactivos bloqueados en checkout y negociaciones | ✅ |
| Cancelación automática de negociaciones al agotar stock | ✅ |
| Handler de errores con UUID + tabla `application_errors` | ✅ |
| Reembolso automático post-fallo de BD | ✅ |
| Logs con rotación diaria (14 días) | ✅ |
| Credenciales sin defaults hardcodeados | ✅ |
| OAuth solo modificable por SuperAdmin | ✅ |
| FormRequests centralizados (StoreTalento, StoreProducto, StoreTarjeta) | ✅ |

### Pendientes

| Prioridad | Tarea |
|-----------|-------|
| Media | Guardar idempotency-key en `pagos_compra` |
| Media | IDOR en endpoints de imágenes, paquetes, direcciones |
| Media | Auditoría de cambios OAuth |
| Media | Moderación de mensajes |
| Baja | Videos a S3/CDN |
| Baja | Subniveles de admin |

---

## 10. Devoluciones y Cancelaciones

### Compras
- Reembolso automático si falla BD post-cobro
- Cancelación por admin → reembolso manual fuera del sistema
- Delivery no se devuelve (pendiente definir política)

### Intercambios
- Cancelación por emisor: estados Inicial y contraoferta, sin penalidad
- Cancelación automática por stock = 0
- Sin penalidad por cancelar

---

## 11. Modelo de Datos

```
users
├── items (id_user)
│   ├── imagenes_item (id_item)
│   ├── inventario_items (id_item)
│   ├── item_views (id_item)
│   └── item_color (item_id)
├── carritos (id_user) ─── 1:1
│   └── items_intencion_compra (id_carrito)
├── tarjetas_pagos (id_user) ─── encriptadas
├── direcciones (id_user)
├── paquetes (id_user)
└── negociaciones (emisor / receptor)

pagos_compra ─── orden autosuficiente
├── pago_items ─── snapshot inmutable
└── compra_trazabilidad ─── historial

pagos_registro_talento ─── pago por publicar
pago_envio_intercambio ─── pago envío intercambios
config_tarifa_categoria29 ─── config talentos (cacheada 1h)
delivery_zonas + delivery_config ─── config envíos
application_errors ─── errores con UUID
```

---

## 12. Historial del Usuario

| Tab | Contenido |
|-----|-----------|
| Compras | Órdenes con snapshot de artículos, montos, trazabilidad, rastreo |
| Ventas | Items propios comprados por otros |
| Intercambios | Negociaciones como emisor o receptor |

---

## 13. Validaciones Centralizadas

| FormRequest | Uso |
|-------------|-----|
| `StoreTalentoRequest` | Crear talento (cat 29) |
| `StoreProductoRequest` | Crear producto (otras categorías) |
| `StoreTarjetaRequest` | Registrar tarjeta (Luhn + expiración) |
| `AgregarItemCarritoRequest` | Agregar al carrito (max 999) |
| `ProcesarPagoRequest` | Checkout (tarjeta + CVV) |
| `StoreNegociacionRequest` | Crear negociación |
| `ContraofertaRequest` | Enviar contraoferta |

---

## 14. Trade-offs

| Decisión | Justificación |
|----------|--------------|
| Precio al checkout | Evita "precio congelado" y expiración de carrito |
| Sin expiración de carrito | Stock se valida al checkout |
| Carrito no mixto | Evita ambigüedad en delivery |
| Inventario = cupos para talentos | Reutiliza infraestructura existente |
| Archivos en memoria durante cobro | PHP elimina tmp files en requests largos |
| Encriptación AES-256 local | Protege sin vault externo |
| Multi-vendedor en carrito | Futuro: dividir por vendedor |

---

## 15. Roadmap

### Corto plazo
- Notificación de cambio de precio en checkout
- Idempotency-key en `pagos_compra`
- Ventana de devolución (5 días post-entrega)
- Exportación PDF de facturas
- Dividir ItemController (1,966 líneas) en controladores especializados

### Mediano plazo
- Videos a S3/CDN con presigned URLs
- Subniveles de admin (moderador, soporte)
- Tablas de auditoría de pagos
- Simulador de delivery en vista de producto
- Logging con notificaciones (Telegram/Slack)
- Tests unitarios para servicios críticos

### Largo plazo
- Tokenización de tarjetas via vault CardNet
- Checkout dividido por vendedor
- Workers/Jobs para procesos lentos
- Lock de carrito por dispositivo
- Sistema de disputas
