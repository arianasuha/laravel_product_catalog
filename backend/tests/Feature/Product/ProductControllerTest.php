<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    #[Test]
    public function it_can_list_paginated_products()
    {
        Product::factory()->count(15)->create();

        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'price', 'stock', 'image', 'created_at', 'updated_at']
                ],
                'links',
            ])
            ->assertJsonCount(10, 'data');
    }

    #[Test]
    public function it_can_create_a_product_without_image()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 19.99,
            'stock' => 100,
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertCreated()
            ->assertJson($productData);

        $this->assertDatabaseHas('products', $productData);
    }

    #[Test]
    public function it_can_create_a_product_with_image()
    {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not installed.');
        }

        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 19.99,
            'stock' => 100,
            'image' => $image,
        ]);

        $response->assertCreated();

        $product = Product::first();
        $this->assertNotNull($product->image);
        $this->assertStringContainsString('storage/products/', $product->image);
    }

    #[Test]
    public function it_validates_product_creation()
    {
        $response = $this->postJson('/api/products', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'price', 'stock']);
    }

    #[Test]
    public function it_validates_price_is_numeric_and_positive()
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Test',
            'price' => 'invalid',
            'stock' => 10,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['price']);

        $response = $this->postJson('/api/products', [
            'name' => 'Test',
            'price' => -10,
            'stock' => 10,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['price']);
    }

    #[Test]
    public function it_validates_stock_is_integer_and_positive()
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Test',
            'price' => 10.99,
            'stock' => 'invalid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['stock']);

        $response = $this->postJson('/api/products', [
            'name' => 'Test',
            'price' => 10.99,
            'stock' => -10,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['stock']);
    }

    #[Test]
    public function it_validates_image_file_types()
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->postJson('/api/products', [
            'name' => 'Test',
            'price' => 10.99,
            'stock' => 10,
            'image' => $invalidFile,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }


    #[Test]
    public function it_can_show_a_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => (float) $product->price,
                    'stock' => $product->stock,
                    'image' => $product->image,
                ]
            ]);
    }


    #[Test]
    public function it_returns_404_for_nonexistent_product()
    {
        // First create and delete a product to ensure ID 1 doesn't exist
        $product = Product::factory()->create();
        $product->delete();

        $response = $this->getJson('/api/products/999');

        $response->assertNotFound();
    }

    #[Test]
    public function it_can_update_a_product()
    {
        $product = Product::factory()->create();
        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'price' => 29.99,
            'stock' => 50,
        ];

        $response = $this->putJson("/api/products/{$product->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('products', array_merge(['id' => $product->id], $updateData));
    }

    #[Test]
    public function it_can_update_product_with_new_image()
    {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not installed.');
        }

        $product = Product::factory()->create(['image' => 'storage/products/old-image.jpg']);
        $newImage = UploadedFile::fake()->image('new-product.jpg');

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated',
            'price' => 10.99,
            'stock' => 10,
            'image' => $newImage,
        ]);

        $response->assertOk();

        $product->refresh();
        $this->assertNotNull($product->image);
        $this->assertNotEquals('storage/products/old-image.jpg', $product->image);
        $this->assertStringContainsString('storage/products/', $product->image);
    }

    #[Test]
    public function it_can_clear_product_image()
    {
        $product = Product::factory()->create(['image' => 'storage/products/old-image.jpg']);

        // Mock the storage delete
        Storage::shouldReceive('delete')
            ->once()
            ->with('products/old-image.jpg');

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated',
            'price' => 10.99,
            'stock' => 10,
            'clear_image' => true,
        ]);

        $response->assertOk();

        $product->refresh();
        $this->assertNull($product->image);
    }

    #[Test]
    public function it_can_delete_a_product()
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    #[Test]
    public function it_deletes_product_image_when_product_is_deleted()
    {
        $product = Product::factory()->create(['image' => 'storage/products/image.jpg']);

        $this->deleteJson("/api/products/{$product->id}");

        Storage::disk('public')->assertMissing('products/image.jpg');
    }

    #[Test]
    public function it_requires_authentication()
    {
        // Create a new test case without authentication
        $this->refreshApplication();

        $response = $this->postJson('/api/products', [
            'name' => 'Test',
            'price' => 10.99,
            'stock' => 10,
        ]);

        $response->assertUnauthorized();
    }
}