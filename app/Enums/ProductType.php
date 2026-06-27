<?php

namespace App\Enums;

enum ProductType: string
{
    case SINGLE = 'single';
    case VARIABLE = 'variable';
    case COMBO = 'combo';
    case MODIFIER = 'modifier';

    public function label(): string
    {
        return match ($this) {
            self::SINGLE => 'Produk Tunggal',
            self::VARIABLE => 'Produk Variabel',
            self::COMBO => 'Paket/Kombo',
            self::MODIFIER => 'Modifier',
        };
    }
}
