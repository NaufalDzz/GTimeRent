<?php

namespace App\Http\Controllers;

use App\Domain\Rental\HoldQuota;
use App\Domain\Rental\RentalStatus;
use App\Models\Rental;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RentalController extends Controller
{
    /**
     * Riwayat pesanan milik anggota yang sedang masuk saja.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('rentals.index', [
            'rentals' => Rental::query()
                ->where('user_id', $user->id)
                ->with('items')
                ->latest('id')
                ->paginate(10),
            'held' => $user->heldUnitCount(),
            'maxUnits' => HoldQuota::MAX_UNITS,
        ]);
    }

    /**
     * Detail pesanan. Akses dibatasi oleh RentalPolicy::view di route.
     */
    public function show(Request $request, Rental $rental): View
    {
        $rental->load('items');

        return view('rentals.show', [
            'rental' => $rental,
            'steps' => $this->steps($rental),
            'isNew' => $request->session()->get('newRental') === true,
            'held' => $request->user()->heldUnitCount(),
            'maxUnits' => HoldQuota::MAX_UNITS,
            'adminContact' => config('gtrent.admin_contact'),
        ]);
    }

    /**
     * Langkah perjalanan pesanan beserta waktunya.
     *
     * @return list<array{label: string, at: ?DateTimeInterface, state: string}>
     */
    private function steps(Rental $rental): array
    {
        if ($rental->status === RentalStatus::Cancelled) {
            return [
                ['label' => RentalStatus::PendingPayment->label(), 'at' => $rental->created_at, 'state' => 'done'],
                ['label' => RentalStatus::Cancelled->label(), 'at' => $rental->cancelled_at, 'state' => 'stopped'],
            ];
        }

        $steps = [
            ['label' => RentalStatus::PendingPayment->label(), 'at' => $rental->created_at],
            ['label' => RentalStatus::Paid->label(), 'at' => $rental->payment_confirmed_at],
            ['label' => RentalStatus::InDelivery->label(), 'at' => $rental->dispatched_at],
            ['label' => 'Diterima · aktif 120 jam', 'at' => $rental->received_at],
            ['label' => RentalStatus::Returned->label(), 'at' => $rental->returned_at],
        ];

        $reached = count(array_filter($steps, fn (array $step): bool => $step['at'] !== null));

        return array_map(fn (array $step, int $index): array => [
            ...$step,
            'state' => match (true) {
                $index < $reached - 1, $rental->status === RentalStatus::Returned => 'done',
                $index === $reached - 1 => 'current',
                default => 'todo',
            },
        ], $steps, array_keys($steps));
    }
}
