@extends('layouts.base')

@section('body')
<div class="gtr" style="position: relative; overflow: hidden; background: radial-gradient(120% 90% at 10% 0%, #2A0C1A 0%, #0A0608 60%)">
<div aria-hidden="true" style="position: absolute; width: 2200px; height: 2200px; left: -900px; top: -1960px; border-radius: 50%; background: radial-gradient(circle at 50% 100%, #1A0710 0%, #0A0306 60%); box-shadow: 0 0 140px 20px rgba(226,88,156,0.35), inset 0 -18px 50px rgba(255,190,222,0.5)"></div>

<header class="topbar" style="position: relative; border: 0; background: transparent">
<a href="{{ route('home') }}" class="logo">/GTime Rent</a>
@auth
<a href="{{ route('profile.edit') }}" class="avatar" aria-label="Profil {{ auth()->user()->name }}">{{ auth()->user()->initials() }}</a>
@endauth
</header>

<main class="wrap" style="position: relative; max-width: 1180px; padding-top: clamp(96px, 18vh, 200px); display: flex; flex-wrap: wrap; gap: 48px; align-items: flex-end">
<div style="flex: 1 1 420px; min-width: 0">
<div class="eyebrow">@yield('eyebrow')</div>
<div aria-hidden="true" style="margin-top: 12px; font-family: Poppins, sans-serif; font-weight: 800; font-size: clamp(120px, 16vw, 220px); line-height: 0.9; letter-spacing: -0.05em; background: linear-gradient(180deg, #FFFFFF 0%, #E7A3C5 100%); -webkit-background-clip: text; background-clip: text; color: transparent">@yield('code')</div>
<h1 style="margin: 20px 0 0; font-family: Poppins, sans-serif; font-weight: 700; font-size: clamp(26px, 3vw, 38px); line-height: 1.15">@yield('heading')</h1>
<p style="margin: 12px 0 0; max-width: 520px; font-size: 16px; line-height: 1.6; color: #D9C7D0">@yield('message')</p>
<div class="hstack" style="margin-top: 28px">
@yield('actions')
</div>
</div>
@yield('aside')
</main>
</div>
@endsection
