# Implementation Plan

- [x] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - Creación de ítem sin descuento y con tiene_video
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bug exists
  - **Scoped PBT Approach**: Scope the property to concrete failing cases:
    - Case A: `store()` called without `descuento` field → expects item created with `descuento = null`
    - Case B: `AddTalento()` called without `descuento` field → expects item created with `descuento = null`
    - Case C: `store()` called with `tiene_video => false` in `$itemData` → expects `tiene_video` persisted via mass assignment
  - Test that `store()` and `AddTalento()` succeed when `descuento` is absent from validated data (from Bug Condition: `isBugCondition(input)` where `'descuento' NOT IN input.validatedFields OR ('tiene_video' NOT IN Item.fillable AND 'tiene_video' IN input.itemData)`)
  - The test assertions should match Expected Behavior: item created successfully with `descuento = null`, `tiene_video` persisted correctly
  - Run test on UNFIXED code
  - **EXPECTED OUTCOME**: Test FAILS (this is correct - it proves the bug exists: "Undefined array key 'descuento'" exception or `tiene_video` not persisted)
  - Document counterexamples found to understand root cause
  - Mark task complete when test is written, run, and failure is documented
  - _Requirements: 1.1, 1.2, 1.3, 2.1, 2.2, 2.3_

- [x] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Comportamiento existente sin cambios para entradas válidas
  - **IMPORTANT**: Follow observation-first methodology
  - Observe behavior on UNFIXED code for non-buggy inputs (cases where `isBugCondition` returns false):
    - Observe: `store()` with explicit `descuento = 15.5` creates item with `descuento = 15.5` on unfixed code
    - Observe: `store()` with explicit `descuento = 0` creates item with `descuento = 0` on unfixed code
    - Observe: `AddTalento()` with explicit `descuento = 10` creates talent with `descuento = 10` on unfixed code
    - Observe: `store()` with colors and stock syncs correctly on unfixed code
    - Observe: Validation errors returned correctly for invalid requests on unfixed code
  - Write property-based tests capturing observed behavior patterns from Preservation Requirements:
    - For all requests with explicit numeric `descuento`, item is created with that exact value
    - For all requests with valid images, images are saved with correct order
    - For all requests with colors and stock, sync operates correctly
    - For invalid requests, validation errors are returned with input
  - Property-based testing generates many test cases for stronger preservation guarantees
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_

- [x] 3. Fix para registro de ítems (descuento null coalescing + tiene_video fillable)

  - [x] 3.1 Implement the fix
    - Add `'tiene_video'` to `$fillable` array in `app/Models/Item.php` (after `'presentacion'`)
    - Change `'descuento' => $validatedData['descuento']` to `'descuento' => $validatedData['descuento'] ?? null` in `store()` method (~line 340 of `ItemController.php`)
    - Change `'descuento' => $validatedData['descuento']` to `'descuento' => $validatedData['descuento'] ?? null` in `AddTalento()` method (~line 100 of `ItemController.php`)
    - _Bug_Condition: isBugCondition(input) where 'descuento' NOT IN input.validatedFields OR ('tiene_video' NOT IN Item.fillable AND 'tiene_video' IN input.itemData)_
    - _Expected_Behavior: Item created successfully with descuento = (input.descuento ?? null), tiene_video persisted via mass assignment_
    - _Preservation: Requests with explicit descuento produce same result; image saving, color sync, inventory creation, redirects, validation unchanged_
    - _Requirements: 2.1, 2.2, 2.3, 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_

  - [x] 3.2 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Creación de ítem sin descuento y con tiene_video
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - The test from task 1 encodes the expected behavior
    - When this test passes, it confirms the expected behavior is satisfied
    - Run bug condition exploration test from step 1
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - _Requirements: 2.1, 2.2, 2.3_

  - [x] 3.3 Verify preservation tests still pass
    - **Property 2: Preservation** - Comportamiento existente sin cambios para entradas válidas
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests from step 2
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Confirm all tests still pass after fix (no regressions)

- [x] 4. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.
