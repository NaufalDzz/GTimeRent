<?php

namespace Tests\Feature\Admin\Master;

use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_index_shows_active_and_archived_categories_with_inline_edit(): void
    {
        $active = Category::factory()->create(['name' => 'Gaming']);
        Category::factory()->create(['name' => 'Paket Ujian'])->delete();

        $this->actingAs($this->admin)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Tambah kategori')
            ->assertSee('Gaming')
            ->assertSee('Paket Ujian')
            ->assertSee('Diarsipkan')
            ->assertSee('Pulihkan');

        $this->actingAs($this->admin)
            ->get(route('admin.categories.index', ['ubah' => $active->id]))
            ->assertOk()
            ->assertSee('Nama kategori')
            ->assertSee(route('admin.categories.update', $active));
    }

    public function test_admin_creates_and_renames_category(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), ['name' => 'Streaming'])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasNoErrors();

        $category = Category::query()->where('name', 'Streaming')->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), ['name' => 'Streaming Pro'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Streaming Pro', $category->fresh()->name);
    }

    public function test_name_is_required_short_and_unique_including_archived(): void
    {
        Category::factory()->create(['name' => 'Gaming']);
        Category::factory()->create(['name' => 'Lama'])->delete();

        $this->actingAs($this->admin)->post(route('admin.categories.store'), ['name' => ''])->assertSessionHasErrors('name');
        $this->actingAs($this->admin)->post(route('admin.categories.store'), ['name' => str_repeat('a', 81)])->assertSessionHasErrors('name');
        $this->actingAs($this->admin)->post(route('admin.categories.store'), ['name' => 'Gaming'])->assertSessionHasErrors('name');
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), ['name' => 'Lama'])
            ->assertSessionHasErrors(['name' => 'Nama kategori sudah dipakai, termasuk kategori yang diarsipkan.']);

        $this->assertSame(2, Category::withTrashed()->count());
    }

    public function test_update_errors_use_separate_bag_and_allow_keeping_own_name(): void
    {
        Category::factory()->create(['name' => 'Gaming']);
        $category = Category::factory()->create(['name' => 'OLED']);

        $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), ['name' => 'Gaming'])
            ->assertSessionHasErrorsIn(CategoryRequest::UPDATE_BAG, 'name');

        $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), ['name' => 'OLED'])
            ->assertSessionHasNoErrors();
    }

    public function test_archive_keeps_existing_unit_links_and_restore_makes_it_selectable(): void
    {
        $category = Category::factory()->create(['name' => 'Headset']);
        $unit = Unit::factory()->create();
        $unit->categories()->attach($category);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('status');

        $this->assertSoftDeleted($category);
        $this->assertSame(['Headset'], $unit->fresh()->categories->pluck('name')->all());

        $this->actingAs($this->admin)
            ->get(route('admin.units.create'))
            ->assertOk()
            ->assertDontSee('name="categories[]" value="'.$category->id.'"', false);

        $this->actingAs($this->admin)
            ->post(route('admin.categories.restore', $category))
            ->assertSessionHas('status');

        $this->assertNotSoftDeleted($category);

        $this->actingAs($this->admin)
            ->get(route('admin.units.create'))
            ->assertSee('name="categories[]" value="'.$category->id.'"', false);
    }
}
