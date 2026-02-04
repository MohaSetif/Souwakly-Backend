<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_affiliate_can_create_order()
    {
        $affiliate = User::factory()->create();
        $affiliate->assignRole('Affiliate');

        $product = Product::factory()->create();

        $token = $affiliate->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', [
                'product_id' => $product->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.product_id', $product->id)
            ->assertJsonPath('data.affiliate_id', $affiliate->id)
            ->assertJsonPath('data.merchant_id', $product->merchant_id);

        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'affiliate_id' => $affiliate->id,
        ]);
    }

    public function test_merchant_can_see_their_products_orders()
    {
        $merchant = User::factory()->create();
        $merchant->assignRole('Merchant');

        $product = Product::factory()->create(['merchant_id' => $merchant->id]);
        Order::factory()->count(2)->create(['product_id' => $product->id, 'merchant_id' => $merchant->id]);

        // Other order
        Order::factory()->create();

        $token = $merchant->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_affiliate_can_see_their_own_orders()
    {
        $affiliate = User::factory()->create();
        $affiliate->assignRole('Affiliate');

        Order::factory()->count(3)->create(['affiliate_id' => $affiliate->id]);

        // Other order
        Order::factory()->create();

        $token = $affiliate->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_merchant_can_update_order_status()
    {
        $merchant = User::factory()->create();
        $merchant->assignRole('Merchant');

        $order = Order::factory()->create(['merchant_id' => $merchant->id, 'status' => 'pending']);

        $token = $merchant->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/orders/{$order->id}/status", [
                'status' => 'confirmed',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_merchant_cannot_update_others_order_status()
    {
        $merchant1 = User::factory()->create();
        $merchant1->assignRole('Merchant');

        $merchant2 = User::factory()->create();
        $merchant2->assignRole('Merchant');

        $order = Order::factory()->create(['merchant_id' => $merchant2->id]);

        $token = $merchant1->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/orders/{$order->id}/status", [
                'status' => 'confirmed',
            ]);

        $response->assertStatus(403);
    }
}
