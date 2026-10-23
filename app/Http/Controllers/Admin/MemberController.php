<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Rental\HoldQuota;
use App\Domain\Rental\RentalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MemberRequest;
use App\Models\RentalItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::withTrashed()
            ->with('profile')
            ->addSelect(['held_units' => RentalItem::query()
                ->selectRaw('count(*)')
                ->join('rentals', 'rentals.id', '=', 'rental_items.rental_id')
                ->whereColumn('rentals.user_id', 'users.id')
                ->whereIn('rentals.status', RentalStatus::holdingValues()),
            ])
            ->orderByRaw('role = ? desc', [User::ROLE_ADMIN])
            ->orderByRaw('deleted_at is not null')
            ->orderBy('name')
            ->get();

        $editing = null;
        if ($request->filled('ubah')) {
            $editing = $users->first(fn (User $user): bool => $user->id === $request->integer('ubah') && $user->isMember());
        }

        return view('admin.members.index', [
            'users' => $users,
            'editing' => $editing,
            'memberCount' => $users->filter(fn (User $user): bool => $user->isMember() && ! $user->trashed())->count(),
            'maxUnits' => HoldQuota::MAX_UNITS,
            'serviceAreas' => config('gtrent.service_areas'),
        ]);
    }

    /**
     * Akun dan profilnya dibuat dalam satu transaksi; role selalu anggota.
     */
    public function store(MemberRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $member = DB::transaction(function () use ($data): User {
            $member = new User(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
            $member->role = User::ROLE_MEMBER;
            $member->save();
            $member->profile()->create([
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'service_area' => $data['service_area'] ?? null,
            ]);

            return $member;
        });

        return redirect()->route('admin.members.index', ['ubah' => $member->id])
            ->with('status', "Anggota {$member->name} dibuat beserta profilnya.");
    }

    public function update(MemberRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($user, $data): void {
            $user->fill(['name' => $data['name'], 'email' => $data['email']]);
            if (filled($data['password'] ?? null)) {
                $user->password = $data['password'];
            }
            $user->save();

            $user->profile()->updateOrCreate([], [
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'service_area' => $data['service_area'] ?? null,
            ]);
        });

        return redirect()->route('admin.members.index', ['ubah' => $user->id])
            ->with('status', 'Tersimpan. Transaksi lama tetap memakai alamat snapshot.');
    }

    /**
     * Baris akun dikunci seperti saat checkout, sehingga arsip tidak bisa menyusul pesanan baru.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('error', 'Admin tidak dapat mengarsipkan akunnya sendiri.');
        }
        if (! $user->isMember()) {
            return back()->with('error', 'Akun admin tidak dapat diarsipkan dari halaman anggota.');
        }

        $held = DB::transaction(function () use ($user): int {
            $locked = User::query()->lockForUpdate()->findOrFail($user->id);
            $held = $locked->heldUnitCount();
            if ($held === 0) {
                $locked->delete();
            }

            return $held;
        });

        if ($held > 0) {
            return back()->with('error', "{$user->name} masih menahan {$held} unit. Arsip tersedia setelah semua pesanannya selesai atau dibatalkan.");
        }

        return redirect()->route('admin.members.index')
            ->with('status', "Akun {$user->name} diarsipkan dan tidak bisa masuk lagi. Riwayat pesanannya tetap terbaca.");
    }

    public function restore(User $user): RedirectResponse
    {
        $user->restore();

        return redirect()->route('admin.members.index', ['ubah' => $user->id])
            ->with('status', "Akun {$user->name} dipulihkan dan bisa masuk kembali.");
    }
}
