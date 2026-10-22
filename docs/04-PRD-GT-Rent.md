# Product Requirements Document - GT-Rent

Versi 1.0 | Gaming Time Rent | 22 Oktober 2026 | Target: besok, 23 Oktober 2026. Jam belum ditentukan.

Nama kelompok: ____________________  
Nama tim: ____________________  
Anggota 1: ____________________  
Anggota 2: ____________________  
Anggota 3: ____________________

## 1. Tujuan produk dan acuan

GT-Rent adalah website rental paket PC gaming dan peripheral untuk penggunaan di rumah penyewa, dengan pengantaran serta penjemputan oleh kurir internal GTR. Produk akademik harus berfungsi lengkap pada aturan inti dan dapat didemonstrasikan oleh tim tiga orang.

Acuan: `01-BRD-GT-Rent.md`, `02-Flowchart.md`, `03-ERD-Awal.md`, serta `05-Katalog-dan-Riset.md`. Harga sewa, denda, dan ongkir pada katalog adalah asumsi demo yang dapat diubah admin. Sumber harga pembelian dan riset produk dicatat terpisah.

Satu unit sewa berupa paket PC dengan monitor pasangannya atau paket peripheral fisik tetap. Fullset menggabungkan dua unit yang sudah ada. Komponen tercatat sebagai kelengkapan paket dan tidak memiliki transaksi sewa terpisah.

## 2. Pengguna dan hak akses

| Kemampuan | Anggota | Admin |
|---|---|---|
| Daftar publik | Menjadi member otomatis | Akun awal melalui seeder/pengelolaan admin |
| Login/logout dan profil sendiri | Ya | Ya |
| Katalog dan pencarian | Ya | Ya |
| Membuat pesanan untuk diri sendiri | Ya | Tidak diperlukan pada MVP |
| Melihat transaksi | Milik sendiri | Seluruh anggota |
| CRUD unit/kategori/anggota | Tidak | Ya |
| Konfirmasi lunas, pengiriman, penerimaan | Tidak | Ya |
| Pembatalan belum dibayar/pengembalian | Menghubungi admin | Mencatat di aplikasi |
| Cetak riwayat | Tidak diwajibkan | Ya |
| Pengaturan ongkir | Tidak | Ya |

Kurir tidak memiliki akun. Hak akses diperiksa pada server untuk setiap request, termasuk akses URL langsung dan identitas transaksi yang diganti.

## 3. Aturan inti

1. Semua fungsi aplikasi memerlukan login, selain halaman masuk/pendaftaran.
2. Register publik selalu membuat role member dan satu profil dalam operasi atomik. Kolom role kiriman publik diabaikan/ditolak.
3. Maksimal dua unit ditahan untuk anggota di seluruh pesanan. Penahanan mencakup pending_payment, paid, in_delivery, dan active.
4. Setiap kode unit mewakili satu paket fisik tetap. Nama boleh sama, tetapi kode wajib unik, termasuk terhadap arsip.
5. Stok ditahan saat checkout valid. Satu unit tidak dapat ditahan dua anggota bersamaan.
6. Harga paket maksimal lima hari ditetapkan per unit. Pengembalian awal tidak mengurangi harga paket.
7. Harga, denda harian, ongkir, identitas unit, kelengkapan, dan alamat disalin ke transaksi saat checkout.
8. Tagihan awal = jumlah harga paket + ongkir antar-jemput sekali per transaksi.
9. Pembayaran tunai/transfer dikonfirmasi manual oleh admin sebelum pengiriman.
10. Kurir GTR melayani area demo Bandung, Bojongsoang, dan Dayeuhkolot tanpa integrasi tarif/geocoding.
11. Seluruh unit pada satu pesanan dikirim, dimulai, dan dikembalikan bersama. Tidak ada penambahan item atau pengembalian sebagian.
12. Masa sewa mulai saat penyewa menerima seluruh unit. Sistem menghitung due_at = received_at + 120 jam.
13. Hanya admin mencatat pengembalian berdasarkan waktu seluruh unit diterima GTR.
14. Denda per unit = ceil(keterlambatan positif / 24 jam) x daily_fine_snapshot. Tepat pada due_at menghasilkan nol.
15. Overdue adalah label turunan dari active + now > due_at. Tidak ada pengembalian otomatis pada hari kelima.
16. Riwayat dan tagihan lama tidak berubah ketika profil atau tarif master diubah.

## 4. Kebutuhan fungsional dan kriteria penerimaan

### FR-01 - Autentikasi dan profil

- Anggota dapat mendaftar dengan nama, email, password, dan konfirmasi password.
- Login akun diarsipkan ditolak. Password disimpan sebagai hash.
- Sistem membuat satu profil bersama akun. user_id pada profil bersifat unique.
- Pengguna hanya mengubah nama/email/password dan profilnya sendiri. Role tidak dapat diubah melalui form profil.
- Nomor telepon dan alamat profil menjadi nilai awal checkout. Perubahan profil berikutnya tidak mengubah transaksi lama.
- Lulus jika register publik tidak dapat membuat admin, tidak muncul profil ganda, dan pengguna tidak dapat mengakses profil orang lain.

### FR-02 - Katalog dan pencarian

- Tampilkan kode unit, nama, jenis paket, spesifikasi, isi tetap, kategori, harga lima hari, denda harian, dan ketersediaan.
- Pencarian menggunakan nama. Kode membedakan set fisik yang bernama sama.
- Paket dapat memiliki beberapa kategori. Kategori arsip tidak tersedia untuk penugasan baru.
- Unit arsip, maintenance, atau ditahan tidak dapat ditambahkan ke pesanan baru.
- Pilihan fullset menambahkan satu unit pc_monitor dan satu unit peripheral yang tersedia, tanpa membuat kode stok fullset baru.
- Keranjang sederhana dapat disimpan dalam session. Sistem baru menahan stok ketika checkout berhasil.
- Lulus jika nama sama/kode berbeda dapat ditampilkan dan pencarian tidak menghilangkan informasi tiap set fisik.

### FR-03 - Checkout dan penahanan stok

- Anggota memilih 1-2 kode unit berbeda dengan quantity satu untuk setiap kode.
- Isi nama penerima, nomor telepon, alamat lengkap, dan pilihan area layanan. Tampilkan daftar barang dan total sebelum submit.
- Total dihitung ulang di server dari master data, bukan dipercaya dari browser.
- Dalam satu transaksi database, kunci anggota dan unit terpilih, hitung ulang unit ditahan lintas pesanan, lalu periksa ketersediaan.
- Jika valid, buat rental pending_payment dan rental_items berisi snapshot. Jika satu item gagal, jangan menahan sebagian pesanan.
- Tolak request berisi kode duplikat, unit tidak tersedia, lebih dari dua item, atau kuota yang akan melebihi dua.
- Lulus jika dua request bersamaan tidak dapat memesan unit sama atau melampaui kuota anggota.

### FR-04 - Pembayaran dan pembatalan

- Detail pesanan menampilkan nomor transaksi, daftar unit, harga snapshot, ongkir, total, dan status pembayaran.
- Anggota melakukan pembayaran di luar aplikasi. Tidak ada gateway atau unggah bukti wajib pada MVP.
- Admin hanya dapat mengonfirmasi pending_payment menjadi paid dengan metode cash/transfer. Simpan actor dan waktu konfirmasi.
- Sistem menolak pengulangan konfirmasi dengan pesan jelas. Pengulangan tidak boleh menambah tagihan atau mengubah timestamp awal.
- Admin dapat membatalkan hanya pending_payment, wajib memberi alasan, dan melepas seluruh unit atomik.
- Tidak ada pembatalan otomatis, refund otomatis, atau pembatalan setelah paid dalam versi pertama.

### FR-05 - Pengiriman dan awal sewa

- Admin hanya dapat mengirim rental paid. Catat keberangkatan, dispatched_by, dan ubah menjadi in_delivery.
- Admin mencatat received_at sebenarnya ketika seluruh unit diterima penyewa beserta receipt_recorded_by, lalu sistem menetapkan due_at dan status active.
- received_at tidak boleh sebelum keberangkatan atau di masa depan. Timestamp pencatatan disimpan terpisah.
- Waktu pengiriman tidak memakan masa sewa lima hari.
- Data alamat dan ongkir memakai snapshot checkout. Perubahan tarif global hanya berlaku untuk pesanan baru.

### FR-06 - Pengembalian dan denda

- Anggota menghubungi admin di luar aplikasi untuk penjemputan. Alur ini tidak memerlukan tiket penjemputan baru.
- Selama perangkat masih pada kurir kembali, status tetap active dan unit masih ditahan.
- Admin membuka rental active, memeriksa kelengkapan, dan memasukkan returned_at sebenarnya beserta catatan opsional.
- returned_at harus >= received_at dan <= waktu sekarang. Waktu pencatatan dapat lebih belakangan.
- Hitung denda memakai tarif snapshot setiap unit, bukan tarif master terbaru.
- Dalam satu transaksi database simpan late_days/fine_amount, returned_at, return_recorded_at, returned_by, lalu status returned.
- Pemanggilan ulang tidak boleh menutup dua kali atau menggandakan denda. Anggota ditolak meskipun mengetahui URL endpoint.
- Unit yang siap pakai dapat dipesan lagi. Jika perlu perawatan, admin menandainya maintenance sebelum menyewakannya kembali.
- Aplikasi menampilkan denda final terpisah dari tagihan awal. Admin menangani pelunasan denda dan biaya kerusakan secara manual. Versi ini tidak memakai kalkulator kerusakan atau gateway tambahan.

### FR-07 - Pengelolaan master oleh admin

- Unit: tambah, ubah, arsip, set ready/maintenance, atur harga dan denda, isi spesifikasi/kelengkapan, serta pilih banyak kategori.
- Larang perubahan jenis/kelengkapan dan pengarsipan unit yang masih ditahan. Perubahan harga hanya berdampak pada transaksi baru.
- Kategori: tambah, ubah, dan arsip dengan tetap mempertahankan referensi serta keterbacaan riwayat.
- Anggota: tambah, ubah, dan arsip. Sistem membuat satu profil otomatis. Admin belum dapat mengarsipkan akun yang masih menahan unit.
- Admin tidak dapat mengarsipkan dirinya sendiri. Register publik tidak dapat mengubah role.
- Admin mengarsipkan data historis. Transaksi tidak memiliki tombol hapus.

### FR-08 - Daftar, riwayat, dan cetak

- Anggota melihat pesanan miliknya, unit ditahan, jatuh tempo, estimasi denda aktif, dan denda final.
- Admin melihat semua pesanan dengan filter anggota, status, dan periode tanggal dibuat. Daftar pinjaman aktif menampilkan label terlambat.
- Halaman cetak admin memuat identitas anggota, nomor transaksi, kode/nama snapshot unit, waktu sewa/kembali, harga paket, ongkir, dan denda terpisah.
- Cetak dapat menggunakan print browser dengan CSS cetak. Navigasi/tombol disembunyikan dan judul serta tanggal cetak dicantumkan.
- Data anggota lain tidak muncul dalam query, halaman detail, atau keluaran cetak anggota.

### FR-09 - Pengaturan operasional

- Satu pengaturan ongkir flat antar-jemput berlaku untuk semua area demo, dengan nilai awal Rp75.000 per transaksi.
- Tampilkan keterangan area Bandung dan sekitar Telkom: Bojongsoang/Dayeuhkolot. Admin memverifikasi alamat sebelum mengonfirmasi pembayaran karena pilihan area tidak membuktikan koordinat alamat.
- Nominal baru berlaku untuk checkout berikutnya. Ongkir tidak dikalikan jumlah unit atau ditagih ulang untuk penjemputan.

## 5. Status dan transisi yang diizinkan

| Status awal | Aksi | Status akhir | Actor dan syarat |
|---|---|---|---|
| Belum ada | Checkout | pending_payment | Anggota. Seluruh input, stok, dan kuota valid |
| pending_payment | Konfirmasi lunas | paid | Admin mencatat metode cash/transfer |
| pending_payment | Batalkan | cancelled | Admin mengisi alasan wajib, lalu sistem melepas unit |
| paid | Catat pengiriman | in_delivery | Admin mencatat waktu keberangkatan yang valid |
| in_delivery | Catat diterima penyewa | active | Admin mencatat received_at yang valid, lalu sistem menghitung due_at |
| active | Catat diterima GTR | returned | Admin mencatat returned_at yang valid, lalu sistem menghitung denda |

Semua lompatan status lain ditolak. Returned dan cancelled merupakan status terminal. Estimasi keterlambatan tidak mengubah status. Unit masih ditahan pada empat status pertama yang bukan terminal.

## 6. Validasi penting

| Area | Validasi wajib |
|---|---|
| Nama/email/password | Nama 2-100 karakter. Email valid, panjang <=254, dan unique. Password >=8 karakter dengan konfirmasi yang sesuai |
| Profil/checkout | Telepon 8-20 karakter digit dengan awalan + opsional. Alamat 10-1000 karakter. Nama penerima 2-100 karakter. Area harus dari daftar server |
| Unit | Kode unique, sepanjang 3-30 karakter huruf/angka/tanda - atau _. Nama 2-120 karakter. Jenis valid, spesifikasi/isi wajib, dan minimal satu kategori aktif |
| Harga/ongkir/denda | Integer rupiah 0-1.000.000.000. Nilai negatif atau desimal ditolak. Server menghitung total sendiri dan mengabaikan total kiriman browser |
| Checkout | Array 1-2 unit_ids yang distinct, ada, dan aktif. Server menghitung kuota dalam transaksi |
| Waktu | dispatched_at >= payment_confirmed_at, received_at >= dispatched_at, returned_at >= received_at, dan waktu kejadian <= sekarang |
| Pembatalan | Status pending_payment. Alasan wajib sepanjang 3-255 karakter. Actor harus admin |
| Arsip | Transaksi tidak dihapus dan kode/email tidak dipakai ulang. Anggota/unit yang masih menahan stok tidak dapat diarsipkan |

Semua pesan kesalahan berbahasa Indonesia. Form mempertahankan input aman yang telah diisi dan melakukan escape pada konten katalog/alamat, tanpa menampilkan kembali password. Terapkan CSRF pada seluruh perubahan data.

## 7. Halaman minimum

| Halaman | Isi/tindakan inti |
|---|---|
| Login dan register | Form autentikasi, validasi, pesan kesalahan |
| Profil sendiri | Identitas, kontak, alamat default |
| Katalog dan detail | Cari nama, kategori, isi paket tetap, kode, harga/denda, ketersediaan |
| Checkout | 1-2 unit, alamat/area, rincian tagihan, konfirmasi pemesanan |
| Pesanan saya/detail | Status, barang, tagihan, waktu, denda, petunjuk kontak admin |
| Admin dashboard/transaksi | Daftar semua status, filter, daftar unit dipinjam dan terlambat |
| Admin detail transaksi | Tombol transisi yang sah, pembayaran, pengiriman, penerimaan, pengembalian/cancel |
| Admin master | CRUD unit, kategori, anggota, serta pengaturan ongkir |
| Admin cetak riwayat | Laporan siap print berdasarkan filter yang dipilih |

## 8. Arsitektur dan data

Rancangan menggunakan PHP 8.4, Laravel 13, Blade server-rendered, dan MySQL 8/InnoDB. Versi ini tidak memerlukan SPA atau integrasi layanan berbayar. Versi PHP lokal yang diperiksa mendukung Laravel 13. Tim masih perlu memverifikasi server MySQL pada awal implementasi. SQLite dapat dipakai untuk uji awal, sedangkan perilaku konkurensi perlu diuji terpisah pada database target.

Delapan tabel inti: users, profiles, categories, units, category_unit, rentals, rental_items, settings. Kamus data lengkap dan constraint ada pada ERD awal. Sistem menyimpan komponen sebagai teks kelengkapan pada unit dan snapshot item, tanpa tabel stok per komponen.

| Bagian | Tanggung jawab | Ketergantungan |
|---|---|---|
| Domain PHP | Kebijakan kuota, tenggat 120 jam, pembulatan denda, transisi sah | PHP/DateTimeImmutable, tanpa Eloquent, Request, DB atau Auth facade |
| Application/use case | Checkout, konfirmasi pembayaran, kirim, mulai sewa, kembali, batal | Kontrak RentalRepository/UnitRepository, TransactionRunner, Clock, DTO actor/data |
| Infrastructure | Implementasi Eloquent, transaksi/kunci database, pemetaan entity/DTO | Laravel dan database |
| HTTP/presentation | Form Request, autentikasi/policy, controller tipis, Blade/cetak | Laravel untuk menerjemahkan input dan hasil use case |

Service provider menghubungkan kontrak dengan implementasi. Jangan mengembalikan Eloquent Model atau Illuminate Collection melalui kontrak inti. Gunakan fake repository dan fake clock untuk uji aturan tanpa database.

Checkout dan perubahan penahanan stok wajib atomik. Pada MySQL, kunci anggota lalu unit dalam urutan id yang konsisten. Hitung ulang kuota setelah kunci diperoleh, lalu tulis seluruh perubahan. Jika gagal atau terjadi deadlock, lakukan rollback dan retry terbatas. Jika tetap gagal, tampilkan pesan aman agar pengguna dapat mencoba kembali. Aturan bisnis tetap berada di use case, bukan query pada controller.

Referensi resmi: [Laravel 13 release policy](https://laravel.com/docs/13.x/releases), [service container](https://laravel.com/docs/13.x/container), [database transactions](https://laravel.com/docs/13.x/database), [query locking](https://laravel.com/docs/13.x/queries#pessimistic-locking), [authorization](https://laravel.com/docs/13.x/authorization).

## 9. Data demo dan contoh hitung

Katalog terpilih dan sumber ada pada dokumen 05 serta `data-demo.json`. Harga berikut murni angka simulasi akademik, dapat diatur admin, dan bukan rekomendasi tarif komersial.

| Produk contoh | Harga paket <=5 hari | Denda/unit/hari |
|---|---|---|
| PC Mid + monitor IPS | Rp500.000 | Rp100.000 |
| PC High + monitor OLED | Rp1.200.000 | Rp200.000 |
| A1 peripheral lengkap high-end | Rp350.000 | Rp50.000 |
| A2 peripheral lengkap mid-range | Rp125.000 | Rp20.000 |
| Ongkir antar-jemput per pesanan | Rp75.000 | Tidak menjadi unit denda |

Fullset mid: Rp500.000 + Rp125.000 + Rp75.000 = Rp700.000 dibayar di awal. Jika penyewa menerima seluruh unit pada 22 Oktober 2026 pukul 10.00 WIB, jatuh temponya 27 Oktober pukul 10.00 WIB. Kembali tepat waktu: denda Rp0. Kembali 27 Oktober pukul 10.01: satu hari x (Rp100.000 + Rp20.000) = Rp120.000. Kembali 28 Oktober pukul 10.01: dua hari = Rp240.000. Gunakan clock yang dapat dikendalikan pada pengujian agar tidak perlu mengubah jam sistem komputer.

Seeder yang direncanakan membuat admin, sedikitnya tiga anggota dengan profil, kategori, pengaturan, dua unit bernama sama dengan kode berbeda, PC high/OLED, dan paket peripheral yang memiliki mousepad. Seluruh identitas akun demo harus berupa data fiktif yang jelas dan password demo dikonfigurasi lewat lingkungan lokal. Skenario pinjaman aktif/terlambat dapat dibuat sebagai fixture terpisah dengan waktu referensi yang terdokumentasi.

## 10. Pengujian dan UAT

| ID | Skenario | Hasil yang harus dipenuhi |
|---|---|---|
| T01 | Register dan manipulasi role publik | Role member, satu profil, dan password di-hash |
| T02 | Edit profil/URL milik pengguna lain | Akses ditolak. Pengguna tetap dapat mengubah profil sendiri |
| T03 | Kode unit duplikat vs nama sama | Kode ditolak. Nama sama diizinkan |
| T04 | Banyak kategori dan pencarian nama | Unit dapat masuk beberapa kategori dan ditemukan |
| T05 | Checkout fullset mid | Dua item dengan total Rp700.000 dan ongkir sekali |
| T06 | Anggota punya dua unit pending lalu pesan lagi | Ditolak lintas pesanan |
| T07 | Dua pengguna memesan unit sama bersamaan | Satu sukses dan lainnya gagal aman |
| T08 | Dua checkout anggota sama bersamaan | Total unit ditahan tidak melewati dua |
| T09 | Satu item checkout tidak tersedia | Seluruh checkout rollback tanpa penahanan parsial |
| T10 | Batalkan pending_payment | Status cancelled dan semua unit tersedia kembali |
| T11 | Batalkan paid atau ulang konfirmasi pembayaran | Request ditolak tanpa menggandakan proses |
| T12 | Kirim sebelum lunas atau lompat status | Ditolak |
| T13 | Terima penyewa belakangan dari keberangkatan | Lima hari mulai dari received_at, bukan checkout |
| T14 | Kembali sebelum/tepat due_at | Harga paket tetap dan denda nol |
| T15 | Kembali +1 detik, +24 jam, +24 jam 1 detik | Hari denda 1, 1, 2 |
| T16 | Tarif denda unit diubah setelah checkout | Pengembalian memakai tarif snapshot |
| T17 | Ongkir/profil/nama unit master diubah | Snapshot transaksi lama tidak berubah |
| T18 | Anggota mencoba aksi/URL pengembalian | Ditolak server |
| T19 | returned_at sebelum received_at/di masa depan | Request ditolak. Transaksi dan stok tetap semula |
| T20 | Pencatatan kembali terlambat oleh admin | Denda memakai waktu GTR menerima barang sebenarnya |
| T21 | Pengembalian dikirim dua kali | Tidak menggandakan denda atau pelepasan stok |
| T22 | Daftar/cetak riwayat dan filter admin | Admin melihat semua data sesuai filter. Anggota hanya melihat miliknya |
| T23 | Arsip unit/anggota yang masih ditahan | Arsip ditolak. Riwayat selesai tetap terbaca setelah arsip |
| T24 | Unit maintenance/arsip dipilih lewat request manual | Ditolak walau id diketahui |
| T25 | Uang negatif/desimal atau total browser dimanipulasi | Server menolak input tidak valid atau menghitung ulang total. Tarif server tidak berubah |
| T26 | Riset, diagram, dan model katalog | Label demo, sumber, hitungan unit, status, dan kamus data konsisten |

Uji domain mencakup kuota, transisi, tenggat, dan batas pembulatan dengan fake clock. Uji fitur mencakup policy, validasi, transaksi database, serta snapshot. Uji konkurensi dijalankan pada database target. UAT dilakukan oleh perwakilan anggota dan admin menggunakan skenario nyata di atas.

UAT aplikasi belum dijalankan. Paket ini berisi spesifikasi dan rencana pengujian. Setelah implementasi, catat hasil aktual, penguji, tanggal, bukti, dan retest. Tandai lulus hanya setelah skenario dijalankan.

## 11. Prioritas, pembagian tim, dan pengumpulan

Semua kebutuhan soal 1-20 merupakan target wajib pengumpulan. Prioritas P0 meliputi alur wajib, migration/seeder, dan bukti pengumpulan. Varian katalog B/H tambahan, pemolesan visual, dan filter ekstra masuk P1 yang dapat ditunda. Gateway, kalender reservasi, dan komponen bebas berada di luar versi pertama.

Urutan implementasi: autentikasi/profil/master, katalog/checkout atomik, alur admin sampai pengembalian/denda, laporan/cetak, lalu pengujian dan kelengkapan pengumpulan. Tim dapat mengelompokkan skenario pengujian agar efisien. Setiap aturan wajib tetap harus diperiksa sebelum dinyatakan lulus. Jadwal ini adalah target, bukan jaminan seluruh implementasi selesai dalam satu hari.

Pembagian tiga orang menggunakan label Anggota 1/2/3 sampai nama diisi:

1. Akun, profil, master unit/kategori, dan migration dasar.
2. Domain transaksi, snapshot, kuota, status, dan denda.
3. Integrasi tampilan, laporan/cetak, dokumentasi, dan koordinasi UAT.

Satu anggota mengintegrasikan perubahan dan menyelesaikan konflik. Rincian ada pada dokumen 06.

Hasil akhir tugas: README identitas/fitur/skema/video, BRD, flowchart/ERD final, PRD, desain, implementasi frontend-backend, migration/seeder, hasil UAT, manual book, dan file terkait di GitLab. Dokumen ini belum mengimplementasikan atau menguji aplikasi dan belum melakukan push. Identitas tim sengaja dibiarkan untuk diisi pengguna.

## 12. Pemetaan spesifikasi soal

| Butir soal | Cakupan PRD / dokumen |
|---|---|
| 1 | README dan kelengkapan pengumpulan pada dokumen 06 |
| 2-6 | FR-01 dan matriks hak akses |
| 7-9 | FR-02/FR-07, serta ERD units dan category_unit |
| 10 | FR-07 CRUD admin dengan pelestarian riwayat |
| 11 | FR-03 untuk kuota lintas status dan transaksi |
| 12 | FR-05/FR-06 untuk 120 jam dan pembulatan denda |
| 13 | FR-06 untuk policy admin dan waktu pengembalian sebenarnya |
| 14-16 | FR-08 daftar, riwayat sendiri, dan cetak admin |
| 17 | Bagian 6 validasi |
| 18-19 | ERD delapan tabel, migration/seeder yang direncanakan |
| 20 | Urutan artefak, implementasi, UAT, manual, dokumentasi, GitLab |

## 13. Batas versi dan risiko terbuka

Versi pertama memakai paket tetap, pengembalian bersama, pembayaran/kurir manual, dan satu tarif ongkir demo. Reservasi tanggal mendatang, komponen bebas, refund otomatis, perpanjangan otomatis, live tracking, dan kalkulator kerusakan berada di luar versi ini.

Unit berarti paket berkode unik sesuai keputusan proyek. Hal ini perlu dijelaskan dalam presentasi karena paket PC memuat dua perangkat fisik. Angka harga pasar/historis dan rekomendasi komunitas tidak boleh dipresentasikan sebagai bukti keuntungan atau jaminan performa.

Tim masih perlu melengkapi jam pengumpulan dan identitas pada dokumen. Singkatan F&B mengikuti format pengajar. Rencana produk menggunakan frontend dan backend.
