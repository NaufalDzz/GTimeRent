<?php

namespace App\Application\Rental;

use App\Application\Rental\Data\Actor;
use App\Domain\Rental\RentalRuleViolation;

/**
 * Pemeriksaan actor admin di dalam use case, sebagai lapisan kedua setelah policy HTTP.
 */
trait AdminOnly
{
    private function ensureAdmin(Actor $actor): void
    {
        if (! $actor->isAdmin) {
            throw new RentalRuleViolation('Hanya admin yang dapat mencatat perubahan status pesanan.');
        }
    }
}
