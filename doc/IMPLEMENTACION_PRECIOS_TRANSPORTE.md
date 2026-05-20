# 💰 Implementación: Precios en Catálogo de Artículos de Transporte

> **Módulo:** Transporte y Mudanza  
> **Fecha de planificación:** 2026-05-19  
> **Estado:** ⏳ Pendiente de implementar  
> **Rama:** `udate-01.app`

---

## 📋 Objetivo

Agregar un campo **precio (RD$)** editable a cada artículo del catálogo de transporte/mudanza.  
En la vista pública `/transporte`, cuando el tipo de servicio sea **mudanza**, mostrar un **estimado aproximado** calculado en tiempo real según los artículos seleccionados × cantidad × precio.  
Si el servicio es **solo transporte**, **no** mostrar el cálculo.

---

## 📁 Archivos a Modificar

| # | Archivo | Acción | Descripción |
|---|---------|--------|-------------|
| 1 | `database/migrations/XXXX_add_precio_to_transporte_articulos.php` | **CREAR** | Nueva migración para agregar columna `precio` |
| 2 | `app/Models/TransporteArticulo.php` | MODIFICAR | Agregar `precio` a `$fillable` y `$casts` |
| 3 | `app/Http/Controllers/Admin/AdminTransporteController.php` | MODIFICAR | Aceptar y guardar `precio` en store/update |
| 4 | `resources/views/admin/transporte/index.blade.php` | MODIFICAR | Campo precio en formulario y tabla del catálogo |
| 5 | `resources/views/transporte/create.blade.php` | MODIFICAR | Mostrar estimado aproximado para mudanzas |
| 6 | `public/check.php` | MODIFICAR | Agregar columna `precio` en producción |
| 7 | `database/migrations/2026_05_19_180000_seed_transporte_articulos_table.php` | MODIFICAR | Incluir `precio => 0.00` en seed |

---

## 🔧 Paso 1: Migración de Base de Datos

### Crear archivo: `database/migrations/XXXX_add_precio_to_transporte_articulos.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transporte_articulos', function (Blueprint $table) {
            $table->decimal('precio', 10, 2)->default(0.00)->after('categoria');
        });
    }

    public function down(): void
    {
        Schema::table('transporte_articulos', function (Blueprint $table) {
            $table->dropColumn('precio');
        });
    }
};
```

---

## 🔧 Paso 2: Modelo `TransporteArticulo.php`

### Cambios en `app/Models/TransporteArticulo.php`

```diff
 protected $fillable = [
     'nombre',
     'categoria',
+    'precio',
     'estatus',
 ];

 protected $casts = [
     'estatus' => 'boolean',
+    'precio'  => 'decimal:2',
 ];
```

---

## 🔧 Paso 3: Controller Admin

### Cambios en `app/Http/Controllers/Admin/AdminTransporteController.php`

#### Método `storeArticulo()` — agregar validación y guardar precio:

```diff
 public function storeArticulo(Request $request)
 {
     $request->validate([
         'nombre'    => 'required|string|max:255',
         'categoria' => 'required|in:transporte,mudanza,ambos',
+        'precio'    => 'required|numeric|min:0',
     ]);

     TransporteArticulo::create([
         'nombre'    => $request->nombre,
         'categoria' => $request->categoria,
+        'precio'    => $request->precio,
         'estatus'   => true,
     ]);

     return back()->with('success', '¡Artículo agregado con éxito al catálogo de transporte!');
 }
```

#### Método `updateArticulo()` — agregar validación y guardar precio:

```diff
 public function updateArticulo(Request $request, $id)
 {
     $request->validate([
         'nombre'    => 'required|string|max:255',
         'categoria' => 'required|in:transporte,mudanza,ambos',
+        'precio'    => 'required|numeric|min:0',
         'estatus'   => 'required|boolean',
     ]);

     $articulo = TransporteArticulo::findOrFail($id);
     $articulo->update([
         'nombre'    => $request->nombre,
         'categoria' => $request->categoria,
+        'precio'    => $request->precio,
         'estatus'   => $request->estatus,
     ]);

     return back()->with('success', '¡Artículo actualizado con éxito!');
 }
```

---

## 🔧 Paso 4: Vista Admin — Catálogo de Artículos

### Cambios en `resources/views/admin/transporte/index.blade.php`

#### 4a. Formulario "Agregar Nuevo Artículo" — agregar campo precio

Después del `<div>` de "Categoría del Servicio" y antes del botón, agregar:

```html
<div>
    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Precio (RD$) <span class="text-red-500">*</span></label>
    <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">RD$</span>
        <input type="number" name="precio" required min="0" step="0.01" placeholder="0.00"
               class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
    </div>
</div>
```

> **Nota:** Cambiar el grid del formulario de `md:grid-cols-3` a `md:grid-cols-4` para acomodar el nuevo campo.

#### 4b. Tabla del catálogo — agregar columna "Precio"

En el `<thead>`, agregar después de "Categoría":

```html
<th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase border-b">Precio</th>
```

En el `<tbody>`, dentro del form inline de edición, agregar después del select de categoría:

```html
<td class="px-6 py-4">
    <div class="relative">
        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none">RD$</span>
        <input type="number" name="precio" value="{{ $art->precio }}" min="0" step="0.01"
               class="w-28 pl-9 pr-2 py-1 border border-transparent hover:border-gray-300 rounded focus:border-blue-500 text-sm font-semibold text-gray-800 bg-transparent focus:bg-white transition-all">
    </div>
</td>
```

> **Nota:** Actualizar el `colspan` del mensaje "No hay artículos" de `5` a `6`.

---

## 🔧 Paso 5: Vista Pública `/transporte` — Estimado de Mudanza

### Cambios en `resources/views/transporte/create.blade.php`

#### 5a. Agregar `data-precio` a cada tarjeta de artículo

En la línea del `<div class="articulo-item ...">`, agregar el atributo:

```diff
 <div class="articulo-item flex flex-col gap-2 p-3 bg-white rounded-xl border border-gray-100 shadow-sm hover:border-blue-300 transition-all" 
      data-category="{{ $art->categoria }}" 
+     data-precio="{{ $art->precio }}"
      style="display: none;">
```

#### 5b. Agregar bloque de estimado debajo del checklist

Después del cierre del `<div id="checklist-articulos-container">` (después de la línea 109), insertar:

```html
<!-- Estimado Aproximado de Mudanza -->
<div id="estimado-mudanza-container" class="mt-4 hidden">
    <div class="bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-xl p-5 shadow-sm">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-purple-800">Estimado Aproximado de Mudanza</h4>
                <p class="text-xs text-purple-600">Este valor es solo un estimado referencial. El precio final puede variar.</p>
            </div>
        </div>
        <div class="flex items-center justify-between bg-white/70 rounded-lg px-4 py-3 border border-purple-100">
            <span class="text-sm font-semibold text-gray-700">Total estimado:</span>
            <span id="estimado-total" class="text-2xl font-bold text-purple-700">RD$ 0.00</span>
        </div>
        <p class="text-[10px] text-purple-500 mt-2 text-right italic">* No incluye distancia, pisos ni condiciones especiales.</p>
    </div>
</div>
```

#### 5c. Modificar el JavaScript

Agregar la función de cálculo y conectarla a los eventos existentes.

En la función `filterArticles()`, al final (después de mostrar/ocultar artículos), agregar:

```javascript
// Mostrar/ocultar estimado según tipo de servicio
const estimadoContainer = document.getElementById('estimado-mudanza-container');
if (selectedValue === 'mudanza') {
    estimadoContainer.classList.remove('hidden');
    calcularEstimado();
} else {
    estimadoContainer.classList.add('hidden');
}
```

Agregar nueva función global `calcularEstimado()`:

```javascript
function calcularEstimado() {
    const tipoServicio = document.getElementById('tipo_servicio').value;
    const estimadoContainer = document.getElementById('estimado-mudanza-container');
    const estimadoTotal = document.getElementById('estimado-total');
    
    if (tipoServicio !== 'mudanza') {
        estimadoContainer.classList.add('hidden');
        return;
    }

    let total = 0;
    document.querySelectorAll('.articulo-item').forEach(item => {
        const checkbox = item.querySelector('.articulo-checkbox');
        const cantInput = item.querySelector('input[type="number"]');
        const precio = parseFloat(item.getAttribute('data-precio')) || 0;
        
        if (checkbox && checkbox.checked && item.style.display !== 'none') {
            const cantidad = parseInt(cantInput.value) || 1;
            total += precio * cantidad;
        }
    });

    estimadoTotal.textContent = 'RD$ ' + total.toLocaleString('es-DO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
```

Modificar la función `toggleCantidad()` para recalcular:

```diff
 function toggleCantidad(id) {
     const checkbox = document.getElementById('art-' + id);
     const quantityInput = document.getElementById('cant-' + id);
     if (checkbox.checked) {
         quantityInput.removeAttribute('disabled');
         quantityInput.focus();
     } else {
         quantityInput.setAttribute('disabled', 'disabled');
         quantityInput.value = '1';
     }
+    calcularEstimado();
 }
```

Agregar evento `input` a los campos de cantidad para recalcular en tiempo real. Dentro del `DOMContentLoaded`:

```javascript
// Recalcular estimado cuando cambie la cantidad de un artículo
document.querySelectorAll('.articulo-item input[type="number"]').forEach(input => {
    input.addEventListener('input', calcularEstimado);
});
```

---

## 🔧 Paso 6: Producción — `public/check.php`

### Cambios en `public/check.php`

#### 6a. Al CREAR la tabla `transporte_articulos`, incluir la columna `precio`:

```diff
 DB::statement("CREATE TABLE transporte_articulos (
     id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
     nombre VARCHAR(255) NOT NULL,
     categoria ENUM('transporte', 'mudanza', 'ambos') NOT NULL DEFAULT 'ambos',
+    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
     estatus TINYINT(1) NOT NULL DEFAULT 1,
     created_at TIMESTAMP NULL,
     updated_at TIMESTAMP NULL
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
```

#### 6b. Si la tabla ya existe, verificar la columna `precio`:

Después de `echo "✅ transporte_articulos ya existe\n";`, agregar:

```php
// Verificar si tiene columna precio
$cols = Illuminate\Support\Facades\Schema::getColumnListing('transporte_articulos');
if (!in_array('precio', $cols)) {
    DB::statement("ALTER TABLE transporte_articulos ADD precio DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER categoria");
    echo "✅ Columna precio agregada a transporte_articulos\n";
}
```

#### 6c. En el seed de artículos, incluir `precio`:

```diff
 DB::table('transporte_articulos')->insert([
     'nombre'     => $art['nombre'],
     'categoria'  => $art['categoria'],
+    'precio'     => 0.00,
     'estatus'    => true,
     'created_at' => now(),
     'updated_at' => now(),
 ]);
```

---

## 🔧 Paso 7: Migración Seeder existente

### Cambios en `database/migrations/2026_05_19_180000_seed_transporte_articulos_table.php`

En cada insert, agregar el campo `precio`:

```diff
 DB::table('transporte_articulos')->insert([
     'nombre'     => $art['nombre'],
     'categoria'  => $art['categoria'],
+    'precio'     => 0.00,
     'estatus'    => true,
     'created_at' => now(),
     'updated_at' => now(),
 ]);
```

---

## ✅ Verificación Post-Implementación

### En Producción
1. Acceder a `https://dominio.com/check.php` → debe crear/alterar columna `precio`.
2. Admin → Catálogo de Artículos → asignar precios reales a cada artículo.

### Flujo de Usuario
1. Ir a `/transporte`.
2. Seleccionar **Mudanza** → debe aparecer el bloque de estimado con `RD$ 0.00`.
3. Marcar artículos y ajustar cantidades → el estimado se actualiza en tiempo real.
4. Cambiar a **Transporte** → el bloque de estimado desaparece.

### Datos Esperados
| Escenario | Resultado |
|-----------|-----------|
| Mudanza + 2 sofás a RD$500 c/u | Estimado: RD$ 1,000.00 |
| Mudanza + 1 nevera (RD$800) + 3 cajas (RD$100) | Estimado: RD$ 1,100.00 |
| Transporte (cualquier artículo) | Sin estimado visible |
| Artículo sin precio asignado (RD$0.00) | Suma 0, no afecta el total |

---

## 📝 Notas Importantes

- Los precios iniciales serán `RD$ 0.00`. El **administrador** debe asignarlos desde el panel.
- El estimado es **referencial**. Se recomienda agregar texto claro indicando que el precio final puede variar según distancia, pisos, accesibilidad, etc.
- Esta implementación **no afecta** la lógica de guardado de solicitudes existente, solo agrega información visual al usuario.
