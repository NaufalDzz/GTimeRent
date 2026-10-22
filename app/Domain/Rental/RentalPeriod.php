<?php

namespace App\Domain\Rental;

use DateInterval;
use DateTimeImmutable;

/**
 * Masa sewa paket: 120 jam sejak seluruh unit diterima penyewa, dan denda per hari keterlambatan.
 */
final class RentalPeriod
{
    public const HOURS = 120;

    private const SECONDS_PER_DAY = 86400;

    public static function dueAt(DateTimeImmutable $receivedAt): DateTimeImmutable
    {
        return $receivedAt->add(new DateInterval('PT'.self::HOURS.'H'));
    }

    /**
     * Hari terlambat = ceil(keterlambatan positif / 24 jam). Tepat pada due_at bernilai nol.
     */
    public static function lateDays(DateTimeImmutable $dueAt, DateTimeImmutable $returnedAt): int
    {
        $lateSeconds = $returnedAt->getTimestamp() - $dueAt->getTimestamp();

        if ($lateSeconds <= 0) {
            return 0;
        }

        return intdiv($lateSeconds + self::SECONDS_PER_DAY - 1, self::SECONDS_PER_DAY);
    }

    public static function isOverdue(RentalStatus $status, ?DateTimeImmutable $dueAt, DateTimeImmutable $now): bool
    {
        return $status === RentalStatus::Active && $dueAt !== null && $now > $dueAt;
    }
}
