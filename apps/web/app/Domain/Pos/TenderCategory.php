<?php

declare(strict_types=1);

namespace App\Domain\Pos;

// Author by Lab | zefry
enum TenderCategory: string
{
    case CASH = 'CASH';
    case MANUAL_EXTERNAL = 'MANUAL_EXTERNAL';

    public function evidenceMode(): string
    {
        return match ($this) {
            self::CASH => 'CASH_COUNTED',
            self::MANUAL_EXTERNAL => 'OPERATOR_RECORDED',
        };
    }
}
