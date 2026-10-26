@extends('layouts.base')

@php
    $menu = [
        ['route' => 'admin.rentals.index', 'match' => ['admin.rentals.index', 'admin.rentals.show'], 'label' => 'Transaksi', 'icon' => '<path d="M4 6h16M4 12h16M4 18h10"></path>'],
        ['route' => 'admin.units.index', 'match' => ['admin.units.*'], 'label' => 'Unit', 'icon' => '<rect x="2" y="4" width="20" height="13" rx="2"></rect><path d="M8 21h8M12 17v4"></path>'],
        ['route' => 'admin.categories.index', 'match' => ['admin.categories.*'], 'label' => 'Kategori', 'icon' => '<path d="M3 7l9-4 9 4-9 4z"></path><path d="M3 12l9 4 9-4M3 17l9 4 9-4"></path>'],
        ['route' => 'admin.members.index', 'match' => ['admin.members.*'], 'label' => 'Anggota', 'icon' => '<circle cx="9" cy="8" r="3.5"></circle><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6"></path>'],
        ['route' => 'admin.settings.edit', 'match' => ['admin.settings.*'], 'label' => 'Ongkir', 'icon' => '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"></path><circle cx="7" cy="18" r="1.8"></circle><circle cx="17" cy="18" r="1.8"></circle>'],
        ['route' => 'admin.rentals.print', 'match' => ['admin.rentals.print'], 'label' => 'Cetak riwayat', 'icon' => '<path d="M6 9V3h12v6M6 18H4v-7h16v7h-2"></path><rect x="6" y="14" width="12" height="7"></rect>'],
    ];
@endphp

@section('body')
<div @class(['gtr', 'admin-shell', 'admin-detail' => View::hasSection('compact-admin')])>
@hasSection('compact-admin')
<header class="topbar no-print">
<a href="{{ route('admin.rentals.index') }}" class="logo">/GTime Rent</a>
<a href="{{ route('admin.rentals.index') }}" class="small">← Semua transaksi</a>
</header>
@else
<aside class="admin-side no-print">
<a href="{{ route('admin.rentals.index') }}" class="logo" style="display: block; padding: 0 12px">/GTime Rent</a>
<nav aria-label="Menu admin" class="admin-nav">
@foreach ($menu as $entry)
<a href="{{ route($entry['route']) }}" @if (request()->routeIs(...$entry['match'])) aria-current="page" @endif><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $entry['icon'] !!}</svg>{{ $entry['label'] }}</a>
@endforeach
</nav>
<div class="admin-user">
<a href="{{ route('profile.edit') }}" class="avatar" style="width: 36px; height: 36px; background: #F6EEF2; color: #0A0608; font-size: 12px; font-weight: 700" aria-label="Profil {{ auth()->user()->name }}">{{ auth()->user()->initials() }}</a>
<div style="flex: 1; min-width: 0"><div class="small" style="font-weight: 500">{{ auth()->user()->name }}</div><div class="tiny text-dim">Admin</div></div>
<form method="POST" action="{{ route('logout') }}">
@csrf
<button type="submit" class="link-btn">Keluar</button>
</form>
</div>
</aside>
@endif

<main class="admin-main">
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
