<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'affiliate_id' => User::factory(),
            'merchant_id' => User::factory(),
            'status' => 'pending',
        ];
    }
}
