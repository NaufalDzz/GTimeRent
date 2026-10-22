# Business Requirements Document (BRD)

## GT-Rent — Gaming Time Rent

| Informasi | Keterangan |
|---|---|
| Versi | 1.0 — dasar dokumen tugas |
| Tanggal | 22 Oktober 2026 |
| Jenis proyek | Tugas akademik berupa website rental PC gaming dengan proses bisnis realistis |
| Tim | Tiga anggota; identitas kelompok dan anggota belum diberikan |
| Target pengumpulan | 23 Oktober 2026; jam pengumpulan belum diketahui |
| Teknologi yang ditetapkan | PHP Laravel |
| Wilayah layanan demo | Bandung, Bojongsoang, Dayeuhkolot, termasuk sekitar Telkom |

## 1. Latar belakang

Biaya awal kepemilikan PC gaming dan peripheral premium dapat menjadi hambatan bagi pengguna yang hanya membutuhkan perangkat untuk penggunaan sementara. GT-Rent mengusulkan penyewaan paket PC gaming dan peripheral sebagai alternatif akses perangkat tanpa membayar seluruh biaya pembeliannya di awal.

Sebagai konteks industri, laporan TrendForce tanggal 30 September 2026 memproyeksikan kenaikan harga kontrak conventional DRAM sebesar 10–15% dan NAND Flash sebesar 15–20% secara kuartalan pada kuartal IV 2026. Laporan tersebut juga membahas tekanan biaya komponen pada pasar PC. Angka ini merupakan proyeksi harga komponen, bukan ukuran kenaikan harga seluruh PC gaming di Indonesia.

Sumber: [TrendForce — AI Server Demand Sustains Memory Contract Price Increases in 4Q26, While Consumer-Side Pressure Persists](https://www.trendforce.com/presscenter/news/20260930-13258.html).

Riset harga lokal menemukan nominal listing komponen pada retailer Indonesia, termasuk data harga dasar/promo yang belum menjadi kutipan harga checkout. Belum ditemukan pasangan harga bertanggal untuk SKU identik pada retailer yang sama, sehingga dokumen ini tidak mengklaim persentase kenaikan harga PC Indonesia. Bukti dan batas interpretasinya dicatat pada `05-Katalog-dan-Riset.md`. Permintaan pasar dan keuntungan usaha masih merupakan hipotesis bisnis; proyek ini belum melakukan validasi pelanggan atau perhitungan kelayakan usaha.

Selain menyediakan akses perangkat, website dibutuhkan untuk mencatat inventaris, menahan unit yang telah dipesan, memeriksa batas peminjaman anggota, mengonfirmasi pembayaran, serta menelusuri pengiriman, pengembalian, dan denda.

## 2. Masalah bisnis

1. Pengguna membutuhkan pilihan penggunaan PC gaming dan peripheral premium untuk periode singkat.
2. Pengelola membutuhkan informasi ketersediaan yang dapat mencegah satu unit dipesan oleh dua anggota pada waktu bersamaan.
3. Pencatatan transaksi perlu menghubungkan pemesan, paket, harga, pembayaran, serah terima, jatuh tempo, dan pengembalian.
4. Pengelola membutuhkan dasar perhitungan denda yang konsisten dan riwayat transaksi yang dapat dicetak.

## 3. Tujuan dan pengguna

### 3.1 Tujuan

- Menyediakan katalog paket PC gaming dan peripheral dengan harga dan kelengkapan yang jelas.
- Mengelola seluruh siklus penyewaan melalui akun admin dan anggota.
- Mencegah peminjaman ganda serta pelampauan batas dua unit per anggota.
- Menghitung jatuh tempo dan denda berdasarkan waktu kejadian sebenarnya.
- Menyediakan laporan dan bukti pengujian sesuai kebutuhan tugas akademik.

### 3.2 Pengguna dan pihak terkait

| Pihak | Kepentingan |
|---|---|
| Anggota/penyewa | Melihat katalog, memilih paket, mengetahui tagihan, dan melihat transaksi miliknya |
| Admin GTR | Mengelola inventaris, kategori, anggota, pembayaran, pengiriman, pengembalian, dan laporan |
| Kurir internal GTR | Mengantar dan menjemput perangkat berdasarkan koordinasi admin; tidak memiliki akun aplikasi |
| Tim pengembang | Menyelesaikan aplikasi beserta dokumen dan pengujian dengan tenggat yang tersedia |
| Pengajar/penguji | Memeriksa pemenuhan spesifikasi, prinsip arsitektur, dokumentasi, dan hasil demonstrasi |

Segmen awal yang diusulkan untuk narasi bisnis adalah gamer yang membutuhkan perangkat sementara atau ingin mencoba perangkat sebelum membeli. Rincian segmen dan lokasi usaha masih dapat dipertajam menggunakan data pendukung.

## 4. Definisi unit dan katalog

**Satu unit sewa adalah satu paket fisik tetap yang memiliki kode unik.** Penghitungan kuota menggunakan paket sewa tersebut.

| Istilah | Definisi |
|---|---|
| Paket PC + monitor | Satu PC dengan monitor pasangannya serta kelengkapan tetap yang tercantum; dihitung satu unit sewa |
| Paket peripheral | Satu set perangkat dengan isi tetap yang ditampilkan pada katalog; dihitung satu unit sewa |
| Fullset | Gabungan satu paket PC + monitor dan satu paket peripheral dalam transaksi; dihitung dua unit sewa |
| Komponen/kelengkapan | Daftar isi paket untuk pemeriksaan serah terima; tidak dipilih atau dipinjam secara terpisah dari paketnya |
| Unit ditahan | Unit yang telah dipesan dan belum dilepas melalui pembatalan atau pengembalian |
| Jatuh tempo | Waktu penerimaan oleh penyewa ditambah lima kali 24 jam |

Paket peripheral memiliki varian tetap, termasuk tingkat high-end dan mid-range. Kombinasi dapat berupa mouse dan keyboard, mouse dan headset, maupun paket peripheral lengkap sesuai data katalog. Mousepad disertakan pada setiap paket yang berisi mouse. Isi dan model perangkat harus ditampilkan sebelum anggota memesan.

Setiap paket memiliki perangkat fisiknya sendiri. Satu komponen fisik tidak dicatat sebagai stok tersedia di beberapa paket sekaligus. Monitor dalam paket PC selalu menjadi bagian paket tersebut. Fullset menggunakan dua unit yang sudah ada, sehingga tidak menciptakan stok fullset tambahan.

Nama paket boleh sama untuk beberapa set fisik, tetapi kode unit setiap set wajib berbeda. Satu unit dapat masuk ke beberapa kategori. Anggota dapat menyewa paket PC + monitor saja, fullset, atau peripheral saja selama total unit yang sedang ditahan untuknya tidak melebihi dua.

## 5. Aturan bisnis yang disepakati

| ID | Aturan |
|---|---|
| BR-01 | Aplikasi memiliki dua role login: admin dan anggota. Pengguna harus login untuk mengakses fungsi aplikasi; login dan pendaftaran menjadi pintu masuknya. |
| BR-02 | Pengguna dapat mendaftar sendiri dan otomatis menjadi anggota. Pendaftaran publik tidak memberikan pilihan role admin. |
| BR-03 | Satu akun memiliki satu profil. Pengguna dapat mengubah profil miliknya sendiri. |
| BR-04 | Admin dapat menambah, mengubah, dan menghapus/mengarsipkan data unit, kategori, dan anggota dengan tetap menjaga riwayat transaksi. |
| BR-05 | Anggota dapat mencari unit berdasarkan nama. Kode unit bersifat unik dan unit mendukung banyak kategori. |
| BR-06 | Maksimal dua unit ditahan untuk satu anggota di seluruh transaksinya. Unit menunggu pembayaran, persiapan/pengiriman, dan unit yang masih dipinjam ikut dihitung. |
| BR-07 | Unit langsung ditahan saat pesanan dibuat dan tidak tersedia bagi pemesan lain. Admin dapat membatalkan pesanan yang belum dibayar untuk melepaskan seluruh unit di dalamnya. |
| BR-08 | Tarif sewa merupakan harga paket maksimal lima hari yang ditetapkan per unit. Pengembalian lebih awal tetap menggunakan harga paket yang sama. |
| BR-09 | Ongkir menggunakan tarif tetap per transaksi pada area demo Bandung, Bojongsoang, dan Dayeuhkolot. Tarif demo Rp75.000 mencakup pengantaran dan penjemputan kembali, ditambahkan sekali ke tagihan, dan dapat diatur admin. Batas area ini simulasi operasional tanpa pemetaan koordinat otomatis. |
| BR-10 | Tagihan awal adalah jumlah harga paket yang dipilih ditambah ongkir antar-jemput. Pembayaran tunai/transfer dikonfirmasi manual oleh admin dan harus lunas sebelum perangkat diserahkan untuk pengiriman. |
| BR-11 | Pengiriman dan penjemputan dilaksanakan kurir internal GTR melalui koordinasi admin. Aplikasi tidak membutuhkan role kurir atau integrasi tarif kurir pihak ketiga. |
| BR-12 | Masa sewa dimulai ketika seluruh perangkat transaksi diterima penyewa. Admin mencatat waktu penerimaan sebenarnya; waktu ini menjadi awal hitungan lima hari. |
| BR-13 | Seluruh unit dalam satu transaksi mengikuti waktu mulai dan jatuh tempo yang sama, serta diserahkan dan dikembalikan bersama. |
| BR-14 | Hanya admin yang dapat mencatat pengembalian. Anggota menghubungi admin untuk mengatur pengembalian/penjemputan. |
| BR-15 | Waktu pengembalian efektif adalah waktu seluruh perangkat transaksi diterima pihak GTR, bukan waktu penyerahan kepada kurir atau waktu admin menekan tombol pencatatan. |
| BR-16 | Denda dihitung per unit menggunakan tarif harian yang ditetapkan untuk masing-masing unit. Komponen di dalam paket tidak dikenai denda terpisah. |
| BR-17 | Tepat pada jatuh tempo tidak dikenai denda. Keterlambatan positif dibulatkan ke atas per 24 jam: sampai 24 jam dihitung satu hari, lebih dari 24 jam sampai 48 jam dihitung dua hari, dan seterusnya. |
| BR-18 | Admin dapat melihat seluruh unit yang ditahan/dipinjam, melihat riwayat anggota, dan mencetak riwayat. Anggota hanya dapat melihat transaksi serta unit yang dipinjam olehnya. |

Tarif sewa, tarif denda, dan ongkir yang digunakan pada suatu transaksi perlu dipertahankan dalam riwayatnya agar perubahan tarif katalog tidak mengubah tagihan yang telah disepakati.

### 5.1 Rumus tagihan dan denda

**Tagihan awal = jumlah harga paket seluruh unit + ongkir antar-jemput per transaksi.**

**Hari terlambat = pembulatan ke atas dari selisih positif waktu pengembalian efektif terhadap jatuh tempo, dalam satuan 24 jam.**

**Denda unit = hari terlambat × tarif denda harian unit pada transaksi.**

**Total denda transaksi = jumlah denda seluruh unit dalam transaksi.**

Tanggal dan waktu pada tampilan menggunakan WIB (Asia/Jakarta). Contoh: penerimaan 22 Oktober pukul 10.00 memiliki jatuh tempo 27 Oktober pukul 10.00. Penerimaan kembali oleh GTR tepat pada jatuh tempo tidak dikenai denda; satu menit setelahnya dikenai satu hari denda per unit.

## 6. Alur bisnis tingkat tinggi

1. Anggota mendaftar/login dan melengkapi profil.
2. Anggota mencari serta memilih satu atau dua unit paket yang tersedia.
3. Sistem memeriksa seluruh unit yang sedang ditahan untuk anggota, menahan unit pesanan, dan menghitung tagihan awal.
4. Anggota melakukan pembayaran sesuai petunjuk. Admin mengonfirmasi pelunasannya.
5. Admin mengoordinasikan pengiriman melalui kurir GTR.
6. Saat perangkat diterima penyewa, admin mencatat waktu serah terima dan sistem menentukan jatuh tempo lima hari kemudian.
7. Anggota menggunakan perangkat dan menghubungi admin untuk mengatur pengembalian.
8. Kurir GTR menjemput perangkat. Admin memeriksa kelengkapan dan mencatat waktu penerimaan sebenarnya oleh GTR.
9. Sistem menghitung denda bila ada, menutup peminjaman, dan memperbarui ketersediaan unit sesuai hasil pemeriksaan admin.
10. Admin dapat melihat dan mencetak riwayat; anggota melihat riwayat miliknya sendiri.

Cabang pembatalan: admin membatalkan pesanan yang belum dibayar; seluruh unit yang ditahan pesanan itu dilepaskan dan riwayat pembatalan tetap tercatat.

## 7. Ruang lingkup versi akademik

### 7.1 Termasuk

- Autentikasi, pendaftaran anggota, dua role, dan satu profil per pengguna.
- Katalog paket tetap, pencarian nama, kategori jamak, dan kode unit unik.
- Pengelolaan unit, kategori, serta anggota oleh admin.
- Pesanan, penahanan stok, batas dua unit, dan pembatalan pesanan belum dibayar oleh admin.
- Harga paket lima hari, ongkir antar-jemput per transaksi, dan konfirmasi pembayaran manual.
- Pencatatan serah terima dan pengembalian oleh admin dengan waktu kejadian sebenarnya.
- Perhitungan denda, riwayat, dan halaman cetak laporan.
- Validasi input, migration, seeder, dan pengujian aturan penting.

### 7.2 Batas versi pertama

- Paket menggunakan komponen tetap. Pemilihan komponen bebas dari stok bersama tidak termasuk versi pertama.
- Proses kurir dan pembayaran dikoordinasikan manual; tidak membutuhkan pelacakan langsung, payment gateway, atau API ojek online.
- Penanganan kerusakan, kehilangan, dan sengketa dilakukan melalui koordinasi admin. Perhitungan biaya otomatis pada aplikasi difokuskan pada denda keterlambatan.
- Nominal harga dan inventaris awal untuk demo merupakan data simulasi akademik, bukan klaim harga pasar atau inventaris usaha yang telah beroperasi.

## 8. Batasan arsitektur dan kualitas

Implementasi menggunakan PHP Laravel dan memenuhi empat prinsip yang ditentukan pada materi tugas:

1. **Dependency Inversion:** aturan bisnis bergantung pada kontrak; akses penyimpanan disediakan melalui implementasi yang dapat diganti.
2. **Single Responsibility:** penanganan HTTP, validasi input, aturan penyewaan, dan akses database memiliki tanggung jawab yang jelas.
3. **Testability:** batas dua unit, penahanan stok, tenggat, dan perhitungan denda dapat diuji dengan kondisi serta waktu yang ditentukan.
4. **Independence from Framework:** inti aturan bisnis menggunakan PHP tanpa ketergantungan langsung pada HTTP request atau Eloquent; detail Laravel ditempatkan pada bagian penghubung.

Hak akses diperiksa pada server. Operasi yang menahan stok dan memeriksa kuota harus mencegah dua permintaan bersamaan menghasilkan peminjaman ganda atau pelampauan kuota. Pengelolaan data harus mempertahankan keterbacaan transaksi lama.

## 9. Kriteria keberhasilan dan dasar UAT

Berikut merupakan target pengujian, belum merupakan hasil UAT:

| ID | Skenario penerimaan | Hasil yang diharapkan |
|---|---|---|
| UAT-01 | Anggota mendaftar lalu mengakses profil | Akun menjadi anggota dan hanya memiliki satu profil |
| UAT-02 | Anggota mencoba mengakses halaman/data anggota lain atau fungsi admin | Akses ditolak |
| UAT-03 | Admin membuat dua set bernama sama dengan kode berbeda, lalu mencoba kode duplikat | Nama sama diterima; kode duplikat ditolak |
| UAT-04 | Admin memberi beberapa kategori dan anggota mencari nama unit | Relasi kategori serta pencarian bekerja |
| UAT-05 | Anggota memesan dua unit, kemudian mencoba menambah satu unit dalam pesanan lain | Pesanan tambahan ditolak selama dua unit masih ditahan |
| UAT-06 | Dua anggota mencoba memesan unit fisik yang sama | Hanya satu pesanan dapat menahan unit tersebut |
| UAT-07 | Admin membatalkan pesanan belum dibayar | Kuota anggota dan ketersediaan unit dilepaskan |
| UAT-08 | Memesan paket PC saja atau fullset | Paket PC dihitung satu unit; fullset dihitung dua unit; ongkir ditambahkan sekali |
| UAT-09 | Admin memproses pengiriman dan serah terima | Pengiriman mensyaratkan pembayaran lunas; masa sewa mulai saat penerimaan penyewa |
| UAT-10 | Pengembalian dilakukan sebelum, tepat pada, dan setelah jatuh tempo | Harga paket tetap; denda mengikuti aturan pembulatan ke atas per 24 jam |
| UAT-11 | Admin mencatat pengembalian belakangan | Denda memakai waktu penerimaan sebenarnya oleh GTR |
| UAT-12 | Anggota mencoba mencatat pengembalian | Ditolak; hanya admin dapat menyelesaikannya |
| UAT-13 | Admin dan anggota melihat riwayat; admin mencetak | Admin melihat seluruh riwayat, anggota hanya miliknya; hasil cetak tersedia |

Hasil UAT final harus memuat pelaksana, waktu pengujian, hasil aktual, bukti, serta temuan dan pengujian ulang jika diperlukan.

## 10. Hasil proyek dan tahapan berikutnya

Alur pengumpulan mengikuti tugas: nama proyek dan BRD, flowchart/ERD, PRD, desain, implementasi, hasil UAT, manual book, dokumentasi, serta pengumpulan di GitLab. Singkatan F&B pada instruksi pengajar belum dikonfirmasi; rencana teknis saat ini mencakup frontend dan backend.

Hasil akhir mencakup:

1. BRD, flowchart, ERD, PRD, dan desain halaman.
2. Source code Laravel, migration, dan seeder data awal admin, anggota, kategori, serta unit paket.
3. Hasil pengujian dan laporan UAT berdasarkan pelaksanaan sebenarnya.
4. Manual book untuk admin dan anggota.
5. README berisi nama kelompok, nama tim/anggota, nama proyek, daftar fitur, gambar skema database, serta rekaman demo penjelasan website.
6. Source code dan file terkait dalam repositori GitLab untuk pengumpulan.

Tahap setelah BRD ini adalah flowchart dan ERD awal, kemudian PRD yang memuat perilaku fitur, validasi, status transaksi, dan kriteria penerimaan lebih rinci. Penyimpanan atau publikasi ke GitLab belum dilakukan dalam penyusunan dokumen ini.

## 11. Informasi yang masih perlu dilengkapi

- Nama kelompok, nama tim, dan identitas tiga anggota.
- Jam pengumpulan pada 23 Oktober 2026.
- Verifikasi cakupan alamat operasional ketika usaha benar-benar dijalankan. Area dan tarif simulasi sudah ditetapkan pada katalog demo.
- Konfirmasi fisik, SKU/BIOS, dan ketersediaan produk bila perangkat benar-benar akan diadakan. Model serta kelengkapan contoh sudah disediakan pada dokumen 05.
- Bukti historis lokal yang sebanding jika laporan final hendak menambahkan klaim kenaikan harga PC Indonesia. Dokumen saat ini membatasi klaim pada biaya awal dan proyeksi komponen global.

Aturan inti transaksi dalam bagian 4–6 menggunakan keputusan yang telah diberikan selama wawancara. Flowchart, ERD awal, PRD, katalog berbasis riset, serta panduan pembagian kerja/UAT kini tersedia pada dokumen 02–06. Harga rental/denda dan ongkir merupakan angka DEMO, bukan hasil survei kelayakan bisnis.
