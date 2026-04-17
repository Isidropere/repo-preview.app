# Manual de SuperAdministrador — CambialóRD

> Guía para usuarios con rol SuperAdmin (`isSuperAdmin = 1`)
> El SuperAdmin tiene acceso completo al sistema, incluyendo todo lo del Admin.

---

## 1. Acceso

- URL: `/admin`
- Requiere: cuenta con `isSuperAdmin = 1`
- Tiene acceso a todas las rutas de Admin más las exclusivas de SuperAdmin

---

## 2. Estadísticas del Sistema

- URL: `/admin/estadisticas`
- Muestra métricas en tiempo real:
  - Total de usuarios, productos, ventas, intercambios
  - Gráficas de actividad por período
  - Ingresos por ventas y registros de talentos

### Configuración de delivery desde estadísticas
- Ajusta los porcentajes de ganancia por tipo de zona (corta, larga, especial)
- Los cambios aplican inmediatamente al cálculo de envíos

---

## 3. Mensajes Predefinidos

- URL: `/admin/mensajes-predefinidos`
- Gestiona los mensajes que los usuarios pueden usar en negociaciones

### Crear mensaje
1. Haz click en **Nuevo mensaje**
2. Ingresa: título, texto del mensaje, tipo (acción), rol (emisor/receptor/general)
3. Activa o desactiva el mensaje
4. Guarda

### Editar/Eliminar
- Usa los botones de acción en cada mensaje
- Los mensajes eliminados no se pueden recuperar

---

## 4. Cuentas Bancarias de la Empresa

- URL: `/admin/cuentas-banco`
- Gestiona las cuentas bancarias que se muestran a los usuarios para pagos por transferencia

### Agregar cuenta
1. Haz click en **Nueva cuenta**
2. Ingresa: banco, número de cuenta, tipo (ahorro/corriente/otro), titular, descripción
3. Activa la cuenta para que sea visible

### Activar/Desactivar
- Usa el toggle para mostrar u ocultar una cuenta sin eliminarla

---

## 5. Configuración de Tarifas (Categoría 29)

- URL: `/admin/config-tarifa`
- Controla los parámetros de publicación de talentos/servicios

| Campo | Descripción |
|-------|-------------|
| Monto de registro | Costo base por cupo publicado (RD$) |
| Descuento venta masiva | % de descuento al comprar en cantidad |
| Cantidad mínima para descuento | Cuántos cupos debe comprar para obtener el descuento |

Ejemplo: Si monto = RD$100 y el usuario publica 10 cupos → paga RD$1,000.

---

## 6. Autenticación Social (OAuth)

- URL: `/admin` → sección OAuth
- Configura los proveedores de login social: Google, Facebook, Instagram

### Configurar un proveedor
1. Ingresa el `Client ID` y `Client Secret` del proveedor
2. Configura la `Redirect URI` (debe coincidir con la registrada en el proveedor)
3. Activa el proveedor con el toggle

> **Seguridad:** Los cambios en OAuth quedan registrados en logs. Solo el SuperAdmin puede modificar estas configuraciones.

---

## 7. Gestión de Roles de Usuario

Para asignar roles de Admin o SuperAdmin a un usuario, ejecuta directamente en la BD:

```sql
-- Hacer admin
UPDATE users SET isAdmin = 1 WHERE email = 'correo@ejemplo.com';

-- Hacer superadmin
UPDATE users SET isAdmin = 1, isSuperAdmin = 1 WHERE email = 'correo@ejemplo.com';

-- Quitar roles
UPDATE users SET isAdmin = 0, isSuperAdmin = 0 WHERE email = 'correo@ejemplo.com';
```

> No se puede asignar roles desde la interfaz por seguridad (protección contra mass assignment).

---

## 8. Zonas de Delivery

- Configura desde `/admin/estadisticas` → sección delivery
- Cada zona tiene: nombre, tipo, lista de pueblos, precio persona, precio empresa, días de entrega

### Tipos de zona
| Tipo | Descripción |
|------|-------------|
| corta | Zonas cercanas (ej: Santo Domingo) |
| larga | Zonas lejanas (ej: norte del país) |
| especial | Zonas con tarifa especial |

---

## 9. Logs y Errores del Sistema

- Los errores se registran en `storage/logs/cabialoErrores-YYYY-MM-DD.log`
- Rotación diaria automática, retención de 14 días
- Los errores de usuario también se guardan en la tabla `application_errors` con UUID de referencia

### Consultar errores recientes
```sql
SELECT error_reference, message, url, user_id, created_at 
FROM application_errors 
ORDER BY created_at DESC 
LIMIT 20;
```

---

## 10. Mantenimiento

### Limpiar caché
```bash
php artisan optimize:clear
```

### Ejecutar migraciones pendientes
```bash
php artisan migrate
```

### Ver estado de migraciones
```bash
php artisan migrate:status
```

---

## 11. Diferencias Admin vs SuperAdmin

| Función | Admin | SuperAdmin |
|---------|-------|-----------|
| Ver/gestionar órdenes | ✅ | ✅ |
| Moderar imágenes | ✅ | ✅ |
| Gestionar usuarios | ✅ | ✅ |
| Ver mensajes predefinidos | ✅ (solo lectura) | ✅ |
| Crear/editar mensajes predefinidos | ❌ | ✅ |
| Estadísticas y gráficas | ❌ | ✅ |
| Configurar tarifas cat. 29 | ❌ | ✅ |
| Gestionar cuentas bancarias | ❌ | ✅ |
| Configurar OAuth | ❌ | ✅ |
| Configurar delivery | ❌ | ✅ |
| Asignar roles (vía BD) | ❌ | ✅ |
