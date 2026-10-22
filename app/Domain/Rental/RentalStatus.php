<?php

namespace App\Domain\Rental;

/**
 * Status pesanan sewa beserta transisi yang sah (PRD bagian 5).
 */
enum RentalStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case InDelivery = 'in_delivery';
    case Active = 'active';
    case Returned = 'returned';
    case Cancelled = 'cancelled';

    /**
     * Status yang masih menahan unit (dihitung untuk kuota dan ketersediaan).
     *
     * @return list<self>
     */
    public static function holding(): array
    {
        return [self::PendingPayment, self::Paid, self::InDelivery, self::Active];
    }

    /**
     * @return list<string>
     */
    public static function holdingValues(): array
    {
        return array_map(fn (self $status): string => $status->value, self::holding());
    }

    public function holdsUnits(): bool
    {
        return in_array($this, self::holding(), true);
    }

    public function isTerminal(): bool
    {
        return $this === self::Returned || $this === self::Cancelled;
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::PendingPayment => $next === self::Paid || $next === self::Cancelled,
            self::Paid => $next === self::InDelivery,
            self::InDelivery => $next === self::Active,
            self::Active => $next === self::Returned,
            self::Returned, self::Cancelled => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Menunggu bayar',
            self::Paid => 'Lunas',
            self::InDelivery => 'Dikirim',
            self::Active => 'Aktif',
            self::Returned => 'Dikembalikan',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
