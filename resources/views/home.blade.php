<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>GTime Rent · Beranda</title>
{{-- Pasang penanda animasi sebelum paint pertama supaya h1 dan tombol tidak berkedip tampil lalu hilang.
     Kalau modul animasi tidak jalan dalam 3 detik, penanda dicabut lagi dan halaman tampil normal. --}}
<script>
(function (root) {
    root.classList.add('tw-js');
    setTimeout(function () {
        if (!root.hasAttribute('data-tw-ready')) root.classList.remove('tw-js');
    }, 3000);
})(document.documentElement);
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
body{margin:0;background:#0A0608}
a{color:#F4B3D3}a:hover{color:#FFFFFF}
@media (max-width: 860px){.hide-sm{display:none !important}}
</style>
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="gtr-home" style="--accent: #E2589C; font-family: Inter, system-ui, sans-serif; color: #F6EEF2; background: #0A0608; min-height: 100vh">

<section data-hero style="position: relative; overflow: hidden; min-height: 860px; display: flex; flex-direction: column; background: radial-gradient(110% 90% at 90% 100%, #3B1526 0%, #1A0A11 45%, #0A0608 80%)">
<div aria-hidden="true" style="position: absolute; width: 2440px; height: 2440px; left: -920px; top: -2110px; border-radius: 50%; background: radial-gradient(circle at 76% 84%, #A33A6E 0%, #5A1636 28%, #2A0B1A 55%, #12060B 75%); box-shadow: 0 0 160px 40px rgba(226,88,156,0.30), inset -30px -40px 90px rgba(255,214,234,0.62), inset -6px -8px 18px rgba(255,236,246,0.85)"></div>

<header style="position: relative; z-index: 2; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; padding: 22px 40px">
<a href="{{ route('home') }}" style="font-family: Poppins, sans-serif; font-weight: 700; font-size: 20px; color: #F6EEF2; text-decoration: none; letter-spacing: -0.01em">/GTime Rent</a>
<nav aria-label="Navigasi utama" style="display: flex; flex-wrap: wrap; gap: 28px; font-size: 14px">
<a href="{{ route('catalog.index') }}" style="color: #D9C7D0; text-decoration: none">Katalog</a>
<a href="#paket" style="color: #D9C7D0; text-decoration: none">Paket</a>
<a href="#cara" style="color: #D9C7D0; text-decoration: none">Cara sewa</a>
<a href="#area" style="color: #D9C7D0; text-decoration: none">Area layanan</a>
</nav>
<div style="display: flex; align-items: center; gap: 20px">
@auth
<a href="{{ auth()->user()->isAdmin() ? route('admin.rentals.index') : route('rentals.index') }}" style="font-size: 14px; color: #F6EEF2; text-decoration: none">{{ auth()->user()->isAdmin() ? 'Dashboard' : 'Pesanan saya' }}</a>
<a href="{{ route('profile.edit') }}" style="display: inline-flex; align-items: center; min-height: 44px; padding: 0 20px; border-radius: 999px; background: #F6EEF2; color: #0A0608; font-size: 14px; font-weight: 600; text-decoration: none">Profil</a>
@else
<a href="{{ route('login') }}" style="font-size: 14px; color: #F6EEF2; text-decoration: none">Masuk</a>
<a href="{{ route('login', ['tab' => 'daftar']) }}" style="display: inline-flex; align-items: center; min-height: 44px; padding: 0 20px; border-radius: 999px; background: #F6EEF2; color: #0A0608; font-size: 14px; font-weight: 600; text-decoration: none">Daftar</a>
@endauth
</div>
</header>

<div style="position: relative; z-index: 2; flex-grow: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 96px 24px 64px">
<div style="display: inline-flex; align-items: center; gap: 10px; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.14); border-radius: 999px; background: rgba(10,6,8,0.45); font-family: 'JetBrains Mono', monospace; font-size: 12px; letter-spacing: 0.08em; color: #E7D6DF">
<span style="width: 6px; height: 6px; background: var(--accent)"></span>
SEWA PC GAMING · DIANTAR KE RUMAH
</div>
<h1 data-typewriter style="margin: 28px 0 0; font-family: Poppins, sans-serif; font-weight: 800; font-size: clamp(64px, 11.5vw, 176px); line-height: 0.95; letter-spacing: -0.035em; color: #FFFFFF"><span class="tw-sr">/GTime Rent</span><span class="tw-sizer" aria-hidden="true">/GTime Rent</span><span class="tw-layer" aria-hidden="true"></span></h1>
<p data-tw-reveal style="margin: 22px 0 0; font-family: Poppins, sans-serif; font-weight: 600; font-size: 22px; color: #FFFFFF">Gaming Time Rent</p>
<p data-tw-reveal style="margin: 18px 0 0; font-size: 13px; font-weight: 500; letter-spacing: 0.34em; color: #D9C7D0">PC / MONITOR / PERIPHERALS / DELIVERY</p>
<div data-tw-reveal style="display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 44px">
<a href="{{ route('catalog.index') }}" style="display: inline-flex; align-items: center; gap: 8px; min-height: 48px; padding: 0 24px; border-radius: 999px; background: #F6EEF2; color: #0A0608; font-weight: 600; font-size: 15px; text-decoration: none">Lihat katalog
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></a>
<a href="#cara" style="display: inline-flex; align-items: center; min-height: 48px; padding: 0 24px; border-radius: 999px; border: 1px solid rgba(255,255,255,0.22); background: rgba(10,6,8,0.4); color: #F6EEF2; font-weight: 500; font-size: 15px; text-decoration: none">Cara sewa</a>
</div>
</div>

<div class="hide-sm" style="position: absolute; z-index: 3; right: 32px; bottom: 104px; width: 264px; padding: 14px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.12); background: rgba(19,12,16,0.82)">
<div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 600">Fullset Mid <span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 400; color: #B3A0AA">2 UNIT</span></div>
<div style="margin-top: 10px; padding: 12px; border-radius: 8px; background: #0A0608; font-family: 'JetBrains Mono', monospace; font-size: 12px; line-height: 1.7; color: #D9C7D0">
<div>PC-MID-001 · PC + IPS</div>
<div>PER-A2-001 · Peripheral</div>
<div style="color: #5FD39A">+ ongkir antar-jemput</div>
</div>
<div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 10px; font-size: 12px; color: #B3A0AA">5 hari, bayar di awal <span style="font-family: Poppins, sans-serif; font-size: 16px; font-weight: 600; color: #FFFFFF">{{ \App\Support\Format::rupiah($example['total']) }}</span></div>
</div>

<div style="position: relative; z-index: 2; display: flex; flex-wrap: wrap; justify-content: space-around; gap: 20px 40px; padding: 26px 40px; border-top: 1px solid rgba(255,255,255,0.08); font-family: 'JetBrains Mono', monospace; font-size: 12px; letter-spacing: 0.12em; color: #B3A0AA">
<span>BANDUNG</span><span>BOJONGSOANG</span><span>DAYEUHKOLOT</span><span>ANTAR-JEMPUT KURIR GTR</span><span>MASA SEWA 5 HARI</span>
</div>
</section>

<section id="paket" style="max-width: 1280px; margin: 0 auto; padding: 128px 32px 64px; box-sizing: border-box">
<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 32px">
<div>
<div style="display: flex; align-items: center; gap: 10px; font-family: 'JetBrains Mono', monospace; font-size: 12px; letter-spacing: 0.1em; color: #B3A0AA"><span style="width: 6px; height: 6px; background: var(--accent)"></span>PILIH PAKET</div>
<h2 style="margin: 18px 0 0; font-family: 'JetBrains Mono', monospace; font-weight: 300; font-size: clamp(30px, 3.6vw, 48px); line-height: 1.2; letter-spacing: -0.02em; color: #B3A0AA">Satu kode, satu paket fisik.<br><span style="color: #F6EEF2">Gabungkan jadi fullset.</span></h2>
</div>
<p style="margin: 0; max-width: 380px; font-size: 15px; line-height: 1.65; color: #B3A0AA">PC beserta monitornya dihitung satu unit, paket peripheral juga satu unit. Fullset berarti dua kode unit dalam satu pesanan, dan ongkir tetap dibayar sekali.</p>
</div>

<div style="position: relative; overflow: hidden; margin-top: 48px; border-radius: 22px; border: 1px solid rgba(255,255,255,0.1); background: linear-gradient(120deg, #4A1530 0%, #22091A 48%, #3A1620 100%); padding: 56px 40px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 40px">
<div style="max-width: 300px">
<p style="margin: 0; font-size: 16px; font-weight: 600; color: #FFFFFF">Rencana main akhir pekan, total jelas sebelum bayar.</p>
<p style="margin: 10px 0 0; font-size: 14px; line-height: 1.6; color: #D9C7D0">PC Mid + IPS dan peripheral A2 dipesan bersama: dua kode berbeda, satu tagihan, dikirim dan dijemput bersamaan.</p>
</div>

<div style="flex: 0 1 400px; min-width: 280px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.12); background: #100A0D; box-shadow: 0 30px 80px rgba(0,0,0,0.5); overflow: hidden">
<div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #2B1D24">
<span style="font-family: Poppins, sans-serif; font-weight: 600; font-size: 15px">Ringkasan pesanan</span>
<span style="padding: 4px 10px; border-radius: 999px; background: #2B1D24; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #D9C7D0">2 / 2 unit</span>
</div>
<div style="padding: 8px 20px">
<div style="display: flex; justify-content: space-between; gap: 16px; padding: 14px 0; border-bottom: 1px solid #22171D">
<div><div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #F4B3D3">PC-MID-001</div><div style="margin-top: 4px; font-size: 14px">PC Mid + IPS</div><div style="margin-top: 2px; font-size: 12px; color: #94808A">RTX 5070 · 27" 1440p 180Hz</div></div>
<div style="font-size: 14px; font-weight: 500; white-space: nowrap">{{ \App\Support\Format::rupiah($example['pcPrice']) }}</div>
</div>
<div style="display: flex; justify-content: space-between; gap: 16px; padding: 14px 0; border-bottom: 1px solid #22171D">
<div><div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #F4B3D3">PER-A2-001</div><div style="margin-top: 4px; font-size: 14px">A2 Mid-Range Lengkap</div><div style="margin-top: 2px; font-size: 12px; color: #94808A">Mouse · keyboard · headset · mousepad</div></div>
<div style="font-size: 14px; font-weight: 500; white-space: nowrap">{{ \App\Support\Format::rupiah($example['peripheralPrice']) }}</div>
</div>
<div style="display: flex; justify-content: space-between; gap: 16px; padding: 14px 0; font-size: 14px; color: #D9C7D0">Ongkir antar-jemput (sekali)<span style="white-space: nowrap">{{ \App\Support\Format::rupiah($shippingFee) }}</span></div>
</div>
<div style="display: flex; justify-content: space-between; align-items: baseline; padding: 16px 20px; background: #1B1217">
<span style="font-size: 13px; color: #B3A0AA">Total dibayar di awal</span>
<span style="font-family: Poppins, sans-serif; font-size: 22px; font-weight: 700">{{ \App\Support\Format::rupiah($example['total']) }}</span>
</div>
</div>

<div style="display: flex; flex-direction: column; gap: 12px; max-width: 260px">
<div style="padding: 16px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.12); background: rgba(10,6,8,0.55)">
<div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.08em; color: #B3A0AA">DENDA / HARI</div>
<div style="margin-top: 6px; font-family: Poppins, sans-serif; font-size: 20px; font-weight: 600">{{ \App\Support\Format::rupiah($example['pcFine'] + $example['peripheralFine']) }}</div>
<div style="margin-top: 4px; font-size: 12px; color: #B3A0AA">{{ \App\Support\Format::rupiah($example['pcFine']) }} + {{ \App\Support\Format::rupiah($example['peripheralFine']) }} per unit</div>
</div>
<div style="padding: 16px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.12); background: rgba(10,6,8,0.55)">
<div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.08em; color: #B3A0AA">MULAI SEWA</div>
<div style="margin-top: 6px; font-size: 14px; line-height: 1.5">Saat semua unit diterima, bukan saat checkout.</div>
</div>
</div>
</div>
</section>

<section style="max-width: 1280px; margin: 0 auto; padding: 64px 32px; box-sizing: border-box">
<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 16px">
<div>
<div style="display: flex; align-items: center; gap: 10px; font-family: 'JetBrains Mono', monospace; font-size: 12px; letter-spacing: 0.1em; color: #B3A0AA"><span style="width: 6px; height: 6px; background: var(--accent)"></span>KATALOG</div>
<h2 style="margin: 14px 0 0; font-family: Poppins, sans-serif; font-weight: 600; font-size: 32px; letter-spacing: -0.02em">Paket yang siap diantar</h2>
</div>
<a href="{{ route('catalog.index') }}" style="display: inline-flex; align-items: center; gap: 6px; min-height: 44px; font-size: 14px; text-decoration: none">Lihat semua paket
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></a>
</div>

<div style="margin-top: 32px; display: flex; flex-wrap: wrap; gap: 20px">
@foreach ($units as $u)
<a href="{{ route('catalog.show', $u['code']) }}" style="flex: 1 1 260px; display: flex; flex-direction: column; border-radius: 18px; border: 1px solid #2B1D24; background: #130C10; overflow: hidden; color: #F6EEF2; text-decoration: none">
<div style="position: relative; height: 176px; display: flex; align-items: center; justify-content: center; background: radial-gradient(90% 120% at 80% 100%, #5A1A3B 0%, #1B0A12 70%)">
@if ($u['isPc'])
<svg width="84" height="84" viewBox="0 0 24 24" fill="none" stroke="#F4B3D3" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="13" rx="2"></rect><path d="M8 21h8M12 17v4"></path></svg>
@endif
@if ($u['isPer'])
<svg width="84" height="84" viewBox="0 0 24 24" fill="none" stroke="#F4B3D3" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="11" rx="2"></rect><path d="M6 11h.01M9 11h.01M12 11h.01M15 11h.01M18 11h.01M7 14.5h10"></path></svg>
@endif
<span style="position: absolute; top: 14px; left: 14px; padding: 4px 8px; border-radius: 6px; background: rgba(10,6,8,0.7); font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #F4B3D3">{{ $u['code'] }}</span>
<span class="badge tone-{{ $u['tone'] }}" style="position: absolute; top: 14px; right: 14px">{{ $u['availability'] }}</span>
</div>
<div style="padding: 18px; display: flex; flex-direction: column; gap: 8px; flex-grow: 1">
<div style="font-family: Poppins, sans-serif; font-weight: 600; font-size: 18px">{{ $u['name'] }}</div>
<div style="font-size: 13px; line-height: 1.5; color: #B3A0AA">{{ $u['spec'] }}</div>
<div style="margin-top: auto; padding-top: 14px; display: flex; justify-content: space-between; align-items: baseline; border-top: 1px solid #22171D">
<span style="font-family: Poppins, sans-serif; font-weight: 600; font-size: 17px">{{ $u['price'] }} <span style="font-family: Inter, sans-serif; font-weight: 400; font-size: 12px; color: #94808A">/ 5 hari</span></span>
<span style="font-size: 12px; color: #94808A">denda {{ $u['fine'] }}/hari</span>
</div>
</div>
</a>
@endforeach
</div>
</section>

<section id="cara" style="max-width: 1280px; margin: 0 auto; padding: 96px 32px 64px; box-sizing: border-box">
<div style="display: flex; align-items: center; gap: 10px; font-family: 'JetBrains Mono', monospace; font-size: 12px; letter-spacing: 0.1em; color: #B3A0AA"><span style="width: 6px; height: 6px; background: var(--accent)"></span>ALUR SEWA</div>
<h2 style="margin: 18px 0 0; font-family: 'JetBrains Mono', monospace; font-weight: 300; font-size: clamp(30px, 3.6vw, 48px); line-height: 1.2; letter-spacing: -0.02em; color: #B3A0AA">Dari checkout sampai<br><span style="color: #F6EEF2">dijemput lagi.</span></h2>

<ol style="list-style: none; margin: 56px 0 0; padding: 0; display: flex; flex-wrap: wrap; gap: 0; border-top: 1px solid #2B1D24">
@foreach ($steps as $s)
<li style="flex: 1 1 220px; padding: 28px 24px 8px 0; box-sizing: border-box">
<div style="display: flex; align-items: center; gap: 10px">
<span style="width: 10px; height: 10px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 0 4px rgba(226,88,156,0.18)"></span>
<span style="font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #94808A">{{ $s['no'] }}</span>
</div>
<div style="margin-top: 18px; font-family: Poppins, sans-serif; font-weight: 600; font-size: 18px">{{ $s['title'] }}</div>
<p style="margin: 8px 0 0; font-size: 14px; line-height: 1.6; color: #B3A0AA">{{ $s['body'] }}</p>
<span style="display: inline-block; margin-top: 14px; padding: 4px 10px; border-radius: 999px; border: 1px solid #3A2830; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #D9C7D0">{{ $s['tag'] }}</span>
</li>
@endforeach
</ol>
</section>

<section id="area" style="max-width: 1280px; margin: 0 auto; padding: 64px 32px 96px; box-sizing: border-box">
<div style="display: flex; flex-wrap: wrap; gap: 20px">
<div style="flex: 1 1 280px; padding: 32px; border-radius: 20px; border: 1px solid #2B1D24; background: #100A0D">
<div style="font-family: 'JetBrains Mono', monospace; font-weight: 300; font-size: 96px; line-height: 1; letter-spacing: -0.04em">120</div>
<div style="margin-top: 14px; font-size: 15px; font-weight: 600">jam masa sewa</div>
<p style="margin: 6px 0 0; font-size: 14px; line-height: 1.6; color: #B3A0AA">Dihitung dari waktu semua unit diterima. Waktu pengiriman tidak memotong lima hari kamu.</p>
</div>
<div style="flex: 1 1 280px; padding: 32px; border-radius: 20px; border: 1px solid #2B1D24; background: #100A0D">
<div style="font-family: 'JetBrains Mono', monospace; font-weight: 300; font-size: 96px; line-height: 1; letter-spacing: -0.04em">2</div>
<div style="margin-top: 14px; font-size: 15px; font-weight: 600">unit maksimal per anggota</div>
<p style="margin: 6px 0 0; font-size: 14px; line-height: 1.6; color: #B3A0AA">Berlaku untuk semua pesanan yang masih berjalan, termasuk yang belum dibayar.</p>
</div>
<div style="flex: 1 1 280px; padding: 32px; border-radius: 20px; border: 1px solid #2B1D24; background: #100A0D">
<div style="font-family: 'JetBrains Mono', monospace; font-weight: 300; font-size: 96px; line-height: 1; letter-spacing: -0.04em">@if ($shippingFee % 1000 === 0){{ $shippingFee / 1000 }}<span style="font-size: 48px">rb</span>@else<span style="font-size: 48px">{{ \App\Support\Format::rupiah($shippingFee) }}</span>@endif</div>
<div style="margin-top: 14px; font-size: 15px; font-weight: 600">ongkir antar-jemput</div>
<p style="margin: 6px 0 0; font-size: 14px; line-height: 1.6; color: #B3A0AA">Sekali per pesanan, tidak dikali jumlah unit dan tidak ditagih lagi saat penjemputan.</p>
</div>
</div>

<div style="margin-top: 20px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 24px; padding: 28px 32px; border-radius: 20px; border: 1px solid #2B1D24; background: #130C10">
<div style="display: flex; align-items: center; gap: 16px">
<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#F4B3D3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"></path><circle cx="12" cy="9.5" r="2.5"></circle></svg>
<div>
<div style="font-size: 15px; font-weight: 600">Area layanan kurir GTR</div>
<div style="margin-top: 4px; font-size: 13px; color: #B3A0AA">Admin memverifikasi alamat sebelum pembayaran dikonfirmasi.</div>
</div>
</div>
<div style="display: flex; flex-wrap: wrap; gap: 8px">
<span style="padding: 8px 14px; border-radius: 999px; background: #22171D; font-size: 13px">Bandung</span>
<span style="padding: 8px 14px; border-radius: 999px; background: #22171D; font-size: 13px">Bojongsoang</span>
<span style="padding: 8px 14px; border-radius: 999px; background: #22171D; font-size: 13px">Dayeuhkolot</span>
</div>
</div>
</section>

<section style="position: relative; overflow: hidden; padding: 120px 32px; background: radial-gradient(80% 120% at 50% 120%, #4A1530 0%, #1A0A11 55%, #0A0608 85%)">
<div aria-hidden="true" style="position: absolute; width: 1800px; height: 1800px; left: 50%; top: 260px; margin-left: -900px; border-radius: 50%; background: #12060B; box-shadow: 0 -10px 120px 10px rgba(226,88,156,0.28), inset 0 24px 60px rgba(255,214,234,0.35)"></div>
<div style="position: relative; max-width: 1216px; margin: 0 auto; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 32px; padding: 48px; box-sizing: border-box; border-radius: 22px; border: 1px solid rgba(255,255,255,0.14); background: rgba(27,18,23,0.55); backdrop-filter: blur(18px)">
<div>
<div style="display: flex; align-items: center; gap: 10px; font-family: 'JetBrains Mono', monospace; font-size: 12px; letter-spacing: 0.1em; color: #D9C7D0"><span style="width: 6px; height: 6px; background: var(--accent)"></span>MULAI SEWA</div>
<h2 style="margin: 18px 0 0; font-family: 'JetBrains Mono', monospace; font-weight: 300; font-size: clamp(30px, 3.6vw, 48px); line-height: 1.2; letter-spacing: -0.02em; color: #E7D6DF">Siap main<br>malam ini?</h2>
</div>
<div style="max-width: 400px">
<p style="margin: 0; font-size: 15px; line-height: 1.65; color: #D9C7D0">Daftar sebagai anggota, pilih paket, lalu bayar tunai atau transfer. Kurir GTR yang mengantar dan menjemput.</p>
<div style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 24px">
<a href="{{ route('login', ['tab' => 'daftar']) }}" style="display: inline-flex; align-items: center; min-height: 48px; padding: 0 24px; border-radius: 999px; background: #F6EEF2; color: #0A0608; font-weight: 600; font-size: 15px; text-decoration: none">Daftar sekarang</a>
<a href="{{ route('login') }}" style="display: inline-flex; align-items: center; min-height: 48px; padding: 0 24px; border-radius: 999px; border: 1px solid rgba(255,255,255,0.22); color: #F6EEF2; font-size: 15px; text-decoration: none">Saya sudah punya akun</a>
</div>
</div>
</div>
</section>

<footer style="border-top: 1px solid #1F151A; background: #0A0608">
<div style="max-width: 1280px; margin: 0 auto; padding: 56px 32px 40px; box-sizing: border-box; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 40px">
<div style="max-width: 320px">
<div style="font-family: Poppins, sans-serif; font-weight: 700; font-size: 22px">/GTime Rent</div>
<p style="margin: 10px 0 0; font-size: 13px; line-height: 1.6; color: #94808A">Rental PC gaming dan peripheral untuk dipakai di rumah, diantar dan dijemput kurir GTR.</p>
</div>
<div style="display: flex; flex-wrap: wrap; gap: 64px; font-size: 14px">
<div style="display: flex; flex-direction: column; gap: 10px">
<span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.1em; color: #94808A">PRODUK</span>
<a href="{{ route('catalog.index') }}" style="color: #D9C7D0; text-decoration: none">Paket PC + monitor</a>
<a href="{{ route('catalog.index') }}" style="color: #D9C7D0; text-decoration: none">Peripheral</a>
<a href="{{ route('catalog.index') }}" style="color: #D9C7D0; text-decoration: none">Fullset</a>
</div>
<div style="display: flex; flex-direction: column; gap: 10px">
<span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.1em; color: #94808A">BANTUAN</span>
<a href="#cara" style="color: #D9C7D0; text-decoration: none">Cara sewa</a>
<a href="#area" style="color: #D9C7D0; text-decoration: none">Area layanan</a>
<span style="color: #D9C7D0">{{ $adminContact }}</span>
</div>
</div>
</div>
<div style="max-width: 1280px; margin: 0 auto; padding: 20px 32px 32px; box-sizing: border-box; border-top: 1px solid #1F151A; font-size: 12px; color: #94808A">© 2026 GTime Rent · Proyek akademik. Harga yang tampil adalah data demo.</div>
</footer>

</div>
<canvas class="glyph-field" data-glyph-field aria-hidden="true" hidden></canvas>
</body>
</html>
