<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Product\Product;
use App\Models\Product\Variation;
use App\Models\Product\ProductVariation;
use App\Models\Product\VariationLocationDetail;

class ProductUtil extends Util
{
    /**
     * Create single product variation (dummy)
     */
    public function createSingleProductVariation($product, $businessId)
    {
        // Create product variation group
        $productVariation = ProductVariation::create([
            'business_id' => $businessId,
            'product_id' => $product->id,
            'name' => $product->name,
            'is_dummy' => true,
        ]);

        // Create single variation
        $variation = Variation::create([
            'name' => $product->name,
            'product_id' => $product->id,
            'sub_sku' => $product->sku,
            'product_variation_id' => $productVariation->id,
            'default_purchase_price' => 0,
            'dpp_inc_tax' => 0,
            'profit_percent' => 0,
            'default_sell_price' => 0,
            'sell_price_inc_tax' => 0,
            'is_active' => true,
        ]);

        return $variation;
    }

    /**
     * Create variable product variations
     */
    public function createVariableProductVariations($request, $product, $businessId)
    {
        $variations = [];

        if ($request->has('variation_templates')) {
            foreach ($request->variation_templates as $templateId => $values) {
                $template = DB::table('variation_templates')->find($templateId);

                foreach ($values as $value) {
                    $variation = Variation::create([
                        'name' => $value,
                        'product_id' => $product->id,
                        'sub_sku' => $this->generateSubSku($product, $template->name, $value),
                        'product_variation_id' => $templateId,
                        'default_purchase_price' => $request->input("purchase_price.{$templateId}.{$value}", 0),
                        'default_sell_price' => $request->input("sell_price.{$templateId}.{$value}", 0),
                        'is_active' => true,
                    ]);

                    $variations[] = $variation;
                }
            }
        }

        return $variations;
    }

    /**
     * Generate sub SKU
     */
    protected function generateSubSku($product, $templateName, $value)
    {
        $prefix = strtoupper(substr($templateName, 0, 2));
        $suffix = strtoupper(substr($value, 0, 2));

        return $product->sku . '-' . $prefix . $suffix;
    }

    /**
     * Update product quantity at location
     */
    public function updateProductQuantity($variationId, $locationId, $quantity)
    {
        $detail = VariationLocationDetail::firstOrCreate(
            [
                'variation_id' => $variationId,
                'location_id' => $locationId,
            ],
            ['qty_available' => 0]
        );

        $detail->qty_available = $quantity;
        $detail->save();

        return $detail;
    }

    /**
     * Decrease product quantity
     */
    public function decreaseProductQuantity($variationId, $locationId, $quantity)
    {
        $detail = VariationLocationDetail::firstOrCreate(
            [
                'variation_id' => $variationId,
                'location_id' => $locationId,
            ],
            ['qty_available' => 0]
        );

        $detail->qty_available -= $quantity;
        $detail->save();

        return $detail;
    }

    /**
     * Increase product quantity
     */
    public function increaseProductQuantity($variationId, $locationId, $quantity)
    {
        $detail = VariationLocationDetail::firstOrCreate(
            [
                'variation_id' => $variationId,
                'location_id' => $locationId,
            ],
            ['qty_available' => 0]
        );

        $detail->qty_available += $quantity;
        $detail->save();

        return $detail;
    }

    /**
     * Get current stock for variation at location
     */
    public function getCurrentStock($variationId, $locationId)
    {
        $detail = VariationLocationDetail::where('variation_id', $variationId)
            ->where('location_id', $locationId)
            ->first();

        return $detail ? $detail->qty_available : 0;
    }

    /**
     * Calculate invoice total
     */
    public function calculateInvoiceTotal($products, $businessId, $locationId)
    {
        $totalBeforeTax = 0;
        $totalTax = 0;

        foreach ($products as $item) {
            $variation = Variation::find($item['variation_id']);
            if (!$variation) continue;

            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? $variation->default_sell_price;
            $lineTotal = $quantity * $unitPrice;

            $taxAmount = 0;
            if ($item['tax_id'] ?? null) {
                $taxRate = DB::table('tax_rates')->find($item['tax_id']);
                if ($taxRate) {
                    $taxAmount = ($lineTotal * $taxRate->amount) / 100;
                }
            }

            $totalBeforeTax += $lineTotal;
            $totalTax += $taxAmount;
        }

        return [
            'total_before_tax' => $totalBeforeTax,
            'tax_amount' => $totalTax,
            'total' => $totalBeforeTax + $totalTax,
        ];
    }

    /**
     * Get product discount
     */
    public function getProductDiscount($product, $variation, $contactGroupId = null, $priceGroupId = null)
    {
        $discount = 0;

        // Check product discount
        if ($product->discount_rate > 0) {
            if ($product->discount_type == 'percentage') {
                $discount = ($variation->default_sell_price * $product->discount_rate) / 100;
            } else {
                $discount = $product->discount_rate;
            }
        }

        return $discount;
    }

    /**
     * Get product stock details
     */
    public function getProductStockDetails($productId, $locationId = null)
    {
        $query = VariationLocationDetail::whereHas('variation', function ($q) use ($productId) {
            $q->where('product_id', $productId);
        });

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query->get();
    }

    /**
     * Get product alert (low stock)
     */
    public function getProductAlert($businessId, $locationId = null)
    {
        $query = Product::where('business_id', $businessId)
            ->where('enable_stock', true)
            ->whereColumn('alert_quantity', '>', 0);

        // This is a simplified version - in production, you'd check actual stock
        return $query->get();
    }

    /**
     * Update variation stock
     */
    public function updateProductStock($transactionId, $transactionType, $status)
    {
        $lines = match ($transactionType) {
            'sell' => DB::table('transaction_sell_lines')
                ->where('transaction_id', $transactionId)
                ->get(),
            'purchase' => DB::table('purchase_lines')
                ->where('transaction_id', $transactionId)
                ->get(),
            default => collect(),
        };

        foreach ($lines as $line) {
            $quantity = $line->quantity;

            if ($transactionType == 'sell') {
                if ($status == 'final') {
                    $this->decreaseProductQuantity($line->variation_id, $line->location_id ?? 1, $quantity);
                } else {
                    $this->increaseProductQuantity($line->variation_id, $line->location_id ?? 1, $quantity);
                }
            } elseif ($transactionType == 'purchase') {
                if ($status == 'received') {
                    $this->increaseProductQuantity($line->variation_id, $line->location_id ?? 1, $quantity);
                } else {
                    $this->decreaseProductQuantity($line->variation_id, $line->location_id ?? 1, $quantity);
                }
            }
        }
    }
}
