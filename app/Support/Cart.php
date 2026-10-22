<?php

namespace App\Support;

use App\Domain\Rental\HoldQuota;
use App\Models\Unit;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\Collection;

/**
 * Keranjang anggota di session: daftar id unit, maksimal sebanyak kuota tahan.
 * Keranjang tidak menahan unit; ketersediaan dan kuota dicek ulang saat checkout.
 */
final class Cart
{
    private const KEY = 'cart.units';

    public function __construct(private readonly Session $session) {}

    /**
     * @return list<int>
     */
    public function ids(): array
    {
        return array_values(array_map('intval', (array) $this->session->get(self::KEY, [])));
    }

    public function count(): int
    {
        return count($this->ids());
    }

    public function has(int $unitId): bool
    {
        return in_array($unitId, $this->ids(), true);
    }

    /**
     * Unit di keranjang (termasuk yang diarsipkan agar bisa ditandai tidak tersedia).
     *
     * @return Collection<int, Unit>
     */
    public function units(): Collection
    {
        $ids = $this->ids();

        return Unit::withTrashed()->withHeldFlag()->whereIn('id', $ids)->get()
            ->sortBy(fn (Unit $unit): int|false => array_search($unit->id, $ids, true))
            ->values();
    }

    /**
     * Menambah unit; unit yang sudah ada diabaikan. Mengembalikan false jika keranjang penuh.
     *
     * @param  list<int>  $unitIds
     */
    public function add(array $unitIds): bool
    {
        $ids = array_values(array_unique([...$this->ids(), ...$unitIds]));
        if (count($ids) > HoldQuota::MAX_UNITS) {
            return false;
        }

        $this->session->put(self::KEY, $ids);

        return true;
    }

    public function remove(int $unitId): void
    {
        $this->session->put(self::KEY, array_values(array_diff($this->ids(), [$unitId])));
    }

    public function clear(): void
    {
        $this->session->forget(self::KEY);
    }
}
