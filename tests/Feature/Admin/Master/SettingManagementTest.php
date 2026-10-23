<?php

namespace Tests\Feature\Admin\Master;

use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingManagementTest extends TestCase
{
    use CreatesRentals, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->seedSettingRow();
    }

    /**
     * Setting::current() mencari id = 1, sedangkan id tidak fillable dan auto-increment MySQL
     * tidak ikut di-rollback RefreshDatabase; baris tunggal dibuat eksplisit seperti DemoCatalogSeeder.
     */
    private function seedSettingRow(): void
    {
        Setting::query()->forceCreate([
            'id' => 1,
            'flat_shipping_fee' => Setting::DEFAULT_SHIPPING_FEE,
            'service_area_text' => Setting::DEFAULT_AREA_TEXT,
        ]);
    }

    public function test_edit_page_shows_current_fee(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Ongkir antar-jemput')
            ->assertSee('Rp75.000')
            ->assertSee('Simpan tarif');
    }

    public function test_admin_updates_flat_fee_and_area_text(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), ['flat_shipping_fee' => 90000, 'service_area_text' => 'Bandung saja.'])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasNoErrors();

        $setting = Setting::current();
        $this->assertSame(90000, $setting->flat_shipping_fee);
        $this->assertSame('Bandung saja.', $setting->service_area_text);
    }

    public function test_new_fee_applies_to_later_checkout_only(): void
    {
        $member = User::factory()->create();
        $before = $this->checkout($member, [Unit::factory()->create()]);

        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), ['flat_shipping_fee' => 50000, 'service_area_text' => 'Bandung'])
            ->assertSessionHasNoErrors();

        $after = $this->checkout($member, [Unit::factory()->create()]);

        $this->assertSame(75000, $before->fresh()->shipping_fee_snapshot);
        $this->assertSame(50000, $after->shipping_fee_snapshot);
    }

    public function test_t25_invalid_fee_is_rejected_and_server_value_is_unchanged(): void
    {
        foreach (['-5000', '75000.5', '', 'gratis', '1000000001'] as $invalid) {
            $this->actingAs($this->admin)
                ->put(route('admin.settings.update'), ['flat_shipping_fee' => $invalid, 'service_area_text' => 'Bandung'])
                ->assertSessionHasErrors('flat_shipping_fee');
        }

        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), ['flat_shipping_fee' => 0, 'service_area_text' => ''])
            ->assertSessionHasErrors('service_area_text');

        $this->assertSame(Setting::DEFAULT_SHIPPING_FEE, Setting::current()->flat_shipping_fee);
    }
}
