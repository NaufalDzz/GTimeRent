<?php

namespace Database\Seeders;

use App\Application\Rental\CancelRental;
use App\Application\Rental\CheckoutRental;
use App\Application\Rental\ConfirmPayment;
use App\Application\Rental\Data\Actor;
use App\Application\Rental\Data\CheckoutData;
use App\Application\Rental\DispatchRental;
use App\Application\Rental\RecordReceipt;
use App\Application\Rental\RecordReturn;
use App\Domain\Rental\PaymentMethod;
use App\Models\Unit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Transaksi fixture untuk demo, relatif terhadap waktu seeding (T). Semua dibuat lewat
 * use case Application dengan jam yang dimajukan, sehingga snapshot dan aturan tetap berlaku.
 *
 * - anggota1: PC-MID-002 + PER-B2-001 dikembalikan terlambat 2 hari (denda Rp230.000),
 *             PER-H1-001 dibatalkan admin, PER-B1-001 sedang dikirim. Menahan 1 unit.
 * - anggota2: PC-MID-001 + PER-A1-001 aktif dan terlambat (jatuh tempo T-1 hari 3 jam). Kuota penuh.
 * - anggota3: PER-A2-001 menunggu bayar dan PC-HIGH-001 lunas. Kuota penuh.
 *
 * Unit bebas setelah seeding: PC-MID-002, PER-B2-001, PER-H1-001, PER-H2-001.
 */
class DemoRentalSeeder extends Seeder
{
    private CarbonImmutable $seededAt;

    private Actor $admin;

    public function run(): void
    {
        $this->seededAt = CarbonImmutable::now()->utc();
        $this->admin = new Actor(User::query()->where('email', 'admin@gtrent.test')->value('id'), true);

        try {
            $this->seedAnggota1();
            $this->seedAnggota2();
            $this->seedAnggota3();
        } finally {
            Carbon::setTestNow();
        }
    }

    private function seedAnggota1(): void
    {
        $member = $this->member('anggota1@gtrent.test');

        $returned = $this->checkout($member, ['PC-MID-002', 'PER-B2-001'], '-10 days');
        $this->at('-10 days +1 hour', fn () => app(ConfirmPayment::class)->handle($this->admin, $returned, PaymentMethod::Transfer));
        $this->at('-10 days +3 hours', fn () => app(DispatchRental::class)->handle($this->admin, $returned, $this->time('-10 days +3 hours')));
        $this->at('-10 days +5 hours', fn () => app(RecordReceipt::class)->handle($this->admin, $returned, $this->time('-10 days +5 hours')));
        $this->at('-3 days +2 hours', fn () => app(RecordReturn::class)->handle(
            $this->admin,
            $returned,
            $this->time('-3 days +2 hours'),
            'Unit lengkap, terlambat dua hari.',
        ));

        $cancelled = $this->checkout($member, ['PER-H1-001'], '-2 days');
        $this->at('-2 days +4 hours', fn () => app(CancelRental::class)->handle(
            $this->admin,
            $cancelled,
            'Alamat belum dapat diverifikasi, anggota meminta batal.',
        ));

        $inDelivery = $this->checkout($member, ['PER-B1-001'], '-5 hours');
        $this->at('-4 hours', fn () => app(ConfirmPayment::class)->handle($this->admin, $inDelivery, PaymentMethod::Cash));
        $this->at('-30 minutes', fn () => app(DispatchRental::class)->handle($this->admin, $inDelivery, $this->time('-30 minutes')));
    }

    private function seedAnggota2(): void
    {
        $member = $this->member('anggota2@gtrent.test');

        $overdue = $this->checkout($member, ['PC-MID-001', 'PER-A1-001'], '-7 days');
        $this->at('-7 days +1 hour', fn () => app(ConfirmPayment::class)->handle($this->admin, $overdue, PaymentMethod::Cash));
        $this->at('-7 days +2 hours', fn () => app(DispatchRental::class)->handle($this->admin, $overdue, $this->time('-7 days +2 hours')));
        $this->at('-6 days -3 hours', fn () => app(RecordReceipt::class)->handle($this->admin, $overdue, $this->time('-6 days -3 hours')));
    }

    private function seedAnggota3(): void
    {
        $member = $this->member('anggota3@gtrent.test');

        $paid = $this->checkout($member, ['PC-HIGH-001'], '-3 hours');
        $this->at('-1 hour', fn () => app(ConfirmPayment::class)->handle($this->admin, $paid, PaymentMethod::Transfer));

        $this->checkout($member, ['PER-A2-001'], '-2 hours');
    }

    /**
     * @param  list<string>  $codes
     */
    private function checkout(User $member, array $codes, string $offset): int
    {
        $profile = $member->profile;

        return $this->at($offset, fn () => app(CheckoutRental::class)->handle(
            new Actor($member->id, false),
            new CheckoutData(
                Unit::query()->whereIn('code', $codes)->pluck('id')->all(),
                $member->name,
                $profile->phone,
                $profile->address,
                $profile->service_area,
            ),
        ))->id;
    }

    private function at(string $offset, callable $work): mixed
    {
        Carbon::setTestNow($this->time($offset));

        return $work();
    }

    private function time(string $offset): CarbonImmutable
    {
        return $this->seededAt->modify($offset);
    }

    private function member(string $email): User
    {
        return User::query()->with('profile')->where('email', $email)->firstOrFail();
    }
}
