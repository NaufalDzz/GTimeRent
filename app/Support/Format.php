<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * Format tampilan: rupiah dan waktu WIB.
 */
final class Format
{
    public static function rupiah(?int $amount): string
    {
        return 'Rp'.number_format((int) $amount, 0, ',', '.');
    }

    public static function wib(?DateTimeInterface $value, string $format = 'd M Y, H.i'): string
    {
        if ($value === null) {
            return '—';
        }

        return CarbonImmutable::instance($value)
            ->setTimezone(config('gtrent.display_timezone'))
            ->locale('id')
            ->translatedFormat($format).' WIB';
    }

    /**
     * Nilai untuk input datetime-local dalam WIB.
     */
    public static function wibInput(?DateTimeInterface $value): string
    {
        $value ??= now();

        return CarbonImmutable::instance($value)
            ->setTimezone(config('gtrent.display_timezone'))
            ->format('Y-m-d\TH:i:s');
    }

    /**
     * Mengubah input datetime-local WIB menjadi waktu UTC.
     */
    public static function fromWibInput(string $value): CarbonImmutable
    {
        return CarbonImmutable::parse($value, config('gtrent.display_timezone'))->utc();
    }
}
