@extends('layouts.base')

@section('title', 'Cetak riwayat transaksi')

@php
    use App\Domain\Rental\RentalStatus;
    use App\Support\Format;
@endphp

@section('body')
<style>
.print-wrap{overflow-x:auto}
.print-sheet{max-width:1300px;min-width:1100px;margin:0 auto;box-sizing:border-box;padding:40px 32px;border-radius:6px;background:#fff;color:#1a1216;box-shadow:0 30px 80px rgba(0,0,0,.55);font-family:Inter,system-ui,sans-serif}
.print-sheet table{width:100%;min-width:860px;margin-top:20px;border-collapse:collapse;font-size:12px}
.print-sheet th{padding:8px;border-bottom:1px solid #1a1216;font-weight:600;text-align:left;color:#5e4d56}
.print-sheet td{padding:10px 8px;border-bottom:1px solid #e4dadf;vertical-align:top}
.print-sheet tfoot td{padding:12px 8px;border-top:2px solid #1a1216;border-bottom:0;font-weight:600}
.print-sheet .num{text-align:right;white-space:nowrap}
.print-sheet .is-cancelled{color:#8a7a82}
.print-sheet dl{margin:0;display:grid;grid-template-columns:auto auto;gap:4px 16px;font-size:12px}
.print-sheet dt{color:#5e4d56}
.print-sheet dd{margin:0}
@media print{
  @page{size:A4 landscape;margin:12mm}
  body{background:#fff !important}
  .gtr{background:#fff !important;min-height:0 !important}
  .print-wrap{padding:0 !important}
  .print-wrap{overflow:visible}
  .print-sheet{box-shadow:none;border-radius:0;padding:0;max-width:none;min-width:0}
  .print-sheet table{font-size:10px;table-layout:fixed;overflow-wrap:anywhere}
  .print-sheet td,.print-sheet th{padding:8px 4px;white-space:normal !important}
  .print-sheet td div{white-space:normal !important}
  .print-sheet tr{break-inside:avoid}
  .print-sheet thead{display:table-header-group}
  .print-sheet tfoot{display:table-row-group}
  .print-sheet table{min-width:0}
}
</style>
<div class="gtr" style="padding-bottom: 80px">
<div class="no-print between" style="padding: 18px 40px; border-bottom: 1px solid var(--rule); background: #0d080a; align-items: flex-end">
<div class="hstack" style="gap: 20px">
<a href="{{ route('admin.rentals.index', array_filter($filters, fn ($value) => $value !== null)) }}" class="hstack small" style="gap: 8px; min-height: 44px; text-decoration: none"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"></path></svg>Dashboard</a>
<span class="label-mono">Pratinjau cetak</span>
</div>
<form method="GET" action="{{ route('admin.rentals.print') }}" class="hstack" style="align-items: flex-end" aria-label="Filter laporan">
<div class="filter-field">
<label for="p-member">Anggota</label>
<select id="p-member" name="member" class="input">
<option value="">Semua anggota</option>
@foreach ($members as $member)
<option value="{{ $member->id }}" @selected($filters['member'] === $member->id)>{{ $member->name }}</option>
@endforeach
</select>
</div>
<div class="filter-field">
<label for="p-status">Status</label>
<select id="p-status" name="status" class="input">
<option value="">Semua status</option>
@foreach ($statusOptions as $value => $label)
<option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
@endforeach
</select>
</div>
<div class="filter-field">
<label for="p-from">Dibuat dari</label>
<input id="p-from" type="date" name="from" value="{{ $filters['from'] }}" class="input" style="color-scheme: dark">
</div>
<div class="filter-field">
<label for="p-to">Sampai</label>
<input id="p-to" type="date" name="to" value="{{ $filters['to'] }}" class="input" style="color-scheme: dark">
</div>
<button type="submit" class="btn btn-sm" style="min-height: 44px">Terapkan</button>
<button type="button" class="btn btn-primary btn-sm" style="min-height: 44px" onclick="window.print()"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4v-7h16v7h-2"></path><rect x="6" y="14" width="12" height="7"></rect></svg>Cetak</button>
</form>
</div>

<div class="print-wrap" style="padding: 40px 24px 0">
<article class="print-sheet">
<header style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 20px; padding-bottom: 20px; border-bottom: 2px solid #1a1216">
<div>
<div class="poppins" style="font-weight: 700; font-size: 22px">/GTime Rent</div>
<h1 class="poppins" style="margin: 6px 0 0; font-weight: 600; font-size: 18px">Laporan Riwayat Transaksi</h1>
</div>
<dl>
<dt>Dicetak</dt><dd>{{ Format::wib(now()) }}</dd>
<dt>Oleh</dt><dd>{{ auth()->user()->name }}</dd>
<dt>Periode</dt><dd>{{ $periodLabel }} (tanggal dibuat)</dd>
<dt>Anggota</dt><dd>{{ $memberLabel }}</dd>
<dt>Status</dt><dd>{{ $statusLabel }}</dd>
</dl>
</header>

@if ($rentals->isEmpty())
<p style="margin: 28px 0 0; font-size: 13px; color: #5e4d56">Tidak ada transaksi yang cocok dengan filter ini.</p>
@else
<div style="overflow-x: auto">
<table>
<thead>
<tr>
<th scope="col" style="padding-left: 0">No. transaksi</th>
<th scope="col">Anggota</th>
<th scope="col">Unit (snapshot)</th>
<th scope="col">Status</th>
<th scope="col">Mulai sewa</th>
<th scope="col">Kembali</th>
<th scope="col" class="num">Harga paket</th>
<th scope="col" class="num">Ongkir</th>
<th scope="col" class="num" style="padding-right: 0">Denda</th>
</tr>
</thead>
<tbody>
@foreach ($rentals as $rental)
@php
    $cancelled = $rental->status === RentalStatus::Cancelled;
    $overdue = $rental->isOverdue();
@endphp
<tr @class(['is-cancelled' => $cancelled])>
<td class="mono" style="padding-left: 0; white-space: nowrap">{{ $rental->number }}<div style="font-family: Inter, sans-serif; color: #5e4d56">{{ Format::wib($rental->created_at, 'd M Y H.i') }}</div></td>
<td>
<div style="white-space: nowrap">{{ $rental->user->name }}</div>
<div style="color: #5e4d56">{{ $rental->user->email }}</div>
<div style="color: #5e4d56">{{ $rental->recipient_phone }} · {{ $rental->service_area }}</div>
</td>
<td style="line-height: 1.5">
@foreach ($rental->items as $item)
<div><span class="mono">{{ $item->code_snapshot }}</span> · {{ $item->name_snapshot }}</div>
@endforeach
</td>
<td style="white-space: nowrap">{{ $overdue ? 'Aktif · terlambat' : $rental->statusLabel() }}</td>
<td style="white-space: nowrap">{{ $rental->received_at ? Format::wib($rental->received_at, 'd M Y H.i') : '—' }}</td>
<td style="white-space: nowrap">
@if ($rental->returned_at)
{{ Format::wib($rental->returned_at, 'd M Y H.i') }}
@elseif ($rental->due_at)
Tempo {{ Format::wib($rental->due_at, 'd M Y H.i') }}
@else
—
@endif
</td>
<td class="num">{{ Format::rupiah($rental->subtotal) }}</td>
<td class="num">{{ Format::rupiah($rental->shipping_fee_snapshot) }}</td>
<td class="num" style="padding-right: 0">
@if ($rental->status === RentalStatus::Returned)
{{ Format::rupiah($rental->fineTotal()) }}
@elseif ($overdue)
berjalan
@else
—
@endif
</td>
</tr>
@endforeach
</tbody>
<tfoot>
<tr>
<td colspan="6" style="padding-left: 0">Total ({{ $totals['count'] }} transaksi, tanpa yang dibatalkan)</td>
<td class="num">{{ Format::rupiah($totals['packages']) }}</td>
<td class="num">{{ Format::rupiah($totals['shipping']) }}</td>
<td class="num" style="padding-right: 0">{{ Format::rupiah($totals['fines']) }}</td>
</tr>
</tfoot>
</table>
</div>
@endif

<footer style="margin-top: 28px; font-size: 11px; line-height: 1.6; color: #5e4d56">
<p style="margin: 0; max-width: 680px">Harga, ongkir, dan denda memakai snapshot saat checkout. Denda final ditagih terpisah dari tagihan awal. Pesanan dibatalkan tidak masuk total. Mulai sewa = waktu semua unit diterima penyewa. Waktu dalam WIB.</p>
</footer>
</article>
</div>
</div>
@endsection
