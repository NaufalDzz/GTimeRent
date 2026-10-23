<?php

namespace Tests\Feature\Admin\Rentals;

use App\Domain\Rental\PaymentMethod;
use App\Domain\Rental\RentalStatus;
use App\Models\Unit;

class RentalStatusActionsTest extends AdminRentalTestCase
{
    public function test_cancel_requires_reason_and_keeps_rental_pending(): void
    {
        $unit = Unit::factory()->create();
        $rental = $this->checkout($this->member(), [$unit]);

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.show', $rental))
            ->post(route('admin.rentals.cancel', $rental), ['reason' => 'ok'])
            ->assertRedirect(route('admin.rentals.show', $rental))
            ->assertSessionHasErrors('reason');

        $this->actingAs($this->admin)
            ->post(route('admin.rentals.cancel', $rental), ['reason' => ''])
            ->assertSessionHasErrors('reason');

        $this->assertSame(RentalStatus::PendingPayment, $rental->refresh()->status);
        $this->assertFalse(Unit::query()->available()->whereKey($unit->id)->exists());
    }

    public function test_cancel_pending_rental_releases_units(): void
    {
        $unit = Unit::factory()->create();
        $rental = $this->checkout($this->member(), [$unit]);

        $this->actingAs($this->admin)
            ->post(route('admin.rentals.cancel', $rental), ['reason' => 'Alamat tidak dapat diverifikasi.'])
            ->assertRedirect(route('admin.rentals.show', $rental))
            ->assertSessionHas('status');

        $rental->refresh();
        $this->assertSame(RentalStatus::Cancelled, $rental->status);
        $this->assertSame('Alamat tidak dapat diverifikasi.', $rental->cancel_reason);
        $this->assertSame($this->admin->id, $rental->cancelled_by);
        $this->assertTrue(Unit::query()->available()->whereKey($unit->id)->exists());
    }

    public function test_cancel_after_paid_is_rejected(): void
    {
        $rental = $this->checkout($this->member(), [Unit::factory()->create()]);
        $this->confirm($rental);

        $this->actingAs($this->admin)
            ->from(route('admin.rentals.show', $rental))
            ->post(route('admin.rentals.cancel', $rental), ['reason' => 'Berubah pikiran.'])
            ->assertRedirect(route('admin.rentals.show', $rental))
            ->assertSessionHas('error');

        $this->assertSame(RentalStatus::Paid, $rental->refresh()->status);
        $this->assertNull($rental->cancelled_at);
    }

    public function test_confirm_twice_is_rejected_without_changing_confirmation_time(): void
    {
        $rental = $this->checkout($this->member(), [Unit::factory()->create()]);

        $this->travelToTime($this->start->addHour());
        $this->actingAs($this->admin)
            ->post(route('admin.rentals.confirm', $rental), ['payment_method' => PaymentMethod::Cash->value])
            ->assertRedirect(route('admin.rentals.show', $rental))
            ->assertSessionHas('status');

        $confirmedAt = $rental->refresh()->payment_confirmed_at;
        $this->assertTrue($confirmedAt->equalTo($this->start->addHour()));

        $this->travelToTime($this->start->addHours(2));
        $this->actingAs($this->admin)
            ->from(route('admin.rentals.show', $rental))
            ->post(route('admin.rentals.confirm', $rental), ['payment_method' => PaymentMethod::Transfer->value])
            ->assertSessionHas('error');

        $rental->refresh();
        $this->assertSame(RentalStatus::Paid, $rental->status);
        $this->assertSame(PaymentMethod::Cash, $rental->payment_method);
        $this->assertTrue($rental->payment_confirmed_at->equalTo($confirmedAt));
    }

    public function test_confirm_requires_valid_payment_method(): void
    {
        $rental = $this->checkout($this->member(), [Unit::factory()->create()]);

        $this->actingAs($this->admin)
            ->post(route('admin.rentals.confirm', $rental), ['payment_method' => 'qris'])
            ->assertSessionHasErrors('payment_method');

        $this->assertSame(RentalStatus::PendingPayment, $rental->refresh()->status);
    }

    public function test_dispatch_before_paid_is_rejected(): void
    {
        $rental = $this->checkout($this->member(), [Unit::factory()->create()]);

        $this->travelToTime($this->start->addHour());
        $this->actingAs($this->admin)
            ->from(route('admin.rentals.show', $rental))
            ->post(route('admin.rentals.dispatch', $rental), ['dispatched_at' => $this->wibInput($this->start->addMinutes(30))])
            ->assertRedirect(route('admin.rentals.show', $rental))
            ->assertSessionHas('error');

        $this->assertSame(RentalStatus::PendingPayment, $rental->refresh()->status);
        $this->assertNull($rental->dispatched_at);
    }

    public function test_dispatch_and_receive_set_due_at_from_received_at(): void
    {
        $rental = $this->checkout($this->member(), [Unit::factory()->create()]);
        $this->travelToTime($this->start->addHour());
        $this->confirm($rental);

        $this->travelToTime($this->start->addHours(5));
        $this->actingAs($this->admin)
            ->post(route('admin.rentals.dispatch', $rental), ['dispatched_at' => $this->wibInput($this->start->addHours(2))])
            ->assertSessionHas('status');
        $this->assertSame(RentalStatus::InDelivery, $rental->refresh()->status);
        $this->assertTrue($rental->dispatched_at->equalTo($this->start->addHours(2)));

        $receivedAt = $this->start->addHours(4)->addMinutes(15);
        $this->travelToTime($this->start->addHours(6));
        $this->actingAs($this->admin)
            ->post(route('admin.rentals.receive', $rental), ['received_at' => $this->wibInput($receivedAt)])
            ->assertSessionHas('status');

        $rental->refresh();
        $this->assertSame(RentalStatus::Active, $rental->status);
        $this->assertTrue($rental->received_at->equalTo($receivedAt));
        $this->assertTrue($rental->receipt_recorded_at->equalTo($this->start->addHours(6)));
        $this->assertTrue($rental->due_at->equalTo($receivedAt->addHours(120)));
        $this->assertFalse($rental->due_at->equalTo($rental->created_at->addHours(120)));
    }

    public function test_dispatch_time_in_future_is_rejected(): void
    {
        $rental = $this->checkout($this->member(), [Unit::factory()->create()]);
        $this->confirm($rental);

        $this->actingAs($this->admin)
            ->post(route('admin.rentals.dispatch', $rental), ['dispatched_at' => $this->wibInput($this->start->addHour())])
            ->assertSessionHas('error');

        $this->actingAs($this->admin)
            ->post(route('admin.rentals.dispatch', $rental), ['dispatched_at' => 'kemarin'])
            ->assertSessionHasErrors('dispatched_at');

        $this->assertSame(RentalStatus::Paid, $rental->refresh()->status);
    }

    public function test_member_cannot_post_admin_status_actions(): void
    {
        $member = $this->member();
        $pending = $this->checkout($member, [Unit::factory()->create()]);
        $active = $this->activeRental($this->member('Anggota Lain'), [Unit::factory()->create()]);
        $this->travelToTime($this->start->addDays(2));

        $this->actingAs($member)
            ->post(route('admin.rentals.confirm', $pending), ['payment_method' => PaymentMethod::Cash->value])
            ->assertForbidden();
        $this->actingAs($member)
            ->post(route('admin.rentals.cancel', $pending), ['reason' => 'Batal saja.'])
            ->assertForbidden();
        $this->actingAs($member)
            ->post(route('admin.rentals.return', $active), ['returned_at' => $this->wibInput($this->start->addDay())])
            ->assertForbidden();

        $this->assertSame(RentalStatus::PendingPayment, $pending->refresh()->status);
        $this->assertSame(RentalStatus::Active, $active->refresh()->status);
    }

    public function test_detail_shows_only_actions_valid_for_status(): void
    {
        $pending = $this->checkout($this->member('Anggota Satu'), [Unit::factory()->create()]);

        $this->actingAs($this->admin)
            ->get(route('admin.rentals.show', $pending))
            ->assertOk()
            ->assertSee($pending->number)
            ->assertSee(route('admin.rentals.confirm', $pending))
            ->assertSee(route('admin.rentals.cancel', $pending))
            ->assertDontSee(route('admin.rentals.dispatch', $pending))
            ->assertDontSee(route('admin.rentals.return', $pending));

        $this->confirm($pending);
        $this->actingAs($this->admin)
            ->get(route('admin.rentals.show', $pending))
            ->assertOk()
            ->assertSee(route('admin.rentals.dispatch', $pending))
            ->assertDontSee(route('admin.rentals.confirm', $pending))
            ->assertDontSee(route('admin.rentals.cancel', $pending));

        $active = $this->activeRental($this->member('Anggota Dua'), [Unit::factory()->create()]);
        $this->actingAs($this->admin)
            ->get(route('admin.rentals.show', $active))
            ->assertOk()
            ->assertSee(route('admin.rentals.return', $active))
            ->assertSee('Admin Uji')
            ->assertSee('Catat diterima GTR')
            ->assertDontSee(route('admin.rentals.receive', $active));
    }
}
