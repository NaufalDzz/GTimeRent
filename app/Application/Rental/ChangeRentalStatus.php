<?php

namespace App\Application\Rental;

use App\Application\Rental\Contracts\Clock;
use App\Application\Rental\Contracts\RentalRepository;
use App\Application\Rental\Contracts\TransactionRunner;
use App\Application\Rental\Data\Actor;
use App\Domain\Rental\Rental;
use App\Domain\Rental\RentalRuleViolation;
use Closure;
use DateTimeImmutable;

/**
 * Kerangka bersama use case admin: kunci pesanan, jalankan transisi entity, simpan.
 */
abstract class ChangeRentalStatus
{
    use AdminOnly;

    public function __construct(
        protected readonly RentalRepository $rentals,
        protected readonly TransactionRunner $transactions,
        protected readonly Clock $clock,
    ) {}

    /**
     * @param  Closure(Rental, DateTimeImmutable): void  $change
     */
    protected function change(Actor $actor, int $rentalId, Closure $change): Rental
    {
        $this->ensureAdmin($actor);

        return $this->transactions->run(function () use ($rentalId, $change): Rental {
            $rental = $this->rentals->findForUpdate($rentalId);
            if ($rental === null) {
                throw new RentalRuleViolation('Pesanan tidak ditemukan.');
            }

            $change($rental, $this->clock->now());
            $this->rentals->update($rental);

            return $rental;
        });
    }
}
