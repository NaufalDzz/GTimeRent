@extends('layouts.admin')

@section('title', 'Unit')

@section('content')
<div class="between" style="align-items: flex-end; gap: 20px">
<div>
<div class="eyebrow">Master data · {{ $activeCount }} unit aktif</div>
<h1 class="h1-admin">Unit</h1>
</div>
<a href="{{ route('admin.units.index', ['baru' => 1]) }}" class="btn btn-primary btn-sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>Tambah unit</a>
</div>

<form method="GET" action="{{ route('admin.units.index') }}" class="hstack" style="margin-top: 28px; align-items: flex-end" role="search" aria-label="Cari unit">
<div class="filter-field" style="flex: 1 1 260px">
<label for="q">Cari nama atau kode</label>
<input id="q" name="q" type="search" class="input" value="{{ $query }}" placeholder="mis. PC Mid atau PC-MID-001">
</div>
<div class="filter-field" style="flex: 0 1 200px">
<label for="status">Status</label>
<select id="status" name="status" class="input">
@foreach ($filters as $value => $label)
<option value="{{ $value }}" @selected($filter === $value)>{{ $label }}</option>
@endforeach
</select>
</div>
<button type="submit" class="btn btn-sm">Terapkan</button>
@if ($query !== '' || $filter !== '')
<a href="{{ route('admin.units.index') }}" class="link-btn">Reset</a>
@endif
</form>

<div class="admin-workspace" style="margin-top: 20px">
<div class="workspace-list">
@if ($units->isEmpty())
<div class="empty">Tidak ada unit yang cocok dengan pencarian ini.</div>
@else
<div class="table-wrap">
<table class="table" style="min-width: 560px">
<thead>
<tr>
<th scope="col">Kode / nama</th>
<th scope="col">Jenis</th>
<th scope="col" style="text-align: right">Harga · denda</th>
<th scope="col">Status</th>
<th scope="col"><span class="sr-only">Aksi</span></th>
</tr>
</thead>
<tbody>
@foreach ($units as $unit)
<tr @class(['selected-row' => $editing?->is($unit)]) @if ($unit->trashed()) style="color: var(--dim)" @endif>
<td>
<a href="{{ route('admin.units.index', ['ubah' => $unit->code]) }}" style="text-decoration: none; color: inherit">
<span class="code" style="display: block">{{ $unit->code }}</span>
<span style="display: block; margin-top: 2px">{{ $unit->name }}</span>
</a>
</td>
<td class="small text-subtle" style="white-space: nowrap"><x-unit-icon :kind="$unit->kind" :size="16" style="vertical-align: -3px; margin-right: 4px" />{{ $unit->kind->label() }}</td>
<td style="text-align: right; white-space: nowrap"><div>{{ \App\Support\Format::rupiah($unit->package_price) }}</div><div class="tiny text-dim">{{ \App\Support\Format::rupiah($unit->daily_fine) }}/hari</div></td>
<td style="white-space: nowrap">
<span class="badge tone-{{ $unit->availabilityTone() }}">{{ $unit->availabilityLabel() }}</span>
@if ($holdingRental = $unit->rentalItems->first()?->rental)
<a href="{{ route('admin.rentals.show', $holdingRental) }}" class="tiny" style="display: block; margin-top: 4px">Ditahan {{ $holdingRental->number }}</a>
@endif
@if ($unit->isHeld() && $unit->availabilityLabel() !== 'Sedang ditahan')
<span class="badge tone-warning" style="margin-left: 4px">Ditahan</span>
@endif
</td>
<td style="white-space: nowrap; text-align: right">
@if ($unit->trashed())
<form method="POST" action="{{ route('admin.units.restore', $unit) }}">
@csrf
<button type="submit" class="link-btn">Pulihkan</button>
</form>
@else
<a href="{{ route('admin.units.index', ['ubah' => $unit->code]) }}" class="small" aria-label="Ubah {{ $unit->code }}">Ubah</a>
@endif
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
@endif
</div>
<aside class="workspace-panel stack" aria-label="Editor unit">
@include('admin.units._form', [
    'unit' => $editing ?? new \App\Models\Unit(['kind' => \App\Domain\Catalog\UnitKind::PcMonitor, 'condition_status' => \App\Domain\Catalog\UnitCondition::Ready]),
    'isHeld' => $editing?->isHeld() ?? false,
    'inline' => true,
])
@if ($editing)
<div class="aside-card stack-sm">
@if ($editing->trashed())
<p class="note">Unit ini diarsipkan. Riwayat transaksinya tetap tersimpan.</p>
<form method="POST" action="{{ route('admin.units.restore', $editing) }}">@csrf<button class="btn btn-sm" type="submit">Pulihkan unit</button></form>
@elseif ($editing->isHeld())
<p class="note">Unit masih ditahan. Arsip tersedia setelah pesanan selesai atau dibatalkan.</p>
<button class="btn btn-sm" type="button" disabled>Arsipkan</button>
@else
<form method="POST" action="{{ route('admin.units.destroy', $editing) }}" onsubmit="return confirm('Arsipkan unit ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" type="submit">Arsipkan</button></form>
@endif
</div>
@endif
</aside>
</div>
@endsection
