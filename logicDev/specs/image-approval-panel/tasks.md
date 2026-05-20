# Implementation Plan: Image Approval Panel

## Overview

Implement the image moderation layer for CambialóRD: database migrations, model updates, service logic, admin controller, Blade view, and public display gating.

## Tasks

- [x] 1. Database migrations
  - [x] 1.1 Create migration `add_estado_to_imagenes_item`
    - Add `estado` ENUM(`pendiente`,`aprobado`,`rechazado`) NOT NULL DEFAULT `pendiente`
    - Add `motivo_rechazo` TEXT NULL
    - _Requirements: 1.1, 1.2_
  - [x] 1.2 Create migration `add_foto_perfil_estado_to_users`
    - Add `foto_perfil_estado` ENUM(`pendiente`,`aprobado`,`rechazado`) NOT NULL DEFAULT `pendiente`
    - Add `foto_perfil_motivo_rechazo` TEXT NULL
    - _Requirements: 2.1, 2.2_

- [x] 2. Update models
  - [x] 2.1 Update `ImagenItem` model
    - Add `estado` and `motivo_rechazo` to `$fillable`
    - Add `$attributes = ['estado' => 'pendiente']` default so new records start as `pendiente`
    - _Requirements: 1.3, 1.4_
  - [ ]* 2.2 Write property test for ImagenItem default estado (Property 1)
    - **Property 1: New images default to pendiente**
    - **Validates: Requirements 1.1, 1.3**
  - [x] 2.3 Update `User` model
    - Add `foto_perfil_estado` and `foto_perfil_motivo_rechazo` to `$fillable`
    - _Requirements: 2.3, 2.4_
  - [ ]* 2.4 Write property test for User default foto_perfil_estado (Property 2)
    - **Property 2: New users default to pendiente profile photo estado**
    - **Validates: Requirements 2.1, 2.3**

- [x] 3. Implement ImageModerationService
  - [x] 3.1 Create `app/Services/ImageModerationService.php`
    - Implement `aprobarImagenItem(int $idImagen): ImagenItem`
    - Implement `rechazarImagenItem(int $idImagen, string $motivo): ImagenItem`
    - Implement `aprobarFotoPerfil(int $userId): User`
    - Implement `rechazarFotoPerfil(int $userId, string $motivo): User`
    - Implement `aprobarTodasImagenesItem(): int`
    - Implement `aprobarTodasFotosPerfil(): int`
    - Private `notificar(int $userId, string $mensaje)` helper: creates `Message` record with `id_emisor = null` and fires `NuevaNotificacion` event
    - Throw `ModelNotFoundException` for missing IDs
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 6.3, 6.4, 6.5, 6.6, 7.2, 7.3, 8.1, 8.2, 8.3, 8.4, 8.5_
  - [ ]* 3.2 Write property test for approval sets estado to aprobado (Property 7)
    - **Property 7: Approval sets estado to aprobado**
    - **Validates: Requirements 5.1, 5.2**
  - [ ]* 3.3 Write property test for approval dispatches notification (Property 8)
    - **Property 8: Approval dispatches a notification to the owner**
    - **Validates: Requirements 5.3, 5.4, 8.1, 8.3**
  - [ ]* 3.4 Write property test for rejection requires non-empty motivo (Property 9)
    - **Property 9: Rejection requires non-empty motivo**
    - **Validates: Requirements 6.1, 6.2, 9.3**
  - [ ]* 3.5 Write property test for valid rejection sets estado and stores motivo (Property 10)
    - **Property 10: Valid rejection sets estado to rechazado and stores motivo**
    - **Validates: Requirements 6.3, 6.4**
  - [ ]* 3.6 Write property test for rejection dispatches notification with motivo (Property 11)
    - **Property 11: Rejection dispatches a notification containing the motivo**
    - **Validates: Requirements 6.5, 6.6, 8.2, 8.4**
  - [ ]* 3.7 Write property test for bulk approval (Property 12)
    - **Property 12: Bulk approval sets all pending images to aprobado**
    - **Validates: Requirements 7.2, 7.3**

- [ ] 4. Checkpoint — Ensure all tests pass, ask the user if questions arise.

- [x] 5. Create AdminImagenesController
  - [x] 5.1 Create `app/Http/Controllers/Admin/AdminImagenesController.php`
    - Inject `ImageModerationService`
    - `index()`: query pending `ImagenItem` (with item relation) and pending `User` records, pass to view
    - `aprobarItem(Request $r, int $id)`: call service, redirect back with success flash
    - `rechazarItem(Request $r, int $id)`: validate `motivo_rechazo` required|string, call service, redirect back
    - `aprobarPerfil(Request $r, int $id)`: call service, redirect back with success flash
    - `rechazarPerfil(Request $r, int $id)`: validate `motivo_rechazo` required|string, call service, redirect back
    - `aprobarTodosItems(Request $r)`: call service, redirect back with count in flash
    - `aprobarTodosPerfiles(Request $r)`: call service, redirect back with count in flash
    - Catch `ModelNotFoundException` → abort(404)
    - _Requirements: 4.1, 4.2, 5.1, 5.2, 6.1, 6.2, 7.1, 7.4, 9.1, 9.3, 9.4, 9.5_
  - [ ]* 5.2 Write feature tests for AdminImagenesController
    - Test `GET /admin/imagenes` returns 200 for admin, 302 for guest, 403 for regular user
    - Test panel shows empty-state message when no pending records exist
    - Test approval of non-existent id returns 404
    - Test rejection without motivo returns validation error
    - Test "Aprobar todas" flash includes count
    - _Requirements: 4.5, 9.1, 9.2, 9.4_
  - [ ]* 5.3 Write property test for panel shows only pending images (Property 5)
    - **Property 5: Panel shows all and only pending images**
    - **Validates: Requirements 4.2, 4.4**

- [x] 6. Register routes
  - [x] 6.1 Add the `imagenes` route group inside the existing `auth + admin` middleware group in `routes/web.php`
    - `GET /admin/imagenes` → `index` (name: `admin.imagenes.index`)
    - `POST /admin/imagenes/items/{id}/aprobar` → `aprobarItem`
    - `POST /admin/imagenes/items/{id}/rechazar` → `rechazarItem`
    - `POST /admin/imagenes/items/aprobar-todas` → `aprobarTodosItems`
    - `POST /admin/imagenes/perfiles/{id}/aprobar` → `aprobarPerfil`
    - `POST /admin/imagenes/perfiles/{id}/rechazar` → `rechazarPerfil`
    - `POST /admin/imagenes/perfiles/aprobar-todas` → `aprobarTodosPerfiles`
    - _Requirements: 4.1, 9.1, 9.5_

- [x] 7. Create Blade view
  - [x] 7.1 Create `resources/views/admin/imagenes/index.blade.php`
    - Extend `layouts.app`, match existing admin panel inline CSS style
    - Section "Imágenes de Items": count badge, "Aprobar todas" form button, table with thumbnail/item name/upload date/type/approve+reject buttons
    - Section "Fotos de Perfil": count badge, "Aprobar todas" form button, table with avatar thumbnail/username/upload date/approve+reject buttons
    - Rejection uses a `<details>` inline form to capture `motivo_rechazo` before submitting
    - Empty-state message per section when no pending images
    - All state-changing forms use `@csrf` and `method="POST"`
    - Display session flash messages for success/error feedback
    - _Requirements: 4.2, 4.3, 4.4, 4.5, 5.5, 6.7, 7.1, 7.4, 9.5_
  - [ ]* 7.2 Write property test for panel renders required metadata (Property 6)
    - **Property 6: Panel renders required metadata for each pending image**
    - **Validates: Requirements 4.3**

- [x] 8. Update public image display logic
  - [x] 8.1 Update `app/View/Components/ImageDisplay.php` and its Blade template
    - Check `$imagen->estado` before rendering: if not `aprobado`, render placeholder path instead of `ruta`
    - _Requirements: 3.1, 3.3, 3.4_
  - [ ]* 8.2 Write property test for non-approved images render as placeholder (Property 3)
    - **Property 3: Non-approved images render as placeholder**
    - **Validates: Requirements 3.1, 3.3, 3.4**
  - [x] 8.3 Update profile photo rendering logic (wherever `foto_perfil` is output in Blade views)
    - Check `foto_perfil_estado`; if not `aprobado`, render default avatar URL
    - _Requirements: 3.2, 3.5_
  - [ ]* 8.4 Write property test for non-approved profile photos render as default avatar (Property 4)
    - **Property 4: Non-approved profile photos render as default avatar**
    - **Validates: Requirements 3.2, 3.5**

- [x] 9. Add navigation link in admin index
  - [x] 9.1 Add a link to `/admin/imagenes` in the admin navigation/index view (e.g. `resources/views/admin/stats.blade.php` or the admin sidebar)
    - _Requirements: 4.1_

- [x] 10. Final checkpoint — Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for a faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties; unit/feature tests validate specific examples and edge cases
