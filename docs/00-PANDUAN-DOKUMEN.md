# Paket dokumen GT-Rent - Gaming Time Rent

Versi 1.0 | 22 Oktober 2026 | Tenggat diasumsikan besok, 23 Oktober 2026.

Nama kelompok: ____________________  
Nama tim: ____________________  
Anggota 1: ____________________  
Anggota 2: ____________________  
Anggota 3: ____________________

## Mulai membaca dari sini

| Kebutuhan | Berkas |
|---|---|
| Bagikan gambaran proyek ke grup | [PDF ringkasan BRD, 3 halaman](../output/pdf/GT-Rent-Ringkasan-BRD.pdf) |
| Dasar kebutuhan bisnis | [BRD lengkap](01-BRD-GT-Rent.md) |
| Lihat alur transaksi | [PDF flowchart](../output/pdf/GT-Rent-Flowchart.pdf), [penjelasan dan Mermaid](02-Flowchart.md) |
| Rancang migration/database | [PDF ERD](../output/pdf/GT-Rent-ERD-Awal.pdf), [kamus data lengkap](03-ERD-Awal.md) |
| Acuan fitur dan validasi | [PRD PDF](../output/pdf/GT-Rent-PRD.pdf), [PRD editable](04-PRD-GT-Rent.md) |
| Isi seeder dan cek bukti | [Katalog dan riset](05-Katalog-dan-Riset.md), [data demo JSON](data-demo.json) |
| Bagi pekerjaan dan catat UAT | [Panduan kerja](06-Pembagian-Kerja-dan-UAT.md) |

Sumber riset terperinci berada di `research/`. Diagram PNG, SVG, dan Mermaid yang dapat diedit berada di `diagrams/`. Semua berkas dibundel dalam `../output/GT-Rent-Paket-Dokumen.zip`.

## Aturan yang jangan berubah antaranggota

1. Paket PC + monitor = satu unit; paket peripheral tetap = satu unit; fullset = dua unit.
2. Maksimal dua unit ditahan untuk anggota lintas semua pesanan, termasuk sebelum pembayaran dan selama pengiriman.
3. Lima hari dihitung sejak seluruh perangkat diterima penyewa. Harga tetap meski kembali lebih cepat.
4. Kurir GTR, area demo Bandung/Bojongsoang/Dayeuhkolot, ongkir flat Rp75.000 antar-jemput sekali per transaksi.
5. Pembayaran manual dikonfirmasi admin sebelum pengiriman.
6. Pengembalian hanya admin, memakai waktu sebenarnya seluruh barang diterima GTR. Denda positif dibulatkan ke atas per 24 jam.
7. Harga/denda/ongkir dan kelengkapan transaksi disalin sebagai snapshot; perubahan master tidak mengubah riwayat.
8. Tidak ada stok komponen bersama. Mousepad disertakan pada paket yang berisi mouse.

## Batas bukti dan status

Harga sewa/denda/ongkir adalah **angka simulasi akademik**, bukan tarif pasar. Riset Reddit merupakan masukan subjektif; spesifikasi dicek dengan pabrikan. Belum ada pasangan harga historis lokal sebanding yang membuktikan persentase kenaikan PC Indonesia. Dokumen memakai konteks biaya awal dan proyeksi tekanan biaya komponen global.

Paket ini merupakan dokumentasi dan rencana. Aplikasi, migration/seeder Laravel, desain UI final, hasil UAT aplikasi, manual penggunaan aplikasi final, dan video demo belum dibuat melalui pekerjaan dokumentasi ini. Form UAT belum diisi hasil lulus. Tidak ada publikasi GitLab yang dilakukan.

Baseline teknis rancangan: Laravel 13/PHP 8.4/Blade dan MySQL 8. Server database perlu diperiksa pada awal implementasi. Delapan tabel bisnis inti dirinci dalam ERD; tabel bawaan framework dapat ditambahkan oleh scaffold.

## Membuat ulang PDF

Skrip `../scripts/build_documents.py` menggunakan Python dengan ReportLab, pypdf, font Arial Windows, dan Poppler `pdftoppm` pada PATH. Jalankan dari workspace dengan Python yang memiliki dependensi tersebut. Skrip memperbarui JSON demo, sumber Mermaid, PDF, render PNG, hasil verifikasi otomatis, dan ZIP. Render perlu diperiksa visual kembali setelah setiap perubahan; status pemeriksaan visual tidak diasumsikan lulus otomatis.
