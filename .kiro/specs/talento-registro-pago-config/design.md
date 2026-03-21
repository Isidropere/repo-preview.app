# Design Document: talento-registro-pago-config

## Overview

Esta funcionalidad introduce un flujo de pago con tarjeta (Cardnet) obligatorio para publicar talentos/servicios (categoría 29) en CambialóRD. El diseño se divide en cuatro áreas:

1. **Flujo de pago previo al registro**: interceptar el submit del formulario de talento, mostrar un formulario de pago con tarjeta (igual al flujo de checkout existente), procesar el cobro con `CardnetProvider::cobrar()` y, si es aprobado, crear el ítem con `estatus = 1` directamente (sin aprobación manual).
2. **CRUD de cuentas bancarias**: gestión desde el panel SuperAdmin (`/admin/estadisticas`) de la tabla `cuentas_banco_empresa`. Estas cuentas son **informativas** (para referencia interna), no se usan en el flujo de pago con Cardnet.
3. **Configuración de tarifas (singleton)**: gestión desde el panel SuperAdmin de la tabla `config_tarifa_categoria29`.
4. **Descuento por volumen**: mostrar mensaje informativo en detalle de talento y aplicar descuento automático en `CarritoService` cuando la cantidad alcanza el mínimo configurado.

El pago se procesa en tiempo real con **Cardnet** (pasarela ya integrada en el proyecto). Si el pago es aprobado, el ítem queda activo (`estatus = 1`) inmediatamente. Si falla, se muestra el error y no se crea el ítem.

---

## Architecture

```mermaid
flowchart TD
    A[Usuario llena formulario talento] --> B{id_categoria_item == 29?}
    B -- No --> C[Flujo normal: guardar ítem estatus=1]
    B -- Sí --> D[Guardar datos en sesión]
    D --> E[Redirigir a vista de pago con tarjeta]
    E --> F[Mostrar formulario de tarjeta + monto_registro]
    F --> G[Usuario ingresa datos de tarjeta / selecciona tarjeta guardada]
    G --> H[POST /talento/pago → TalentoRegistroPagoController]
    H --> I[CardnetProvider::cobrar\(\)]
    I -- success=false --> J[Mostrar error, NO crear ítem]
    I -- success=true --> K[Crear ítem con estatus=1]
    K --> L[Crear registro en pagos_registro_talento con transaction_id]
    L --> M[Redirigir a /talentos con mensaje de éxito]
```

```mermaid
flowchart LR
    SA[SuperAdmin] --> CB[CRUD cuentas_banco_empresa\n(informativo)]
    SA --> CT[CRUD config_tarifa_categoria29]
    CT --> V[Vista de pago del usuario: monto_registro]
    CT --> DV[Detalle del talento: mensaje descuento]
    CT --> CS[CarritoService: descuento por volumen]
```

### Capas involucradas

| Capa | Componentes nuevos / modificados |
|---|---|
| Rutas | `routes/web.php`: nuevas rutas de pago talento y admin |
| Controladores | `TalentoRegistroPagoController` (nuevo), `ItemController` (modificado), `AdminStatsController` (modificado) |
| Servicios | `TalentoRegistroPagoService` (nuevo), `CarritoService` (modificado) |
| Modelos | `CuentaBancoEmpresa`, `ConfigTarifaCategoria29`, `PagoRegistroTalento` (nuevos) |
| Migraciones | 3 nuevas tablas |
| Vistas | Vista de pago talento con tarjeta (nueva), secciones en `stats.blade.php` (modificado), detalle de producto (modificado) |

---

## Components and Interfaces

### TalentoRegistroPagoController

Controlador nuevo en `app/Http/Controllers/TalentoRegistroPagoController.php`.

```php
// Muestra la vista de pago con tarjeta: formulario de tarjeta + monto_registro
// Carga las tarjetas guardadas del usuario (igual que checkout)
public function mostrarPago(): View

// Procesa el pago con Cardnet:
//   1. Recupera datos de sesión (talento_pendiente_data, talento_pendiente_files)
//   2. Obtiene tarjeta del usuario (id_tarjeta + cvv del request)
//   3. Llama a PagoService::cobrarTarjeta() con monto_registro
//   4. Si success=false → redirect back con error, NO crea ítem
//   5. Si success=true → crea ítem con estatus=1, crea PagoRegistroTalento con transaction_id
//   6. Limpia sesión, redirige a /talentos con mensaje de éxito
public function procesarPago(Request $request): RedirectResponse
```

### AdminCuentaBancoController

Controlador nuevo en `app/Http/Controllers/Admin/AdminCuentaBancoController.php`.

```php
public function index(): JsonResponse      // Lista todas las cuentas
public function store(Request $request): JsonResponse   // Crea cuenta
public function update(Request $request, int $id): JsonResponse  // Actualiza
public function destroy(int $id): JsonResponse          // Elimina
public function toggleActivo(int $id): JsonResponse     // Activa/desactiva
```

Todas las rutas protegidas con middleware `superadmin`.

### AdminConfigTarifaController

Controlador nuevo en `app/Http/Controllers/Admin/AdminConfigTarifaController.php`.

```php
public function show(): JsonResponse       // Obtiene la config vigente (o defaults)
public function update(Request $request): JsonResponse  // Actualiza (upsert singleton)
```

Rutas protegidas con middleware `superadmin`.

### Modificaciones a ItemController

`AddTalento()` se modifica para interceptar cuando `id_categoria_item == 29`:
- Validar el formulario normalmente.
- Guardar los datos validados (sin archivos) en `session('talento_pendiente_data')`.
- Guardar los archivos temporalmente en `storage/app/temp/` con un UUID.
- Redirigir a `GET /talento/pago`.

### Modificaciones a CarritoService

`agregarItem()` se modifica para detectar ítems de categoría 29 con `tipo_trans=1`:
- Consultar `ConfigTarifaCategoria29::vigente()`.
- Si `cantidad >= cantidad_minima_descuento`, calcular `descuento = valor × (descuento_venta_masiva / 100)` y guardarlo en `items_intencion_compra.descuento`.
- Si `cantidad < cantidad_minima_descuento`, guardar `descuento = 0` (o el descuento propio del ítem si lo tiene).

### TalentoRegistroPagoService

Servicio nuevo en `app/Services/TalentoRegistroPagoService.php`.

```php
// Recupera datos de sesión, procesa el cobro con PagoService,
// si success=true: guarda el ítem con estatus=1, mueve archivos temp a storage definitivo,
// crea el registro en pagos_registro_talento con transaction_id, limpia la sesión.
// Si success=false: lanza excepción con el mensaje de error de Cardnet.
public function procesarPagoYGuardarTalento(
    int $userId,
    TarjetaPago $tarjeta,
    ?string $cvv,
    string $clientIp
): Item

// Obtiene la tarjeta del usuario para el pago (igual que CheckoutService)
private function obtenerTarjeta(string $idTarjeta, int $userId): ?TarjetaPago
```

---

## Data Models

### Tabla: `cuentas_banco_empresa`

```sql
CREATE TABLE cuentas_banco_empresa (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    banco           VARCHAR(100) NOT NULL,
    numero_cuenta   VARCHAR(50)  NOT NULL,
    tipo_cuenta     ENUM('ahorro','corriente','otro') NOT NULL,
    titular         VARCHAR(150) NOT NULL,
    descripcion     TEXT         NULL,
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      TIMESTAMP    NULL,
    updated_at      TIMESTAMP    NULL
);
```

**Modelo Eloquent**: `app/Models/CuentaBancoEmpresa.php`

```php
protected $table    = 'cuentas_banco_empresa';
protected $fillable = ['banco','numero_cuenta','tipo_cuenta','titular','descripcion','activo'];
protected $casts    = ['activo' => 'boolean'];

public function scopeActivas($query) { return $query->where('activo', true); }
```

### Tabla: `config_tarifa_categoria29`

```sql
CREATE TABLE config_tarifa_categoria29 (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    monto_registro              DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    descuento_venta_masiva      DECIMAL(5,2)  NOT NULL DEFAULT 0.00,  -- porcentaje 0-100
    cantidad_minima_descuento   INT UNSIGNED  NOT NULL DEFAULT 1,
    created_at                  TIMESTAMP     NULL,
    updated_at                  TIMESTAMP     NULL
);
```

**Modelo Eloquent**: `app/Models/ConfigTarifaCategoria29.php`

```php
protected $table    = 'config_tarifa_categoria29';
protected $fillable = ['monto_registro','descuento_venta_masiva','cantidad_minima_descuento'];
protected $casts    = [
    'monto_registro'           => 'decimal:2',
    'descuento_venta_masiva'   => 'decimal:2',
    'cantidad_minima_descuento'=> 'integer',
];

// Retorna el único registro o un objeto con defaults si no existe
public static function vigente(): self
{
    return static::first() ?? new static([
        'monto_registro'            => 0,
        'descuento_venta_masiva'    => 0,
        'cantidad_minima_descuento' => 1,
    ]);
}
```

### Tabla: `pagos_registro_talento`

```sql
CREATE TABLE pagos_registro_talento (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_item          BIGINT UNSIGNED NOT NULL,
    id_user          BIGINT UNSIGNED NOT NULL,
    transaction_id   VARCHAR(100)    NOT NULL,   -- pnRef de Cardnet
    monto_pagado     DECIMAL(10,2)   NOT NULL,
    estatus          VARCHAR(20)     NOT NULL DEFAULT 'aprobado',  -- siempre aprobado al crear
    notas            TEXT            NULL,
    created_at       TIMESTAMP       NULL,
    updated_at       TIMESTAMP       NULL,

    CONSTRAINT fk_prt_item FOREIGN KEY (id_item) REFERENCES items(id_item) ON DELETE CASCADE,
    CONSTRAINT fk_prt_user FOREIGN KEY (id_user) REFERENCES users(id)      ON DELETE CASCADE
);
```

> **Nota**: A diferencia del flujo manual anterior, no hay `comprobante_path` ni estados `pendiente`/`rechazado`. El registro se crea únicamente cuando Cardnet aprueba el pago (`success = true`), por lo que `estatus` siempre es `'aprobado'` al momento de la inserción. Se mantiene el campo por extensibilidad futura (ej. reembolsos).

**Modelo Eloquent**: `app/Models/PagoRegistroTalento.php`

```php
protected $table    = 'pagos_registro_talento';
protected $fillable = ['id_item','id_user','transaction_id','monto_pagado','estatus','notas'];

public function item() { return $this->belongsTo(Item::class, 'id_item', 'id_item'); }
public function user() { return $this->belongsTo(User::class, 'id_user'); }
```

### Relación en `Item`

```php
// En app/Models/Item.php
public function pagoRegistro()
{
    return $this->hasOne(PagoRegistroTalento::class, 'id_item', 'id_item');
}
```

### Sesión temporal durante flujo de pago

Durante el flujo de pago, se usa la sesión de Laravel para persistir los datos del formulario:

- `session('talento_pendiente_data')`: array con los campos validados del formulario (sin archivos).
- `session('talento_pendiente_files')`: array con rutas temporales de los archivos subidos.

Los archivos temporales se almacenan en `storage/app/temp/{uuid}/` y se mueven a `public/storage/talentos/{id_item}/` al confirmar el pago exitoso. Si el pago falla, los archivos temporales permanecen en sesión para el siguiente intento.

### Rutas nuevas

```php
// Flujo de pago de talento (auth requerido)
Route::middleware(['auth'])->group(function () {
    Route::get('/talento/pago',  [TalentoRegistroPagoController::class, 'mostrarPago'])->name('talento.pago.show');
    Route::post('/talento/pago', [TalentoRegistroPagoController::class, 'procesarPago'])
        ->middleware('throttle.sensitive:5,1')
        ->name('talento.pago.procesar');
});

// Admin: cuentas bancarias (superadmin) — informativas
Route::middleware(['auth','superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/cuentas-banco',               [AdminCuentaBancoController::class, 'index'])->name('cuentas.index');
    Route::post('/cuentas-banco',              [AdminCuentaBancoController::class, 'store'])->name('cuentas.store');
    Route::put('/cuentas-banco/{id}',          [AdminCuentaBancoController::class, 'update'])->name('cuentas.update');
    Route::delete('/cuentas-banco/{id}',       [AdminCuentaBancoController::class, 'destroy'])->name('cuentas.destroy');
    Route::patch('/cuentas-banco/{id}/toggle', [AdminCuentaBancoController::class, 'toggleActivo'])->name('cuentas.toggle');

    // Config tarifa
    Route::get('/config-tarifa', [AdminConfigTarifaController::class, 'show'])->name('config_tarifa.show');
    Route::put('/config-tarifa', [AdminConfigTarifaController::class, 'update'])->name('config_tarifa.update');
});
```

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Registro de talento categoría 29 siempre redirige a pago

*For any* usuario autenticado que envíe el formulario de talento con `id_categoria_item = 29`, el sistema no debe crear el ítem directamente sino redirigir a la vista de pago con tarjeta, y no debe existir ningún registro nuevo en la tabla `items` hasta que Cardnet apruebe el cobro.

**Validates: Requirements 1.1**

### Property 2: Vista de pago muestra monto correcto y tarjetas del usuario

*For any* valor de `monto_registro` en `config_tarifa_categoria29` y cualquier conjunto de tarjetas guardadas del usuario en `tarjetas_pagos`, la vista de pago debe mostrar el monto exacto de `config_tarifa_categoria29` y las tarjetas activas del usuario.

**Validates: Requirements 1.2**

### Property 3: Pago aprobado crea ítem con estatus=1 y registro de pago

*For any* usuario autenticado con datos de talento válidos en sesión y un cobro Cardnet que retorna `success = true`, el sistema debe: (a) crear el ítem con `estatus = 1`, (b) crear un registro en `pagos_registro_talento` con el `transaction_id` de Cardnet y el `id_item` correcto.

**Validates: Requirements 1.3**

### Property 4: Pago rechazado no crea ítem ni registro de pago

*For any* cobro Cardnet que retorna `success = false`, el sistema debe mostrar el error y no debe crear ningún registro en `items` ni en `pagos_registro_talento`.

**Validates: Requirements 1.4**

### Property 5: Datos del formulario persisten en sesión durante el flujo de pago

*For any* conjunto de datos válidos del formulario de talento enviados a `POST /talento/agregar` con `id_categoria_item = 29`, los datos deben estar disponibles en la sesión (`talento_pendiente_data`) inmediatamente después de la redirección a la vista de pago.

**Validates: Requirements 1.7**

### Property 6: CRUD de cuentas bancarias — creación con datos válidos

*For any* conjunto de datos válidos (`banco`, `numero_cuenta`, `tipo_cuenta`, `titular`), un SuperAdmin puede crear un registro en `cuentas_banco_empresa` y el registro debe existir en la base de datos con los valores exactos enviados.

**Validates: Requirements 2.4**

### Property 7: CRUD de cuentas bancarias — actualización y eliminación

*For any* cuenta bancaria existente, (a) actualizarla con datos válidos debe reflejar los nuevos valores en la base de datos, y (b) eliminarla debe resultar en que el registro ya no exista.

**Validates: Requirements 2.5, 2.6**

### Property 8: Toggle de cuenta bancaria es idempotente en pares

*For any* cuenta bancaria, aplicar `toggleActivo` dos veces consecutivas debe devolver el campo `activo` a su valor original.

**Validates: Requirements 2.7**

### Property 9: Acceso no autorizado a rutas de gestión retorna 403

*For any* usuario que no sea SuperAdmin (incluyendo usuarios no autenticados, usuarios normales y admins sin `is_super_admin`), cualquier intento de acceder a las rutas de gestión de cuentas bancarias o config de tarifas debe retornar HTTP 403.

**Validates: Requirements 2.8**

### Property 10: Validación de campos obligatorios en cuentas bancarias y config tarifa

*For any* solicitud de creación o edición de `CuentaBanco` que omita al menos uno de los campos obligatorios (`banco`, `numero_cuenta`, `tipo_cuenta`, `titular`), o cualquier solicitud de actualización de `ConfigTarifa` con `monto_registro < 0`, `descuento_venta_masiva` fuera de [0,100] o `cantidad_minima_descuento < 1`, el sistema debe retornar errores de validación sin guardar el registro.

**Validates: Requirements 2.9, 3.5**

### Property 11: ConfigTarifa es singleton — siempre existe exactamente un registro

*For any* secuencia de operaciones de actualización sobre `config_tarifa_categoria29`, la tabla debe contener exactamente un registro. El método `ConfigTarifaCategoria29::vigente()` debe retornar siempre un objeto con valores numéricos válidos (usando defaults si la tabla está vacía).

**Validates: Requirements 3.2, 3.6**

### Property 12: Actualización de ConfigTarifa persiste valores correctos

*For any* conjunto de valores válidos (`monto_registro >= 0`, `descuento_venta_masiva` en [0,100], `cantidad_minima_descuento >= 1`), actualizar la ConfigTarifa debe resultar en que `ConfigTarifaCategoria29::vigente()` retorne exactamente esos valores.

**Validates: Requirements 3.4**

### Property 13: Descuento por volumen se aplica si y solo si cantidad >= mínimo

*For any* talento de categoría 29 con `tipo_trans = 1`, cualquier valor de `cantidad`, y la `ConfigTarifa` vigente: si `cantidad >= cantidad_minima_descuento`, el campo `descuento` en `items_intencion_compra` debe ser `valor × (descuento_venta_masiva / 100)`; si `cantidad < cantidad_minima_descuento`, el descuento debe ser 0 (o el descuento propio del ítem).

**Validates: Requirements 4.2, 4.3, 4.6**

### Property 14: transaction_id de Cardnet se guarda correctamente

*For any* cobro Cardnet aprobado, el `transaction_id` (campo `pnRef` de la respuesta) guardado en `pagos_registro_talento` debe ser exactamente el valor retornado por `CardnetProvider::cobrar()`.

**Validates: Requirements 1.3, 5.1**

---

## Error Handling

### Flujo de pago de talento

| Situación | Comportamiento |
|---|---|
| Sesión expirada o sin `talento_pendiente_data` | Redirigir a `/talentos/crear` con mensaje de error |
| Tarjeta no encontrada o no pertenece al usuario | Redirect back con error descriptivo |
| Cardnet retorna `success = false` | Redirect back con el mensaje de error de Cardnet, NO se crea el ítem |
| Error de red / timeout con Cardnet | Redirect back con mensaje genérico, log del error, NO se crea el ítem |
| Error al mover archivo temporal tras pago aprobado | `DB::rollBack()`, intentar anular transacción Cardnet, log de error, mensaje genérico al usuario |
| `config_tarifa_categoria29` vacía | Usar objeto con defaults (monto=0), no lanzar excepción |

### CRUD de cuentas bancarias y config tarifa

| Situación | Comportamiento |
|---|---|
| Campos obligatorios faltantes | HTTP 422 con `errors` en JSON |
| Valores fuera de rango (config tarifa) | HTTP 422 con `errors` en JSON |
| Cuenta bancaria no encontrada (update/delete) | HTTP 404 |
| Usuario no SuperAdmin | HTTP 403 (middleware `superadmin`) |

### Archivos temporales

Los archivos temporales en `storage/app/temp/` deben limpiarse si el flujo falla definitivamente. Se recomienda un comando Artisan de limpieza periódica (`php artisan talento:limpiar-temp`) para archivos con más de 24 horas de antigüedad.

---

## Testing Strategy

### Enfoque dual: Unit tests + Property-based tests

Se usa un enfoque complementario:
- **Unit tests**: verifican ejemplos concretos, casos de integración y edge cases específicos.
- **Property-based tests**: verifican propiedades universales con entradas generadas aleatoriamente.

Para PHP/Laravel se usa **[Pest PHP](https://pestphp.com/)** con el plugin **[pest-plugin-faker](https://github.com/pestphp/pest-plugin-faker)** para generación de datos. Para property-based testing en PHP se implementan generadores manuales con Faker dentro de Pest (bucles de N iteraciones con datos aleatorios). Cardnet se mockea en todos los tests.

Todos los tests usan `DatabaseTransactions` (no `RefreshDatabase`), consistente con el proyecto.

### Configuración de property tests

- Mínimo **100 iteraciones** por property test.
- Cada test lleva un comentario con el tag: `Feature: talento-registro-pago-config, Property N: <texto>`.

### Tests unitarios (ejemplos y edge cases)

```
tests/Feature/TalentoRegistroPago/
  FlujoPagoTest.php
    - test_redirige_a_pago_cuando_categoria_es_29
    - test_no_redirige_cuando_categoria_no_es_29
    - test_vista_pago_muestra_monto_registro
    - test_pago_aprobado_crea_item_con_estatus_1
    - test_pago_aprobado_crea_registro_pago_con_transaction_id
    - test_pago_rechazado_no_crea_item
    - test_pago_rechazado_muestra_error_cardnet
    - test_sesion_expirada_redirige_a_crear
    - test_tarjeta_invalida_retorna_error

  AdminCuentaBancoTest.php
    - test_superadmin_puede_crear_cuenta
    - test_superadmin_puede_editar_cuenta
    - test_superadmin_puede_eliminar_cuenta
    - test_superadmin_puede_toggle_activo
    - test_admin_normal_recibe_403
    - test_usuario_normal_recibe_403
    - test_campos_obligatorios_retornan_422

  AdminConfigTarifaTest.php
    - test_superadmin_puede_actualizar_config
    - test_valores_invalidos_retornan_422
    - test_vigente_retorna_defaults_si_tabla_vacia
    - test_singleton_no_crea_segundo_registro

  DescuentoVolumenTest.php
    - test_descuento_aplicado_cuando_cantidad_alcanza_minimo
    - test_sin_descuento_cuando_cantidad_menor_al_minimo
    - test_detalle_talento_muestra_mensaje_descuento
    - test_sin_mensaje_cuando_config_vacia
```

### Property-based tests

```
tests/Feature/TalentoRegistroPago/Properties/
  PropiedadesPagoTest.php
```

Cada property test sigue el patrón:

```php
it('Property 3: pago aprobado siempre crea talento con estatus=1', function () {
    // Feature: talento-registro-pago-config, Property 3: pago aprobado crea ítem con estatus=1
    repeat(100, function () {
        $user = User::factory()->create();
        $tarjeta = TarjetaPago::factory()->create(['id_user' => $user->id]);
        $this->actingAs($user);

        // Mock Cardnet: siempre aprueba
        $transactionId = Str::random(20);
        $this->mock(PagoService::class, fn($m) =>
            $m->shouldReceive('cobrarTarjeta')->andReturn([
                'success' => true,
                'transaction_id' => $transactionId,
                'approval_code' => 'ABC123',
                'status' => 'approved',
                'raw' => [],
                'error' => null,
            ])
        );

        $datos = generarDatosTalentoAleatorios();
        session(['talento_pendiente_data' => $datos]);

        $response = $this->post(route('talento.pago.procesar'), [
            'id_tarjeta' => $tarjeta->id_tarjeta,
            'cvv' => '123',
        ]);

        $item = Item::latest('id_item')->first();
        expect($item->estatus)->toBe(1);
        expect(PagoRegistroTalento::where('id_item', $item->id_item)
            ->where('transaction_id', $transactionId)->exists())->toBeTrue();
    });
})->uses(DatabaseTransactions::class);
```

Lista de property tests a implementar (uno por propiedad testeable):

| Property | Test name |
|---|---|
| 1 | `prop_registro_cat29_siempre_redirige_a_pago` |
| 2 | `prop_vista_pago_muestra_monto_correcto` |
| 3 | `prop_pago_aprobado_crea_item_estatus_1_y_registro` |
| 4 | `prop_pago_rechazado_no_crea_item` |
| 5 | `prop_datos_formulario_persisten_en_sesion` |
| 6 | `prop_crud_cuentas_creacion_datos_validos` |
| 7 | `prop_crud_cuentas_actualizacion_y_eliminacion` |
| 8 | `prop_toggle_cuenta_bancaria_es_idempotente` |
| 9 | `prop_acceso_no_autorizado_retorna_403` |
| 10 | `prop_validacion_campos_obligatorios` |
| 11 | `prop_config_tarifa_singleton` |
| 12 | `prop_actualizacion_config_tarifa_persiste` |
| 13 | `prop_descuento_volumen_segun_cantidad` |
| 14 | `prop_transaction_id_cardnet_guardado_correctamente` |
