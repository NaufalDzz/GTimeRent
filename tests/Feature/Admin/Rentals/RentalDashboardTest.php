<?php

namespace Tests\Feature\Admin\Rentals;

use App\Models\Unit;

class RentalDashboardTest extends AdminRentalTestCase
{
    public function test_admin_dashboard_lists_all_rentals_with_overdue_label(): void
    {
        $overdue = $this->activeRental($this->member('Anggota Telat'), [Unit::factory()->create()]);
        $pending = $this->checkout($this->member('Anggota Baru'), [Unit::factory()->create()]);
        $this->travelToTime($this->start->addDays(6));

        $this->actingAs($this->admin)
            ->get(route('admin.rentals.index'))
            ->assertOk()
            ->assertSee('Transaksi')
            ->assertSee($overdue->number)
            ->assertSee($pending->number)
            ->assertSee('Aktif · Terlambat')
            ->assertSee('Menunggu bayar')
            ->assertSee(route('admin.rentals.show', $overdue));
    }

    public function test_admin_filters_by_member_status_and_created_date(): void
    {
        $first = $this->member('Anggota Pertama');
        $second = $this->member('Anggota Kedua');
        $early = $this->checkout($first, [Unit::factory()->create()]);
        $overdue = $this->activeRental($second, [Unit::factory()->create()]);
        $this->travelToTime($this->start->addDays(10));
        $late = $this->checkout($first, [Unit::factory()->create()]);

        $this->actingAs($this->admin)
            ->get(route('admin.rentals.index', ['member' => $first->id]))
            ->assertOk()
            ->assertSee($early->number)
            ->assertSee($late->number)
            ->assertDontSee($overdue->number);

        $this->actingAs($this->admin)
            ->get(route('admin.rentals.index', ['status' => 'overdue']))
            ->assertOk()
            ->assertSee($overdue->number)
            ->assertDontSee($early->number)
            ->assertDontSee($late->number);

        $day = $this->start->setTimezone('Asia/Jakarta')->format('Y-m-d');
        $this->actingAs($this->admin)
            ->get(route('admin.rentals.index', ['from' => $day, 'to' => $day]))
            ->assertOk()
            ->assertSee($early->number)
            ->assertSee($overdue->number)
            ->assertDontSee($late->number);

        $this->actingAs($this->admin)
            ->get(route('admin.rentals.index', ['status' => 'bukan-status']))
            ->assertRedirect(route('admin.rentals.index'))
            ->assertSessionHasErrors('status');
    }

    public function test_print_page_shows_filtered_rentals_with_separate_fines(): void
    {
        $first = $this->member('Anggota Cetak');
        $other = $this->member('Anggota Lain');
        $printed = $this->checkout($first, [Unit::factory()->create(['code' => 'PC-PRN-001', 'name' => 'PC Cetak Uji'])]);
        $hidden = $this->checkout($other, [Unit::factory()->create()]);

        $this->actingAs($this->admin)
            ->get(route('admin.rentals.print', ['member' => $first->id]))
            ->assertOk()
            ->assertSee('Laporan Riwayat Transaksi')
            ->assertSee('Dicetak')
            ->assertSee('Anggota Cetak')
            ->assertSee($first->email)
            ->assertSee($printed->number)
            ->assertSee('PC-PRN-001')
            ->assertSee('PC Cetak Uji')
            ->assertSee('Rp500.000')
            ->assertSee('Rp75.000')
            ->assertSee('Denda')
            ->assertSee('window.print()', false)
            ->assertDontSee($hidden->number);
    }

    public function test_index_links_to_print_with_same_filters(): void
    {
        $member = $this->member();
        $this->checkout($member, [Unit::factory()->create()]);

        $this->actingAs($this->admin)
            ->get(route('admin.rentals.index', ['member' => $member->id, 'status' => 'pending_payment']))
            ->assertOk()
            ->assertSee(route('admin.rentals.print', ['member' => $member->id, 'status' => 'pending_payment']));
    }

    public function test_member_cannot_access_admin_pages(): void
    {
        $member = $this->member();
        $rental = $this->checkout($member, [Unit::factory()->create()]);

        $this->actingAs($member)->get(route('admin.rentals.index'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.rentals.print'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.rentals.show', $rental))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.rentals.index'))->assertRedirect(route('login'));
    }
}
