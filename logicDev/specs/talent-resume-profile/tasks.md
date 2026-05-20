# Implementation Plan: Talent Resume Profile (Hoja de Vida)

## Overview

CRUD simple para hoja de vida de usuario. Migración + modelo, controlador con rutas, vista de formulario, integración en tu_cuenta, gate en creación de talentos, y relación en User.

## Tasks

- [ ] 1. Migration, check.php, and HojaVida Model
  - [ ] 1.1 Create migration `database/migrations/2026_06_01_000001_create_hojas_vida_table.php`
    - Table `hojas_vida` with columns: `id`, `id_user` (unique FK to users), `nombres` (varchar 100), `apellidos` (varchar 100), `titulo_profesional` (varchar 150), `descripcion_bio` (text), `habilidades` (text), `experiencia` (text), `ubicacion` (varchar 200), `timestamps`
    - _Requirements: 2.2, 6.1_
  - [ ] 1.2 Create `check.php` SQL script for MochaHost deploy (same pattern as existing deploy-migrate route)
    - Raw SQL to create `hojas_vida` table if not exists
    - _Requirements: 2.2_
  - [ ] 1.3 Create model `app/Models/HojaVida.php`
    - `$table = 'hojas_vida'`, `$fillable` with only allowed fields (no telefono/email)
    - `belongsTo(User::class, 'id_user', 'id')` relationship
    - _Requirements: 2.2, 6.1, 6.3_
  - [ ]* 1.4 Write property test: mass assignment ignores disallowed fields
    - **Property 7: Mass assignment ignores disallowed fields**
    - **Validates: Requirements 6.3**

- [ ] 2. HojaVidaController and Routes
  - [ ] 2.1 Create `app/Http/Controllers/HojaVidaController.php`
    - `form()` method: loads existing HojaVida or pre-fills nombres/apellidos from auth user
    - `save(Request $request)` method: validates required fields, uses `updateOrCreate` on `id_user`
    - Redirect with success message on save
    - _Requirements: 1.1, 1.2, 2.1, 2.3, 2.4, 3.1, 3.2, 3.3_
  - [ ] 2.2 Register routes in `routes/web.php` inside the `auth` middleware group
    - `GET /mi-hoja-vida` → `HojaVidaController@form` named `hoja-vida.form`
    - `POST /mi-hoja-vida` → `HojaVidaController@save` named `hoja-vida.save`
    - _Requirements: 2.1, 3.2_
  - [ ]* 2.3 Write property tests for controller logic
    - **Property 1: Pre-fill user data on first access**
    - **Property 2: Creation stores all fields and links to user**
    - **Property 3: One record per user (updateOrCreate uniqueness)**
    - **Property 4: Store-then-load round trip**
    - **Property 5: Validation rejects incomplete submissions**
    - **Validates: Requirements 1.1, 1.2, 2.1, 2.2, 2.3, 2.4, 3.1, 3.2**

- [ ] 3. Checkpoint - Migration and controller working
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 4. View form.blade.php
  - [ ] 4.1 Create `resources/views/hoja-vida/form.blade.php`
    - Single form for create and edit, extends `layouts.app`
    - Pre-filled `nombres` and `apellidos` fields (from user data or existing record)
    - Fields: nombres, apellidos, titulo_profesional, descripcion_bio, habilidades, experiencia, ubicacion
    - NO phone or email input fields
    - Display validation errors per field
    - Display flash messages (success, warning)
    - Use existing Tailwind CSS classes consistent with project style
    - Include `btn-volver` component linking back to tu_cuenta
    - _Requirements: 1.1, 1.2, 1.3, 2.4, 3.1, 3.3, 6.2_

- [ ] 5. Modify tu_cuenta.blade.php
  - [ ] 5.1 Add "Mi Hoja de Vida" card to the options grid in `resources/views/tu-cuenta/tu_cuenta.blade.php`
    - Link to `route('hoja-vida.form')`
    - Dynamic subtitle: "Completa tu perfil profesional" if no hoja de vida, "Edita tu perfil profesional" if exists
    - Use same card styling as existing grid items
    - _Requirements: 4.1, 4.2, 4.3, 4.4_

- [ ] 6. Talent creation gate and User model relation
  - [ ] 6.1 Modify the `/talentos/crear` route closure in `routes/web.php`
    - Add check at the beginning: if user has no `hojas_vida` record, redirect to `hoja-vida.form` with warning message
    - _Requirements: 5.1, 5.2, 5.3_
  - [ ] 6.2 Add `hojaVida()` hasOne relationship to `app/Models/User.php`
    - `return $this->hasOne(HojaVida::class, 'id_user', 'id')`
    - _Requirements: 2.1_
  - [ ]* 6.3 Write property test for talent creation gate
    - **Property 6: Talent creation gate**
    - **Validates: Requirements 5.1, 5.3**

- [ ] 7. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- The design uses PHP/Laravel — all code follows existing project conventions
- `check.php` is needed because the hosting (MochaHost) has no SSH access
- Property tests use PHPUnit with random data generation (100+ iterations per property)
