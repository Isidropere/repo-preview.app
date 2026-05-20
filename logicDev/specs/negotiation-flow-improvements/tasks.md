# Plan de Implementación: Mejoras al Flujo de Negociación

## Visión General

Implementación incremental en tres bloques: (1) correcciones del modal de negociación del carrito, (2) pestaña de intención de compra en admin, (3) chat con mensajes predefinidos y aprobación bilateral en negociaciones. Se usan estilos inline o clases CSS existentes. No se toca `stats.blade.php`.

## Tareas

- [x] 1. Correcciones del modal de negociación del carrito
  - [x] 1.1 Corregir la URL de cálculo de delivery en el JavaScript del modal
    - En `resources/views/carrito/carrito.blade.php` (o el partial del modal de negociación), cambiar la URL de fetch de `/api/delivery/calcular` a `/delivery/calcular`
    - Verificar que el parámetro `pueblo` se envíe correctamente con `encodeURIComponent`
    - Manejar errores de la respuesta: si falla, mostrar mensaje inline "No se pudo calcular el envío" sin bloquear el formulario
    - _Requisitos: 1.1, 1.2, 1.3_

  - [x] 1.2 Agregar campo oculto `accionInput` y sincronizar con el select de AccionPredefinido
    - Agregar `<input type="hidden" name="accionInput" id="accionInput">` dentro del formulario del modal
    - Agregar event listener `change` al select de AccionPredefinido que actualice `accionInput.value`
    - Verificar que el valor se incluya en el POST al enviar el formulario
    - _Requisitos: 2.1, 2.2, 2.3_

  - [x] 1.3 Reemplazar `alert()` por validación visual inline en el modal
    - Crear funciones JS `validarCampo(campo, mensaje)` y `limpiarError(campo)` usando estilos inline (`border: 2px solid #ef4444` y `<span>` de error)
    - Reemplazar cada `alert()` de validación por llamadas a `validarCampo()`
    - Agregar listeners `input`/`change` en cada campo para llamar `limpiarError()` al corregir
    - _Requisitos: 3.1, 3.2, 3.3_

  - [ ]* 1.4 Escribir test unitario para la corrección de la URL de delivery
    - Verificar que el JavaScript del modal usa `/delivery/calcular` y no `/api/delivery/calcular`
    - _Requisitos: 1.1_

  - [ ]* 1.5 Escribir test de propiedad para el cálculo de delivery
    - **Propiedad 1: Cálculo de delivery retorna costo válido para municipios conocidos**
    - Generar 100 combinaciones aleatorias de municipios registrados en zonas activas y valores de artículo ≥ 0
    - Verificar que `DeliveryService::calcular()` retorna `success: true` con `costo_envio_total` ≥ 0 y desglose completo
    - **Valida: Requisitos 1.2**

- [x] 2. Checkpoint — Verificar correcciones del carrito
  - Asegurar que todos los tests pasan, preguntar al usuario si surgen dudas.

- [x] 3. Pestaña de intención de compra en admin
  - [x] 3.1 Verificar y completar el partial `tabla-intencion-compra.blade.php`
    - Verificar que el partial existe en `resources/views/admin/partials/tabla-intencion-compra.blade.php`; si no existe, crearlo
    - La tabla debe mostrar las columnas: usuario, artículo, cantidad, precio unitario y total
    - Usar `$item->carrito->usuario->nombres`, `$item->item->item`, `$item->cantidad`, `$item->item->valor`, y `$item->item->valor * $item->cantidad`
    - Incluir mensaje "No hay datos" cuando no haya resultados
    - Usar estilos inline o clases CSS existentes (no tocar `stats.blade.php`)
    - _Requisitos: 4.1, 4.2, 4.3_

  - [x] 3.2 Verificar la query de filtrado y paginación en `AdminComprasService`
    - Confirmar que `queryIntencionCompra()` filtra por `tipo_trans` IN (1, 3)
    - Confirmar que la búsqueda filtra por nombre del artículo, nombre del usuario o email
    - Confirmar paginación a 20 registros por página
    - Si falta alguna funcionalidad, implementarla
    - _Requisitos: 4.3, 4.4, 4.5_

  - [ ]* 3.3 Escribir test de propiedad para la query de intención de compra
    - **Propiedad 2: Query de intención de compra filtra correctamente por tipo de transacción y búsqueda**
    - Generar 100 conjuntos de items con distintos `tipo_trans` y términos de búsqueda aleatorios
    - Verificar que solo se retornan items con `tipo_trans` IN (1, 3) y que los resultados coinciden con el término de búsqueda
    - **Valida: Requisitos 4.3, 4.4**

  - [ ]* 3.4 Escribir test unitario para la pestaña de intención de compra
    - Verificar que GET `/admin?tab=intencion_compra` retorna 200 y contiene las columnas esperadas
    - Verificar que la paginación retorna máximo 20 registros
    - _Requisitos: 4.1, 4.2, 4.5_

- [x] 4. Checkpoint — Verificar pestaña admin
  - Asegurar que todos los tests pasan, preguntar al usuario si surgen dudas.

- [x] 5. Chat con mensajes predefinidos en negociaciones
  - [x] 5.1 Crear endpoint POST `/negociaciones/{id}/mensaje` en `NegociacionController`
    - Agregar ruta en el grupo `negociaciones` en `routes/web.php`
    - Implementar método `enviarMensaje()` en `NegociacionController` que reciba `mensaje` y `tipo_accion`
    - Llamar a `NegociacionService::crearMensaje()` para persistir el mensaje
    - Retornar respuesta JSON con el mensaje creado
    - _Requisitos: 5.4, 6.3_

  - [ ]* 5.2 Escribir test de propiedad para round-trip de persistencia de mensajes
    - **Propiedad 4: Round-trip de persistencia de mensajes de negociación**
    - Generar 100 mensajes válidos (no vacíos) con pares de usuarios y items aleatorios
    - Almacenar vía `crearMensaje()` y recuperar vía `obtenerMensajes()`; verificar que texto, IDs de emisor/receptor e ID de item coinciden
    - **Valida: Requisitos 5.4, 6.3**

  - [x] 5.3 Agregar sección de chat colapsable en `tarjeta-negociacion.blade.php`
    - Dentro de cada tarjeta con estado "Inicial", "aceptado" o "contraoferta", agregar bloque colapsable "💬 Mensajes"
    - Implementar carga de historial de mensajes vía AJAX (`GET /carrito/negociaciones/mensajes/{emisor}/{receptor}`)
    - Renderizar mensajes en formato de burbujas: alineadas a la derecha si es propio, izquierda si es del otro
    - Mostrar emisor, texto y fecha en cada burbuja
    - Usar estilos inline para las burbujas de chat
    - _Requisitos: 5.1, 5.5_

  - [x] 5.4 Implementar selector de acción predefinida y filtrado de mensajes predefinidos
    - Agregar `<select>` con los tipos distintos de `PredefinedMessage` (campo `tipo`) en la sección de chat
    - Al seleccionar un tipo, filtrar mensajes predefinidos por tipo y por rol del usuario (emisor, receptor, general)
    - Mostrar los mensajes filtrados como botones o lista seleccionable
    - Al hacer clic en un mensaje predefinido, mostrar vista previa en un área de preview
    - Botón "Enviar" que hace POST a `/negociaciones/{id}/mensaje` con el mensaje y tipo_accion
    - _Requisitos: 5.2, 5.3, 6.1, 6.2_

  - [ ]* 5.5 Escribir test de propiedad para filtrado de mensajes predefinidos
    - **Propiedad 3: Filtrado de mensajes predefinidos por rol y tipo**
    - Generar 100 combinaciones de rol (emisor, receptor) y tipo de acción
    - Verificar que los mensajes retornados tienen `rol` igual al del usuario o "general", y `tipo` coincide con el seleccionado
    - **Valida: Requisitos 5.2, 6.2**

  - [x] 5.6 Implementar botones de Aceptar/Rechazar/Aprobar junto al chat
    - Si estado ∈ {Inicial, contraoferta} y usuario es receptor → mostrar botones "Aceptar" y "Rechazar"
    - Si estado = "aceptado" y usuario no ha confirmado → mostrar botón "Aprobar intercambio"
    - Deshabilitar botón después del primer clic para evitar doble envío
    - Al rechazar: cambiar estado a "rechazado" y deshabilitar envío de mensajes
    - Al aceptar ambos: proceder al flujo de pago existente
    - _Requisitos: 7.1, 7.2, 7.3, 7.4_

  - [ ]* 5.7 Escribir test de propiedad para visibilidad de acciones según estado y rol
    - **Propiedad 5: Visibilidad de acciones según estado de negociación y rol**
    - Generar 100 combinaciones de estados y roles; verificar que las acciones visibles cumplen las reglas definidas
    - **Valida: Requisitos 5.1, 7.1, 7.2**

  - [ ]* 5.8 Escribir test de propiedad para transiciones de estado
    - **Propiedad 6: Transiciones de estado de negociación preservan invariantes**
    - Generar 100 secuencias de transiciones; verificar que aceptar/rechazar solo es posible desde estados válidos y por el rol correcto, y que después de rechazar no se pueden enviar mensajes
    - **Valida: Requisitos 7.3, 7.4**

- [x] 6. Integración y cableado final
  - [x] 6.1 Conectar todos los componentes y verificar flujo completo
    - Verificar que el modal del carrito envía correctamente la negociación con accionInput y delivery calculado
    - Verificar que la pestaña admin muestra datos de intención de compra
    - Verificar que el chat de negociaciones carga mensajes, filtra predefinidos y permite aceptar/rechazar/aprobar
    - Manejar errores de red y estados inconsistentes según la tabla de manejo de errores del diseño
    - _Requisitos: 1.1-1.3, 2.1-2.3, 3.1-3.3, 4.1-4.5, 5.1-5.5, 6.1-6.3, 7.1-7.4_

  - [ ]* 6.2 Escribir tests de integración del flujo completo
    - Test: enviar negociación desde carrito con delivery y acción predefinida
    - Test: cargar pestaña intención de compra con filtros y paginación
    - Test: flujo completo de chat → aceptar → aprobar bilateral
    - _Requisitos: 1.1-7.4_

- [x] 7. Checkpoint final — Asegurar que todos los tests pasan
  - Asegurar que todos los tests pasan, preguntar al usuario si surgen dudas.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada tarea referencia requisitos específicos para trazabilidad
- Los checkpoints aseguran validación incremental
- Los tests de propiedades validan propiedades universales de correctitud
- Los tests unitarios validan ejemplos específicos y edge cases
- No se modifica `stats.blade.php` en ninguna tarea
- Se usan estilos inline o clases CSS existentes en todas las vistas
