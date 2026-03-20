# Database — Convenciones y Estándares

## Motor y Charset
- MariaDB 10.4+ / MySQL 8+
- Engine: InnoDB (todas las tablas)
- Charset: `utf8mb4` — collation preferido: `utf8mb4_unicode_ci`
- NO mezclar collations (`utf8mb4_general_ci` vs `utf8mb4_unicode_ci`) en tablas que se relacionan

## Naming
- Tablas: español, snake_case, plural (`pagos_compra`, `tarjetas_pagos`, `items`)
- Primary keys: `id_{entidad_singular}` (ej: `id_item`, `id_carrito`, `id_pago_compra`)
- Foreign keys: mismo nombre que la PK referenciada
- Columnas: español, snake_case (`fecha_creacion`, `es_predeterminada`)
- Índices: `idx_{tabla}_{columna}` para índices manuales

## Primary Keys
- Tablas transaccionales (pagos): UUID `char(36) DEFAULT uuid()`
- Tablas de catálogo/entidad: `int(11) AUTO_INCREMENT`
- Tablas geográficas (provincias, municipios, distritos): `varchar` con código jerárquico

## Foreign Keys
- Siempre definir FK constraints explícitos
- ON DELETE: `CASCADE` para dependientes, `SET NULL` para opcionales
- ON UPDATE: `CASCADE` siempre

## Timestamps
- Tablas con auditoría: usar `created_at` / `updated_at` de Laravel
- Tablas legacy: campo `fecha datetime DEFAULT current_timestamp()`
- NO mezclar ambos patrones en la misma tabla

## Índices
- Toda FK debe tener índice (MariaDB lo crea automáticamente con CONSTRAINT)
- Agregar índices compuestos para queries frecuentes (ej: `WHERE id_user = ? AND estatus = ?`)
- Columnas usadas en WHERE/ORDER BY frecuentes deben tener índice

## Migraciones
- Usar migraciones Laravel para todos los cambios de esquema
- Naming: `{fecha}_{descripcion_accion}.php`
- Migración rota conocida: `2026_03_15_130000_restructure_predefined_messages.php` — usar `--path=` para evitarla
- Siempre incluir `down()` para rollback

## Reglas de Integridad
- No almacenar datos calculados que puedan derivarse de otros campos
- Usar `decimal(10,2)` para montos monetarios, NO `double` ni `float`
- Campos booleanos: `tinyint(1)` con DEFAULT explícito
- Campos de estado: preferir `varchar` con valores legibles sobre `tinyint` con códigos numéricos
