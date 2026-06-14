<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Thú bông', 'Hoa sáp', 'Hộp quà', 'Chocolate',
            'Phụ kiện couple', 'Thiệp', 'Nến thơm', 'Vòng tay',
        ]);

        return [
            'name'        => $name,
            'slug'        => Str::slug($name),
            'description' => $this->faker->sentence(10),
            'is_active'   => true,
            'sort_order'  => $this->faker->numberBetween(0, 10),
        ];
    }
}