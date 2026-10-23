<?php

namespace Tests\Feature\Member;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function profileData(User $user, array $overrides = []): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '0812-3456 7890',
            'address' => 'Jl. Telekomunikasi No. 1, Bojongsoang',
            'service_area' => 'Bojongsoang',
            ...$overrides,
        ];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_member_profile_page_renders(): void
    {
        $member = User::factory()->create(['name' => 'Anggota Profil']);
        $member->profile()->create(['phone' => '081234567890', 'address' => 'Jl. Profil No. 9, Bandung', 'service_area' => 'Bandung']);

        $this->actingAs($member)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Anggota Profil')
            ->assertSee('Jl. Profil No. 9, Bandung')
            ->assertSee('Unit ditahan')
            ->assertSee('Simpan perubahan')
            ->assertSee(route('logout'));
    }

    public function test_admin_profile_page_renders(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Akun admin')
            ->assertDontSee('Unit ditahan');
    }

    public function test_member_updates_own_profile_without_changing_role(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->put(route('profile.update'), $this->profileData($member, ['name' => 'Nama Baru', 'role' => User::ROLE_ADMIN]))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasNoErrors();

        $member->refresh()->load('profile');
        $this->assertSame('Nama Baru', $member->name);
        $this->assertSame(User::ROLE_MEMBER, $member->role);
        $this->assertSame('081234567890', $member->profile->phone);
        $this->assertSame('Bojongsoang', $member->profile->service_area);

        $this->get(route('profile.edit'))->assertSee('Profil tersimpan.');
    }

    public function test_email_must_be_unique(): void
    {
        $member = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($member)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), $this->profileData($member, ['email' => $other->email]))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('email');

        $this->assertNotSame($other->email, $member->fresh()->email);
    }

    public function test_password_change_requires_current_password(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->put(route('profile.update'), $this->profileData($member, [
                'current_password' => 'salah-sekali',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ]))
            ->assertSessionHasErrors('current_password');

        $this->put(route('profile.update'), $this->profileData($member, [
            'current_password' => 'password',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ]))->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('password-baru', $member->fresh()->password));
    }
}
