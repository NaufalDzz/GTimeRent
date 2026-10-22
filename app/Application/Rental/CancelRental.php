<?php

namespace App\Application\Rental;

use App\Application\Rental\Data\Actor;
use App\Domain\Rental\Rental;
use DateTimeImmutable;

final class CancelRental extends ChangeRentalStatus
{
    public function handle(Actor $actor, int $rentalId, string $reason): Rental
    {
        return $this->change($actor, $rentalId, function (Rental $rental, DateTimeImmutable $now) use ($actor, $reason): void {
            $rental->cancel($reason, $actor->id, $now);
        });
    }
}
