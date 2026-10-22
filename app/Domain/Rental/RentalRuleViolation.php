<?php

namespace App\Domain\Rental;

use DomainException;

/**
 * Pelanggaran aturan bisnis sewa. Pesannya aman ditampilkan ke pengguna.
 */
class RentalRuleViolation extends DomainException
{
    public static function invalidTransition(RentalStatus $from, string $action): self
    {
        return new self("Tidak dapat {$action}: pesanan berstatus {$from->label()}.");
    }

    public static function quotaExceeded(int $held, int $requested): self
    {
        $remaining = max(0, HoldQuota::MAX_UNITS - $held);

        return new self(
            "Kuota sewa terlampaui: kamu sedang menahan {$held} unit dan memilih {$requested} unit. "
            .'Maksimal '.HoldQuota::MAX_UNITS." unit ditahan di seluruh pesanan, jadi sisa kuotamu {$remaining} unit."
        );
    }
}
