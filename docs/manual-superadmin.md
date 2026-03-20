# Manual de Super Administrador — CambialóRD

## 1. Acceso

1. Inicia sesión con una cuenta que tenga `isSuperAdmin = true` en la base de datos.
2. El Super Admin tiene acceso a todo lo que tiene el Admin normal, más funcionalidades exclusivas.
3. Las rutas exclusivas están protegidas por el middleware `superadmin`.

---

## 2. Funcionalidades Exclusivas del Super Admin

El Super Admin tiene acceso a dos áreas que el Admin normal no puede modificar:

1. Dashboard de Estadísticas
2. Gestión completa de Mensajes Predefinidos

---

## 3. Dashboard de Estadísticas

### 3.1 Acceso
- Ruta: `/admin/estadisticas`
- Solo visible para Super Admins.

### 3.2 Filtros
El dashboard permite filtrar por:
- Período: 7 días, 30 días, 90 días, 365 días o rango personalizado.
- Estatus de compra: pendiente, aprobado, rechazado, enviado, entregado, cancelado.
- Estado de intercambio: Inicial, pendiente, contraoferta, aceptado, completado, rechazado, cancelado.
- Tipo de transacción: Venta, Intercambio o Ambos.

### 3.3 KPIs (Indicadores Clave)
El dashboard muestra en tiempo real:
- Total de compras y desglose por estado (pendientes, aprobadas, entregadas).
- Monto total de ventas aprobadas (RD$).
- Total de intercambios y desglose (activos, completados).
- Total de ventas únicas por artículo.
- Usuarios nuevos registrados.
- Items publicados en el período.

### 3.4 Gráficos Disponibles
- Compras por día (cantidad y monto).
- Compras por estado (distribución).
- Intercambios por día.
- Intercambios por estado.
- Ventas por día.
- Monto de ingresos por día (solo aprobadas).
- Usuarios nuevos por día.
- Items publicados por día.

### 3.5 Tablas de Análisis
- Top 8 categorías más vendidas.
- Top 10 vendedores (por cantidad de ventas y monto).
- Top 10 compradores (por cantidad de compras y monto).
- Top 10 intercambiadores (por intercambios completados).
- Items sin movimiento (más de 30 días sin vistas ni negociaciones).
- Trazabilidad reciente (últimos 50 cambios de estado).

### 3.6 Métricas Avanzadas
- Tasa de conversión de compras: % de intenciones de compra que terminan en pago.
- Tasa de conversión de negociaciones: % de negociaciones que se completan.
- Tiempo promedio de cierre por estado de negociación.
- Ingresos semanales y mensuales.
- Actividad por provincia (usuarios registrados por zona).

### 3.7 Delivery Stats
- Zonas de delivery activas con precios base y costos estimados.
- Porcentajes de ganancia, plataforma, manejo y seguro por zona.
- Días de entrega estimados por zona.
- Configuración actual del delivery.

### 3.8 Alertas Automáticas
El sistema genera alertas cuando detecta:
- 3 o más pagos rechazados en las últimas 24 horas.
- Usuarios con 5 o más negociaciones rechazadas en el período.
- Publicaciones con más de 60 días sin actividad.
- Tasa de conversión de compras menor al 20% (con más de 10 intenciones).
- Si no hay alertas, muestra "Todo en orden".

### 3.9 Endpoint de datos
- Ruta API: `GET /admin/estadisticas/data`
- Acepta parámetros: `periodo`, `fecha_desde`, `fecha_hasta`, `estatus_compra`, `estado_intercambio`, `tipo_trans`.
- Retorna JSON con todos los datos del dashboard.

---

## 4. Gestión de Mensajes Predefinidos

### 4.1 Acceso
- Ruta: `/admin/mensajes-predefinidos`
- El Admin normal solo puede ver la lista.
- El Super Admin puede crear, editar, eliminar y activar/desactivar.

### 4.2 Crear mensaje
- Ruta: `POST /admin/mensajes-predefinidos`
- Campos: título, mensaje, rol (comprador/vendedor), tipo de acción.

### 4.3 Editar mensaje
- Ruta: `PUT /admin/mensajes-predefinidos/{id}`
- Modifica cualquier campo del mensaje.

### 4.4 Eliminar mensaje
- Ruta: `DELETE /admin/mensajes-predefinidos/{id}`
- Elimina permanentemente el mensaje predefinido.

### 4.5 Activar/Desactivar mensaje
- Ruta: `PATCH /admin/mensajes-predefinidos/{id}/toggle`
- Alterna el estado activo del mensaje sin eliminarlo.

---

## 5. Todas las Funcionalidades de Admin

El Super Admin hereda todas las capacidades del Admin normal:
- Gestión de compras (ver, cambiar estado, trazabilidad).
- Gestión de ventas (ver detalle).
- Gestión de intercambios (ver, cambiar estado).
- Gestión de usuarios (ver, editar, activar/desactivar).
- Gestión de categorías.
- Ver mensajes predefinidos.

Consulta el Manual de Administrador para detalles de estas funcionalidades.

---

## 6. Configuración de Delivery

### 6.1 Zonas
Las zonas de delivery se configuran en la tabla `delivery_zonas`:
- Cada zona tiene: nombre, tipo (corta/larga/especial), precio base, días de entrega.
- Se pueden activar/desactivar zonas individuales.

### 6.2 Configuración global
La tabla `delivery_config` contiene los porcentajes aplicados:
- Porcentaje de ganancia
- Porcentaje de plataforma
- Porcentaje de manejo
- Porcentaje de seguro

Estos porcentajes se aplican sobre el precio base de cada zona para calcular el costo final.

### 6.3 API de Delivery
- Endpoint: `GET /api/delivery/calcular`
- Calcula el costo de envío basado en municipio de origen, destino, peso y dimensiones.
- Los servicios (talentos) están excluidos del cálculo.

---

## 7. Seguridad y Buenas Prácticas

- El campo `isSuperAdmin` nunca debe estar en el `$fillable` del modelo User.
- Solo asignar Super Admin directamente en la base de datos.
- Revisar las alertas del dashboard regularmente.
- Monitorear la tasa de conversión — una caída puede indicar problemas técnicos.
- Revisar los pagos rechazados para detectar fraude.
- Mantener actualizados los porcentajes de delivery según costos reales.

---

## 8. Preparación para Producción

Antes de publicar, verificar en el archivo `.env`:

| Variable | Valor producción |
|---|---|
| APP_ENV | production |
| APP_DEBUG | false |
| SESSION_SECURE_COOKIE | true |
| LOG_LEVEL | warning |
| DB_HOST / DB_DATABASE / DB_USERNAME / DB_PASSWORD | Credenciales reales |
| CARDNET_* | Credenciales reales de CardNet |
| GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET | Credenciales reales de Google OAuth |

Ejecutar antes del deploy:
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
