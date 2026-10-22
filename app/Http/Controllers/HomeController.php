<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Beranda publik. Data paket dan alur sewa masih statis, sama dengan desain Main.dc.html.
     */
    public function __invoke(): View
    {
        $units = [
            ['code' => 'PC-MID-001', 'name' => 'PC Mid + IPS', 'spec' => 'Ryzen 5 7600 · RTX 5070 12GB · 32GB DDR5 · Acer 27" 1440p 180Hz', 'price' => 'Rp500.000', 'fine' => 'Rp100.000', 'isPc' => true, 'isPer' => false],
            ['code' => 'PC-HIGH-001', 'name' => 'PC High + OLED', 'spec' => 'Ryzen 7 9800X3D · RTX 5070 Ti 16GB · MSI 27" QD-OLED 360Hz', 'price' => 'Rp1.200.000', 'fine' => 'Rp200.000', 'isPc' => true, 'isPer' => false],
            ['code' => 'PER-A1-001', 'name' => 'A1 High-End Lengkap', 'spec' => 'PRO X2 Superstrike · Wooting 80HE · PRO X3 Lightspeed · Artisan XL', 'price' => 'Rp350.000', 'fine' => 'Rp50.000', 'isPc' => false, 'isPer' => true],
            ['code' => 'PER-A2-001', 'name' => 'A2 Mid-Range Lengkap', 'spec' => 'G304 Lightspeed · AULA F75 · HyperX Cloud III · Logitech G640', 'price' => 'Rp125.000', 'fine' => 'Rp20.000', 'isPc' => false, 'isPer' => true],
        ];

        $steps = [
            ['no' => '01', 'title' => 'Pilih 1–2 unit', 'body' => 'Cari paket PC, peripheral, atau fullset. Stok baru ditahan saat checkout berhasil.', 'tag' => 'Menunggu bayar'],
            ['no' => '02', 'title' => 'Bayar tunai/transfer', 'body' => 'Pembayaran di luar aplikasi. Admin mengonfirmasi lunas sebelum barang dikirim.', 'tag' => 'Lunas'],
            ['no' => '03', 'title' => 'Diantar kurir GTR', 'body' => 'Semua unit dalam pesanan diantar bersamaan ke alamat di area layanan.', 'tag' => 'Dikirim'],
            ['no' => '04', 'title' => 'Main 5 hari', 'body' => 'Masa sewa 120 jam dimulai saat semua unit kamu terima.', 'tag' => 'Aktif'],
            ['no' => '05', 'title' => 'Dijemput kembali', 'body' => 'Hubungi admin. Denda dihitung per 24 jam keterlambatan, dibulatkan ke atas.', 'tag' => 'Dikembalikan'],
        ];

        return view('home', compact('units', 'steps'));
    }
}
