<?php

namespace Tests\Feature\Member;

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
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  list<int>  $unitIds
     */
    private function checkout(User $member, array $unitIds): Rental
    {
        $stored = app(CheckoutRental::class)->handle(
            new Actor($member->id, false),
            new CheckoutData($unitIds, $member->name, '081234567890', 'Jl. Telekomunikasi No. 1, Bojongsoang', 'Bojongsoang'),
        );

        return Rental::query()->findOrFail($stored->id);
    }

    /**
     * Pesanan satu unit (Rp500.000, denda Rp100.000/hari) yang dibayar pada $start
     * lalu dikirim dan diterima satu jam kemudian.
     */
    private function activeRental(User $member, Actor $admin, CarbonImmutable $start): Rental
    {
        $this->travelTo($start);
        $rental = $this->checkout($member, [Unit::factory()->create()->id]);
        app(ConfirmPayment::class)->handle($admin, $rental->id, PaymentMethod::Transfer);
        $this->travelBack();

        app(DispatchRental::class)->handle($admin, $rental->id, $start->addHour());
        app(RecordReceipt::class)->handle($admin, $rental->id, $start->addHour());

        return $rental;
    }

    public function test_index_lists_only_own_rentals(): void
    {
        $member = User::factory()->create();
        $other = User::factory()->create();
        $own = $this->checkout($member, [Unit::factory()->create()->id]);
        $foreign = $this->checkout($other, [Unit::factory()->create()->id]);

        $this->actingAs($member)->get(route('rentals.index'))
            ->assertOk()
            ->assertSee($own->number)
            ->assertDontSee($foreign->number)
            ->assertSee('Menunggu bayar')
            ->assertSee('1/2');
    }

    public function test_index_shows_empty_state(): void
    {
        $this->actingAs(User::factory()->create())->get(route('rentals.index'))
            ->assertOk()
            ->assertSee('Belum ada pesanan.');
    }

    public function test_member_cannot_view_another_members_rental(): void
    {
        $member = User::factory()->create();
        $foreign = $this->checkout(User::factory()->create(), [Unit::factory()->create()->id]);

        $this->actingAs($member)->get(route('rentals.show', $foreign))->assertForbidden();
    }

    public function test_admin_cannot_use_member_rental_pages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('rentals.index'))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('rentals.index'))->assertRedirect(route('login'));
    }

    public function test_pending_rental_shows_payment_instructions_and_snapshots(): void
    {
        $member = User::factory()->create();
        $unit = Unit::factory()->create();
        $rental = $this->checkout($member, [$unit->id]);
        $unit->update(['name' => 'Nama Baru Setelah Checkout', 'package_price' => 999000]);

        $this->actingAs($member)->get(route('rentals.show', $rental))
            ->assertOk()
            ->assertSee($rental->number)
            ->assertSee('Cara bayar')
            ->assertSee('Rp575.000')
            ->assertSee($unit->code)
            ->assertDontSee('Nama Baru Setelah Checkout')
            ->assertDontSee('Pesanan dibuat.');
    }

    public function test_overdue_active_rental_shows_late_label_and_estimated_fine(): void
    {
        $member = User::factory()->create();
        $admin = new Actor(User::factory()->admin()->create()->id, true);
        $rental = $this->activeRental($member, $admin, CarbonImmutable::now()->subDays(6));

        $this->actingAs($member)->get(route('rentals.show', $rental))
            ->assertOk()
            ->assertSee('Terlambat')
            ->assertSee('Estimasi denda saat ini')
            ->assertSee('Rp100.000')
            ->assertDontSee('Cara bayar');

        $this->get(route('rentals.index'))->assertOk()->assertSee('Terlambat')->assertSee('Estimasi denda Rp100.000');
    }

    public function test_returned_rental_shows_final_fine_separately(): void
    {
        $member = User::factory()->create();
        $admin = new Actor(User::factory()->admin()->create()->id, true);
        $start = CarbonImmutable::now()->subDays(8);
        $rental = $this->activeRental($member, $admin, $start);
        app(RecordReturn::class)->handle($admin, $rental->id, $start->addHours(1 + 120 + 30), 'Lengkap.');

        $this->actingAs($member)->get(route('rentals.show', $rental))
            ->assertOk()
            ->assertSee('Dikembalikan')
            ->assertSee('Denda final')
            ->assertSee('Rp200.000')
            ->assertSee('Rp575.000');
    }

    public function test_cancelled_rental_shows_reason(): void
    {
        $member = User::factory()->create();
        $admin = new Actor(User::factory()->admin()->create()->id, true);
        $rental = $this->checkout($member, [Unit::factory()->create()->id]);
        app(CancelRental::class)->handle($admin, $rental->id, 'Alamat di luar area layanan');

        $this->actingAs($member)->get(route('rentals.show', $rental))
            ->assertOk()
            ->assertSee('Dibatalkan')
            ->assertSee('Alamat di luar area layanan');
    }
}
