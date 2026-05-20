# Item Registration Fix — Bugfix Design

## Overview

Los métodos `store()` y `AddTalento()` del `ItemController` fallan silenciosamente al crear ítems debido a dos defectos: (1) acceso directo a `$validatedData['descuento']` sin operador null coalescing, que lanza "Undefined array key" cuando el campo no se envía; y (2) el campo `tiene_video` ausente del array `$fillable` del modelo `Item`, lo que impide su persistencia vía mass assignment. Ambos errores provocan excepciones dentro de bloques `try/catch` que ejecutan `DB::rollBack()`, revirtiendo toda la transacción. El fix es mínimo: agregar `?? null` al acceso de `descuento` en ambos métodos y añadir `'tiene_video'` al array `$fillable` del modelo `Item`.

## Glossary

- **Bug_Condition (C)**: La condición que dispara el bug — cuando `descuento` no está presente en los datos validados O cuando `tiene_video` se incluye en `$itemData` pero no está en `$fillable`
- **Property (P)**: El comportamiento deseado — los ítems se crean exitosamente con `descuento = null` cuando no se envía, y `tiene_video` se persiste correctamente vía mass assignment
- **Preservation**: El comportamiento existente que debe permanecer sin cambios — creación de ítems con descuento explícito, sincronización de colores, guardado de imágenes, validación, redirecciones
- **`store()`**: Método en `ItemController` (línea 269) que crea productos con inventario, colores e imágenes
- **`AddTalento()`**: Método en `ItemController` (línea 23) que crea talentos (categoría 29) con imágenes
- **`$fillable`**: Array del modelo `Item` que define qué campos se pueden asignar masivamente vía `Item::create()`
- **`$itemData`**: Array asociativo construido en ambos métodos con los datos para `Item::create()`

## Bug Details

### Bug Condition

El bug se manifiesta cuando un usuario crea un producto o talento sin enviar el campo `descuento` en el formulario, O cuando el array `$itemData` incluye `tiene_video` que no está en `$fillable`. En `store()` y `AddTalento()`, el acceso `$validatedData['descuento']` sin `??` lanza una excepción PHP, y la ausencia de `tiene_video` en `$fillable` causa que Laravel ignore el campo o que la inserción SQL falle si la columna tiene restricción NOT NULL sin valor por defecto.

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input of type ItemCreationRequest
  OUTPUT: boolean

  descuentoMissing := 'descuento' NOT IN input.validatedFields
  tieneVideoNotFillable := 'tiene_video' NOT IN Item.fillable
                           AND 'tiene_video' IN input.itemData

  RETURN descuentoMissing OR tieneVideoNotFillable
END FUNCTION
```

### Examples

- **Producto sin descuento**: Usuario crea producto sin campo `descuento` → `$validatedData['descuento']` lanza "Undefined array key 'descuento'" → `DB::rollBack()` → ítem no se crea. **Esperado**: ítem se crea con `descuento = null`.
- **Talento sin descuento**: Usuario crea talento sin campo `descuento` → mismo error que arriba → talento no se crea. **Esperado**: talento se crea con `descuento = null`.
- **Producto con video**: Usuario sube video como imagen principal → `Item::create()` incluye `tiene_video => false` pero el campo no está en `$fillable` → si la columna es NOT NULL sin default, INSERT falla → `DB::rollBack()`. **Esperado**: `tiene_video = false` se persiste correctamente, luego se actualiza a `true` vía `$item->save()`.
- **Producto con descuento explícito y sin video**: Usuario envía `descuento = 10` y sube imagen (no video) → no se dispara el bug de descuento, pero `tiene_video` sigue sin estar en `$fillable` → posible fallo SQL. **Esperado**: ítem se crea con `descuento = 10` y `tiene_video = false`.

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- La creación de ítems con un valor numérico explícito de `descuento` debe seguir funcionando exactamente igual
- La subida y guardado de imágenes (principal y adicionales) con su orden correcto debe permanecer sin cambios
- La sincronización de colores con su stock correspondiente (`$item->colors()->sync()`) debe seguir funcionando
- La creación de registros de inventario (`Inventario::create()`) en `store()` debe permanecer sin cambios
- Las redirecciones post-creación deben mantenerse: `items.user` para productos, `items.admintalento` para talentos
- La validación de formularios y el retorno de errores con `withErrors()->withInput()` debe seguir funcionando
- La invalidación de caché (`home_intercambio`, `home_venta`) debe seguir ejecutándose tras commit exitoso

**Scope:**
Todas las entradas que NO involucren la condición del bug deben permanecer completamente sin afectar. Esto incluye:
- Requests con `descuento` explícito (valor numérico válido)
- Requests que no incluyen `tiene_video` en el flujo de datos (otros métodos del controlador)
- Operaciones de lectura, actualización y eliminación de ítems existentes
- Validación de campos requeridos y formatos

## Hypothesized Root Cause

Based on the bug description, the most likely issues are:

1. **Acceso directo a clave nullable sin null coalescing**: En ambos métodos (`store()` línea ~340, `AddTalento()` línea ~100), la línea `'descuento' => $validatedData['descuento']` accede directamente al array. Cuando la regla de validación es `'nullable|numeric|min:0'` y el campo no se envía en el request, Laravel no incluye la clave en `$validatedData`. PHP lanza "Undefined array key 'descuento'" que es capturada por el `catch (\Exception $e)` y ejecuta `DB::rollBack()`.

2. **Campo `tiene_video` ausente de `$fillable`**: El array `$fillable` del modelo `Item` contiene 16 campos pero no incluye `'tiene_video'`. Cuando `Item::create($itemData)` se ejecuta con `'tiene_video' => false`, Laravel silenciosamente ignora el campo por protección de mass assignment. Si la columna `tiene_video` en la base de datos tiene restricción `NOT NULL` sin valor `DEFAULT`, la sentencia `INSERT` falla con un error SQL, que también es capturado por el `catch` y provoca `DB::rollBack()`.

3. **Interacción entre ambos bugs**: Si `descuento` no se envía, la excepción ocurre ANTES de llegar a `Item::create()`, por lo que el bug de `tiene_video` nunca se alcanza en ese escenario. Sin embargo, si `descuento` SÍ se envía, el bug de `tiene_video` puede manifestarse independientemente.

4. **El `$item->save()` posterior funciona**: Después de `Item::create()`, el código hace `$item->tiene_video = true; $item->save()` para videos. Este `save()` funciona porque la asignación directa de atributos (`$item->tiene_video = true`) no está sujeta a la protección de `$fillable`. Pero si `Item::create()` ya falló, este código nunca se ejecuta.

## Correctness Properties

Property 1: Bug Condition - Creación de ítem sin descuento

_For any_ request de creación de ítem (producto o talento) donde el campo `descuento` no se envía en el formulario, la función corregida SHALL crear el ítem exitosamente con `descuento = null` en la base de datos, sin lanzar excepciones ni revertir la transacción.

**Validates: Requirements 2.1**

Property 2: Bug Condition - Persistencia de tiene_video vía mass assignment

_For any_ request de creación de ítem donde `$itemData` incluye `tiene_video => false`, la función corregida SHALL persistir el valor de `tiene_video` correctamente en la base de datos mediante `Item::create()`, sin que el campo sea ignorado por la protección de mass assignment.

**Validates: Requirements 2.2, 2.3**

Property 3: Preservation - Creación con descuento explícito

_For any_ request de creación de ítem donde el campo `descuento` se envía con un valor numérico válido, la función corregida SHALL producir el mismo resultado que la función original: el ítem se crea con el valor exacto de descuento proporcionado.

**Validates: Requirements 3.1**

Property 4: Preservation - Comportamiento existente sin cambios

_For any_ request que NO involucre la condición del bug (descuento presente, sin problemas de fillable), la función corregida SHALL producir exactamente el mismo resultado que la función original, preservando sincronización de colores, guardado de imágenes, creación de inventario, redirecciones y validación.

**Validates: Requirements 3.2, 3.3, 3.4, 3.5, 3.6**

## Fix Implementation

### Changes Required

Assuming our root cause analysis is correct:

**File**: `app/Models/Item.php`

**Target**: Array `$fillable`

**Specific Changes**:
1. **Agregar `tiene_video` a `$fillable`**: Añadir `'tiene_video'` al array `$fillable` del modelo `Item` para que `Item::create()` pueda asignar masivamente este campo.
   - Ubicación: después de `'presentacion'` en el array `$fillable`
   - Cambio: agregar `'tiene_video'` como último elemento del array

---

**File**: `app/Http/Controllers/ItemController.php`

**Function**: `store()` (línea 269)

**Specific Changes**:
2. **Null coalescing en `descuento`**: Cambiar `'descuento' => $validatedData['descuento']` por `'descuento' => $validatedData['descuento'] ?? null` en la construcción de `$itemData` (~línea 340).
   - Esto evita la excepción "Undefined array key" cuando el campo no se envía
   - El valor `null` es compatible con la columna `descuento` que acepta NULL en la BD

---

**File**: `app/Http/Controllers/ItemController.php`

**Function**: `AddTalento()` (línea 23)

**Specific Changes**:
3. **Null coalescing en `descuento`**: Cambiar `'descuento' => $validatedData['descuento']` por `'descuento' => $validatedData['descuento'] ?? null` en la construcción de `$itemData` (~línea 100).
   - Mismo fix que en `store()`, aplicado al método de creación de talentos

### Summary of Changes

| # | Archivo | Cambio | Línea aprox. |
|---|---------|--------|-------------|
| 1 | `app/Models/Item.php` | Agregar `'tiene_video'` a `$fillable` | ~57 |
| 2 | `app/Http/Controllers/ItemController.php` | `$validatedData['descuento'] ?? null` en `store()` | ~340 |
| 3 | `app/Http/Controllers/ItemController.php` | `$validatedData['descuento'] ?? null` en `AddTalento()` | ~100 |

## Testing Strategy

### Validation Approach

La estrategia de testing sigue un enfoque de dos fases: primero, generar contraejemplos que demuestren el bug en el código sin corregir, luego verificar que el fix funciona correctamente y preserva el comportamiento existente.

### Exploratory Bug Condition Checking

**Goal**: Generar contraejemplos que demuestren el bug ANTES de implementar el fix. Confirmar o refutar el análisis de causa raíz. Si refutamos, necesitaremos re-hipotetizar.

**Test Plan**: Escribir tests que simulen requests HTTP a `store()` y `AddTalento()` sin el campo `descuento` y con `tiene_video` en los datos. Ejecutar estos tests en el código SIN CORREGIR para observar los fallos y entender la causa raíz.

**Test Cases**:
1. **Store sin descuento**: Simular POST a `store()` sin campo `descuento` (fallará en código sin corregir con "Undefined array key")
2. **AddTalento sin descuento**: Simular POST a `AddTalento()` sin campo `descuento` (fallará en código sin corregir)
3. **Store con tiene_video**: Simular creación de ítem verificando que `tiene_video` se persiste (fallará si NOT NULL sin default)
4. **AddTalento con tiene_video**: Simular creación de talento verificando persistencia de `tiene_video`

**Expected Counterexamples**:
- `$validatedData['descuento']` lanza "Undefined array key 'descuento'" cuando el campo no se envía
- `Item::create()` ignora `tiene_video` o falla con error SQL si la columna es NOT NULL sin default
- Posibles causas: acceso directo a array sin null coalescing, campo ausente de `$fillable`

### Fix Checking

**Goal**: Verificar que para todas las entradas donde la condición del bug se cumple, la función corregida produce el comportamiento esperado.

**Pseudocode:**
```
FOR ALL input WHERE isBugCondition(input) DO
  result := store_fixed(input) OR AddTalento_fixed(input)
  ASSERT item_created_successfully(result)
  ASSERT result.item.descuento == (input.descuento ?? null)
  ASSERT result.item.tiene_video == input.itemData.tiene_video
END FOR
```

### Preservation Checking

**Goal**: Verificar que para todas las entradas donde la condición del bug NO se cumple, la función corregida produce el mismo resultado que la función original.

**Pseudocode:**
```
FOR ALL input WHERE NOT isBugCondition(input) DO
  ASSERT store_original(input) == store_fixed(input)
  ASSERT AddTalento_original(input) == AddTalento_fixed(input)
END FOR
```

**Testing Approach**: Se recomienda property-based testing para preservation checking porque:
- Genera muchos casos de prueba automáticamente sobre el dominio de entrada
- Detecta edge cases que los unit tests manuales podrían omitir
- Provee garantías fuertes de que el comportamiento no cambia para entradas no afectadas por el bug

**Test Plan**: Observar el comportamiento en código SIN CORREGIR primero para requests con descuento explícito y creación normal de ítems, luego escribir property-based tests capturando ese comportamiento.

**Test Cases**:
1. **Preservación de descuento explícito**: Verificar que crear ítem con `descuento = 15.5` sigue funcionando correctamente tras el fix
2. **Preservación de colores y stock**: Verificar que la sincronización de colores con stock sigue funcionando
3. **Preservación de imágenes**: Verificar que el guardado de imágenes principal y adicionales sigue funcionando
4. **Preservación de inventario**: Verificar que `Inventario::create()` en `store()` sigue creando el registro correctamente
5. **Preservación de validación**: Verificar que requests inválidos siguen retornando errores de validación

### Unit Tests

- Test de `store()` sin campo `descuento` → ítem creado con `descuento = null`
- Test de `AddTalento()` sin campo `descuento` → ítem creado con `descuento = null`
- Test de `Item::create()` con `tiene_video => false` → campo persistido correctamente
- Test de `Item::create()` con `tiene_video => true` → campo persistido correctamente
- Test de `store()` con `descuento = 0` (edge case, valor falsy pero válido)
- Test de `store()` con `descuento = null` explícito

### Property-Based Tests

- Generar requests aleatorios con y sin `descuento` y verificar que el ítem se crea correctamente en ambos casos
- Generar valores aleatorios de `tiene_video` (true/false) y verificar persistencia correcta vía mass assignment
- Generar requests completos con todos los campos válidos y verificar que el comportamiento de preservación se mantiene

### Integration Tests

- Test de flujo completo de creación de producto con video como imagen principal → `tiene_video = true` persistido
- Test de flujo completo de creación de talento sin descuento → ítem creado, redirección a `items.admintalento`
- Test de flujo completo de creación de producto con colores, stock, imágenes y sin descuento → todo persistido correctamente
