<?php

namespace App\Application\Rental;

use App\Application\Rental\Contracts\Clock;
use App\Application\Rental\Contracts\RentalRepository;
use App\Application\Rental\Contracts\TransactionRunner;
use App\Application\Rental\Contracts\UnitRepository;
use App\Application\Rental\Data\Actor;
use App\Application\Rental\Data\CheckoutData;
use App\Application\Rental\Data\CheckoutUnit;
use App\Application\Rental\Data\StoredRental;
use App\Domain\Rental\HoldQuota;
use App\Domain\Rental\Rental;
use App\Domain\Rental\RentalLine;
use App\Domain\Rental\RentalRuleViolation;

/**
 * Checkout atomik: kunci anggota, kunci unit berurutan id, hitung ulang kuota dan
 * ketersediaan, lalu buat pesanan menunggu bayar beserta snapshot. Tidak ada penahanan parsial.
 */
final class CheckoutRental
{
    public function __construct(
        private readonly RentalRepository $rentals,
        private readonly UnitRepository $units,
        private readonly TransactionRunner $transactions,
        private readonly Clock $clock,
    ) {}

    public function handle(Actor $actor, CheckoutData $data): StoredRental
    {
        if ($actor->isAdmin) {
            throw new RentalRuleViolation('Checkout hanya untuk akun anggota.');
        }

        $unitIds = $data->unitIds;
        if ($unitIds === [] || count($unitIds) > HoldQuota::MAX_UNITS) {
            throw new RentalRuleViolation('Pilih 1 sampai '.HoldQuota::MAX_UNITS.' unit untuk satu pesanan.');
        }
        if (count(array_unique($unitIds)) !== count($unitIds)) {
            throw new RentalRuleViolation('Kode unit dalam satu pesanan tidak boleh sama.');
        }
        if (! in_array($data->serviceArea, $this->units->serviceAreas(), true)) {
            throw new RentalRuleViolation('Area layanan tidak tersedia. Pilih '.implode(', ', $this->units->serviceAreas()).'.');
        }
        sort($unitIds);

        // Pernyataan pertama dalam transaksi harus berupa locking read, agar snapshot baca
        // konsisten (hitung kuota/ketersediaan) baru dibuat setelah kunci diperoleh.
        return $this->transactions->run(function () use ($actor, $data, $unitIds): StoredRental {
            if (! $this->rentals->lockMember($actor->id)) {
                throw new RentalRuleViolation('Akun anggota tidak aktif.');
            }

            $units = $this->units->lockForCheckout($unitIds);
            if (count($units) !== count($unitIds)) {
                throw new RentalRuleViolation('Unit yang dipilih tidak ditemukan.');
            }

            foreach ($units as $unit) {
                $reason = $unit->unavailableReason();
                if ($reason !== null) {
                    throw new RentalRuleViolation($reason.' Tidak ada unit yang ditahan; pilih unit lain.');
                }
            }

            HoldQuota::assertCanHold($this->rentals->countHeldUnits($actor->id), count($units));

            $rental = Rental::open(
                $actor->id,
                array_map(fn (CheckoutUnit $unit): RentalLine => new RentalLine(
                    null,
                    $unit->id,
                    $unit->code,
                    $unit->name,
                    $unit->contents,
                    $unit->packagePrice,
                    $unit->dailyFine,
                ), $units),
                $this->units->flatShippingFee(),
            );

            return $this->rentals->insert(
                $rental,
                trim($data->recipientName),
                trim($data->recipientPhone),
                trim($data->address),
                $data->serviceArea,
                $this->clock->now(),
            );
        });
    }
}
