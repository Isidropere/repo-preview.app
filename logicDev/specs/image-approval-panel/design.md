# Design Document: Image Approval Panel

## Overview

The Image Approval Panel adds a moderation layer to all image uploads in CambialóRD. Every `ImagenItem` and user profile photo starts in `pendiente` state and is hidden from public views until an admin approves it. Admins work through a dedicated panel at `/admin/imagenes` where they can approve or reject images individually or in bulk, and the platform notifies users of the outcome via the existing `Message`-based notification system.

The feature touches four layers:

1. **Database** — two migrations adding `estado`/`motivo_rechazo` columns to `imagenes_item` and `users`.
2. **Models** — `ImagenItem` and `User` expose the new fields as fillable.
3. **Service** — `ImageModerationService` encapsulates all approval/rejection logic and notification dispatch.
4. **HTTP + Views** — `AdminImagenesController` + a single Blade view at `resources/views/admin/imagenes/index.blade.php`.

---

## Architecture

```
Browser (Admin)
    │
    ▼
AdminImagenesController          ← routes/web.php (auth + admin middleware)
    │
    ▼
ImageModerationService           ← pure PHP, no HTTP concerns
    ├── ImagenItem::query()      ← Eloquent
    ├── User::query()            ← Eloquent
    └── Message::create()        ← persists notification
        + event(NuevaNotificacion) ← real-time broadcast

Public views (Blade)
    └── ImageDisplay component   ← checks estado before rendering URL
```

No new queue jobs are introduced. Notification dispatch is synchronous (same as the rest of the app). Bulk approval iterates in PHP and dispatches one notification per affected owner — acceptable for the expected backlog size.

---

## Components and Interfaces

### 1. Migrations

**`add_estado_to_imagenes_item`**
- Adds `estado` ENUM(`pendiente`, `aprobado`, `rechazado`) NOT NULL DEFAULT `pendiente`
- Adds `motivo_rechazo` TEXT NULL

**`add_foto_perfil_estado_to_users`**
- Adds `foto_perfil_estado` ENUM(`pendiente`, `aprobado`, `rechazado`) NOT NULL DEFAULT `pendiente`
- Adds `foto_perfil_motivo_rechazo` TEXT NULL

### 2. Models

**`ImagenItem`** — adds to `$fillable`: `estado`, `motivo_rechazo`

**`User`** — adds to `$fillable`: `foto_perfil_estado`, `foto_perfil_motivo_rechazo`

### 3. ImageModerationService

```php
namespace App\Services;

class ImageModerationService
{
    // Approve a single item image. Returns the updated ImagenItem.
    public function aprobarImagenItem(int $idImagen): ImagenItem;

    // Reject a single item image with a reason. Returns the updated ImagenItem.
    public function rechazarImagenItem(int $idImagen, string $motivo): ImagenItem;

    // Approve a single profile photo. Returns the updated User.
    public function aprobarFotoPerfil(int $userId): User;

    // Reject a single profile photo with a reason. Returns the updated User.
    public function rechazarFotoPerfil(int $userId, string $motivo): User;

    // Approve all pending item images. Returns count of approved records.
    public function aprobarTodasImagenesItem(): int;

    // Approve all pending profile photos. Returns count of approved records.
    public function aprobarTodasFotosPerfil(): int;
}
```

Each method:
- Loads the record (throws `ModelNotFoundException` on missing ID → controller catches → 404)
- Updates the estado/motivo columns
- Calls the private `notificar(int $userId, string $mensaje)` helper which:
  - Creates a `Message` record with `id_emisor = null` (system), `id_receptor = $userId`
  - Fires `event(new NuevaNotificacion($mensaje, $userId))`

### 4. AdminImagenesController

```php
namespace App\Http\Controllers\Admin;

class AdminImagenesController extends Controller
{
    public function index(): View;                          // GET  /admin/imagenes
    public function aprobarItem(Request $r, int $id): RedirectResponse;   // POST /admin/imagenes/items/{id}/aprobar
    public function rechazarItem(Request $r, int $id): RedirectResponse;  // POST /admin/imagenes/items/{id}/rechazar
    public function aprobarPerfil(Request $r, int $id): RedirectResponse; // POST /admin/imagenes/perfiles/{id}/aprobar
    public function rechazarPerfil(Request $r, int $id): RedirectResponse;// POST /admin/imagenes/perfiles/{id}/rechazar
    public function aprobarTodosItems(Request $r): RedirectResponse;      // POST /admin/imagenes/items/aprobar-todas
    public function aprobarTodosPerfiles(Request $r): RedirectResponse;   // POST /admin/imagenes/perfiles/aprobar-todas
}
```

### 5. Routes (added to the `auth + admin` group)

```php
Route::prefix('imagenes')->name('admin.imagenes.')->group(function () {
    Route::get('/',                          [AdminImagenesController::class, 'index'])->name('index');
    Route::post('/items/{id}/aprobar',       [AdminImagenesController::class, 'aprobarItem'])->name('items.aprobar');
    Route::post('/items/{id}/rechazar',      [AdminImagenesController::class, 'rechazarItem'])->name('items.rechazar');
    Route::post('/items/aprobar-todas',      [AdminImagenesController::class, 'aprobarTodosItems'])->name('items.aprobarTodas');
    Route::post('/perfiles/{id}/aprobar',    [AdminImagenesController::class, 'aprobarPerfil'])->name('perfiles.aprobar');
    Route::post('/perfiles/{id}/rechazar',   [AdminImagenesController::class, 'rechazarPerfil'])->name('perfiles.rechazar');
    Route::post('/perfiles/aprobar-todas',   [AdminImagenesController::class, 'aprobarTodosPerfiles'])->name('perfiles.aprobarTodas');
});
```

### 6. Blade View — `resources/views/admin/imagenes/index.blade.php`

Extends `layouts.app`. Inline CSS matching the existing admin panel style (no Tailwind). Two sections:

- **Imágenes de Items** — table with thumbnail, item name, upload date, type, approve/reject buttons
- **Fotos de Perfil** — table with avatar thumbnail, username, upload date, approve/reject buttons

Each section has a count badge and an "Aprobar todas" button. Rejection triggers a small inline form (or `<details>` element) to capture `motivo_rechazo` before submitting.

### 7. ImageDisplay Component Update

`app/View/Components/ImageDisplay.php` and its Blade template are updated to check `$item->imagenes` estado before rendering the `<img>` src. If `estado !== 'aprobado'`, the placeholder path is used instead.

---

## Data Models

### `imagenes_item` (updated)

| Column               | Type                                    | Notes                        |
|----------------------|-----------------------------------------|------------------------------|
| id_imagen            | INT PK                                  | existing                     |
| nombre               | VARCHAR                                 | existing                     |
| extension            | VARCHAR                                 | existing                     |
| id_item              | INT FK → items                          | existing                     |
| orden_visualizacion  | INT                                     | existing                     |
| ruta                 | VARCHAR                                 | existing                     |
| tipo                 | VARCHAR                                 | existing                     |
| **estado**           | ENUM('pendiente','aprobado','rechazado')| **new**, DEFAULT 'pendiente' |
| **motivo_rechazo**   | TEXT NULL                               | **new**                      |

### `users` (updated)

| Column                        | Type                                    | Notes                        |
|-------------------------------|-----------------------------------------|------------------------------|
| ...existing columns...        |                                         |                              |
| **foto_perfil_estado**        | ENUM('pendiente','aprobado','rechazado')| **new**, DEFAULT 'pendiente' |
| **foto_perfil_motivo_rechazo**| TEXT NULL                               | **new**                      |

### Estado transition diagram

```
[upload] ──► pendiente ──► aprobado
                      └──► rechazado
```

There is no transition back from `aprobado` or `rechazado` in this feature scope. Re-uploading a new image creates a new record starting at `pendiente`.

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: New images default to pendiente

*For any* newly created `ImagenItem` record (regardless of other field values), its `estado` attribute SHALL equal `'pendiente'`.

**Validates: Requirements 1.1, 1.3**

---

### Property 2: New users default to pendiente profile photo estado

*For any* newly created `User` record that has a `foto_perfil` value, its `foto_perfil_estado` attribute SHALL equal `'pendiente'`.

**Validates: Requirements 2.1, 2.3**

---

### Property 3: Non-approved images render as placeholder

*For any* `ImagenItem` whose `estado` is `'pendiente'` or `'rechazado'`, the `ImageDisplay` component SHALL render the placeholder URL, not the image's `ruta`. Conversely, when `estado` is `'aprobado'`, the component SHALL render the actual `ruta`.

**Validates: Requirements 3.1, 3.3, 3.4**

---

### Property 4: Non-approved profile photos render as default avatar

*For any* `User` whose `foto_perfil_estado` is `'pendiente'` or `'rechazado'`, the profile photo rendering logic SHALL output the default avatar URL, not `foto_perfil`. When `foto_perfil_estado` is `'aprobado'`, the actual `foto_perfil` URL SHALL be rendered.

**Validates: Requirements 3.2, 3.5**

---

### Property 5: Panel shows all and only pending images

*For any* database state, the panel index response SHALL contain exactly the set of `ImagenItem` records with `estado = 'pendiente'` and exactly the set of `User` records with `foto_perfil_estado = 'pendiente'` — no more, no less.

**Validates: Requirements 4.2, 4.4**

---

### Property 6: Panel renders required metadata for each pending image

*For any* pending `ImagenItem` displayed in the panel, the rendered HTML SHALL include the item name, the image thumbnail (or placeholder), the upload date, and the image type. For any pending profile photo, the rendered HTML SHALL include the username and the photo thumbnail.

**Validates: Requirements 4.3**

---

### Property 7: Approval sets estado to aprobado

*For any* `ImagenItem` in `pendiente` state, calling the approval action SHALL set its `estado` to `'aprobado'` and persist the change. The same holds for any `User`'s `foto_perfil_estado`.

**Validates: Requirements 5.1, 5.2**

---

### Property 8: Approval dispatches a notification to the owner

*For any* approved `ImagenItem`, a `Message` record SHALL be created with `id_receptor` equal to the item's owner `id_user`, and the message text SHALL contain the item's name. For any approved profile photo, a `Message` record SHALL be created with `id_receptor` equal to the user's `id`.

**Validates: Requirements 5.3, 5.4, 8.1, 8.3**

---

### Property 9: Rejection requires non-empty motivo

*For any* rejection request where `motivo_rechazo` is empty or composed entirely of whitespace, the system SHALL return a validation error and SHALL NOT change the `estado` of the target record.

**Validates: Requirements 6.1, 6.2, 9.3**

---

### Property 10: Valid rejection sets estado to rechazado and stores motivo

*For any* `ImagenItem` in `pendiente` state and any non-empty `motivo_rechazo` string, the rejection action SHALL set `estado` to `'rechazado'`, persist `motivo_rechazo`, and leave no other fields changed. The same holds for `User.foto_perfil_estado` and `foto_perfil_motivo_rechazo`.

**Validates: Requirements 6.3, 6.4**

---

### Property 11: Rejection dispatches a notification containing the motivo

*For any* rejected `ImagenItem` with a given `motivo_rechazo`, a `Message` record SHALL be created for the item owner whose `mensaje` text contains the `motivo_rechazo` string. The same holds for rejected profile photos.

**Validates: Requirements 6.5, 6.6, 8.2, 8.4**

---

### Property 12: Bulk approval sets all pending images to aprobado

*For any* set of `ImagenItem` records with `estado = 'pendiente'`, the "Aprobar todas" action SHALL set every one of them to `'aprobado'` and dispatch a notification to each distinct item owner. No non-pending records SHALL be modified. The same holds for bulk profile photo approval.

**Validates: Requirements 7.2, 7.3**

---

## Error Handling

| Scenario | Behavior |
|---|---|
| `id_imagen` not found | `ModelNotFoundException` → controller returns 404 |
| `id` (user) not found | `ModelNotFoundException` → controller returns 404 |
| Empty `motivo_rechazo` on rejection | Laravel validation error → redirect back with `errors` bag |
| Unauthenticated access to `/admin/imagenes` | `auth` middleware → redirect to login |
| Non-admin access to `/admin/imagenes` | `admin` middleware → 403 |
| DB write failure during bulk approval | Exception propagates → 500; partial approvals are committed (no transaction rollback needed — each approval is independent and idempotent) |
| Notification dispatch failure | Logged as warning; does not roll back the estado change (same pattern as `NegociacionService::crearMensaje`) |

---

## Testing Strategy

### Unit / Feature Tests (PHPUnit + Laravel TestCase)

Focus on specific examples, edge cases, and integration points:

- `GET /admin/imagenes` returns 200 for admin, 302 for guest, 403 for regular user (Requirement 9.1, 9.2)
- Panel shows "sin imágenes pendientes" message when no pending records exist (Requirement 4.5)
- "Aprobar todas" response includes the count of approved images (Requirement 7.4)
- `GET /admin/imagenes` with no pending images in one section shows the empty-state message for that section
- Approval of a non-existent `id_imagen` returns 404 (Requirement 9.4)
- "Aprobar todas" button is present in the rendered HTML (Requirement 7.1)

### Property-Based Tests (PHPUnit + [eris](https://github.com/giorgiosironi/eris) or [php-quickcheck](https://github.com/steos/php-quickcheck))

Each property test runs a minimum of **100 iterations** with randomly generated inputs.

Tag format: `Feature: image-approval-panel, Property {N}: {property_text}`

| Property | Test description |
|---|---|
| P1 | Generate random ImagenItem field sets; assert `estado` defaults to `'pendiente'` |
| P2 | Generate random User records with foto_perfil; assert `foto_perfil_estado` defaults to `'pendiente'` |
| P3 | Generate ImagenItem with random estado; assert ImageDisplay renders placeholder iff estado ≠ 'aprobado' |
| P4 | Generate User with random foto_perfil_estado; assert profile photo render logic returns default avatar iff estado ≠ 'aprobado' |
| P5 | Seed random mix of pending/approved/rejected images; assert panel index contains exactly the pending ones |
| P6 | Seed random pending ImagenItem records; assert each row in panel HTML contains item name, date, type |
| P7 | Generate random pending ImagenItem; call approve; assert estado = 'aprobado' in DB |
| P8 | Generate random pending ImagenItem; call approve; assert Message record exists for owner with item name in mensaje |
| P9 | Generate random whitespace-only motivo strings; call reject; assert validation error and estado unchanged |
| P10 | Generate random pending ImagenItem + non-empty motivo; call reject; assert estado = 'rechazado' and motivo persisted |
| P11 | Generate random pending ImagenItem + non-empty motivo; call reject; assert Message record for owner contains motivo text |
| P12 | Seed N random pending ImagenItem records; call bulk approve; assert all N have estado = 'aprobado' and N Message records created |
