<?php

namespace App\Application\Rental\Data;

final class StoredRental
{
    public function __construct(
        public readonly int $id,
        public readonly string $number,
    ) {}
}
