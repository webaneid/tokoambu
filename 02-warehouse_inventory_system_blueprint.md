# Toko Ambu — Warehouse & Inventory System Blueprint

Dokumen ini adalah **canvas khusus fase pengembangan gudang & stok**, terpisah dari blueprint utama, agar fokus, scalable, dan tidak mengganggu MVP core.

---

## 1) Tujuan Sistem Gudang

* Mengetahui **barang datang dari supplier kapan**
* Mengetahui **barang disimpan di mana (gudang / rak / bin)**
* Mengetahui **stok real-time**
* Setiap order customer **otomatis mengurangi stok**
* Semua pergerakan stok **punya histori (audit trail)**

---

## 2) Prinsip Desain (WAJIB DIPATUHI)

1. Stok **tidak boleh diubah langsung** tanpa histori
2. Semua perubahan stok harus lewat **stock movement**
3. Purchase ≠ otomatis masuk stok (harus ada proses receiving)
4. Order ≠ langsung mengurangi stok (kurangi saat packed/shipped)
5. Sistem harus bisa menjawab pertanyaan: *barang ini ada di rak mana sekarang?*

---

## 3) Alur Utama (End-to-End)

### 3.1 Supplier Order (Purchase)

* Buat Purchase
* Isi:

  * tanggal order
  * supplier
  * item + qty_ordered
* Status awal: `ordered`

---

### 3.2 Supplier Kirim Barang

* Update status purchase → `shipped`
* (Opsional) simpan nomor resi supplier

---

### 3.3 Barang Diterima (Receiving)

* Input:

  * tanggal barang datang
  * qty_received per item
* Update status purchase → `received`

> **Catatan:**
> Barang belum dianggap stok tersedia sebelum proses receiving selesai.

---

### 3.4 Penyimpanan Barang (Putaway)

* Operator memilih lokasi simpan:

  * Gudang
  * Rak
  * Bin / Shelf
* Sistem:

  * menambah stok ke lokasi tersebut
  * membuat histori stock movement (type: `receive`)

Contoh kode lokasi:

* `GUD1-A01-02`

---

### 3.5 Order Customer → Pengurangan Stok

* Saat order masuk status `packed` / `shipped`:

  1. Operator pilih lokasi pengambilan barang
  2. Sistem validasi stok cukup
  3. Sistem mengurangi stok dari lokasi tersebut
  4. Sistem membuat stock movement (type: `ship`)

---

## 4) Struktur Data Gudang

### 4.1 warehouses

* id
* code (unique) → contoh: GUD1
* name
* address (nullable)
* is_active
* timestamps

---

### 4.2 locations

Mewakili rak / bin fisik.

* id
* warehouse_id
* code → contoh: A01-02
* zone (nullable)
* rack (nullable)
* bin (nullable)
* description (nullable)
* is_active
* timestamps

Tampilan lokasi ke user:

* `GUD1-A01-02`

---

### 4.3 inventory_balances

Stok aktif per lokasi.

* id
* product_id
* location_id
* qty_on_hand
* qty_reserved (default 0)
* timestamps

Rumus penting:

* **qty_available = qty_on_hand − qty_reserved**

---

### 4.4 stock_movements

Histori semua perubahan stok.

* id
* movement_date
* product_id
* from_location_id (nullable)
* to_location_id (nullable)
* qty
* movement_type:

  * receive
  * ship
  * transfer
  * adjust
  * reserve
  * unreserve
  * return
* reference_type (purchase / order / manual)
* reference_id
* notes (nullable)
* created_by
* timestamps

---

## 5) Integrasi dengan Modul Lain

### 5.1 Integrasi Purchase

* Saat purchase `received`:

  * buat stock movement `receive`
  * tambah inventory_balances

### 5.2 Integrasi Order

* Saat order `packed` / `shipped`:

  * buat stock movement `ship`
  * kurangi inventory_balances

### 5.3 Preorder (Opsional Lanjutan)

* Saat preorder dibuat dan stok tersedia:

  * movement `reserve`
* Saat preorder batal:

  * movement `unreserve`

---

## 6) Hak Akses (RBAC)

### Super Admin

* full akses gudang

### Operator

* manage locations
* receiving & putaway
* picking & shipping
* transfer antar rak

### Finance

* ❌ tidak punya akses gudang

---

## 7) Roadmap Implementasi

### Tahap 1 (Wajib, Cepat Kepakai)

* 1 gudang
* rak sederhana
* receiving + putaway
* pengurangan stok saat packed/shipped
* histori stock movement

### Tahap 2 (Lengkap)

* multi gudang
* rack + bin
* reservasi preorder
* stock opname & adjustment

---

## 7.1 Sistem Pengurangan Stok (Stock Out / Adjustment)

Selain karena penjualan (order), stok bisa berkurang karena berbagai alasan. Maka perlu **Form Pengeluaran Barang** (Stock Out).

### A) Jenis Pengeluaran Barang

Pengeluaran dicatat sebagai `stock_movements`:

1. **Otomatis (Penjualan)**

   * Sumber: Order
   * movement_type: `ship`
   * reference_type: `order`

2. **Manual (Non-Penjualan)**

   * Sumber: Form Pengeluaran Barang
   * movement_type: `adjust`
   * reference_type: `manual`

---

### B) Form Pengeluaran Barang (Manual) — SPESIFIKASI

**Tujuan:** mengurangi stok karena rusak, hilang, gift, dll, dengan histori jelas.

**Field Form (WAJIB):**

* Tanggal & waktu
* Produk
* Lokasi asal (rak/bin)
* Qty keluar
* Alasan (dropdown)
* Catatan

**Field Opsional:**

* Lampiran (foto rusak / berita acara)

---

### C) Alasan (Reason) Default

Disediakan sebagai dropdown agar konsisten:

* rusak
* hilang
* gift
* sample
* expired
* return_to_supplier
* stock_opname
* lainnya

Jika `lainnya` dipilih → **catatan wajib diisi**.

---

### D) Aturan Validasi (KETAT)

* qty > 0
* qty <= qty_available
* lokasi wajib dipilih
* setiap submit **WAJIB** membuat:

  * 1 record `stock_movements`
  * update `inventory_balances.qty_on_hand`

---

### E) Hak Akses

Permission: `stock_adjustment`

* Super Admin: ✅
* Operator: ✅
* Finance: ❌

---

### F) Output Sistem

Setelah submit:

* stok berkurang sesuai qty
* histori movement tercatat
* dapat ditelusuri di laporan stok

---

## 8) Checklist Anti Error (Untuk AI / Dev)

(Untuk AI / Dev)

* Jangan update stok langsung di tabel produk
* Jangan skip stock movement
* Jangan kurangi stok saat order dibuat
* Kurangi stok **hanya** saat packed/shipped
* Semua movement harus punya reference

---

## 9) Definition of Done — Warehouse Module

* Bisa jawab: *stok produk X ada di rak mana?*
* Bisa trace: *stok ini berkurang karena apa?*
* Tidak ada selisih stok tanpa histori

---

# 10) Form Transfer Stok (Pindah Rak)

Digunakan saat barang **dipindahkan antar rak/bin/gudang** tanpa mengubah total stok.

## 10.1 Tujuan

* Merapikan penempatan barang
* Mengikuti layout gudang aktual
* Tetap menjaga histori pergerakan stok

## 10.2 Form Transfer Stok

**Field:**

* tanggal & waktu
* produk
* lokasi asal
* lokasi tujuan
* qty dipindahkan
* catatan (opsional)

## 10.3 Proses Sistem

1. Validasi qty <= qty_available di lokasi asal
2. Kurangi `inventory_balances` di lokasi asal
3. Tambah `inventory_balances` di lokasi tujuan
4. Buat `stock_movements`:

   * movement_type: `transfer`
   * from_location → to_location

## 10.4 Hak Akses

* Super Admin: ✅
* Operator: ✅
* Finance: ❌

---

# 11) Stock Opname (Hitung Fisik)

Digunakan untuk mencocokkan stok sistem dengan stok fisik.

## 11.1 Konsep

* Sistem **tidak langsung** mengubah stok
* Operator input hasil hitung fisik
* Sistem menghitung selisih otomatis

## 11.2 Alur Stock Opname

1. Pilih gudang / lokasi
2. Sistem tampilkan stok sistem (qty_on_hand)
3. Operator input stok fisik
4. Sistem hitung selisih:

   * selisih > 0 → penambahan
   * selisih < 0 → pengurangan
5. Operator konfirmasi

## 11.3 Dampak Sistem

* Buat `stock_movements`:

  * movement_type: `adjust`
  * reason: `stock_opname`
* Update `inventory_balances`

## 11.4 Catatan Penting

* Stock opname **harus beralasan**
* Semua koreksi bisa diaudit

---

# 12) Report Pengeluaran Stok by Reason

Laporan khusus untuk melihat **stok keluar non-penjualan**.

## 12.1 Filter Laporan

* periode tanggal
* produk
* lokasi
* reason (rusak, gift, hilang, dll)

## 12.2 Data Ditampilkan

* tanggal
* produk
* lokasi
* qty keluar
* reason
* user input
* catatan

## 12.3 Manfaat

* tahu barang rusak berapa
* tahu gift/sample berapa
* bahan evaluasi operasional

---

# 13) Dead Stock (Stok Mati / Lama Tidak Bergerak)

Dead stock adalah stok yang **tidak mengalami pergerakan keluar** dalam jangka waktu tertentu.

## 13.1 Definisi (Bisa Diatur)

Dead stock jika:

* tidak ada movement `ship` atau `adjust` keluar
* selama **X bulan** (default: 3 atau 6 bulan)

Nilai X **bisa di-setting** oleh Super Admin.

## 13.2 Cara Deteksi

Per produk / per lokasi:

* ambil `last_out_date` dari `stock_movements`
* bandingkan dengan tanggal hari ini

## 13.3 Status Stok

* `active` → masih bergerak
* `slow_moving` → hampir dead stock
* `dead_stock` → melewati threshold

## 13.4 Tabel Tambahan (Opsional)

Jika ingin performa lebih baik:

### inventory_analytics

* product_id
* location_id
* last_in_date
* last_out_date
* dead_stock_days
* status
* timestamps

---

# 14) Manfaat Bisnis Dead Stock

* diskon khusus
* bundling
* clearance sale
* evaluasi supplier / produk

---

# 15) Catatan Teknis Penting

* Dead stock **tidak mengubah stok**
* Hanya penanda analitik & pengambilan keputusan
* Semua perhitungan berdasarkan `stock_movements`

---

# 16) Dashboard Gudang (Warehouse Dashboard)

Dashboard khusus untuk memantau kondisi stok secara cepat.

## 16.1 Widget Utama

* Total SKU
* Total stok on hand
* **Active stock**
* **Slow moving stock**
* **Dead stock**

## 16.2 Tabel Ringkasan Stok

Kolom:

* Produk
* Lokasi
* Qty on hand
* Last out date
* Status (active / slow / dead)
* Rekomendasi aksi

## 16.3 Filter

* Gudang
* Lokasi
* Kategori produk
* Status stok

---

# 17) Alert Otomatis (Dead & Slow Stock)

Sistem memberi peringatan tanpa harus dicek manual.

## 17.1 Trigger Alert

* **Slow moving** → tidak ada stok keluar selama X hari (mis. 60 hari)
* **Dead stock** → tidak ada stok keluar selama Y hari (mis. 120 hari)

Nilai X & Y **bisa diatur di Settings**.

## 17.2 Channel Alert

* Notifikasi dalam aplikasi (badge / bell)
* (Opsional) WhatsApp / Email

## 17.3 Isi Alert

* Nama produk
* Lokasi
* Qty tersisa
* Lama tidak bergerak
* CTA (lihat detail / buat promo)

---

# 18) Integrasi Dead Stock dengan Pricing & Promo

Dead stock dipakai sebagai **bahan keputusan harga**, bukan otomatis mengubah harga.

## 18.1 Status Pricing

Produk dengan status:

* `active` → harga normal
* `slow_moving` → rekomendasi diskon ringan
* `dead_stock` → rekomendasi clearance / bundling

## 18.2 Rekomendasi Diskon (Manual Approval)

Contoh:

* Slow moving → diskon 5–10%
* Dead stock → diskon 20–50% atau bundling

⚠️ Diskon **tidak otomatis aktif**, harus di-approve Super Admin.

## 18.3 Data yang Ditampilkan ke Owner

* estimasi nilai stok mati (qty × HPP)
* estimasi potensi cash recovery jika diskon

---

# 19) Roadmap Lanjutan (Opsional)

Jika gudang sudah stabil:

* barcode / QR per rak
* scan picking & receiving
* FIFO / FEFO (expired)
* integrasi ke forecasting pembelian

---

# 20) Definition of Done — Advanced Warehouse

* Dashboard gudang berjalan
* Alert slow & dead stock aktif
* Rekomendasi promo tersedia
* Tidak ada perubahan stok tanpa histori

---

# 21) Paket Instruksi untuk Codex (Agar Bisa Diakomodir)

Bagian ini dibuat khusus supaya implementasi oleh AI/code assistant tidak salah arah.

## 21.1 Rules WAJIB (Tidak Boleh Dilanggar)

1. Jika Warehouse module aktif: **dilarang** update stok langsung di `products.stock` (anggap legacy).
2. Sumber kebenaran stok adalah:

   * `inventory_balances` (saldo per lokasi)
   * `stock_movements` (histori pergerakan)
3. Pengurangan stok **bukan** saat order dibuat.

   * Pengurangan stok dilakukan saat status order `packed` atau `shipped`.
4. Semua perubahan stok **wajib** punya record `stock_movements`.
5. Semua movement wajib punya `reference_type` + `reference_id` (untuk audit).

Kalimat kunci:

* Jika tidak ada record di `stock_movements`, berarti stok tidak pernah berubah.

---

## 21.2 Event & Listener (Otomatisasi Inti)

### A) Purchase Receiving

* Event: `PurchaseReceived`
* Listener: `CreateReceiveMovementsAndIncreaseBalances`

  * tambah `inventory_balances.qty_on_hand`
  * buat `stock_movements` (movement_type = `receive`, reference = purchase)

### B) Stock Transfer (Pindah Rak)

* Event: `StockTransferred`
* Listener: `MoveBalancesAndCreateTransferMovement`

  * kurangi saldo lokasi asal
  * tambah saldo lokasi tujuan
  * buat `stock_movements` (movement_type = `transfer`)

### C) Stock Adjustment (Rusak/Hilang/Gift/Dll)

* Event: `StockAdjusted`
* Listener: `DecreaseBalanceAndCreateAdjustMovement`

  * kurangi `qty_on_hand`
  * buat `stock_movements` (movement_type = `adjust`, reason sesuai)

### D) Order Packed/Shipped (Pengurangan Karena Dijual)

* Event: `OrderPackedOrShipped`
* Listener: `PickAndDecreaseBalanceCreateShipMovement`

  * validasi stok cukup di lokasi pick
  * kurangi `qty_on_hand`
  * buat `stock_movements` (movement_type = `ship`, reference = order)

### E) Stock Opname Confirmed

* Event: `StockOpnameConfirmed`
* Listener: `CreateOpnameAdjustments`

  * hitung selisih fisik vs sistem
  * buat `stock_movements` (movement_type = `adjust`, reason = `stock_opname`)
  * update `inventory_balances`

### F) Dead Stock Analytics (Job Terjadwal)

* Job: `ComputeDeadStockStatusJob`

  * hitung `last_out_date` per product/location
  * set status: active / slow / dead
  * buat notifikasi alert jika melewati threshold

---

## 21.3 Struktur Modul yang Disarankan (Biar Rapi)

Rekomendasi folder agar logic gudang tidak nyampur dengan order/payment:

* `app/Domain/Inventory/`

  * `Actions/`
  * `Events/`
  * `Listeners/`
  * `Services/InventoryService.php`
  * `DTOs/`
* `app/Http/Controllers/Warehouse/` (UI)

---

## 21.4 Acceptance Scenarios (Checklist Uji)

Codex dianggap selesai jika semua skenario ini lolos:

1. Purchase received → stok bertambah di lokasi yang dipilih + movement `receive` tercatat.
2. Order packed/shipped → stok berkurang dari lokasi pick + movement `ship` tercatat.
3. Adjustment rusak/hilang/gift → stok berkurang + movement `adjust` + reason tercatat.
4. Transfer rak → saldo pindah (asal berkurang, tujuan bertambah) + movement `transfer` tercatat.
5. Stock opname → sistem hitung selisih + koreksi saldo + movement `adjust` reason `stock_opname`.
6. Report pengeluaran by reason menampilkan data movement `adjust` non-penjualan.
7. Dead stock terhitung dan alert muncul sesuai threshold.

---

# 22) Diagram Alur State (Visual Mental Model)

Bagian ini adalah peta mental yang memisahkan state machine agar implementasi tidak tercampur.

## 22.1 State — Purchase (Supplier → Barang Masuk)

```text
draft → ordered → shipped → received
                    ↘ cancelled
```

## 22.2 State — Order (Customer → Barang Keluar)

```text
draft → waiting_payment → dp_paid → paid → packed → shipped → done
                       ↘ cancelled
```

**Rule stok:** pengurangan stok hanya terjadi saat `packed` atau `shipped`.

## 22.3 State — Inventory (Movement-based)

Inventory tidak punya satu status panjang, tetapi berbentuk **transisi movement**:

```text
receive → putaway → available
available → transfer → available
available → ship → out
available → adjust(+) → available
available → adjust(-) → available
available → opname_adjust → available
```

Keterangan:

* `available` = saldo di `inventory_balances`
* semua panah = record di `stock_movements`

---

# 23) Inventory Engine (Event/Listener Laravel)

Bagian ini mendefinisikan mesin inventory yang menjaga konsistensi saldo & histori.

## 23.1 Event Inti

* `PurchaseReceived`
* `StockPutAway` (opsional jika receiving & putaway dipisah)
* `StockTransferred`
* `StockAdjusted`
* `OrderPackedOrShipped`
* `StockOpnameConfirmed`

## 23.2 Listener / Actions Inti

* `IncreaseBalanceAndCreateReceiveMovement`
* `MoveBalanceAndCreateTransferMovement`
* `DecreaseBalanceAndCreateShipMovement`
* `ApplyAdjustmentAndCreateAdjustMovement`
* `ApplyOpnameDiffAndCreateAdjustMovement`

## 23.3 Guard & Safety (WAJIB)

* Validasi stok cukup sebelum pengurangan
* Gunakan DB transaction (atomic)
* Idempotency: cegah movement dobel untuk reference yang sama
* Concurrency-safe: gunakan locking saat update `inventory_balances`

---

# 24) UI Wireframe Gudang (Mobile-first)

Wireframe teks ini jadi acuan UI supaya cepat dibangun dan konsisten.

## 24.1 Receiving + Putaway

**Menu:** Gudang → Receiving

* Search Purchase (kode/supplier)
* List item per purchase:

  * Produk, qty_ordered
  * Input qty_received
  * Dropdown lokasi simpan (rak/bin)
* CTA: **Konfirmasi Barang Masuk**

Output:

* Purchase status → `received`
* Movement `receive`
* Balance lokasi bertambah

---

## 24.2 Transfer Stok (Pindah Rak)

**Menu:** Gudang → Transfer

* Produk (search)
* Lokasi asal (dropdown) + tampilkan qty_available
* Lokasi tujuan (dropdown)
* Qty
* Catatan (opsional)
* CTA: **Pindahkan**

Output:

* Movement `transfer`
* Balance asal berkurang, tujuan bertambah

---

## 24.3 Stock Opname

**Menu:** Gudang → Opname

* Pilih lokasi (rak/bin)
* Tabel stok sistem:

  * Produk | Stok sistem | Input stok fisik
* Sistem tampilkan selisih per item
* CTA: **Konfirmasi Opname**
* (Opsional) upload foto/catatan

Output:

* Movement `adjust` reason `stock_opname` untuk item selisih
* Balance disesuaikan

---
