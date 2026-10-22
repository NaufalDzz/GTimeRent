# Katalog demo dan dasar riset GT-Rent

Versi 1.0 | Tanggal akses sumber: 22 Oktober 2026 | Area demo: Bandung, Bojongsoang, Dayeuhkolot.

## 1. Cara membaca bukti

- Keputusan proyek mengikuti wawancara, seperti dua unit, lima hari, dan paket tetap.
- Fakta produk mengacu pada model dan spesifikasi pabrikan.
- Masukan komunitas berasal dari pengalaman Reddit dan daftar rekomendasi yang bersifat subjektif.
- Harga ritel Indonesia berasal dari listing toko. Harga checkout dan stok masih perlu dikonfirmasi.
- Angka demo mencakup harga sewa, denda, ongkir, dan jumlah stok untuk pengujian akademik. Angka ini bukan hasil survei tarif rental dan tidak membuktikan keuntungan usaha.

Label mid-range/high-end adalah posisi relatif dalam katalog GT-Rent, bukan peringkat universal. Produk yang lebih mahal tidak otomatis cocok untuk setiap pengguna. Tidak ada janji FPS, hasil kompetitif, atau pengadaan perangkat nyata.

## 2. Paket PC dan monitor tetap

| Komponen | PC Mid + IPS | PC High + OLED |
|---|---|---|
| CPU | AMD Ryzen 5 7600 | AMD Ryzen 7 9800X3D |
| GPU | Inno3D RTX 5070 Twin X2, 12 GB | Colorful RTX 5070 Ti Battle AX, 16 GB |
| Motherboard | GIGABYTE B650M DS3H, AM5/DDR5 | GIGABYTE B650M DS3H, AM5/DDR5, dengan BIOS yang mendukung 9800X3D |
| RAM | 32 GB, 2 x 16 GB DDR5-6000 | 32 GB, 2 x 16 GB DDR5-6000 |
| Storage | NVMe PCIe 4.0 1 TB | NVMe PCIe 4.0 2 TB |
| PSU | 750 W 80+ Gold, ATX 3.x, konektor sesuai GPU | 850 W 80+ Gold, ATX 3.x, konektor sesuai GPU |
| Cooling/case | Wraith Stealth dan case airflow dengan ukuran sesuai komponen | Tower cooler AM5 dan case airflow dengan clearance GPU/cooler sesuai SKU |
| Monitor pasangan | Acer Nitro VG271U M3, 27 inci, 1440p IPS 180 Hz | MSI MPG 271QRX QD-OLED, 26,5 inci, 1440p 360 Hz |
| Kelengkapan tetap | PC, monitor/stand, dua kabel daya, satu kabel DisplayPort | PC, monitor/stand, dua kabel daya, satu kabel DisplayPort |
| Harga DEMO <=5 hari | Rp500.000 | Rp1.200.000 |
| Denda DEMO per unit/hari | Rp100.000 | Rp200.000 |

Tim perlu menguji stabilitas profil RAM DDR5-6000 pada kombinasi BIOS/CPU yang dipakai. CPU tray dan box memiliki isi serta garansi berbeda. Paket high-end membutuhkan cooler tersendiri. Sebelum pengadaan, pastikan ukuran, konektor, dan dukungan BIOS setiap SKU pendukung. Katalog akademik ini belum menyatakan perangkat telah dibeli.

Dasar: Reddit PC-R9/PC-R11 untuk contoh segmentasi, AMD PC-R4/PC-R12, GIGABYTE PC-R5, NVIDIA PC-R6, Acer PC-R7, MSI PH-P10. Sumber lengkap ada pada catatan riset terlampir.

## 3. Paket peripheral tetap

| Komponen | A1 High-End lengkap | A2 Mid-Range lengkap |
|---|---|---|
| Mouse | Logitech PRO X2 SUPERSTRIKE | Logitech G304 LIGHTSPEED |
| Keyboard | Wooting 80HE | AULA F75, varian linear, mode kabel sebagai default demo |
| Headset | Logitech PRO X3 LIGHTSPEED | HyperX Cloud III wired |
| Mousepad | ARTISAN NINJA FX ZERO XL SOFT | Logitech G640 |
| Kelengkapan lain | Receiver/cable masing-masing perangkat yang memerlukan | Receiver/cable masing-masing perangkat yang memerlukan |
| Harga DEMO <=5 hari | Rp350.000 | Rp125.000 |
| Denda DEMO per unit/hari | Rp50.000 | Rp20.000 |

Wooting menggunakan pendekatan analog/Hall-effect, sedangkan AULA F75 adalah keyboard mekanis biasa tanpa Rapid Trigger. Informasi stok Wooting berbeda antar halaman pabrikan. Ketersediaan di Indonesia belum dikonfirmasi.

Pilihan tambahan berupa data katalog tetap:

| Paket | Isi tetap | Harga DEMO <=5 hari | Denda DEMO/hari |
|---|---|---|---|
| B1 High | Mouse, headset, dan mousepad model kelas A1 dalam set fisik tersendiri | Rp250.000 | Rp30.000 |
| B2 Mid | Mouse, headset, dan mousepad model kelas A2 dalam set fisik tersendiri | Rp75.000 | Rp15.000 |
| H1 High | Headset model kelas A1 dan kelengkapannya dalam set fisik tersendiri | Rp100.000 | Rp20.000 |
| H2 Mid | Headset model kelas A2 dan kelengkapannya dalam set fisik tersendiri | Rp50.000 | Rp10.000 |

Mousepad masuk setiap paket yang berisi mouse. Tidak ada pemilihan mouse/keyboard bebas dari satu stok bersama. A1, A2, B1, B2, H1, dan H2 yang memakai model sama tetap memiliki salinan fisik berbeda.

## 4. Inventaris simulasi untuk seeder

| Kode fisik demo | Nama katalog | Kategori contoh |
|---|---|---|
| PC-MID-001 | PC Mid + IPS | Gaming, PC + Monitor, Mid-Range |
| PC-MID-002 | PC Mid + IPS | Gaming, PC + Monitor, Mid-Range |
| PC-HIGH-001 | PC High + OLED | Gaming, PC + Monitor, High-End, OLED |
| PER-A1-001 | A1 High-End Lengkap | Gaming, Peripheral, High-End, Lengkap |
| PER-A2-001 | A2 Mid-Range Lengkap | Gaming, Peripheral, Mid-Range, Lengkap |
| PER-B1-001 | B1 Mouse + Headset High | Peripheral, High-End, Mouse + Headset |
| PER-B2-001 | B2 Mouse + Headset Mid | Peripheral, Mid-Range, Mouse + Headset |
| PER-H1-001 | H1 Headset High | Peripheral, High-End, Headset |
| PER-H2-001 | H2 Headset Mid | Peripheral, Mid-Range, Headset |

`data-demo.json` menyediakan data terstruktur yang konsisten dengan tabel ini. Label komponen/asset tag di dalamnya adalah identitas simulasi, bukan nomor seri pabrikan. Dua nama PC Mid yang sama sengaja digunakan untuk menguji persyaratan nama boleh sama dan kode wajib unik.

Lima unit inti PC-MID-001, PC-MID-002, PC-HIGH-001, PER-A1-001, dan PER-A2-001 cukup untuk menunjukkan fitur versi pertama. Varian B/H dapat ditambahkan sebagai data tanpa alur baru.

## 5. Harga lokal dan klaim kenaikan biaya

Riset menemukan nominal pada sumber toko Indonesia berikut. Halaman mengandung data harga dasar/promo, sehingga harga efektif harus dikonfirmasi pada halaman transaksi toko sebelum dipakai sebagai anggaran pembelian.

| Sumber/model | Nominal yang ditemukan | Batas interpretasi |
|---|---|---|
| Enterkomputer, Inno3D RTX 5070 Twin X2 OC, SKU 194604 | Payload halaman memuat Rp16.300.000 dan Rp11.950.000 | Angka dasar/promo berbeda. Harga final checkout belum terverifikasi |
| Enterkomputer, Colorful RTX 5070 Ti Battle AX, SKU 203190 | Payload memuat Rp24.050.000 dan Rp23.050.000. Tautan order memuat salah satu nominal | Snapshot listing, bukan bukti harga historis atau rata-rata pasar |
| Enterkomputer, Ryzen 7 9800X3D tray, SKU 197405 | Indeks/kategori menampilkan Rp7.730.000 | Nilai terindeks yang berbeda dari varian box. Harga pembelian final belum terverifikasi |

Tautan bukti: [Inno3D SKU 194604](https://enterkomputer.com/detail/194604/Inno3D-GeForce-RTX-5070-12GB-GDDR7-TWIN-X2-OC-DUAL-FAN-Garansi-3-Thn), [Colorful SKU 203190](https://www.enterkomputer.com/detail/203190/Colorful-GeForce-RTX-5070-Ti-16GB-GDDR7-Battle-AX-16GB-V), [kategori CPU](https://ecommerce.enterkomputer.com/category/processors-cpu).

**Belum ditemukan pasangan harga bertanggal untuk SKU identik pada retailer Indonesia yang sama. Karena itu, tidak ada persentase kenaikan harga PC lokal yang diklaim.** Harga dua model berbeda, harga USD, MSRP peluncuran, dan harga dasar versus promo bukan pasangan bukti kenaikan historis.

Laporan [TrendForce, 30 September 2026](https://www.trendforce.com/presscenter/news/20260930-13258.html) memberikan konteks tekanan biaya dengan proyeksi harga kontrak DRAM konvensional +10-15% dan NAND Flash +15-20% secara kuartalan pada Q4 2026. Ini proyeksi komponen global, bukan kenaikan harga retail PC Indonesia.

Narasi BRD yang digunakan: **biaya awal kepemilikan PC gaming/peripheral premium dan tekanan biaya komponen membuka hipotesis kebutuhan akses perangkat jangka pendek**. Kelayakan usaha belum dibuktikan melalui utilisasi, biaya perawatan, depresiasi, atau survei kesediaan membayar.

## 6. Sumber komunitas dan pabrikan

| ID | Sumber utama | Pemakaian |
|---|---|---|
| PC-R9 | [Reddit build 1440p, 10 April 2025](https://www.reddit.com/r/buildapcforme/comments/1jw9873) | Contoh arsitektur kelas menengah. Harga AS lama tidak dipakai sebagai harga Indonesia |
| PC-R11 | [Reddit r/indotech, rakitan 15 jutaan](https://www.reddit.com/r/indotech/comments/1vitbqz/saran-buat-rakit-pc-dengan-budget-15-jutaan/) | Opini tentang kelas produk, bukan benchmark atau transaksi |
| PH-R1 | [Reddit PRO X2 SUPERSTRIKE](https://www.reddit.com/r/MouseReview/comments/1rbqcab/logitech_pro_x2_superstrike_review_after_7_days/) | Ulasan satu pengguna mengenai bentuk/feel |
| PH-R2 | [Reddit Wooting 80HE](https://www.reddit.com/r/MechanicalKeyboards/comments/1hhmi2u/whats_the_consensus_on_the_wooting_80he/) | Perbedaan preferensi layout dan switch |
| PH-R3 | [Reddit rekomendasi mousepad](https://www.reddit.com/r/MousepadReview/comments/1w9b20y/weekly_buying_advice_recommendations_thread/) | Pemilihan berdasarkan kontrol, tekstur, dan preferensi |
| PH-R4 | [Reddit daftar OLED komunitas](https://www.reddit.com/r/OLED_Gaming/comments/1p1zygc/best_oled_gaming_monitors_of_2025/) | Shortlist subjektif, bukan peringkat terukur |
| PH-R5 | [Reddit AULA F75, enam bulan](https://www.reddit.com/r/BudgetKeebs/comments/1iylnm4/longterm_review_aula_f75_6_months_later/) | Pengalaman pemakaian, bukan statistik reliabilitas |
| PC-P | [AMD 7600](https://www.amd.com/en/products/processors/desktops/ryzen/7000-series/amd-ryzen-5-7600.html), [AMD 9800X3D](https://www.amd.com/en/products/processors/desktops/ryzen/9000-series/amd-ryzen-7-9800X3d.html), [NVIDIA RTX 5070 family](https://www.nvidia.com/en-us/geforce/graphics-cards/50-series/rtx-5070-family/) | Identitas dan spesifikasi CPU/GPU |
| MON-P | [Acer VG271U M3](https://www.acer.com/gb-en/monitors/gaming/nitro-vg1/pdp/UM.HV1EE.304), [MSI 271QRX](https://www.msi.com/Monitor/MPG-271QRX-QD-OLED/Specification) | Monitor tetap mid/high |
| PH-P | [Logitech X2 SUPERSTRIKE](https://www.logitechg.com/en-us/shop/p/pro-x2-superstrike-mouse), [Wooting 80HE](https://wooting.io/wooting-80he), [G304](https://www.logitechg.com/id-id/shop/p/g304-lightspeed-wireless-gaming-mouse.910-005284), [AULA F75](https://aulastar.com/mechanical-keyboard/176.html) | Identitas model. Stok Indonesia belum dikonfirmasi |

Catatan riset lengkap: `research/pc-dan-harga-lokal.md` dan `research/peripheral.md`. Pilihan katalog menggabungkan masukan komunitas dan spesifikasi resmi dengan batas klaim di atas. Tim tidak membuat tierlist numerik sendiri.
