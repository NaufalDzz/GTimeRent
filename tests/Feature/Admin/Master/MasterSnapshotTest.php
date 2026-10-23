<?php

namespace Tests\Feature\Admin\Master;

use App\Models\Category;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterSnapshotTest extends TestCase
{
    use CreatesRentals, RefreshDatabase;

    public function test_t17_master_changes_after_checkout_do_not_change_rental_snapshot(): void
    {
        Setting::query()->forceCreate(['id' => 1, 'flat_shipping_fee' => 75000, 'service_area_text' => Setting::DEFAULT_AREA_TEXT]);
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['name' => 'Anggota Lama']);
        $member->profile()->create(['phone' => '081111111111', 'address' => 'Alamat lama nomor 1', 'service_area' => 'Bandung']);
        $category = Category::factory()->create();
        $unit = Unit::factory()->create(['code' => 'PC-SNAP-001', 'name' => 'Nama Lama', 'package_price' => 500000, 'daily_fine' => 100000]);
        $unit->categories()->attach($category);

        $rental = $this->checkout($member, [$unit], 'Penerima Lama');
        $item = $rental->items()->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.units.update', $unit), [
                'name' => 'Nama Baru',
                'kind' => $unit->kind->value,
                'specs_text' => $unit->specs_text,
                'contents_text' => $unit->contents_text,
                'package_price' => 900000,
                'daily_fine' => 150000,
                'condition_status' => 'ready',
                'categories' => [$category->id],
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), ['flat_shipping_fee' => 120000, 'service_area_text' => 'Area baru.'])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->put(route('admin.members.update', $member), [
                'name' => 'Anggota Baru',
                'email' => $member->email,
                'phone' => '082222222222',
                'address' => 'Alamat baru nomor 2',
                'service_area' => 'Dayeuhkolot',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Nama Baru', $unit->fresh()->name);
        $this->assertSame(900000, $unit->fresh()->package_price);
        $this->assertSame(120000, Setting::current()->flat_shipping_fee);
        $this->assertSame('Dayeuhkolot', $member->fresh()->profile->service_area);

        $rental->refresh();
        $item->refresh();
        $this->assertSame('PC-SNAP-001', $item->code_snapshot);
        $this->assertSame('Nama Lama', $item->name_snapshot);
        $this->assertSame(500000, $item->package_price_snapshot);
        $this->assertSame(100000, $item->daily_fine_snapshot);
        $this->assertSame(75000, $rental->shipping_fee_snapshot);
        $this->assertSame(575000, $rental->initial_total);
        $this->assertSame('Penerima Lama', $rental->recipient_name);
        $this->assertSame('081234567890', $rental->recipient_phone);
        $this->assertSame('Jl. Telekomunikasi No. 1, Bojongsoang', $rental->delivery_address);
        $this->assertSame('Bojongsoang', $rental->service_area);
    }
}
