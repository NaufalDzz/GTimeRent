@extends('layouts.admin')

@section('title', 'Transaksi')

@php
    use App\Domain\Rental\RentalStatus;
    use App\Support\Format;

    $hasFilter = $filterParameters !== [];
@endphp

@section('content')
<div class="between" style="align-items: flex-end; gap: 20px">
<div>
<div class="eyebrow">{{ mb_strtoupper(now()->setTimezone(config('gtrent.display_timezone'))->locale('id')->translatedFormat('l, d M Y · H.i')) }} WIB</div>
<h1 class="h1-admin">Transaksi</h1>
</div>
<a href="{{ route('admin.rentals.print', $filterParameters) }}" class="btn btn-sm no-print"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4v-7h16v7h-2"></path><rect x="6" y="14" width="12" height="7"></rect></svg>Cetak sesuai filter</a>
</div>

<div class="hstack" style="margin-top: 28px; align-items: stretch">
@foreach ($kpis as $kpi)
<a href="{{ $kpi['status'] ? route('admin.rentals.index', ['status' => $kpi['status']]) : route('admin.rentals.index') }}" class="kpi" @if ($kpi['tone'] === 'danger' && $kpi['value'] > 0) style="border-color: #5a2a33; background: #1e0e12" @endif>
<div class="hstack small text-muted" style="gap: 8px"><span class="badge tone-{{ $kpi['tone'] }}" style="padding: 0; background: none" aria-hidden="true"></span>{{ $kpi['label'] }}</div>
<div class="kpi-n">{{ $kpi['value'] }}</div>
<div class="tiny text-dim" style="margin-top: 8px">{{ $kpi['hint'] }}</div>
</a>
@endforeach
</div>

<form method="GET" action="{{ route('admin.rentals.index') }}" class="stack" style="margin-top: 28px; gap: 16px" aria-label="Filter transaksi">
<div class="hstack" style="align-items: flex-end">
<div class="filter-field">
<label for="f-member">Anggota</label>
<select id="f-member" name="member" class="input">
<option value="">Semua anggota</option>
@foreach ($members as $member)
<option value="{{ $member->id }}" @selected($filters['member'] === $member->id)>{{ $member->name }}</option>
@endforeach
</select>
</div>
<div class="filter-field">
<label for="f-status">Status</label>
<select id="f-status" name="status" class="input">
<option value="">Semua status</option>
@foreach ($statusOptions as $value => $label)
<option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
@endforeach
</select>
</div>
<div class="filter-field">
<label for="f-from">Dibuat dari</label>
<input id="f-from" type="date" name="from" value="{{ $filters['from'] }}" class="input" style="color-scheme: dark">
</div>
<div class="filter-field">
<label for="f-to">Sampai</label>
<input id="f-to" type="date" name="to" value="{{ $filters['to'] }}" class="input" style="color-scheme: dark">
</div>
<button type="submit" class="btn btn-primary btn-sm" style="min-height: 44px">Terapkan</button>
@if ($hasFilter)
<a href="{{ route('admin.rentals.index') }}" class="btn btn-sm" style="min-height: 44px">Reset</a>
@endif
</div>
<x-field-error name="member" />
<x-field-error name="status" />
<x-field-error name="from" />
<x-field-error name="to" />
</form>

<div role="group" aria-label="Filter status cepat" class="hstack" style="margin-top: 16px; gap: 8px">
<a href="{{ route('admin.rentals.index', array_diff_key($filterParameters, ['status' => true])) }}" class="filter-pill" @if ($filters['status'] === null) aria-current="true" @endif>Semua</a>
@foreach ($statusOptions as $value => $label)
<a href="{{ route('admin.rentals.index', [...$filterParameters, 'status' => $value]) }}" class="filter-pill" @if ($filters['status'] === $value) aria-current="true" @endif>{{ $label }}</a>
@endforeach
</div>

@if ($rentals->isEmpty())
<div class="empty" style="margin-top: 16px">Tidak ada transaksi yang cocok dengan filter ini.</div>
@else
<div class="table-wrap" style="margin-top: 16px">
<table class="table" style="min-width: 880px">
<thead>
<tr>
<th scope="col">No. transaksi</th>
<th scope="col">Anggota</th>
<th scope="col">Unit</th>
<th scope="col">Dibuat</th>
<th scope="col">Status</th>
<th scope="col">Jatuh tempo / denda</th>
<th scope="col" style="text-align: right">Tagihan</th>
<th scope="col"><span class="sr-only">Aksi</span></th>
</tr>
</thead>
<tbody>
@foreach ($rentals as $rental)
@php $overdue = $rental->isOverdue(); @endphp
<tr @if ($overdue) style="background: rgba(255, 90, 110, 0.05)" @endif>
<td class="mono" style="font-size: 12px; color: var(--link); white-space: nowrap">{{ $rental->number }}</td>
<td style="white-space: nowrap">{{ $rental->user->name }}@if ($rental->user->trashed()) <span class="tiny text-dim">(diarsipkan)</span>@endif</td>
<td class="mono" style="font-size: 12px; color: var(--muted)">{{ $rental->items->pluck('code_snapshot')->join(' · ') }}</td>
<td class="text-subtle" style="white-space: nowrap">{{ Format::wib($rental->created_at, 'd M Y H.i') }}</td>
<td style="white-space: nowrap"><span class="badge tone-{{ $rental->statusTone() }}">{{ $overdue ? 'Aktif · Terlambat' : $rental->statusLabel() }}</span></td>
<td style="white-space: nowrap; color: {{ $overdue ? '#ff9aa8' : 'var(--subtle)' }}">
@if ($rental->status === RentalStatus::Active)
{{ Format::wib($rental->due_at, 'd M H.i') }}@if ($overdue) · +{{ $rental->estimatedLateDays() }} hari @endif
@elseif ($rental->status === RentalStatus::Returned)
{{ $rental->fineTotal() > 0 ? 'Denda '.Format::rupiah($rental->fineTotal()) : 'Tanpa denda' }}
@else
—
@endif
</td>
<td style="text-align: right; white-space: nowrap">{{ Format::rupiah($rental->initial_total) }}</td>
<td style="text-align: right; padding-top: 8px"><a href="{{ route('admin.rentals.show', $rental) }}" class="btn btn-sm" style="min-height: 36px" aria-label="Buka {{ $rental->number }}">Buka</a></td>
</tr>
@endforeach
</tbody>
</table>
</div>
@if ($rentals->hasPages())
<nav aria-label="Halaman transaksi" class="between small text-subtle" style="margin-top: 16px">
<span>Halaman {{ $rentals->currentPage() }} dari {{ $rentals->lastPage() }} · {{ $rentals->total() }} transaksi</span>
<span class="hstack" style="gap: 8px">
@if ($rentals->previousPageUrl())
<a href="{{ $rentals->previousPageUrl() }}" class="btn btn-sm" rel="prev">Sebelumnya</a>
@endif
@if ($rentals->nextPageUrl())
<a href="{{ $rentals->nextPageUrl() }}" class="btn btn-sm" rel="next">Berikutnya</a>
@endif
</span>
</nav>
@endif
@endif
@endsection
