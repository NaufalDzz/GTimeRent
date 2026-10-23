<?php

namespace Tests\Feature\Admin\Master;

use App\Application\Rental\CheckoutRental;
use App\Application\Rental\Data\Actor;
use App\Application\Rental\Data\CheckoutData;
use App\Domain\Catalog\UnitCondition;
use App\Domain\Catalog\UnitKind;
use App\Domain\Rental\RentalRuleViolation;
use App\Models\Category;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitManagementTest extends TestCase
{
    use CreatesRentals, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'code' => 'PC-MID-009',
            'name' => 'PC Mid + IPS',
            'kind' => UnitKind::PcMonitor->value,
            'specs_text' => 'Ryzen 5 7600; RTX 5070 12GB',
            'contents_text' => "/PC - satu PC\n/MON - satu monitor",
            'package_price' => 500000,
            'daily_fine' => 100000,
            'condition_status' => UnitCondition::Ready->value,
            'categories' => [Category::factory()->create()->id],
        ], $overrides);
    }

    public function test_index_lists_active_and_archived_units_with_status(): void
    {
        $held = Unit::factory()->create(['code' => 'PC-HELD-001']);
        $this->checkout(User::factory()->create(), [$held]);
        Unit::factory()->create(['code' => 'PC-ARSIP-001'])->delete();
        Unit::factory()->maintenance()->create(['code' => 'PC-SERVIS-001']);

        $this->actingAs($this->admin)
            ->get(route('admin.units.index'))
            ->assertOk()
            ->assertSee('Tambah unit')
            ->assertSee('PC-HELD-001')
            ->assertSee('Sedang ditahan')
            ->assertSee('PC-ARSIP-001')
            ->assertSee('Diarsipkan')
            ->assertSee('Pulihkan')
            ->assertSee('Maintenance');
    }

    public function test_index_searches_by_name_or_code_and_filters_archived(): void
    {
        Unit::factory()->create(['code' => 'PER-H2-001', 'name' => 'H2 Headset Mid']);
        Unit::factory()->create(['code' => 'PC-HIGH-001', 'name' => 'PC High + OLED'])->delete();

        $this->actingAs($this->admin)
            ->get(route('admin.units.index', ['q' => 'Headset']))
            ->assertOk()
            ->assertSee('PER-H2-001')
            ->assertDontSee('PC-HIGH-001');

        $this->actingAs($this->admin)
            ->get(route('admin.units.index', ['status' => 'arsip']))
            ->assertOk()
            ->assertSee('PC-HIGH-001')
            ->assertDontSee('PER-H2-001');
    }

    public function test_create_and_edit_pages_render(): void
    {
        $unit = Unit::factory()->create();
        Category::factory()->create(['name' => 'Kategori Aktif']);

        $this->actingAs($this->admin)
            ->get(route('admin.units.create'))
            ->assertOk()
            ->assertSee('Tambah paket fisik')
            ->assertSee('Kategori Aktif');

        $this->actingAs($this->admin)
            ->get(route('admin.units.edit', $unit))
            ->assertOk()
            ->assertSee($unit->code)
            ->assertSee('Simpan perubahan')
            ->assertSee('Arsipkan');
    }

    public function test_t03_same_name_with_different_codes_is_allowed(): void
    {
        Unit::factory()->create(['code' => 'PC-MID-001', 'name' => 'PC Mid + IPS']);

        $this->actingAs($this->admin)
            ->post(route('admin.units.store'), $this->payload(['code' => 'PC-MID-002', 'name' => 'PC Mid + IPS']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.units.edit', 'PC-MID-002'));

        $this->assertSame(2, Unit::query()->where('name', 'PC Mid + IPS')->count());
    }

    public function test_t03_duplicate_code_is_rejected_including_archived_units(): void
    {
        Unit::factory()->create(['code' => 'PC-MID-001']);
        Unit::factory()->create(['code' => 'PC-OLD-001'])->delete();

        $this->actingAs($this->admin)
            ->post(route('admin.units.store'), $this->payload(['code' => 'PC-MID-001']))
            ->assertSessionHasErrors(['code' => 'Kode sudah dipakai, termasuk unit yang diarsipkan.']);

        $this->actingAs($this->admin)
            ->post(route('admin.units.store'), $this->payload(['code' => 'PC-OLD-001']))
            ->assertSessionHasErrors('code');

        $this->assertSame(1, Unit::withTrashed()->where('code', 'PC-OLD-001')->count());
    }

    public function test_code_format_and_required_fields_are_validated(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.units.store'), $this->payload([
                'code' => 'PC MID/01',
                'name' => 'A',
                'kind' => 'laptop',
                'specs_text' => '',
                'contents_text' => '',
                'condition_status' => 'rusak',
            ]))
            ->assertSessionHasErrors(['code', 'name', 'kind', 'specs_text', 'contents_text', 'condition_status']);

        $this->assertSame(0, Unit::query()->count());
    }

    public function test_t04_unit_with_multiple_categories_is_found_by_catalog_name_search(): void
    {
        $gaming = Category::factory()->create(['name' => 'Gaming']);
        $oled = Category::factory()->create(['name' => 'OLED']);

        $this->actingAs($this->admin)
            ->post(route('admin.units.store'), $this->payload([
                'code' => 'PC-HIGH-009',
                'name' => 'PC High + OLED',
                'categories' => [$gaming->id, $oled->id],
            ]))
            ->assertSessionHasNoErrors();

        $unit = Unit::query()->where('code', 'PC-HIGH-009')->firstOrFail();
        $this->assertEqualsCanonicalizing([$gaming->id, $oled->id], $unit->categories->modelKeys());

        $this->actingAs(User::factory()->create())
            ->get(route('catalog.index', ['q' => 'High + OLED']))
            ->assertOk()
            ->assertSee('PC-HIGH-009')
            ->assertSee('Gaming')
            ->assertSee('OLED');
    }

    public function test_category_is_required_and_archived_category_is_not_assignable(): void
    {
        $archived = Category::factory()->create();
        $archived->delete();

        $this->actingAs($this->admin)
            ->post(route('admin.units.store'), $this->payload(['categories' => []]))
            ->assertSessionHasErrors(['categories' => 'Pilih minimal satu kategori aktif.']);

        $this->actingAs($this->admin)
            ->post(route('admin.units.store'), $this->payload(['categories' => [$archived->id]]))
            ->assertSessionHasErrors('categories.0');

        $this->assertSame(0, Unit::query()->count());
    }

    public function test_update_keeps_links_to_archived_categories(): void
    {
        $active = Category::factory()->create();
        $archived = Category::factory()->create();
        $unit = Unit::factory()->create();
        $unit->categories()->sync([$active->id, $archived->id]);
        $archived->delete();
        $replacement = Category::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.units.edit', $unit))
            ->assertOk()
            ->assertSee($archived->name);

        $this->actingAs($this->admin)
            ->put(route('admin.units.update', $unit), $this->payload(['categories' => [$replacement->id], 'name' => 'Nama Baru']))
            ->assertSessionHasNoErrors();

        $unit->refresh();
        $this->assertSame('Nama Baru', $unit->name);
        $this->assertEqualsCanonicalizing([$archived->id, $replacement->id], $unit->categories->modelKeys());
    }

    public function test_code_cannot_be_changed_on_update(): void
    {
        $unit = Unit::factory()->create(['code' => 'PC-TETAP-001']);

        $this->actingAs($this->admin)
            ->put(route('admin.units.update', $unit), $this->payload(['code' => 'PC-BARU-001']))
            ->assertSessionHasNoErrors();

        $this->assertSame('PC-TETAP-001', $unit->fresh()->code);
    }

    public function test_t25_negative_or_decimal_money_is_rejected(): void
    {
        foreach (['-1', '1500.5', '1.0', 'abc', '1000000001'] as $invalid) {
            $this->actingAs($this->admin)
                ->post(route('admin.units.store'), $this->payload(['package_price' => $invalid, 'daily_fine' => $invalid]))
                ->assertSessionHasErrors(['package_price', 'daily_fine']);
        }

        $this->assertSame(0, Unit::query()->count());

        $this->actingAs($this->admin)
            ->post(route('admin.units.store'), $this->payload(['package_price' => 0, 'daily_fine' => 1000000000]))
            ->assertSessionHasNoErrors();
    }

    public function test_held_unit_rejects_kind_contents_and_maintenance_changes_but_allows_price(): void
    {
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();
        $unit->categories()->sync([$category->id]);
        $this->checkout(User::factory()->create(), [$unit]);

        $base = [
            'name' => $unit->name,
            'kind' => $unit->kind->value,
            'specs_text' => $unit->specs_text,
            'contents_text' => str_replace("\n", "\r\n", $unit->contents_text),
            'package_price' => 650000,
            'daily_fine' => 120000,
            'condition_status' => UnitCondition::Ready->value,
            'categories' => [$category->id],
        ];

        $this->actingAs($this->admin)
            ->get(route('admin.units.edit', $unit))
            ->assertOk()
            ->assertSee('Jenis dan kelengkapan dikunci selama unit ditahan pesanan.');

        $this->actingAs($this->admin)
            ->put(route('admin.units.update', $unit), array_merge($base, ['kind' => UnitKind::Peripheral->value]))
            ->assertSessionHasErrors('kind');

        $this->actingAs($this->admin)
            ->put(route('admin.units.update', $unit), array_merge($base, ['contents_text' => '/PC - satu PC saja']))
            ->assertSessionHasErrors('contents_text');

        $this->actingAs($this->admin)
            ->put(route('admin.units.update', $unit), array_merge($base, ['condition_status' => UnitCondition::Maintenance->value]))
            ->assertSessionHasErrors('condition_status');

        $this->assertSame(UnitCondition::Ready, $unit->fresh()->condition_status);

        $this->actingAs($this->admin)
            ->put(route('admin.units.update', $unit), $base)
            ->assertSessionHasNoErrors();

        $this->assertSame(650000, $unit->fresh()->package_price);
    }

    public function test_t23_held_unit_cannot_be_archived_until_rental_is_cancelled(): void
    {
        $unit = Unit::factory()->create();
        $rental = $this->checkout(User::factory()->create(), [$unit]);

        $this->actingAs($this->admin)
            ->from(route('admin.units.edit', $unit))
            ->delete(route('admin.units.destroy', $unit))
            ->assertRedirect(route('admin.units.edit', $unit))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($unit);

        $this->cancelRental($this->admin, $rental);

        $this->actingAs($this->admin)
            ->delete(route('admin.units.destroy', $unit))
            ->assertRedirect(route('admin.units.index'))
            ->assertSessionHas('status');

        $this->assertSoftDeleted($unit);
        $item = $rental->items()->firstOrFail();
        $this->assertSame($unit->code, $item->code_snapshot);
        $this->assertTrue($item->unit->trashed());

        $this->flushSession();
        $this->actingAs(User::factory()->create())
            ->get(route('catalog.index'))
            ->assertDontSee($unit->code);
    }

    public function test_t23_unit_can_be_archived_after_rental_is_returned_and_restored(): void
    {
        $unit = Unit::factory()->create();
        $rental = $this->checkout(User::factory()->create(), [$unit]);
        $this->completeRental($this->admin, $rental);

        $this->actingAs($this->admin)
            ->delete(route('admin.units.destroy', $unit))
            ->assertSessionHas('status');

        $this->assertSoftDeleted($unit);
        $this->assertSame($unit->name, $rental->items()->firstOrFail()->name_snapshot);

        $this->actingAs($this->admin)
            ->get(route('admin.units.edit', $unit))
            ->assertOk()
            ->assertSee('Pulihkan unit');

        $this->actingAs($this->admin)
            ->post(route('admin.units.restore', $unit))
            ->assertSessionHas('status');

        $this->assertNotSoftDeleted($unit);
    }

    public function test_t24_maintenance_or_archived_unit_cannot_be_checked_out_even_with_known_id(): void
    {
        $member = User::factory()->create();
        $maintenance = Unit::factory()->maintenance()->create();
        $archived = Unit::factory()->create();
        $archived->delete();

        foreach ([$maintenance, $archived] as $unit) {
            try {
                app(CheckoutRental::class)->handle(
                    new Actor($member->id, false),
                    new CheckoutData([$unit->id], 'Penerima Uji', '081234567890', 'Jl. Telekomunikasi No. 1', 'Bandung'),
                );
                $this->fail("Checkout {$unit->code} seharusnya ditolak.");
            } catch (RentalRuleViolation $violation) {
                $this->assertStringContainsString($unit->code, $violation->getMessage());
            }
        }

        $this->assertDatabaseCount('rentals', 0);
    }
}
