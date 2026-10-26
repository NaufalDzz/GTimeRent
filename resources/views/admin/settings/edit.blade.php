@extends('layouts.admin')

@section('title', 'Ongkir')

@section('content')
<div class="eyebrow">Pengaturan operasional</div>
<h1 class="h1-admin">Ongkir antar-jemput</h1>

<div class="row" style="margin-top: 28px; gap: 24px">
<section class="card shipping-hero" style="flex: 1 1 320px; min-width: 0" aria-labelledby="current-fee">
<div class="label-mono">Tarif berlaku sekarang</div>
<div id="current-fee" class="poppins" style="margin-top: 10px; font-size: 44px; font-weight: 700; line-height: 1.1">{{ \App\Support\Format::rupiah($setting->flat_shipping_fee) }}</div>
<div class="small text-subtle" style="margin-top: 6px">per pesanan · flat untuk semua area demo</div>
<div class="chips" style="margin-top: 16px">
@foreach ($serviceAreas as $area)
<span class="chip">{{ $area }}</span>
@endforeach
</div>
<p class="small text-muted" style="margin: 18px 0 0; line-height: 1.6">{{ $setting->service_area_text }}</p>
</section>

<form method="POST" action="{{ route('admin.settings.update') }}" aria-label="Ubah ongkir" class="aside-card stack" style="flex: 1 1 380px; min-width: 0" novalidate>
@csrf
@method('PUT')
<div class="field">
<label for="o-fee">Tarif baru (Rp)</label>
<input id="o-fee" name="flat_shipping_fee" type="number" min="0" max="1000000000" step="1" inputmode="numeric" class="input" value="{{ old('flat_shipping_fee', $setting->flat_shipping_fee) }}" required @error('flat_shipping_fee') aria-invalid="true" aria-describedby="flat_shipping_fee-error" @enderror>
<x-field-error name="flat_shipping_fee" />
</div>
@if ($previewSubtotal !== null)
<section class="shipping-preview stack-sm" aria-labelledby="shipping-preview-title" aria-live="polite" data-shipping-preview data-subtotal="{{ $previewSubtotal }}">
<h2 id="shipping-preview-title" class="label-mono">Pratinjau checkout berikutnya</h2>
<div class="sum-row"><span>Fullset Mid (2 unit)</span><span>{{ \App\Support\Format::rupiah($previewSubtotal) }}</span></div>
<div class="sum-row"><span>Ongkir (sekali, bukan per unit)</span><span data-preview-fee>{{ \App\Support\Format::rupiah($setting->flat_shipping_fee) }}</span></div>
<div class="sum-total"><span>Tagihan awal</span><strong data-preview-total>{{ \App\Support\Format::rupiah($previewSubtotal + $setting->flat_shipping_fee) }}</strong></div>
</section>
@else
<p class="note">Pratinjau fullset tersedia setelah PC-MID-001 dan PER-A2-001 ada di katalog.</p>
@endif
<div class="field">
<label for="o-area">Teks area layanan</label>
<textarea id="o-area" name="service_area_text" rows="3" class="input small" maxlength="1000" required @error('service_area_text') aria-invalid="true" aria-describedby="service_area_text-error" @enderror>{{ old('service_area_text', $setting->service_area_text) }}</textarea>
<p class="field-hint">Ditampilkan ke anggota di katalog dan checkout.</p>
<x-field-error name="service_area_text" />
</div>
<ul class="note" style="padding-left: 18px">
<li>Hanya berlaku untuk checkout setelah disimpan.</li>
<li>Pesanan yang sudah ada tetap memakai ongkir snapshot.</li>
<li>Ditagih sekali per pesanan, tidak dikalikan jumlah unit, dan tidak ditagih lagi saat penjemputan.</li>
</ul>
<button type="submit" class="btn btn-primary">Simpan tarif</button>
</form>
</div>
<section class="stack" style="margin-top: 32px" aria-labelledby="shipping-history-title">
<h2 id="shipping-history-title" class="h2" style="font-size: 16px">Riwayat perubahan</h2>
<div class="table-wrap">
<table class="table">
<thead><tr><th scope="col">Tarif baru</th><th scope="col">Perubahan / oleh</th><th scope="col">Waktu (WIB)</th></tr></thead>
<tbody>
@foreach ($history as $change)
<tr><td class="mono">{{ \App\Support\Format::rupiah($change->new_fee) }}</td><td>Dari {{ \App\Support\Format::rupiah($change->previous_fee) }} · {{ $change->admin_name }}</td><td class="small">{{ \App\Support\Format::wib(\Carbon\CarbonImmutable::parse($change->created_at, 'UTC')) }}</td></tr>
@endforeach
<tr><td class="mono">{{ \App\Support\Format::rupiah($history->last()?->previous_fee ?? $setting->flat_shipping_fee) }}</td><td class="text-subtle">Nilai awal</td><td class="small">{{ \App\Support\Format::wib($setting->created_at) }}</td></tr>
</tbody>
</table>
</div>
</section>
@endsection
