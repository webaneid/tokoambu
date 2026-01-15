# SOP-table - Standard Table + Mobile Card

Dokumen ini adalah standar UI table list Tokoambu (desktop + mobile) agar konsisten di semua halaman.

## Kapan Dipakai
- Dipakai untuk semua halaman list data (produk, order, invoice, pembayaran, dll).
- Desktop menampilkan tabel.
- Mobile menampilkan kartu (card list).

## Struktur Utama (Card Table)
Gunakan wrapper card sesuai SOP halaman:

```
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
  {{-- isi mobile + desktop --}}
</div>
```

## Mobile (Card List)
Gunakan block di mobile dan sembunyikan di desktop:

```
<div class="block sm:hidden divide-y divide-gray-200">
  @foreach($items as $item)
    <div class="p-4 space-y-2">
      ...
    </div>
  @endforeach
</div>
```

### Elemen di Card
Gunakan pola berikut:
- Judul utama: `text-sm font-semibold text-gray-900`
- Subjudul: `text-sm text-gray-500`
- Meta info kecil: `text-sm text-gray-500`
- Badge status: `inline-flex px-3 py-1 rounded-full text-xs font-medium`
- Aksi: `flex items-center gap-3 pt-2`
- Ikon aksi ukuran `w-4 h-4` (1rem).

Contoh skeleton:

```
<div class="p-4 space-y-2">
  <div class="flex items-start justify-between gap-3">
    <div class="min-w-0">
      <p class="text-sm font-semibold text-gray-900">Judul</p>
      <p class="text-sm text-gray-500">Subjudul</p>
    </div>
  </div>

  <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
    <span>Meta 1</span>
    <span class="text-gray-300">•</span>
    <span>Meta 2</span>
  </div>

  <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
    Status
  </span>

  <div class="flex items-center gap-3 pt-2">
    {{-- ikon aksi --}}
  </div>
</div>
```

## Desktop (Table)
Gunakan table sesuai SOP-01:

```
<div class="hidden sm:block">
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">...</thead>
    <tbody class="bg-white divide-y divide-gray-200">...</tbody>
  </table>
</div>
```

### Header Table
```
<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
  Header
</th>
```

### Body Cell
- Default: `text-sm text-gray-500`
- Angka kanan: `text-right`
- Highlight: `text-sm font-medium text-gray-900`

### Sorting
Jika sortable, gunakan link dengan indikator:

```
<a href="{{ $sortUrl('column') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
  Nama <span class="text-[8px] leading-none">{{ $sortIndicator('column') }}</span>
</a>
```

## Ikon Aksi
Gunakan heroicons outline:

```
<x-heroicon name="eye" class="w-4 h-4" />
<x-heroicon name="pencil-square" class="w-4 h-4" />
<x-heroicon name="trash" class="w-4 h-4" />
```

Warna:
- Lihat/Edit: `text-blue`
- Hapus: `text-red-600`

## Responsif
- Mobile: card list (`block sm:hidden`)
- Desktop: tabel (`hidden sm:block`)

Pastikan keduanya berada di card yang sama agar konsisten.
