@extends('layouts.base')

@section('body')
<div class="gtr">
<header class="topbar no-print">
<div class="topbar-left">
<a href="{{ auth()->user()?->isAdmin() ? route('admin.rentals.index') : route('home') }}" class="logo">/GTime Rent</a>
<nav aria-label="Navigasi anggota" class="nav-pills">
<a href="{{ route('catalog.index') }}" class="nav-pill" @if (request()->routeIs('catalog.*')) aria-current="page" @endif>Katalog</a>
@auth
@if (auth()->user()->isAdmin())
<a href="{{ route('admin.rentals.index') }}" class="nav-pill">Dashboard</a>
@else
<a href="{{ route('rentals.index') }}" class="nav-pill" @if (request()->routeIs('rentals.*')) aria-current="page" @endif>Pesanan saya</a>
@endif
@endauth
</nav>
</div>
<div class="topbar-right">
@auth
@if (auth()->user()->isMember())
<span class="quota-chip hide-sm">KUOTA <b>{{ $navHeld }}/{{ $navMaxUnits }}</b> UNIT</span>
<a href="{{ route('checkout.create') }}" class="icon-btn" aria-label="Keranjang, {{ $navCartCount }} unit">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 6h15l-1.5 9h-12z"></path><path d="M6 6 5 3H2"></path><circle cx="9" cy="20" r="1.3"></circle><circle cx="18" cy="20" r="1.3"></circle></svg>
@if ($navCartCount > 0)<span class="count">{{ $navCartCount }}</span>@endif
</a>
@endif
<a href="{{ route('profile.edit') }}" class="avatar" aria-label="Profil {{ auth()->user()->name }}" @if (request()->routeIs('profile.*')) aria-current="page" @endif>{{ auth()->user()->initials() }}</a>
@else
<a href="{{ route('login') }}" class="btn btn-sm">Masuk</a>
<a href="{{ route('login', ['tab' => 'daftar']) }}" class="btn btn-sm btn-primary">Daftar</a>
@endauth
</div>
</header>

<main class="wrap @yield('wrap-class')">
@if (session('status'))
<div class="flash" role="status" style="margin-bottom: 24px">{{ session('status') }}</div>
@endif
@if (session('error'))
<div class="alert" role="alert" style="margin-bottom: 24px">{{ session('error') }}</div>
@endif
@yield('content')
</main>
</div>
@endsection
