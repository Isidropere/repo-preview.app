# Manual de Administrador — CambialóRD

## 1. Acceso al Panel

1. Inicia sesión con una cuenta que tenga el campo `isAdmin = true` en la base de datos.
2. Accede al panel desde la URL `/admin` o desde el menú de administración.
3. El panel está protegido por el middleware `admin` — usuarios sin permisos recibirán error 403.

---

## 2. Panel Principal

Al acceder a `/admin`, verás un panel con pestañas (tabs) que organizan toda la información:

- Compras
- Ventas
- Intercambios
- Intención de compra
- Intención de intercambio

### 2.1 Filtros disponibles
- Pestaña activa (tab)
- Estatus de la transacción
- Búsqueda por texto (nombre, email, ID)

---

## 3. Gestión de Compras

### 3.1 Listado de compras
- Ruta: `/admin/compras`
- Muestra todas las compras paginadas (20 por página).
- Filtros: estatus (pendiente, aprobado, rechazado, enviado, entregado, cancelado) y búsqueda.

### 3.2 Detalle de compra
- Ruta: `/admin/compras/{id}`
- Información completa:
  - Datos del comprador
  - Items comprados con imágenes
  - Tarjeta utilizada
  - Proveedor de pago
  - Dirección de envío (provincia y municipio)
  - Historial de trazabilidad (cambios de estado con fecha, admin responsable y notas)

### 3.3 Actualizar estado de compra
1. En el detalle de la compra, selecciona el nuevo estado.
2. Opcionalmente agrega una nota explicativa (máximo 500 caracteres).
3. Haz clic en "Actualizar estado".
4. El cambio queda registrado en la trazabilidad con tu usuario como responsable.

**Estados disponibles:**
| Estado | Descripción |
|---|---|
| pendiente | Pago recibido, pendiente de procesamiento |
| aprobado | Pago verificado y aprobado |
| rechazado | Pago rechazado por el procesador |
| enviado | Pedido despachado al cliente |
| entregado | Pedido recibido por el cliente |
| cancelado | Pedido cancelado |

---

## 4. Gestión de Ventas

### 4.1 Detalle de venta
- Ruta: `/admin/ventas/{id}`
- Muestra la misma información que una compra pero enfocada en el vendedor.
- Incluye datos del vendedor, categoría del producto y trazabilidad.

---

## 5. Gestión de Intercambios

### 5.1 Detalle de intercambio
- Ruta: `/admin/intercambios/{id}`
- Información:
  - Artículo solicitado con imágenes
  - Usuario emisor (quien propone)
  - Usuario receptor (dueño del artículo)
  - Estado actual de la negociación

### 5.2 Actualizar estado de intercambio
- Selecciona el nuevo estado y opcionalmente agrega una nota.
- Estados: Inicial, pendiente, contraoferta, aceptado, completado, rechazado, cancelado.

---

## 6. Mensajes Predefinidos

### 6.1 Acceso
- Ruta: `/admin/mensajes-predefinidos`
- Los administradores normales solo pueden ver los mensajes.
- Solo el Super Admin puede crear, editar, eliminar y activar/desactivar mensajes.

### 6.2 ¿Qué son?
- Son plantillas de mensajes que los usuarios pueden usar durante negociaciones.
- Cada mensaje tiene: título, contenido, rol (comprador/vendedor) y tipo de acción.

---

## 7. Gestión de Usuarios

### 7.1 Listado
- Ruta: `/usuarios`
- Muestra todos los usuarios paginados con su tipo de usuario.

### 7.2 Ver detalle
- Ruta: `/usuarios/{id}`
- Información completa del usuario: datos personales, tipo, direcciones.

### 7.3 Editar usuario
- Ruta: `/usuarios/{id}/edit`
- Puedes modificar: nombres, apellidos, teléfono y tipo de usuario.

### 7.4 Activar/Desactivar usuario
- Ruta: `PUT /usuarios/{id}/toggle-status`
- Alterna el estatus del usuario entre activo (1) e inactivo (0).
- Un usuario inactivo no puede acceder a la plataforma.

### 7.5 Desactivar usuario (desde resource)
- La acción "Eliminar" no borra el usuario, sino que lo desactiva (estatus = 0).

---

## 8. Categorías de Items

- Los administradores con rol 3 pueden gestionar categorías.
- Crear, editar y eliminar categorías de productos.
- Las categorías se usan para organizar los artículos en la plataforma.

---

## 9. Buenas Prácticas

- Revisa las compras pendientes diariamente.
- Agrega notas descriptivas al cambiar estados para mantener trazabilidad clara.
- Monitorea los intercambios activos para detectar problemas.
- Verifica que los usuarios reportados tengan su estatus actualizado.
- No compartas tus credenciales de administrador.

---

## 10. Resolución de Problemas

**Un pago aparece como "aprobado" pero el cliente dice que no recibió confirmación:**
- Verifica en la trazabilidad si el estado cambió correctamente.
- Revisa los logs del sistema para errores post-cobro.
- El sistema tiene reembolso automático si falla el registro en BD.

**Un usuario no puede publicar artículos:**
- Verifica que su email esté verificado.
- Verifica que su estatus sea activo (1).
- Verifica que tenga el tipo de usuario correcto.

**Un intercambio está atascado:**
- Puedes cambiar el estado manualmente desde el panel de intercambios.
- Contacta a ambas partes si es necesario.
