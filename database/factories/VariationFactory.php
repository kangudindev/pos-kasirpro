<?php

namespace Database\Factories;

use App\Models\Product\Variation;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class VariationFactory extends Factory
{
    protected $model = Variation::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => $this->faker->word(),
            'sub_sku' => $this->faker->unique()->ean8(),
            'default_purchase_price' => $this->faker->numberBetween(10000, 50000),
            'default_sell_price' => $this->faker->numberBetween(15000, 80000),
            'sell_price_inc_tax' => $this->faker->numberBetween(15000, 85000),
            'is_active' => true,
        ];
    }
}
