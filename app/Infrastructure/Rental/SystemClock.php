<?php

namespace App\Infrastructure\Rental;

use App\Application\Rental\Contracts\Clock;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Jam sistem melalui helper now() Laravel, sehingga Carbon::setTestNow tetap berlaku pada uji fitur.
 */
final class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return now()->toDateTimeImmutable()->setTimezone(new DateTimeZone('UTC'));
    }
}
