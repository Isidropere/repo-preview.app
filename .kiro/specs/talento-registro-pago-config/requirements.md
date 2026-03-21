# Requirements Document

## Introduction

Esta funcionalidad agrega un flujo de pago manual (transferencia bancaria) obligatorio para publicar talentos/servicios (categoría 29) en CambialóRD. Incluye: (1) un flujo de pago previo al registro del talento, (2) una tabla de cuentas bancarias de la empresa administrada por SuperAdmin desde el panel de estadísticas, (3) una tabla de configuración de tarifas para la categoría 29, y (4) lógica de descuento por volumen de compra para ítems de categoría 29 con tipo_trans=1.

## Glossary

- **Sistema**: La aplicación web CambialóRD (Laravel 11).
- **Talento**: Ítem publicado bajo la categoría con `id_categoria_item = 29` (servicios/talentos).
- **SuperAdmin**: Usuario con `isSuperAdmin = true` en la tabla `users`.
- **CuentaBanco**: Registro en la tabla `cuentas_banco_empresa` que representa una cuenta bancaria de la empresa.
- **ConfigTarifa**: Registro en la tabla `config_tarifa_categoria29` con los parámetros de cobro y descuento para la categoría 29.
- **PagoRegistro**: Proceso manual (transferencia bancaria) que el usuario debe completar antes de publicar un Talento.
- **ComprobanteRegistro**: Imagen o archivo que el usuario sube como evidencia de la transferencia bancaria realizada.
- **DescuentoVolumen**: Reducción de precio aplicada automáticamente cuando la cantidad de compra de un Talento alcanza `cantidad_minima_descuento`.
- **Panel_Admin**: Vista en `/admin/estadisticas` (`resources/views/admin/stats.blade.php`).
- **ItemController**: Controlador `app/Http/Controllers/ItemController.php` que gestiona la creación de ítems.
- **CarritoService**: Servicio `app/Services/CarritoService.php` que gestiona la lógica del carrito.

---

## Requirements

### Requirement 1: Flujo de pago previo al registro de Talento

**User Story:** Como usuario registrado, quiero ver las instrucciones de pago y subir mi comprobante antes de publicar mi talento/servicio, para que mi publicación quede pendiente de aprobación tras confirmar el pago.

#### Acceptance Criteria

1. WHEN un usuario autenticado accede a la ruta `POST /talento/agregar` con `id_categoria_item = 29`, THE Sistema SHALL redirigir al usuario a una vista de pago de registro antes de guardar el ítem.
2. WHEN el usuario está en la vista de pago de registro, THE Sistema SHALL mostrar las cuentas bancarias activas de `cuentas_banco_empresa` junto con el `monto_registro` de `config_tarifa_categoria29`.
3. WHEN el usuario sube un ComprobanteRegistro válido (imagen JPEG, PNG, JPG, PDF, máximo 5 MB) y confirma el pago, THE Sistema SHALL guardar el Talento con `estatus = 0` (pendiente de aprobación) y almacenar el comprobante asociado.
4. IF el usuario no sube un ComprobanteRegistro, THEN THE Sistema SHALL mostrar un mensaje de error indicando que el comprobante es obligatorio y no guardará el ítem.
5. IF el archivo subido como ComprobanteRegistro supera 5 MB o no es de tipo JPEG, PNG, JPG o PDF, THEN THE Sistema SHALL mostrar un mensaje de error descriptivo y no guardará el ítem.
6. WHEN el Talento es guardado con `estatus = 0`, THE Sistema SHALL mostrar al usuario un mensaje de confirmación indicando que su publicación está pendiente de revisión.
7. THE Sistema SHALL almacenar los datos del formulario de talento en sesión durante el flujo de pago para no perder la información ingresada por el usuario.

---

### Requirement 2: Tabla y CRUD de cuentas bancarias de la empresa

**User Story:** Como SuperAdmin, quiero gestionar las cuentas bancarias de la empresa desde el panel de administración, para que los usuarios vean siempre información bancaria actualizada al momento de pagar.

#### Acceptance Criteria

1. THE Sistema SHALL mantener una tabla `cuentas_banco_empresa` con los campos: `id`, `banco`, `numero_cuenta`, `tipo_cuenta` (ahorro/corriente/otro), `titular`, `descripcion` (nullable), `activo` (boolean, default true), `created_at`, `updated_at`.
2. WHEN un SuperAdmin accede al Panel_Admin (`/admin/estadisticas`), THE Sistema SHALL mostrar un botón o sección para gestionar las cuentas bancarias de la empresa.
3. WHEN el SuperAdmin abre el modal de cuentas bancarias, THE Sistema SHALL mostrar la lista de CuentaBanco existentes con sus campos principales.
4. WHEN el SuperAdmin completa el formulario de creación con `banco`, `numero_cuenta`, `tipo_cuenta` y `titular` válidos y confirma, THE Sistema SHALL crear un nuevo registro en `cuentas_banco_empresa`.
5. WHEN el SuperAdmin edita una CuentaBanco existente y confirma los cambios, THE Sistema SHALL actualizar el registro correspondiente en `cuentas_banco_empresa`.
6. WHEN el SuperAdmin elimina una CuentaBanco, THE Sistema SHALL eliminar el registro de `cuentas_banco_empresa`.
7. WHEN el SuperAdmin activa o desactiva una CuentaBanco, THE Sistema SHALL actualizar el campo `activo` del registro correspondiente.
8. IF un usuario que no es SuperAdmin intenta acceder a las rutas de gestión de cuentas bancarias, THEN THE Sistema SHALL retornar un error HTTP 403.
9. IF el SuperAdmin intenta crear o editar una CuentaBanco sin los campos obligatorios (`banco`, `numero_cuenta`, `tipo_cuenta`, `titular`), THEN THE Sistema SHALL retornar errores de validación descriptivos sin guardar el registro.

---

### Requirement 3: Tabla y CRUD de configuración de tarifas para categoría 29

**User Story:** Como SuperAdmin, quiero configurar el monto de registro y los parámetros de descuento por volumen para la categoría 29, para poder ajustar las tarifas sin modificar código.

#### Acceptance Criteria

1. THE Sistema SHALL mantener una tabla `config_tarifa_categoria29` con los campos: `id`, `monto_registro` (decimal, mínimo 0), `descuento_venta_masiva` (decimal, porcentaje 0–100), `cantidad_minima_descuento` (integer, mínimo 1), `created_at`, `updated_at`.
2. THE Sistema SHALL garantizar que exista exactamente un registro activo en `config_tarifa_categoria29` en todo momento (configuración singleton).
3. WHEN un SuperAdmin accede al Panel_Admin, THE Sistema SHALL mostrar un formulario o sección para editar la ConfigTarifa vigente.
4. WHEN el SuperAdmin actualiza la ConfigTarifa con valores válidos y confirma, THE Sistema SHALL actualizar el registro en `config_tarifa_categoria29`.
5. IF el SuperAdmin intenta guardar una ConfigTarifa con `monto_registro` negativo, `descuento_venta_masiva` fuera del rango 0–100, o `cantidad_minima_descuento` menor a 1, THEN THE Sistema SHALL retornar errores de validación descriptivos sin guardar el registro.
6. WHEN no existe ningún registro en `config_tarifa_categoria29`, THE Sistema SHALL usar valores por defecto: `monto_registro = 0`, `descuento_venta_masiva = 0`, `cantidad_minima_descuento = 1`.

---

### Requirement 4: Descuento por volumen en la vista de detalle y carrito (categoría 29, tipo_trans=1)

**User Story:** Como comprador, quiero ver un mensaje informativo sobre el descuento por volumen al visualizar o agregar al carrito un talento/servicio de venta, para poder aprovechar el descuento si compro la cantidad mínima requerida.

#### Acceptance Criteria

1. WHEN un usuario visualiza el detalle de un Talento con `tipo_trans = 1` (venta), THE Sistema SHALL mostrar un mensaje indicando la cantidad mínima y el porcentaje de descuento por volumen, usando los valores de `config_tarifa_categoria29`.
2. WHEN un usuario agrega al carrito un Talento con `tipo_trans = 1` y la cantidad solicitada es mayor o igual a `cantidad_minima_descuento`, THE CarritoService SHALL aplicar automáticamente el `descuento_venta_masiva` como descuento en el registro de `items_intencion_compra`.
3. WHEN un usuario agrega al carrito un Talento con `tipo_trans = 1` y la cantidad solicitada es menor a `cantidad_minima_descuento`, THE CarritoService SHALL guardar el ítem en el carrito sin aplicar el descuento por volumen.
4. WHILE el carrito contiene un Talento con descuento por volumen aplicado, THE Sistema SHALL mostrar en la vista del carrito el descuento desglosado y el total ajustado.
5. IF `config_tarifa_categoria29` no tiene un registro activo, THEN THE Sistema SHALL mostrar el Talento sin mensaje de descuento y sin aplicar descuento por volumen.
6. THE Sistema SHALL calcular el descuento por volumen como: `precio_unitario × (descuento_venta_masiva / 100)` por unidad, aplicado sobre la cantidad total.

---

### Requirement 5: Gestión de comprobantes de pago de registro (SuperAdmin)

**User Story:** Como SuperAdmin, quiero ver y aprobar los comprobantes de pago de registro de talentos, para poder activar las publicaciones una vez verificado el pago.

#### Acceptance Criteria

1. THE Sistema SHALL mantener una tabla `pagos_registro_talento` con los campos: `id`, `id_item` (FK a `items`), `id_user` (FK a `users`), `comprobante_path` (ruta del archivo), `monto_pagado` (decimal), `estatus` (enum: `pendiente`, `aprobado`, `rechazado`), `notas` (nullable), `created_at`, `updated_at`.
2. WHEN un SuperAdmin accede al Panel_Admin, THE Sistema SHALL mostrar la lista de comprobantes de pago con `estatus = pendiente`.
3. WHEN el SuperAdmin aprueba un comprobante de pago, THE Sistema SHALL actualizar `pagos_registro_talento.estatus` a `aprobado` y cambiar `items.estatus` a `1` (activo) para el ítem correspondiente.
4. WHEN el SuperAdmin rechaza un comprobante de pago, THE Sistema SHALL actualizar `pagos_registro_talento.estatus` a `rechazado` y mantener `items.estatus = 0`.
5. IF el SuperAdmin intenta aprobar o rechazar un comprobante que ya fue procesado (`estatus != pendiente`), THEN THE Sistema SHALL retornar un mensaje de error indicando que el comprobante ya fue procesado.
