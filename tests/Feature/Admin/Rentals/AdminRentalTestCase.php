<?php

namespace Tests\Feature\Admin\Rentals;

use App\Application\Rental\CheckoutRental;
use App\Application\Rental\ConfirmPayment;
use App\Application\Rental\Data\Actor;
use App\Application\Rental\Data\CheckoutData;
use App\Application\Rental\DispatchRental;
use App\Application\Rental\RecordReceipt;
use App\Domain\Rental\PaymentMethod;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Pesanan uji dibuat lewat use case asli dengan jam yang dikendalikan Carbon::setTestNow,
 * yang juga dibaca SystemClock.
 */
abstract class AdminRentalTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected CarbonImmutable $start;

    protected function setUp(): void
    {
        parent::setUp();

        $this->start = CarbonImmutable::parse('2026-10-01 03:00:00', 'UTC');
        $this->travelToTime($this->start);
        $this->admin = User::factory()->admin()->create(['name' => 'Admin Uji']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    protected function travelToTime(CarbonImmutable $time): void
    {
        Carbon::setTestNow($time);
    }

    protected function member(string $name = 'Anggota Uji'): User
    {
        return User::factory()->create(['name' => $name]);
    }

    /**
     * @param  list<Unit>  $units
     */
    protected function checkout(User $member, array $units): Rental
    {
        $stored = app(CheckoutRental::class)->handle(
            new Actor($member->id, false),
            new CheckoutData(
                array_map(fn (Unit $unit): int => $unit->id, $units),
                $member->name,
                '081234567890',
                'Jl. Telekomunikasi No. 1, Bojongsoang',
                'Bojongsoang',
            ),
        );

        return Rental::query()->findOrFail($stored->id);
    }

    protected function adminActor(): Actor
    {
        return new Actor($this->admin->id, true);
    }

    protected function confirm(Rental $rental): void
    {
        app(ConfirmPayment::class)->handle($this->adminActor(), $rental->id, PaymentMethod::Transfer);
    }

    /**
     * Membawa pesanan ke status aktif: checkout di start, lunas +1 jam, berangkat +2 jam,
     * diterima +3 jam. Jam akhir berada di +3 jam.
     *
     * @param  list<Unit>  $units
     */
    protected function activeRental(User $member, array $units): Rental
    {
        $this->travelToTime($this->start);
        $rental = $this->checkout($member, $units);

        $this->travelToTime($this->start->addHour());
        $this->confirm($rental);

        $this->travelToTime($this->start->addHours(2));
        app(DispatchRental::class)->handle($this->adminActor(), $rental->id, $this->start->addHours(2)->toDateTimeImmutable());

        $this->travelToTime($this->start->addHours(3));
        app(RecordReceipt::class)->handle($this->adminActor(), $rental->id, $this->start->addHours(3)->toDateTimeImmutable());

        return $rental->refresh();
    }

    protected function wibInput(CarbonImmutable $time): string
    {
        return Format::wibInput($time);
    }
}
