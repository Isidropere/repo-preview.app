# Requirements Document

## Introduction

The Image Approval Panel is an admin moderation feature for the CambialóRD marketplace. All images uploaded to the platform — item images (products and talentos) and user profile photos — must be reviewed and approved by an admin before being shown publicly. New images start in a `pendiente` (pending) state and are replaced by a placeholder until approved. Admins can approve or reject images individually or in bulk, and users are notified of the outcome via the existing notification system.

## Glossary

- **Panel_Aprobacion**: The admin-facing image moderation panel at `/admin/imagenes`.
- **ImagenItem**: An image associated with a product or talento, stored in the `imagenes_item` table.
- **FotoPerfil**: A user profile photo stored in the `users.foto_perfil` column.
- **Estado_Imagen**: The moderation status of an image. One of: `pendiente`, `aprobado`, `rechazado`.
- **Motivo_Rechazo**: A required text reason provided by the admin when rejecting an image.
- **Sistema_Notificacion**: The existing notification system based on the `NuevaNotificacion` event.
- **Admin**: An authenticated user with `isAdmin = true`, authorized to access `/admin` routes.
- **Placeholder**: A UI element shown in place of a pending or rejected image, displaying the message "Imagen en espera de aprobación".

---

## Requirements

### Requirement 1: Estado de aprobación en imágenes de items

**User Story:** As an admin, I want all item images to have an approval status, so that I can control which images are shown publicly.

#### Acceptance Criteria

1. THE `imagenes_item` table SHALL include an `estado` column with possible values `pendiente`, `aprobado`, and `rechazado`, defaulting to `pendiente`.
2. THE `imagenes_item` table SHALL include a `motivo_rechazo` column (nullable text) to store the admin's rejection reason.
3. WHEN a new `ImagenItem` record is created, THE Sistema SHALL set its `estado` to `pendiente` automatically.
4. THE `ImagenItem` model SHALL expose `estado` and `motivo_rechazo` as fillable attributes.

---

### Requirement 2: Estado de aprobación en fotos de perfil

**User Story:** As an admin, I want user profile photos to have an approval status, so that inappropriate profile images are not shown publicly before review.

#### Acceptance Criteria

1. THE `users` table SHALL include a `foto_perfil_estado` column with possible values `pendiente`, `aprobado`, and `rechazado`, defaulting to `pendiente`.
2. THE `users` table SHALL include a `foto_perfil_motivo_rechazo` column (nullable text) to store the admin's rejection reason.
3. WHEN a user uploads a new profile photo, THE Sistema SHALL set `foto_perfil_estado` to `pendiente`.
4. THE `User` model SHALL expose `foto_perfil_estado` and `foto_perfil_motivo_rechazo` as fillable attributes.

---

### Requirement 3: Ocultamiento público de imágenes pendientes y rechazadas

**User Story:** As a platform visitor, I want to see only approved images, so that I am not exposed to unmoderated content.

#### Acceptance Criteria

1. WHILE an `ImagenItem` has `estado` equal to `pendiente` or `rechazado`, THE Sistema SHALL display the Placeholder instead of the image in all public-facing views.
2. WHILE a `FotoPerfil` has `foto_perfil_estado` equal to `pendiente` or `rechazado`, THE Sistema SHALL display a default avatar placeholder instead of the profile photo in all public-facing views.
3. THE `ImageDisplay` component SHALL check the `estado` of the associated `ImagenItem` before rendering the image URL.
4. WHEN an `ImagenItem` has `estado` equal to `aprobado`, THE Sistema SHALL display the image normally.
5. WHEN a `FotoPerfil` has `foto_perfil_estado` equal to `aprobado`, THE Sistema SHALL display the profile photo normally.

---

### Requirement 4: Panel de administración de imágenes pendientes

**User Story:** As an admin, I want a dedicated panel showing all pending images grouped by type, so that I can efficiently review and moderate uploaded content.

#### Acceptance Criteria

1. THE Panel_Aprobacion SHALL be accessible at the route `/admin/imagenes` under the existing `auth` + `admin` middleware.
2. THE Panel_Aprobacion SHALL display all images with `estado = pendiente` grouped into two sections: "Imágenes de Items" and "Fotos de Perfil".
3. THE Panel_Aprobacion SHALL display a thumbnail, the item name or username, the upload date, and the image type for each pending image.
4. THE Panel_Aprobacion SHALL display a count of total pending images in each section.
5. WHEN there are no pending images in a section, THE Panel_Aprobacion SHALL display a message indicating no pending images exist for that section.

---

### Requirement 5: Aprobación individual de imágenes

**User Story:** As an admin, I want to approve a single image, so that it becomes visible to the public immediately.

#### Acceptance Criteria

1. WHEN an admin submits an approval action for a single `ImagenItem`, THE Panel_Aprobacion SHALL set the image's `estado` to `aprobado` and persist the change.
2. WHEN an admin submits an approval action for a single `FotoPerfil`, THE Panel_Aprobacion SHALL set the user's `foto_perfil_estado` to `aprobado` and persist the change.
3. WHEN an `ImagenItem` is approved, THE Sistema_Notificacion SHALL dispatch a `NuevaNotificacion` event to the owner of the associated item with a message indicating the image was approved.
4. WHEN a `FotoPerfil` is approved, THE Sistema_Notificacion SHALL dispatch a `NuevaNotificacion` event to the user with a message indicating the profile photo was approved.
5. WHEN the approval action succeeds, THE Panel_Aprobacion SHALL remove the image from the pending list and display a success confirmation.

---

### Requirement 6: Rechazo individual de imágenes con motivo

**User Story:** As an admin, I want to reject a single image with a required reason, so that the user understands why their image was not approved.

#### Acceptance Criteria

1. WHEN an admin submits a rejection action for a single `ImagenItem`, THE Panel_Aprobacion SHALL require a non-empty `motivo_rechazo` text before processing.
2. IF the `motivo_rechazo` field is empty on rejection, THEN THE Panel_Aprobacion SHALL return a validation error and not persist any change.
3. WHEN a valid rejection is submitted for an `ImagenItem`, THE Panel_Aprobacion SHALL set the image's `estado` to `rechazado` and store the `motivo_rechazo`.
4. WHEN a valid rejection is submitted for a `FotoPerfil`, THE Panel_Aprobacion SHALL set the user's `foto_perfil_estado` to `rechazado` and store the `foto_perfil_motivo_rechazo`.
5. WHEN an `ImagenItem` is rejected, THE Sistema_Notificacion SHALL dispatch a `NuevaNotificacion` event to the owner of the associated item including the `motivo_rechazo` in the notification message.
6. WHEN a `FotoPerfil` is rejected, THE Sistema_Notificacion SHALL dispatch a `NuevaNotificacion` event to the user including the `foto_perfil_motivo_rechazo` in the notification message.
7. WHEN the rejection action succeeds, THE Panel_Aprobacion SHALL remove the image from the pending list and display a success confirmation.

---

### Requirement 7: Aprobación masiva de imágenes

**User Story:** As an admin, I want to approve all pending images at once, so that I can process a large backlog efficiently.

#### Acceptance Criteria

1. THE Panel_Aprobacion SHALL provide a "Aprobar todas" control for each section (items and profile photos).
2. WHEN an admin activates the "Aprobar todas" control for the items section, THE Panel_Aprobacion SHALL set `estado` to `aprobado` for all currently pending `ImagenItem` records and dispatch a `NuevaNotificacion` event to each affected item owner.
3. WHEN an admin activates the "Aprobar todas" control for the profile photos section, THE Panel_Aprobacion SHALL set `foto_perfil_estado` to `aprobado` for all currently pending `FotoPerfil` records and dispatch a `NuevaNotificacion` event to each affected user.
4. WHEN the bulk approval action completes, THE Panel_Aprobacion SHALL display a confirmation indicating how many images were approved.

---

### Requirement 8: Notificaciones al usuario por resultado de moderación

**User Story:** As a user, I want to receive a notification when my image is approved or rejected, so that I know the status of my uploaded content.

#### Acceptance Criteria

1. WHEN an `ImagenItem` transitions to `aprobado`, THE Sistema_Notificacion SHALL send a notification to the item owner with the message: "Tu imagen del artículo '[nombre del item]' ha sido aprobada."
2. WHEN an `ImagenItem` transitions to `rechazado`, THE Sistema_Notificacion SHALL send a notification to the item owner with the message: "Tu imagen del artículo '[nombre del item]' fue rechazada. Motivo: [motivo_rechazo]."
3. WHEN a `FotoPerfil` transitions to `aprobado`, THE Sistema_Notificacion SHALL send a notification to the user with the message: "Tu foto de perfil ha sido aprobada."
4. WHEN a `FotoPerfil` transitions to `rechazado`, THE Sistema_Notificacion SHALL send a notification to the user with the message: "Tu foto de perfil fue rechazada. Motivo: [foto_perfil_motivo_rechazo]."
5. THE Sistema_Notificacion SHALL use the existing `NuevaNotificacion` event and the existing notifications persistence mechanism for all image moderation notifications.

---

### Requirement 9: Integridad y seguridad del panel

**User Story:** As a platform operator, I want the approval panel to be secure and consistent, so that only authorized admins can moderate images.

#### Acceptance Criteria

1. THE Panel_Aprobacion SHALL be protected by the existing `auth` and `admin` middleware on all routes.
2. IF an unauthenticated or non-admin user attempts to access `/admin/imagenes`, THEN THE Sistema SHALL redirect the request to the login page or return a 403 response.
3. THE Panel_Aprobacion SHALL validate all incoming approval and rejection requests server-side before persisting any change.
4. WHEN an approval or rejection is submitted for an `id_imagen` that does not exist, THE Panel_Aprobacion SHALL return a 404 response.
5. THE Panel_Aprobacion SHALL use CSRF protection on all state-changing requests.
