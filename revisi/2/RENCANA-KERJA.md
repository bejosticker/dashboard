# Rencana Kerja — Revisi Web Aplikasi BEJOSTICKER (Batch 2 / 11-07-2026)

Dokumen sumber: `revisi/2/REVISI 110726 Web Aplikasi BEJOSTICKER.pdf` (16 halaman)
Disusun: 11 Juli 2026 · **Direvisi setelah klarifikasi klien**

---

## 0. Ringkasan Eksekutif

Dokumen klien ini **bukan sekadar revisi tampilan**. Isinya dua hal besar:

1. **Perluasan master data** — 2 menu baru (Merek, Ukuran), penambahan kolom di 5 menu lama.
2. **Pembangunan terminal POS dari nol** — halaman kasir lengkap dengan numpad, split payment,
   panel pelanggan, riwayat transaksi, nomor invoice otomatis, dan cetak struk thermal.

Kondisi kode saat ini: **tidak ada halaman POS.** Yang ada di menu "Penjualan" (`/sales`) adalah
form CRUD admin — dropdown produk biasa, satu metode pembayaran, tanpa invoice, tanpa kasir,
tanpa struk. Semua fitur POS di halaman 9–16 dokumen klien harus dibangun baru.

**Estimasi total: ~185 jam kerja (≈ 5 minggu untuk 1 developer).**

---

## 1. Keputusan yang Sudah Disepakati dengan Klien

| # | Pertanyaan | Jawaban Klien | Konsekuensi |
|---|---|---|---|
| 1 | Tipe Offline/Online di menu Toko | **Tidak dipakai** — tersisa di mockup karena lupa dihapus | Kolom `type` dilepas dari form. ⚠️ Ada efek samping, lihat §2-A1 |
| 2 | Satu invoice untuk Bahan + Cetak — bisa pakai database sekarang? | **Ya, dibahas** | **Bisa.** Pakai Opsi 1 di §3 — `sales` jadi induk transaksi POS. Data lama aman |
| 3 | Tax / PPN | **Ditampilkan, tapi belum dipakai** (perhitungan PPN belum rampung) | Baris Tax tampil dengan nilai 0, tidak bisa diedit. Kolom disiapkan di DB untuk diaktifkan nanti |
| 4 | Satuan harga (cm vs meter) | **Semua per meter.** Penjualan 10cm ditulis `0,1` | Satuan tunggal = **meter**, kuantitas **desimal**. Lihat §4 — ini keputusan besar |
| 5 | Harga Eceran | **Dihapus.** Cukup Meteran Grosir & Meteran Umum | Kolom eceran dilepas dari Produk. Lihat §4 |

---

## 2. Rincian Pekerjaan

### BAGIAN A — Manajemen & Data (~38 jam)

#### A1. Toko — Format Invoice (hal. 2) — 8 jam
- Kolom `description` diubah menjadi **Format Invoice** (contoh: `SBP/TAHUN/BULAN/TRANSAKSI KE`).
- Tabel menampilkan contoh invoice ter-render: `SBP/2026/07/0001`.
- Bangun **generator nomor invoice** per toko, counter reset per bulan.
- **Tipe Offline/Online dihapus** dari form (sesuai keputusan #1).

> ⚠️ **Efek samping yang harus diputuskan.** Kolom `type` ternyata sedang dipakai:
> `MarketOnlineController.php:12` menjalankan `Toko::where('type', 'Online')` untuk mengisi
> dropdown di menu **Toko Market Online**. Kalau kolomnya dilepas, dropdown itu jadi kosong.
> **Saran:** hapus `type`, lalu dropdown Market Online menampilkan semua toko. Biaya ~1 jam.

#### A2. Supplier — No. WhatsApp (hal. 3) — 2 jam
- Tambah kolom No. WhatsApp (form + tabel). Keterangan jadi textarea 3 baris.

#### A3. Merek — MENU BARU (hal. 4) — 5 jam
- CRUD lengkap: Nama Merek, Status (Lokal/Import), Keterangan.
- Migrasi, model, controller, view, entri sidebar. Jadi dropdown di form Produk.

#### A4. Pelanggan — Status & Keterangan (hal. 5) — 4 jam
- No. WhatsApp menjadi **wajib** (sudah unik, tinggal validasi).
- Tambah **Status: Grosir / Umum** — penentu jenis harga default di POS.
- Tambah Keterangan (textarea). Baris pelanggan Grosir di-highlight hijau.

#### A5. Produk (hal. 6) — 8 jam
- Tambah **SKU**, **Ukuran** (dropdown master Ukuran), **Merek** (dropdown master Merek).
- **Harga Eceran dihapus** (keputusan #5). Satuan meter, kuantitas desimal. Lihat §4.
- Tabel produk dirombak: SKU, Foto+Nama, Ukuran, Merek, Stok ("3 Roll 13 M"), Kulak (+ nilai
  total stok), Agen, Grosir, Umum, Meteran Grosir, Meteran Umum, Stok Minimal.
- Pasang tombol **Re-Stock** di tiap baris (fitur `stock_adjustments` sudah ada, tinggal wiring).

#### A6. Produk Cetak (hal. 7) — 4 jam
- Tambah **SKU**. Harga Grosir & Umum **per meter**. Opsi satuan saat transaksi.

#### A7. Ukuran — MENU BARU (hal. 8) — 5 jam
- CRUD lengkap: Nama Ukuran (mis. `50x18 (ptg)`), Keterangan.
- Dasar untuk *search by size* dan laporan stok stagnan per ukuran.

---

### BAGIAN B — Terminal POS (~100 jam) ← **INI YANG TERBERAT**

#### B1. Sesi Kasir (hal. 10) — 6 jam
- Modal pembuka: Tanggal Penjualan, Nama Toko, Nama Kasir → tombol **Buat**.
- Header POS menampilkan Toko / Tgl / Jam / No. Invoice / Kasir.

#### B2. Generator Nomor Invoice — 4 jam
- Membaca Format Invoice dari master Toko → `BS/2026/07/0002`.
- Counter aman dari *race condition* (dua kasir simultan).

#### B3. Layar POS (hal. 11–15) — 72 jam

| Komponen | Jam |
|---|---|
| Layout POS + state keranjang (bahan & cetak dalam satu keranjang) | 16 |
| Modal **Tambah Penjualan Bahan** (cari produk, qty desimal, satuan, jenis harga, harga) | 8 |
| Modal **Tambah Penjualan Cetak** (pilih produk, jenis harga, panjang, lebar, lembaran, satuan) | 9 |
| Panel **Data Customer** (cari via No. WA, badge GROSIR/UMUM, auto-set jenis harga), **Riwayat Transaksi**, modal **Tambah Pelanggan Baru** | 12 |
| **Numpad** + kalkulasi total/diskon/kembalian (dukung kuantitas desimal) | 8 |
| **Split Payment** (Cash + Transfer + QRIS, nominal per metode) | 10 |
| Baris **Tax** (tampil, nilai 0, non-aktif) | 1 |
| Catatan Kasir + modal sukses + tombol Cetak Lagi | 3 |
| Search produk + **scan barcode** | 5 |

#### B4. Cetak Struk Thermal (hal. 16) — 10 jam
- Logo, tanggal, jam, toko, kasir, no. invoice, item (nama + ukuran + merek + qty × harga),
  Total QTY, Sub Total, Diskon, Total, Bayar (metode), Kembali, "Terima Kasih".
- Belum ada infrastruktur cetak sama sekali — dibangun dari nol.

#### B5. Tabel Transaksi POS (hal. 10) — 8 jam
- Nama, No. WA, Status, Tanggal, Total Nominal, **Laba**, Diskon, Total Produk,
  **Metode Pembayaran** (bisa gabungan: "CASH, TRANSFER"), Aksi. Grosir di-highlight hijau.

---

### BAGIAN C — Perombakan Data & Integrasi (~38 jam)

#### C1. Perubahan Skema Database — 18 jam
**Tabel baru:** `brands` (merek), `sizes` (ukuran), `sale_payments` (split payment).

**Kolom baru:**
- `products`: `sku`, `brand_id`, `size_id`
- `cetak_products`: `sku`
- `suppliers`: `whatsapp`
- `customers`: `status` (grosir/umum), `description`
- `toko`: `invoice_format` (dan lepas `type`)
- `sales`: `invoice_no`, `toko_id`, `cashier`, `note`, `profit`, `subtotal`, `tax`, `paid`, `change`

**Penyatuan transaksi Bahan + Cetak:** lihat §3.

#### C2. Migrasi Data Lama — 6 jam
- Backfill `invoice_no` untuk transaksi lama.
- Backfill `profit` (laba yang selama ini tidak disimpan).
- Isi default `status` pelanggan.
- **Tidak ada konversi harga eceran** — lihat §4, storage internal tetap utuh.

#### C3. Dampak ke Laporan — 12 jam
- Laporan Keuangan, Laporan Toko, Laporan Market Online membaca `sales` — semua disesuaikan.
- Tambahan: laporan stok stagnan per Ukuran.

#### C4. Pengujian, Perbaikan Bug, Deploy — 12 jam
- Uji alur kasir end-to-end, cetak struk di printer asli, split payment, potong stok.

---

### BAGIAN D — Manajemen Proyek (~10 jam)

---

## 3. Keputusan Arsitektur — Satu Invoice untuk Bahan + Cetak

**Masalah.** Sekarang penjualan Bahan dan Cetak adalah dua transaksi terpisah dengan tabel header
masing-masing (`sales` dan `cetak_product_sales`). POS menuntut 1 keranjang → 1 invoice → 1 struk.

**Opsi 1 — `sales` jadi induk transaksi POS. ✅ DIPILIH**
- `sales` "naik pangkat" jadi header POS (+ invoice, toko, kasir, catatan, laba, tax, bayar, kembalian).
- Item bahan tetap di `sales_items`.
- Item cetak digantungkan ke `sales` yang sama lewat satu kolom penghubung baru.
- Split payment ditampung tabel baru `sale_payments`.
- `cetak_product_sales` **tetap hidup** untuk riwayat lama.

**Untung:** data lama tidak disentuh, laporan lama tidak pecah, migrasi ringan.
**Konsekuensi:** laporan membaca dua jalur (transaksi lama & POS baru) lalu digabung — sudah dihitung.

**Opsi 2 — Tabel POS baru dari nol.** Lebih rapi secara arsitektur, tapi semua laporan ditulis ulang
dan seluruh data lama harus dimigrasi. Lebih mahal, lebih berisiko. **Ditolak.**

---

## 4. Keputusan Satuan — Semua Per Meter, Kuantitas Desimal

Klien: *"biar tidak bingung rooting-nya, jadi per meter saja. Penjualan 10cm ditulis 0,1."*

**Artinya:**
- Satuan tunggal di seluruh aplikasi = **meter**. Tidak ada lagi cm atau /10cm.
- Kuantitas boleh **desimal**: `0,1` = 10cm · `0,5` = 50cm · `2,25` = 2 meter 25cm.
- Label harga eceran di menu Produk berubah dari `(/10cm)` menjadi **per meter**.

**Rekomendasi teknis penting — penyimpanan internal tetap dalam cm (bilangan bulat).**
Database saat ini menyimpan stok dan kuantitas dalam **cm sebagai bilangan bulat**
(`stock_cm`, `per_roll_cm`, `sales_items.quantity`). Ini **jangan diubah**, karena:

- Angka desimal (*floating point*) rawan galat pembulatan. Menjumlahkan `0,1 + 0,2` di komputer
  tidak selalu persis `0,3`. Untuk stok dan uang, ini berbahaya — stok bisa "bocor" pelan-pelan.
- Bilangan bulat cm itu presisi mutlak: `0,1 m` = tepat `10 cm`, selamanya.

Jadi: **input dan tampilan dalam meter (desimal), penyimpanan tetap cm (bulat).** Konversi ×100
dilakukan di satu tempat saja. Ini membuat perubahan "per meter" jadi **murni perubahan tampilan
dan input** — tanpa migrasi data berisiko, tanpa konversi harga lama.

### 4.1 Harga Eceran DIHAPUS (keputusan #5)

Klien: *"harga eceran bisa dihapus, jadi hanya meter umum dan meter grosir."*

Struktur harga **Produk (bahan)** menjadi:

| Harga | Satuan | Status |
|---|---|---|
| Harga Kulak | per Roll/Pak | tetap |
| Harga Agen | per Roll/Pak | tetap |
| Harga Grosir | per Roll/Pak | tetap |
| Harga Umum | per Roll/Pak | tetap |
| Harga Meteran Grosir | per meter | tetap |
| Harga Meteran Umum | per meter | tetap |
| ~~Harga Eceran Grosir~~ | ~~/10cm~~ | **DIHAPUS** |
| ~~Harga Eceran Umum~~ | ~~/10cm~~ | **DIHAPUS** |

Dropdown "Jenis Harga" di POS jadi **5 opsi**: Agen · Grosir (roll) · Umum (roll) ·
Meteran Grosir · Meteran Umum. Potongan di bawah 1 meter tetap bisa dijual — pakai harga
meteran dengan kuantitas desimal (`0,1` = 10cm).

> **Catatan:** kolom `price_eceran_grosir_cm` dan `price_eceran_umum_cm` adalah hasil **revisi
> batch 1**. Sekarang di-*rollback*. Ini kerja yang terbuang karena perubahan permintaan klien,
> bukan karena kesalahan teknis — layak dicatat sebagai *scope churn*.

> ⚠️ **Perlu konfirmasi cepat (tidak menahan pekerjaan):**
> 1. Harga **per Roll/Pak** (Kulak/Agen/Grosir/Umum) **tetap ada**, kan? Kalimat klien *"hanya meter
>    umum dan meter grosir"* dibaca sebagai: di antara harga **berbasis meter**, tinggal 2 tarif itu.
>    Harga roll adalah kategori terpisah dan tetap dipakai (mockup hal. 6 masih memuatnya).
> 2. Di **Produk Cetak**, "Harga Eceran" itu **per lembar** — satuan yang berbeda, bukan tarif meter.
>    Apakah ikut dihapus juga, atau tetap dipertahankan? (Modal POS hal. 13 masih punya field
>    "Lembaran", jadi dugaan saya **tetap dipertahankan**.)

---

## 5. Rekapitulasi Effort

| Bagian | Jam |
|---|---|
| A — Manajemen & Data | 36 |
| B — Terminal POS | 99 |
| C — Perombakan Data & Integrasi | 37 |
| D — Manajemen Proyek | 10 |
| **TOTAL** | **~182 jam** |

≈ **23 hari kerja** ≈ **5 minggu** untuk 1 developer penuh waktu.

---

## 6. Urutan Pengerjaan & Termin

**Termin 1 — Fondasi Master Data (2 minggu)**
Merek, Ukuran, SKU, Format Invoice, No. WA Supplier, Status Pelanggan, perombakan Produk &
Produk Cetak, penyeragaman satuan meter. → Klien sudah bisa input data lengkap.

**Termin 2 — Terminal POS (2 minggu)**
Sesi kasir, layar POS, keranjang gabungan, panel pelanggan, numpad, split payment, invoice.
→ Kasir sudah bisa transaksi.

**Termin 3 — Struk, Laporan, Finalisasi (1 minggu)**
Cetak struk thermal, penyesuaian laporan, migrasi data lama, QA, deploy.

---

## 7. Pertanyaan yang MASIH Perlu Dijawab Klien

Sudah terjawab: tipe toko, arsitektur invoice gabungan, Tax, satuan meter, harga eceran.
Sisanya — tidak menahan Termin 1, tapi harus terjawab sebelum Termin 2 dimulai:

1. **Format Invoice:** counter "TRANSAKSI KE" reset **per bulan** atau **per tahun**? Di mockup ada
   `BS2026/07/0002` (4 digit) dan `BP/2026/07/00001` (5 digit) — perlu satu standar.
2. **Harga per Roll/Pak tetap ada?** Dan **Harga Eceran per Lembar di Produk Cetak** ikut dihapus
   atau tidak? (Lihat §4.1 — dua konfirmasi cepat.)
3. **Dropdown Toko Market Online** setelah `type` dihapus — tampilkan semua toko? (Lihat §2-A1.)
4. **Split payment:** boleh 3 metode sekaligus (Cash + Transfer + QRIS)? Mockup cuma 2 slot.
5. **Nama Kasir:** dari user yang login, atau ketik bebas? (Mockup: ketik bebas — artinya tidak ada
   jejak audit siapa kasir sebenarnya.)
6. **Scan barcode:** barcode = SKU? Perlu fitur cetak label barcode juga?
7. **Printer thermal:** merek/model dan lebar kertas (58mm atau 80mm)?
8. **Riwayat Transaksi** di panel POS: tampilkan berapa transaksi terakhir?
9. **Laba produk cetak:** untuk bahan, laba = jual − kulak. Untuk cetak, dihitung dari apa?
10. **Penjualan Cetak memotong stok bahan?** Saat ini produk cetak punya stok sendiri.
11. **Tanggal backdate:** nomor invoice ikut tanggal mundur atau tanggal hari ini?

---

## 8. Dampak ke Laporan (pertanyaan klien)

### 8.1 Ya, Laporan Keuangan berubah — dan wajib berubah

Kondisi sekarang (`ReportController::index`, baris 73–106): penjualan **tidak dirinci sama sekali**.
Hanya muncul sebagai **dua baris gelondongan**:

- `Penjualan Offline` = `Sale::sum('total')`
- `Penjualan Cetak` = `CetakProductSale::sum('total')`

Setelah POS jalan, item cetak menempel ke `sales` (Opsi 1, §3). Akibatnya:

- Baris **"Penjualan Cetak" akan menyusut ke nol** seiring waktu — karena penjualan cetak baru
  masuk lewat POS, bukan lewat `cetak_product_sales` lagi.
- Baris **"Penjualan Offline" membengkak** karena diam-diam sudah memuat penjualan cetak.

Kalau laporannya tidak disesuaikan, klien akan membaca ini sebagai *"penjualan cetak mati"* —
padahal cuma pindah jalur. **Ini bukan kosmetik, ini laporan yang salah.** Penyesuaian ini
**sudah termasuk** di §2-C3 (12 jam) dan **sudah masuk harga**.

### 8.2 Temuan lebih besar: Laporan Toko saat ini TIDAK memuat penjualan

`ReportController::tokoReport` (baris 139–157) hanya menarik **Pengeluaran**, **Pengambilan Bahan**,
dan **Pemasukan Toko**. **Penjualan tidak ada.**

Penyebabnya: tabel `sales` **tidak punya kolom `toko_id`** — sistem tidak tahu penjualan itu terjadi
di toko mana. Jadi selama ini "Laporan Toko" tidak bisa menjawab pertanyaan paling dasar:
*"toko ini bulan lalu jualan berapa?"*

**Kabar baiknya:** begitu POS mencatat `toko_id` di tiap transaksi (sudah masuk lingkup §2-C1),
lubang ini tertutup dengan sendirinya. Laporan Toko **untuk pertama kalinya** bisa menampilkan
penjualan riil per toko.

### 8.3 Laporan Market Online — tidak terpengaruh

Sumber datanya (`online_markets`, `online_incomes`, `online_ads`) tidak disentuh POS. Aman.

### 8.4 Laporan BARU yang jadi mungkin — di luar harga saat ini

POS membuka data yang selama ini tidak pernah tercatat. Ini **fitur baru**, bukan penyesuaian,
jadi **belum masuk Rp 15jt**:

| Laporan Baru | Dimungkinkan oleh | Jam |
|---|---|---|
| Penjualan per Toko | kolom `toko_id` baru | 6 |
| Penjualan & rekap shift per Kasir | kolom `cashier` baru | 6 |
| Rekap Metode Pembayaran (Cash / Transfer / QRIS) | tabel `sale_payments` (split payment) | 5 |
| Laporan Stok Stagnan per Ukuran & Merek | master Ukuran & Merek baru (diminta di mockup hal. 8) | 6 |
| Laporan Laba / margin per produk & merek | kolom `profit` tersimpan | 7 |
| **Total** | | **~30 jam** |

**Usulan Paket C — Revisi & Perluasan Laporan: Rp 3 juta.**

### 8.5 Soal draft revisi laporan

**Ya, layak dibuat** — dan lebih baik dibuat **sebelum** Termin 3 dimulai, supaya penyesuaian
laporan (§2-C3) dan laporan baru (§8.4) bisa dikerjakan sekaligus, bukan dua kali bongkar.

Saran alur: buat draft/mockup laporan → klien review → baru dikunci lingkupnya. Pembuatan draft
sendiri ~4 jam, bisa dianggap bagian dari Manajemen Proyek (§2-D).

---

## 9. Di Luar Lingkup

- **Hardware:** printer thermal dan barcode scanner dibeli klien.
- **Pelatihan kasir** — bisa ditambahkan sebagai item terpisah.
- **Garansi bug:** 30 hari setelah serah terima. Setelahnya masuk retainer/maintenance.
- **Perubahan lingkup di tengah jalan** dihitung sebagai pekerjaan tambahan.
