<?php

namespace App\Application\Rental;

use App\Application\Rental\Data\Actor;
use App\Domain\Rental\Rental;
use DateTimeImmutable;

final class RecordReceipt extends ChangeRentalStatus
{
    public function handle(Actor $actor, int $rentalId, DateTimeImmutable $receivedAt): Rental
    {
        return $this->change($actor, $rentalId, function (Rental $rental, DateTimeImmutable $now) use ($actor, $receivedAt): void {
            $rental->recordReceipt($receivedAt, $actor->id, $now);
        });
    }
}
