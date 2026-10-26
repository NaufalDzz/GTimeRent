<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $previewUnits = Unit::query()->whereIn('code', ['PC-MID-001', 'PER-A2-001'])->get();

        return view('admin.settings.edit', [
            'setting' => Setting::current(),
            'serviceAreas' => config('gtrent.service_areas'),
            'previewSubtotal' => $previewUnits->count() === 2 ? (int) $previewUnits->sum('package_price') : null,
            'history' => DB::table('shipping_fee_changes')
                ->join('users', 'users.id', '=', 'shipping_fee_changes.changed_by')
                ->select('shipping_fee_changes.*', 'users.name as admin_name')
                ->orderByDesc('shipping_fee_changes.id')->get(),
        ]);
    }

    /**
     * Pesanan lama memakai shipping_fee_snapshot, jadi nilai baru hanya berlaku untuk checkout berikutnya.
     */
    public function update(SettingRequest $request): RedirectResponse
    {
        Setting::current();
        DB::transaction(function () use ($request): void {
            $setting = Setting::query()->lockForUpdate()->findOrFail(1);
            $data = $request->validated();
            $nextFee = (int) $data['flat_shipping_fee'];

            if ($setting->flat_shipping_fee !== $nextFee) {
                DB::table('shipping_fee_changes')->insert([
                    'previous_fee' => $setting->flat_shipping_fee,
                    'new_fee' => $nextFee,
                    'changed_by' => $request->user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $setting->update($data);
        });

        return redirect()->route('admin.settings.edit')
            ->with('status', 'Tarif baru tersimpan dan berlaku untuk checkout berikutnya.');
    }
}
