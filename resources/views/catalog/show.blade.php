@extends('layouts.app')

@section('title', $unit->code.' · '.$unit->name)

@php
    use App\Domain\Catalog\UnitKind;
    use App\Support\Format;

    $specs = $unit->specsList();
    $inCart = auth()->user()?->isMember() && app(\App\Support\Cart::class)->has($unit->id);
@endphp

@section('content')
<nav aria-label="Breadcrumb" class="hstack small text-dim" style="gap: 8px">
<a href="{{ route('catalog.index') }}" class="text-subtle" style="text-decoration: none">Katalog</a>
<span aria-hidden="true">/</span>
<span>{{ $unit->kind->label() }}</span>
<span aria-hidden="true">/</span>
<span aria-current="page" style="color: var(--text)">{{ $unit->code }}</span>
</nav>

<div class="row" style="margin-top: 24px; gap: 32px; align-items: flex-start">
<div class="detail-hero">
<x-unit-icon :kind="$unit->kind" size="220" stroke="0.6" style="position: relative; color: #F4B3D3" />
<span class="unit-code" style="top: 20px; left: 20px; font-size: 12px">{{ $unit->code }}</span>
<div class="chips" style="position: absolute; left: 20px; bottom: 20px; gap: 8px">
@foreach (array_slice($specs, 0, 3) as $spec)
<span class="pill">{{ $spec }}</span>
@endforeach
</div>
</div>

<div class="stack" style="flex: 1 1 400px; min-width: 0; gap: 20px">
<div>
<div class="hstack" style="flex-wrap: wrap; gap: 10px">
<span class="label-mono" style="font-size: 12px">{{ mb_strtoupper($unit->kind->label()) }} · 1 UNIT</span>
<span class="badge tone-{{ $unit->availabilityTone() }}">{{ $unit->availabilityLabel() }}</span>
</div>
<h1 class="poppins" style="margin: 12px 0 0; font-weight: 700; font-size: clamp(30px, 3.4vw, 40px); letter-spacing: -0.03em; line-height: 1.1">{{ $unit->name }}</h1>
<div class="chips" style="margin-top: 12px">
@foreach ($unit->categories as $cat)
<span class="chip" style="font-size: 12px">{{ $cat->name }}</span>
@endforeach
</div>
</div>

<div class="row" style="gap: 12px">
<div class="stat">
<div class="label-mono">Harga paket</div>
<div class="stat-n">{{ Format::rupiah($unit->package_price) }}</div>
<div class="tiny text-subtle" style="margin-top: 2px">Tetap, untuk pemakaian sampai 5 hari</div>
</div>
<div class="stat">
<div class="label-mono">Denda / hari</div>
<div class="stat-n">{{ Format::rupiah($unit->daily_fine) }}</div>
<div class="tiny text-subtle" style="margin-top: 2px">Per 24 jam lewat jatuh tempo</div>
</div>
</div>

@if (! $unit->isAvailable())
<div class="alert" role="status">
<span class="alert-icon" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v5M12 16h.01"></path></svg></span>
<div><strong>Unit ini belum bisa dipesan.</strong>{{ $unit->condition_status === \App\Domain\Catalog\UnitCondition::Maintenance ? 'Unit sedang maintenance.' : 'Unit sedang ditahan pesanan lain sampai dikembalikan atau dibatalkan.' }} Pilih kode lain dengan nama yang sama bila ada.</div>
</div>
<a href="{{ route('catalog.index', ['q' => $unit->name]) }}" class="btn">Cari unit serupa</a>
@else
<form method="POST" action="{{ route('cart.store') }}" class="stack" style="gap: 20px" id="add-form">
@csrf
<input type="hidden" name="units[]" value="{{ $unit->code }}">

@if ($partners->isNotEmpty())
<fieldset class="aside-card" style="margin: 0; padding: 18px">
<div class="between">
<legend style="float: left; padding: 0; font-size: 14px; font-weight: 600">Jadikan fullset</legend>
<span class="mono tiny text-dim">OPSIONAL · +1 UNIT</span>
</div>
<div class="stack-sm" style="clear: both; margin-top: 12px; gap: 8px">
<label class="radio-card">
<span class="hstack" style="gap: 12px"><input type="radio" name="units[]" value="" data-price="0" checked><span class="dot" aria-hidden="true"></span><span>Tanpa {{ $unit->kind === UnitKind::PcMonitor ? 'peripheral' : 'PC + monitor' }}</span></span>
<span class="small text-muted">+Rp0</span>
</label>
@foreach ($partners as $partner)
<label class="radio-card">
<span class="hstack" style="gap: 12px"><input type="radio" name="units[]" value="{{ $partner->code }}" data-price="{{ $partner->package_price }}" @checked(old('units.1') === $partner->code)><span class="dot" aria-hidden="true"></span><span><span style="display: block; font-size: 14px">{{ $partner->name }}</span><span class="mono" style="display: block; font-size: 11px; color: var(--dim)">{{ $partner->code }}</span></span></span>
<span class="small text-muted">+{{ Format::rupiah($partner->package_price) }}</span>
</label>
@endforeach
</div>
</fieldset>
@endif

<div class="soft-box" style="padding: 18px; font-size: 14px">
<div class="sum-row"><span id="line-label">1 paket · {{ $unit->code }}</span><span id="line-subtotal">{{ Format::rupiah($unit->package_price) }}</span></div>
<div class="sum-row" style="margin-top: 8px"><span>Ongkir antar-jemput (sekali)</span><span>{{ Format::rupiah($shippingFee) }}</span></div>
<div class="sum-total"><span>Estimasi tagihan awal</span><strong id="line-total">{{ Format::rupiah($unit->package_price + $shippingFee) }}</strong></div>
</div>

<div class="row" style="gap: 12px">
@auth
@can('member')
@if ($inCart)
<a href="{{ route('checkout.create') }}" class="btn btn-primary" style="flex: 1 1 200px; min-height: 52px">Sudah di keranjang · Checkout</a>
@else
<button type="submit" class="btn btn-primary" style="flex: 1 1 200px; min-height: 52px">Tambah ke keranjang</button>
@endif
@else
<span class="btn" aria-disabled="true" style="flex: 1 1 200px; min-height: 52px">Akun admin tidak memesan</span>
@endcan
@else
<a href="{{ route('login') }}" class="btn btn-primary" style="flex: 1 1 200px; min-height: 52px">Masuk untuk memesan</a>
@endauth
<a href="{{ route('catalog.index') }}" class="btn" style="min-height: 52px">Kembali</a>
</div>
<p class="note">Keranjang belum menahan stok. Unit baru ditahan untukmu ketika checkout berhasil.</p>
</form>
@endif
</div>
</div>

<div class="row" style="margin-top: 64px; gap: 32px; align-items: flex-start">
<section style="flex: 1 1 520px; min-width: 0" aria-labelledby="spec-title">
<h2 id="spec-title" class="eyebrow" style="margin: 0; font-weight: 400">Spesifikasi</h2>
<dl class="spec-list">
@foreach ($specs as $spec)
<div><dt class="mono">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</dt><dd>{{ $spec }}</dd></div>
@endforeach
</dl>
</section>

<section style="flex: 1 1 400px; min-width: 0" aria-labelledby="contents-title">
<h2 id="contents-title" class="eyebrow" style="margin: 0; font-weight: 400">Isi paket tetap</h2>
<ul class="stack-sm" style="list-style: none; margin: 20px 0 0; padding: 0; gap: 10px">
@foreach ($unit->contentsList() as $line)
@php [$part, $desc] = array_pad(explode(' - ', $line, 2), 2, null); @endphp
<li class="item hstack" style="gap: 14px; align-items: baseline">
@if ($desc !== null)
<span class="code" style="min-width: 136px">{{ $part }}</span><span class="small text-muted" style="font-size: 14px">{{ ucfirst($desc) }}</span>
@else
<span class="small text-muted" style="font-size: 14px">{{ $line }}</span>
@endif
</li>
@endforeach
</ul>
<div class="soft-box" style="margin-top: 16px">Kelengkapan diperiksa admin saat barang kembali. Semua unit dalam satu pesanan dikirim dan dikembalikan bersamaan.</div>
</section>
</div>

@if ($partners->isNotEmpty())
<script>
(() => {
    const form = document.getElementById('add-form');
    if (!form) return;
    const base = {{ $unit->package_price }}, fee = {{ $shippingFee }};
    const rupiah = (n) => 'Rp' + n.toLocaleString('id-ID');
    form.addEventListener('change', () => {
        const picked = form.querySelector('input[type=radio]:checked');
        const extra = Number(picked?.dataset.price || 0);
        const two = extra > 0;
        document.getElementById('line-label').textContent = two ? '2 paket · {{ $unit->code }} + ' + picked.value : '1 paket · {{ $unit->code }}';
        document.getElementById('line-subtotal').textContent = rupiah(base + extra);
        document.getElementById('line-total').textContent = rupiah(base + extra + fee);
    });
})();
</script>
@endif
@endsection
