# Implementation Plan: talento-registro-pago-config

## Overview

Implementar el flujo de pago con Cardnet para registro de talentos (categoría 29), el CRUD de cuentas bancarias informativas, la configuración de tarifas singleton y el descuento por volumen en carrito/detalle.

## Tasks

- [x] 1. Crear migraciones y modelos base
  - Crear migración `create_cuentas_banco_empresa_table` con campos: `id`, `banco`, `numero_cuenta`, `tipo_cuenta` (enum), `titular`, `descripcion` (nullable), `activo` (default 1), timestamps
  - Crear migración `create_config_tarifa_categoria29_table` con campos: `id`, `monto_registro` (decimal 10,2), `descuento_venta_masiva` (decimal 5,2), `cantidad_minima_descuento` (int unsigned, default 1), timestamps
  - Crear migración `create_pagos_registro_talento_table` con campos: `id`, `id_item` (FK items), `id_user` (FK users), `transaction_id` (varchar 100), `monto_pagado` (decimal 10,2), `estatus` (varchar 20, default 'aprobado'), `notas` (nullable), timestamps
  - Crear modelo `app/Models/CuentaBancoEmpresa.php` con `$fillable`, `$casts`, scope `activas()`
  - Crear modelo `app/Models/ConfigTarifaCategoria29.php` con `$fillable`, `$casts`, método estático `vigente()`
  - Crear modelo `app/Models/PagoRegistroTalento.php` con `$fillable`, relaciones `item()` y `user()`
  - Agregar relación `pagoRegistro()` en `app/Models/Item.php`
  - _Requirements: 2.1, 3.1, 5.1_

  - [ ]* 1.1 Escribir tests de modelos
    - Verificar que `ConfigTarifaCategoria29::vigente()` retorna defaults cuando la tabla está vacía
    - Verificar que `vigente()` retorna el registro existente cuando hay uno
    - _Requirements: 3.6_

- [x] 2. Implementar CRUD de cuentas bancarias (SuperAdmin)
  - Crear `app/Http/Controllers/Admin/AdminCuentaBancoController.php` con métodos `index`, `store`, `update`, `destroy`, `toggleActivo` — todos retornan JSON
  - Validar en `store`/`update`: `banco` required string max:100, `numero_cuenta` required string max:50, `tipo_cuenta` required in:ahorro,corriente,otro, `titular` required string max:150, `descripcion` nullable string
  - Registrar rutas en `routes/web.php` bajo middleware `['auth','superadmin']` prefix `admin`:
    - `GET /cuentas-banco` → `admin.cuentas.index`
    - `POST /cuentas-banco` → `admin.cuentas.store`
    - `PUT /cuentas-banco/{id}` → `admin.cuentas.update`
    - `DELETE /cuentas-banco/{id}` → `admin.cuentas.destroy`
    - `PATCH /cuentas-banco/{id}/toggle` → `admin.cuentas.toggle`
  - _Requirements: 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 2.9_

  - [ ]* 2.1 Escribir tests de AdminCuentaBancoController
    - `test_superadmin_puede_crear_cuenta` — verifica registro en BD con valores exactos
    - `test_superadmin_puede_editar_cuenta` — verifica actualización en BD
    - `test_superadmin_puede_eliminar_cuenta` — verifica que el registro ya no existe
    - `test_superadmin_puede_toggle_activo` — verifica cambio de campo `activo`
    - `test_admin_normal_recibe_403` y `test_usuario_normal_recibe_403`
    - `test_campos_obligatorios_retornan_422`
    - _Requirements: 2.4, 2.5, 2.6, 2.7, 2.8, 2.9_

- [x] 3. Implementar CRUD de configuración de tarifas (SuperAdmin)
  - Crear `app/Http/Controllers/Admin/AdminConfigTarifaController.php` con métodos `show` (retorna JSON con `vigente()`) y `update` (upsert singleton con `updateOrCreate(['id' => 1], $datos)`)
  - Validar en `update`: `monto_registro` required numeric min:0, `descuento_venta_masiva` required numeric between:0,100, `cantidad_minima_descuento` required integer min:1
  - Registrar rutas en `routes/web.php` bajo middleware `['auth','superadmin']` prefix `admin`:
    - `GET /config-tarifa` → `admin.config_tarifa.show`
    - `PUT /config-tarifa` → `admin.config_tarifa.update`
  - _Requirements: 3.2, 3.3, 3.4, 3.5, 3.6_

  - [ ]* 3.1 Escribir tests de AdminConfigTarifaController
    - `test_superadmin_puede_actualizar_config` — verifica que `vigente()` retorna los valores guardados
    - `test_valores_invalidos_retornan_422` — monto negativo, descuento > 100, cantidad < 1
    - `test_singleton_no_crea_segundo_registro` — dos updates consecutivos → exactamente 1 fila en tabla
    - _Requirements: 3.2, 3.4, 3.5_

- [x] 4. Checkpoint — Asegurar que migraciones, modelos y rutas admin funcionan
  - Verificar que todas las migraciones corren sin errores
  - Verificar que los endpoints de cuentas bancarias y config tarifa responden correctamente
  - Asegurar que todos los tests hasta aquí pasan. Preguntar al usuario si hay dudas.

- [x] 5. Modificar ItemController para interceptar categoría 29
  - En `AddTalento()`, después de validar el formulario, agregar condición: si `id_categoria_item == 29`:
    - Guardar datos validados (sin archivos) en `session('talento_pendiente_data')`
    - Guardar archivos subidos temporalmente en `storage/app/temp/{uuid}/` y sus rutas en `session('talento_pendiente_files')`
    - Redirigir a `route('talento.pago.show')`
  - Si `id_categoria_item != 29`, continuar con el flujo normal existente
  - _Requirements: 1.1, 1.7_

  - [ ]* 5.1 Escribir tests de intercepción en ItemController
    - `test_redirige_a_pago_cuando_categoria_es_29` — verifica redirect y datos en sesión
    - `test_no_redirige_cuando_categoria_no_es_29` — verifica flujo normal sin sesión
    - `test_datos_persisten_en_sesion` — verifica que `talento_pendiente_data` contiene los campos enviados
    - _Requirements: 1.1, 1.7_

- [x] 6. Crear TalentoRegistroPagoService
  - Crear `app/Services/TalentoRegistroPagoService.php`
  - Método `procesarPagoYGuardarTalento(int $userId, string $idTarjeta, ?string $cvv, string $clientIp): array`:
    1. Recuperar `session('talento_pendiente_data')` — si vacío, lanzar excepción
    2. Obtener tarjeta del usuario con `TarjetaPago::where('id_tarjeta', $idTarjeta)->where('id_user', $userId)->first()` — si null, retornar error
    3. Obtener `monto_registro` de `ConfigTarifaCategoria29::vigente()`
    4. Preparar `$datosTarjeta` usando `$tarjeta->datosCardnet($cvv)`
    5. Llamar a `PagoService::cobrarTarjeta($monto, '214', $datosTarjeta, ['client_ip' => $clientIp])`
    6. Si `success = false`: retornar `['success' => false, 'error' => $resultado['error']]`
    7. Si `success = true`: dentro de `DB::transaction()`:
       - Crear `Item` con los datos de sesión y `estatus = 1`
       - Mover archivos de `storage/app/temp/` a `public/storage/talentos/{id_item}/`
       - Crear `PagoRegistroTalento` con `transaction_id`, `monto_pagado`, `estatus = 'aprobado'`
       - Limpiar sesión (`talento_pendiente_data`, `talento_pendiente_files`)
       - Retornar `['success' => true, 'item' => $item]`
    8. Si falla el `DB::transaction()` tras cobro aprobado: intentar anular con `PagoService::anularTransaccion()`, log del error
  - _Requirements: 1.3, 1.4, 5.1_

  - [ ]* 6.1 Escribir property test: pago aprobado siempre crea ítem con estatus=1
    - **Property 3: Pago aprobado crea ítem con estatus=1 y registro de pago**
    - Mock `PagoService::cobrarTarjeta` para retornar `success=true` con `transaction_id` aleatorio
    - 100 iteraciones con datos de talento aleatorios
    - Verificar `items.estatus = 1` y `pagos_registro_talento.transaction_id` correcto
    - **Validates: Requirements 1.3**

  - [ ]* 6.2 Escribir property test: pago rechazado no crea ítem
    - **Property 4: Pago rechazado no crea ítem ni registro de pago**
    - Mock `PagoService::cobrarTarjeta` para retornar `success=false` con mensajes de error aleatorios
    - 100 iteraciones
    - Verificar que no se crea ningún `Item` ni `PagoRegistroTalento`
    - **Validates: Requirements 1.4**

- [x] 7. Crear TalentoRegistroPagoController y vista de pago
  - Crear `app/Http/Controllers/TalentoRegistroPagoController.php`
  - Método `mostrarPago()`:
    - Si no hay `session('talento_pendiente_data')`, redirigir a `route('items.talento_create')` con error
    - Cargar tarjetas activas del usuario: `TarjetaPago::where('id_user', auth()->id())->where('estatus', 1)->get()`
    - Cargar `$monto = ConfigTarifaCategoria29::vigente()->monto_registro`
    - Retornar vista `talentos.pago-talento` con `$tarjetas` y `$monto`
  - Método `procesarPago(Request $request)`:
    - Validar: `id_tarjeta` required exists:tarjetas_pagos,id_tarjeta, `cvv` nullable string max:4
    - Llamar a `TalentoRegistroPagoService::procesarPagoYGuardarTalento()`
    - Si error: `redirect()->back()->with('error', $resultado['error'])`
    - Si éxito: `redirect()->route('items.admintalento')->with('success', 'Tu talento fue publicado exitosamente.')`
  - Crear vista `resources/views/talentos/pago-talento.blade.php`:
    - Mostrar monto a pagar
    - Selector de tarjeta guardada (igual que checkout) con campo CVV
    - Formulario para agregar nueva tarjeta (reutilizar componente de checkout si existe)
    - Botón "Pagar y publicar"
  - Registrar rutas en `routes/web.php` bajo middleware `auth`:
    - `GET /talento/pago` → `talento.pago.show`
    - `POST /talento/pago` → `talento.pago.procesar` con `throttle.sensitive:5,1`
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.6_

  - [ ]* 7.1 Escribir tests de TalentoRegistroPagoController
    - `test_vista_pago_muestra_monto_registro` — verifica que el monto de `config_tarifa_categoria29` aparece en la vista
    - `test_sesion_expirada_redirige_a_crear` — sin sesión → redirect a crear talento
    - `test_pago_aprobado_redirige_a_talentos_con_exito` — mock Cardnet aprobado
    - `test_pago_rechazado_redirige_back_con_error` — mock Cardnet rechazado
    - `test_tarjeta_invalida_retorna_error_validacion`
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.6_

- [x] 8. Checkpoint — Verificar flujo completo de pago con Cardnet
  - Asegurar que todos los tests del flujo de pago pasan
  - Verificar que el mock de Cardnet funciona correctamente en tests
  - Preguntar al usuario si hay dudas antes de continuar.

- [x] 9. Implementar descuento por volumen en CarritoService
  - En `app/Services/CarritoService.php`, método `agregarItem()`:
    - Después de resolver el ítem, verificar si `$item->id_categoria_item == 29 && $item->tipo_trans == 1`
    - Si sí: obtener `$config = ConfigTarifaCategoria29::vigente()`
    - Si `$cantidad >= $config->cantidad_minima_descuento` y `$config->descuento_venta_masiva > 0`:
      - Calcular `$descuento = $item->valor * ($config->descuento_venta_masiva / 100)`
      - Guardar en `items_intencion_compra.descuento = $descuento`
    - Si no aplica: guardar `descuento = 0` (o el descuento propio del ítem)
  - _Requirements: 4.2, 4.3, 4.6_

  - [ ]* 9.1 Escribir property test: descuento por volumen según cantidad
    - **Property 13: Descuento por volumen se aplica si y solo si cantidad >= mínimo**
    - 100 iteraciones con valores aleatorios de `cantidad`, `cantidad_minima_descuento`, `descuento_venta_masiva`
    - Verificar que `items_intencion_compra.descuento` es exactamente `valor × (descuento / 100)` cuando aplica, y 0 cuando no
    - **Validates: Requirements 4.2, 4.3, 4.6**

  - [ ]* 9.2 Escribir tests unitarios de descuento por volumen
    - `test_descuento_aplicado_cuando_cantidad_alcanza_minimo`
    - `test_sin_descuento_cuando_cantidad_menor_al_minimo`
    - `test_sin_descuento_cuando_config_vacia`
    - _Requirements: 4.2, 4.3_

- [x] 10. Mostrar mensaje de descuento en detalle de talento
  - En la vista de detalle de producto (o en `ItemController::showDetail()`/`show()`), para ítems con `id_categoria_item == 29` y `tipo_trans == 1`:
    - Pasar `$config = ConfigTarifaCategoria29::vigente()` a la vista
    - Mostrar mensaje: "Compra {$config->cantidad_minima_descuento} o más y obtén {$config->descuento_venta_masiva}% de descuento"
    - Si `descuento_venta_masiva == 0` o config vacía, no mostrar el mensaje
  - _Requirements: 4.1, 4.5_

  - [ ]* 10.1 Escribir tests de vista de detalle
    - `test_detalle_talento_muestra_mensaje_descuento` — verifica que el mensaje aparece con los valores correctos
    - `test_sin_mensaje_cuando_descuento_es_cero`
    - _Requirements: 4.1, 4.5_

- [x] 11. Integrar secciones en panel SuperAdmin (/admin/estadisticas)
  - En `resources/views/admin/stats.blade.php`, agregar:
    - Sección/tab "Cuentas Bancarias" con tabla de cuentas y botones CRUD (modal con formulario)
    - Sección/tab "Tarifas Categoría 29" con formulario de edición de `config_tarifa_categoria29`
  - Las llamadas AJAX usan los endpoints JSON ya creados en tareas 2 y 3
  - En `AdminStatsController::index()`, pasar `$cuentasBanco` y `$configTarifa` a la vista
  - _Requirements: 2.2, 2.3, 3.3_

- [x] 12. Checkpoint final — Asegurar que todos los tests pasan
  - Ejecutar suite completa de tests del feature
  - Verificar que no hay regresiones en tests existentes
  - Asegurar que todos los tests pasan. Preguntar al usuario si hay dudas.

## Notes

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cardnet debe mockearse en todos los tests (`PagoService` o `CardnetProvider`)
- Los tests usan `DatabaseTransactions` (no `RefreshDatabase`), consistente con el proyecto
- La tabla `pagos_registro_talento` no tiene `comprobante_path` ni estados `pendiente`/`rechazado` — el registro solo se crea cuando Cardnet aprueba
- Las cuentas bancarias (`cuentas_banco_empresa`) son informativas y no participan en el flujo de pago con Cardnet
