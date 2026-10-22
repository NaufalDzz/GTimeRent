<?php

namespace App\Application\Rental;

use App\Application\Rental\Data\Actor;
use App\Domain\Rental\Rental;
use DateTimeImmutable;

final class DispatchRental extends ChangeRentalStatus
{
    public function handle(Actor $actor, int $rentalId, DateTimeImmutable $dispatchedAt): Rental
    {
        return $this->change($actor, $rentalId, function (Rental $rental, DateTimeImmutable $now) use ($actor, $dispatchedAt): void {
            $rental->dispatch($dispatchedAt, $actor->id, $now);
        });
    }
}
