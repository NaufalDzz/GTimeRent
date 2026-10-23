<?php

namespace Tests\Feature\Admin\Master;

use App\Application\Rental\CancelRental;
use App\Application\Rental\CheckoutRental;
use App\Application\Rental\ConfirmPayment;
use App\Application\Rental\Data\Actor;
use App\Application\Rental\Data\CheckoutData;
use App\Application\Rental\DispatchRental;
use App\Application\Rental\RecordReceipt;
use App\Application\Rental\RecordReturn;
use App\Domain\Rental\PaymentMethod;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;

/**
 * Membuat pesanan lewat use case Application, sama seperti DemoRentalSeeder.
 */
trait CreatesRentals
{
    /**
     * @param  list<Unit>  $units
     */
    protected function checkout(User $member, array $units, string $recipientName = 'Penerima Uji'): Rental
    {
        $stored = app(CheckoutRental::class)->handle(
            new Actor($member->id, false),
            new CheckoutData(
                array_map(fn (Unit $unit): int => $unit->id, $units),
                $recipientName,
                '081234567890',
                'Jl. Telekomunikasi No. 1, Bojongsoang',
                'Bojongsoang',
            ),
        );

        return Rental::query()->findOrFail($stored->id);
    }

    protected function cancelRental(User $admin, Rental $rental): void
    {
        app(CancelRental::class)->handle(new Actor($admin->id, true), $rental->id, 'Dibatalkan untuk pengujian.');
    }

    /**
     * Menjalankan pesanan sampai dikembalikan dengan jam yang dimajukan per langkah.
     */
    protected function completeRental(User $admin, Rental $rental): void
    {
        $actor = new Actor($admin->id, true);

        $this->travel(1)->minutes();
        app(ConfirmPayment::class)->handle($actor, $rental->id, PaymentMethod::Cash);
        $this->travel(1)->minutes();
        app(DispatchRental::class)->handle($actor, $rental->id, now()->toDateTimeImmutable());
        $this->travel(1)->minutes();
        app(RecordReceipt::class)->handle($actor, $rental->id, now()->toDateTimeImmutable());
        $this->travel(1)->hours();
        app(RecordReturn::class)->handle($actor, $rental->id, now()->toDateTimeImmutable(), 'Lengkap.');
    }
}
