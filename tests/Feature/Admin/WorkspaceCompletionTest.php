<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\Master\CreatesRentals;
use Tests\TestCase;

class WorkspaceCompletionTest extends TestCase
{
    use CreatesRentals, RefreshDatabase;

    public function test_held_unit_links_to_the_order_that_currently_holds_it(): void
    {
        $unit = Unit::factory()->create();
        $rental = $this->checkout(User::factory()->create(), [$unit]);

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.units.index'))
            ->assertOk()->assertSee(route('admin.rentals.show', $rental));
    }

    public function test_unit_list_opens_the_selected_unit_in_an_edit_panel(): void
    {
        $admin = User::factory()->admin()->create();
        $unit = Unit::factory()->create(['code' => 'PC-EDIT-001']);
        $unit->categories()->attach(Category::factory()->create());

        $this->actingAs($admin)->get(route('admin.units.index', ['ubah' => $unit->code]))
            ->assertOk()
            ->assertViewHas('editing', fn (Unit $editing): bool => $editing->is($unit))
            ->assertSee('Simpan perubahan')
            ->assertSee(route('admin.units.update', $unit));
    }

    public function test_inline_unit_update_returns_to_the_list_and_selected_unit(): void
    {
        $admin = User::factory()->admin()->create();
        $unit = Unit::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)->put(route('admin.units.update', $unit), [
            'name' => 'Paket Diperbarui',
            'kind' => $unit->kind->value,
            'specs_text' => $unit->specs_text,
            'contents_text' => $unit->contents_text,
            'package_price' => 600000,
            'daily_fine' => 100000,
            'condition_status' => 'ready',
            'categories' => [$category->id],
            'return_to_list' => '1',
        ])->assertRedirect(route('admin.units.index', ['ubah' => $unit->code]))
            ->assertSessionHasNoErrors();

        $this->assertSame('Paket Diperbarui', $unit->fresh()->name);
    }

    public function test_shipping_preview_uses_current_catalog_prices(): void
    {
        Unit::factory()->create(['code' => 'PC-MID-001', 'package_price' => 510000]);
        Unit::factory()->peripheral()->create(['code' => 'PER-A2-001', 'package_price' => 130000]);

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertViewHas('previewSubtotal', 640000)
            ->assertSee('Rp715.000');
    }

    public function test_shipping_changes_record_the_old_new_fee_and_admin(): void
    {
        $admin = User::factory()->admin()->create();
        Setting::current();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'flat_shipping_fee' => 90000,
            'service_area_text' => 'Bandung dan Bojongsoang.',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('shipping_fee_changes', [
            'previous_fee' => 75000,
            'new_fee' => 90000,
            'changed_by' => $admin->id,
        ]);

        $this->get(route('admin.settings.edit'))->assertOk()
            ->assertSee('Riwayat perubahan')->assertSee($admin->name)->assertSee('Rp90.000');
    }

    public function test_saving_the_same_fee_does_not_invent_another_price_change(): void
    {
        Setting::current();
        $this->actingAs(User::factory()->admin()->create())->put(route('admin.settings.update'), [
            'flat_shipping_fee' => 75000,
            'service_area_text' => 'Bandung dan sekitarnya.',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('shipping_fee_changes', 0);
        $this->assertSame('Bandung dan sekitarnya.', Setting::current()->service_area_text);
    }
}
