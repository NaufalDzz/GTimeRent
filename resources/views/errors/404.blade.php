@extends('errors.layout')

@section('title', 'Tidak ditemukan')
@section('eyebrow', 'TIDAK DITEMUKAN')
@section('code', '404')
@section('heading', 'Halaman atau unit ini tidak ada.')
@section('message', 'Tautannya mungkin salah ketik, atau unit sudah diarsipkan admin. Cari paket lain di katalog.')

@section('actions')
<a href="{{ route('catalog.index') }}" class="btn btn-primary">Ke katalog</a>
<a href="{{ route('home') }}" class="btn">Beranda</a>
@endsection
