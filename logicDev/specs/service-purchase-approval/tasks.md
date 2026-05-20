# Implementation Plan: Service Purchase Approval

## Overview

Implementar el flujo de aprobación previa al pago para servicios/talentos (categoría 29). El comprador solicita, el proveedor aprueba/rechaza desde `/mis-ventas-talentos`, y solo tras aprobación se permite el pago. Se sigue el patrón Controller → Service → Model existente.

## Tasks

- [x] 1. Crear migración y modelo SolicitudServicio
  - [x] 1.1 Crear migración `create_solicitudes_servicio_table`
    - Crear archivo `database/migrations/2025_07_XX_000001_create_solicitudes_servicio_table.php`
    - Tabla `solicitudes_servicio` con columnas: `id_solicitud` (PK auto), `id_comprador`, `id_proveedor`, `id_item`, `id_carrito`, `cantidad`, `monto_total`, `estado` (default `pendiente_aprobacion`), `fecha_creacion`, `fecha_actualizacion`
    - Índices: `idx_proveedor_estado`, `idx_comprador_estado`, `idx_item`
    - Foreign keys a `users`, `items`, `carritos`
    - _Requirements: 1.1, 1.2_

  - [x] 1.2 Agregar SQL de la tabla `solicitudes_servicio` en `public/check.php`
    - Seguir el patrón existente: verificar si la tabla existe con `Schema::hasTable`, crearla con `DB::statement` si no existe
    - Incluir todos los índices y foreign keys del diseño
    - _Requirements: 1.1, 1.2_

  - [x] 1.3 Crear modelo `SolicitudServicio`
    - Crear `app/Models/SolicitudServicio.php`
    - `$table = 'solicitudes_servicio'`, `$primaryKey = 'id_solicitud'`, `$timestamps = false`
    - `$fillable` con todos los campos, `$casts` para `monto_total`, `fecha_creacion`, `fecha_actualizacion`
    - Relaciones: `comprador()`, `proveedor()`, `item()`, `carrito()` como `belongsTo`
    - _Requirements: 1.2_

- [x] 2. Crear SolicitudServicioService
  - [x] 2.1 Implementar `crearDesdeCarrito(int $compradorId, Carrito $carrito): array`
    - Para cada item seleccionado del carrito servicio, crear una `SolicitudServicio` con estado `pendiente_aprobacion`
    - Validar que el comprador no sea el dueño del item (Req 8.4)
    - Enviar notificación al proveedor usando `NuevaNotificacion` event y `Message` model (Req 7.1)
    - Retornar array con success/message
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 7.1, 8.4_

  - [x] 2.2 Implementar `aprobar(int $proveedorId, int $solicitudId): array`
    - Verificar que el usuario es el proveedor del item (Req 8.1)
    - Verificar que el estado es `pendiente_aprobacion` (Req 4.4)
    - Cambiar estado a `aprobada`, actualizar `fecha_actualizacion`
    - Enviar notificación al comprador (Req 7.2)
    - _Requirements: 4.1, 4.2, 4.4, 7.2, 8.1_

  - [x] 2.3 Implementar `rechazar(int $proveedorId, int $solicitudId): array`
    - Verificar que el usuario es el proveedor del item (Req 8.1)
    - Verificar que el estado es `pendiente_aprobacion` (Req 5.5)
    - Cambiar estado a `rechazada`, actualizar `fecha_actualizacion`
    - Eliminar el item del carrito servicio del comprador (Req 5.3)
    - Enviar notificación al comprador (Req 7.3)
    - _Requirements: 5.1, 5.2, 5.3, 5.5, 7.3, 8.1_

  - [x] 2.4 Implementar `marcarPagada(int $solicitudId): void` y `tieneAprobacion(int $compradorId, int $itemId): bool`
    - `marcarPagada`: cambiar estado a `pagada`, enviar notificación al proveedor (Req 7.4)
    - `tieneAprobacion`: verificar si existe solicitud `aprobada` para el comprador+item
    - _Requirements: 6.1, 6.2, 6.3, 7.4_

  - [ ]* 2.5 Write property test: Service checkout creates solicitudes instead of charging
    - **Property 1: Service checkout creates solicitudes instead of charging**
    - Generar carritos servicio aleatorios, verificar creación de solicitudes con estado `pendiente_aprobacion` y campos correctos, sin procesamiento de pago
    - **Validates: Requirements 1.1, 1.2, 7.1**

  - [ ]* 2.6 Write property test: Product checkout remains unchanged
    - **Property 2: Product checkout remains unchanged**
    - Generar carritos producto aleatorios, verificar que no se crean `SolicitudServicio` y el flujo de pago procede normal
    - **Validates: Requirements 1.5**

  - [ ]* 2.7 Write property test: Non-blocking concurrent solicitudes
    - **Property 3: Non-blocking concurrent solicitudes**
    - Generar múltiples compradores para un mismo item, verificar que todos pueden crear solicitudes sin error
    - **Validates: Requirements 1.4**

  - [ ]* 2.8 Write property test: State transition guards
    - **Property 7: State transition guards**
    - Generar solicitudes en estados aleatorios (no `pendiente_aprobacion`), verificar que aprobar/rechazar falla
    - **Validates: Requirements 4.4, 5.5**

  - [ ]* 2.9 Write property test: Payment gate based on approval status
    - **Property 9: Payment gate based on approval status**
    - Generar solicitudes en estados aleatorios, verificar que pago solo funciona con estado `aprobada`
    - **Validates: Requirements 6.1, 6.4**

  - [ ]* 2.10 Write property test: Authorization enforcement
    - **Property 11: Authorization enforcement**
    - Generar usuarios aleatorios y solicitudes, verificar que solo el actor correcto puede ejecutar cada acción
    - **Validates: Requirements 8.1, 8.2, 8.3**

  - [ ]* 2.11 Write property test: Self-purchase prevention
    - **Property 12: Self-purchase prevention**
    - Generar usuarios con items propios, verificar que auto-solicitud es rechazada
    - **Validates: Requirements 8.4**

- [x] 3. Modificar CheckoutService para interceptar servicios
  - [x] 3.1 Modificar `CheckoutService::procesar()` para desviar carrito servicio
    - Después de cargar el carrito, si `$carrito->tipo === 'servicio'`: delegar a `SolicitudServicioService::crearDesdeCarrito()` y retornar sin cobrar
    - Excepción: si TODOS los items tienen solicitud `aprobada`, proceder al cobro normal y llamar `marcarPagada()` post-pago para cada solicitud
    - Inyectar `SolicitudServicioService` en el constructor
    - Mantener flujo de productos sin cambios
    - _Requirements: 1.1, 1.5, 6.1, 6.2, 6.4_

- [x] 4. Checkpoint - Verificar lógica de negocio
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Crear SolicitudServicioController
  - [x] 5.1 Implementar método `index()` — Vista `/mis-ventas-talentos`
    - Consultar todas las `SolicitudServicio` donde `id_proveedor` = usuario autenticado, ordenadas por `fecha_creacion` desc
    - Eager load: `comprador`, `item`, `comprador.direcciones.municipio` (dirección predeterminada)
    - Pasar datos a vista `solicitudes.mis-ventas-talentos`
    - _Requirements: 3.1, 3.2, 3.3, 3.4_

  - [x] 5.2 Implementar método `aprobar(int $id)` — POST `/solicitudes-servicio/{id}/aprobar`
    - Delegar a `SolicitudServicioService::aprobar()`
    - Redirect a `solicitudes.index` con mensaje de éxito o error
    - _Requirements: 4.1, 4.2, 4.3, 8.1, 8.3_

  - [x] 5.3 Implementar método `rechazar(int $id)` — POST `/solicitudes-servicio/{id}/rechazar`
    - Delegar a `SolicitudServicioService::rechazar()`
    - Redirect a `solicitudes.index` con mensaje de confirmación o error
    - _Requirements: 5.1, 5.2, 5.4, 8.1, 8.3_

- [x] 6. Modificar CarritoController y checkout view
  - [x] 6.1 Modificar `CarritoController::checkout()` para cargar info del proveedor en servicios
    - Si el carrito es tipo `servicio`, para cada item cargar: nombre del proveedor (`item.user.name`) y municipio de dirección predeterminada del proveedor
    - Si el proveedor no tiene dirección predeterminada, usar "Ubicación no disponible"
    - Pasar datos adicionales a la vista `carrito.checkout`
    - _Requirements: 2.1, 2.2, 2.3_

  - [x] 6.2 Modificar `resources/views/carrito/checkout.blade.php` para mostrar info del proveedor
    - Cuando el carrito es tipo `servicio`, mostrar para cada item: nombre del proveedor y su ubicación (municipio)
    - Cambiar texto del botón de pago a "Enviar solicitud" para servicios
    - _Requirements: 2.1, 2.2, 2.3_

- [x] 7. Crear vista mis-ventas-talentos
  - [x] 7.1 Crear `resources/views/solicitudes/mis-ventas-talentos.blade.php`
    - Seguir el mismo layout y estructura de tabs que `mis-intercambios.blade.php`
    - Tab "Pendientes" con solicitudes en estado `pendiente_aprobacion` (con badge de conteo)
    - Tab "Procesadas" con solicitudes en estados `aprobada`, `rechazada`, `pagada`
    - Cada tarjeta de solicitud muestra: nombre del servicio, nombre del comprador, ubicación del comprador (municipio o "Ubicación no disponible"), monto total, fecha, estado con badge de color
    - Botones "Aprobar" y "Rechazar" solo para solicitudes pendientes (formularios POST con CSRF)
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 4.3, 5.4_

- [-] 8. Agregar rutas
  - [x] 8.1 Agregar rutas en `routes/web.php`
    - `GET /mis-ventas-talentos` → `SolicitudServicioController@index` (name: `solicitudes.index`)
    - `POST /solicitudes-servicio/{id}/aprobar` → `SolicitudServicioController@aprobar` (name: `solicitudes.aprobar`)
    - `POST /solicitudes-servicio/{id}/rechazar` → `SolicitudServicioController@rechazar` (name: `solicitudes.rechazar`)
    - Dentro del grupo `middleware(['auth'])`
    - _Requirements: 3.1, 4.1, 5.1_

- [x] 9. Actualizar notificaciones (campanita) para detectar solicitudes de servicio
  - [x] 9.1 Modificar `NotificationController::listar()` para incluir notificaciones de solicitudes de servicio
    - Las notificaciones ya se crean como `Message` en el service (paso 2), verificar que el `listar()` las retorna correctamente
    - Si es necesario, agregar un campo `tipo` o `url` al Message para distinguir notificaciones de solicitud y enlazar a `/mis-ventas-talentos`
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

  - [x] 9.2 Agregar enlace a "Mis Ventas de Talentos" en el menú de navegación
    - Agregar link en `resources/views/partials/menu.blade.php` similar al enlace de "Mis Intercambios"
    - _Requirements: 3.1_

- [ ] 10. Final checkpoint - Verificar integración completa
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- El archivo `public/check.php` sirve como fallback SQL para producción en MochaHost (sin SSH)
- Las migraciones se ejecutan vía `/deploy-migrate` route en producción
- Las notificaciones usan el evento `NuevaNotificacion` y el modelo `Message` existentes
