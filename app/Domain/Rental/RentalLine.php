<?php

namespace App\Domain\Rental;

/**
 * Satu unit dalam pesanan, berisi snapshot data unit saat checkout.
 */
final class RentalLine
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $unitId,
        public readonly string $code,
        public readonly string $name,
        public readonly string $contents,
        public readonly int $packagePrice,
        public readonly int $dailyFine,
        private int $lateDays = 0,
        private int $fineAmount = 0,
    ) {}

    public function applyLateDays(int $lateDays): void
    {
        $this->lateDays = $lateDays;
        $this->fineAmount = $lateDays * $this->dailyFine;
    }

    public function lateDays(): int
    {
        return $this->lateDays;
    }

    public function fineAmount(): int
    {
        return $this->fineAmount;
    }
}
