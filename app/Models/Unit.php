<?php

namespace App\Models;

use App\Domain\Catalog\UnitCondition;
use App\Domain\Catalog\UnitKind;
use App\Domain\Rental\RentalStatus;
use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'name', 'kind', 'specs_text', 'contents_text', 'package_price', 'daily_fine', 'condition_status'])]
class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => UnitKind::class,
            'condition_status' => UnitCondition::class,
            'package_price' => 'integer',
            'daily_fine' => 'integer',
        ];
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTrashed()->orderBy('name');
    }

    /**
     * @return HasMany<RentalItem, $this>
     */
    public function rentalItems(): HasMany
    {
        return $this->hasMany(RentalItem::class);
    }

    /**
     * Unit yang sedang ditahan pesanan berstatus non-terminal.
     */
    #[Scope]
    protected function held(Builder $query): void
    {
        $query->whereHas('rentalItems.rental', fn (Builder $rental) => $rental->whereIn('status', RentalStatus::holdingValues()));
    }

    /**
     * Unit siap pakai, tidak diarsipkan, dan tidak ditahan.
     */
    #[Scope]
    protected function available(Builder $query): void
    {
        $query->where('condition_status', UnitCondition::Ready)
            ->whereDoesntHave('rentalItems.rental', fn (Builder $rental) => $rental->whereIn('status', RentalStatus::holdingValues()));
    }

    /**
     * Menambahkan atribut is_held ke hasil query tanpa N+1.
     */
    #[Scope]
    protected function withHeldFlag(Builder $query): void
    {
        $query->withExists(['rentalItems as is_held' => fn (Builder $item) => $item->whereHas(
            'rental',
            fn (Builder $rental) => $rental->whereIn('status', RentalStatus::holdingValues()),
        )]);
    }

    public function isHeld(): bool
    {
        if (array_key_exists('is_held', $this->attributes)) {
            return (bool) $this->attributes['is_held'];
        }

        return $this->rentalItems()
            ->whereHas('rental', fn (Builder $rental) => $rental->whereIn('status', RentalStatus::holdingValues()))
            ->exists();
    }

    public function isAvailable(): bool
    {
        return ! $this->trashed() && $this->condition_status === UnitCondition::Ready && ! $this->isHeld();
    }

    /**
     * Status ketersediaan untuk tampilan katalog/admin.
     */
    public function availabilityLabel(): string
    {
        return match (true) {
            $this->trashed() => 'Diarsipkan',
            $this->condition_status === UnitCondition::Maintenance => 'Maintenance',
            $this->isHeld() => 'Sedang ditahan',
            default => 'Tersedia',
        };
    }

    /**
     * Kunci warna badge ketersediaan di tampilan.
     */
    public function availabilityTone(): string
    {
        return match (true) {
            $this->trashed(), $this->condition_status === UnitCondition::Maintenance => 'muted',
            $this->isHeld() => 'warning',
            default => 'success',
        };
    }

    /**
     * Daftar kelengkapan per baris.
     *
     * @return list<string>
     */
    public function contentsList(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->contents_text))));
    }

    /**
     * Spesifikasi per komponen; dipisah baris baru atau titik koma.
     *
     * @return list<string>
     */
    public function specsList(): array
    {
        return array_values(array_filter(array_map(
            fn (string $part): string => rtrim(trim($part), '.'),
            preg_split('/\R|;/', (string) $this->specs_text),
        )));
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
