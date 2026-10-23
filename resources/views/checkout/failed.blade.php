@extends('layouts.app')

@section('title', 'Checkout ditolak')

@php
    use App\Support\Format;

    $title = match (true) {
        $isQuotaFull => 'Kuota unit kamu sudah penuh',
        $hasUnavailableUnit => 'Ada unit yang sudah tidak tersedia',
        default => 'Checkout belum bisa diproses',
    };
@endphp

@section('content')
<ol aria-label="Langkah checkout" class="hstack small" style="list-style: none; margin: 0; padding: 0; gap: 8px">
<li class="badge tone-muted" style="padding: 8px 14px">1 · Keranjang</li>
<li aria-current="step" class="badge tone-danger" style="padding: 8px 14px">2 · Data pengiriman · gagal</li>
<li class="badge" style="padding: 8px 14px; border: 1px solid var(--card-line); color: var(--faint)">3 · Pesanan dibuat</li>
</ol>

<div class="alert" role="alert" style="margin-top: 24px; padding: 24px; flex-wrap: wrap; gap: 18px">
<span class="alert-icon" aria-hidden="true" style="width: 48px; height: 48px; border-radius: 14px"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l10 18H2z"></path><path d="M12 10v5M12 18h.01"></path></svg></span>
<div style="flex: 1 1 400px; min-width: 0">
<h1 class="poppins" style="margin: 0; font-weight: 700; font-size: clamp(24px, 2.6vw, 32px); line-height: 1.2; color: #fff">{{ $title }}</h1>
<p style="margin: 8px 0 0; font-size: 15px; line-height: 1.6; color: #e7d6df">{{ $reason }}</p>
<div class="hstack small" style="margin-top: 14px; display: inline-flex; gap: 8px; padding: 8px 12px; border-radius: 10px; background: rgba(10, 6, 8, 0.6); color: var(--muted)">
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9be7c0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5 9-10"></path></svg>
Tidak ada unit yang ditahan dari percobaan ini. Belum ada pesanan atau tagihan yang dibuat.
</div>
</div>
</div>

<div class="row" style="margin-top: 28px; gap: 28px">
<section class="card" aria-labelledby="h-items" style="flex: 999 1 520px; min-width: 0">
<h2 id="h-items" class="h2" style="font-size: 18px">Isi keranjang</h2>
@if ($units->isEmpty())
<p class="small text-subtle" style="margin: 16px 0 0">Keranjang kosong.</p>
@else
<ul class="stack-sm" style="list-style: none; margin: 16px 0 0; padding: 0; gap: 12px">
@foreach ($units as $unit)
@php
    [$statusLabel, $tone] = match (true) {
        ! $unit->isAvailable() => ['Tidak tersedia · '.$unit->availabilityLabel(), 'danger'],
        $isQuotaFull => ['Melebihi kuota', 'muted'],
        default => ['Tersedia', 'success'],
    };
@endphp
<li class="item hstack" style="gap: 16px; padding: 16px; border-radius: 14px; @if ($tone === 'danger') border-color: #6b2232; background: #1e0c12; @endif">
<span class="icon-tile" aria-hidden="true"><x-unit-icon :kind="$unit->kind" size="26" stroke="1.3" /></span>
<div style="flex: 1 1 200px; min-width: 0">
<div style="font-weight: 500">{{ $unit->name }}</div>
<div class="mono tiny text-dim">{{ $unit->code }} · {{ Format::rupiah($unit->package_price) }}</div>
</div>
<span class="badge tone-{{ $tone }}">{{ $statusLabel }}</span>
</li>
@endforeach
</ul>
@endif
<p class="small text-subtle" style="margin: 14px 0 0; line-height: 1.55">Data pengiriman yang sudah kamu isi tetap tersimpan saat kembali ke checkout.</p>
</section>

<aside class="stack" style="flex: 1 1 320px; min-width: 0; gap: 12px">
<div class="soft-box" style="padding: 20px; color: var(--muted)">
<div class="label-mono" style="color: var(--subtle)">Langkah berikutnya</div>
<p style="margin: 8px 0 0">
@if ($isQuotaFull)
Kamu sedang menahan {{ $held }}/{{ $maxUnits }} unit. Kuota kembali setelah pesanan @if ($holdingRental)<span class="mono">{{ $holdingRental->number }}</span>@endif selesai dikembalikan atau dibatalkan oleh admin, atau kurangi isi keranjang.
@elseif ($hasUnavailableUnit)
Hapus unit yang tidak tersedia dari keranjang, lalu pilih unit lain dari katalog atau lanjutkan checkout dengan unit yang tersisa.
@else
Periksa kembali isi keranjang dan data pengiriman, lalu coba checkout lagi.
@endif
</p>
</div>
@if ($isQuotaFull && $holdingRental)
<a href="{{ route('rentals.show', $holdingRental) }}" class="btn btn-primary">Lihat {{ $holdingRental->number }}</a>
<a href="{{ route('checkout.create') }}" class="btn">Kembali ke checkout</a>
@else
<a href="{{ route('checkout.create') }}" class="btn btn-primary">Kembali ke checkout</a>
<a href="{{ route('catalog.index') }}" class="btn">Ganti unit di katalog</a>
@endif
</aside>
</div>
@endsection
