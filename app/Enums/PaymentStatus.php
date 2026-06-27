<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PAID = 'paid';
    case DUE = 'due';

    public function label(): string
    {
        return match ($this) {
            self::PAID => 'Lunas',
            self::DUE => 'Belum Lunas',
        };
    }
}
