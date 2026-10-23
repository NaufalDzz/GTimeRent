<?php

namespace Tests\Feature\Member;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_member_with_one_profile_and_ignores_role(): void
    {
        $this->post(route('register'), [
            'name' => 'Anggota Baru',
            'email' => 'baru@gtrent.test',
            'password' => 'rahasia-123',
            'password_confirmation' => 'rahasia-123',
            'role' => User::ROLE_ADMIN,
        ])->assertRedirect(route('profile.edit'));

        $user = User::query()->where('email', 'baru@gtrent.test')->sole();
        $this->assertSame(User::ROLE_MEMBER, $user->role);
        $this->assertSame(1, Profile::query()->where('user_id', $user->id)->count());
        $this->assertNotSame('rahasia-123', $user->password);
        $this->assertTrue(Hash::check('rahasia-123', $user->password));
        $this->assertAuthenticatedAs($user);

        $this->get(route('profile.edit'))->assertOk()->assertSee('Akun dibuat.');
    }
}
