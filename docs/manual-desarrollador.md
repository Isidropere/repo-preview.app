# Manual de Desarrollador — Cambialord

## Stack tecnológico

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Blade + Tailwind CSS + Chart.js (estadísticas)
- **Base de datos**: MySQL
- **Pagos**: Stripe (vía `PagoService`)
- **WebSockets**: Laravel Reverb (notificaciones en tiempo real)
- **Autenticación**: Laravel Auth + Google OAuth

---

## Estructura del proyecto (archivos clave)

```
app/
  Http/Controllers/
    Admin/
      AdminComprasController.php  → Panel admin: compras, ventas, intercambios
      AdminStatsController.php    → Estadísticas en JSON para la vista
    PagoController.php            → Procesamiento de pagos con tarjeta
    NegociacionController.php     → Intercambios entre usuarios
    NotificacionController.php    → Listado y marcado de notificaciones
  Models/
    Negociacion.php               → Modelo de intercambios
    PagoCompra.php                → Modelo de órdenes de pago
    ItemIntencionCompra.php       → Ítems dentro de un carrito
    CompraTrazabilidad.php        → Historial de cambios de estado
    Message.php                   → Mensajes entre usuarios
  Services/
    PagoService.php               → Abstracción del proveedor de pagos (Stripe)

resources/views/
  admin/
    index.blade.php               → Panel principal (tabs: compras/ventas/intercambios)
    stats.blade.php               → Página de estadísticas (carga datos vía fetch)
    compras/
      index.blade.php             → Lista de compras con filtros
      show.blade.php              → Detalle de una compra + cambio de estado
    ventas/
      show.blade.php              → Detalle de una venta
    intercambios/
      show.blade.php              → Detalle de un intercambio + cambio de estado
    partials/
      tabla-compras.blade.php     → Tabla reutilizable de compras
      tabla-ventas.blade.php      → Tabla reutilizable de ventas
      tabla-intercambios.blade.php → Tabla reutilizable de intercambios
  historial/
    historial.blade.php           → Historial del usuario (compras/ventas/intercambios)
  components/
    notificaciones.blade.php      → Campana de notificaciones con panel desplegable
  partials/
    header.blade.php              → Navbar principal (carrito, notificaciones, usuario)

database/migrations/
  2026_03_14_184822_make_messages_nullable.php → Hace nullable id_oferta e id_paquete en messages
```

---

## Base de datos — tablas principales

| Tabla                    | Descripción                                      |
|--------------------------|--------------------------------------------------|
| `users`                  | Usuarios del sistema                             |
| `items`                  | Artículos publicados                             |
| `carritos`               | Carrito de compras por usuario                   |
| `items_intencion_compra` | Ítems dentro de un carrito                       |
| `pagos_compra`           | Órdenes de pago                                  |
| `negociaciones`          | Intercambios entre usuarios                      |
| `messages`               | Mensajes entre usuarios en negociaciones         |
| `compra_trazabilidad`    | Historial de cambios de estado de compras        |
| `inventarios`            | Stock de cada artículo                           |
| `tarjetas_pago`          | Tarjetas guardadas de los usuarios               |
| `proveedores_pago`       | Proveedores de pago (ej: Stripe = id 1)          |

### Campo `estatus` en `pagos_compra`

Es un `tinyint`. Mapa de valores:

```
1 = Pendiente
2 = Aprobado
3 = Rechazado
4 = Enviado
5 = Entregado
6 = Cancelado
```

### Campo `estado` en `negociaciones`

Es un `string`. Valores posibles:
`'Inicial'`, `'pendiente'`, `'contraoferta'`, `'aceptado'`, `'completado'`, `'rechazado'`, `'cancelado'`

### Campo `tipo_trans` en `items`

```
1 = Venta directa
2 = Intercambio
```

---

## Flujo de pago (`PagoController`)

```
POST /pago/procesar
  ↓
Obtener carrito del usuario autenticado
  ↓
Filtrar ítems con es_seleccionado = true
  ↓
Calcular monto total (precio × cantidad - descuento)
  ↓
Validar tarjeta seleccionada (debe pertenecer al usuario)
  ↓
PagoService::cobrarTarjeta($monto, 'usd', $payment_method_id)
  ↓
Si falla → redirect con error
  ↓
Por cada ítem:
  - Verificar stock en inventarios
  - Descontar inventario
  - Crear PagoCompra (estatus=1, id_proveedor_pago=1)
  - Eliminar ítem del carrito
  ↓
Redirect con éxito
```

> **Importante**: Si el cobro se realiza pero luego falla la verificación de stock, el dinero ya fue cobrado. Implementar reembolso automático en ese caso.

---

## Flujo de negociación (`NegociacionController::store`)

```
POST /savenegociaciones
  ↓
Validar: item_id (nullable), receptor_id (nullable), mensaje (required), monto_oferta (nullable)
  ↓
Si item_id → buscar Item → obtener usuario_receptor_id del item
Si no item_id → usar receptor_id directo (respuesta desde notificación)
  ↓
Si hay item_id → crear Negociacion (estado='Inicial')
  ↓
Crear Message (id_oferta y id_paquete son nullable desde migración 2026_03_14)
  ↓
Retornar JSON { status: 'ok' }
```

---

## Estadísticas (`AdminStatsController::data`)

El endpoint `GET /admin/estadisticas/data` devuelve JSON con:

```json
{
  "kpis": { ... },
  "compras_por_dia": [ { "dia": "2026-03-01", "total": 5 }, ... ],
  "compras_por_estado": [ { "estatus": "Pendiente", "total": 10 }, ... ],
  "ventas_por_dia": [ ... ],
  "intercambios_por_dia": [ ... ],
  "intercambios_por_estado": [ ... ],
  "trazabilidad": [ ... ],
  "actualizado_en": "14/03/2026 18:45:00"
}
```

La vista `stats.blade.php` llama a este endpoint con `fetch()` al cargar y renderiza las gráficas con **Chart.js 4.x**.

---

## Paginación personalizada

Todas las tablas del admin usan la vista custom:

```php
$collection->appends(request()->except('page'))->links('vendor.pagination.custom')
```

El archivo de la vista está en:
`resources/views/vendor/pagination/custom.blade.php`

---

## Notificaciones en tiempo real

Se usa **Laravel Reverb** (WebSockets). El canal es `notificaciones.{userId}`.

El evento `NuevaNotificacion` se escucha en `app.blade.php`:

```js
Echo.channel('notificaciones.' + userId)
    .listen('NuevaNotificacion', (e) => {
        // Incrementa el contador de la campana
    });
```

El componente `notificaciones.blade.php` hace polling cada 60 segundos como fallback.

---

## Comandos útiles

```bash
# Correr migraciones
php artisan migrate

# Limpiar caché de configuración
php artisan config:clear
php artisan cache:clear

# Ver rutas del admin
php artisan route:list --path=admin

# Iniciar servidor de desarrollo
php artisan serve

# Iniciar WebSockets (Reverb)
php artisan reverb:start
```

---

## Variables de entorno importantes (`.env`)

```env
DB_CONNECTION=mysql
DB_DATABASE=cambialo_rd

STRIPE_KEY=pk_...
STRIPE_SECRET=sk_...

REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...

MAIL_MAILER=smtp
```

---

## Notas de deuda técnica

- Las tablas pivot `negociacion_items_oferta` y `negociacion_items_contraoferta` están definidas en el modelo `Negociacion` pero **no existen en la BD**. No usar esas relaciones hasta crear las migraciones.
- El campo `estatus` en `pagos_compra` es `tinyint` en BD pero en algunas partes del código se compara como string (`'pendiente'`). Hay inconsistencia — revisar y unificar.
- Si el cobro de Stripe se realiza pero falla la verificación de stock, no hay reembolso automático implementado.
- El log de Laravel (`storage/logs/laravel.log`) estaba vacío en desarrollo — verificar que `LOG_CHANNEL=stack` esté configurado en `.env`.
