<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Collection;

abstract class MarketplaceService
{
    public function __construct(protected array $channel = [])
    {
    }

    abstract public function syncProducts(Collection $products): array;

    abstract public function syncOrders(): array;

    public function platform(): string
    {
        return $this->channel['platform'] ?? 'unknown';
    }
}
