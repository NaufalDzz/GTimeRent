<?php

namespace App\Application\Rental\Data;

use App\Domain\Catalog\UnitCondition;

/**
 * Data unit yang sudah dikunci untuk checkout.
 */
final class CheckoutUnit
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly string $name,
        public readonly string $contents,
        public readonly int $packagePrice,
        public readonly int $dailyFine,
        public readonly UnitCondition $condition,
        public readonly bool $archived,
        public readonly bool $held,
    ) {}

    public function unavailableReason(): ?string
    {
        return match (true) {
            $this->archived => "Unit {$this->code} sudah diarsipkan.",
            $this->condition !== UnitCondition::Ready => "Unit {$this->code} sedang maintenance.",
            $this->held => "Unit {$this->code} baru saja dipesan anggota lain.",
            default => null,
        };
    }
}
