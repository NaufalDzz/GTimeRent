<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Rental\RentalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RentalFilterRequest;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Contracts\View\View;

/**
 * Laporan riwayat transaksi siap cetak dengan filter yang sama seperti dashboard.
 */
class PrintController extends Controller
{
    public function __invoke(RentalFilterRequest $request): View
    {
        $rentals = $request->rentalQuery()->get();
        $counted = $rentals->reject(fn (Rental $rental): bool => $rental->status === RentalStatus::Cancelled);

        return view('admin.rentals.print', [
            'rentals' => $rentals,
            'filters' => $request->filters(),
            'members' => User::withTrashed()->where('role', User::ROLE_MEMBER)->orderBy('name')->get(['id', 'name']),
            'statusOptions' => RentalFilterRequest::statusOptions(),
            'memberLabel' => $request->memberLabel(),
            'statusLabel' => $request->statusLabel(),
            'periodLabel' => $request->periodLabel(),
            'totals' => [
                'count' => $counted->count(),
                'packages' => $counted->sum('subtotal'),
                'shipping' => $counted->sum('shipping_fee_snapshot'),
                'fines' => $counted->sum(fn (Rental $rental): int => $rental->fineTotal()),
            ],
        ]);
    }
}
