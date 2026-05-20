# Requirements Document

## Introduction

Este documento describe el flujo de pago de envío para intercambios entre usuarios en CambialóRD. Cuando dos usuarios acuerdan un intercambio (negociación pasa a estado "aceptado"), ambas partes deben pagar el costo de envío de su respectivo artículo antes de que el intercambio se marque como "completado". La plataforma no cobra comisión sobre los artículos intercambiados — su beneficio proviene exclusivamente del transporte de ambos artículos (doble vía). Existe un caso especial para intercambios producto vs. servicio (categoría 29): el lado del servicio descuenta 1 unidad de su "pull" de créditos en lugar de pagar envío.

## Glossary

- **Negociacion**: Registro de un acuerdo de intercambio entre dos usuarios. Tiene estados: Inicial → pendiente → contraoferta → aceptado → completado → rechazado / cancelado.
- **Emisor**: Usuario que inicia la negociación y ofrece su paquete de artículos.
- **Receptor**: Usuario dueño del artículo solicitado que acepta o rechaza la propuesta.
- **PagoEnvioIntercambio**: Registro del pago de envío de un participante en un intercambio.
- **Pull**: Créditos prepagados por un talento (categoría 29) registrados en PagoRegistroTalento, que le permiten participar en intercambios sin pagar envío.
- **DeliveryService**: Servicio que calcula el costo de envío según municipio de origen y valor del artículo.
- **PagoService**: Servicio que ejecuta cobros con tarjeta (CardNet/Stripe).
- **TarjetaPago**: Tarjeta de pago guardada por el usuario en la plataforma.
- **Direcciones**: Dirección registrada por el usuario; la predeterminada se usa para calcular el envío.
- **Item_Servicio**: Artículo de categoría 29 (talento/servicio), excluido del cálculo de delivery físico.
- **Item_Producto**: Artículo de cualquier categoría distinta a 29, sujeto a pago de envío.
- **Sistema**: El sistema backend de CambialóRD (Laravel API).
- **App**: La aplicación móvil de CambialóRD (React Native).

---

## Requirements

### Requirement 1: Detección del estado "aceptado" y notificación de pago de envío

**User Story:** Como usuario que acaba de acordar un intercambio, quiero recibir una notificación inmediata indicando que debo pagar el envío de mi artículo, para poder completar el proceso sin demoras.

#### Acceptance Criteria

1. WHEN la Negociacion cambia al estado "aceptado", THE Sistema SHALL crear dos registros PagoEnvioIntercambio — uno para el emisor y otro para el receptor — con estado "pendiente".
2. WHEN la Negociacion cambia al estado "aceptado", THE Sistema SHALL enviar una notificación al usuario emisor indicando que debe pagar el envío de su artículo.
3. WHEN la Negociacion cambia al estado "aceptado", THE Sistema SHALL enviar una notificación al usuario receptor indicando que debe pagar el envío de su artículo.
4. IF el emisor no tiene una dirección predeterminada registrada, THEN THE Sistema SHALL incluir en la notificación una advertencia indicando que debe registrar una dirección antes de proceder al pago.
5. IF el receptor no tiene una dirección predeterminada registrada, THEN THE Sistema SHALL incluir en la notificación una advertencia indicando que debe registrar una dirección antes de proceder al pago.

---

### Requirement 2: Cálculo del costo de envío por participante

**User Story:** Como usuario participante en un intercambio, quiero ver el costo exacto de envío de mi artículo antes de pagar, para tomar una decisión informada.

#### Acceptance Criteria

1. WHEN el usuario solicita el detalle de pago de envío de su intercambio, THE Sistema SHALL calcular el costo de envío usando DeliveryService con el municipio de la dirección predeterminada del usuario y el valor del artículo que envía.
2. WHEN el usuario solicita el detalle de pago de envío, THE Sistema SHALL retornar el desglose completo: precio base, costo flete, costo plataforma, costo seguro, costo manejo y costo total.
3. IF el artículo del usuario tiene id_categoria_item igual a 29 (Item_Servicio), THEN THE Sistema SHALL omitir el cálculo de delivery y retornar que aplica descuento de pull en lugar de pago de envío.
4. IF el usuario no tiene dirección predeterminada registrada, THEN THE Sistema SHALL retornar un error indicando que debe registrar una dirección antes de calcular el envío.
5. IF DeliveryService no encuentra zona de delivery para el municipio del usuario, THEN THE Sistema SHALL retornar un error descriptivo indicando que la zona no está disponible.

---

### Requirement 3: Pago de envío con tarjeta guardada (artículo físico)

**User Story:** Como usuario con un artículo físico en un intercambio aceptado, quiero pagar el envío de mi artículo usando mi tarjeta guardada, para completar mi parte del acuerdo.

#### Acceptance Criteria

1. WHEN el usuario envía una solicitud de pago de envío con su id_tarjeta, THE Sistema SHALL verificar que la tarjeta pertenece al usuario antes de procesar el cobro.
2. WHEN el cobro es aprobado por PagoService, THE Sistema SHALL actualizar el registro PagoEnvioIntercambio del usuario a estado "pagado" y guardar el transaction_id y approval_code.
3. IF el cobro es rechazado por PagoService, THEN THE Sistema SHALL mantener el PagoEnvioIntercambio en estado "pendiente" y retornar el mensaje de error del proveedor de pago.
4. IF el usuario intenta pagar un PagoEnvioIntercambio que ya está en estado "pagado", THEN THE Sistema SHALL retornar un error indicando que el envío ya fue pagado.
5. IF el usuario no tiene tarjeta guardada activa, THEN THE Sistema SHALL retornar un error indicando que debe agregar una tarjeta de pago.
6. THE Sistema SHALL registrar el pago de envío con: id_negociacion, id_user, monto, id_tarjeta, transaction_id, approval_code, estado y timestamps.

---

### Requirement 4: Descuento de pull para artículos de servicio (categoría 29)

**User Story:** Como talento (categoría 29) participante en un intercambio producto vs. servicio, quiero que se descuente 1 unidad de mi pull en lugar de cobrarme envío, para no pagar por un servicio que no requiere transporte físico.

#### Acceptance Criteria

1. WHEN el usuario con Item_Servicio confirma su participación en el pago de envío del intercambio, THE Sistema SHALL verificar que el usuario tiene al menos 1 unidad de pull disponible en PagoRegistroTalento con estatus "aprobado".
2. WHEN el pull es suficiente, THE Sistema SHALL descontar 1 unidad del pull del usuario y actualizar el PagoEnvioIntercambio a estado "pagado_pull".
3. IF el usuario con Item_Servicio no tiene pull disponible, THEN THE Sistema SHALL retornar un error indicando que no tiene créditos suficientes y debe adquirir un plan de pull.
4. THE Sistema SHALL registrar en el PagoEnvioIntercambio el tipo de pago como "pull" y la referencia al registro PagoRegistroTalento utilizado.
5. WHEN el descuento de pull es procesado, THE Sistema SHALL enviar una notificación al usuario confirmando que se descontó 1 unidad de su pull.

---

### Requirement 5: Transición automática a "completado"

**User Story:** Como administrador de la plataforma, quiero que el intercambio pase automáticamente a "completado" cuando ambas partes hayan pagado su envío, para no tener que hacerlo manualmente.

#### Acceptance Criteria

1. WHEN el pago de envío de un participante es confirmado (estado "pagado" o "pagado_pull"), THE Sistema SHALL verificar si el otro participante también tiene su PagoEnvioIntercambio en estado "pagado" o "pagado_pull".
2. WHEN ambos PagoEnvioIntercambio están en estado "pagado" o "pagado_pull", THE Sistema SHALL actualizar el estado de la Negociacion a "completado".
3. WHILE solo uno de los dos PagoEnvioIntercambio está confirmado, THE Sistema SHALL mantener la Negociacion en estado "aceptado" y notificar al participante que falta que complete su pago.
4. THE Sistema SHALL ejecutar la verificación y transición dentro de una transacción de base de datos para evitar condiciones de carrera.

---

### Requirement 6: Consulta del estado de pagos de envío (usuario)

**User Story:** Como usuario participante en un intercambio, quiero consultar el estado de los pagos de envío de ambas partes, para saber si el intercambio está listo para completarse.

#### Acceptance Criteria

1. WHEN el usuario consulta el detalle de una negociación aceptada, THE Sistema SHALL retornar el estado de su propio PagoEnvioIntercambio y el estado del PagoEnvioIntercambio del otro participante.
2. THE Sistema SHALL retornar para cada PagoEnvioIntercambio: estado, monto, tipo_pago (tarjeta o pull) y fecha de pago si aplica.
3. IF el usuario consulta una negociación que no le pertenece (no es emisor ni receptor), THEN THE Sistema SHALL retornar un error 403.

---

### Requirement 7: Panel de administración — vista de pagos de envío de intercambios

**User Story:** Como administrador, quiero ver el estado de los dos pagos de envío de cada intercambio en el panel de administración, para monitorear el proceso y resolver incidencias.

#### Acceptance Criteria

1. WHEN el administrador consulta el detalle de un intercambio, THE Sistema SHALL mostrar los dos registros PagoEnvioIntercambio con: usuario, monto, estado, tipo_pago, transaction_id y fecha.
2. WHEN el administrador consulta la lista de intercambios, THE Sistema SHALL permitir filtrar por estado de pago de envío (ambos pagados, uno pendiente, ambos pendientes).
3. THE Sistema SHALL mostrar en el detalle del intercambio si el intercambio está bloqueado esperando pago de envío de alguna de las partes.
4. WHERE el administrador tiene permisos de superadmin, THE Sistema SHALL permitir marcar manualmente un PagoEnvioIntercambio como "pagado" para resolver incidencias excepcionales.

---

### Requirement 8: Pantalla de pago de envío en la App móvil

**User Story:** Como usuario de la App móvil, quiero ver una pantalla clara para pagar el envío de mi artículo en un intercambio aceptado, para completar el proceso desde mi teléfono.

#### Acceptance Criteria

1. WHEN la negociación pasa a estado "aceptado", THE App SHALL mostrar al usuario una pantalla o modal de pago de envío con el desglose del costo calculado por DeliveryService.
2. WHEN el usuario confirma el pago en la App, THE App SHALL enviar la solicitud al endpoint de pago de envío con la tarjeta seleccionada.
3. WHEN el pago es confirmado por el Sistema, THE App SHALL actualizar la pantalla de negociación mostrando el estado "envío pagado" para el usuario actual.
4. IF el artículo del usuario es de categoría 29 (Item_Servicio), THE App SHALL mostrar en lugar del formulario de pago un botón de confirmación indicando que se descontará 1 unidad de pull.
5. IF el usuario no tiene tarjeta guardada, THE App SHALL mostrar un mensaje con un enlace para agregar una tarjeta antes de continuar.
6. WHEN ambos pagos están confirmados, THE App SHALL actualizar el estado de la negociación a "completado" y mostrar un mensaje de confirmación al usuario.
