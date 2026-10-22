<?php

namespace App\Http\Controllers;

use App\Domain\Rental\HoldQuota;
use App\Http\Requests\ProfileRequest;
use App\Models\Rental;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('profile');

        return view('profile.edit', [
            'user' => $user,
            'profile' => $user->profile,
            'held' => $user->isMember() ? $user->heldUnitCount() : 0,
            'maxUnits' => HoldQuota::MAX_UNITS,
            'holdingRental' => $user->isMember()
                ? Rental::query()->where('user_id', $user->id)->holding()->latest('id')->first()
                : null,
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
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

        return redirect()->route('profile.edit')
            ->with('status', 'Profil tersimpan. Checkout berikutnya memakai data terbaru.');
    }
}
