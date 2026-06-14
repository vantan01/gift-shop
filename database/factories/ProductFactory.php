<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name  = $this->faker->unique()->words(3, true);
        $price = $this->faker->randomElement([
            49000, 79000, 99000, 129000, 149000,
            199000, 249000, 299000, 349000, 499000,
        ]);

        return [
            'category_id'         => Category::factory(),
            'name'                => ucfirst($name),
            'slug'                => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'description'         => $this->faker->paragraphs(2, true),
            'short_description'   => $this->faker->sentence(12),
            'price'               => $price,
            'compare_price'       => $this->faker->boolean(40)
                                        ? $price + $this->faker->randomElement([20000, 30000, 50000])
                                        : null,
            'stock'               => $this->faker->numberBetween(0, 100),
            'is_active'           => true,
            'is_featured'         => $this->faker->boolean(20),
            'low_stock_threshold' => 5,
            'sold_count'          => $this->faker->numberBetween(0, 500),
        ];
    }
}