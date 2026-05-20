# Bugfix Requirements Document

## Introduction

Los métodos `store()` y `AddTalento()` del `ItemController` fallan silenciosamente al intentar crear ítems (productos y talentos). La transacción se revierte debido a dos problemas: (1) el campo `tiene_video` no está incluido en el array `$fillable` del modelo `Item`, lo que causa que Laravel lo ignore durante `Item::create()` y potencialmente falle con un error SQL si la columna tiene restricción NOT NULL sin valor por defecto; (2) el campo `descuento` se accede directamente desde `$validatedData` sin operador null coalescing, causando un error "Undefined array key" cuando el usuario no envía ese campo en el formulario. Ambos errores provocan excepciones que activan el `DB::rollBack()`, impidiendo el registro de cualquier ítem.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN un usuario crea un producto o talento sin enviar el campo `descuento` en el formulario THEN el sistema lanza una excepción "Undefined array key 'descuento'" al construir el array `$itemData`, la transacción se revierte y el ítem no se registra

1.2 WHEN un usuario crea un producto o talento y el array `$itemData` incluye `tiene_video => false` THEN el sistema ignora silenciosamente el campo `tiene_video` durante `Item::create()` porque no está en `$fillable`, y si la columna de base de datos tiene restricción NOT NULL sin valor por defecto, la inserción SQL falla y la transacción se revierte

1.3 WHEN un usuario crea un producto o talento y sube un video como imagen principal THEN el sistema no puede persistir `tiene_video = true` mediante mass assignment (`Item::create()`) porque el campo no está en `$fillable`, aunque el `$item->save()` posterior sí funciona si el ítem fue creado exitosamente

### Expected Behavior (Correct)

2.1 WHEN un usuario crea un producto o talento sin enviar el campo `descuento` en el formulario THEN el sistema SHALL asignar `null` como valor de descuento (usando el operador `??`) y crear el ítem exitosamente

2.2 WHEN un usuario crea un producto o talento y el array `$itemData` incluye `tiene_video => false` THEN el sistema SHALL aceptar el campo `tiene_video` durante `Item::create()` y persistirlo correctamente en la base de datos

2.3 WHEN un usuario crea un producto o talento y sube un video como imagen principal THEN el sistema SHALL persistir `tiene_video = true` correctamente tanto en la creación inicial como en la actualización posterior

### Unchanged Behavior (Regression Prevention)

3.1 WHEN un usuario crea un producto con todos los campos requeridos incluyendo `descuento` con un valor numérico válido THEN el sistema SHALL CONTINUE TO crear el ítem con el descuento especificado

3.2 WHEN un usuario crea un producto con imágenes (no video) como archivo principal THEN el sistema SHALL CONTINUE TO crear el ítem con `tiene_video = false` y guardar las imágenes correctamente

3.3 WHEN un usuario crea un talento (categoría 29) con todos los campos válidos THEN el sistema SHALL CONTINUE TO crear el ítem y redirigir a la ruta de administración de talentos

3.4 WHEN un usuario crea un producto con colores y stock THEN el sistema SHALL CONTINUE TO sincronizar los colores con su stock correspondiente

3.5 WHEN un usuario crea un producto con imágenes adicionales THEN el sistema SHALL CONTINUE TO guardar todas las imágenes adicionales con su orden correcto

3.6 WHEN la validación del formulario falla (campos requeridos faltantes, formatos inválidos) THEN el sistema SHALL CONTINUE TO devolver los errores de validación y redirigir al formulario con los datos ingresados
