# Plan de Implementación: intercambio-envio-pago

## Descripción general

Implementar el flujo de pago de envío para intercambios. Cuando una negociación pasa a "aceptado", se crean dos registros `PagoEnvioIntercambio` (uno por participante). Cada usuario paga su envío con tarjeta o descuenta 1 pull (categoría 29). Cuando ambos pagan, la negociación pasa automáticamente a "completado".

## Tareas

- [x] 1. Migración — crear tabla `pago_envio_intercambio`
  - Crear archivo de migración en `database/migrations/`
  - Columnas: `id` (bigint PK auto), `id_negociacion` (bigint FK → negociaciones.id_negociacion), `id_user` (bigint FK → users.id), `monto` (decimal 10,2 default 0), `tipo_pago` (enum: tarjeta|pull), `estado` (enum: pendiente|pagado|pagado_pull, default pendiente), `id_tarjeta` (varchar 255 nullable FK → tarjetas_pagos.id_tarjeta), `transaction_id` (varchar 255 nullable), `approval_code` (varchar 255 nullable), `id_pago_registro_talento` (bigint nullable FK → pagos_registro_talento.id), `timestamps`
  - Ejecutar `php artisan migrate`
  - _Requirements: 1.1, 3.6, 4.4_

- [x] 2. Modelo `PagoEnvioIntercambio` y extensión de `Negociacion`
  - [x] 2.1 Crear `app/Models/PagoEnvioIntercambio.php`
    - Tabla: `pago_envio_intercambio`, fillable con todos los campos del diseño
    - Relaciones: `negociacion()` belongsTo, `usuario()` belongsTo, `tarjeta()` belongsTo TarjetaPago, `pagoRegistroTalento()` belongsTo PagoRegistroTalento
    - _Requirements: 3.6, 4.4_
  - [x] 2.2 Agregar relación `pagoEnvios()` hasMany en `app/Models/Negociacion.php`
    - `hasMany(PagoEnvioIntercambio::class, 'id_negociacion', 'id_negociacion')`
    - _Requirements: 7.1_

- [-] 3. Servicio `IntercambioEnvioService`
  - [ ] 3.1 Crear `app/Services/IntercambioEnvioService.php` con método `crearPagosAlAceptar(Negociacion $neg): void`
    - Crear 2 registros PagoEnvioIntercambio en estado "pendiente" (uno para emisor, uno para receptor)
    - Enviar notificación a ambos usuarios vía `NuevaNotificacion` event
    - Incluir advertencia si algún usuario no tiene dirección predeterminada
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_
  - [ ]* 3.2 Escribir property test — Property 1: Creación de registros al aceptar
    - **Property 1: Para cualquier negociación que pase a "aceptado", se crean exactamente 2 PagoEnvioIntercambio en estado "pendiente", uno por cada participante**
    - **Validates: Requirements 1.1**
  - [ ] 3.3 Implementar método `calcularCosto(int $userId, int $negociacionId): array`
    - Verificar que el usuario es participante de la negociación (403 si no)
    - Si el artículo del usuario tiene `id_categoria_item = 29`: retornar `tipo_pago = 'pull'` con `pull_disponible`
    - Si no: obtener dirección predeterminada del usuario (422 si no existe), llamar a `DeliveryService::calcular()` con municipio y valor del artículo, retornar desglose completo
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_
  - [ ]* 3.4 Escribir property test — Property 2: Cálculo de costo retorna desglose completo
    - **Property 2: Para cualquier usuario con dirección y artículo físico (cat ≠ 29), calcularCosto retorna todos los campos del desglose**
    - **Validates: Requirements 2.1, 2.2**
  - [ ]* 3.5 Escribir property test — Property 3: Categoría 29 omite delivery y retorna pull
    - **Property 3: Para cualquier usuario con artículo de cat 29, calcularCosto retorna tipo_pago = 'pull' sin campos de desglose**
    - **Validates: Requirements 2.3, 4.1**
  - [ ] 3.6 Implementar método `pagarConTarjeta(int $userId, int $negociacionId, string $idTarjeta, ?string $cvv, string $clientIp): array`
    - Verificar que el PagoEnvioIntercambio existe y está en "pendiente" (422 si ya pagado)
    - Verificar que la tarjeta pertenece al usuario (403 si no)
    - Llamar a `PagoService::cobrarTarjeta()` con monto calculado
    - Si aprobado: actualizar registro a estado "pagado" con `transaction_id` y `approval_code` dentro de `DB::transaction`
    - Si falla el registro en BD post-cobro: intentar reembolso automático vía `PagoService::reembolsar()` y loguear con `Log::critical`
    - Si rechazado: mantener estado "pendiente", retornar mensaje del proveedor
    - Llamar a `verificarYCompletar()` al final si el cobro fue exitoso
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_
  - [ ]* 3.7 Escribir property test — Property 4: Pago con tarjeta actualiza estado y guarda credenciales
    - **Property 4: Para cualquier pago aprobado por PagoService, el PagoEnvioIntercambio pasa a "pagado" con transaction_id y approval_code no nulos**
    - **Validates: Requirements 3.2, 3.6**
  - [ ]* 3.8 Escribir property test — Property 5: Pago rechazado mantiene estado pendiente
    - **Property 5: Para cualquier cobro rechazado por PagoService, el PagoEnvioIntercambio permanece en "pendiente" con transaction_id nulo**
    - **Validates: Requirements 3.3**
  - [ ] 3.9 Implementar método `pagarConPull(int $userId, int $negociacionId): array`
    - Verificar que el PagoEnvioIntercambio existe y está en "pendiente" (422 si ya pagado)
    - Verificar que el artículo del usuario es categoría 29
    - Buscar 1 fila de `pagos_registro_talento` con `id_user = $userId` y `estatus = 'aprobado'`
    - Si no hay pull disponible: retornar error 422
    - Dentro de `DB::transaction`: cambiar esa fila a `estatus = 'usado'`, actualizar PagoEnvioIntercambio a "pagado_pull" con `id_pago_registro_talento`
    - Enviar notificación al usuario confirmando descuento de pull
    - Llamar a `verificarYCompletar()` al final
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_
  - [ ]* 3.10 Escribir property test — Property 6: Descuento de pull actualiza estado y referencia
    - **Property 6: Para cualquier usuario con pull disponible, pagarConPull cambia exactamente 1 fila de pagos_registro_talento a 'usado' y actualiza PagoEnvioIntercambio a 'pagado_pull'**
    - **Validates: Requirements 4.2, 4.4**
  - [ ] 3.11 Implementar método `verificarYCompletar(int $negociacionId): void`
    - Dentro de `DB::transaction` con `lockForUpdate` en los registros PagoEnvioIntercambio
    - Verificar si ambos registros están en estado "pagado" o "pagado_pull"
    - Si ambos confirmados: actualizar Negociacion a "completado" y notificar a ambos usuarios
    - Si solo uno: notificar al participante que falta que complete su pago
    - _Requirements: 5.1, 5.2, 5.3, 5.4_
  - [ ]* 3.12 Escribir property test — Property 7: Transición a completado solo cuando ambos pagos confirmados
    - **Property 7: El estado "completado" solo se alcanza cuando los 2 PagoEnvioIntercambio están en "pagado" o "pagado_pull"; con solo uno confirmado el estado permanece "aceptado"**
    - **Validates: Requirements 5.1, 5.2, 5.3**
  - [ ] 3.13 Implementar método `obtenerEstado(int $userId, int $negociacionId): array`
    - Verificar que el usuario es participante (403 si no)
    - Retornar `mi_pago` y `otro_pago` con campos: estado, monto, tipo_pago, fecha_pago
    - _Requirements: 6.1, 6.2, 6.3_
  - [ ]* 3.14 Escribir property test — Property 8: Consulta de estado retorna ambos pagos
    - **Property 8: Para cualquier usuario participante, obtenerEstado retorna exactamente mi_pago y otro_pago con los campos estado, monto, tipo_pago y fecha_pago**
    - **Validates: Requirements 6.1, 6.2**

- [ ] 4. Checkpoint — Verificar servicio base
  - Asegurarse de que todos los tests pasen, consultar al usuario si hay dudas.

- [ ] 5. Integrar `crearPagosAlAceptar` en `NegociacionService::aceptar()`
  - Modificar `app/Services/NegociacionService.php` método `aceptar()`
  - Después de `$neg->update(['estado' => 'aceptado'])`, llamar a `app(IntercambioEnvioService::class)->crearPagosAlAceptar($neg)`
  - _Requirements: 1.1, 1.2, 1.3_

- [ ] 6. Controlador web `IntercambioEnvioController` y rutas
  - [ ] 6.1 Crear `app/Http/Controllers/IntercambioEnvioController.php`
    - Constructor inyecta `IntercambioEnvioService`
    - `calcularEnvio($negociacionId)`: llama a `calcularCosto(auth()->id(), $negociacionId)`, retorna JSON
    - `pagar(Request $request, $negociacionId)`: valida `id_tarjeta` (nullable), `cvv` (nullable); si el artículo es cat 29 llama `pagarConPull()`, si no llama `pagarConTarjeta()`; retorna JSON
    - `estado($negociacionId)`: llama a `obtenerEstado(auth()->id(), $negociacionId)`, retorna JSON
    - _Requirements: 2.1, 3.1, 6.1_
  - [ ] 6.2 Registrar rutas en `routes/web.php` bajo middleware `auth`
    - `GET /intercambio/{negociacionId}/envio` → `IntercambioEnvioController@calcularEnvio`
    - `POST /intercambio/{negociacionId}/envio/pagar` → `IntercambioEnvioController@pagar`
    - `GET /intercambio/{negociacionId}/envio/estado` → `IntercambioEnvioController@estado`
    - _Requirements: 2.1, 3.1, 6.1_

- [ ] 7. Panel de administración — actualizar vista de intercambio
  - [ ] 7.1 Actualizar `AdminComprasController::showIntercambio()` en `app/Http/Controllers/Admin/AdminComprasController.php`
    - Agregar `pagoEnvios.usuario` al eager loading del `with([...])`
    - Pasar `$pagoEnvios` a la vista
    - _Requirements: 7.1, 7.3_
  - [ ] 7.2 Actualizar `resources/views/admin/intercambios/show.blade.php`
    - Agregar sección "Pagos de Envío" con tabla que muestre ambos registros PagoEnvioIntercambio
    - Columnas: usuario, monto, estado, tipo_pago, transaction_id, fecha
    - Indicar si el intercambio está bloqueado esperando pago de alguna parte
    - Usar solo CSS inline (sin Tailwind)
    - _Requirements: 7.1, 7.3_

- [ ] 8. API móvil — `IntercambioEnvioController` en mobil/api
  - [ ] 8.1 Crear `mobil/api/app/Http/Controllers/Api/IntercambioEnvioController.php`
    - Misma lógica que el controlador web: inyectar `IntercambioEnvioService` (o instanciarlo si el service no está disponible en el proyecto mobil/api)
    - Métodos: `calcularEnvio()`, `pagar()`, `estado()` — retornan JSON
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_
  - [ ] 8.2 Registrar rutas en `mobil/api/routes/api.php` bajo middleware `auth:sanctum`
    - `GET /intercambio/{negociacionId}/envio`
    - `POST /intercambio/{negociacionId}/envio/pagar`
    - `GET /intercambio/{negociacionId}/envio/estado`
    - _Requirements: 8.1, 8.2, 8.3_

- [ ] 9. Tests de integración
  - [ ]* 9.1 Crear `tests/Feature/IntercambioEnvioServiceTest.php` con tests unitarios
    - Negociación con ambos artículos físicos → 2 registros pendiente creados
    - Negociación producto vs. servicio → registro pull + registro tarjeta
    - Cobro rechazado → registro permanece pendiente
    - Pull = 0 → error descriptivo
    - Pago ya realizado → error idempotente (doble submit)
    - `verificarYCompletar` con solo 1 pago → estado sigue "aceptado"
    - `verificarYCompletar` con ambos pagos → estado "completado"
    - GET estado de negociación ajena → 403
    - POST pagar con tarjeta ajena → 403
    - GET envio sin dirección → 422
    - _Requirements: 1.1, 3.2, 3.3, 3.4, 4.2, 4.3, 5.2, 5.3, 6.3_

- [ ] 10. Checkpoint final — Verificar integración completa
  - Asegurarse de que todos los tests pasen, consultar al usuario si hay dudas.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada tarea referencia los requisitos específicos para trazabilidad
- Los property tests usan **eris** (o php-quickcheck) con mínimo 100 iteraciones por propiedad
- Los tests unitarios y de propiedad son complementarios, no excluyentes
- `verificarYCompletar` debe ejecutarse siempre dentro de `DB::transaction` con `lockForUpdate` para evitar condiciones de carrera
