<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Akun demo dari docs/data-demo.json. Password diambil dari DEMO_PASSWORD di .env.
 */
class DemoUserSeeder extends Seeder
{
    /**
     * Profil fiktif per anggota demo.
     *
     * @var array<string, array{phone: string, address: string, service_area: string}>
     */
    private const PROFILES = [
        'anggota1@gtrent.test' => ['phone' => '081200000001', 'address' => 'Jl. Telekomunikasi No. 1, Sukapura', 'service_area' => 'Dayeuhkolot'],
        'anggota2@gtrent.test' => ['phone' => '081200000002', 'address' => 'Jl. Sukabirus No. 12, Citeureup', 'service_area' => 'Dayeuhkolot'],
        'anggota3@gtrent.test' => ['phone' => '081200000003', 'address' => 'Jl. Bojongsoang Raya No. 45', 'service_area' => 'Bojongsoang'],
    ];

    public function run(): void
    {
        $password = config('gtrent.demo_password');
        if (blank($password)) {
            throw new RuntimeException('Isi DEMO_PASSWORD di .env sebelum menjalankan seeder.');
        }

        foreach (File::json(base_path('docs/data-demo.json'))['demo_users'] as $row) {
            $user = User::withTrashed()->firstOrNew(['email' => $row['email']]);
            $user->forceFill([
                'name' => $row['name'],
                'password' => $password,
                'role' => $row['role'],
                'email_verified_at' => now(),
                'deleted_at' => null,
            ])->save();

            if (isset(self::PROFILES[$row['email']])) {
                $user->profile()->updateOrCreate([], self::PROFILES[$row['email']]);
            }
        }
    }
}
