<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Auth\User;
use App\Models\Business\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::factory()->create();
        $this->user = User::factory()->create([
            'business_id' => $this->business->id,
        ]);
    }

    public function test_payment_endpoint_requires_authentication(): void
    {
        $response = $this->post('/api/v1/payment/create', [
            'transaction_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_payment_validation_requires_transaction_id(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->post('/api/v1/payment/create', []);

        $response->assertStatus(422);
    }

    public function test_webhook_signature_validation(): void
    {
        $response = $this->post('/api/v1/payment/webhook', [
            'order_id' => 'TEST-123',
            'transaction_status' => 'settlement',
            'signature_key' => 'invalid_signature',
        ]);

        $response->assertStatus(403);
    }
}
