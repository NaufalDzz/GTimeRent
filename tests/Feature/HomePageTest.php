<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_uses_current_prices_shipping_fee_and_availability(): void
    {
        Unit::factory()->create(['code' => 'PC-MID-001', 'package_price' => 510000, 'daily_fine' => 100000]);
        Unit::factory()->peripheral()->create(['code' => 'PER-A2-001', 'package_price' => 125000, 'daily_fine' => 20000]);
        Unit::factory()->maintenance()->create(['code' => 'PC-HIGH-001']);
        Setting::current()->update(['flat_shipping_fee' => 80000]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Rp510.000')
            ->assertSee('Rp80.000')
            ->assertSee('Rp715.000')
            ->assertSee('Maintenance')
            ->assertSee(config('gtrent.admin_contact'))
            ->assertDontSee('[NOMOR WHATSAPP]');
    }
}
