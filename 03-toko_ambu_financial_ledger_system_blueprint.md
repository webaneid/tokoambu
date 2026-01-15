# Toko Ambu — Financial & Ledger System Blueprint
Canvas ini khusus untuk **laporan keuangan toko**: uang masuk, uang keluar, dan seluruh pembelanjaan operasional, terintegrasi dengan modul Order, Purchase, dan Warehouse.

---

## 1) Tujuan Sistem Keuangan
- Mengetahui **uang masuk & keluar secara real-time**
- Mengetahui **toko untung atau rugi**
- Memisahkan transaksi **otomatis (transaksional)** dan **manual (operasional)**
- Semua uang **punya sumber & alasan jelas** (audit-ready)

---

## 2) Prinsip Utama (WAJIB)
1. **Single Source of Truth**: semua laporan keuangan berasal dari **Ledger**
2. Tidak ada laporan yang dihitung dari order langsung tanpa ledger
3. Transaksi otomatis **tidak boleh dicatat manual** (anti dobel)
4. Transaksi manual **wajib pakai kategori & keterangan**
5. Kas ≠ Stok (uang & barang dipisah total)

---

## 3) Konsep Inti: Ledger (Buku Kas)
Ledger adalah **buku besar** yang mencatat semua uang.

### 3.1 Struktur Ledger Entry
Setiap baris ledger mewakili **1 kejadian uang**.

Field utama:
- entry_date
- type: `income` | `expense`
- category
- amount
- description
- source_type (payment, purchase, payroll, manual, adjustment)
- source_id
- created_by

---

## 4) Uang Masuk (Income)

### 4.1 Income Otomatis (Transaksional)
Dibuat otomatis oleh sistem:

1. **Pembayaran Customer**
   - DP
   - Pelunasan
   - source_type: `payment`
   - reference: order_id

2. **Retur Dana (Opsional)**
   - contoh: refund supplier
   - source_type: `adjustment`

⚠️ Finance **tidak boleh** input manual income untuk kasus ini.

---

### 4.2 Income Manual
Digunakan jika:
- suntikan modal
- pendapatan lain-lain

Contoh kategori:
- modal_owner
- pendapatan_lain

---

## 5) Uang Keluar (Expense)

### 5.1 Expense Otomatis (Transaksional)
Dibuat otomatis oleh sistem:

1. **Pembayaran ke Supplier**
   - berdasarkan Purchase
   - source_type: `purchase`

2. **Biaya Ongkir (jika dibayar toko)**
   - source_type: `shipment`

---

### 5.2 Expense Manual (Operasional)
Digunakan untuk semua biaya toko.

Contoh kategori default:
- gaji_karyawan
- beli_alat
- sewa
- listrik_air
- internet
- konsumsi
- perawatan
- pajak
- biaya_admin
- lain_lain

---

## 6) Kategori Keuangan (Configurable)
Kategori **tidak hardcode**, dikelola di Settings.

Setiap kategori:
- nama
- type: income / expense
- is_active

---

## 7) Hak Akses (RBAC)

### Super Admin
- full akses laporan
- kelola kategori

### Finance
- input ledger manual
- verifikasi payment
- lihat laporan

### Operator
- read-only ringkasan

---

## 8) Laporan Keuangan Inti

### 8.1 Laporan Kas Harian
- tanggal
- total income
- total expense
- saldo bersih

### 8.2 Laporan Bulanan
- income per kategori
- expense per kategori
- laba / rugi

### 8.3 Cashflow
- grafik uang masuk vs keluar

---

## 9) Integrasi dengan Modul Lain

### 9.1 Order & Payment (Customer)
- Payment **verified** → sistem membuat **ledger income otomatis**
- Sumber pencatatan: `payments` (atau `order_payments` jika dipisah)
- Ledger wajib menyimpan referensi:
  - source_type = `payment`
  - source_id = id pembayaran

### 9.2 Purchase & Supplier (Pembayaran Supplier)
**Prinsip:** ledger expense dibuat saat **uang benar-benar keluar**, bukan saat purchase dibuat.

#### Opsi Implementasi yang Direkomendasikan (Scalable)
Buat tabel khusus untuk histori pembayaran supplier:
- `purchase_payments`

Setiap pembayaran supplier (DP/termin/lunas) = 1 row.
- Ketika `purchase_payments.status = verified` → buat **ledger expense otomatis**.

Status ringkasan di `purchases`:
- `unpaid` | `partial` | `paid`

Field ringkasan (opsional, sebagai cache):
- `purchases.paid_amount` = SUM(purchase_payments yang verified)

#### Catatan Sinkron dengan Implementasi yang Sudah Ada
Saat ini sudah ada migrasi yang menambahkan kolom pembayaran di tabel `purchases`:
- `payment_status`, `payment_method`, `payment_date`, `paid_amount`, `supplier_bank_account_id`, `payer_bank_account_id`

**Instruksi untuk AI/Dev:**
1) WAJIB audit dulu schema yang sudah ada.
2) Jika sistem masih greenfield, boleh:
   - mempertahankan kolom-kolom ini sebagai **summary** (read-only)
   - dan tetap membuat `purchase_payments` untuk histori pembayaran.
3) Jika memilih tidak membuat `purchase_payments`, maka minimal:
   - ubah `payment_status` agar mendukung `unpaid/partial/paid`
   - ledger expense dibuat setiap kali ada pembayaran (bukan hanya saat paid penuh)

### 9.3 Bank Account (Sumber & Tujuan Uang)
Untuk transfer/non-tunai, pembukuan harus jelas:
- `payer_bank_account_id` = rekening/akun uang **toko** (source of funds)
- `supplier_bank_account_id` = rekening **supplier** (destination)

Catatan:
- akun milik toko sebaiknya dikelola di Settings (default source account)

### 9.4 Warehouse
- Warehouse **tidak mempengaruhi ledger** kecuali ada kejadian uang
- Receiving/putaway tidak membuat ledger

### 9.5 Bukti Transfer (Media) — Sinkron dengan Implementasi Saat Ini
Untuk bukti pembayaran/transfer, gunakan model & tabel **`media`** yang sudah ada.

**Standar:**
- Simpan bukti sebagai `Media` dengan:
  - `type = payment_proof`
  - `uploaded_by` = user yang upload
  - `metadata` (array) untuk info tambahan (mis. nomor referensi, catatan)
- Akses URL file dari `Media->url` (Storage::url).

**Relasi yang tersedia saat ini (sinkron dengan kode user):**
- Bukti pembayaran ke supplier dapat dikaitkan ke **Purchase** melalui `media.purchase_id`.
- Foto produk memakai `media.product_id` (tidak terkait keuangan).

**Instruksi untuk AI/Dev (WAJIB):**
1) Audit dulu struktur tabel `media` & relasinya (karena saat ini hanya ada `product_id` dan `purchase_id`).
2) Untuk payment supplier (DP/termin/lunas):
   - minimal: simpan bukti di `media.purchase_id`
   - rekomendasi scalable: tambah kolom `purchase_payment_id` (atau polymorphic `attachable_type/id`) supaya setiap termin punya bukti sendiri.
3) Untuk payment customer:
   - rekomendasi: tambah kolom `payment_id` / `order_id` (atau polymorphic) supaya bukti bisa terikat ke payment tertentu.
   - jika belum mau ubah schema: simpan keterkaitan di `media.metadata` (mis. `{order_id:..., payment_id:...}`) sebagai solusi sementara.

**Catatan Anti Double:**
- Upload bukti **tidak otomatis membuat ledger**.
- Ledger dibuat saat payment **verified/posted** (sesuai flow).

---

## 10) Validasi & Anti Error
- Tidak boleh ada ledger tanpa category
- Tidak boleh amount <= 0
- Transaksi otomatis tidak bisa dihapus manual
- Semua ledger punya created_by

---

## 11) Definition of Done — Financial Module
- Semua uang tercatat di ledger
- Bisa lihat untung/rugi
- Tidak ada dobel pencatatan
- Bisa audit asal-usul uang

---

Dokumen ini menjadi acuan utama pengembangan **Sistem Keuangan Toko Ambu**.

