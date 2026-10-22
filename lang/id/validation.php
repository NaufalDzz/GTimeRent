<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pesan validasi
    |--------------------------------------------------------------------------
    |
    | Hanya aturan yang dipakai aplikasi ini. Aturan lain jatuh ke bahasa
    | fallback (en).
    |
    */

    'accepted' => ':Attribute wajib dicentang.',
    'array' => ':Attribute tidak valid.',
    'boolean' => ':Attribute harus ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Password saat ini salah.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'distinct' => ':Attribute berisi nilai ganda.',
    'email' => ':Attribute harus alamat email yang valid.',
    'exists' => ':Attribute yang dipilih tidak ditemukan.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'integer' => ':Attribute harus bilangan bulat.',
    'max' => [
        'array' => ':Attribute maksimal :max item.',
        'numeric' => ':Attribute maksimal :max.',
        'string' => ':Attribute maksimal :max karakter.',
    ],
    'min' => [
        'array' => ':Attribute minimal :min item.',
        'numeric' => ':Attribute minimal :min.',
        'string' => ':Attribute minimal :min karakter.',
    ],
    'numeric' => ':Attribute harus angka.',
    'password' => [
        'letters' => ':Attribute harus berisi minimal satu huruf.',
        'mixed' => ':Attribute harus berisi huruf besar dan huruf kecil.',
        'numbers' => ':Attribute harus berisi minimal satu angka.',
        'symbols' => ':Attribute harus berisi minimal satu simbol.',
        'uncompromised' => ':Attribute ini pernah bocor di internet. Pilih yang lain.',
    ],
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':Attribute wajib diisi.',
    'required_with' => ':Attribute wajib diisi jika :values diisi.',
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah dipakai.',

    'custom' => [
        'recipient_phone' => [
            'regex' => 'Nomor telepon hanya boleh angka, 8–20 digit, boleh diawali +.',
        ],
        'phone' => [
            'regex' => 'Nomor telepon hanya boleh angka, 8–20 digit, boleh diawali +.',
        ],
        'agree' => [
            'accepted' => 'Centang persetujuan aturan sewa dulu.',
        ],
    ],

    'attributes' => [
        'name' => 'nama',
        'email' => 'email',
        'password' => 'password',
        'current_password' => 'password saat ini',
        'phone' => 'nomor telepon',
        'address' => 'alamat',
        'service_area' => 'area layanan',
        'recipient_name' => 'nama penerima',
        'recipient_phone' => 'telepon penerima',
        'agree' => 'persetujuan',
        'code' => 'kode unit',
        'kind' => 'jenis unit',
        'specs_text' => 'spesifikasi',
        'contents_text' => 'isi paket',
        'package_price' => 'harga paket',
        'daily_fine' => 'denda per hari',
        'condition_status' => 'kondisi',
        'categories' => 'kategori',
        'flat_shipping_fee' => 'ongkir',
        'service_area_text' => 'teks area layanan',
        'payment_method' => 'cara bayar',
        'reason' => 'alasan',
        'notes' => 'catatan',
        'occurred_at' => 'waktu',
        'units' => 'unit',
    ],

];
