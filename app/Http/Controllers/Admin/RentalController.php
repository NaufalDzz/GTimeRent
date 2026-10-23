<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Rental\RentalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RentalFilterRequest;
use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\User;
use Illuminate\Contracts\View\View;

class RentalController extends Controller
{
    /**
     * Dashboard transaksi: ringkasan status dan daftar seluruh pesanan sesuai filter.
     */
    public function index(RentalFilterRequest $request): View
    {
        $counts = Rental::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $kpis = [
            ['label' => 'Menunggu bayar', 'value' => (int) ($counts[RentalStatus::PendingPayment->value] ?? 0), 'hint' => 'Perlu dicek lunas', 'status' => RentalStatus::PendingPayment->value, 'tone' => 'warning'],
            ['label' => 'Siap dikirim', 'value' => (int) ($counts[RentalStatus::Paid->value] ?? 0), 'hint' => 'Sudah lunas', 'status' => RentalStatus::Paid->value, 'tone' => 'info'],
            ['label' => 'Dalam pengiriman', 'value' => (int) ($counts[RentalStatus::InDelivery->value] ?? 0), 'hint' => 'Catat saat diterima', 'status' => RentalStatus::InDelivery->value, 'tone' => 'info'],
            ['label' => 'Sedang disewa', 'value' => (int) ($counts[RentalStatus::Active->value] ?? 0), 'hint' => 'Termasuk yang terlambat', 'status' => RentalStatus::Active->value, 'tone' => 'success'],
            ['label' => 'Terlambat', 'value' => Rental::query()->overdue()->count(), 'hint' => 'Lewat jatuh tempo', 'status' => RentalFilterRequest::STATUS_OVERDUE, 'tone' => 'danger'],
            ['label' => 'Unit ditahan', 'value' => RentalItem::query()->whereHas('rental', fn ($query) => $query->holding())->count(), 'hint' => 'Belum kembali atau dibatalkan', 'status' => null, 'tone' => 'muted'],
        ];

        return view('admin.rentals.index', [
            'kpis' => $kpis,
            'rentals' => $request->rentalQuery()->paginate(25)->withQueryString(),
            'filters' => $request->filters(),
            'filterParameters' => $request->queryParameters(),
            'statusOptions' => RentalFilterRequest::statusOptions(),
            'members' => User::withTrashed()->where('role', User::ROLE_MEMBER)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Detail pesanan beserta riwayat status dan hanya aksi yang sah untuk status saat ini.
     */
    public function show(Rental $rental): View
    {
        $rental->load(['user', 'items', 'paymentConfirmer', 'dispatcher', 'receiptRecorder', 'returnRecorder', 'canceller']);

        return view('admin.rentals.show', [
            'rental' => $rental,
        ]);
    }
}
