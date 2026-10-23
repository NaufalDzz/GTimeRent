<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\UnitKind;
use App\Models\Setting;
use App\Models\Unit;
use App\Support\Format;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Beranda publik. Teks spesifikasi tetap statis sesuai desain Main.dc.html,
     * sedangkan harga, denda, status, dan ongkir dibaca dari data terkini.
     */
    public function __invoke(): View
    {
        $featured = [
            'PC-MID-001' => 'Ryzen 5 7600 · RTX 5070 12GB · 32GB DDR5 · Acer 27" 1440p 180Hz',
            'PC-HIGH-001' => 'Ryzen 7 9800X3D · RTX 5070 Ti 16GB · MSI 27" QD-OLED 360Hz',
            'PER-A1-001' => 'PRO X2 Superstrike · Wooting 80HE · PRO X3 Lightspeed · Artisan XL',
            'PER-A2-001' => 'G304 Lightspeed · AULA F75 · HyperX Cloud III · Logitech G640',
        ];

        $records = Unit::query()->withHeldFlag()->whereIn('code', array_keys($featured))->get()->keyBy('code');

        $units = collect($featured)
            ->filter(fn (string $spec, string $code): bool => $records->has($code))
            ->map(fn (string $spec, string $code): array => [
                'code' => $code,
                'name' => $records[$code]->name,
                'spec' => $spec,
                'price' => Format::rupiah($records[$code]->package_price),
                'fine' => Format::rupiah($records[$code]->daily_fine),
                'isPc' => $records[$code]->kind === UnitKind::PcMonitor,
                'isPer' => $records[$code]->kind !== UnitKind::PcMonitor,
                'availability' => $records[$code]->availabilityLabel(),
                'tone' => $records[$code]->availabilityTone(),
            ])
            ->values()
            ->all();

        $shippingFee = Setting::current()->flat_shipping_fee;
        $examplePc = $records->get('PC-MID-001');
        $examplePeripheral = $records->get('PER-A2-001');
        $example = [
            'pcPrice' => $examplePc?->package_price ?? 0,
            'peripheralPrice' => $examplePeripheral?->package_price ?? 0,
            'pcFine' => $examplePc?->daily_fine ?? 0,
            'peripheralFine' => $examplePeripheral?->daily_fine ?? 0,
        ];
        $example['total'] = $example['pcPrice'] + $example['peripheralPrice'] + $shippingFee;

        $adminContact = config('gtrent.admin_contact');

        $steps = [
            ['no' => '01', 'title' => 'Pilih 1–2 unit', 'body' => 'Cari paket PC, peripheral, atau fullset. Stok baru ditahan saat checkout berhasil.', 'tag' => 'Menunggu bayar'],
            ['no' => '02', 'title' => 'Bayar tunai/transfer', 'body' => 'Pembayaran di luar aplikasi. Admin mengonfirmasi lunas sebelum barang dikirim.', 'tag' => 'Lunas'],
            ['no' => '03', 'title' => 'Diantar kurir GTR', 'body' => 'Semua unit dalam pesanan diantar bersamaan ke alamat di area layanan.', 'tag' => 'Dikirim'],
            ['no' => '04', 'title' => 'Main 5 hari', 'body' => 'Masa sewa 120 jam dimulai saat semua unit kamu terima.', 'tag' => 'Aktif'],
            ['no' => '05', 'title' => 'Dijemput kembali', 'body' => 'Hubungi admin. Denda dihitung per 24 jam keterlambatan, dibulatkan ke atas.', 'tag' => 'Dikembalikan'],
        ];

        return view('home', compact('units', 'steps', 'shippingFee', 'example', 'adminContact'));
    }
}
