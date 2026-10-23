<?php

namespace Tests\Feature\Member;

use App\Application\Rental\CheckoutRental;
use App\Application\Rental\Data\Actor;
use App\Application\Rental\Data\CheckoutData;
use App\Domain\Rental\RentalStatus;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function shippingData(array $overrides = []): array
    {
        return [
            'recipient_name' => 'Anggota Uji',
            'recipient_phone' => '081234567890',
            'address' => 'Jl. Telekomunikasi No. 1, Bojongsoang',
            'service_area' => 'Bojongsoang',
            'agree' => '1',
            ...$overrides,
        ];
    }

    /**
     * @param  list<int>  $unitIds
     */
    private function checkoutDirectly(User $member, array $unitIds): void
    {
        app(CheckoutRental::class)->handle(
            new Actor($member->id, false),
            new CheckoutData($unitIds, $member->name, '081234567890', 'Jl. Telekomunikasi No. 1', 'Bandung'),
        );
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('checkout.create'))->assertRedirect(route('login'));
        $this->post(route('checkout.store'), $this->shippingData())->assertRedirect(route('login'));
    }

    public function test_admin_cannot_access_checkout(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('checkout.create'))->assertForbidden();
        $this->actingAs($admin)->post(route('checkout.store'), $this->shippingData())->assertForbidden();
    }

    public function test_empty_cart_page_links_to_catalog(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('Keranjang kamu masih kosong.')
            ->assertSee(route('catalog.index'));
    }

    public function test_checkout_page_shows_items_and_single_shipping_fee(): void
    {
        $member = User::factory()->create();
        $member->profile()->create(['phone' => '089999999999', 'address' => 'Jl. Profil No. 5, Bandung', 'service_area' => 'Bandung']);
        $pc = Unit::factory()->create();
        $peripheral = Unit::factory()->peripheral()->create();

        $this->actingAs($member)
            ->post(route('cart.store'), ['units' => [$pc->code, $peripheral->code]])
            ->assertRedirect(route('checkout.create'));

        $this->get(route('checkout.create'))
            ->assertOk()
            ->assertSee($pc->code)
            ->assertSee($peripheral->code)
            ->assertSee('Rp75.000')
            ->assertSee('Rp700.000')
            ->assertSee('089999999999')
            ->assertSee('Jl. Profil No. 5, Bandung');
    }

    public function test_fullset_mid_checkout_totals_700000_with_shipping_once(): void
    {
        $member = User::factory()->create();
        $pc = Unit::factory()->create();
        $peripheral = Unit::factory()->peripheral()->create();

        $this->actingAs($member)->post(route('cart.store'), ['units' => [$pc->code, $peripheral->code]]);
        $response = $this->post(route('checkout.store'), $this->shippingData());

        $rental = Rental::query()->with('items')->sole();
        $response->assertRedirect(route('rentals.show', $rental))->assertSessionHas('newRental', true);

        $this->assertSame(RentalStatus::PendingPayment, $rental->status);
        $this->assertCount(2, $rental->items);
        $this->assertSame(625000, $rental->subtotal);
        $this->assertSame(75000, $rental->shipping_fee_snapshot);
        $this->assertSame(700000, $rental->initial_total);
        $this->assertSame($member->id, $rental->user_id);
        $this->assertSame([], session('cart.units', []));

        $this->get(route('rentals.show', $rental))
            ->assertOk()
            ->assertSee('Pesanan dibuat.')
            ->assertSee($rental->number)
            ->assertSee('Rp700.000')
            ->assertSee('Cara bayar')
            ->assertSee(config('gtrent.admin_contact'));
    }

    public function test_browser_sent_prices_and_totals_are_ignored(): void
    {
        $member = User::factory()->create();
        $pc = Unit::factory()->create();
        $peripheral = Unit::factory()->peripheral()->create();

        $this->actingAs($member)->post(route('cart.store'), ['units' => [$pc->code, $peripheral->code]]);
        $this->post(route('checkout.store'), $this->shippingData([
            'total' => '1',
            'initial_total' => '1',
            'subtotal' => '1',
            'shipping_fee' => '0',
            'package_price' => ['1', '1'],
            'unit_ids' => [999999],
        ]))->assertRedirect();

        $rental = Rental::query()->with('items')->sole();
        $this->assertSame(700000, $rental->initial_total);
        $this->assertSame(75000, $rental->shipping_fee_snapshot);
        $this->assertSame([500000, 125000], $rental->items->pluck('package_price_snapshot')->all());
        $this->assertSame(500000, $pc->fresh()->package_price);
    }

    public function test_member_holding_two_units_cannot_checkout_another_order(): void
    {
        $member = User::factory()->create();
        $this->checkoutDirectly($member, Unit::factory()->count(2)->create()->pluck('id')->all());
        $extra = Unit::factory()->peripheral()->create();

        $this->actingAs($member)
            ->withSession(['cart.units' => [$extra->id]])
            ->post(route('checkout.store'), $this->shippingData())
            ->assertRedirect(route('checkout.failed'))
            ->assertSessionHas('checkoutRejection');

        $this->assertSame(1, Rental::query()->count());
        $this->assertSame(2, $member->heldUnitCount());
        $this->assertSame([$extra->id], session('cart.units'));

        $this->get(route('checkout.failed'))
            ->assertOk()
            ->assertSee('Kuota unit kamu sudah penuh')
            ->assertSee('Kuota sewa terlampaui')
            ->assertSee('Tidak ada unit yang ditahan dari percobaan ini.');
    }

    public function test_one_unavailable_unit_rolls_back_whole_checkout(): void
    {
        $member = User::factory()->create();
        $other = User::factory()->create();
        $taken = Unit::factory()->create();
        $free = Unit::factory()->peripheral()->create();
        $this->checkoutDirectly($other, [$taken->id]);

        $this->actingAs($member)
            ->withSession(['cart.units' => [$taken->id, $free->id]])
            ->post(route('checkout.store'), $this->shippingData())
            ->assertRedirect(route('checkout.failed'))
            ->assertSessionHasInput('address', 'Jl. Telekomunikasi No. 1, Bojongsoang');

        $this->assertSame(0, Rental::query()->where('user_id', $member->id)->count());
        $this->assertSame(0, $member->heldUnitCount());
        $this->assertFalse($free->fresh()->isHeld());

        $this->get(route('checkout.failed'))
            ->assertOk()
            ->assertSee('Ada unit yang sudah tidak tersedia')
            ->assertSee($taken->code);

        $this->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('Jl. Telekomunikasi No. 1, Bojongsoang');
    }

    public function test_maintenance_unit_is_rejected_even_when_injected_into_session(): void
    {
        $member = User::factory()->create();
        $maintenance = Unit::factory()->maintenance()->create();

        $this->actingAs($member)
            ->post(route('cart.store'), ['units' => [$maintenance->code]])
            ->assertSessionHas('error');
        $this->assertSame([], session('cart.units', []));

        $this->withSession(['cart.units' => [$maintenance->id]])
            ->post(route('checkout.store'), $this->shippingData())
            ->assertRedirect(route('checkout.failed'));

        $this->assertSame(0, Rental::query()->count());
    }

    public function test_archived_unit_is_rejected_even_when_injected_into_session(): void
    {
        $member = User::factory()->create();
        $archived = Unit::factory()->create();
        $archived->delete();

        $this->actingAs($member)
            ->withSession(['cart.units' => [$archived->id]])
            ->post(route('checkout.store'), $this->shippingData())
            ->assertRedirect(route('checkout.failed'));

        $this->assertSame(0, Rental::query()->count());
    }

    public function test_checkout_validates_shipping_data_and_keeps_input(): void
    {
        $member = User::factory()->create();
        $unit = Unit::factory()->create();

        $this->actingAs($member)
            ->withSession(['cart.units' => [$unit->id]])
            ->from(route('checkout.create'))
            ->post(route('checkout.store'), $this->shippingData([
                'recipient_phone' => 'abc',
                'service_area' => 'Jakarta',
                'agree' => null,
            ]))
            ->assertRedirect(route('checkout.create'))
            ->assertSessionHasErrors(['recipient_phone', 'service_area', 'agree'])
            ->assertSessionHasInput('recipient_name', 'Anggota Uji');

        $this->assertSame(0, Rental::query()->count());
    }

    public function test_failed_page_without_reason_redirects_to_checkout(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('checkout.failed'))->assertRedirect(route('checkout.create'));
    }
}
