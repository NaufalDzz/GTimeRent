<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Area layanan kurir GTR
    |--------------------------------------------------------------------------
    |
    | Daftar area yang dapat dipilih saat checkout. Pilihan area tidak
    | membuktikan koordinat alamat; admin memverifikasi sebelum konfirmasi.
    |
    */

    'service_areas' => ['Bandung', 'Bojongsoang', 'Dayeuhkolot'],

    /*
    |--------------------------------------------------------------------------
    | Zona waktu tampilan
    |--------------------------------------------------------------------------
    |
    | Waktu disimpan dalam UTC dan ditampilkan serta diinput dalam WIB.
    |
    */

    'display_timezone' => 'Asia/Jakarta',

    /*
    |--------------------------------------------------------------------------
    | Kontak admin (fiktif, untuk demo)
    |--------------------------------------------------------------------------
    */

    'admin_contact' => env('GTR_ADMIN_CONTACT', 'WhatsApp admin GTR (demo): 0812-0000-0000'),

    /*
    |--------------------------------------------------------------------------
    | Password akun demo dari seeder (hanya lingkungan lokal)
    |--------------------------------------------------------------------------
    */

    'demo_password' => env('DEMO_PASSWORD'),

];
