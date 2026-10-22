<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalItem extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'package_price_snapshot' => 'integer',
            'daily_fine_snapshot' => 'integer',
            'late_days' => 'integer',
            'fine_amount' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Rental, $this>
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }

    /**
     * @return list<string>
     */
    public function contentsList(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->contents_snapshot))));
    }
}
