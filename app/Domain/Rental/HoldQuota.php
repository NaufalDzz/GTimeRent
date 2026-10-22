<?php

namespace App\Domain\Rental;

/**
 * Kuota unit yang boleh ditahan satu anggota di seluruh pesanan.
 */
final class HoldQuota
{
    public const MAX_UNITS = 2;

    public static function assertCanHold(int $alreadyHeld, int $requested): void
    {
        if ($requested < 1 || $alreadyHeld + $requested > self::MAX_UNITS) {
            throw RentalRuleViolation::quotaExceeded($alreadyHeld, $requested);
        }
    }

    public static function remaining(int $alreadyHeld): int
    {
        return max(0, self::MAX_UNITS - $alreadyHeld);
    }
}
