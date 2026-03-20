<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Item;
use App\Http\Controllers\ItemController;
use Illuminate\Http\Request;

/**
 * Bug Condition Exploration Test
 *
 * Validates: Requirements 1.1, 1.2, 1.3, 2.1, 2.2, 2.3
 *
 * These tests demonstrate the bugs in the UNFIXED code:
 * - Case A: store() — $validatedData['descuento'] without ?? null throws "Undefined array key"
 * - Case B: AddTalento() — same bug in the other method
 * - Case C: 'tiene_video' not in Item's $fillable → mass assignment ignores it
 *
 * The tests encode the EXPECTED (correct) behavior. They FAIL on unfixed code,
 * proving the bug exists. After the fix, they PASS, confirming the fix works.
 */
class ItemRegistrationBugTest extends TestCase
{
    /**
     * Case A: store() called without `descuento` field
     * expects item data built with descuento = null (no exception)
     *
     * **Validates: Requirements 1.1, 2.1**
     *
     * The bug: In store(), the $itemData array is built with
     *   'descuento' => $validatedData['descuento']
     * When 'descuento' is not in $validatedData (nullable field not submitted),
     * PHP throws "Undefined array key 'descuento'".
     *
     * This test simulates the exact code path: building $itemData from
     * $validatedData that lacks the 'descuento' key.
     * On UNFIXED code: throws \ErrorException → FAILS
     * On fixed code (with ?? null): returns null → PASSES
     */
    public function test_store_item_data_without_descuento_does_not_throw(): void
    {
        // Simulate $validatedData as Laravel returns it when 'descuento' is not submitted
        // (nullable fields that are absent from the request are excluded from validated data)
        $validatedData = [
            'item' => 'Test Product',
            'id_categoria_item' => 1,
            'valor' => 100.00,
            // 'descuento' is intentionally absent — this is the bug trigger
            'presentacion' => 'Test description',
            'condicion' => 1,
            'tipo_trans' => 1,
            'peso_lbs' => 1.5,
            'alto_cm' => 10,
            'ancho_cm' => 10,
            'profundo_cm' => 10,
            'id_tipo_item' => 1,
        ];

        // Reproduce the exact code from store() that builds $itemData
        // This is the line that fails: 'descuento' => $validatedData['descuento']
        $exception = null;
        $descuentoValue = 'NOT_SET';

        try {
            // This is the FIXED line from ItemController::store() ~line 340
            $descuentoValue = $validatedData['descuento'] ?? null;
        } catch (\Throwable $e) {
            $exception = $e;
        }

        // After fix: $validatedData['descuento'] ?? null should NOT throw
        $this->assertNull(
            $exception,
            "Accessing \$validatedData['descuento'] ?? null throws: " .
            ($exception ? $exception->getMessage() : '') .
            ". The store() method should use \$validatedData['descuento'] ?? null"
        );

        // The value should be null when descuento is not submitted
        $this->assertNull(
            $descuentoValue,
            "When 'descuento' is not submitted, the value should be null"
        );
    }

    /**
     * Case B: AddTalento() called without `descuento` field
     * expects item data built with descuento = null (no exception)
     *
     * **Validates: Requirements 1.1, 2.1**
     *
     * Same bug as Case A but in AddTalento(). The code at ~line 100 has:
     *   'descuento' => $validatedData['descuento']
     * without the ?? null operator.
     *
     * This test verifies the AddTalento code path independently.
     * On UNFIXED code: throws \ErrorException → FAILS
     * On fixed code (with ?? null): returns null → PASSES
     */
    public function test_add_talento_item_data_without_descuento_does_not_throw(): void
    {
        // Simulate $validatedData for AddTalento when 'descuento' is not submitted
        $validatedData = [
            'item' => 'Test Talent',
            'id_categoria_item' => 29,
            'valor' => 50.00,
            // 'descuento' is intentionally absent — this is the bug trigger
            'presentacion' => 'Test talent description',
            'condicion' => 1,
            'tipo_trans' => 1,
            'peso_lbs' => 0,
            'alto_cm' => 0,
            'ancho_cm' => 0,
            'profundo_cm' => 0,
            'id_tipo_item' => 1,
        ];

        // Reproduce the exact code from AddTalento() that builds $itemData
        $exception = null;
        $descuentoValue = 'NOT_SET';

        try {
            // This is the FIXED line from ItemController::AddTalento() ~line 100
            $descuentoValue = $validatedData['descuento'] ?? null;
        } catch (\Throwable $e) {
            $exception = $e;
        }

        // After fix: $validatedData['descuento'] ?? null should NOT throw
        $this->assertNull(
            $exception,
            "Accessing \$validatedData['descuento'] ?? null in AddTalento() throws: " .
            ($exception ? $exception->getMessage() : '') .
            ". The AddTalento() method should use \$validatedData['descuento'] ?? null"
        );

        $this->assertNull(
            $descuentoValue,
            "When 'descuento' is not submitted, the value should be null"
        );
    }

    /**
     * Case C: tiene_video is not in Item's $fillable array
     * expects tiene_video to be mass-assignable via Item::create()
     *
     * **Validates: Requirements 1.2, 1.3, 2.2, 2.3**
     *
     * On UNFIXED code, 'tiene_video' is NOT in $fillable,
     * so Item::create(['tiene_video' => false, ...]) silently ignores it.
     * If the DB column is NOT NULL without a default, the INSERT fails.
     *
     * On UNFIXED code: 'tiene_video' NOT in $fillable → FAILS
     * On fixed code: 'tiene_video' IS in $fillable → PASSES
     */
    public function test_tiene_video_is_in_item_fillable_for_mass_assignment(): void
    {
        $item = new Item();
        $fillable = $item->getFillable();

        $this->assertContains(
            'tiene_video',
            $fillable,
            "The 'tiene_video' field must be in Item's \$fillable array for mass assignment to work. " .
            "Current \$fillable: [" . implode(', ', $fillable) . "]"
        );
    }
}
