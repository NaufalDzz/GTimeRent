<?php

namespace App\Application\Rental\Contracts;

use App\Application\Rental\Data\StoredRental;
use App\Domain\Rental\Rental;
use DateTimeImmutable;

interface RentalRepository
{
    /**
     * Mengunci baris anggota aktif untuk checkout. False bila anggota tidak ada atau diarsipkan.
     */
    public function lockMember(int $memberId): bool;

    /**
     * Jumlah unit yang sedang ditahan anggota di seluruh pesanan.
     */
    public function countHeldUnits(int $memberId): int;

    /**
     * Mengambil pesanan dengan kunci baris untuk perubahan status.
     */
    public function findForUpdate(int $rentalId): ?Rental;

    public function insert(Rental $rental, string $recipientName, string $recipientPhone, string $address, string $serviceArea, DateTimeImmutable $now): StoredRental;

    public function update(Rental $rental): void;
}
