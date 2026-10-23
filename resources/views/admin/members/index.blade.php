@extends('layouts.admin')

@section('title', 'Anggota')

@php
    $isNew = $editing === null;
    $profile = $editing?->profile;
    $editingHeld = $editing ? (int) $editing->held_units : 0;
@endphp

@section('content')
<div class="between" style="align-items: flex-end; gap: 20px">
<div>
<div class="eyebrow">Master data · {{ $memberCount }} anggota aktif</div>
<h1 class="h1-admin">Anggota</h1>
</div>
<a href="{{ route('admin.members.index') }}" class="btn btn-primary btn-sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>Tambah anggota</a>
</div>

<div class="row" style="margin-top: 28px; gap: 24px">
<div class="table-wrap" style="flex: 999 1 520px; min-width: 0">
<table class="table" style="min-width: 620px">
<thead>
<tr>
<th scope="col">Nama / email</th>
<th scope="col">Peran</th>
<th scope="col">Unit ditahan</th>
<th scope="col">Status</th>
<th scope="col"><span class="sr-only">Aksi</span></th>
</tr>
</thead>
<tbody>
@foreach ($users as $user)
<tr @if ($editing?->is($user)) style="background: var(--soft)" @endif>
<td>
<div class="hstack" style="gap: 10px; flex-wrap: nowrap">
<span class="avatar" aria-hidden="true" style="width: 34px; height: 34px; font-size: 12px; @if ($user->isAdmin()) background: #f6eef2; color: #0a0608 @endif">{{ $user->initials() }}</span>
<span style="min-width: 0">
<span style="display: block; @if ($user->trashed()) color: var(--dim) @endif">{{ $user->name }}</span>
<span class="tiny text-dim" style="display: block">{{ $user->email }}</span>
</span>
</div>
</td>
<td class="small text-subtle">{{ $user->isAdmin() ? 'Admin' : 'Anggota' }}</td>
<td class="mono small" @if ((int) $user->held_units >= $maxUnits) style="color: #f6cf8e" @endif>{{ $user->isAdmin() ? '—' : $user->held_units.' / '.$maxUnits }}</td>
<td><span class="badge {{ $user->trashed() ? 'tone-muted' : 'tone-success' }}">{{ $user->trashed() ? 'Diarsipkan' : 'Aktif' }}</span></td>
<td style="text-align: right; white-space: nowrap">
@if ($user->isMember())
<a href="{{ route('admin.members.index', ['ubah' => $user->id]) }}" class="small">Ubah</a>
@else
<span class="tiny text-dim">Hanya baca</span>
@endif
</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<div style="flex: 1 1 380px; min-width: 0" class="stack">
<form method="POST" action="{{ $isNew ? route('admin.members.store') : route('admin.members.update', $editing) }}" aria-label="Form anggota" class="aside-card card-flush" style="padding: 0" novalidate>
@csrf
@unless ($isNew)
@method('PUT')
@endunless
<div style="padding: 20px 24px; border-bottom: 1px solid var(--card-line)">
<div class="label-mono">{{ $isNew ? 'Anggota baru' : 'Ubah anggota' }}</div>
<div class="h2" style="margin-top: 4px">{{ $isNew ? 'Tambah akun anggota' : $editing->name }}</div>
</div>
<div class="stack" style="padding: 20px 24px">
<div class="field">
<label for="m-name">Nama</label>
<input id="m-name" name="name" type="text" class="input" value="{{ old('name', $editing?->name) }}" placeholder="2–100 karakter" maxlength="100" required autocomplete="off" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
<x-field-error name="name" />
</div>
<div class="field">
<label for="m-email">Email</label>
<input id="m-email" name="email" type="email" class="input" value="{{ old('email', $editing?->email) }}" placeholder="nama@email.com" maxlength="254" required autocomplete="off" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
<x-field-error name="email" />
</div>
<div class="field-row">
<div class="field">
<label for="m-pass">{{ $isNew ? 'Password' : 'Password baru (opsional)' }}</label>
<input id="m-pass" name="password" type="password" class="input" placeholder="Minimal 8 karakter" autocomplete="new-password" @if ($isNew) required @endif @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
</div>
<div class="field">
<label for="m-pass2">Konfirmasi password</label>
<input id="m-pass2" name="password_confirmation" type="password" class="input" autocomplete="new-password" @if ($isNew) required @endif>
</div>
</div>
<x-field-error name="password" />
<div class="field">
<label for="m-phone">Telepon <span class="text-dim">(profil)</span></label>
<input id="m-phone" name="phone" type="tel" class="input" value="{{ old('phone', $profile?->phone) }}" placeholder="+62 8xx xxxx xxxx" maxlength="25" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
<x-field-error name="phone" />
</div>
<div class="field">
<label for="m-addr">Alamat default <span class="text-dim">(profil)</span></label>
<textarea id="m-addr" name="address" rows="2" class="input" placeholder="10–1000 karakter" @error('address') aria-invalid="true" aria-describedby="address-error" @enderror>{{ old('address', $profile?->address) }}</textarea>
<x-field-error name="address" />
</div>
<div class="field">
<label for="m-area">Area layanan <span class="text-dim">(profil)</span></label>
<select id="m-area" name="service_area" class="input" @error('service_area') aria-invalid="true" aria-describedby="service_area-error" @enderror>
<option value="">Belum dipilih</option>
@foreach ($serviceAreas as $area)
<option value="{{ $area }}" @selected(old('service_area', $profile?->service_area) === $area)>{{ $area }}</option>
@endforeach
</select>
<x-field-error name="service_area" />
</div>
<div class="soft-box">Peran: <strong style="color: var(--text)">Anggota</strong>. Peran tidak bisa diubah lewat form ini. Satu profil dibuat otomatis bersama akun.</div>
@if (! $isNew && $editing->trashed())
<p class="note">Akun ini diarsipkan dan tidak bisa masuk sampai dipulihkan.</p>
@elseif (! $isNew && $editingHeld > 0)
<p class="note" style="color: #f6cf8e">Masih menahan {{ $editingHeld }} unit. Arsip tersedia setelah semua pesanannya selesai.</p>
@endif
<div class="hstack" style="gap: 10px; margin-top: 4px">
<button type="submit" class="btn btn-primary" style="flex: 1 1 160px">{{ $isNew ? 'Simpan anggota' : 'Simpan perubahan' }}</button>
@unless ($isNew)
<a href="{{ route('admin.members.index') }}" class="btn">Batal</a>
@endunless
</div>
</div>
</form>

@unless ($isNew)
<div class="aside-card hstack" style="justify-content: space-between">
<span class="small text-subtle">{{ $editing->trashed() ? 'Pulihkan akses masuk akun ini.' : 'Arsip menutup akses masuk; riwayat tetap tersimpan.' }}</span>
@if ($editing->trashed())
<form method="POST" action="{{ route('admin.members.restore', $editing) }}">
@csrf
<button type="submit" class="btn btn-sm">Pulihkan</button>
</form>
@elseif ($editingHeld > 0)
<button type="button" class="btn btn-sm" disabled title="Masih menahan unit">Arsipkan</button>
@else
<form method="POST" action="{{ route('admin.members.destroy', $editing) }}" onsubmit="return confirm('Arsipkan akun ini?')">
@csrf
@method('DELETE')
<button type="submit" class="btn btn-sm btn-danger">Arsipkan</button>
</form>
@endif
</div>
@endunless
</div>
</div>
@endsection
