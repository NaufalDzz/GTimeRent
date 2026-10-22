<?php

namespace App\Application\Rental\Contracts;

use App\Application\Rental\Data\CheckoutUnit;

interface UnitRepository
{
    /**
     * Mengunci unit terpilih dalam urutan id lalu membaca status ketersediaannya,
     * termasuk unit yang diarsipkan.
     *
     * @param  list<int>  $unitIds
     * @return list<CheckoutUnit>
     */
    public function lockForCheckout(array $unitIds): array;

    public function flatShippingFee(): int;

    /**
     * @return list<string>
     */
    public function serviceAreas(): array;
}
