<?php

namespace App\Application\Rental;

use App\Application\Rental\Data\Actor;
use App\Domain\Rental\PaymentMethod;
use App\Domain\Rental\Rental;
use DateTimeImmutable;

final class ConfirmPayment extends ChangeRentalStatus
{
    public function handle(Actor $actor, int $rentalId, PaymentMethod $method): Rental
    {
        return $this->change($actor, $rentalId, function (Rental $rental, DateTimeImmutable $now) use ($actor, $method): void {
            $rental->confirmPayment($method, $actor->id, $now);
        });
    }
}
