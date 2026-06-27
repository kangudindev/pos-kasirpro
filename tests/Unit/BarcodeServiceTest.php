<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BarcodeServiceTest extends TestCase
{
    public function test_ean13_checksum_calculation(): void
    {
        $checksum = $this->calculateEanChecksum('899123456789');
        $this->assertEquals('2', $checksum);
    }

    public function test_ean13_checksum_with_all_zeros(): void
    {
        $checksum = $this->calculateEanChecksum('000000000000');
        $this->assertEquals('0', $checksum);
    }

    protected function calculateEanChecksum(string $code): string
    {
        $sum = 0;
        for ($i = 0; $i < strlen($code); $i++) {
            $digit = (int) $code[$i];
            $sum += $digit * ($i % 2 === 0 ? 1 : 3);
        }
        return (string) ((10 - ($sum % 10)) % 10);
    }
}
