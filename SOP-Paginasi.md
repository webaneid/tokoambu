# SOP-Paginasi - Standard Pagination

Dokumen ini adalah standar implementasi paginasi di Tokoambu agar konsisten di semua halaman list.

## Kapan Pagination Ditampilkan

Pagination **HANYA** ditampilkan jika `$data->lastPage() > 1` (lebih dari 1 halaman).

```php
@if ($orders->lastPage() > 1)
    {{-- Pagination card --}}
@endif
```

## Struktur Card Pagination

Card pagination terpisah dari card table, dengan jarak `mt-6`:

```blade
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
    <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600">
        {{-- Kiri: Total data --}}
        <div>Total order: {{ number_format($orders->total()) }}</div>

        {{-- Kanan: Navigasi pagination --}}
        <div class="flex items-center gap-2">
            {{-- Pagination buttons --}}
        </div>
    </div>
</div>
```

## Logic Halaman yang Ditampilkan

Tampilkan halaman dengan logic berikut (smart pagination):
- Halaman 1, 2
- Halaman sebelum current page (current - 1)
- Current page
- Halaman setelah current page (current + 1)
- Halaman akhir - 1, halaman akhir

Contoh implementasi:

```php
@php
    $currentPage = $orders->currentPage();
    $lastPage = $orders->lastPage();
    $pages = collect([
        1,
        2,
        $currentPage - 1,
        $currentPage,
        $currentPage + 1,
        $lastPage - 1,
        $lastPage,
    ])->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
        ->unique()
        ->sort()
        ->values();
@endphp
```

## Tombol Previous/Next

Tombol prev/next menggunakan heroicons chevron-left dan chevron-right:

```blade
{{-- Previous Button --}}
<a href="{{ $orders->previousPageUrl() ?? '#' }}"
   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $orders->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
   aria-label="Sebelumnya">
    <x-heroicon name="chevron-left" class="w-4 h-4" />
</a>

{{-- Next Button --}}
<a href="{{ $orders->nextPageUrl() ?? '#' }}"
   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $orders->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
   aria-label="Berikutnya">
    <x-heroicon name="chevron-right" class="w-4 h-4" />
</a>
```

### Style Tombol Previous/Next:
- Ukuran: `w-8 h-8` (32px x 32px)
- Border: `border border-gray-300`
- Text: `text-gray-600`
- Hover: `hover:bg-gray-50`
- Disabled: `opacity-50 pointer-events-none`

## Tombol Angka Halaman

```blade
@php $prev = null; @endphp
@foreach ($pages as $page)
    {{-- Gap indicator jika ada lompatan --}}
    @if ($prev && $page > $prev + 1)
        <span class="px-2 text-gray-400">...</span>
    @endif

    {{-- Page number button --}}
    <a href="{{ $orders->url($page) }}"
       class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
        {{ $page }}
    </a>

    @php $prev = $page; @endphp
@endforeach
```

### Style Angka Halaman:
- Ukuran: `w-8 h-8` (32px x 32px)
- Border radius: `rounded-lg`

**Default (not active):**
- Border: `border-gray-300`
- Text: `text-gray-600`
- Hover: `hover:bg-gray-50`

**Active (current page):**
- Border: `border-primary`
- Background: `bg-primary`
- Text: `text-white`

## Gap Indicator (...)

Jika ada lompatan angka (misalnya dari halaman 2 ke 5), tampilkan `...`:

```blade
@if ($prev && $page > $prev + 1)
    <span class="px-2 text-gray-400">...</span>
@endif
```

Style: `px-2 text-gray-400`

## Total Data (Kiri)

Format: "Total [nama data]: [jumlah dengan format ribuan]"

```blade
<div>Total order: {{ number_format($orders->total()) }}</div>
```

Contoh output:
- Total order: 1,234
- Total produk: 567
- Total invoice: 89

## Contoh Lengkap

```blade
@if ($orders->lastPage() > 1)
    @php
        $currentPage = $orders->currentPage();
        $lastPage = $orders->lastPage();
        $pages = collect([
            1,
            2,
            $currentPage - 1,
            $currentPage,
            $currentPage + 1,
            $lastPage - 1,
            $lastPage,
        ])->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
            ->unique()
            ->sort()
            ->values();
    @endphp
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
        <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600">
            <div>Total order: {{ number_format($orders->total()) }}</div>
            <div class="flex items-center gap-2">
                <a href="{{ $orders->previousPageUrl() ?? '#' }}"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $orders->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                   aria-label="Sebelumnya">
                    <x-heroicon name="chevron-left" class="w-4 h-4" />
                </a>
                @php $prev = null; @endphp
                @foreach ($pages as $page)
                    @if ($prev && $page > $prev + 1)
                        <span class="px-2 text-gray-400">...</span>
                    @endif
                    <a href="{{ $orders->url($page) }}"
                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                        {{ $page }}
                    </a>
                    @php $prev = $page; @endphp
                @endforeach
                <a href="{{ $orders->nextPageUrl() ?? '#' }}"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $orders->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
                   aria-label="Berikutnya">
                    <x-heroicon name="chevron-right" class="w-4 h-4" />
                </a>
            </div>
        </div>
    </div>
@endif
```

## Responsivitas

- Container pagination menggunakan `flex-wrap` untuk mobile
- Gap `gap-4` antara total dan navigasi
- Gap `gap-2` antara tombol pagination
- Text size: `text-sm` untuk total data

---

**PENTING:** Semua halaman list yang menggunakan pagination harus mengikuti format ini agar konsisten dengan halaman `/orders`.
