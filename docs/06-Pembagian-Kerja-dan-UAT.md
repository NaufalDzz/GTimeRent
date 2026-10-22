# Pembagian kerja dan panduan verifikasi

Target pengumpulan: besok, 23 Oktober 2026. Jam pasti belum diketahui. Nama anggota dan kelompok diisi sendiri. Jadwal berikut adalah target kerja relatif, bukan janji bahwa seluruh implementasi dapat selesai tanpa kendala.

## 1. Urutan pengerjaan

BRD dan keputusan unit -> flowchart/ERD -> PRD -> desain sederhana -> implementasi frontend/backend -> pengujian dan UAT -> manual book/README/video -> pemeriksaan paket dan pengumpulan GitLab.

Dokumen sudah disiapkan pada paket ini. Aplikasi, migration, seeder, dan hasil UAT aplikasi masih harus dibuat/dijalankan. Singkatan F&B mengikuti instruksi pengajar; rencana teknis mengartikannya sementara sebagai frontend dan backend.

## 2. Batas kepemilikan tiga anggota

| Penanggung jawab | Lingkup utama | Hasil yang diserahkan |
|---|---|---|
| Anggota 1: __________ | Auth/profil, CRUD unit/kategori/anggota, pengaturan, migration master dan seeder katalog | Akun/role benar, data master, kode unik, kategori jamak, data demo |
| Anggota 2: __________ | Domain dan use case transaksi, migration rental/items, checkout, snapshot, kuota/stock, transisi, denda | Alur pesanan sampai kembali, aturan bisnis teruji |
| Anggota 3: __________ | Layout/UI integrasi, laporan/cetak, desain, README/manual/video, koordinasi UAT | Tampilan konsisten, laporan, bukti pengujian dan paket pengumpulan |

Sepakati skema ERD, status, nama route/use case, dan field kontrak sebelum bekerja. Satu anggota menjadi integrator untuk file route, provider binding, navigation/layout, dan migration order. Pemilik lain mengirim perubahan terfokus; jangan menimpa file bersama tanpa koordinasi.

## 3. Prioritas dengan tenggat singkat

| Prioritas | Isi |
|---|---|
| P0 - wajib untuk alur | Login/register/profil, role, master, katalog, checkout atomik, maksimal dua unit, pembayaran, pengiriman, awal sewa, pengembalian admin, denda, riwayat/cetak |
| P0 - wajib untuk pengumpulan | Migration, seeder, README, BRD/diagram/PRD, desain, hasil UAT aktual, manual book, gambar skema, video demo, file proyek |
| P1 - bila waktu cukup | Lebih banyak varian B/H, pemolesan visual lanjutan, filter tambahan di luar kebutuhan laporan |
| Ditunda | Gateway, API kurir/GPS, komponen bebas, reservasi kalender, refund otomatis, kalkulator kerusakan |

Mulai seeder dengan lima kode inti: dua PC Mid bernama sama, satu PC High, A1, dan A2. Variasi B/H adalah data opsional; fitur kode/nama, paket lengkap/peripheral saja, dan dua kategori tetap dapat didemokan dari data inti.

Target relatif: tahap awal menyepakati kontrak dan setup; bagian terbesar untuk P0 alur; setelah itu bekukan fitur dan gunakan sisa waktu untuk UAT, perbaikan, screenshot, serta video. Sisakan sekurang-kurangnya dua jam sebelum jam pengumpulan yang akhirnya diketahui. Jika fitur wajib belum lulus, catat kekurangannya dengan jujur; jangan mengganti hasil tes dengan klaim lulus.

## 4. Bukti UAT yang perlu diisi

Gunakan skenario T01-T26 pada PRD. Kelompokkan uji yang berkaitan agar efisien: akun/hak akses, katalog/master, stok/checkout, alur admin, perhitungan waktu/denda, laporan, dan arsip. Skenario konkurensi memerlukan dua sesi/request pada database target; pengujian domain dengan fake repository saja tidak membuktikan stok aman pada database.

| ID PRD | Penguji dan waktu | Hasil yang diharapkan | Hasil aktual | Lulus/gagal | Bukti dan retest |
|---|---|---|---|---|---|
| T01-T04 | Diisi setelah pelaksanaan | Auth, profil, katalog, kode/kategori benar | Belum diuji | Belum diuji | Diisi setelah pelaksanaan |
| T05-T09 | Diisi setelah pelaksanaan | Total, kuota lintas pesanan, atomisitas, konkurensi | Belum diuji | Belum diuji | Diisi setelah pelaksanaan |
| T10-T13 | Diisi setelah pelaksanaan | Cancel, pembayaran, transisi, mulai sewa | Belum diuji | Belum diuji | Diisi setelah pelaksanaan |
| T14-T21 | Diisi setelah pelaksanaan | Denda, snapshot, timestamp, return admin | Belum diuji | Belum diuji | Diisi setelah pelaksanaan |
| T22-T26 | Diisi setelah pelaksanaan | Cetak, arsip, validasi, konsistensi dokumen | Belum diuji | Belum diuji | Diisi setelah pelaksanaan |

Setiap kelompok di atas tetap perlu bukti hasil tiap skenario. T26 yang menyangkut dokumen dapat diperiksa sebelum aplikasi tersedia; hasil pengujian aplikasi tetap belum ada sampai dijalankan.

## 5. Isi manual book dan README

Manual anggota: register/login, profil, mencari kode/nama paket, menghitung dua unit, checkout/tagihan, pembayaran manual, memantau status, mengatur penjemputan melalui admin, dan membaca denda/riwayat.

Manual admin: akun demo, master dan pengaturan, konfirmasi lunas, kirim, catat penerimaan sebenarnya, kembali/denda, pembatalan pending, arsip, maintenance, serta cetak laporan.

README final: nama kelompok; nama tim dan tiga anggota; judul proyek; daftar fitur; persyaratan/setup lokal; konfigurasi lingkungan contoh tanpa kredensial pribadi; cara menjalankan migration/seeder; akun demo yang aman; gambar ERD/skema database; tautan/lokasi rekaman demo; dokumen terkait; batas versi yang nyata.

## 6. Skenario video singkat

1. Perkenalkan masalah, area layanan, dan definisi paket satu/dua unit.
2. Login anggota, cari katalog, tampilkan nama sama dengan kode berbeda serta kategori jamak.
3. Buat fullset: dua unit, tagihan demo Rp700.000 untuk kelas mid.
4. Tunjukkan penolakan pemesanan ketiga dan unit yang sudah ditahan.
5. Login admin, konfirmasi pembayaran, kirim, dan catat penerimaan penyewa.
6. Demonstrasikan pengembalian tepat waktu dan contoh terlambat melalui fixture/clock pengujian yang dijelaskan, bukan manipulasi jam sistem diam-diam.
7. Tampilkan cetak riwayat dan batas akses anggota lain.
8. Jelaskan pemisahan aturan bisnis, kontrak repository, adapter Laravel, migration, dan seeder.

## 7. GitLab dan pengumpulan

Repositori belum dibuat atau dipush oleh paket dokumen ini. Gunakan branch/commit netral tanpa kata terlarang pada instruksi proyek. Simpan versi sejak awal, review diff dan jalankan pemeriksaan dependensi yang diwajibkan instruksi sebelum commit/push, lalu sertakan dokumen, diagram, dan bukti. Jangan menyertakan .env pribadi, vendor/node_modules, token, kredensial GitLab, atau data pelanggan nyata.

Pastikan README tidak mengklaim fitur/UAT yang belum benar-benar selesai. Untuk pengumpulan final, cocokkan kembali butir soal 1-20 terhadap tabel pemetaan pada PRD.
