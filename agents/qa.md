# AGENTE QA - PHP

Eres un ingeniero de QA experto en pruebas de software para PHP.

## Objetivo
Validar que el código cumpla con calidad, seguridad y funcionalidad.

## Tipos de pruebas

### 1. Funcionales
- Verificar que endpoints funcionen correctamente
- Validar inputs y outputs

### 2. Validación de datos
- Campos obligatorios
- Tipos de datos correctos
- Casos inválidos

### 3. Seguridad
- SQL Injection
- XSS
- Validación de inputs

### 4. Errores
- Manejo de excepciones
- Respuestas HTTP correctas

## Reglas
- Siempre probar casos positivos y negativos
- Detectar edge cases
- No asumir datos correctos

## Formato de respuesta

### Resultado QA

Estado: ✅ / ❌

Errores encontrados:
- ...

Recomendaciones:
- ...

---

# RESULTADO QA — Auditoría completa de endpoints PHP
Fecha: 2026-03-17

Estado general: ❌ Se encontraron problemas críticos

---

## 🔴 ERRORES CRÍTICOS (deben corregirse)

### E1. API de delivery sin autenticación ni autorización
**Archivo:** `routes/api.php`
**Severidad:** CRÍTICA
El endpoint `POST /api/delivery/config/{clave}` permite a CUALQUIER persona modificar la configuración de porcentajes de ganancia del negocio sin autenticación. Esto es una vulnerabilidad grave — un atacante puede cambiar los porcentajes de plataforma, seguro y manejo a 0% o 100%.
```
Route::post('/config/{clave}', [DeliveryZonaController::class, 'updateConfig']);
// ← Sin middleware auth ni admin
```
**Fix:** Proteger con `middleware(['auth', 'superadmin'])`.

### E2. Rutas de usuario sin middleware auth
**Archivo:** `routes/web.php`
**Severidad:** CRÍTICA
Varias rutas de gestión de usuario están fuera de cualquier grupo `auth`:
```php
Route::controller(UserController::class)->group(function () {
    Route::resource('usuarios', UserController::class)->except(['create', 'store']);
    Route::put('/usuarios/{id}/toggle-status', 'toggleStatus');
    Route::get('/mi-perfil', 'profile');
    Route::put('/actualizar-perfil', 'updateProfile');
});
Route::resource('direcciones', DireccionesController::class);
Route::post('/direccion/predeterminada/{id}', ...);
```
Estas rutas permiten acceso sin autenticación a perfiles, direcciones y toggle de estatus de usuarios.
**Fix:** Envolver en `Route::middleware(['auth'])->group(...)`.

### E3. Bug en ItemController::show() — acceso a propiedad de query builder
**Archivo:** `app/Http/Controllers/ItemController.php` línea ~885
**Severidad:** ALTA
```php
$items = Item::where('id_categoria_item', $id); // ← query builder, NO modelo
if(auth()->id() != $items->id_user) { // ← ERROR: $items es Builder, no tiene id_user
```
Esto lanza una excepción en cada request a `show()`. El código intenta registrar una vista pero accede a `->id_user` en un query builder en vez de un modelo.

### E4. categoriasController referencia modelo inexistente
**Archivo:** `app/Http/Controllers/categoriasController.php`
**Severidad:** ALTA
Importa `App\Models\Producto` que no existe en el proyecto. Cada método lanza `Class not found`. Este controlador es código muerto completo.
**Fix:** Eliminar el archivo.

### E5. NegociacionController::store() pasa $request->all() al servicio
**Archivo:** `app/Http/Controllers/NegociacionController.php` línea ~35
**Severidad:** MEDIA-ALTA
```php
$resultado = $this->negociacionService->crear(auth()->id(), $request->all());
```
Aunque hay validación arriba, `$request->all()` incluye campos no validados. Si el servicio usa esos datos directamente en `create()`, permite mass assignment.
**Fix:** Usar `$request->validated()` en vez de `$request->all()`.

### E6. NegociacionController::storeContraoferta() pasa $request->all()
**Archivo:** `app/Http/Controllers/NegociacionController.php` línea ~72
**Severidad:** MEDIA-ALTA
Mismo problema que E5:
```php
$resultado = $this->negociacionService->contraoferta(auth()->id(), $id, $request->all());
```
**Fix:** Usar `$request->validated()`.

---

## 🟠 PROBLEMAS DE SEGURIDAD

### S1. IDOR en múltiples controladores CRUD
**Archivos:** `RatingController`, `ItemIntencionCompraController`, `OfertaController`, `ImagenItemController`, `CategoriaItemController`, `NotaController`, `NotaDetalleController`, `FacturaTransporteController`, `DeliveryController`, `MiembroController`, `PlanController`, `ProveedorPagoController`
**Severidad:** MEDIA-ALTA
Ninguno de estos controladores verifica que el usuario autenticado tenga permiso para ver/editar/eliminar el recurso. Cualquier usuario autenticado puede modificar o eliminar registros de otros usuarios simplemente cambiando el ID en la URL.
**Fix:** Agregar verificación de propiedad o middleware de autorización (Policies).

### S2. Rutas CRUD sin protección de rol
**Archivo:** `routes/web.php`
**Severidad:** MEDIA
Los resource routes de `CategoriaItemController` solo protegen store/update/destroy con `role:3`, pero los endpoints de escritura de otros controladores CRUD (Delivery, Plan, ProveedorPago, Nota, etc.) no tienen ningún middleware de rol. Cualquier usuario autenticado puede crear/editar/eliminar deliveries, planes, proveedores de pago, etc.

### S3. NotificationController::store() — cualquier usuario puede enviar notificaciones a cualquier otro
**Archivo:** `app/Http/Controllers/NotificationController.php`
**Severidad:** MEDIA
El endpoint acepta `user_id` arbitrario y envía notificaciones (incluso emails) a cualquier usuario. No hay verificación de permisos.

### S4. RegisterController no valida profile_photo en las reglas de validación
**Archivo:** `app/Http/Controllers/RegisterController.php`
**Severidad:** MEDIA
La imagen de perfil se valida manualmente con MIME check después de crear el usuario, pero no está en las reglas de `$request->validate()`. Si alguien envía un archivo malicioso, el usuario ya fue creado en BD.
**Fix:** Agregar `'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'` a las reglas de validación.

### S5. ItemController::search() — potencial SQL injection via LIKE
**Archivo:** `app/Http/Controllers/ItemController.php`
**Severidad:** BAJA (Laravel parametriza, pero el patrón es riesgoso)
```php
$query->where('item', 'like', '%' . $request->q . '%')
```
Aunque Laravel usa prepared statements, el input no se sanitiza para caracteres especiales de LIKE (`%`, `_`). Un usuario puede manipular los resultados de búsqueda.
**Fix:** Escapar caracteres LIKE: `str_replace(['%', '_'], ['\%', '\_'], $request->q)`.

---

## 🟡 VIOLACIONES DE ESTÁNDAR (AGENT.md)

### V1. ItemController — 1847 líneas de lógica de negocio en controller
**Archivo:** `app/Http/Controllers/ItemController.php`
**Severidad:** ALTA (violación directa de "No lógica en controllers")
Es el mayor violador del estándar. Contiene:
- Lógica de creación de items con procesamiento de imágenes
- Lógica de búsqueda y filtrado
- Métodos duplicados (store/AddTalento, update/talentoupdate, guardarImagen/guardarImagenTalento)
- Agrupación de colores (groupColorsByFamily, hexToHsl)
- Gestión de inventario
**Fix:** Crear `ItemService` y extraer toda la lógica.

### V2. NotificationController::listar() — lógica de queries + logging excesivo
**Archivo:** `app/Http/Controllers/NotificationController.php`
**Severidad:** MEDIA
Tiene 15+ líneas de Log con emojis (📩, 👤, 🔍, ✅, 📭, 📨, 🚀, ❌) y lógica de queries directamente en el controller.
**Fix:** Extraer a servicio, reducir logging a lo esencial.

### V3. AdminComprasController::indexCompras() — query building en controller
**Archivo:** `app/Http/Controllers/Admin/AdminComprasController.php`
**Severidad:** MEDIA
Construye queries complejas con `where`, `whereHas`, `orWhere` directamente en el controller en vez de delegarlo al `AdminComprasService`.

### V4. Respuestas JSON inconsistentes
**Archivos:** Múltiples controladores
**Severidad:** MEDIA
Controladores que NO usan el formato estándar `{success, data, message}`:
- `CategoriaItemController::index()` → retorna modelo raw
- `CategoriaItemController::show()` → retorna modelo raw
- `CategoriaItemController::destroy()` → retorna `null, 204`
- `ImagenItemController::index()` → retorna modelo raw
- `ImagenItemController::show()` → retorna modelo raw
- `ImagenItemController::destroy()` → retorna `null, 204`
- `ItemIntencionCompraController::index/show()` → retorna modelo raw
- `ItemIntencionCompraController::destroy()` → retorna `null, 204`
- `OfertaController::index/show()` → retorna modelo raw
- `OfertaController::destroy()` → retorna `null, 204`
- `RatingController::index/show()` → retorna modelo raw
- `RatingController::destroy()` → retorna `null, 204`
- `PaqueteController::index/show()` → retorna modelo raw
- `ItemOfertaController` → mezcla formatos
- `DireccionesController::show()` → retorna modelo raw
- `NotificationController` → usa formatos variados

### V5. RegisterController — lógica de imagen en controller
**Archivo:** `app/Http/Controllers/RegisterController.php`
**Severidad:** MEDIA
~50 líneas de lógica de procesamiento de imagen directamente en el controller.
**Fix:** Extraer a un `ImageService` o similar.

### V6. PaqueteController tiene métodos CRUD duplicados
**Archivo:** `app/Http/Controllers/PaqueteController.php`
**Severidad:** BAJA
Tiene dos conjuntos de endpoints:
- CRUD estándar: `store()`, `update()`, `destroy()` (con lógica inline)
- Endpoints de servicio: `crearPaquete()`, `editarPaquete()`, `eliminarPaquete()` (delegados a PaqueteService)
Los CRUD estándar no usan el servicio y tienen lógica directa.

---

## 🔵 MEJORAS RECOMENDADAS

### M1. Agregar rate limiting a endpoints de búsqueda
Los endpoints `items/search`, `items/search_header`, `buscar` no tienen throttle. Un bot podría hacer scraping masivo.

### M2. Agregar paginación a endpoints que retornan ::all()
Controladores que usan `Model::all()` sin paginación:
- `DeliveryController::index()`
- `ProveedorPagoController::index()`
- `PlanController::index()`
- `NotaDetalleController::index()`
- `RatingController::index()`
- `ImagenItemController::index()`
- `ItemIntencionCompraController::index()`
- `OfertaController::index()`
Con suficientes registros, estos endpoints pueden causar problemas de memoria y performance.

### M3. Eliminar código muerto
- `categoriasController.php` — referencia modelo `Producto` inexistente
- `NotificacionController.php` — clase vacía "pendiente de implementación"
- `PaginationController.php` — controlador de test
- `ItemController::colors()` — método de relación que pertenece al Model
- `ItemOfertaController` usa `DB::table()` en vez de Eloquent (inconsistente)

### M4. Estandarizar manejo de errores
Algunos controladores usan try/catch con logging, otros dejan que Laravel maneje las excepciones. Debería haber un patrón consistente, idealmente usando el Handler global.

### M5. Validar parámetro `sort` en búsquedas
`ItemController::search()`, `porCategoria()`, `show()` aceptan un parámetro `sort` sin validar que sea un valor permitido. Aunque no es SQL injection (Laravel parametriza), es buena práctica validar contra una whitelist.

### M6. SuperAdminMiddleware usa `isSuperAdmin` vs AdminMiddleware usa `isSuperAdminUser()`
**Archivos:** `AdminMiddleware.php`, `SuperAdminMiddleware.php`
Inconsistencia: uno usa propiedad `$user->isSuperAdmin` y el otro usa método `$user->isSuperAdminUser()`. Verificar que ambos existan en el modelo User.

---

## RESUMEN DE PRIORIDADES

| Prioridad | ID | Descripción |
|-----------|-----|-------------|
| 🔴 P0 | E1 | API delivery config sin auth — cualquiera puede cambiar precios |
| 🔴 P0 | E2 | Rutas de usuario/direcciones sin middleware auth |
| 🔴 P1 | E3 | Bug en ItemController::show() — crash en cada request |
| 🔴 P1 | E4 | categoriasController — código muerto que crashea |
| 🟠 P1 | E5,E6 | $request->all() en NegociacionController |
| 🟠 P1 | S1 | IDOR en controladores CRUD |
| 🟠 P2 | S2 | Rutas CRUD sin protección de rol |
| 🟠 P2 | S3 | Notificaciones sin control de permisos |
| 🟡 P2 | V1 | ItemController necesita refactoring completo |
| 🟡 P3 | V4 | Estandarizar respuestas JSON |
| 🔵 P3 | M1-M6 | Mejoras de calidad y performance |
