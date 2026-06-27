<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Auth\User;
use App\Models\Business\Business;
use App\Models\Product\Product;
use App\Models\Product\Variation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $business;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::factory()->create();
        $this->user = User::factory()->create([
            'business_id' => $this->business->id,
        ]);
        
        $this->product = Product::factory()->create([
            'business_id' => $this->business->id,
        ]);
    }

    public function test_pos_page_loads_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/pos');

        $response->assertStatus(200);
        $response->assertViewIs('pos.create');
    }

    public function test_can_search_product_by_barcode(): void
    {
        $variation = Variation::factory()->create([
            'product_id' => $this->product->id,
            'sub_sku' => 'TEST-123',
        ]);

        $response = $this->actingAs($this->user)
            ->post('/pos/search-barcode', [
                'barcode' => 'TEST-123',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('variation_id', $variation->id);
    }

    public function test_search_nonexistent_product_returns_404(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/pos/search-barcode', [
                'barcode' => 'NONEXISTENT',
            ]);

        $response->assertStatus(404);
        $response->assertJsonPath('error', 'Product not found');
    }
}
