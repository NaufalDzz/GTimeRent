<?php

namespace Tests\Feature\Admin\Master;

use App\Models\Category;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_access_any_admin_master_route(): void
    {
        $member = User::factory()->create();
        $other = User::factory()->create();
        $unit = Unit::factory()->create();
        $category = Category::factory()->create();

        $requests = [
            ['get', route('admin.units.index')],
            ['get', route('admin.units.create')],
            ['post', route('admin.units.store')],
            ['get', route('admin.units.edit', $unit)],
            ['put', route('admin.units.update', $unit)],
            ['delete', route('admin.units.destroy', $unit)],
            ['post', route('admin.units.restore', $unit)],
            ['get', route('admin.categories.index')],
            ['post', route('admin.categories.store')],
            ['put', route('admin.categories.update', $category)],
            ['delete', route('admin.categories.destroy', $category)],
            ['post', route('admin.categories.restore', $category)],
            ['get', route('admin.members.index')],
            ['post', route('admin.members.store')],
            ['put', route('admin.members.update', $other)],
            ['delete', route('admin.members.destroy', $other)],
            ['post', route('admin.members.restore', $other)],
            ['get', route('admin.settings.edit')],
            ['put', route('admin.settings.update')],
        ];

        foreach ($requests as [$method, $url]) {
            $this->actingAs($member)->{$method}($url)->assertForbidden();
        }

        $this->assertNotSoftDeleted($unit);
        $this->assertNotSoftDeleted($category);
        $this->assertNotSoftDeleted($other);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.units.index'))->assertRedirect(route('login'));
    }
}
