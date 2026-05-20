# Requirements Document

## Introduction

Esta funcionalidad agrega una hoja de vida básica (perfil profesional) al sistema Cambialord. Los usuarios que deseen publicar talentos (categoría 29) deben completar primero su hoja de vida desde "Tu Cuenta". La hoja de vida se crea una sola vez, puede editarse posteriormente, y NO incluye teléfono ni correo electrónico por privacidad. Los campos de nombre se pre-llenan automáticamente con los datos del usuario registrado.

## Glossary

- **Sistema**: La aplicación web Cambialord
- **Hoja_de_Vida**: Registro en la tabla `hojas_vida` que contiene el perfil profesional básico de un usuario, vinculado por `id_user`
- **Usuario**: Un usuario autenticado del sistema, representado en la tabla `users`
- **Talento**: Un item publicado con `id_categoria_item = 29`, que representa un servicio o habilidad ofrecida
- **Tu_Cuenta**: La página de cuenta del usuario ubicada en la ruta `/tu-cuenta`
- **Formulario_Hoja_de_Vida**: La vista que permite crear o editar la Hoja_de_Vida

## Requirements

### Requirement 1: Pre-llenado automático de datos del usuario

**User Story:** Como usuario, quiero que mi hoja de vida se pre-llene con mis datos de registro, para no tener que escribir información que ya proporcioné.

#### Acceptance Criteria

1. WHEN a Usuario accesses the Formulario_Hoja_de_Vida for the first time, THE Sistema SHALL pre-fill the `nombres` field with the value from the Usuario's `nombres` column in the `users` table
2. WHEN a Usuario accesses the Formulario_Hoja_de_Vida for the first time, THE Sistema SHALL pre-fill the `apellidos` field with the value from the Usuario's `apellidos` column in the `users` table
3. THE Formulario_Hoja_de_Vida SHALL NOT include fields for phone number or email address

### Requirement 2: Creación de la Hoja de Vida

**User Story:** Como usuario, quiero crear mi hoja de vida básica, para poder presentar mi perfil profesional al publicar talentos.

#### Acceptance Criteria

1. WHEN a Usuario submits the Formulario_Hoja_de_Vida with valid data, THE Sistema SHALL create a single Hoja_de_Vida record linked to the Usuario's `id`
2. THE Hoja_de_Vida SHALL contain the following fields: `nombres`, `apellidos`, `titulo_profesional`, `descripcion_bio`, `habilidades`, `experiencia`, and `ubicacion`
3. WHEN a Usuario already has a Hoja_de_Vida and attempts to create another, THE Sistema SHALL redirect the Usuario to the edit view of the existing Hoja_de_Vida
4. WHEN a Usuario submits the Formulario_Hoja_de_Vida with missing required fields, THE Sistema SHALL display validation error messages identifying each missing field

### Requirement 3: Edición de la Hoja de Vida

**User Story:** Como usuario, quiero poder editar mi hoja de vida después de crearla, para mantener mi perfil profesional actualizado.

#### Acceptance Criteria

1. WHEN a Usuario accesses the Formulario_Hoja_de_Vida and a Hoja_de_Vida already exists, THE Sistema SHALL load the existing data into the form fields
2. WHEN a Usuario submits updated data through the Formulario_Hoja_de_Vida, THE Sistema SHALL update the existing Hoja_de_Vida record
3. WHEN the update is successful, THE Sistema SHALL display a confirmation message "Hoja de vida actualizada exitosamente"

### Requirement 4: Acceso desde Tu Cuenta

**User Story:** Como usuario, quiero acceder a mi hoja de vida desde la página Tu Cuenta, para gestionarla fácilmente junto a mis otras opciones de cuenta.

#### Acceptance Criteria

1. THE Tu_Cuenta page SHALL display a link card for "Mi Hoja de Vida" in the options grid
2. WHEN a Usuario clicks the "Mi Hoja de Vida" card, THE Sistema SHALL navigate to the Formulario_Hoja_de_Vida
3. WHILE a Usuario does not have a Hoja_de_Vida, THE "Mi Hoja de Vida" card SHALL display the subtitle "Completa tu perfil profesional"
4. WHILE a Usuario already has a Hoja_de_Vida, THE "Mi Hoja de Vida" card SHALL display the subtitle "Edita tu perfil profesional"

### Requirement 5: Validación obligatoria antes de crear un Talento

**User Story:** Como plataforma, quiero asegurar que los usuarios completen su hoja de vida antes de publicar un talento, para garantizar que cada talento tenga un perfil profesional asociado.

#### Acceptance Criteria

1. WHEN a Usuario attempts to access the talent creation route (`/talentos/crear`) and the Usuario does not have a Hoja_de_Vida, THE Sistema SHALL redirect the Usuario to the Formulario_Hoja_de_Vida
2. WHEN a Usuario is redirected to the Formulario_Hoja_de_Vida due to missing Hoja_de_Vida, THE Sistema SHALL display a message "Debes completar tu hoja de vida antes de publicar un talento"
3. WHILE a Usuario has a completed Hoja_de_Vida, THE Sistema SHALL allow access to the talent creation route without redirection

### Requirement 6: Privacidad de datos de contacto

**User Story:** Como usuario, quiero que mi hoja de vida no exponga mi teléfono ni correo, para proteger mi información de contacto personal.

#### Acceptance Criteria

1. THE Hoja_de_Vida table SHALL NOT store phone number or email address columns
2. THE Formulario_Hoja_de_Vida SHALL NOT render input fields for phone number or email address
3. IF a request attempts to inject `telefono` or `email` fields into the Hoja_de_Vida, THEN THE Sistema SHALL ignore those fields during mass assignment
