@extends('layouts.admin')

@section('title', 'Tambah unit')

@section('content')
<div class="eyebrow">Master data · unit</div>
<h1 class="h1-admin">Tambah unit</h1>

<div class="row" style="margin-top: 28px; gap: 24px">
<div class="grow" style="max-width: 720px">
@include('admin.units._form')
</div>
<aside class="side">
<div class="soft-box">
<p style="margin: 0">Setiap kode mewakili satu paket fisik tetap. Kode wajib unik, termasuk terhadap unit yang sudah diarsipkan, dan tidak dapat diganti setelah disimpan.</p>
<p style="margin: 10px 0 0">Harga, denda, dan kelengkapan disalin ke transaksi saat checkout, sehingga perubahan nanti tidak memengaruhi pesanan lama.</p>
</div>
</aside>
</div>
@endsection
