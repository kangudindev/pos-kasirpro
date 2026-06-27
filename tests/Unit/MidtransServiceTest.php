<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Payment\MidtransService;

class MidtransServiceTest extends TestCase
{
    public function test_client_key_returns_configured_value(): void
    {
        $service = new MidtransService();
        $this->assertNotEmpty($service->getClientKey());
    }

    public function test_is_production_returns_boolean(): void
    {
        $service = new MidtransService();
        $this->assertIsBool($service->isProduction());
    }

    public function test_validate_signature_with_valid_data(): void
    {
        $service = new MidtransService();
        
        $data = [
            'order_id' => 'TEST-123',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => hash('sha512', 'TEST-123200100000.00' . config('services.midtrans.server_key')),
        ];

        $result = $service->validateSignature($data);
        $this->assertTrue($result);
    }
}
