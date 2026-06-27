<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case RECEIVED = 'received';
    case PENDING = 'pending';
    case ORDERED = 'ordered';
    case DRAFT = 'draft';
    case FINAL = 'final';

    public function label(): string
    {
        return match ($this) {
            self::RECEIVED => 'Diterima',
            self::PENDING => 'Menunggu',
            self::ORDERED => 'Dipesan',
            self::DRAFT => 'Draft',
            self::FINAL => 'Selesai',
        };
    }
}
