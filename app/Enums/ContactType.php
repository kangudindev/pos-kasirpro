<?php

namespace App\Enums;

enum ContactType: string
{
    case CUSTOMER = 'customer';
    case SUPPLIER = 'supplier';
    case BOTH = 'both';
    case LEAD = 'lead';

    public function label(): string
    {
        return match ($this) {
            self::CUSTOMER => 'Pelanggan',
            self::SUPPLIER => 'Supplier',
            self::BOTH => 'Pelanggan & Supplier',
            self::LEAD => 'Prospek',
        };
    }
}
