<?php

namespace Tests\Feature\Api;

use App\Models\AffiliateLink;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AffiliateLinkApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_affiliate_can_generate_link()
    {
        $affiliate = User::factory()->create();
        $affiliate->assignRole('Affiliate');

        $product = Product::factory()->create();

        $token = $affiliate->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/affiliate-links', [
                'product_id' => $product->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.product_id', $product->id)
            ->assertJsonPath('data.affiliate_id', $affiliate->id)
            ->assertJsonStructure(['data', 'referral_url']);

        $this->assertDatabaseHas('affiliate_links', [
            'product_id' => $product->id,
            'affiliate_id' => $affiliate->id,
        ]);
    }

    public function test_track_click_increments_clicks()
    {
        $link = AffiliateLink::factory()->create(['clicks' => 5]);

        $response = $this->getJson("/api/ref/{$link->code}");

        $response->assertStatus(200);

        $this->assertEquals(6, $link->fresh()->clicks);
    }

    public function test_affiliate_can_see_their_stats()
    {
        $affiliate = User::factory()->create();
        $affiliate->assignRole('Affiliate');

        AffiliateLink::factory()->count(2)->create(['affiliate_id' => $affiliate->id, 'clicks' => 10]);

        $token = $affiliate->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/affiliate-stats');

        $response->assertStatus(200)
            ->assertJsonPath('total_links', 2)
            ->assertJsonPath('total_clicks', 20);
    }

    public function test_affiliate_can_get_whatsapp_link()
    {
        $affiliate = User::factory()->create();
        $affiliate->assignRole('Affiliate');

        $link = AffiliateLink::factory()->create(['affiliate_id' => $affiliate->id]);

        $token = $affiliate->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/affiliate-links/{$link->id}/whatsapp?phone=123456789");

        $response->assertStatus(200)
            ->assertJsonStructure(['whatsapp_url']);

        $this->assertStringContainsString('123456789', $response->json('whatsapp_url'));
        $this->assertStringContainsString($link->code, $response->json('whatsapp_url'));
    }
}
