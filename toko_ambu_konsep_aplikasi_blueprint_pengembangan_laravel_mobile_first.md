# Toko Ambu
Konsep aplikasi transaksi jual beli sederhana berbasis **Laravel** (web-app **responsif**) untuk membantu pencatatan produk, preorder, pembayaran, dan keuangan agar usaha terlihat jelas **untung/rugi**.

---

## 1) Masalah yang Diselesaikan
1. Produk banyak & bervariasi → lupa asal barang, modal, harga jual, dan untung per produk. Tidak ada dokumentasi uang keluar/masuk.
2. Preorder tidak terkontrol → tidak tahu siapa yang PO, sudah bayar/DP/belum, dan sisa pembayaran.
3. Pembayaran kacau → tidak tahu siapa bayar apa, kapan, dan bukti transfernya.

---

## 2) Tujuan Utama
- Semua transaksi **traceable** (bisa ditelusuri dari produk → order → pembayaran → kas → laporan).
- Semua uang **cashflow-aware** (pemasukan/pengeluaran tercatat otomatis & rapi).
- Operasional simpel untuk dipakai harian (responsif di desktop & mobile).

---

## 3) Ruang Lingkup Fitur (MVP → Phase Berikutnya)

### MVP (Versi 1 — langsung kepakai)
1. **Produk** (SKU, varian, harga modal/jual default, kategori)
2. **Distributor (Supplier)** + riwayat pembelian
3. **Customer** + riwayat order/preorder
4. **Order & Preorder** (status, item, total)
5. **Pembayaran** (DP/termin) + upload **bukti transfer**
6. **Keuangan (Kas/Ledger)** (income/expense, kategori)
7. **Invoice** (print/download)
8. **Label pengiriman** (print)
9. **Role & Permission** (Super Admin, Operator, Finance)
10. **Pengaturan/Option Page** (nama toko, logo, alamat, API RajaOngkir)

### Phase 2 (setelah MVP stabil)
- **Integrasi RajaOngkir** (ongkir & estimasi)
- Payment gateway
- Customer login (user) untuk cek status & upload bukti

---

## 4) Peran Tim (Role) & Akses
**Nama role operasional yang tidak rancu:** **Operator** (alternatif: Staff Operasional / Fulfillment)

### 4.1 Super Admin (Owner)
- Full akses
- Kelola user & role
- Kelola pengaturan toko (Option Page)
- Set API RajaOngkir
- Akses semua laporan

### 4.2 Operator
- Kelola produk
- Buat & kelola order/preorder
- Update status (packing/shipping)
- Cetak invoice & label
- Input ongkir (manual/MVP) atau dari RajaOngkir (Phase 2)
- **Tidak** akses laporan keuangan detail (opsional hanya ringkasan)

### 4.3 Finance
- Input kas masuk/keluar
- Verifikasi pembayaran
- Kelola bukti transfer
- Laporan keuangan
- **Tidak** edit produk/pengiriman (kecuali status pembayaran)

### 4.4 User (Future)
- Customer login: cek order, status, upload bukti, download invoice

---

## 5) Alur Bisnis (Flow) — Versi Sederhana Tapi Rapi

### 5.1 Pembelian Stok (Supplier → Kas)
1. Operator/Finance buat **Purchase**
2. Input item + qty + harga modal
3. Sistem buat **expense** ke ledger (opsional otomatis, bisa toggle)
4. (Opsional) stok bertambah

### 5.2 Order / Preorder (Customer → Order)
1. Operator buat Order/Preorder
2. Tambah item + qty + harga
3. Total terhitung otomatis
4. Status default: `waiting_payment`

### 5.3 Pembayaran (DP/Termin)
1. Finance/Operator input pembayaran (jumlah, metode)
2. Upload bukti transfer
3. Status payment: `pending` → `verified`
4. Sistem update status order:
   - kalau bayar sebagian → `dp_paid`
   - kalau lunas → `paid`
5. Sistem buat **income** ke ledger (ketika payment verified)

### 5.4 Pengiriman
1. Operator pilih alamat, kurir, input ongkir
2. Cetak label
3. Status: `packed` → `shipped` → `done`

---

## 6) Status Order yang Disarankan
- `draft`
- `waiting_payment`
- `dp_paid`
- `paid`
- `packed`
- `shipped`
- `done`
- `cancelled`

---

## 7) Halaman Pengaturan (Option Page) — Super Admin Only

### 7.1 Informasi Toko
- Nama toko
- Logo
- Alamat
- No WhatsApp
- Email
- Catatan footer invoice

### 7.2 Invoice
- Prefix nomor (mis. INV/AMBU)
- Format nomor
- Logo invoice

### 7.3 Pengiriman
- Alamat asal
- Kota asal (untuk RajaOngkir)
- Default kurir (opsional)

### 7.4 API Integration
- RajaOngkir API Key
- Mode (starter/basic/pro)
- Status koneksi (cek koneksi)

---

## 8) Integrasi RajaOngkir (Phase 2)
- Super Admin set API key + kota asal
- Operator pada halaman pengiriman:
  - pilih kurir + service
  - sistem fetch biaya ongkir + estimasi
  - simpan ke order

---

## 9) Struktur Data (Tabel Inti) — Draft

### Master
- `users`
- `roles`, `permissions` (pakai Spatie)
- `products`
- `suppliers`
- `customers`
- `settings` (key-value)

### Transaksi
- `orders`
- `order_items`
- `payments`
- `attachments` (bukti transfer)
- `purchases`
- `purchase_items`
- `ledger_entries` (kas)
- `shipments` (pengiriman)

---

## 10) UI/UX Responsif (Draft)

### Prinsip Desain
- **Mode terang (putih)** sebagai default
- Tampilan **bersih, ringan, elegan** (UMKM profesional, bukan dashboard ribet)
- Kontras cukup, font besar, responsif di desktop & mobile
- Fokus ke kecepatan input & keterbacaan

### Navigasi Utama
- Dashboard
- Order
- Produk
- Pengiriman
- Kas

### Dashboard menampilkan
- Pemasukan hari ini
- Pengeluaran hari ini
- Order belum lunas
- Preorder menunggu DP

---

# Lampiran E — CSS Framework & Styling System
Bagian ini mengunci keputusan styling agar konsisten dan mudah dikembangkan.

## E.1 Konsep Visual Global
- **Base UI**: putih & abu-abu tipis (clean, profesional)
- **Primary color**: **Orange** (hangat, aktif, ramah UMKM)
- **Secondary / Accent**: biru & pink (pemanis, tidak dominan)
- Semua warna **hanya untuk aksen**, bukan background besar

---

## E.2 Framework Utama
- **Tailwind CSS** sebagai framework utama
- Mode: **Light / White UI**
- Pendekatan: utility-first untuk kecepatan development

Stack:
- Laravel 10+
- Tailwind CSS (Vite)
- Blade Components

---

## E.3 Color Palette Resmi — Toko Ambu
Palette ini diambil dari referensi warna yang dikirim user dan dikunci sebagai **design token**.

### 🎯 Primary (Utama — Orange)
Digunakan untuk CTA utama, highlight angka penting, status aktif.

- **University of Tennessee Orange**
  - HEX: `#F17B0D`
  - RGB: `241, 123, 13`
  - Pantone: 716 C

- **Spanish Orange** (hover / active state)
  - HEX: `#DD5700`
  - RGB: `221, 87, 0`

---

### 💙 Secondary (Support — Blue)
Digunakan untuk info, link, badge netral.

- **Egyptian Blue**
  - HEX: `#0D36AA`

- **Sapphire**
  - HEX: `#075AC2`

---

### 💖 Accent (Pemanis — Pink)
Dipakai **hemat** untuk status tertentu, badge, atau highlight spesial.

- **Mexican Pink**
  - HEX: `#D00086`

- **Frostbite**
  - HEX: `#D836A5`

---

### ⚪ Neutral (Base UI)
Digunakan untuk keseluruhan layout.

- Putih: `#FFFFFF`
- Abu terang: `#F9FAFB`
- Abu border: `#E5E7EB`
- Abu teks: `#6B7280`
- Abu gelap teks utama: `#1F2937`

---

## E.4 Design Token (SCSS Variables)
Semua warna **WAJIB** lewat token ini.

```scss
// Primary
$color-primary: #F17B0D;
$color-primary-hover: #DD5700;

// Secondary
$color-blue: #0D36AA;
$color-blue-light: #075AC2;

// Accent
$color-pink: #D00086;
$color-pink-light: #D836A5;

// Neutral
$color-white: #FFFFFF;
$color-gray-50: #F9FAFB;
$color-gray-200: #E5E7EB;
$color-gray-500: #6B7280;
$color-gray-900: #1F2937;
```

---

## E.5 Tailwind Theme Extension
Mapping token ke Tailwind agar class konsisten.

```js
theme: {
  extend: {
    colors: {
      primary: '#F17B0D',
      'primary-hover': '#DD5700',
      blue: '#0D36AA',
      pink: '#D00086',
    }
  }
}
```

Contoh penggunaan:
- `bg-primary`
- `hover:bg-primary-hover`
- `text-blue`
- `border-pink`

---

## E.6 Aturan Pemakaian Warna (PENTING)
1. Background halaman **HARUS** putih / abu.
2. Orange **hanya** untuk:
   - tombol utama
   - total angka penting (total invoice, sisa bayar)
   - status aktif
3. Biru untuk info / link.
4. Pink hanya aksen kecil (badge, indikator khusus).
5. Jangan lebih dari **2 warna aksen** dalam 1 layar.

---

## E.7 Catatan untuk Dev / AI
- Jangan hardcode warna.
- Jangan pakai gradient berlebihan.
- Invoice & label boleh lebih kontras, tapi tetap pakai palette ini.

