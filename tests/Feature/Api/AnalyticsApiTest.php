<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnalyticsApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_access_overview_analytics()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/analytics/overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_users',
                    'total_merchants',
                    'total_affiliates',
                    'total_products',
                    'total_orders'
                ]
            ]);
    }

    public function test_merchant_cannot_access_overview_analytics()
    {
        $merchant = User::factory()->create();
        $merchant->assignRole('Merchant');

        $token = $merchant->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/analytics/overview');

        $response->assertStatus(403);
    }

    public function test_merchant_can_access_own_analytics()
    {
        $merchant = User::factory()->create();
        $merchant->assignRole('Merchant');

        $token = $merchant->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/analytics/merchant-report');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_products',
                    'total_orders',
                    'confirmed_orders',
                    'conversion_rate',
                    'top_products'
                ]
            ]);
    }

    public function test_admin_can_access_sales_by_merchant()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/analytics/merchants');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
