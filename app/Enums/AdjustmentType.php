<?php

namespace App\Enums;

enum AdjustmentType: string
{
    case NORMAL = 'normal';
    case ABNORMAL = 'abnormal';

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => 'Normal',
            self::ABNORMAL => 'Abnormal',
        };
    }
}
