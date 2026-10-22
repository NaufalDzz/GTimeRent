@extends('errors.layout')

@section('title', 'Akses ditolak')
@section('eyebrow', 'AKSES DITOLAK')
@section('code', '403')
@section('heading', 'Halaman ini bukan untuk akunmu.')

@section('message')
@auth
Kamu masuk sebagai <b style="color: #F6EEF2; font-weight: 600">{{ auth()->user()->name }}</b> dengan peran {{ auth()->user()->isAdmin() ? 'Admin' : 'Anggota' }}.
{{ auth()->user()->isAdmin() ? 'Halaman yang kamu buka khusus untuk anggota.' : 'Halaman yang kamu buka hanya bisa dilihat oleh pemiliknya atau admin.' }}
@else
Halaman yang kamu buka butuh akun dengan akses yang sesuai.
@endauth
@endsection

@section('actions')
@if (auth()->user()?->isAdmin())
<a href="{{ route('admin.rentals.index') }}" class="btn btn-primary">Ke panel admin</a>
@else
<a href="{{ route('catalog.index') }}" class="btn btn-primary">Ke katalog</a>
@auth
<a href="{{ route('rentals.index') }}" class="btn">Pesanan saya</a>
@endauth
@endif
@endsection

@section('aside')
<aside aria-label="Kemungkinan penyebab" style="flex: 1 1 340px; min-width: 0; padding: 24px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); background: rgba(19,12,16,0.7); backdrop-filter: blur(12px)">
<div class="label-mono" style="color: #B3A0AA">KEMUNGKINAN PENYEBAB</div>
<ul style="margin: 14px 0 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 14px; font-size: 14px; line-height: 1.55; color: #D9C7D0">
<li style="display: flex; gap: 12px"><span aria-hidden="true" style="flex: none; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 10px; background: #2A1720; color: #F4B3D3"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg></span><span>Membuka halaman admin, misalnya <span class="mono" style="font-size: 13px; color: #F6EEF2">/admin</span>.</span></li>
<li style="display: flex; gap: 12px"><span aria-hidden="true" style="flex: none; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 10px; background: #2A1720; color: #F4B3D3"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M4 12h16M4 17h10"></path></svg></span><span>Membuka pesanan milik anggota lain.</span></li>
<li style="display: flex; gap: 12px"><span aria-hidden="true" style="flex: none; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 10px; background: #2A1720; color: #F4B3D3"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg></span><span>Tautan lama yang dibagikan orang lain. Hubungi admin bila kamu merasa ini keliru.</span></li>
</ul>
@auth
<form method="POST" action="{{ route('logout') }}" style="margin-top: 18px">
@csrf
<button type="submit" class="link-btn" style="font-size: 14px">Masuk dengan akun lain</button>
</form>
@endauth
</aside>
@endsection
