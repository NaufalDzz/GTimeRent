<?php

namespace App\Domain\Rental;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::Transfer => 'Transfer',
        };
    }
}
