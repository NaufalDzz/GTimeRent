<?php

namespace App\Http\Controllers;

use App\Domain\Rental\HoldQuota;
use App\Models\Unit;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Menambah unit (atau pasangan fullset) ke keranjang session. Stok belum ditahan.
     */
    public function store(Request $request, Cart $cart): RedirectResponse
    {
        $codes = array_values(array_filter((array) $request->input('units', []), 'filled'));
        $request->merge(['units' => $codes]);
        $request->validate([
            'units' => ['required', 'array', 'min:1', 'max:'.HoldQuota::MAX_UNITS],
            'units.*' => ['string', 'distinct', 'exists:units,code'],
        ]);

        $units = Unit::query()->withHeldFlag()->whereIn('code', $codes)->get();
        foreach ($units as $unit) {
            if (! $unit->isAvailable()) {
                return back()->with('error', "Unit {$unit->code} belum bisa dipesan ({$unit->availabilityLabel()}).");
            }
        }

        if (! $cart->add($units->pluck('id')->all())) {
            return back()->with('error', 'Keranjang maksimal '.HoldQuota::MAX_UNITS.' unit. Hapus salah satu unit di keranjang dulu.');
        }

        return redirect()->route('checkout.create')->with('status', 'Unit masuk keranjang. Lengkapi data pengiriman untuk menahannya.');
    }

    public function destroy(Unit $unit, Cart $cart): RedirectResponse
    {
        $cart->remove($unit->id);

        return redirect()->route('checkout.create')->with('status', "Unit {$unit->code} dihapus dari keranjang.");
    }
}
