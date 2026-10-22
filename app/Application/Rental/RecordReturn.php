<?php

namespace App\Application\Rental;

use App\Application\Rental\Data\Actor;
use App\Domain\Rental\Rental;
use DateTimeImmutable;

final class RecordReturn extends ChangeRentalStatus
{
    public function handle(Actor $actor, int $rentalId, DateTimeImmutable $returnedAt, ?string $notes): Rental
    {
        return $this->change($actor, $rentalId, function (Rental $rental, DateTimeImmutable $now) use ($actor, $returnedAt, $notes): void {
            $rental->recordReturn($returnedAt, $actor->id, $now, $notes);
        });
    }
}
