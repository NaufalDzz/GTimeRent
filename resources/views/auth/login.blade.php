@extends('layouts.base')

@section('title', $tab === 'daftar' ? 'Daftar' : 'Masuk')

@section('body')
<div class="gtr" style="display: flex; flex-wrap: wrap">

<div style="position: relative; overflow: hidden; flex: 1 1 520px; min-height: 420px; display: flex; flex-direction: column; justify-content: space-between; padding: 32px 40px; box-sizing: border-box; background: radial-gradient(110% 90% at 90% 100%, #3B1526 0%, #1A0A11 45%, #0A0608 80%)">
<div aria-hidden="true" style="position: absolute; width: 1600px; height: 1600px; left: -700px; top: -1180px; border-radius: 50%; background: radial-gradient(circle at 76% 84%, #A33A6E 0%, #5A1636 28%, #2A0B1A 55%, #12060B 75%); box-shadow: 0 0 140px 30px rgba(226,88,156,0.30), inset -24px -32px 80px rgba(255,214,234,0.6), inset -5px -7px 16px rgba(255,236,246,0.85)"></div>
<a href="{{ route('home') }}" class="logo" style="position: relative">/GTime Rent</a>
<div style="position: relative; max-width: 460px">
<div class="eyebrow" style="color: #D9C7D0">ANGGOTA GTR</div>
<h1 style="margin: 18px 0 0; font-family: 'JetBrains Mono', monospace; font-weight: 300; font-size: clamp(30px, 3.4vw, 44px); line-height: 1.2; letter-spacing: -0.02em; color: #E7D6DF">Masuk dulu,<br><span style="color: #FFFFFF">baru pilih rig-nya.</span></h1>
<p style="margin: 16px 0 0; font-size: 15px; line-height: 1.6; color: #D9C7D0">Checkout dan riwayat pesanan hanya bisa dibuka setelah masuk. Pendaftaran langsung membuat akun anggota.</p>
</div>
<div class="mono" style="position: relative; font-size: 12px; letter-spacing: 0.1em; color: #B3A0AA">PC / MONITOR / PERIPHERALS / DELIVERY</div>
</div>

<div style="flex: 1 1 520px; display: flex; align-items: center; justify-content: center; padding: 48px 24px; box-sizing: border-box">
<div style="width: 100%; max-width: 420px">
<nav aria-label="Masuk atau daftar" class="segmented">
<a href="{{ route('login') }}" @if ($tab === 'masuk') aria-current="page" @endif>Masuk</a>
<a href="{{ route('login', ['tab' => 'daftar']) }}" @if ($tab === 'daftar') aria-current="page" @endif>Daftar</a>
</nav>

@if ($tab === 'masuk')
<form method="POST" action="{{ route('login.store') }}" class="stack" style="margin-top: 32px; gap: 18px" novalidate>
@csrf
<h2 style="margin: 0; font-family: Poppins, sans-serif; font-weight: 600; font-size: 28px; letter-spacing: -0.02em">Selamat datang lagi</h2>
<div class="field">
<label for="l-email">Email</label>
<input id="l-email" name="email" type="email" class="input" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" required autofocus @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
<x-field-error name="email" />
</div>
<div class="field">
<label for="l-pass">Password</label>
<input id="l-pass" name="password" type="password" class="input" placeholder="Minimal 8 karakter" autocomplete="current-password" required @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
<x-field-error name="password" />
</div>
<label class="check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Ingat saya di perangkat ini</label>
<button type="submit" class="btn btn-primary btn-block" style="min-height: 50px; margin-top: 8px">Masuk</button>
<p class="note" style="font-size: 13px; text-align: center">Masuk sebagai admin? Gunakan akun yang dibuat pengelola.</p>
</form>
@else
<form method="POST" action="{{ route('register') }}" class="stack" style="margin-top: 32px" novalidate>
@csrf
<h2 style="margin: 0; font-family: Poppins, sans-serif; font-weight: 600; font-size: 28px; letter-spacing: -0.02em">Buat akun anggota</h2>
<div class="field">
<label for="r-name">Nama lengkap</label>
<input id="r-name" name="name" type="text" class="input" value="{{ old('name') }}" placeholder="2–100 karakter" autocomplete="name" required maxlength="100" @error('name', 'register') aria-invalid="true" aria-describedby="name-error" @enderror>
<x-field-error name="name" bag="register" />
</div>
<div class="field">
<label for="r-email">Email</label>
<input id="r-email" name="email" type="email" class="input" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" required @error('email', 'register') aria-invalid="true" aria-describedby="email-error" @enderror>
<x-field-error name="email" bag="register" />
</div>
<div class="field-row">
<div class="field" style="flex-basis: 160px">
<label for="r-pass">Password</label>
<input id="r-pass" name="password" type="password" class="input" placeholder="Min. 8 karakter" autocomplete="new-password" required @error('password', 'register') aria-invalid="true" aria-describedby="password-error" @enderror>
</div>
<div class="field" style="flex-basis: 160px">
<label for="r-pass2">Konfirmasi password</label>
<input id="r-pass2" name="password_confirmation" type="password" class="input" placeholder="Ulangi password" autocomplete="new-password" required @error('password', 'register') aria-invalid="true" @enderror>
</div>
</div>
<x-field-error name="password" bag="register" />
<button type="submit" class="btn btn-primary btn-block" style="min-height: 50px; margin-top: 4px">Daftar sebagai anggota</button>
<p class="note">Nomor telepon dan alamat bisa dilengkapi di profil, lalu dipakai otomatis saat checkout.</p>
</form>
@endif
</div>
</div>
</div>
@endsection
