<?php

namespace Tests\Feature\Admin\Master;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    use CreatesRentals, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create(['name' => 'Admin Demo']);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Anggota Baru',
            'email' => 'baru@gtrent.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'phone' => '0812-3456 7890',
            'address' => 'Jl. Telekomunikasi No. 1, Bojongsoang',
            'service_area' => 'Bojongsoang',
        ], $overrides);
    }

    public function test_index_lists_members_and_admins_with_held_count_and_status(): void
    {
        $member = User::factory()->create(['name' => 'Anggota Penahan']);
        $this->checkout($member, [Unit::factory()->create()]);
        User::factory()->create(['name' => 'Anggota Arsip'])->delete();

        $this->actingAs($this->admin)
            ->get(route('admin.members.index'))
            ->assertOk()
            ->assertSee('Tambah akun anggota')
            ->assertSee('Admin Demo')
            ->assertSee('Hanya baca')
            ->assertSee('Anggota Penahan')
            ->assertSee('1 / 2')
            ->assertSee('Anggota Arsip')
            ->assertSee('Diarsipkan');

        $this->actingAs($this->admin)
            ->get(route('admin.members.index', ['ubah' => $member->id]))
            ->assertOk()
            ->assertSee('Ubah anggota')
            ->assertSee('Masih menahan 1 unit.');
    }

    public function test_admin_creates_member_with_profile_and_role_is_always_member(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.members.store'), $this->payload(['role' => User::ROLE_ADMIN]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $member = User::query()->where('email', 'baru@gtrent.test')->firstOrFail();
        $this->assertTrue($member->isMember());
        $this->assertTrue(Hash::check('rahasia123', $member->password));
        $this->assertSame('081234567890', $member->profile->phone);
        $this->assertSame('Bojongsoang', $member->profile->service_area);
    }

    public function test_member_can_be_created_without_optional_contact(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.members.store'), $this->payload(['phone' => '', 'address' => '', 'service_area' => '']))
            ->assertSessionHasNoErrors();

        $this->assertNotNull(User::query()->where('email', 'baru@gtrent.test')->firstOrFail()->profile);
    }

    public function test_store_validates_fields_and_email_unique_including_archived(): void
    {
        User::factory()->create(['email' => 'lama@gtrent.test'])->delete();

        $this->actingAs($this->admin)
            ->post(route('admin.members.store'), $this->payload([
                'name' => 'A',
                'email' => 'bukan-email',
                'password' => 'pendek',
                'password_confirmation' => 'beda',
                'phone' => '12ab',
                'address' => 'pendek',
                'service_area' => 'Jakarta',
            ]))
            ->assertSessionHasErrors(['name', 'email', 'password', 'phone', 'address', 'service_area']);

        $this->actingAs($this->admin)
            ->post(route('admin.members.store'), $this->payload(['email' => 'lama@gtrent.test']))
            ->assertSessionHasErrors(['email' => 'Email sudah dipakai, termasuk akun yang diarsipkan.']);

        $this->assertSame(2, User::withTrashed()->count());
    }

    public function test_admin_updates_member_contact_and_optional_password_without_role(): void
    {
        $member = User::factory()->create(['email' => 'anggota@gtrent.test']);
        $oldHash = $member->password;

        $this->actingAs($this->admin)
            ->put(route('admin.members.update', $member), $this->payload([
                'email' => 'anggota@gtrent.test',
                'password' => '',
                'password_confirmation' => '',
                'role' => User::ROLE_ADMIN,
            ]))
            ->assertSessionHasNoErrors();

        $member->refresh();
        $this->assertSame('Anggota Baru', $member->name);
        $this->assertSame($oldHash, $member->password);
        $this->assertTrue($member->isMember());
        $this->assertSame('Bojongsoang', $member->profile->service_area);

        $this->actingAs($this->admin)
            ->put(route('admin.members.update', $member), $this->payload(['email' => 'anggota@gtrent.test', 'password' => 'baru12345', 'password_confirmation' => 'baru12345']))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('baru12345', $member->fresh()->password));
    }

    public function test_admin_accounts_are_read_only_on_member_form(): void
    {
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.members.update', $otherAdmin), $this->payload())
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->delete(route('admin.members.destroy', $otherAdmin))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($otherAdmin);
    }

    public function test_admin_cannot_archive_self(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.members.destroy', $this->admin))
            ->assertSessionHas('error', 'Admin tidak dapat mengarsipkan akunnya sendiri.');

        $this->assertNotSoftDeleted($this->admin);
    }

    public function test_t23_member_holding_units_cannot_be_archived_until_rental_is_returned(): void
    {
        $member = User::factory()->create();
        $rental = $this->checkout($member, [Unit::factory()->create()]);

        $this->actingAs($this->admin)
            ->delete(route('admin.members.destroy', $member))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($member);

        $this->completeRental($this->admin, $rental);

        $this->actingAs($this->admin)
            ->delete(route('admin.members.destroy', $member))
            ->assertSessionHas('status');

        $this->assertSoftDeleted($member);
        $this->assertTrue($rental->fresh()->user->is($member));
        $this->assertSame(1, $rental->fresh()->items()->count());
    }

    public function test_archived_member_cannot_log_in_and_can_be_restored(): void
    {
        $member = User::factory()->create(['email' => 'arsip@gtrent.test']);

        $this->actingAs($this->admin)
            ->delete(route('admin.members.destroy', $member))
            ->assertSessionHas('status');

        $this->post(route('logout'));

        $this->post(route('login.store'), ['email' => 'arsip@gtrent.test', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($this->admin)
            ->post(route('admin.members.restore', $member))
            ->assertSessionHas('status');

        $this->assertNotSoftDeleted($member);
    }
}
