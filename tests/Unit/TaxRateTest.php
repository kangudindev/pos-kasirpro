<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Settings\TaxRate;

class TaxRateTest extends TestCase
{
    public function test_calculate_tax_returns_correct_amount(): void
    {
        $taxRate = new TaxRate();
        $taxRate->amount = 11.00;

        $result = $taxRate->calculateTax(100000);

        $this->assertEquals(11000, $result);
    }

    public function test_calculate_tax_with_zero_rate(): void
    {
        $taxRate = new TaxRate();
        $taxRate->amount = 0;

        $result = $taxRate->calculateTax(100000);

        $this->assertEquals(0, $result);
    }

    public function test_calculate_tax_with_decimal_amount(): void
    {
        $taxRate = new TaxRate();
        $taxRate->amount = 12.5;

        $result = $taxRate->calculateTax(80000);

        $this->assertEquals(10000, $result);
    }
}
