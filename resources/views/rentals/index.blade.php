@extends('layouts.app')

@section('title', 'Pesanan saya')

@php
    use App\Domain\Rental\RentalStatus;
    use App\Support\Format;
@endphp

@section('content')
<div class="between" style="align-items: flex-end">
<div>
<div class="eyebrow">Pesanan saya</div>
<h1 class="h1-mono">Semua sewa kamu,<br><span>dari bayar sampai kembali.</span></h1>
</div>
<div class="soft-box" style="min-width: 240px">
<div class="between" style="align-items: baseline">
<span class="label-mono" style="color: var(--muted)">Unit ditahan</span>
<span class="mono" style="font-size: 18px; color: var(--text)">{{ $held }}/{{ $maxUnits }}</span>
</div>
<div class="meter" style="margin-top: 10px" role="img" aria-label="{{ $held }} dari {{ $maxUnits }} unit ditahan"><span style="width: {{ min(100, (int) round($held / max(1, $maxUnits) * 100)) }}%"></span></div>
</div>
</div>

@if ($rentals->isEmpty())
<div class="empty" style="margin-top: 36px">
<p style="margin: 0; font-size: 16px; color: var(--text)">Belum ada pesanan.</p>
<p class="small" style="margin: 8px 0 20px">Pesanan yang kamu buat lewat checkout akan muncul di sini.</p>
<a href="{{ route('catalog.index') }}" class="btn btn-primary">Buka katalog</a>
</div>
@else
<ul class="stack-sm" style="list-style: none; margin: 36px 0 0; padding: 0; gap: 12px">
@foreach ($rentals as $rental)
<li>
<a href="{{ route('rentals.show', $rental) }}" class="card stack-sm" style="display: flex; padding: 18px 20px; color: var(--text); text-decoration: none">
<span class="between">
<span class="mono tiny text-muted">{{ $rental->number }}</span>
<span class="badge tone-{{ $rental->statusTone() }}">{{ $rental->statusLabel() }}</span>
</span>
<span style="font-size: 15px; font-weight: 500">{{ $rental->items->pluck('name_snapshot')->join(' + ') }}</span>
<span class="mono tiny text-dim">{{ $rental->items->pluck('code_snapshot')->join(' · ') }}</span>
<span class="between small text-dim">
<span>Dibuat {{ Format::wib($rental->created_at) }}</span>
<span>Tagihan awal <span style="color: var(--text)">{{ Format::rupiah($rental->initial_total) }}</span></span>
</span>
@if ($rental->status === RentalStatus::Active)
<span class="between small" style="padding-top: 10px; border-top: 1px solid var(--inner-line)">
<span class="text-muted">Jatuh tempo {{ Format::wib($rental->due_at) }}</span>
<span @if ($rental->isOverdue()) style="color: #ff9aa8" @else class="text-muted" @endif>Estimasi denda {{ Format::rupiah($rental->estimatedFine()) }}</span>
</span>
@elseif ($rental->status === RentalStatus::Returned)
<span class="between small" style="padding-top: 10px; border-top: 1px solid var(--inner-line)">
<span class="text-muted">Dikembalikan {{ Format::wib($rental->returned_at) }}</span>
<span @if ($rental->fineTotal() > 0) style="color: #ff9aa8" @else class="text-muted" @endif>Denda final {{ Format::rupiah($rental->fineTotal()) }}</span>
</span>
@endif
</a>
</li>
@endforeach
</ul>

@if ($rentals->hasPages())
<nav class="between small" aria-label="Halaman pesanan" style="margin-top: 20px">
@if ($rentals->onFirstPage())
<span class="text-dim">Sebelumnya</span>
@else
<a href="{{ $rentals->previousPageUrl() }}">Sebelumnya</a>
@endif
<span class="text-subtle">Halaman {{ $rentals->currentPage() }} dari {{ $rentals->lastPage() }}</span>
@if ($rentals->hasMorePages())
<a href="{{ $rentals->nextPageUrl() }}">Berikutnya</a>
@else
<span class="text-dim">Berikutnya</span>
@endif
</nav>
@endif
@endif
@endsection
