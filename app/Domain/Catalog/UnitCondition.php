<?php

namespace App\Domain\Catalog;

enum UnitCondition: string
{
    case Ready = 'ready';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Ready => 'Siap',
            self::Maintenance => 'Maintenance',
        };
    }
}
