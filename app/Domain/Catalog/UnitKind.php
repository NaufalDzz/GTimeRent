<?php

namespace App\Domain\Catalog;

enum UnitKind: string
{
    case PcMonitor = 'pc_monitor';
    case Peripheral = 'peripheral';

    public function label(): string
    {
        return match ($this) {
            self::PcMonitor => 'PC + Monitor',
            self::Peripheral => 'Peripheral',
        };
    }
}
