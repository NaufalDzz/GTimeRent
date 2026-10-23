<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['flat_shipping_fee', 'service_area_text'])]
class Setting extends Model
{
    public const DEFAULT_SHIPPING_FEE = 75000;

    public const DEFAULT_AREA_TEXT = 'Bandung dan sekitar Telkom University: Bojongsoang dan Dayeuhkolot. Admin memverifikasi alamat sebelum pembayaran dikonfirmasi.';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'flat_shipping_fee' => 'integer',
        ];
    }

    /**
     * Baris pengaturan tunggal (id = 1).
     */
    public static function current(): self
    {
        $setting = self::query()->find(1);

        if ($setting !== null) {
            return $setting;
        }

        $setting = new self;
        $setting->forceFill([
            'id' => 1,
            'flat_shipping_fee' => self::DEFAULT_SHIPPING_FEE,
            'service_area_text' => self::DEFAULT_AREA_TEXT,
        ])->save();

        return $setting;
    }
}
