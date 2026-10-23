@extends('layouts.admin')

@section('title', 'Ongkir')

@section('content')
<div class="eyebrow">Pengaturan operasional</div>
<h1 class="h1-admin">Ongkir antar-jemput</h1>

<div class="row" style="margin-top: 28px; gap: 24px">
<section class="card" style="flex: 1 1 320px; min-width: 0; background: linear-gradient(140deg, #2a0e1c 0%, #130c10 70%)" aria-labelledby="current-fee">
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
@endsection
