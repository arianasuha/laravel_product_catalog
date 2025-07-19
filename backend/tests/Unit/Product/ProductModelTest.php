<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'name',
            'description',
            'price',
            'stock',
            'image',
        ];

        $product = new Product();

        $this->assertEquals($fillable, $product->getFillable());
    }

    #[Test]
    public function it_can_be_created_with_valid_attributes()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 19.99,
            'stock' => 100,
            'image' => 'test.jpg',
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 19.99,
            'stock' => 100,
            'image' => 'test.jpg',
        ]);
    }

    #[Test]
    public function description_and_image_can_be_null()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'price' => 19.99,
            'stock' => 100,
        ]);

        $this->assertNull($product->description);
        $this->assertNull($product->image);
    }

    #[Test]
    public function name_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Product::create([
            'price' => 19.99,
            'stock' => 100,
        ]);
    }

    #[Test]
    public function price_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Product::create([
            'name' => 'Test Product',
            'stock' => 100,
        ]);
    }

    #[Test]
    public function price_must_be_numeric()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'price' => '19.99', // String that can be cast to numeric
            'stock' => 100,
        ]);

        $this->assertEquals(19.99, $product->price);
        $this->assertIsFloat($product->price);
    }

    #[Test]
    public function stock_defaults_to_zero()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'price' => 19.99,
        ]);

        $this->assertEquals(0, $product->stock);
    }

    #[Test]
    public function stock_must_be_an_integer()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'price' => 19.99,
            'stock' => '100', // String that can be cast to integer
        ]);

        $this->assertEquals(100, $product->stock);
        $this->assertIsInt($product->stock);
    }

    #[Test]
    public function it_has_timestamps()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'price' => 19.99,
            'stock' => 100,
        ]);

        $this->assertNotNull($product->created_at);
        $this->assertNotNull($product->updated_at);
    }

    #[Test]
    public function it_can_update_attributes()
    {
        $product = Product::create([
            'name' => 'Original Name',
            'price' => 10.00,
            'stock' => 50,
        ]);

        $product->update([
            'name' => 'Updated Name',
            'price' => 20.00,
            'stock' => 100,
        ]);

        $this->assertEquals('Updated Name', $product->name);
        $this->assertEquals(20.00, $product->price);
        $this->assertEquals(100, $product->stock);
    }

    #[Test]
    public function it_can_be_deleted()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'price' => 19.99,
            'stock' => 100,
        ]);

        $productId = $product->id;
        $product->delete();

        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }
}