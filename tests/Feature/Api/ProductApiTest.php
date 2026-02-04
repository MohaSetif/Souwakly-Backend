<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_merchant_can_create_product()
    {
        $merchant = User::factory()->create();
        $merchant->assignRole('Merchant');

        $token = $merchant->createToken('test')->plainTextToken;

        $productData = [
            'name' => 'Awesome Product',
            'description' => 'This is a test product',
            'price' => 99.99,
            'brand' => 'TestBrand',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products', $productData);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Awesome Product')
            ->assertJsonPath('data.merchant_id', $merchant->id);

        $this->assertDatabaseHas('products', [
            'name' => 'Awesome Product',
            'merchant_id' => $merchant->id,
        ]);
    }

    public function test_merchant_can_update_own_product()
    {
        $merchant = User::factory()->create();
        $merchant->assignRole('Merchant');

        $product = Product::factory()->create([
            'merchant_id' => $merchant->id,
            'name' => 'Old Name',
        ]);

        $token = $merchant->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/products/{$product->id}", [
                'name' => 'New Name',
                'price' => 150.00,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Name',
        ]);
    }

    public function test_merchant_cannot_update_others_product()
    {
        $merchant1 = User::factory()->create();
        $merchant1->assignRole('Merchant');

        $merchant2 = User::factory()->create();
        $merchant2->assignRole('Merchant');

        $product = Product::factory()->create([
            'merchant_id' => $merchant2->id,
        ]);

        $token = $merchant1->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/products/{$product->id}", [
                'name' => 'Hacked Name',
            ]);

        $response->assertStatus(403);
    }

    public function test_merchant_can_delete_own_product()
    {
        $merchant = User::factory()->create();
        $merchant->assignRole('Merchant');

        $product = Product::factory()->create([
            'merchant_id' => $merchant->id,
        ]);

        $token = $merchant->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_affiliate_cannot_create_product()
    {
        $affiliate = User::factory()->create();
        $affiliate->assignRole('Affiliate');

        $token = $affiliate->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products', [
                'name' => 'Illegal Product',
            ]);

        $response->assertStatus(403);
    }

    public function test_public_can_view_active_products()
    {
        Product::factory()->count(3)->create(['status' => 'active']);
        Product::factory()->create(['status' => 'inactive']);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
