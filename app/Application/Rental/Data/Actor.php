<?php

namespace App\Application\Rental\Data;

final class Actor
{
    public function __construct(
        public readonly int $id,
        public readonly bool $isAdmin,
    ) {}
}
