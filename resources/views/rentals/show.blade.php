@extends('layouts.app')

@section('title', 'Pesanan '.$rental->number)

@php
    use App\Domain\Rental\RentalStatus;
    use App\Support\Format;

    $isPending = $rental->status === RentalStatus::PendingPayment;
    $isActive = $rental->status === RentalStatus::Active;
    $isOverdue = $rental->isOverdue();
    $dailyFineTotal = (int) $rental->items->sum('daily_fine_snapshot');
    $remainingSeconds = $isActive && $rental->due_at ? max(0, $rental->due_at->getTimestamp() - now()->getTimestamp()) : 0;
    $help = match ($rental->status) {
        RentalStatus::PendingPayment => 'Ingin membatalkan pesanan? Pembatalan dilakukan oleh admin.',
        RentalStatus::Active => 'Sudah selesai main? Hubungi admin untuk jadwal penjemputan dan pengembalian.',
        RentalStatus::Returned => 'Pelunasan denda, bila ada, dilakukan langsung ke admin.',
        RentalStatus::Cancelled => 'Ada pertanyaan soal pembatalan? Admin siap membantu.',
        default => 'Ada pertanyaan soal pengiriman? Hubungi admin.',
    };
@endphp

@section('content')
@if ($isNew && $isPending)
<section class="detail-hero" aria-labelledby="h-rental" style="min-height: 0; padding: 36px; display: block; border-radius: 24px">
<div class="between" style="position: relative; gap: 24px; align-items: flex-end">
<div style="min-width: 0">
<span class="badge tone-{{ $rental->statusTone() }}" style="font-size: 13px; padding: 6px 12px">{{ $rental->statusLabel() }}</span>
<h1 id="h-rental" class="poppins" style="margin: 16px 0 0; font-weight: 700; font-size: clamp(30px, 3.6vw, 46px); line-height: 1.1; letter-spacing: -0.02em">Pesanan dibuat.<br>Unit sudah kami tahan.</h1>
<p style="margin: 12px 0 0; max-width: 560px; line-height: 1.6; color: #e7d6df">Selesaikan pembayaran ke admin. Setelah admin mengonfirmasi, status berubah jadi Lunas dan unit disiapkan untuk dikirim.</p>
</div>
<div style="padding: 16px 20px; border-radius: 16px; background: rgba(10, 6, 8, 0.65); border: 1px solid rgba(255, 255, 255, 0.12)">
<div class="label-mono" style="color: var(--muted)">Kode pesanan</div>
<div class="mono" style="margin-top: 6px; font-size: 18px">{{ $rental->number }}</div>
<div class="tiny text-muted" style="margin-top: 4px">Dibuat {{ Format::wib($rental->created_at) }}</div>
</div>
</div>
</section>
@else
<nav aria-label="Breadcrumb" class="hstack small text-dim" style="gap: 8px">
<a href="{{ route('rentals.index') }}" class="text-subtle" style="text-decoration: none">Pesanan saya</a>
<span aria-hidden="true">/</span>
<span aria-current="page" style="color: var(--text)">{{ $rental->number }}</span>
</nav>
<div class="between" style="margin-top: 20px; align-items: flex-start">
<div>
<div class="label-mono">Nomor transaksi</div>
<h1 id="h-rental" class="poppins" style="margin: 6px 0 0; font-weight: 600; font-size: 26px">{{ $rental->number }}</h1>
<div class="small text-subtle" style="margin-top: 4px">Dibuat {{ Format::wib($rental->created_at) }}</div>
</div>
<span class="badge tone-{{ $rental->statusTone() }}" style="font-size: 13px; padding: 8px 14px">{{ $rental->statusLabel() }}</span>
</div>
@endif

<div class="row" style="margin-top: 28px; gap: 28px">
<div class="stack-lg" style="flex: 999 1 520px; min-width: 0; gap: 20px">

@if ($isActive)
<section class="fullset-banner" aria-label="Masa sewa" style="justify-content: space-between; align-items: flex-end; padding: 24px; border-radius: 18px">
<div>
@if ($isOverdue)
<div class="label-mono" style="color: #ffb3be">Terlambat</div>
<div class="mono" style="margin-top: 8px; font-weight: 300; font-size: 44px; line-height: 1; color: #ffb3be">{{ $rental->estimatedLateDays() }} hari</div>
<div class="small text-muted" style="margin-top: 10px">Lewat jatuh tempo <span style="color: #fff; font-weight: 600">{{ Format::wib($rental->due_at, 'l, d M Y · H.i') }}</span>. Dihitung per 24 jam yang dimulai.</div>
@else
<div class="label-mono" style="color: var(--muted)">Sisa masa sewa</div>
<div class="mono" style="margin-top: 8px; font-weight: 300; font-size: 44px; line-height: 1">{{ intdiv($remainingSeconds, 86400) }} hari {{ intdiv($remainingSeconds % 86400, 3600) }} jam</div>
<div class="small text-muted" style="margin-top: 10px">Jatuh tempo <span style="color: #fff; font-weight: 600">{{ Format::wib($rental->due_at, 'l, d M Y · H.i') }}</span></div>
@endif
</div>
<div style="text-align: right">
<div class="tiny text-muted">Estimasi denda saat ini (belum final)</div>
<div class="poppins" style="margin-top: 4px; font-weight: 700; font-size: 24px">{{ Format::rupiah($rental->estimatedFine()) }}</div>
<div class="tiny text-muted" style="margin-top: 2px">{{ Format::rupiah($dailyFineTotal) }} per 24 jam jika terlambat</div>
</div>
</section>
@endif

@if ($isPending)
<section class="card" aria-labelledby="h-pay">
<h2 id="h-pay" class="h2" style="font-size: 18px">Cara bayar</h2>
<div class="row" style="margin-top: 16px; gap: 20px">
<div style="flex: 1 1 240px; min-width: 0">
<h3 class="label-mono" style="margin: 0; color: var(--muted)">Transfer bank</h3>
<ol class="stack-sm small text-muted" style="margin: 12px 0 0; padding-left: 18px; line-height: 1.55">
<li>Transfer tepat <b class="mono" style="color: var(--text); font-weight: 500">{{ Format::rupiah($rental->initial_total) }}</b> ke rekening yang diberikan admin.</li>
<li>Tulis <span class="mono" style="color: var(--text)">{{ $rental->number }}</span> di berita transfer.</li>
<li>Kirim bukti transfer ke admin. Admin mengonfirmasi secara manual.</li>
</ol>
</div>
<div style="flex: 1 1 240px; min-width: 0">
<h3 class="label-mono" style="margin: 0; color: var(--muted)">Tunai ke admin</h3>
<ol class="stack-sm small text-muted" style="margin: 12px 0 0; padding-left: 18px; line-height: 1.55">
<li>Hubungi admin untuk janji temu, sebutkan kode pesanan.</li>
<li>Bayar tunai <b class="mono" style="color: var(--text); font-weight: 500">{{ Format::rupiah($rental->initial_total) }}</b>. Admin mencatat pembayaran di sistem.</li>
</ol>
</div>
</div>
<div class="soft-box hstack" style="margin-top: 20px; gap: 10px; color: var(--muted)">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f4b3d3" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20l1.5-4A8 8 0 1 1 8 18.5z"></path></svg>
<span>Hubungi admin: <span style="color: var(--text)">{{ $adminContact }}</span></span>
</div>
</section>
@endif

<section class="card" aria-labelledby="h-steps">
<h2 id="h-steps" class="h2" style="font-size: 18px">Perjalanan pesanan</h2>
<ol class="timeline" style="margin-top: 18px">
@foreach ($steps as $step)
<li class="{{ $step['state'] }}">
<span class="mark" aria-hidden="true"></span>
<div>
<div class="small" style="font-size: 14px; font-weight: {{ $step['state'] === 'todo' ? 400 : 600 }}; color: {{ $step['state'] === 'todo' ? 'var(--muted)' : 'var(--text)' }}">{{ $step['label'] }}<span class="sr-only"> ({{ ['done' => 'selesai', 'current' => 'saat ini', 'stopped' => 'dihentikan', 'todo' => 'belum'][$step['state']] }})</span></div>
<div class="mono tiny text-dim">{{ $step['at'] ? Format::wib($step['at']) : 'Belum' }}</div>
</div>
</li>
@endforeach
</ol>
@if ($rental->status === RentalStatus::Cancelled)
<div class="soft-box" style="margin-top: 4px; color: var(--muted)">Dibatalkan admin: <span style="color: #fff">{{ $rental->cancel_reason }}</span>. Unit sudah dilepas kembali ke katalog.</div>
@endif
</section>

<section class="card" aria-labelledby="h-items">
<h2 id="h-items" class="h2" style="font-size: 18px">Unit &amp; tagihan</h2>
<ul style="list-style: none; margin: 12px 0 0; padding: 0; border-top: 1px solid var(--rule)">
@foreach ($rental->items as $item)
<li class="between" style="align-items: flex-start; padding: 14px 0; border-bottom: 1px solid var(--rule)">
<div style="min-width: 0; flex: 1 1 260px">
<div class="code" style="font-size: 11px">{{ $item->code_snapshot }}</div>
<div style="margin-top: 2px; font-size: 14px">{{ $item->name_snapshot }}</div>
@if ($item->contentsList() !== [])
<ul class="tiny text-dim" style="margin: 6px 0 0; padding-left: 16px">
@foreach ($item->contentsList() as $line)
<li>{{ $line }}</li>
@endforeach
</ul>
@endif
</div>
<div style="text-align: right">
<div style="font-size: 14px">{{ Format::rupiah($item->package_price_snapshot) }}</div>
<div class="tiny text-dim" style="margin-top: 2px">denda {{ Format::rupiah($item->daily_fine_snapshot) }}/hari</div>
@if ($rental->status === RentalStatus::Returned && $item->fine_amount > 0)
<div class="tiny" style="margin-top: 2px; color: #ff9aa8">denda {{ $item->late_days }} hari: {{ Format::rupiah($item->fine_amount) }}</div>
@endif
</div>
</li>
@endforeach
<li class="sum-row" style="padding: 14px 0; border-bottom: 1px solid var(--rule)"><span>Ongkir antar-jemput (sekali)</span><span>{{ Format::rupiah($rental->shipping_fee_snapshot) }}</span></li>
</ul>
<div class="sum-total" style="margin-top: 0; border-top: 0">
<span>Tagihan awal · {{ $rental->payment_method ? 'lunas via '.mb_strtolower($rental->payment_method->label()) : ($rental->status === RentalStatus::Cancelled ? 'tidak dibayar' : 'belum dibayar') }}</span>
<strong>{{ Format::rupiah($rental->initial_total) }}</strong>
</div>

@if ($rental->status === RentalStatus::Returned)
<div class="alert" style="margin-top: 16px; justify-content: space-between; align-items: center; flex-wrap: wrap">
<div>
<strong>Denda final</strong>
<span class="tiny text-muted">Dikembalikan {{ Format::wib($rental->returned_at) }}. Terpisah dari tagihan awal.</span>
</div>
<span class="poppins" style="font-weight: 700; font-size: 20px">{{ Format::rupiah($rental->fineTotal()) }}</span>
</div>
@if ($rental->return_notes)
<p class="small text-subtle" style="margin: 12px 0 0">Catatan pengembalian: {{ $rental->return_notes }}</p>
@endif
@endif
</section>
</div>

<aside class="stack" style="flex: 1 1 320px; min-width: 0; gap: 20px">
<section class="card" aria-labelledby="h-ship">
<h2 id="h-ship" class="h2" style="font-size: 18px">Pengiriman</h2>
<dl class="stack-sm small" style="margin: 14px 0 0">
<div><dt class="label-mono">Penerima</dt><dd style="margin: 2px 0 0">{{ $rental->recipient_name }}</dd></div>
<div><dt class="label-mono">Telepon</dt><dd class="mono" style="margin: 2px 0 0">{{ $rental->recipient_phone }}</dd></div>
<div><dt class="label-mono">Alamat</dt><dd style="margin: 2px 0 0; white-space: pre-line">{{ $rental->delivery_address }}</dd></div>
<div><dt class="label-mono">Area layanan</dt><dd style="margin: 2px 0 0">{{ $rental->service_area }}</dd></div>
</dl>
<div class="sum-total">
<span>Total awal</span>
<strong>{{ Format::rupiah($rental->initial_total) }}</strong>
</div>
</section>

@if ($rental->status->holdsUnits())
<section class="soft-box stack-sm" aria-label="Info penahanan" style="padding: 20px; color: var(--muted)">
<div class="between" style="align-items: baseline"><span class="label-mono" style="color: var(--muted)">Kuota ditahan</span><span class="mono" style="font-size: 18px; color: var(--text)">{{ $held }}/{{ $maxUnits }}</span></div>
<div class="meter" role="img" aria-label="{{ $held }} dari {{ $maxUnits }} unit ditahan"><span style="width: {{ min(100, (int) round($held / max(1, $maxUnits) * 100)) }}%"></span></div>
<p style="margin: 0">Pesanan tidak batal otomatis. Kalau ingin membatalkan, hubungi admin. Unit di pesanan ini tetap ditahan sampai dikembalikan atau dibatalkan.</p>
</section>
@endif

<section class="soft-box stack-sm" aria-labelledby="h-help" style="padding: 20px; color: var(--muted)">
<h2 id="h-help" class="label-mono" style="margin: 0; color: var(--muted)">Hubungi admin</h2>
<p style="margin: 0">{{ $help }}</p>
<p style="margin: 0; color: var(--text)">{{ $adminContact }}</p>
</section>

<a href="{{ route('rentals.index') }}" class="btn">Lihat semua pesanan saya</a>
</aside>
</div>
@endsection
