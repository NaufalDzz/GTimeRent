@extends('errors.layout')

@section('title', 'Sesi kedaluwarsa')
@section('eyebrow', 'SESI KEDALUWARSA')
@section('code', '419')
@section('heading', 'Halaman ini sudah terlalu lama terbuka.')
@section('message', 'Demi keamanan, formulir perlu dimuat ulang. Kembali ke halaman sebelumnya lalu kirim lagi.')

@section('actions')
<a href="{{ url()->previous() }}" class="btn btn-primary">Muat ulang halaman</a>
@endsection
