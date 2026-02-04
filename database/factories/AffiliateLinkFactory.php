<?php

namespace Database\Factories;

use App\Models\AffiliateLink;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AffiliateLinkFactory extends Factory
{
    protected $model = AffiliateLink::class;

    public function definition(): array
    {
        return [
            'affiliate_id' => User::factory(),
            'product_id' => Product::factory(),
            'code' => Str::upper(Str::random(10)),
            'clicks' => 0,
        ];
    }
}
