# 📱 Análisis de la App Móvil y Plan de Equivalencia con la Web (Excluyendo Admin/SuperAdmin)

Este documento contiene un análisis técnico estructurado de la aplicación móvil **`cambialo_app`** (desarrollada en Flutter) en comparación con la plataforma web principal. El objetivo es identificar las funcionalidades del lado del cliente que ya están implementadas y listar las brechas de desarrollo pendientes para lograr una paridad total de experiencia para el usuario común.

---

## 🔍 1. Resumen de la Aplicación Móvil
La aplicación móvil está construida en **Flutter** (Dart), lo que le permite compilar de manera nativa y fluida para **Android** e **iOS**. Está diseñada para interactuar con el backend de Laravel a través de rutas de API REST seguras (`routes/api.php`) utilizando la autenticación por token de **Laravel Sanctum**.

* **Enfoque de la App:** Experiencia del usuario final (Cliente) para realizar compras, ventas, trueques, y configurar perfiles.
* **Exclusión de Admin:** Tal como se solicitó, **no abarca ningún módulo administrativo, contabilidad del ERP, auditorías de inventario ni control de caja.** Estas funciones residen exclusivamente en el panel web de administración.

---

## ⚖️ 2. Comparativa de Funcionalidades: Web vs. Móvil

A continuación, se presenta un cuadro detallado que compara las pantallas y capacidades del usuario final en la web frente a su estado actual en la aplicación de Flutter:

| Módulo / Pantalla | 🌐 Sitio Web (Cliente) | 📱 App Móvil (Flutter) | 📢 Estado / Brecha en la App |
| :--- | :--- | :--- | :--- |
| **Inicio (Home)** | Carruseles dinámicos de Ventas e Intercambios, buscador premium y categorías. | Equivalente visual. Carga los carruseles desde la API y permite búsquedas. | **Completado.** (Con fallbacks de imagen). |
| **Catálogo / Búsqueda** | Listado de productos con buscador y filtros avanzados. | Muestra resultados en lista basándose en el query o categoría. | **Completado básico.** (Falta ordenamiento avanzado). |
| **Detalle de Artículo** | Galería de fotos, geolocalización del dueño, especificaciones, botones de Compra y Trueque. | Carga la galería mediante `PageView` y renderiza la información del producto. | **Parcial.** Los botones principales no ejecutan acciones completas de flujo. |
| **Registro e Inicio Sesión** | Login/Registro tradicional y por redes (Google, Facebook). | Pantallas dedicadas de Login y Registro por correo. Guarda el token en llavero seguro. | **Completado.** Autenticación Sanctum completamente integrada. (No tiene OAuth social). |
| **Carrito de Compras** | Visualización de productos agregados, edición de cantidad, descuentos y subtotal. | Pantalla de carrito que lee y elimina productos consumiendo `/api/carrito`. | **Parcial.** Permite ver y eliminar; los botones de "Vaciar" y "Pagar" son placeholders. |
| **Perfil del Usuario** | Dashboard con enlaces para crear Hojas de Vida, publicar artículos, gestionar talentos. | Pantalla "Mi cuenta" con grid de 8 opciones visuales idéntico a la web. | **Placeholder.** El menú visual está hecho, pero casi ningún botón tiene navegación real. |
| **Historial de Operaciones** | Historial clasificado de compras de artículos, propuestas de trueques y solicitudes de servicios. | Pantalla organizada por pestañas que consulta `/api/historial`. | **Completado.** Muestra el listado en crudo del historial directo del usuario. |

---

## 🚧 3. Funcionalidades Faltantes Clave en la App (Brechas a Desarrollar)

Para lograr que la aplicación móvil sea 100% funcional para los clientes de CambialóRD, es indispensable desarrollar las siguientes pantallas y lógicas que actualmente están en modo **placeholder** (onTap vacío):

### 🔄 A. Sistema de Negociaciones e Intercambios (Core Trueques)
* **En la Web:** Un usuario puede proponer un trueque, seleccionar qué artículos suyos ofrece a cambio, añadir dinero adicional (Intercambio + Venta), elegir la provincia/municipio del delivery y entrar a una sala de contraoferta/chat.
* **En la App:** El botón `"Proponer intercambio"` en [item_detail_screen.dart](file:///c:/Users/iperez/Desktop/datos/repos/copi/CB.app/cambialo_app/lib/screens/item_detail_screen.dart) no tiene funcionalidad.
  * *Falta:* Crear la pantalla de selección de artículos propios, formulario de dinero extra y envío de la propuesta a `/api/negociaciones` (creando también las rutas correspondientes en Laravel si faltaran).

### 📦 B. Publicación y Edición de Productos
* **En la Web:** Formulario multi-pasos para registrar artículos con peso, volumen (crucial para delivery), estado físico, precio de venta, modalidad (venta, trueque o ambos) y carga de imágenes integrando la API de ImgBB.
* **En la App:** Las opciones `"Agregar productos"` y `"Gestionar productos"` en la pantalla de cuenta son placeholders.
  * *Falta:* Formulario de subida de productos y lógica de carga de imágenes en segundo plano desde el carrete del móvil.

### 💼 C. Bolsa de Talentos y Servicios
* **En la Web:** Los profesionales registran su currículum o perfil técnico (`Hojas de Vida`), publican sus tarifas por hora o proyecto, y los clientes les envían solicitudes de contratación previa autorización.
* **En la App:** Las opciones `"Agregar un nuevo talento"` y `"Administrar tus talentos"` en el perfil de cuenta están vacías.
  * *Falta:* Formulario de creación de perfil profesional e integración del flujo de solicitud de servicios.

### 💳 D. Checkout y Pasarela de Pagos (Stripe / CardNet)
* **En la Web:** Módulo integrado que calcula dinámicamente las tarifas de delivery de CambialóRD según la provincia y distrito de envío, procesa cupones de descuento y ejecuta el cobro con tarjeta mediante Stripe o la pasarela local CardNet.
* **En la App:** El botón `"Proceder al pago"` en la pantalla de carrito no hace nada.
  * *Falta:* Módulo de ingreso de dirección de entrega (Provincias/Municipios), cálculo dinámico de envío, e integración del WebView de pagos o pasarela nativa para CardNet/Stripe.

### 💬 E. Chat y Mensajería Interactiva
* **En la Web:** Chat en tiempo real con WebSockets (a través de Laravel Reverb) para acordar detalles de trueques o hacer preguntas sobre productos.
* **En la App:** No existe ningún módulo de chat en la versión móvil.
  * *Falta:* Implementar una pantalla de conversaciones y un cliente de WebSockets en Flutter para recibir mensajes al instante.

---

## 🛠️ 4. Conclusiones y Plan de Acción Recomendado

1. **Prioridad 1 (Checkout y Direcciones):** Completar el flujo de compras de la app integrando el módulo de direcciones y el procesamiento de pagos. Esto permitirá que la app empiece a monetizar inmediatamente las ventas tradicionales.
2. **Prioridad 2 (Formulario de Publicación):** Permitir a los usuarios subir artículos directamente tomándoles fotos con la cámara del celular. Esta es la característica más atractiva de una aplicación móvil frente a la web.
3. **Prioridad 3 (Flujo de Negociaciones):** Desarrollar la pantalla intermedia para elegir artículos propios y proponer trueques.
4. **Prioridad 4 (Notificaciones y Chat):** Integrar notificaciones push en el móvil para alertar al cliente inmediatamente cuando su propuesta de intercambio sea aceptada o reciba un mensaje.
