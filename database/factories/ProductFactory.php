<?php

namespace Database\Factories;

use App\Models\Business\Business;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => $this->faker->words(3, true),
            'sku' => $this->faker->unique()->ean8(),
            'description' => $this->faker->sentence(),
            'purchase_price' => $this->faker->numberBetween(10000, 100000),
            'sell_price' => $this->faker->numberBetween(15000, 150000),
            'is_active' => true,
            'type' => 'single',
            'enable_stock' => true,
        ];
    }
}
