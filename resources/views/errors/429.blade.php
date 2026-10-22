@extends('errors.layout')

@section('title', 'Terlalu banyak percobaan')
@section('eyebrow', 'TUNGGU SEBENTAR')
@section('code', '429')
@section('heading', 'Terlalu banyak percobaan.')
@section('message', 'Tunggu sekitar satu menit, lalu coba lagi.')

@section('actions')
<a href="{{ route('login') }}" class="btn btn-primary">Kembali ke halaman masuk</a>
@endsection
