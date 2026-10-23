<?php

namespace Tests\Feature\Admin\Rentals;

use App\Domain\Rental\RentalStatus;
use App\Models\Rental;
use App\Models\Unit;
use Carbon\CarbonImmutable;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;

class RentalReturnFineTest extends AdminRentalTestCase
{
    private function dueAt(): CarbonImmutable
    {
        return $this->start->addHours(3)->addHours(120);
    }

    /**
     * @return array{0: Rental, 1: Unit, 2: Unit}
     */
    private function activeRentalWithTwoUnits(): array
    {
        $pc = Unit::factory()->create();
        $peripheral = Unit::factory()->peripheral()->create();

        return [$this->activeRental($this->member(), [$pc, $peripheral]), $pc, $peripheral];
    }

    private function postReturn(Rental $rental, CarbonImmutable $returnedAt, ?string $notes = null): TestResponse
    {
        return $this->actingAs($this->admin)
            ->from(route('admin.rentals.show', $rental))
            ->post(route('admin.rentals.return', $rental), ['returned_at' => $this->wibInput($returnedAt), 'notes' => $notes]);
    }

    public function test_return_before_or_at_due_has_no_fine(): void
    {
        [$early] = $this->activeRentalWithTwoUnits();
        [$exact] = $this->activeRentalWithTwoUnits();
        $this->travelToTime($this->dueAt()->addHour());

        $this->postReturn($early, $this->dueAt()->subHour())->assertSessionHas('status');
        $this->postReturn($exact, $this->dueAt())->assertSessionHas('status');

        foreach ([$early, $exact] as $rental) {
            $rental->refresh()->load('items');
            $this->assertSame(RentalStatus::Returned, $rental->status);
            $this->assertSame(0, $rental->fineTotal());
            $this->assertSame([0, 0], $rental->items->pluck('late_days')->all());
        }
    }

    /**
     * @return array<string, array{int, int}>
     */
    public static function lateReturns(): array
    {
        return [
            'satu detik' => [1, 1],
            'tepat 24 jam' => [86400, 1],
            '24 jam lebih satu detik' => [86401, 2],
        ];
    }

    #[DataProvider('lateReturns')]
    public function test_late_return_rounds_up_per_day_using_snapshot_fines(int $lateSeconds, int $expectedDays): void
    {
        [$rental] = $this->activeRentalWithTwoUnits();
        $returnedAt = $this->dueAt()->addSeconds($lateSeconds);
        $this->travelToTime($returnedAt->addMinute());

        $this->postReturn($rental, $returnedAt)
            ->assertRedirect(route('admin.rentals.show', $rental))
            ->assertSessionHas('status');

        $rental->refresh()->load('items');
        $this->assertSame(RentalStatus::Returned, $rental->status);
        $this->assertSame([$expectedDays, $expectedDays], $rental->items->pluck('late_days')->all());
        $this->assertSame([$expectedDays * 100000, $expectedDays * 20000], $rental->items->pluck('fine_amount')->all());
        $this->assertSame($expectedDays * 120000, $rental->fineTotal());
    }

    public function test_fine_uses_snapshot_when_master_daily_fine_changes(): void
    {
        [$rental, $pc, $peripheral] = $this->activeRentalWithTwoUnits();
        $pc->update(['daily_fine' => 999000]);
        $peripheral->update(['daily_fine' => 555000]);

        $returnedAt = $this->dueAt()->addHours(2);
        $this->travelToTime($returnedAt);
        $this->postReturn($rental, $returnedAt)->assertSessionHas('status');

        $this->assertSame(120000, $rental->refresh()->load('items')->fineTotal());
    }

    public function test_return_before_received_at_is_rejected(): void
    {
        [$rental] = $this->activeRentalWithTwoUnits();
        $this->travelToTime($this->start->addDay());

        $this->postReturn($rental, $this->start->addHours(2))
            ->assertRedirect(route('admin.rentals.show', $rental))
            ->assertSessionHas('error');

        $rental->refresh();
        $this->assertSame(RentalStatus::Active, $rental->status);
        $this->assertNull($rental->returned_at);
        $this->assertTrue(Unit::query()->held()->count() === 2);
    }

    public function test_return_in_future_is_rejected(): void
    {
        [$rental] = $this->activeRentalWithTwoUnits();
        $this->travelToTime($this->start->addDay());

        $this->postReturn($rental, $this->start->addDay()->addMinute())->assertSessionHas('error');

        $this->assertSame(RentalStatus::Active, $rental->refresh()->status);
        $this->assertNull($rental->returned_at);
    }

    public function test_return_requires_valid_time_format(): void
    {
        [$rental] = $this->activeRentalWithTwoUnits();

        $this->actingAs($this->admin)
            ->post(route('admin.rentals.return', $rental), ['returned_at' => ''])
            ->assertSessionHasErrors('returned_at');

        $this->assertSame(RentalStatus::Active, $rental->refresh()->status);
    }

    public function test_return_recorded_later_uses_actual_returned_at(): void
    {
        [$rental, $pc] = $this->activeRentalWithTwoUnits();
        $returnedAt = $this->dueAt()->subMinutes(10);
        $recordedAt = $this->dueAt()->addDays(3);
        $this->travelToTime($recordedAt);

        $this->postReturn($rental, $returnedAt, 'Lengkap, lecet kecil di casing.')->assertSessionHas('status');

        $rental->refresh()->load('items');
        $this->assertTrue($rental->returned_at->equalTo($returnedAt));
        $this->assertTrue($rental->return_recorded_at->equalTo($recordedAt));
        $this->assertSame($this->admin->id, $rental->returned_by);
        $this->assertSame('Lengkap, lecet kecil di casing.', $rental->return_notes);
        $this->assertSame(0, $rental->fineTotal());
        $this->assertTrue(Unit::query()->available()->whereKey($pc->id)->exists());
    }

    public function test_second_return_is_rejected_and_fine_not_doubled(): void
    {
        [$rental] = $this->activeRentalWithTwoUnits();
        $returnedAt = $this->dueAt()->addHours(30);
        $this->travelToTime($returnedAt->addHour());

        $this->postReturn($rental, $returnedAt)->assertSessionHas('status');
        $firstRecordedAt = $rental->refresh()->return_recorded_at;

        $this->travelToTime($returnedAt->addDays(2));
        $this->postReturn($rental, $returnedAt->addDay())->assertSessionHas('error');

        $rental->refresh()->load('items');
        $this->assertSame(2 * 120000, $rental->fineTotal());
        $this->assertTrue($rental->returned_at->equalTo($returnedAt));
        $this->assertTrue($rental->return_recorded_at->equalTo($firstRecordedAt));
    }

    public function test_returned_detail_is_read_only_with_final_fine(): void
    {
        [$rental] = $this->activeRentalWithTwoUnits();
        $returnedAt = $this->dueAt()->addHours(2);
        $this->travelToTime($returnedAt);
        $this->postReturn($rental, $returnedAt);

        $this->actingAs($this->admin)
            ->get(route('admin.rentals.show', $rental))
            ->assertOk()
            ->assertSee('Transaksi selesai')
            ->assertSee('Rp120.000')
            ->assertDontSee(route('admin.rentals.return', $rental));
    }
}
