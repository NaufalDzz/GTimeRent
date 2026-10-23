<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'setting' => Setting::current(),
            'serviceAreas' => config('gtrent.service_areas'),
        ]);
    }

    /**
     * Pesanan lama memakai shipping_fee_snapshot, jadi nilai baru hanya berlaku untuk checkout berikutnya.
     */
    public function update(SettingRequest $request): RedirectResponse
    {
        Setting::current()->update($request->validated());

        return redirect()->route('admin.settings.edit')
            ->with('status', 'Tarif baru tersimpan dan berlaku untuk checkout berikutnya.');
    }
}
