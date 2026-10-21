<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\Category;
use App\Models\Tenant\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $price = fake()->randomFloat(2, 10, 500);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'price' => $price,
            'promotional_price' => null,
            'stock' => fake()->numberBetween(0, 100),
            'sku' => strtoupper(fake()->bothify('PROD-####-??')),
            'image_url' => fake()->imageUrl(800, 800),
            'is_active' => true,
            'is_featured' => false,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function promotional(float $discountPercent = 20): static
    {
        return $this->state(fn (array $attributes) => [
            'promotional_price' => round($attributes['price'] * (1 - $discountPercent / 100), 2),
        ]);
    }
}
