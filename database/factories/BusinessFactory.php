<?php

namespace Database\Factories;

use App\Models\Business\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'owner_id' => 1,
            'currency_id' => 1,
            'timezone' => 'Asia/Jakarta',
            'accounting_method' => 'fifo',
            'sell_price_tax' => 'excludes',
            'is_active' => true,
        ];
    }
}
