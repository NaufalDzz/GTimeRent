@extends('layouts.app')

@section('title', 'Profil saya')

@section('content')
<div class="eyebrow">Profil saya</div>
@if ($user->isMember())
<h1 class="h1-mono">Data ini jadi isian awal<br><span>setiap kali kamu checkout.</span></h1>
@else
<h1 class="h1-mono">Akun admin<br><span>untuk mengelola GTime Rent.</span></h1>
@endif

<div class="row" style="margin-top: 36px; gap: 28px">
<aside class="detail-hero" style="flex: 1 1 300px; min-height: 0; padding: 28px; display: block; align-self: flex-start">
<div style="position: relative">
<span class="avatar poppins" aria-hidden="true" style="width: 72px; height: 72px; font-size: 24px; border: 1px solid rgba(255, 255, 255, 0.2)">{{ $user->initials() }}</span>
<div class="poppins" style="margin-top: 16px; font-weight: 600; font-size: 22px">{{ $user->name }}</div>
<div style="margin-top: 4px; font-size: 14px; color: #e7d6df; overflow-wrap: anywhere">{{ $user->email }}</div>
<div class="chips" style="margin-top: 14px; gap: 8px">
<span class="pill">{{ $user->isAdmin() ? 'Admin' : 'Anggota' }}</span>
@if ($user->created_at)
<span class="pill">Sejak {{ $user->created_at->locale('id')->translatedFormat('M Y') }}</span>
@endif
</div>
@if ($user->isMember())
<div style="margin-top: 24px; padding: 16px; border-radius: 14px; background: rgba(10, 6, 8, 0.6); border: 1px solid rgba(255, 255, 255, 0.1)">
<div class="between" style="align-items: baseline"><span class="label-mono" style="color: #e7d6df">Unit ditahan</span><span class="mono" style="font-size: 20px">{{ $held }}/{{ $maxUnits }}</span></div>
<div class="meter" style="margin-top: 10px" role="img" aria-label="{{ $held }} dari {{ $maxUnits }} unit ditahan"><span style="width: {{ min(100, (int) round($held / max(1, $maxUnits) * 100)) }}%"></span></div>
@if ($holdingRental)
<a href="{{ route('rentals.show', $holdingRental) }}" class="small" style="display: inline-block; margin-top: 12px">{{ $holdingRental->number }} · {{ $holdingRental->statusLabel() }}</a>
@endif
</div>
@endif
</div>
</aside>

<div style="flex: 999 1 520px; min-width: 0">
<form method="POST" action="{{ route('profile.update') }}" class="stack-lg" style="gap: 20px" novalidate>
@csrf
@method('PUT')

<section class="card stack" aria-labelledby="h-akun">
<h2 id="h-akun" class="h2" style="font-size: 18px">Akun</h2>
<div class="field-row" style="gap: 14px">
<div class="field">
<label for="p-name">Nama</label>
<input id="p-name" name="name" type="text" class="input" value="{{ old('name', $user->name) }}" autocomplete="name" required maxlength="100" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
<x-field-error name="name" />
</div>
<div class="field">
<label for="p-email">Email</label>
<input id="p-email" name="email" type="email" class="input" value="{{ old('email', $user->email) }}" autocomplete="email" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
<x-field-error name="email" />
</div>
</div>
</section>

<section class="card stack" aria-labelledby="h-kontak">
<div class="between">
<h2 id="h-kontak" class="h2" style="font-size: 18px">Kontak &amp; alamat default</h2>
@if ($user->isMember())
<span class="tiny text-dim">Dipakai sebagai isian awal checkout</span>
@endif
</div>
<div class="field">
<label for="p-phone">Nomor telepon</label>
<input id="p-phone" name="phone" type="tel" class="input" value="{{ old('phone', $profile?->phone) }}" placeholder="+62 8xx xxxx xxxx" autocomplete="tel" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
<x-field-error name="phone" />
</div>
<div class="field">
<label for="p-addr">Alamat lengkap</label>
<textarea id="p-addr" name="address" rows="3" class="input" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, patokan (10–1000 karakter)" autocomplete="street-address" maxlength="1000" @error('address') aria-invalid="true" aria-describedby="address-error" @enderror>{{ old('address', $profile?->address) }}</textarea>
<x-field-error name="address" />
</div>
<div class="field">
<label for="p-area">Area layanan</label>
<select id="p-area" name="service_area" class="input" @error('service_area') aria-invalid="true" aria-describedby="service_area-error" @enderror>
<option value="">Belum dipilih</option>
@foreach (config('gtrent.service_areas') as $area)
<option value="{{ $area }}" @selected(old('service_area', $profile?->service_area) === $area)>{{ $area }}</option>
@endforeach
</select>
<x-field-error name="service_area" />
</div>
<div class="soft-box" style="color: var(--muted)">Mengubah alamat di sini tidak mengubah alamat pesanan yang sudah dibuat. Pesanan lama tetap memakai data saat checkout.</div>
</section>

<section class="card stack" aria-labelledby="h-pass">
<h2 id="h-pass" class="h2" style="font-size: 18px">Ganti password</h2>
<div class="field-row" style="gap: 14px">
<div class="field" style="flex-basis: 180px">
<label for="p-old">Password saat ini</label>
<input id="p-old" name="current_password" type="password" class="input" autocomplete="current-password" @error('current_password') aria-invalid="true" aria-describedby="current_password-error" @enderror>
<x-field-error name="current_password" />
</div>
<div class="field" style="flex-basis: 180px">
<label for="p-new">Password baru</label>
<input id="p-new" name="password" type="password" class="input" placeholder="Min. 8 karakter" autocomplete="new-password" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
<x-field-error name="password" />
</div>
<div class="field" style="flex-basis: 180px">
<label for="p-new2">Ulangi password baru</label>
<input id="p-new2" name="password_confirmation" type="password" class="input" autocomplete="new-password">
</div>
</div>
<p class="note">Kosongkan jika tidak ingin mengganti password.</p>
</section>

<div class="between">
<button type="submit" form="logout-form" class="btn">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10"></path></svg>Keluar
</button>
<button type="submit" class="btn btn-primary" style="min-height: 50px; padding: 0 28px">Simpan perubahan</button>
</div>
</form>

<form method="POST" action="{{ route('logout') }}" id="logout-form" hidden>
@csrf
</form>
</div>
</div>
@endsection
