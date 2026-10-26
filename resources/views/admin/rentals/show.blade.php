@extends('layouts.admin')

@section('title', $rental->number)
@section('compact-admin', 'true')

@php
    use App\Domain\Rental\PaymentMethod;
    use App\Domain\Rental\RentalPeriod;
    use App\Domain\Rental\RentalStatus;
    use App\Support\Format;

    $status = $rental->status;
    $overdue = $rental->isOverdue();
    $dailyFineTotal = (int) $rental->items->sum('daily_fine_snapshot');
    $nowInput = Format::wibInput(null);

    $timeline = [[
        'label' => 'Checkout · unit ditahan',
        'who' => $rental->user->name,
        'time' => Format::wib($rental->created_at),
        'state' => 'done',
    ]];

    if ($status === RentalStatus::Cancelled) {
        $timeline[] = [
            'label' => 'Dibatalkan · unit dilepas',
            'who' => ($rental->canceller?->name ?? '—').' · Alasan: '.$rental->cancel_reason,
            'time' => Format::wib($rental->cancelled_at),
            'state' => 'stopped',
        ];
    } else {
        $steps = [
            [$rental->payment_confirmed_at, 'Pembayaran dikonfirmasi'.($rental->payment_method ? ' ('.$rental->payment_method->label().')' : ''), 'Konfirmasi pembayaran', $rental->paymentConfirmer?->name, null],
            [$rental->dispatched_at, 'Kurir berangkat', 'Keberangkatan kurir', $rental->dispatcher?->name, null],
            [$rental->received_at, 'Diterima penyewa · jatuh tempo '.Format::wib($rental->due_at, 'd M H.i'), 'Diterima penyewa', $rental->receiptRecorder?->name, $rental->receipt_recorded_at],
            [$rental->returned_at, 'Diterima GTR · denda '.Format::rupiah($rental->fineTotal()), 'Diterima GTR', $rental->returnRecorder?->name, $rental->return_recorded_at],
        ];
        $currentMarked = false;
        foreach ($steps as [$at, $doneLabel, $pendingLabel, $actor, $recordedAt]) {
            if ($at !== null) {
                $timeline[] = [
                    'label' => $doneLabel,
                    'who' => ($actor ?? '—').($recordedAt !== null ? ' · dicatat '.Format::wib($recordedAt) : ''),
                    'time' => Format::wib($at),
                    'state' => 'done',
                ];

                continue;
            }
            $timeline[] = [
                'label' => $pendingLabel,
                'who' => $currentMarked ? '' : 'Menunggu tindakan admin',
                'time' => '—',
                'state' => $currentMarked ? '' : 'current',
            ];
            $currentMarked = true;
        }
    }

    $actionTitle = match ($status) {
        RentalStatus::PendingPayment => 'Konfirmasi pembayaran',
        RentalStatus::Paid => 'Catat keberangkatan kurir',
        RentalStatus::InDelivery => 'Catat penerimaan penyewa',
        RentalStatus::Active => 'Catat pengembalian',
        RentalStatus::Returned => 'Transaksi selesai',
        RentalStatus::Cancelled => 'Transaksi dibatalkan',
    };
@endphp

@section('content')

<div class="between" style="margin-top: 12px; align-items: flex-end; gap: 20px">
<div>
<div class="label-mono" style="font-size: 12px">Transaksi · {{ $rental->user->name }}</div>
<h1 class="h1-admin mono" style="font-size: 30px">{{ $rental->number }}</h1>
</div>
<span class="badge tone-{{ $rental->statusTone() }}" style="padding: 10px 16px; font-size: 14px; font-weight: 500">{{ $overdue ? 'Aktif · Terlambat' : $rental->statusLabel() }}</span>
</div>

<div class="row" style="margin-top: 28px; gap: 28px">
<div class="stack-lg" style="flex: 999 1 520px; min-width: 0; gap: 20px">

<section class="card" aria-labelledby="timeline-title">
<h2 id="timeline-title" class="small" style="margin: 0; font-size: 14px; font-weight: 600; color: var(--muted)">Riwayat status</h2>
<ol class="timeline" style="margin-top: 18px">
@foreach ($timeline as $step)
<li class="{{ $step['state'] }}">
<span class="mark" aria-hidden="true"></span>
<div class="between" style="flex: 1; align-items: flex-start; gap: 4px 16px">
<div>
<div style="font-size: 14px; font-weight: 500; color: {{ in_array($step['state'], ['done', 'stopped'], true) ? 'var(--text)' : 'var(--dim)' }}">{{ $step['label'] }}</div>
@if ($step['who'] !== '')
<div class="tiny text-dim" style="margin-top: 2px">{{ $step['who'] }}</div>
@endif
</div>
<div class="mono tiny text-subtle">{{ $step['time'] }}</div>
</div>
</li>
@endforeach
</ol>
</section>

<section class="card" aria-labelledby="snapshot-title">
<div class="between">
<h2 id="snapshot-title" style="margin: 0; font-size: 14px; font-weight: 600; color: var(--muted)">Snapshot saat checkout</h2>
<span class="label-mono">Tidak berubah walau tarif master diubah</span>
</div>
<div style="margin-top: 14px; border-top: 1px solid var(--rule)">
@foreach ($rental->items as $item)
<div class="snapshot-line" style="padding: 14px 0; border-bottom: 1px solid var(--rule)">
<div style="min-width: 0">
<div class="code" style="font-size: 11px">{{ $item->code_snapshot }}</div>
<div style="margin-top: 2px; font-size: 14px">{{ $item->name_snapshot }}</div>
<div class="tiny text-dim" style="margin-top: 2px">{{ implode(' · ', $item->contentsList()) }}</div>
</div>
<div style="text-align: right">
<div style="font-size: 14px">{{ Format::rupiah($item->package_price_snapshot) }}</div>
<div class="tiny text-dim" style="margin-top: 2px">denda {{ Format::rupiah($item->daily_fine_snapshot) }}/hari</div>
@if ($status === RentalStatus::Returned)
<div class="tiny" style="margin-top: 2px; color: {{ $item->fine_amount > 0 ? '#ffb4be' : 'var(--subtle)' }}">{{ $item->late_days }} hari · {{ Format::rupiah($item->fine_amount) }}</div>
@endif
</div>
</div>
@endforeach
<div class="sum-row" style="padding: 14px 0; border-bottom: 1px solid var(--rule)"><span>Ongkir antar-jemput</span><span>{{ Format::rupiah($rental->shipping_fee_snapshot) }}</span></div>
<div class="between" style="align-items: baseline; padding: 14px 0"><span class="small text-subtle" style="font-size: 14px">Tagihan awal</span><span class="poppins" style="font-weight: 700; font-size: 20px">{{ Format::rupiah($rental->initial_total) }}</span></div>
@if ($status === RentalStatus::Returned)
<div class="between" style="align-items: baseline; padding: 14px 0; border-top: 1px solid var(--rule)"><span class="small text-subtle" style="font-size: 14px">Denda final (ditagih terpisah)</span><span class="poppins" style="font-weight: 700; font-size: 20px; color: #ffb4be">{{ Format::rupiah($rental->fineTotal()) }}</span></div>
@elseif ($overdue)
<div class="between" style="align-items: baseline; padding: 14px 0; border-top: 1px solid var(--rule)"><span class="small text-subtle" style="font-size: 14px">Estimasi denda berjalan ({{ $rental->estimatedLateDays() }} hari)</span><span class="poppins" style="font-weight: 700; font-size: 20px; color: #ffb4be">{{ Format::rupiah($rental->estimatedFine()) }}</span></div>
@endif
</div>
<div class="hstack" style="align-items: stretch">
<div class="soft-box" style="flex: 1 1 220px; padding: 14px"><div class="tiny text-dim">Anggota</div><div style="margin-top: 4px; font-size: 14px; color: var(--text)">{{ $rental->user->name }}</div><div class="tiny text-subtle">{{ $rental->user->email }}</div></div>
<div class="soft-box" style="flex: 1 1 220px; padding: 14px"><div class="tiny text-dim">Penerima</div><div style="margin-top: 4px; font-size: 14px; color: var(--text)">{{ $rental->recipient_name }} · {{ $rental->recipient_phone }}</div></div>
<div class="soft-box" style="flex: 1 1 100%; padding: 14px"><div class="tiny text-dim">Alamat · {{ $rental->service_area }}</div><div style="margin-top: 4px; font-size: 14px; color: var(--text); white-space: pre-line">{{ $rental->delivery_address }}</div></div>
@if ($rental->return_notes)
<div class="soft-box" style="flex: 1 1 100%; padding: 14px"><div class="tiny text-dim">Catatan pengembalian</div><div style="margin-top: 4px; font-size: 14px; color: var(--text); white-space: pre-line">{{ $rental->return_notes }}</div></div>
@endif
</div>
</section>
</div>

<aside class="aside-card no-print" style="flex: 1 1 360px; min-width: 0; padding: 0; overflow: hidden" aria-labelledby="action-title">
<div style="padding: 20px 24px; border-bottom: 1px solid var(--card-line)">
<div class="label-mono">Aksi yang sah</div>
<h2 id="action-title" class="h2" style="margin-top: 6px; font-size: 18px">{{ $actionTitle }}</h2>
</div>

@if ($status === RentalStatus::PendingPayment)
<div class="stack" style="padding: 24px; gap: 14px">
<form method="POST" action="{{ route('admin.rentals.confirm', $rental) }}" class="stack" style="gap: 14px">
@csrf
<fieldset style="margin: 0; padding: 0; border: 0">
<legend class="field-label" style="padding: 0">Metode pembayaran</legend>
<div class="hstack" style="margin-top: 8px; gap: 8px">
@foreach (PaymentMethod::cases() as $method)
<label class="radio-card" style="flex: 1 1 0">
<span class="hstack" style="gap: 10px"><input type="radio" name="payment_method" value="{{ $method->value }}" @checked(old('payment_method', PaymentMethod::Transfer->value) === $method->value) required><span class="dot" aria-hidden="true"></span>{{ $method->label() }}</span>
</label>
@endforeach
</div>
</fieldset>
<x-field-error name="payment_method" />
<p class="note">Pastikan alamat sudah diverifikasi dan dana sudah diterima sebelum konfirmasi.</p>
<button type="submit" class="btn btn-primary">Konfirmasi lunas</button>
</form>
<form method="POST" action="{{ route('admin.rentals.cancel', $rental) }}" class="stack" style="margin-top: 8px; padding-top: 18px; border-top: 1px solid var(--card-line); gap: 8px">
@csrf
<label for="cancel-reason">Alasan pembatalan (wajib)</label>
<input id="cancel-reason" type="text" name="reason" value="{{ old('reason') }}" class="input" placeholder="3–255 karakter" minlength="3" maxlength="255" required @error('reason') aria-invalid="true" aria-describedby="reason-error" @enderror>
<x-field-error name="reason" />
<button type="submit" class="btn btn-danger">Batalkan &amp; lepas unit</button>
</form>
</div>
@elseif ($status === RentalStatus::Paid)
<form method="POST" action="{{ route('admin.rentals.dispatch', $rental) }}" class="stack" style="padding: 24px; gap: 14px">
@csrf
<label for="dispatched-at">Waktu kurir berangkat (WIB)</label>
<input id="dispatched-at" type="datetime-local" step="1" name="dispatched_at" value="{{ old('dispatched_at', $nowInput) }}" min="{{ Format::wibInput($rental->payment_confirmed_at) }}" max="{{ $nowInput }}" class="input" style="color-scheme: dark" required @error('dispatched_at') aria-invalid="true" aria-describedby="dispatched_at-error" @enderror>
<x-field-error name="dispatched_at" />
<p class="note">Tidak boleh sebelum pembayaran dikonfirmasi ({{ Format::wib($rental->payment_confirmed_at) }}) atau di masa depan.</p>
<button type="submit" class="btn btn-primary">Catat pengiriman</button>
</form>
@elseif ($status === RentalStatus::InDelivery)
<form method="POST" action="{{ route('admin.rentals.receive', $rental) }}" class="stack" style="padding: 24px; gap: 14px">
@csrf
<label for="received-at">Semua unit diterima penyewa pada (WIB)</label>
<input id="received-at" type="datetime-local" step="1" name="received_at" value="{{ old('received_at', $nowInput) }}" min="{{ Format::wibInput($rental->dispatched_at) }}" max="{{ $nowInput }}" class="input" style="color-scheme: dark" required @error('received_at') aria-invalid="true" aria-describedby="received_at-error" @enderror>
<x-field-error name="received_at" />
<div class="soft-box" style="padding: 14px; color: var(--muted)">Jatuh tempo otomatis: <strong id="due-preview" style="color: #fff">{{ Format::wib(RentalPeriod::dueAt(now()->toDateTimeImmutable())) }}</strong> <span class="text-dim">(+{{ RentalPeriod::HOURS }} jam)</span></div>
<p class="note">Tidak boleh sebelum kurir berangkat ({{ Format::wib($rental->dispatched_at) }}) atau di masa depan.</p>
<button type="submit" class="btn btn-primary">Catat diterima penyewa</button>
</form>
@elseif ($status === RentalStatus::Active)
<form method="POST" action="{{ route('admin.rentals.return', $rental) }}" class="stack" style="padding: 24px; gap: 14px" id="return-form" data-due="{{ $rental->due_at->getTimestamp() }}" data-daily-fine="{{ $dailyFineTotal }}">
@csrf
@if ($overdue)
<div class="alert" style="padding: 14px; font-size: 13px">Lewat jatuh tempo {{ Format::wib($rental->due_at) }}. Unit masih ditahan sampai pengembalian dicatat.</div>
@else
<div class="soft-box" style="padding: 14px">Jatuh tempo {{ Format::wib($rental->due_at) }}.</div>
@endif
<label for="returned-at">Semua unit diterima GTR pada (WIB)</label>
<input id="returned-at" type="datetime-local" step="1" name="returned_at" value="{{ old('returned_at', $nowInput) }}" min="{{ Format::wibInput($rental->received_at) }}" max="{{ $nowInput }}" class="input" style="color-scheme: dark" required @error('returned_at') aria-invalid="true" aria-describedby="returned_at-error" @enderror>
<x-field-error name="returned_at" />
<fieldset style="margin: 0; padding: 0; border: 0">
<legend class="field-label" style="padding: 0">Cek kelengkapan</legend>
<div class="stack-sm" style="margin-top: 8px; gap: 6px">
@foreach ($rental->items as $item)
@foreach ($item->contentsList() as $line)
<label class="check" style="align-items: center"><input type="checkbox"><span>{{ $line }}</span></label>
@endforeach
@endforeach
</div>
</fieldset>
<label for="return-notes">Catatan (opsional)</label>
<textarea id="return-notes" name="notes" rows="2" class="input" maxlength="1000" placeholder="Kondisi barang, goresan, dll." @error('notes') aria-invalid="true" aria-describedby="notes-error" @enderror>{{ old('notes') }}</textarea>
<x-field-error name="notes" />
<div class="soft-box" aria-live="polite">
<div class="sum-row" style="font-size: 13px"><span>Keterlambatan</span><span class="mono" id="late-duration">{{ $rental->estimatedLateDays() > 0 ? 'lewat jatuh tempo' : 'tepat waktu' }}</span></div>
<div class="sum-row" style="margin-top: 6px; font-size: 13px"><span>Dibulatkan</span><span class="mono" id="late-days">{{ $rental->estimatedLateDays() }} hari × {{ Format::rupiah($dailyFineTotal) }}</span></div>
<div class="sum-total" style="margin-top: 12px; padding-top: 12px"><span>Denda final</span><strong id="late-fine" style="font-size: 22px; color: #ffb4be">{{ Format::rupiah($rental->estimatedFine()) }}</strong></div>
</div>
<button type="submit" class="btn btn-primary">Catat diterima GTR</button>
</form>
@else
<div class="stack" style="padding: 24px; gap: 14px">
@if ($status === RentalStatus::Returned)
<p class="small text-muted" style="margin: 0; font-size: 14px; line-height: 1.6">Status final. Denda {{ Format::rupiah($rental->fineTotal()) }} ditagih terpisah dari tagihan awal dan dilunasi langsung ke admin. Unit {{ $rental->items->pluck('code_snapshot')->join(', ') }} sudah dilepas.</p>
@else
<p class="small text-muted" style="margin: 0; font-size: 14px; line-height: 1.6">Status final. Pesanan dibatalkan sebelum lunas dan unit sudah dilepas.</p>
@endif
<a href="{{ route('admin.rentals.print', ['member' => $rental->user_id]) }}" class="btn btn-sm">Cetak riwayat anggota ini</a>
</div>
@endif
</aside>
</div>

@if ($status === RentalStatus::InDelivery || $status === RentalStatus::Active)
<script>
(() => {
    const toDate = (value) => value ? new Date(value + (value.length === 16 ? ':00' : '') + '+07:00') : null;
    const rupiah = (n) => 'Rp' + n.toLocaleString('id-ID');
    const wib = (date) => new Intl.DateTimeFormat('id-ID', { timeZone: 'Asia/Jakarta', day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(date) + ' WIB';

    const received = document.getElementById('received-at');
    if (received) {
        const preview = document.getElementById('due-preview');
        const update = () => {
            const at = toDate(received.value);
            if (at && !isNaN(at)) {
                preview.textContent = wib(new Date(at.getTime() + {{ RentalPeriod::HOURS }} * 3600 * 1000));
            }
        };
        received.addEventListener('input', update);
        update();
    }

    const form = document.getElementById('return-form');
    if (form) {
        const input = document.getElementById('returned-at');
        const due = Number(form.dataset.due);
        const dailyFine = Number(form.dataset.dailyFine);
        const update = () => {
            const at = toDate(input.value);
            if (!at || isNaN(at)) {
                return;
            }
            const late = Math.floor(at.getTime() / 1000) - due;
            const days = late > 0 ? Math.ceil(late / 86400) : 0;
            const hours = Math.floor(Math.max(late, 0) / 3600);
            const minutes = Math.floor((Math.max(late, 0) % 3600) / 60);
            const seconds = Math.max(late, 0) % 60;
            document.getElementById('late-duration').textContent = late > 0 ? hours + ' jam ' + minutes + ' menit ' + seconds + ' detik' : 'tepat waktu';
            document.getElementById('late-days').textContent = days + ' hari × ' + rupiah(dailyFine);
            document.getElementById('late-fine').textContent = rupiah(days * dailyFine);
        };
        input.addEventListener('input', update);
        update();
    }
})();
</script>
@endif
@endsection
