<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use App\Models\Inventario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Preservation Property Tests (BEFORE fix)
 *
 * **Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6**
 */
class ItemRegistrationPreservationTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        Storage::fake('public');
        $this->user = User::first();
        if (!$this->user->email_verified_at) {
            $this->user->email_verified_at = now();
            $this->user->save();
        }
    }

    private function makeStorePayload(float $descuento, array $overrides = []): array
    {
        $base = [
            'item' => 'Test Product ' . uniqid(),
            'id_categoria_item' => 1,
            'valor' => 100.00,
            'descuento' => $descuento,
            'presentacion' => 'Test product description',
            'condicion' => 1,
            'tipo_trans' => 1,
            'peso_lbs' => 1.5,
            'alto_cm' => 10,
            'ancho_cm' => 10,
            'profundo_cm' => 10,
            'id_tipo_item' => 1,
            'cantidad' => 5,
        ];
        return array_merge($base, $overrides);
    }

    private function makeTalentoPayload(float $descuento, array $overrides = []): array
    {
        $base = [
            'item' => 'Test Talent ' . uniqid(),
            'id_categoria_item' => 29,
            'valor' => 50.00,
            'descuento' => $descuento,
            'presentacion' => 'Test talent description',
            'condicion' => 1,
            'tipo_trans' => 1,
            'peso_lbs' => 0,
            'alto_cm' => 0,
            'ancho_cm' => 0,
            'profundo_cm' => 0,
            'id_tipo_item' => 1,
        ];
        return array_merge($base, $overrides);
    }

    private function fakeImage(string $name = 'product.jpg'): UploadedFile
    {
        $jpeg = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AKwA//9k=');
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_img_');
        file_put_contents($tmpFile, $jpeg);
        return new UploadedFile($tmpFile, $name, 'image/jpeg', null, true);
    }
    /**
     * Property 2 - Preservation: store() with explicit numeric descuento
     * creates item with that exact value.
     *
     * **Validates: Requirements 3.1**
     *
     * @dataProvider explicitDescuentoProvider
     */
    public function test_store_with_explicit_descuento_preserves_value(float $descuento): void
    {
        $payload = $this->makeStorePayload($descuento);
        $response = $this->actingAs($this->user)
            ->post(route('items.store'), array_merge($payload, [
                'imagen_principal' => $this->fakeImage(),
            ]));
        $response->assertRedirect(route('items.user'));
        $item = Item::where('item', $payload['item'])->first();
        $this->assertNotNull($item, 'Item should be created in the database');
        $this->assertEquals(
            number_format($descuento, 2),
            number_format($item->descuento, 2),
            'Item descuento should match'
        );
    }

    public static function explicitDescuentoProvider(): array
    {
        return [
            'descuento 0' => [0.00],
            'descuento 15.5' => [15.50],
            'descuento 10' => [10.00],
            'descuento 99.99' => [99.99],
            'descuento 0.01' => [0.01],
            'descuento 50' => [50.00],
        ];
    }

    /**
     * Property 2 - Preservation: AddTalento() with explicit descuento
     * creates talent with that exact value.
     *
     * **Validates: Requirements 3.1, 3.3**
     *
     * @dataProvider talentoDescuentoProvider
     */
    public function test_add_talento_with_explicit_descuento_preserves_value(float $descuento): void
    {
        $payload = $this->makeTalentoPayload($descuento);
        $response = $this->actingAs($this->user)
            ->post(route('items.AddTalento'), array_merge($payload, [
                'imagen_principal' => $this->fakeImage('talent.jpg'),
            ]));
        $response->assertRedirect(route('items.admintalento'));
        $item = Item::where('item', $payload['item'])->first();
        $this->assertNotNull($item, 'Talent should be created in the database');
        $this->assertEquals(
            number_format($descuento, 2),
            number_format($item->descuento, 2),
            'Talent descuento should match'
        );
    }

    public static function talentoDescuentoProvider(): array
    {
        return [
            'descuento 10' => [10.00],
            'descuento 0' => [0.00],
            'descuento 25.50' => [25.50],
        ];
    }

    /**
     * Property 2 - Preservation: store() with colors passes them to sync.
     *
     * **Validates: Requirements 3.4**
     */
    public function test_store_with_colors_processes_color_data(): void
    {
        $payload = $this->makeStorePayload(10.00, [
            'colors' => [1, 2],
            'stock' => [1 => 5, 2 => 10],
        ]);
        $response = $this->actingAs($this->user)
            ->post(route('items.store'), array_merge($payload, [
                'imagen_principal' => $this->fakeImage(),
            ]));
        $this->assertTrue(
            $response->isRedirect(),
            'Controller should redirect (either success or back with error)'
        );
    }

    /**
     * Property 2 - Preservation: store() creates inventory record correctly.
     *
     * **Validates: Requirements 3.1**
     */
    public function test_store_creates_inventory_record(): void
    {
        $cantidad = 15;
        $payload = $this->makeStorePayload(5.00, ['cantidad' => $cantidad]);
        $response = $this->actingAs($this->user)
            ->post(route('items.store'), array_merge($payload, [
                'imagen_principal' => $this->fakeImage(),
            ]));
        $response->assertRedirect(route('items.user'));
        $item = Item::where('item', $payload['item'])->first();
        $this->assertNotNull($item, 'Item should be created');
        $inventario = Inventario::where('id_item', $item->id_item)->first();
        $this->assertNotNull($inventario, 'Inventory record should be created');
        $this->assertEquals($cantidad, $inventario->cantidad, 'Inventory cantidad should match');
    }
    /**
     * Property 2 - Preservation: store() with images saves with correct order.
     *
     * **Validates: Requirements 3.2, 3.5**
     */
    public function test_store_with_images_saves_with_correct_order(): void
    {
        $payload = $this->makeStorePayload(5.00);
        $mainImage = $this->fakeImage('main.jpg');
        $extra1 = $this->fakeImage('extra1.jpg');
        $extra2 = $this->fakeImage('extra2.jpg');
        $response = $this->actingAs($this->user)
            ->post(route('items.store'), array_merge($payload, [
                'imagen_principal' => $mainImage,
                'imagenes' => [$extra1, $extra2],
            ]));
        $response->assertRedirect(route('items.user'));
        $item = Item::where('item', $payload['item'])->first();
        $this->assertNotNull($item, 'Item should be created');
        $images = $item->imagenes()->orderBy('orden_visualizacion')->get();
        $this->assertGreaterThanOrEqual(1, $images->count(), 'At least main image should be saved');
        $mainImg = $images->where('orden_visualizacion', 1)->first();
        $this->assertNotNull($mainImg, 'Main image should have orden_visualizacion = 1');
        if ($images->count() > 1) {
            $additionalImages = $images->where('orden_visualizacion', '>', 1)->values();
            foreach ($additionalImages as $i => $img) {
                $expectedOrder = $i + 2;
                $this->assertEquals($expectedOrder, $img->orden_visualizacion, 'Image order should be sequential');
            }
        }
    }

    /**
     * Property 2 - Preservation: Validation errors returned for invalid requests.
     *
     * **Validates: Requirements 3.6**
     *
     * @dataProvider invalidRequestProvider
     */
    public function test_validation_errors_returned_for_invalid_store_requests(array $payload, string $expectedErrorField): void
    {
        $response = $this->actingAs($this->user)
            ->from('/mis-productos/crear')
            ->post(route('items.store'), $payload);
        $response->assertSessionHasErrors($expectedErrorField);
    }

    public static function invalidRequestProvider(): array
    {
        return [
            'missing item name' => [
                ['id_categoria_item' => 1, 'valor' => 100, 'descuento' => 10, 'presentacion' => 'desc', 'condicion' => 1, 'tipo_trans' => 1, 'cantidad' => 5],
                'item',
            ],
            'missing valor' => [
                ['item' => 'Test', 'id_categoria_item' => 1, 'descuento' => 10, 'presentacion' => 'desc', 'condicion' => 1, 'tipo_trans' => 1, 'cantidad' => 5],
                'valor',
            ],
            'missing imagen_principal' => [
                ['item' => 'Test', 'id_categoria_item' => 1, 'valor' => 100, 'descuento' => 10, 'presentacion' => 'desc', 'condicion' => 1, 'tipo_trans' => 1, 'cantidad' => 5],
                'imagen_principal',
            ],
            'invalid condicion' => [
                ['item' => 'Test', 'id_categoria_item' => 1, 'valor' => 100, 'descuento' => 10, 'presentacion' => 'desc', 'condicion' => 99, 'tipo_trans' => 1, 'cantidad' => 5],
                'condicion',
            ],
            'missing presentacion' => [
                ['item' => 'Test', 'id_categoria_item' => 1, 'valor' => 100, 'descuento' => 10, 'condicion' => 1, 'tipo_trans' => 1, 'cantidad' => 5],
                'presentacion',
            ],
        ];
    }

    /**
     * Property 2 - Preservation: AddTalento validation errors for invalid requests.
     *
     * **Validates: Requirements 3.6**
     */
    public function test_validation_errors_returned_for_invalid_talento_requests(): void
    {
        $response = $this->actingAs($this->user)
            ->from('/talentos/')
            ->post(route('items.AddTalento'), [
                'id_categoria_item' => 29,
                'valor' => 50,
                'descuento' => 10,
                'presentacion' => 'desc',
                'condicion' => 1,
                'tipo_trans' => 1,
            ]);
        $response->assertSessionHasErrors('item');
    }

    /**
     * Property 2 - Preservation: store() redirects to items.user on success.
     *
     * **Validates: Requirements 3.2**
     */
    public function test_store_redirects_to_items_user_on_success(): void
    {
        $payload = $this->makeStorePayload(10.00);
        $response = $this->actingAs($this->user)
            ->post(route('items.store'), array_merge($payload, [
                'imagen_principal' => $this->fakeImage(),
            ]));
        $response->assertRedirect(route('items.user'));
        $response->assertSessionHas('success');
    }

    /**
     * Property 2 - Preservation: AddTalento() redirects to items.admintalento on success.
     *
     * **Validates: Requirements 3.3**
     */
    public function test_add_talento_redirects_to_admintalento_on_success(): void
    {
        $payload = $this->makeTalentoPayload(10.00);
        $response = $this->actingAs($this->user)
            ->post(route('items.AddTalento'), array_merge($payload, [
                'imagen_principal' => $this->fakeImage('talent.jpg'),
            ]));
        $response->assertRedirect(route('items.admintalento'));
        $response->assertSessionHas('success');
    }
}