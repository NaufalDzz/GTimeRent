@extends('layouts.app')

@section('title', 'Katalog')

@php
    use App\Domain\Catalog\UnitKind;
    use App\Support\Format;
@endphp

@section('content')
<div class="between" style="align-items: flex-end; flex-wrap: wrap; gap: 24px">
<div>
<div class="eyebrow">KATALOG · {{ $units->count() }} DARI {{ $total }} PAKET</div>
<h1 class="h1-mono">Pilih maksimal dua unit.<br><span>Setiap kode adalah satu paket fisik.</span></h1>
</div>
<form method="GET" action="{{ route('catalog.index') }}" role="search" style="position: relative; flex: 0 1 380px; min-width: 260px">
<label for="cari" class="sr-only">Cari nama paket atau kode unit</label>
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94808A" stroke-width="2" stroke-linecap="round" aria-hidden="true" style="position: absolute; left: 16px; top: 15px"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
@if ($category !== '')
<input type="hidden" name="kategori" value="{{ $category }}">
@endif
<input id="cari" name="q" type="search" class="input" value="{{ $query }}" placeholder="Cari nama paket, mis. PC Mid" maxlength="100" style="border-radius: 999px; padding-left: 44px">
</form>
</div>

<nav aria-label="Filter kategori" class="chips" style="margin-top: 32px; gap: 8px">
<a href="{{ route('catalog.index', array_filter(['q' => $query])) }}" class="filter-pill" @if ($category === '') aria-current="true" @endif>Semua</a>
@foreach ($categories as $name)
<a href="{{ route('catalog.index', array_filter(['q' => $query, 'kategori' => $name])) }}" class="filter-pill" @if ($category === $name) aria-current="true" @endif>{{ $name }}</a>
@endforeach
</nav>

<div class="fullset-banner" style="margin-top: 20px">
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F4B3D3" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1.5"></rect><rect x="13" y="13" width="8" height="8" rx="1.5"></rect><path d="M13 7h5M16 4v6M7 13v5"></path></svg>
<p style="margin: 0; flex: 1 1 320px; font-size: 14px; line-height: 1.5; color: var(--muted)"><strong style="color: #fff">Mau fullset?</strong> Buka detail paket PC + monitor, lalu pilih satu paket peripheral. Ongkir tetap dihitung sekali.</p>
@can('member')
<a href="{{ route('checkout.create') }}" class="btn btn-sm">Lihat keranjang ({{ $navCartCount }})</a>
@endcan
</div>

@if ($units->isEmpty())
<div class="empty" style="margin-top: 32px">Tidak ada paket yang cocok. Coba kata lain atau <a href="{{ route('catalog.index') }}" class="link-btn">hapus filter</a>.</div>
@else
<div class="unit-grid" style="margin-top: 32px">
@foreach ($units as $unit)
<article class="unit-card">
<div class="unit-tile {{ $unit->kind === UnitKind::PcMonitor ? 'is-pc' : 'is-per' }}">
<x-unit-icon :kind="$unit->kind" size="76" stroke="1" style="color: #F4B3D3" />
<span class="unit-code">{{ $unit->code }}</span>
<span class="badge tone-{{ $unit->availabilityTone() }}" style="position: absolute; top: 14px; right: 14px">{{ $unit->availabilityLabel() }}</span>
</div>
<div class="unit-body">
<div class="label-mono">{{ mb_strtoupper($unit->kind->label()) }}</div>
<h2 class="poppins" style="margin: 0; font-size: 19px; font-weight: 600">{{ $unit->name }}</h2>
<p class="small text-subtle" style="margin: 0; line-height: 1.55">{{ implode(' · ', $unit->specsList()) }}</p>
<div class="chips">
@foreach ($unit->categories as $cat)
<span class="chip">{{ $cat->name }}</span>
@endforeach
</div>
<div class="unit-foot">
<div>
<div class="price">{{ Format::rupiah($unit->package_price) }} <span class="tiny text-dim" style="font-family: Inter, sans-serif; font-weight: 400">/ maks. 5 hari</span></div>
<div class="tiny text-dim" style="margin-top: 2px">Denda {{ Format::rupiah($unit->daily_fine) }} per hari</div>
</div>
@if ($unit->isAvailable())
<a href="{{ route('catalog.show', $unit) }}" class="btn btn-primary btn-sm">Lihat detail<span class="sr-only"> {{ $unit->code }}</span></a>
@else
<a href="{{ route('catalog.show', $unit) }}" class="btn btn-sm" aria-disabled="true">Belum bisa dipesan<span class="sr-only">, lihat detail {{ $unit->code }}</span></a>
@endif
</div>
</div>
</article>
@endforeach
</div>
@endif
@endsection
