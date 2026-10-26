<?php

namespace App\Http\Controllers;

use App\Application\Rental\CheckoutRental;
use App\Application\Rental\Data\Actor;
use App\Application\Rental\Data\CheckoutData;
use App\Application\Rental\TransactionConflict;
use App\Domain\Rental\HoldQuota;
use App\Domain\Rental\RentalRuleViolation;
use App\Http\Requests\CheckoutRequest;
use App\Models\Rental;
use App\Models\Setting;
use App\Models\Unit;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Halaman checkout. Angka di sini hanya pratinjau; total final dihitung ulang oleh use case.
     */
    public function create(Request $request, Cart $cart): View
    {
        $user = $request->user()->load('profile');
        $units = $cart->units();
        $setting = Setting::current();
        $subtotal = (int) $units->sum('package_price');

        return view('checkout.create', [
            'units' => $units,
            'held' => $user->heldUnitCount(),
            'maxUnits' => HoldQuota::MAX_UNITS,
            'subtotal' => $subtotal,
            'shippingFee' => $setting->flat_shipping_fee,
            'total' => $subtotal + $setting->flat_shipping_fee,
            'dailyFineTotal' => (int) $units->sum('daily_fine'),
            'serviceAreas' => config('gtrent.service_areas'),
            'serviceAreaText' => $setting->service_area_text,
            'defaults' => array_merge([
                'recipient_name' => $user->name,
                'recipient_phone' => $user->profile?->phone,
                'address' => $user->profile?->address,
                'service_area' => $user->profile?->service_area,
            ], $request->session()->get('checkout.shipping', [])),
        ]);
    }

    /**
     * Unit diambil dari keranjang session; harga atau total dari browser tidak pernah dibaca.
     */
    public function store(CheckoutRequest $request, Cart $cart, CheckoutRental $checkout): RedirectResponse
    {
        if ($cart->count() === 0) {
            return redirect()->route('checkout.create')->with('error', 'Keranjang masih kosong. Pilih unit dari katalog dulu.');
        }

        $data = $request->validated();
        $user = $request->user();
        $request->session()->put('checkout.shipping', $request->safe()->only([
            'recipient_name', 'recipient_phone', 'address', 'service_area',
        ]));

        try {
            $rental = $checkout->handle(
                new Actor($user->id, $user->isAdmin()),
                new CheckoutData(
                    $cart->ids(),
                    $data['recipient_name'],
                    $data['recipient_phone'],
                    $data['address'],
                    $data['service_area'],
                ),
            );
        } catch (RentalRuleViolation $violation) {
            return redirect()->route('checkout.failed')
                ->withInput($request->except('agree'))
                ->with('checkoutRejection', $violation->getMessage());
        } catch (TransactionConflict $conflict) {
            return back()->withInput()->with('error', $conflict->getMessage());
        }

        $cart->clear();
        $request->session()->forget('checkout.shipping');

        return redirect()->route('rentals.show', $rental->number)->with('newRental', true);
    }

    /**
     * Penjelasan checkout yang ditolak. Isian pengiriman tetap dibawa ke halaman checkout berikutnya.
     */
    public function failed(Request $request, Cart $cart): View|RedirectResponse
    {
        $reason = $request->session()->get('checkoutRejection');
        if (! is_string($reason)) {
            return redirect()->route('checkout.create');
        }

        $request->session()->keep(['_old_input']);

        $user = $request->user();
        $units = $cart->units();
        $held = $user->heldUnitCount();

        return view('checkout.failed', [
            'reason' => $reason,
            'units' => $units,
            'held' => $held,
            'maxUnits' => HoldQuota::MAX_UNITS,
            'isQuotaFull' => $held + $units->count() > HoldQuota::MAX_UNITS,
            'hasUnavailableUnit' => $units->contains(fn (Unit $unit): bool => ! $unit->isAvailable()),
            'holdingRental' => Rental::query()->where('user_id', $user->id)->holding()->latest('id')->first(),
        ]);
    }
}
