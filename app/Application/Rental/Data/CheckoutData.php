<?php

namespace App\Application\Rental\Data;

final class CheckoutData
{
    /**
     * @param  list<int>  $unitIds
     */
    public function __construct(
        public readonly array $unitIds,
        public readonly string $recipientName,
        public readonly string $recipientPhone,
        public readonly string $address,
        public readonly string $serviceArea,
    ) {}
}
