<?php

namespace App\Infrastructure\Rental;

use App\Application\Rental\Contracts\RentalRepository;
use App\Application\Rental\Data\StoredRental;
use App\Domain\Rental\Rental;
use App\Domain\Rental\RentalLine;
use App\Domain\Rental\RentalStatus;
use App\Models\Rental as RentalModel;
use App\Models\RentalItem;
use App\Models\User;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Support\Str;

final class EloquentRentalRepository implements RentalRepository
{
    /**
     * Pemetaan properti entity ke kolom tabel rentals.
     */
    private const STATE_COLUMNS = [
        'paymentMethod' => 'payment_method',
        'paymentConfirmedBy' => 'payment_confirmed_by',
        'paymentConfirmedAt' => 'payment_confirmed_at',
        'dispatchedAt' => 'dispatched_at',
        'dispatchedBy' => 'dispatched_by',
        'receivedAt' => 'received_at',
        'receiptRecordedAt' => 'receipt_recorded_at',
        'receiptRecordedBy' => 'receipt_recorded_by',
        'dueAt' => 'due_at',
        'returnedAt' => 'returned_at',
        'returnRecordedAt' => 'return_recorded_at',
        'returnedBy' => 'returned_by',
        'returnNotes' => 'return_notes',
        'cancelledAt' => 'cancelled_at',
        'cancelledBy' => 'cancelled_by',
        'cancelReason' => 'cancel_reason',
    ];

    public function lockMember(int $memberId): bool
    {
        return User::query()
            ->whereKey($memberId)
            ->where('role', User::ROLE_MEMBER)
            ->lockForUpdate()
            ->exists();
    }

    public function countHeldUnits(int $memberId): int
    {
        return RentalItem::query()
            ->join('rentals', 'rentals.id', '=', 'rental_items.rental_id')
            ->where('rentals.user_id', $memberId)
            ->whereIn('rentals.status', RentalStatus::holdingValues())
            ->count();
    }

    public function findForUpdate(int $rentalId): ?Rental
    {
        $model = RentalModel::query()->whereKey($rentalId)->lockForUpdate()->first();
        if ($model === null) {
            return null;
        }

        $lines = $model->items()->get()->map(fn (RentalItem $item): RentalLine => new RentalLine(
            $item->id,
            $item->unit_id,
            $item->code_snapshot,
            $item->name_snapshot,
            $item->contents_snapshot,
            $item->package_price_snapshot,
            $item->daily_fine_snapshot,
            $item->late_days,
            $item->fine_amount,
        ))->all();

        $state = [];
        foreach (self::STATE_COLUMNS as $property => $column) {
            $value = $model->getAttribute($column);
            $state[$property] = $value instanceof DateTimeInterface ? $this->utc($value) : $value;
        }

        return Rental::restore($model->id, $model->user_id, $model->status, $lines, $model->shipping_fee_snapshot, $state);
    }

    public function insert(Rental $rental, string $recipientName, string $recipientPhone, string $address, string $serviceArea, DateTimeImmutable $now): StoredRental
    {
        $model = new RentalModel;
        $model->forceFill([
            'number' => 'TMP-'.Str::ulid(),
            'user_id' => $rental->memberId,
            'status' => $rental->status(),
            'recipient_name' => $recipientName,
            'recipient_phone' => $recipientPhone,
            'delivery_address' => $address,
            'service_area' => $serviceArea,
            'shipping_fee_snapshot' => $rental->shippingFee,
            'subtotal' => $rental->subtotal(),
            'initial_total' => $rental->initialTotal(),
            'created_at' => $this->utc($now),
            'updated_at' => $this->utc($now),
        ]);
        $model->save();

        $model->forceFill(['number' => $this->formatNumber($model->id, $now)])->save();

        foreach ($rental->lines as $line) {
            $model->items()->save((new RentalItem)->forceFill([
                'unit_id' => $line->unitId,
                'code_snapshot' => $line->code,
                'name_snapshot' => $line->name,
                'contents_snapshot' => $line->contents,
                'package_price_snapshot' => $line->packagePrice,
                'daily_fine_snapshot' => $line->dailyFine,
            ]));
        }

        return new StoredRental($model->id, $model->number);
    }

    public function update(Rental $rental): void
    {
        $model = RentalModel::query()->findOrFail($rental->id);

        $attributes = ['status' => $rental->status()];
        foreach ($rental->state() as $property => $value) {
            $attributes[self::STATE_COLUMNS[$property]] = $value instanceof DateTimeImmutable ? $this->utc($value) : $value;
        }
        $model->forceFill($attributes)->save();

        foreach ($rental->lines as $line) {
            RentalItem::query()->whereKey($line->id)->update([
                'late_days' => $line->lateDays(),
                'fine_amount' => $line->fineAmount(),
            ]);
        }
    }

    /**
     * Nomor transaksi GTR-YYYYMMDD-NNNN (tanggal WIB, urutan dari id agar selalu unik).
     */
    private function formatNumber(int $id, DateTimeImmutable $now): string
    {
        $date = $now->setTimezone(new DateTimeZone(config('gtrent.display_timezone')))->format('Ymd');

        return sprintf('GTR-%s-%04d', $date, $id);
    }

    private function utc(DateTimeInterface $value): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($value)->setTimezone(new DateTimeZone('UTC'));
    }
}
