# SOP-01 Standard Halaman (Filter, Table, Pagination)

Dokumen ini adalah standar UI untuk halaman list di Tokoambu agar konsisten.

## 1. Struktur Halaman (Container)
Gunakan wrapper berikut untuk semua halaman list:

```
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            ...
        </div>
    </div>
</div>
```

Jika ada beberapa blok (filter, table, pagination), tiap blok berbentuk card `bg-white` terpisah.

## 2. Card Filter (di atas table)
- Card: `bg-white overflow-hidden shadow-sm sm:rounded-lg`
- Isi: `p-6 text-gray-900`
- Layout form:
  - Desktop: 1 baris horizontal
  - Mobile: wrap ke bawah otomatis
- Container form:

```
<div class="flex flex-wrap items-end gap-4 md:flex-nowrap">
  <div class="w-full md:flex-1">...</div>
  ...
  <div class="flex items-end gap-2 md:flex-none pt-6">...</div>
</div>
```

### Field Filter (input/select)
- Label: `text-sm font-medium text-gray-700 mb-1`
- Input/select:
  - Class: `w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary`
  - Tinggi standar `h-10`

### Tombol Filter/Reset
- Tinggi: `h-10`
- Filter:
  - `bg-primary text-white rounded-lg hover:bg-primary-hover transition`
- Reset:
  - `border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center flex items-center`

## 3. Card Table
- Card: `bg-white overflow-hidden shadow-sm sm:rounded-lg`
- Table tanpa padding dalam (langsung `table` di card):

```
<table class="min-w-full divide-y divide-gray-200">
```

### Header Table
- `bg-gray-50`
- `text-xs font-medium text-gray-500 uppercase tracking-wider`
- Header sorting: link di t-head untuk sorting kolom
  - Indikator `▲/▼` ukuran `text-[8px] leading-none`
  - URL memakai query `sort` dan `direction`
  - Default sorting tetap ditentukan di controller (contoh: `sku` atau `name`)

### Body Table
- Row hover: `hover:bg-gray-50`
- Cell umum: `text-sm text-gray-500`
- Cell angka kanan: `text-right`
- Cell sku: `text-sm font-medium text-gray-900`

## 4. Ikon Aksi
- Gunakan Heroicons (outline) via `<x-heroicon>`.
- Ukuran: `w-4 h-4` (1rem).
- Warna:
  - Lihat: `text-blue`
  - Edit: `text-blue`
  - Hapus: `text-red-600`

Contoh:
```
<x-heroicon name="eye" class="w-4 h-4" />
<x-heroicon name="pencil-square" class="w-4 h-4" />
<x-heroicon name="trash" class="w-4 h-4" />
```

## 5. Card Pagination (di bawah table)
- Tampilkan hanya jika `lastPage() > 1`.
- Card: `bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6`
- Layout:
  - Kiri: total data
  - Kanan: paginasi

### Format Pagination
- Arrow kiri (prev) dan arrow kanan (next)
- Angka halaman:
  - Tampilkan 2 angka awal dan 2 angka akhir
  - Jika ada gap, tampilkan `...`
- Style tombol:
  - Default: `border border-gray-300 text-gray-600 hover:bg-gray-50`
  - Active: `border-primary bg-primary text-white`

Contoh komponen:
```
<div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600">
  <div>Total produk: ...</div>
  <div class="flex items-center gap-2">...</div>
</div>
```

## 6. Font & Warna
- Font umum table: `text-sm`
- Header table: `text-xs`
- Label input: `text-sm`
- Warna utama:
  - Primary: `text-primary` / `bg-primary`
  - Netral: `text-gray-500`, `text-gray-700`
  - Border: `border-gray-300`

---

Dokumen ini jadi acuan semua halaman list agar konsisten dengan halaman Produk & Customers.
