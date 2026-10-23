@extends('layouts.admin')

@section('title', 'Ubah unit '.$unit->code)

@section('content')
<div class="eyebrow">Master data · unit</div>
<h1 class="h1-admin">{{ $unit->name }}</h1>

<div class="row" style="margin-top: 28px; gap: 24px">
<div class="grow" style="max-width: 720px">
@include('admin.units._form')
</div>
<aside class="side stack">
<div class="aside-card stack-sm">
<div class="label-mono">Status saat ini</div>
<div><span class="badge tone-{{ $unit->availabilityTone() }}">{{ $unit->availabilityLabel() }}</span></div>
@if ($unit->trashed())
<p class="note">Unit arsip tidak tampil untuk pesanan baru, tetapi riwayat transaksinya tetap terbaca.</p>
<form method="POST" action="{{ route('admin.units.restore', $unit) }}">
@csrf
<button type="submit" class="btn btn-sm">Pulihkan unit</button>
</form>
@elseif ($isHeld)
<p class="note">Unit masih ditahan pesanan. Arsip tersedia setelah pesanannya selesai atau dibatalkan.</p>
<button type="button" class="btn btn-sm" disabled title="Unit masih ditahan pesanan">Arsipkan</button>
@else
<p class="note">Arsip menyembunyikan unit dari katalog. Kode tetap tercatat dan tidak dapat dipakai ulang.</p>
<form method="POST" action="{{ route('admin.units.destroy', $unit) }}" onsubmit="return confirm('Arsipkan unit ini?')">
@csrf
@method('DELETE')
<button type="submit" class="btn btn-sm btn-danger">Arsipkan</button>
</form>
@endif
</div>
</aside>
</div>
@endsection
