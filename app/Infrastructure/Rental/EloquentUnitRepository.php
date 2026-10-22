<?php

namespace App\Infrastructure\Rental;

use App\Application\Rental\Contracts\UnitRepository;
use App\Application\Rental\Data\CheckoutUnit;
use App\Domain\Rental\RentalStatus;
use App\Models\RentalItem;
use App\Models\Setting;
use App\Models\Unit;

final class EloquentUnitRepository implements UnitRepository
{
    public function lockForCheckout(array $unitIds): array
    {
        $units = Unit::withTrashed()
            ->whereIn('id', $unitIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        // Dibaca setelah kunci unit diperoleh, sehingga pesanan yang baru commit ikut terlihat.
        $heldIds = RentalItem::query()
            ->join('rentals', 'rentals.id', '=', 'rental_items.rental_id')
            ->whereIn('rental_items.unit_id', $unitIds)
            ->whereIn('rentals.status', RentalStatus::holdingValues())
            ->pluck('rental_items.unit_id')
            ->all();

        return $units->map(fn (Unit $unit): CheckoutUnit => new CheckoutUnit(
            $unit->id,
            $unit->code,
            $unit->name,
            $unit->contents_text,
            $unit->package_price,
            $unit->daily_fine,
            $unit->condition_status,
            $unit->trashed(),
            in_array($unit->id, $heldIds),
        ))->values()->all();
    }

    public function flatShippingFee(): int
    {
        return Setting::current()->flat_shipping_fee;
    }

    public function serviceAreas(): array
    {
        return config('gtrent.service_areas');
    }
}
