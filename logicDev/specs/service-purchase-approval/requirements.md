# Requirements Document

## Introduction

Cuando un comprador intenta adquirir un servicio/talento (categoría 29) en CambialóRD, el flujo actual procede directamente al pago sin intervención del proveedor. Esta funcionalidad introduce un paso de aprobación previo al pago: el proveedor del servicio debe aprobar o rechazar la solicitud antes de que el comprador pueda pagar. Además, se crea una nueva vista "Mis Ventas de Talentos" (`/mis-ventas-talentos`) donde los proveedores gestionan las solicitudes entrantes, similar al patrón existente de "Mis Intercambios".

## Glossary

- **Sistema**: La aplicación web CambialóRD
- **Comprador**: Usuario autenticado que desea adquirir un servicio/talento (categoría 29)
- **Proveedor**: Usuario autenticado que publicó el servicio/talento (dueño del Item con `id_categoria_item = 29`)
- **Solicitud_Servicio**: Registro que representa una petición de compra de servicio pendiente de aprobación por el Proveedor. Contiene referencia al Comprador, al Item, estado y timestamps
- **Carrito_Servicio**: Carrito del usuario con `tipo = 'servicio'` que contiene items de categoría 29
- **CheckoutService**: Servicio que orquesta el flujo completo de pago
- **Vista_Mis_Ventas_Talentos**: Página `/mis-ventas-talentos` donde el Proveedor gestiona solicitudes de servicio entrantes
- **Notificacion**: Mensaje interno del sistema enviado a un usuario mediante el evento NuevaNotificacion y el modelo Message

## Requirements

### Requirement 1: Creación de Solicitud de Servicio al hacer Checkout

**User Story:** Como Comprador, quiero que al intentar pagar un servicio/talento se cree una solicitud pendiente de aprobación, para que el Proveedor pueda decidir si acepta prestar el servicio antes de que se me cobre.

#### Acceptance Criteria

1. WHEN el Comprador inicia el checkout de un Carrito_Servicio, THE Sistema SHALL crear una Solicitud_Servicio con estado `pendiente_aprobacion` para cada item seleccionado en el carrito, en lugar de proceder al cobro
2. WHEN el Sistema crea una Solicitud_Servicio, THE Solicitud_Servicio SHALL almacenar el identificador del Comprador, el identificador del Item, el identificador del Proveedor, la cantidad, el monto total y la fecha de creación
3. WHEN el Sistema crea una Solicitud_Servicio, THE Sistema SHALL mostrar al Comprador un mensaje de confirmación indicando que la solicitud fue enviada y está pendiente de aprobación del Proveedor
4. WHILE un Item tiene una Solicitud_Servicio en estado `pendiente_aprobacion`, THE Sistema SHALL permitir que otros compradores también creen solicitudes para el mismo Item (no bloquear el inventario)
5. THE CheckoutService SHALL mantener el flujo de pago directo sin cambios para items de tipo `producto` (categoría distinta a 29)

### Requirement 2: Visualización de Información del Proveedor antes del Checkout

**User Story:** Como Comprador, quiero ver quién dará el servicio y desde dónde vendrá antes de confirmar la solicitud, para tomar una decisión informada.

#### Acceptance Criteria

1. WHEN el Comprador accede a la vista de checkout de un Carrito_Servicio, THE Sistema SHALL mostrar el nombre del Proveedor de cada servicio seleccionado
2. WHEN el Comprador accede a la vista de checkout de un Carrito_Servicio, THE Sistema SHALL mostrar la ubicación (municipio) de la dirección predeterminada del Proveedor para cada servicio seleccionado
3. IF el Proveedor no tiene una dirección predeterminada registrada, THEN THE Sistema SHALL mostrar el texto "Ubicación no disponible" en lugar de la ubicación

### Requirement 3: Vista Mis Ventas de Talentos

**User Story:** Como Proveedor, quiero tener una vista dedicada donde pueda ver y gestionar las solicitudes de compra de mis servicios, para poder aprobar o rechazar cada solicitud.

#### Acceptance Criteria

1. THE Sistema SHALL proveer una vista accesible en la ruta `/mis-ventas-talentos` para usuarios autenticados
2. WHEN el Proveedor accede a la Vista_Mis_Ventas_Talentos, THE Sistema SHALL mostrar todas las Solicitudes_Servicio asociadas a items del Proveedor, ordenadas por fecha de creación descendente
3. WHEN el Proveedor accede a la Vista_Mis_Ventas_Talentos, THE Sistema SHALL mostrar para cada solicitud: el nombre del servicio, el nombre del Comprador, la ubicación (municipio) del Comprador, el monto total, la fecha de creación y el estado actual
4. IF el Comprador no tiene una dirección registrada, THEN THE Sistema SHALL mostrar "Ubicación no disponible" en la solicitud correspondiente
5. THE Vista_Mis_Ventas_Talentos SHALL seguir el mismo patrón visual y de layout que la vista existente de Mis Intercambios (`/negociaciones`)

### Requirement 4: Aprobación de Solicitud por el Proveedor

**User Story:** Como Proveedor, quiero poder aprobar una solicitud de servicio, para que el Comprador pueda proceder al pago.

#### Acceptance Criteria

1. WHEN el Proveedor aprueba una Solicitud_Servicio, THE Sistema SHALL cambiar el estado de la solicitud a `aprobada`
2. WHEN el Proveedor aprueba una Solicitud_Servicio, THE Sistema SHALL enviar una Notificacion al Comprador indicando que su solicitud fue aprobada y que puede proceder al pago
3. WHEN el Proveedor aprueba una Solicitud_Servicio, THE Sistema SHALL redirigir al Proveedor a la Vista_Mis_Ventas_Talentos con un mensaje de éxito
4. WHILE una Solicitud_Servicio tiene estado distinto a `pendiente_aprobacion`, THE Sistema SHALL impedir que el Proveedor la apruebe nuevamente

### Requirement 5: Rechazo de Solicitud por el Proveedor

**User Story:** Como Proveedor, quiero poder rechazar una solicitud de servicio, para declinar prestar el servicio a un Comprador específico.

#### Acceptance Criteria

1. WHEN el Proveedor rechaza una Solicitud_Servicio, THE Sistema SHALL cambiar el estado de la solicitud a `rechazada`
2. WHEN el Proveedor rechaza una Solicitud_Servicio, THE Sistema SHALL enviar una Notificacion al Comprador indicando que su solicitud fue rechazada
3. WHEN el Proveedor rechaza una Solicitud_Servicio, THE Sistema SHALL eliminar el item correspondiente del Carrito_Servicio del Comprador
4. WHEN el Proveedor rechaza una Solicitud_Servicio, THE Sistema SHALL redirigir al Proveedor a la Vista_Mis_Ventas_Talentos con un mensaje de confirmación
5. WHILE una Solicitud_Servicio tiene estado distinto a `pendiente_aprobacion`, THE Sistema SHALL impedir que el Proveedor la rechace nuevamente

### Requirement 6: Pago tras Aprobación

**User Story:** Como Comprador, quiero poder proceder al pago una vez que el Proveedor apruebe mi solicitud, para completar la compra del servicio.

#### Acceptance Criteria

1. WHEN una Solicitud_Servicio tiene estado `aprobada`, THE Sistema SHALL permitir al Comprador acceder al flujo de pago estándar para ese servicio
2. WHEN el Comprador completa el pago de una Solicitud_Servicio aprobada, THE Sistema SHALL cambiar el estado de la solicitud a `pagada`
3. WHEN el Comprador completa el pago, THE Sistema SHALL enviar una Notificacion al Proveedor indicando que el Comprador completó el pago del servicio
4. IF el Comprador intenta pagar un servicio cuya Solicitud_Servicio no tiene estado `aprobada`, THEN THE Sistema SHALL bloquear el pago y mostrar un mensaje indicando que la solicitud debe ser aprobada primero

### Requirement 7: Notificaciones de Cambio de Estado

**User Story:** Como usuario (Comprador o Proveedor), quiero recibir notificaciones cuando cambie el estado de una solicitud de servicio, para estar informado del progreso.

#### Acceptance Criteria

1. WHEN el Sistema crea una Solicitud_Servicio, THE Sistema SHALL enviar una Notificacion al Proveedor indicando que tiene una nueva solicitud de servicio pendiente
2. WHEN el estado de una Solicitud_Servicio cambia a `aprobada`, THE Sistema SHALL enviar una Notificacion al Comprador con un enlace para proceder al pago
3. WHEN el estado de una Solicitud_Servicio cambia a `rechazada`, THE Sistema SHALL enviar una Notificacion al Comprador indicando el rechazo
4. WHEN el estado de una Solicitud_Servicio cambia a `pagada`, THE Sistema SHALL enviar una Notificacion al Proveedor confirmando el pago recibido
5. THE Sistema SHALL utilizar el evento NuevaNotificacion existente y el modelo Message para todas las notificaciones de Solicitud_Servicio

### Requirement 8: Autorización y Seguridad

**User Story:** Como usuario, quiero que solo las partes involucradas puedan gestionar una solicitud de servicio, para proteger la integridad del proceso.

#### Acceptance Criteria

1. THE Sistema SHALL permitir aprobar o rechazar una Solicitud_Servicio únicamente al Proveedor del item asociado
2. THE Sistema SHALL permitir pagar una Solicitud_Servicio únicamente al Comprador que la creó
3. IF un usuario no autorizado intenta aprobar, rechazar o pagar una Solicitud_Servicio, THEN THE Sistema SHALL retornar un error HTTP 403
4. THE Sistema SHALL validar que el Comprador no pueda crear una Solicitud_Servicio para sus propios items
