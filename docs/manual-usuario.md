# Manual de Usuario — CambialóRD

> Guía básica para usuarios registrados de la plataforma

---

## 1. Registro e Inicio de Sesión

### Crear una cuenta
1. Ve a `/registro`
2. Completa: nombres, apellidos, teléfono, correo, contraseña (mínimo 8 caracteres)
3. Selecciona tu tipo de usuario (Persona o Empresa)
4. Acepta los términos y condiciones
5. Opcionalmente sube una foto de perfil
6. Haz click en **Crear mi cuenta**

También puedes registrarte con **Google** usando el botón correspondiente.

### Iniciar sesión
1. Ve a `/login`
2. Ingresa tu correo y contraseña
3. Usa el icono de ojo para mostrar/ocultar la contraseña
4. Marca "Recordarme" si quieres mantener la sesión activa

### Cambiar contraseña
1. Ve a `/contraseña`
2. Ingresa tu contraseña actual
3. Ingresa y confirma la nueva contraseña
4. Haz click en **Cambiar contraseña**

---

## 2. Explorar el Catálogo

### Página de inicio
- Muestra categorías populares, productos de intercambio y productos en venta
- Usa el buscador del header para encontrar artículos específicos

### Categorías
- Navega por categorías desde el menú o la página de inicio
- Filtra por precio, nombre o fecha
- Busca dentro de una categoría específica

### Ver detalle de un producto
- Haz click en cualquier artículo para ver su detalle completo
- Verás: imágenes, precio, stock disponible, descripción, condición
- Si el artículo está **Agotado**, no podrás comprarlo ni intercambiarlo

---

## 3. Comprar un Producto

### Agregar al carrito
1. En el detalle del producto, selecciona la cantidad
2. Haz click en **Agregar al carrito**
3. No puedes agregar tus propios artículos
4. No puedes mezclar productos físicos y servicios (talentos) en el mismo carrito

### Revisar el carrito
- Ve a `/carrito/carrito`
- Selecciona/deselecciona los artículos que quieres pagar
- El total se calcula automáticamente sobre los artículos seleccionados

### Checkout y pago
1. Ve a `/carrito/checkout`
2. Selecciona una tarjeta guardada o agrega una nueva
3. Ingresa el CVV de tu tarjeta
4. Verifica el resumen del pedido y el costo de envío
5. Haz click en **Confirmar y Pagar**
6. Si el pago es exitoso, recibirás confirmación y podrás ver tu orden en el historial

### Gestionar tarjetas
- En el checkout puedes agregar nuevas tarjetas
- Haz click en el icono de basura para eliminar una tarjeta (te pedirá confirmación)

---

## 4. Intercambiar un Artículo

### Proponer un intercambio
1. Ve al detalle de un artículo con modalidad "Intercambio"
2. Haz click en **Intercambio**
3. Selecciona un paquete de tus artículos para ofrecer
4. Escribe un mensaje inicial
5. Opcionalmente indica un monto de oferta en dinero
6. Haz click en **Enviar propuesta**

### Seguimiento de negociaciones
- Ve a `/historial` → pestaña **Intercambios**
- Verás todas tus negociaciones activas e históricas
- Estados posibles: Inicial, Contraoferta, Aceptado, Rechazado, Cancelado, Completado

### Cancelar una negociación
- Solo el emisor puede cancelar en estados Inicial o Contraoferta
- Una vez aceptada, no se puede cancelar

---

## 5. Publicar un Producto

1. Ve a **Mis Productos** → **Nuevo Producto**
2. Paso 1 — Información: nombre, precio, cantidad, categoría
3. Paso 2 — Imágenes: sube imagen/video principal y hasta 4 adicionales
4. Paso 3 — Detalles: descripción (máx. 250 caracteres), condición, modalidad, dimensiones
5. Haz click en **Publicar producto**

Las imágenes pasan por moderación antes de aparecer públicamente.

---

## 6. Publicar un Talento (Servicio)

1. Ve a **Mis Talentos** → **Nuevo talento**
2. Completa: nombre, precio, cantidad de cupos, modalidad, descripción
3. Sube imagen/video principal
4. Haz click en **Publicar talento**
5. Se abrirá un modal de pago — selecciona tu tarjeta e ingresa el CVV
6. El costo de publicación es: `monto_base × cantidad_de_cupos`
7. Tras el pago exitoso, el talento queda publicado

---

## 7. Historial

Ve a `/historial` para ver:
- **Compras**: órdenes realizadas con estado, artículos y seguimiento de envío
- **Ventas**: artículos tuyos que fueron comprados
- **Intercambios**: negociaciones en las que participas

---

## 8. Mis Productos y Talentos

- **Mis Productos** (`/mis-productos`): lista, edita o elimina tus artículos
- **Mis Talentos** (`/talentos`): lista, edita o elimina tus servicios
- Haz click en la imagen para ampliarla
- Usa los iconos de acción: ver, editar, eliminar

---

## 9. Preguntas Frecuentes

**¿Puedo comprar mis propios artículos?**
No. El sistema lo bloquea automáticamente.

**¿Qué pasa si el pago se procesa pero hay un error?**
El sistema intenta un reembolso automático. Si falla, contacta soporte con el código de aprobación que aparece en pantalla.

**¿Cuántos cupos puedo publicar para un talento?**
Hasta 999. Cada compra o intercambio aceptado consume 1 cupo.

**¿Puedo mezclar productos y talentos en el carrito?**
No. Debes hacer compras separadas.
