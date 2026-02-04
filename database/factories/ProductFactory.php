<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'brand' => $this->faker->company,
            'price' => $this->faker->randomFloat(2, 10, 500),
            'images' => [],
            'merchant_id' => User::factory(),
            'status' => 'active',
        ];
    }
}
