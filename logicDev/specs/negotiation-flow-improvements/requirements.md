# Documento de Requisitos — Mejoras al Flujo de Negociación

## Introducción

Este documento define los requisitos para tres mejoras al flujo de negociación en CambialoRD:
1. Corrección de bugs en el modal de negociación del carrito (`/carrito/carrito`)
2. Nueva pestaña de intención de compra en el panel admin (`/admin?tab=intencion_compra`)
3. Flujo de negociación bilateral con mensajes predefinidos en `/negociaciones`

## Glosario

- **Sistema_Carrito**: Módulo del carrito de compras que incluye el modal de negociación para iniciar intercambios desde `/carrito/carrito`
- **Sistema_Admin**: Panel de administración accesible en `/admin` con pestañas para gestionar compras, ventas e intercambios
- **Sistema_Negociacion**: Módulo de negociaciones/intercambios entre usuarios, accesible en `/negociaciones`
- **Emisor**: Usuario que propone un intercambio (quiere el artículo ajeno)
- **Receptor**: Usuario dueño del artículo solicitado
- **Mensaje_Predefinido**: Mensaje preconfigurado en la tabla `predefined_messages` con campos titulo, mensaje, tipo y rol
- **Accion_Predefinida**: Tipo/categoría de mensaje predefinido seleccionable por el usuario (campo `tipo` del modelo PredefinedMessage)
- **Aprobacion_Bilateral**: Proceso donde tanto emisor como receptor deben confirmar/aprobar la negociación antes de proceder al pago
- **ItemIntencionCompra**: Modelo que representa un artículo agregado al carrito pero no comprado aún

## Requisitos

### Requisito 1: Corrección de la ruta de cálculo de delivery en el carrito

**Historia de Usuario:** Como usuario, quiero que el cálculo de delivery funcione correctamente en el modal de negociación del carrito, para poder ver el costo de envío sin errores 404.

#### Criterios de Aceptación

1. WHEN el modal de negociación del carrito necesita calcular el costo de delivery, THE Sistema_Carrito SHALL utilizar la ruta web `/delivery/calcular` en lugar de la ruta API `/api/delivery/calcular`
2. WHEN la ruta `/delivery/calcular` responde exitosamente, THE Sistema_Carrito SHALL mostrar el costo de envío calculado al usuario
3. IF la ruta `/delivery/calcular` retorna un error, THEN THE Sistema_Carrito SHALL mostrar un mensaje de error descriptivo al usuario sin interrumpir el flujo de negociación

### Requisito 2: Envío del valor de AccionPredefinido al backend

**Historia de Usuario:** Como usuario, quiero que la acción predefinida seleccionada en el modal de negociación se envíe correctamente al backend, para que mi intención de negociación quede registrada con el tipo de acción correcto.

#### Criterios de Aceptación

1. THE Sistema_Carrito SHALL incluir un campo oculto (`input hidden`) con name `accionInput` en el formulario del modal de negociación
2. WHEN el usuario selecciona un valor en el select de AccionPredefinido, THE Sistema_Carrito SHALL actualizar el valor del campo oculto `accionInput` con el valor seleccionado
3. WHEN el formulario de negociación se envía al backend, THE Sistema_Carrito SHALL incluir el valor de `accionInput` en los datos enviados

### Requisito 3: Validación visual de campos requeridos en el modal de negociación

**Historia de Usuario:** Como usuario, quiero ver indicaciones visuales claras cuando no completo campos requeridos en el modal de negociación, para corregir errores sin depender de alertas del navegador.

#### Criterios de Aceptación

1. WHEN el usuario intenta enviar el formulario de negociación con campos requeridos vacíos, THE Sistema_Carrito SHALL resaltar visualmente los campos faltantes con un borde rojo y un mensaje de error debajo de cada campo
2. THE Sistema_Carrito SHALL mostrar los mensajes de error de validación inline (debajo del campo correspondiente) en lugar de usar `alert()` del navegador
3. WHEN el usuario corrige un campo previamente marcado como inválido, THE Sistema_Carrito SHALL remover inmediatamente la indicación visual de error de ese campo

### Requisito 4: Nueva pestaña de intención de compra en el panel admin

**Historia de Usuario:** Como administrador, quiero ver una pestaña dedicada en `/admin` que muestre todos los artículos en carritos que no han sido comprados, para identificar oportunidades de venta y patrones de comportamiento de usuarios.

#### Criterios de Aceptación

1. THE Sistema_Admin SHALL mostrar una pestaña "Intención de Compra" en la navegación del panel principal `/admin` accesible mediante el parámetro `?tab=intencion_compra`
2. WHEN el administrador selecciona la pestaña "Intención de Compra", THE Sistema_Admin SHALL mostrar una tabla con las columnas: usuario, artículo, cantidad, precio unitario y total
3. THE Sistema_Admin SHALL mostrar únicamente artículos de tipo venta (tipo_trans 1 o 3) que están en carritos activos y no han sido comprados
4. WHEN el administrador ingresa un término de búsqueda en la pestaña "Intención de Compra", THE Sistema_Admin SHALL filtrar los resultados por nombre del artículo, nombre del usuario o email del usuario
5. THE Sistema_Admin SHALL paginar los resultados de intención de compra con 20 registros por página

### Requisito 5: Interfaz de chat con mensajes predefinidos en negociaciones

**Historia de Usuario:** Como usuario (emisor o receptor), quiero poder enviar mensajes predefinidos durante la negociación desde la página `/negociaciones`, para comunicar mis intenciones de forma rápida y estructurada sin salir de la vista de intercambios.

#### Criterios de Aceptación

1. WHEN una negociación está en estado "Inicial", "aceptado" o "contraoferta", THE Sistema_Negociacion SHALL mostrar una sección de mensajes predefinidos dentro de la tarjeta de negociación
2. THE Sistema_Negociacion SHALL mostrar los mensajes predefinidos disponibles filtrados por el rol del usuario (emisor, receptor o general)
3. WHEN el usuario selecciona un mensaje predefinido, THE Sistema_Negociacion SHALL mostrar una vista previa del mensaje antes de enviarlo
4. WHEN el usuario confirma el envío de un mensaje predefinido, THE Sistema_Negociacion SHALL registrar el mensaje asociado a la negociación y mostrarlo en la interfaz de chat
5. THE Sistema_Negociacion SHALL mostrar el historial de mensajes de la negociación en formato de chat (burbujas de mensaje con emisor, texto y fecha)

### Requisito 6: Selector de acción predefinida en la vista de negociaciones

**Historia de Usuario:** Como usuario, quiero poder seleccionar una acción predefinida (tipo de mensaje) al negociar desde `/negociaciones`, para indicar claramente mi intención (contraoferta, aceptación condicional, consulta, etc.).

#### Criterios de Aceptación

1. THE Sistema_Negociacion SHALL mostrar un selector de tipo de acción predefinida (basado en los tipos distintos del modelo PredefinedMessage) en la interfaz de mensajes de cada negociación activa
2. WHEN el usuario selecciona un tipo de acción, THE Sistema_Negociacion SHALL filtrar los mensajes predefinidos disponibles según el tipo seleccionado
3. WHEN el usuario envía un mensaje con una acción seleccionada, THE Sistema_Negociacion SHALL registrar tanto el mensaje como el tipo de acción asociado

### Requisito 7: Aprobación bilateral desde la vista de negociaciones con mensajes

**Historia de Usuario:** Como usuario (emisor o receptor), quiero poder aprobar o rechazar una negociación directamente desde la interfaz de chat en `/negociaciones`, para completar el flujo de negociación de forma integrada.

#### Criterios de Aceptación

1. WHILE una negociación está en estado "Inicial" o "contraoferta" y el usuario es el receptor, THE Sistema_Negociacion SHALL mostrar botones de "Aceptar" y "Rechazar" junto a la interfaz de mensajes
2. WHILE una negociación está en estado "aceptado" y el usuario no ha confirmado aún, THE Sistema_Negociacion SHALL mostrar un botón de "Aprobar intercambio" junto a la interfaz de mensajes
3. WHEN ambos usuarios (emisor y receptor) han aprobado la negociación, THE Sistema_Negociacion SHALL proceder al flujo de pago existente
4. WHEN el receptor rechaza la negociación desde la interfaz de mensajes, THE Sistema_Negociacion SHALL cambiar el estado a "rechazado" y deshabilitar el envío de nuevos mensajes
