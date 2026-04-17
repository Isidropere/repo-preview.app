# Manual de Administrador — CambialóRD

> Guía para usuarios con rol Admin (`isAdmin = 1`)

---

## 1. Acceso al Panel

- URL: `/admin`
- Requiere: cuenta con `isAdmin = 1`
- El panel muestra tabs: Compras, Ventas, Intercambios

---

## 2. Gestión de Órdenes de Compra

### Ver todas las órdenes
- Ve a `/admin/compras`
- Filtra por estado: pendiente, aprobado, enviado, entregado, cancelado
- Haz click en una orden para ver el detalle completo

### Actualizar estado de una orden
1. Abre el detalle de la orden
2. Selecciona el nuevo estado en el dropdown
3. Haz click en **Actualizar estado**
4. El cambio queda registrado en la trazabilidad con fecha y hora

Flujo de estados:
```
aprobado → enviado → entregado
         → cancelado
```

### Agregar tracking de envío
1. En el detalle de la orden, busca la sección de rastreo
2. Ingresa el **código de rastreo** (ej: número de guía)
3. Ingresa la **URL de rastreo** completa
4. Guarda los cambios
5. El comprador verá el enlace "Rastrear envío" en su historial

---

## 3. Moderación de Imágenes

- URL: `/admin/imagenes`
- Muestra todas las imágenes de artículos y fotos de perfil pendientes de aprobación

### Aprobar una imagen
- Haz click en **✓ Aprobar** junto a la imagen
- La imagen quedará visible públicamente

### Rechazar una imagen
- Haz click en **✗ Rechazar**
- Ingresa el motivo del rechazo
- El usuario verá el motivo en su panel

### Aprobar todas
- Usa el botón **✓ Aprobar todas** para aprobar en lote
- Aplica por separado para imágenes de artículos y fotos de perfil

---

## 4. Gestión de Usuarios

- URL: `/usuarios` (dentro del panel admin)
- Lista todos los usuarios con paginación

### Ver detalle de un usuario
- Haz click en el usuario para ver: datos personales, tipo, estado, direcciones

### Activar/Desactivar usuario
- Usa el toggle de estado para activar o desactivar una cuenta
- Un usuario desactivado no puede iniciar sesión

### Editar usuario
- Modifica datos básicos del usuario
- No puedes cambiar `isAdmin` ni `isSuperAdmin` desde aquí (solo SuperAdmin)

---

## 5. Gestión de Intercambios

- Ve a `/admin/intercambios/{id}` para ver el detalle de un intercambio
- Puedes actualizar el estado de un intercambio
- Verás los participantes, artículos involucrados y mensajes

---

## 6. Mensajes Predefinidos (Solo lectura)

- URL: `/admin/mensajes-predefinidos`
- Visualiza los mensajes predefinidos disponibles para negociaciones
- Para crear, editar o eliminar mensajes, se requiere rol SuperAdmin

---

## 7. Notas Importantes

- Los cambios de estado de órdenes son irreversibles (no hay "deshacer")
- Siempre verifica el tracking antes de marcar como "enviado"
- Las imágenes rechazadas no se eliminan, solo se ocultan del catálogo público
- No puedes modificar precios ni datos de artículos de otros usuarios
