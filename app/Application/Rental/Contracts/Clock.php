<?php

namespace App\Application\Rental\Contracts;

use DateTimeImmutable;

interface Clock
{
    /**
     * Waktu sekarang dalam UTC.
     */
    public function now(): DateTimeImmutable;
}
