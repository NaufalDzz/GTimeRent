@extends('layouts.app')

@section('title', 'Checkout')

@php
    use App\Support\Format;

    $afterCheckout = $held + $units->count();
    $selectedArea = old('service_area', $defaults['service_area']);
@endphp

@section('content')
<ol aria-label="Langkah checkout" class="hstack small" style="list-style: none; margin: 0 0 28px; padding: 0; gap: 8px">
<li class="badge tone-muted" style="padding: 8px 14px">1 · Keranjang</li>
<li aria-current="step" class="badge tone-danger" style="padding: 8px 14px; color: #fff; background: #3a1220">2 · Pengiriman &amp; tagihan</li>
<li class="badge" style="padding: 8px 14px; border: 1px solid var(--card-line); color: var(--faint)">3 · Menunggu bayar</li>
</ol>

<div class="eyebrow">Checkout</div>
<h1 class="h1-mono">Ke mana rig-nya<br><span>kami antar?</span></h1>

@if ($units->isEmpty())
<div class="empty" style="margin-top: 36px">
<p style="margin: 0; font-size: 16px; color: var(--text)">Keranjang kamu masih kosong.</p>
<p class="small" style="margin: 8px 0 20px">Pilih paket PC atau peripheral dari katalog. Unit baru ditahan untukmu setelah checkout berhasil.</p>
<a href="{{ route('catalog.index') }}" class="btn btn-primary">Buka katalog</a>
</div>
@else
<form method="POST" action="{{ route('checkout.store') }}" id="checkout-form" class="row" style="margin-top: 36px" novalidate>
@csrf
<div class="stack-lg" style="flex: 999 1 560px; min-width: 0; gap: 28px">

<section class="card" aria-labelledby="h-units">
<div class="between">
<h2 id="h-units" class="h2" style="font-size: 18px">Unit dipesan</h2>
<span class="mono tiny text-muted">{{ $units->count() }} dari maks. {{ $maxUnits }}</span>
</div>
<ul class="stack-sm" style="list-style: none; margin: 16px 0 0; padding: 0">
@foreach ($units as $unit)
<li class="item hstack" style="gap: 16px">
<span class="icon-tile" aria-hidden="true"><x-unit-icon :kind="$unit->kind" size="26" stroke="1.3" /></span>
<div style="flex: 1 1 200px; min-width: 0">
<div class="code" style="font-size: 11px">{{ $unit->code }}</div>
<div style="margin-top: 2px; font-weight: 500">{{ $unit->name }}</div>
<div class="tiny text-dim" style="margin-top: 2px">Denda {{ Format::rupiah($unit->daily_fine) }}/hari</div>
</div>
@unless ($unit->isAvailable())
<span class="badge tone-{{ $unit->availabilityTone() }}">{{ $unit->availabilityLabel() }}</span>
@endunless
<span style="font-weight: 500">{{ Format::rupiah($unit->package_price) }}</span>
<button type="submit" form="remove-{{ $unit->code }}" class="icon-btn" aria-label="Hapus {{ $unit->code }} dari keranjang">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"></path></svg>
</button>
</li>
@endforeach
</ul>
@if ($units->count() < $maxUnits)
<a href="{{ route('catalog.index') }}" class="small" style="display: inline-block; margin-top: 14px">Tambah unit lain dari katalog</a>
@endif
</section>

<section class="card stack" aria-labelledby="h-ship" style="gap: 18px">
<div class="between">
<h2 id="h-ship" class="h2" style="font-size: 18px">Penerima &amp; alamat</h2>
<span class="tiny text-dim">Diisi dari profil, bisa diubah untuk pesanan ini</span>
</div>
<div class="field-row" style="gap: 14px">
<div class="field">
<label for="c-name">Nama penerima</label>
<input id="c-name" name="recipient_name" type="text" class="input" value="{{ old('recipient_name', $defaults['recipient_name']) }}" autocomplete="name" required maxlength="100" @error('recipient_name') aria-invalid="true" aria-describedby="recipient_name-error" @enderror>
<x-field-error name="recipient_name" />
</div>
<div class="field">
<label for="c-phone">Nomor telepon</label>
<input id="c-phone" name="recipient_phone" type="tel" class="input" value="{{ old('recipient_phone', $defaults['recipient_phone']) }}" placeholder="+62 8xx xxxx xxxx" autocomplete="tel" required @error('recipient_phone') aria-invalid="true" aria-describedby="recipient_phone-error" @enderror>
<x-field-error name="recipient_phone" />
</div>
</div>
<div class="field">
<label for="c-addr">Alamat lengkap</label>
<textarea id="c-addr" name="address" rows="3" class="input" placeholder="Nama jalan, nomor rumah, RT/RW, patokan" autocomplete="street-address" required maxlength="1000" @error('address') aria-invalid="true" aria-describedby="address-error" @enderror>{{ old('address', $defaults['address']) }}</textarea>
<x-field-error name="address" />
</div>
<fieldset style="margin: 0; padding: 0; border: 0">
<legend class="field-label" style="padding: 0">Area layanan</legend>
<div class="row" style="margin-top: 10px; gap: 10px">
@foreach ($serviceAreas as $area)
<label class="radio-card" style="flex: 1 1 160px">
<span class="hstack" style="gap: 10px"><input type="radio" name="service_area" value="{{ $area }}" @checked($selectedArea === $area) required><span class="dot" aria-hidden="true"></span><span style="font-size: 14px">{{ $area }}</span></span>
</label>
@endforeach
</div>
<x-field-error name="service_area" />
<p class="note" style="margin-top: 10px">{{ $serviceAreaText }} Di luar area ini belum bisa dilayani.</p>
</fieldset>
</section>
</div>

<aside class="aside-card card-flush" aria-labelledby="h-bill" style="flex: 1 1 360px; min-width: 0; border-radius: 20px">
<h2 id="h-bill" class="poppins" style="margin: 0; padding: 20px 24px; border-bottom: 1px solid var(--card-line); font-size: 16px; font-weight: 600">Rincian tagihan</h2>
<div class="stack-sm" style="padding: 16px 24px; gap: 12px">
@foreach ($units as $unit)
<div class="sum-row"><span>{{ $unit->name }} <span class="mono text-dim" style="font-size: 11px">{{ $unit->code }}</span></span><span>{{ Format::rupiah($unit->package_price) }}</span></div>
@endforeach
<div class="sum-row" style="padding-top: 12px; border-top: 1px solid var(--rule)"><span>Ongkir antar-jemput (sekali per pesanan)</span><span>{{ Format::rupiah($shippingFee) }}</span></div>
</div>
<div class="between" style="padding: 18px 24px; background: var(--soft); align-items: baseline">
<span class="small text-subtle" style="font-size: 14px">Total dibayar di awal</span>
<span class="poppins" style="font-size: 26px; font-weight: 700">{{ Format::rupiah($total) }}</span>
</div>
<div class="stack" style="padding: 20px 24px; gap: 14px">
<p class="small text-muted" style="margin: 0; line-height: 1.55">Masa sewa 120 jam mulai saat semua unit diterima. Kalau terlambat: <span style="color: #fff">{{ Format::rupiah($dailyFineTotal) }}</span> per 24 jam.</p>
<p class="small text-muted" style="margin: 0; line-height: 1.55">Bayar tunai atau transfer ke admin. Status berubah jadi Lunas setelah admin mengonfirmasi.</p>

@if ($afterCheckout > $maxUnits)
<div class="alert" role="status" style="padding: 14px 16px; font-size: 13px">
<div><strong>Kuota tidak cukup.</strong>Kamu sedang menahan {{ $held }} dari {{ $maxUnits }} unit. Kurangi isi keranjang atau tunggu pesanan lain selesai.</div>
</div>
@endif

<label class="check soft-box" for="c-agree" style="padding: 12px; color: var(--muted)">
<input id="c-agree" name="agree" type="checkbox" value="1" required @checked(old('agree')) @error('agree') aria-invalid="true" aria-describedby="agree-error" @enderror>
<span>Data pesanan sudah benar. Kuota saya akan terpakai {{ $afterCheckout }}/{{ $maxUnits }} unit.</span>
</label>
<x-field-error name="agree" />
<button type="submit" class="btn btn-primary btn-block" style="min-height: 52px">Buat pesanan</button>
<p class="note">Unit belum ditahan sebelum pesanan dibuat. Total dihitung ulang oleh sistem saat kamu menekan tombol ini.</p>
</div>
</aside>
</form>

@foreach ($units as $unit)
<form method="POST" action="{{ route('cart.destroy', $unit) }}" id="remove-{{ $unit->code }}" hidden>
@csrf
@method('DELETE')
</form>
@endforeach
@endif
@endsection
