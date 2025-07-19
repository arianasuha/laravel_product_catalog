<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true) . ' Product', // Generates a 2-word name
            'description' => $this->faker->paragraph(3), // Generates 3 sentences
            'price' => $this->faker->randomFloat(2, 10, 1000), // Price between 10 and 1000 with 2 decimal places
            'stock' => $this->faker->numberBetween(0, 200), // Stock between 0 and 200
            'image' => null, // You can add logic here to generate dummy image URLs if needed
        ];
    }
}

