<?php

namespace App\Enums;

enum TransactionType: string
{
    case PURCHASE = 'purchase';
    case SELL = 'sell';
    case EXPENSE = 'expense';
    case STOCK_ADJUSTMENT = 'stock_adjustment';

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE => 'Pembelian',
            self::SELL => 'Penjualan',
            self::EXPENSE => 'Pengeluaran',
            self::STOCK_ADJUSTMENT => 'Penyesuaian Stok',
        };
    }
}
