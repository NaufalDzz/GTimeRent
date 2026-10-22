<?php

namespace App\Models;

use App\Domain\Rental\PaymentMethod;
use App\Domain\Rental\RentalPeriod;
use App\Domain\Rental\RentalStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model baca/tulis untuk tabel rentals. Perubahan status hanya lewat use case Application.
 */
class Rental extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RentalStatus::class,
            'payment_method' => PaymentMethod::class,
            'shipping_fee_snapshot' => 'integer',
            'subtotal' => 'integer',
            'initial_total' => 'integer',
            'payment_confirmed_at' => 'immutable_datetime',
            'dispatched_at' => 'immutable_datetime',
            'received_at' => 'immutable_datetime',
            'receipt_recorded_at' => 'immutable_datetime',
            'due_at' => 'immutable_datetime',
            'returned_at' => 'immutable_datetime',
            'return_recorded_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return HasMany<RentalItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(RentalItem::class)->orderBy('id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function paymentConfirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_confirmed_by')->withTrashed();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by')->withTrashed();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function receiptRecorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receipt_recorded_by')->withTrashed();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function returnRecorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by')->withTrashed();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by')->withTrashed();
    }

    #[Scope]
    protected function holding(Builder $query): void
    {
        $query->whereIn('status', RentalStatus::holdingValues());
    }

    #[Scope]
    protected function overdue(Builder $query): void
    {
        $query->where('status', RentalStatus::Active)->where('due_at', '<', now());
    }

    public function isOverdue(): bool
    {
        return RentalPeriod::isOverdue($this->status, $this->due_at, now()->toDateTimeImmutable());
    }

    /**
     * Hari terlambat berjalan untuk pesanan aktif (estimasi, belum final).
     */
    public function estimatedLateDays(): int
    {
        if ($this->status !== RentalStatus::Active || $this->due_at === null) {
            return 0;
        }

        return RentalPeriod::lateDays($this->due_at, now()->toDateTimeImmutable());
    }

    public function estimatedFine(): int
    {
        $days = $this->estimatedLateDays();

        return $this->items->sum(fn (RentalItem $item): int => $days * $item->daily_fine_snapshot);
    }

    public function fineTotal(): int
    {
        return (int) $this->items->sum('fine_amount');
    }

    /**
     * Label status untuk tampilan, termasuk label turunan "Terlambat".
     */
    public function statusLabel(): string
    {
        return $this->isOverdue() ? 'Terlambat' : $this->status->label();
    }

    /**
     * Kunci warna badge status di tampilan.
     */
    public function statusTone(): string
    {
        if ($this->isOverdue()) {
            return 'danger';
        }

        return match ($this->status) {
            RentalStatus::PendingPayment => 'warning',
            RentalStatus::Paid, RentalStatus::InDelivery => 'info',
            RentalStatus::Active => 'success',
            RentalStatus::Returned, RentalStatus::Cancelled => 'muted',
        };
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }
}
