# SOP: Frontend Development Workflow

> **DOKUMEN WAJIB DIBACA SEBELUM MENGERJAKAN FRONTEND**
>
> Dokumen ini menjelaskan step-by-step lengkap cara membuat halaman frontend di aplikasi Toko Ambu.
> Ikuti setiap langkah dengan teliti untuk menghindari kesalahan.

---

## 🎯 Prinsip Dasar yang WAJIB Dipahami

### 1. Ini Aplikasi EXISTING - Bukan Aplikasi Baru!

**YANG HARUS DILAKUKAN:**
- ✅ **CEK DULU** - Selalu lihat file yang sudah ada sebelum coding
- ✅ **TIRU POLA** - Copy pattern dan struktur yang sudah ada
- ✅ **KONSISTEN** - Ikuti naming convention dan coding style yang sudah diterapkan
- ✅ **TANYA DULU** - Jika ragu, tanya atau cek dokumentasi

**YANG TIDAK BOLEH DILAKUKAN:**
- ❌ **JANGAN** edit file secara sembarangan tanpa memahami fungsinya
- ❌ **JANGAN** membuat pola baru jika sudah ada pola yang serupa
- ❌ **JANGAN** menghapus code yang tidak kamu pahami
- ❌ **JANGAN** menambahkan method/function jika sudah ada (CEK DULU!)

**Contoh Kesalahan yang Sering Terjadi:**
```php
// ❌ SALAH - Menambahkan method tanpa cek apakah sudah ada
class ProductVariant extends Model
{
    // Method ini SUDAH ADA di model!
    public function featuredMedia() {
        return $this->belongsTo(Media::class);
    }
}
```

**Cara yang Benar:**
```bash
# Step 1: CEK DULU dengan Read tool atau grep
grep -n "featuredMedia" app/Models/ProductVariant.php

# Step 2: Jika sudah ada, JANGAN tambahkan lagi
# Step 3: Jika belum ada, baru tambahkan
```

### 2. Frontend Mengikuti Backend (BUKAN SEBALIKNYA)

**Prinsip Utama:**
```
Backend (Laravel) → Controller → View (Blade) → SCSS → Browser
       ↓
   [SUDAH OK]
       ↓
Frontend tinggal tampilkan saja
```

**Artinya:**
- ✅ Backend **SUDAH** menyediakan semua data yang dibutuhkan
- ✅ Controller **SUDAH** passing data ke view dengan benar
- ✅ Model **SUDAH** punya relationship dan logic yang lengkap
- ✅ Frontend **HANYA** perlu tampilkan data dengan UI yang bagus

**Yang TIDAK Boleh:**
- ❌ Mengubah struktur database untuk keperluan UI
- ❌ Menambah/mengurangi field di request validation tanpa alasan jelas
- ❌ Mengubah logic controller karena UI tidak sesuai
- ❌ Menambah relationship baru di model tanpa cek apakah sudah ada

**Contoh yang Benar:**
```php
// Controller sudah passing data lengkap
public function index()
{
    $items = $this->cartService->getItems(); // ← Data sudah lengkap
    return view('storefront.cart.index', compact('items'));
}
```

```blade
{{-- Frontend tinggal tampilkan --}}
@foreach ($items as $item)
    <h3>{{ $item->product->name }}</h3>
    @if($item->variant)
        <p>{{ $item->variant->display_name }}</p>
    @endif
@endforeach
```

### 3. Mobile-First Design - 480px Max Width

**Konsep:**
- 📱 Semua halaman dirancang untuk layar **maksimal 480px**
- 📱 Desktop pun menampilkan versi mobile (tidak ada versi desktop terpisah)
- 📱 Semua interaksi dirancang untuk touch (`:active`, bukan `:hover`)
- 📱 Font size, spacing, button size dirancang untuk mobile

**Layout yang Digunakan:**
```blade
{{-- ✅ BENAR - Untuk storefront --}}
@extends('storefront.layouts.app-mobile')

{{-- ❌ SALAH - Ini untuk admin panel --}}
@extends('layouts.app')
```

**Viewport Meta Tag (sudah ada di layout):**
```html
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
```

**Max Width Container:**
```scss
.storefront-main {
  max-width: var(--ane-max-width-mobile); // 480px
  margin: 0 auto;
}
```

### 4. TIDAK Menggunakan Bootstrap di Storefront

**Teknologi yang Digunakan:**
```
Backend (Admin)    → Tailwind CSS
Storefront (User)  → Custom SCSS + CSS Variables
```

**Class yang TIDAK BOLEH Digunakan di Storefront:**
```html
<!-- ❌ SALAH - Ini Bootstrap class -->
<div class="container">
<div class="row">
<div class="col-12 col-md-6">
<div class="card">
<div class="card-body">
<button class="btn btn-primary">
<div class="d-flex justify-content-between">
<span class="text-muted">

<!-- ✅ BENAR - Gunakan custom class -->
<div class="storefront-main">
<div class="cart-items-list">
<div class="cart-item-card">
<button class="btn btn-primary">
<div class="cart-summary">
<span class="summary-label">
```

**Cara Deteksi Bootstrap:**
- Jika ada class yang diawali `btn-`, `col-`, `d-`, `text-`, `bg-` → kemungkinan Bootstrap
- Jika ada class seperti `container`, `row`, `card`, `card-body` → pasti Bootstrap
- **CEK file SCSS** - jika tidak ada definisi class tersebut di SCSS, berarti salah

**Cara yang Benar:**
1. Lihat file SCSS yang sudah ada
2. Gunakan class yang sudah didefinisikan
3. Jika belum ada, buat class baru di SCSS
4. Jangan pernah gunakan class yang tidak ada di SCSS kita

---

## 📁 Struktur SCSS

### Lokasi File SCSS
```
app/resources/scss/
├── abstracts/
│   ├── _variables.scss    # CSS custom properties (--ane-*)
│   └── _mixins.scss        # Reusable mixins
├── base/
│   └── _reset.scss         # CSS reset
├── components/
│   ├── _button.scss        # Komponen button
│   ├── _header.scss        # Header storefront
│   ├── _bottom-nav.scss    # Bottom navigation
│   └── ...                 # Komponen lainnya
├── layouts/
│   └── _app.scss           # Layout utama
├── pages/
│   ├── _shop.scss          # Halaman shop
│   ├── _product-detail.scss
│   ├── _cart.scss          # Halaman cart
│   └── ...                 # Halaman lainnya
└── app.scss                # Entry point (import semua file)
```

### CSS Custom Properties (Variables)
Semua styling menggunakan format `var(--ane-*)`:

**Colors:**
```scss
--ane-color-primary        // Orange (#F17B0D)
--ane-color-primary-hover  // Darker orange
--ane-color-primary-light  // Light orange
--ane-color-light          // White (#FFFFFF)
--ane-color-gray-100       // Lightest gray
--ane-color-gray-200       // Light gray (borders)
--ane-color-gray-400       // Medium gray
--ane-color-text-primary   // Dark text
--ane-color-text-secondary // Gray text
--ane-color-text-muted     // Lighter text
```

**Spacing:**
```scss
--ane-spacing-xs   // Extra small
--ane-spacing-sm   // Small
--ane-spacing-md   // Medium
--ane-spacing-lg   // Large
--ane-spacing-xl   // Extra large
--ane-spacing-2xl  // 2x Extra large
```

**Border Radius:**
```scss
--ane-radius-sm    // Small radius
--ane-radius-md    // Medium radius
--ane-radius-lg    // Large radius
--ane-radius-xl    // Extra large
--ane-radius-2xl   // 2x Extra large
--ane-radius-full  // Fully rounded (circle)
```

**Font Sizes:**
```scss
--ane-font-size-xs    // Extra small
--ane-font-size-sm    // Small
--ane-font-size-base  // Base (16px)
--ane-font-size-lg    // Large
--ane-font-size-xl    // Extra large
--ane-font-size-2xl   // 2x Extra large
```

**Z-Index:**
```scss
--ane-z-sticky  // 1010 (sticky elements)
--ane-z-fixed   // 1020 (fixed elements like bottom-nav)
--ane-z-modal   // 1050 (modals, overlays)
```

**Shadows:**
```scss
--ane-shadow-sm  // Small shadow
--ane-shadow-md  // Medium shadow
--ane-shadow-lg  // Large shadow
```

---

## 🛠️ Workflow Membuat Halaman Baru (STEP-BY-STEP LENGKAP)

> **PENTING:** Ikuti urutan ini dengan ketat. Jangan skip step apapun!

---

### 📋 Step 0: Pahami Task dan Planning

**Sebelum mulai coding, jawab pertanyaan ini:**

1. **Apa tujuan halaman ini?**
   - Contoh: "Halaman keranjang belanja untuk menampilkan produk yang akan dibeli"

2. **Data apa yang dibutuhkan?**
   - Contoh: "List cart items, product name, variant, price, quantity, total"

3. **Apakah backend sudah siap?**
   - ✅ Cek controller: Apakah method sudah ada?
   - ✅ Cek route: Apakah route sudah didefinisikan?
   - ✅ Cek data: Apakah controller sudah passing data ke view?

4. **Adakah halaman serupa yang bisa dijadikan referensi?**
   - Contoh: Jika bikin cart, lihat shop page untuk referensi product card

**Contoh Planning:**
```
Task: Membuat halaman keranjang belanja
Backend: ✅ CartController sudah ada, method index() sudah passing $items
Route: ✅ route('cart.index') sudah ada
Referensi: shop/index.blade.php (untuk product card structure)
```

---

### 📖 Step 1: CEK File yang Sudah Ada (WAJIB!)

**JANGAN SKIP STEP INI! Ini step paling penting.**

#### 1.1. Identifikasi Halaman Serupa

**Pertanyaan:** Halaman apa yang paling mirip dengan yang akan dibuat?

| Halaman Baru | Referensi yang Cocok |
|--------------|---------------------|
| Cart | Shop (product listing) |
| Checkout | Order detail |
| Profile | Account settings |
| Product Detail | Existing product detail |
| Category | Shop dengan filter |

#### 1.2. Baca File Blade Referensi

**Lokasi:** `resources/views/storefront/[section]/[page].blade.php`

**Yang Harus Diperhatikan:**

```blade
{{-- 1. Layout apa yang digunakan? --}}
@extends('storefront.layouts.app-mobile')  ← Catat ini

{{-- 2. Section apa yang digunakan? --}}
@section('title', 'Shop - Toko Ambu')  ← Title format
@section('content')  ← Content section

{{-- 3. Struktur HTML umum --}}
<div class="storefront-app">
    <main class="storefront-main">
        {{-- Header pattern --}}
        <div class="shop-header">...</div>

        {{-- Content pattern --}}
        <div class="product-grid">...</div>
    </main>
</div>

{{-- 4. Class naming convention --}}
{{-- Perhatikan: shop-header, product-grid, product-card, dll --}}

{{-- 5. Cara menampilkan image --}}
@php
    $imageUrl = $product->featuredMedia
        ? Storage::url($product->featuredMedia->path)
        : null;
@endphp

{{-- 6. Scripts pattern --}}
@push('scripts')
<script>
// JavaScript code
</script>
@endpush
```

**Action Items:**
- [ ] Buka file blade referensi dengan Read tool
- [ ] Screenshot atau catat struktur HTML utama
- [ ] Catat semua class yang digunakan
- [ ] Catat cara menampilkan data (loop, conditional, dll)
- [ ] Catat cara menampilkan image
- [ ] Catat JavaScript pattern jika ada

#### 1.3. Baca File SCSS Referensi

**Lokasi:** `resources/scss/pages/_[page].scss`

**Yang Harus Diperhatikan:**

```scss
/**
 * PAGES — SHOP
 * ============
 * Shopping page with product grid
 */

// 1. Import apa yang digunakan?
@use '../abstracts/mixins' as *;  ← Selalu ada

// 2. Struktur class hierarchy
.shop-header {          // ← Parent class
  .back-btn { }         // ← Child class (nested)
  .shop-title { }       // ← Sibling class
}

// 3. CSS Variables apa yang dipakai?
var(--ane-color-primary)
var(--ane-spacing-lg)
var(--ane-radius-xl)

// 4. Mixins apa yang dipakai?
@include flex-center;
@include smooth-transition;

// 5. Pattern responsive
@media (max-width: 360px) {
  // Styles for very small screens
}
```

**Action Items:**
- [ ] Buka file SCSS referensi dengan Read tool
- [ ] Catat semua CSS variables yang digunakan
- [ ] Catat semua mixins yang digunakan
- [ ] Catat pattern naming (BEM atau tidak)
- [ ] Catat pattern nesting (max 3 level)

#### 1.4. Cek Components yang Tersedia

**Lokasi:** `resources/scss/components/`

**Components yang Sudah Ada:**
- `_button.scss` → Button styles
- `_header.scss` → Header storefront
- `_bottom-nav.scss` → Bottom navigation
- `_hero-banner.scss` → Hero banner
- `_category-circles.scss` → Category circles
- `_product-card.scss` → Product card

**Cara Cek:**
```bash
# List semua components
ls resources/scss/components/

# Baca component tertentu
cat resources/scss/components/_button.scss
```

**Action Items:**
- [ ] Cek apakah ada component yang bisa dipakai ulang
- [ ] Jika ada button, gunakan component `_button.scss`
- [ ] Jika ada product card, gunakan component `_product-card.scss`
- [ ] Catat class name component yang akan dipakai

#### 1.5. Checklist Setelah CEK

**Pastikan kamu sudah:**
- [ ] Baca minimal 1 file blade referensi lengkap
- [ ] Baca minimal 1 file SCSS referensi lengkap
- [ ] Catat semua class yang akan dipakai ulang
- [ ] Catat CSS variables yang sering dipakai
- [ ] Catat mixins yang tersedia
- [ ] Cek component yang bisa dipakai ulang

**Jika belum semua ✅, JANGAN lanjut ke Step 2!**

---

### 🎨 Step 2: Buat Wireframe / Sketch (Optional tapi Recommended)

**Buat sketch sederhana struktur halaman:**

```
┌─────────────────────────┐
│ ← Back    Title      🛒 │ ← Header (sticky)
├─────────────────────────┤
│                         │
│  ┌───┐  Product Name    │ ← Cart Item Card
│  │IMG│  Variant         │
│  │   │  SKU: XXX        │
│  └───┘  Rp 100.000   ❌ │
│         [−] 1 [+]       │
│                         │
│  ┌───┐  Product Name    │ ← Cart Item Card
│  │IMG│  Variant         │
│  │   │  SKU: YYY        │
│  └───┘  Rp 150.000   ❌ │
│         [−] 2 [+]       │
│                         │
├─────────────────────────┤
│ Subtotal   Rp 400.000   │ ← Cart Summary (fixed bottom)
│ Total      Rp 400.000   │
│ [    Checkout    ]      │
└─────────────────────────┘
```

**Mapping ke Class:**
```
Header          → .cart-header
Cart Item       → .cart-item-card
  Image         → .cart-item-image
  Info          → .cart-item-info
  Delete btn    → .cart-item-delete
  Quantity      → .cart-item-quantity
Summary         → .cart-summary (fixed bottom)
```

---

### 📝 Step 3: Buat File Blade View

**Lokasi:** `resources/views/storefront/[section]/[page].blade.php`

**Contoh:** `resources/views/storefront/cart/index.blade.php`

#### 3.1. Copy Template dari Referensi

**JANGAN buat dari nol! Copy dari file yang sudah ada.**

```bash
# Contoh: Copy struktur dari shop
# Tapi jangan copy isi, hanya struktur dasarnya
```

#### 3.2. Struktur File Blade Lengkap

```blade
{{-- ============================================ --}}
{{-- 1. EXTENDS LAYOUT --}}
{{-- ============================================ --}}
@extends('storefront.layouts.app-mobile')

{{-- ============================================ --}}
{{-- 2. TITLE SECTION --}}
{{-- ============================================ --}}
@section('title', 'Keranjang Belanja - Toko Ambu')

{{-- ============================================ --}}
{{-- 3. CONTENT SECTION --}}
{{-- ============================================ --}}
@section('content')
<div class="storefront-app">
    <main class="storefront-main">

        {{-- ========================================== --}}
        {{-- HEADER (Sticky) --}}
        {{-- ========================================== --}}
        <div class="cart-header">
            {{-- Back Button --}}
            <a href="{{ route('shop.index') }}" class="back-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>

            {{-- Title --}}
            <h1 class="cart-title">Keranjang Belanja</h1>

            {{-- Spacer for centering --}}
            <div style="width: 24px;"></div>
        </div>

        {{-- ========================================== --}}
        {{-- CONDITIONAL: Cart Not Empty --}}
        {{-- ========================================== --}}
        @if (!$items->isEmpty())

            {{-- ====================================== --}}
            {{-- CART ITEMS LIST --}}
            {{-- ====================================== --}}
            <div class="cart-items-list">
                @foreach ($items as $item)
                    <div class="cart-item-card" data-cart-item-id="{{ $item->id }}">

                        {{-- Product Image --}}
                        <div class="cart-item-image">
                            @php
                                // Image priority: variant image > product image
                                $imageUrl = null;
                                if ($item->variant && $item->variant->featuredMedia) {
                                    $imageUrl = Storage::url($item->variant->featuredMedia->path);
                                } elseif ($item->product->featuredMedia) {
                                    $imageUrl = Storage::url($item->product->featuredMedia->path);
                                }
                            @endphp

                            @if ($imageUrl)
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $item->product->name }}"
                                >
                            @else
                                {{-- Placeholder --}}
                                <div class="cart-item-image-placeholder">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Product Info --}}
                        <div class="cart-item-info">
                            <h3 class="cart-item-name">{{ $item->product->name }}</h3>

                            @if($item->variant)
                                <p class="cart-item-variant">
                                    {{ $item->variant->display_name }}
                                </p>
                            @endif

                            <p class="cart-item-sku">
                                SKU: {{ $item->variant ? $item->variant->sku : $item->product->sku }}
                            </p>

                            <p class="cart-item-price">
                                Rp {{ number_format($item->price, 0, ',', '.') }}
                            </p>
                        </div>

                        {{-- Delete Button --}}
                        <button
                            class="cart-item-delete remove-item"
                            data-cart-item-id="{{ $item->id }}"
                            aria-label="Hapus item"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>

                        {{-- Quantity Controls --}}
                        <div class="cart-item-quantity">
                            <button
                                class="qty-btn decrease-qty"
                                type="button"
                                data-cart-item-id="{{ $item->id }}"
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>

                            <input
                                type="number"
                                class="qty-input"
                                value="{{ $item->quantity }}"
                                min="1"
                                max="99"
                                data-cart-item-id="{{ $item->id }}"
                                readonly
                            >

                            <button
                                class="qty-btn increase-qty"
                                type="button"
                                data-cart-item-id="{{ $item->id }}"
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ====================================== --}}
            {{-- CART SUMMARY (Fixed Bottom) --}}
            {{-- ====================================== --}}
            <div class="cart-summary">
                {{-- Summary Details --}}
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value" id="subtotal-display">
                        Rp {{ number_format($subtotal, 0, ',', '.') }}
                    </span>
                </div>

                <div class="summary-row total-row">
                    <span class="summary-label">Total</span>
                    <span class="summary-value total-value" id="total-display">
                        Rp {{ number_format($total, 0, ',', '.') }}
                    </span>
                </div>

                {{-- Checkout Button --}}
                @auth('customer')
                    <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-block btn-lg" style="margin-top: var(--ane-spacing-lg);">
                        Checkout
                    </a>
                @else
                    <a href="{{ route('customer.login') }}" class="btn btn-primary btn-block btn-lg" style="margin-top: var(--ane-spacing-lg);">
                        Login untuk Checkout
                    </a>
                @endauth
            </div>

        {{-- ========================================== --}}
        {{-- CONDITIONAL: Cart Empty --}}
        {{-- ========================================== --}}
        @else
            <div class="empty-cart">
                <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.3">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <h3 class="empty-cart-title">Keranjang Kosong</h3>
                <p class="empty-cart-text">Mulai berbelanja dan tambahkan produk ke keranjang Anda</p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary">
                    Mulai Belanja
                </a>
            </div>
        @endif

    </main>
</div>

{{-- ============================================ --}}
{{-- 4. SCRIPTS SECTION --}}
{{-- ============================================ --}}
@push('scripts')
<script>
// ==========================================
// Event Listeners
// ==========================================

// Increase quantity
document.querySelectorAll('.increase-qty').forEach(btn => {
    btn.addEventListener('click', function() {
        const cartItemId = this.dataset.cartItemId;
        const input = document.querySelector(`.qty-input[data-cart-item-id="${cartItemId}"]`);
        const newQty = parseInt(input.value) + 1;

        if (newQty <= 99) {
            input.value = newQty;
            updateCart(cartItemId, newQty);
        }
    });
});

// Decrease quantity
document.querySelectorAll('.decrease-qty').forEach(btn => {
    btn.addEventListener('click', function() {
        const cartItemId = this.dataset.cartItemId;
        const input = document.querySelector(`.qty-input[data-cart-item-id="${cartItemId}"]`);
        const newQty = parseInt(input.value) - 1;

        if (newQty >= 1) {
            input.value = newQty;
            updateCart(cartItemId, newQty);
        }
    });
});

// Remove item
document.querySelectorAll('.remove-item').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const cartItemId = this.dataset.cartItemId;

        if (confirm('Hapus produk dari keranjang?')) {
            removeFromCart(cartItemId);
        }
    });
});

// ==========================================
// API Functions
// ==========================================

// Update cart quantity
function updateCart(cartItemId, quantity) {
    fetch('{{ route("cart.update") }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({
            cart_item_id: cartItemId,
            quantity: quantity,
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateTotals(data.total);
            location.reload(); // Reload for simplicity
        } else {
            alert(data.message || 'Gagal memperbarui keranjang');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

// Remove from cart
function removeFromCart(cartItemId) {
    fetch(`/cart/${cartItemId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cartItem = document.querySelector(`[data-cart-item-id="${cartItemId}"]`);
            if (cartItem) {
                cartItem.remove();

                if (document.querySelectorAll('.cart-item-card').length === 0) {
                    location.reload();
                } else {
                    updateTotals(data.total);
                }
            }
        } else {
            alert(data.message || 'Gagal menghapus produk');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

// Update totals display
function updateTotals(total) {
    document.getElementById('subtotal-display').textContent =
        'Rp ' + total.toLocaleString('id-ID', { minimumFractionDigits: 0 });
    document.getElementById('total-display').textContent =
        'Rp ' + total.toLocaleString('id-ID', { minimumFractionDigits: 0 });
}
</script>
@endpush
@endsection
```

#### 3.3. Checklist Blade File

**Pastikan sudah ada:**
- [ ] `@extends('storefront.layouts.app-mobile')`
- [ ] `@section('title', '...')`
- [ ] `@section('content')` ... `@endsection`
- [ ] `<div class="storefront-app">` sebagai wrapper
- [ ] `<main class="storefront-main">` sebagai main container
- [ ] Semua class menggunakan custom class (bukan Bootstrap)
- [ ] Image menggunakan `Storage::url($media->path)`
- [ ] Route menggunakan `route('...')` helper
- [ ] Number format menggunakan `number_format()`
- [ ] Conditional `@if`, `@foreach`, `@auth` sesuai kebutuhan
- [ ] `@push('scripts')` jika ada JavaScript
- [ ] CSRF token `{{ csrf_token() }}` untuk form/AJAX
- [ ] Data attribute untuk JavaScript (`data-*`)

### 🎨 Step 4: Buat File SCSS

**Lokasi:** `resources/scss/pages/_namahalaman.scss`

**Contoh:** `resources/scss/pages/_cart.scss`

#### 4.1. Template Dasar SCSS

```scss
/**
 * PAGES — NAMA HALAMAN (UPPERCASE)
 * =================================
 * Deskripsi singkat halaman (1 kalimat)
 */

// ============================================================================
// IMPORTS
// ============================================================================
@use '../abstracts/mixins' as *;

// ============================================================================
// HEADER SECTION
// ============================================================================

.page-header {
  @include flex-between;
  padding: var(--ane-spacing-lg);
  background: var(--ane-color-light);
  border-bottom: 1px solid var(--ane-color-gray-200);
  position: sticky;
  top: 0;
  z-index: var(--ane-z-fixed);
}

.back-btn {
  @include flex-center;
  width: 40px;
  height: 40px;
  color: var(--ane-color-text-primary);
  text-decoration: none;
  @include smooth-transition;

  &:active {
    color: var(--ane-color-primary);
  }
}

.page-title {
  font-size: var(--ane-font-size-xl);
  font-weight: var(--ane-font-weight-bold);
  color: var(--ane-color-text-primary);
  margin: 0;
}

// ============================================================================
// CONTENT SECTION
// ============================================================================

.page-content {
  padding: var(--ane-spacing-lg);
  padding-bottom: calc(80px + var(--ane-spacing-lg)); // Space for bottom-nav
}

// ============================================================================
// CARD COMPONENT
// ============================================================================

.item-card {
  background: var(--ane-color-light);
  border: 1px solid var(--ane-color-gray-200);
  border-radius: var(--ane-radius-xl);
  padding: var(--ane-spacing-lg);
  margin-bottom: var(--ane-spacing-lg);
  box-shadow: var(--ane-shadow-sm);
  @include smooth-transition;
}

// ============================================================================
// RESPONSIVE
// ============================================================================

@media (max-width: 360px) {
  .page-content {
    padding: var(--ane-spacing-md);
  }
}
```

#### 4.2. Contoh Lengkap: Cart SCSS

```scss
/**
 * PAGES — CART
 * ============
 * Shopping cart page styles
 */

@use '../abstracts/mixins' as *;

// ============================================================================
// HEADER
// ============================================================================

.cart-header {
  @include flex-between;
  padding: var(--ane-spacing-lg);
  background: var(--ane-color-light);
  border-bottom: 1px solid var(--ane-color-gray-200);
  position: sticky;
  top: 0;
  z-index: var(--ane-z-fixed);
}

.cart-title {
  font-size: var(--ane-font-size-xl);
  font-weight: var(--ane-font-weight-bold);
  color: var(--ane-color-text-primary);
  margin: 0;
}

// ============================================================================
// CART ITEMS
// ============================================================================

.cart-items-list {
  padding: var(--ane-spacing-lg);
  padding-bottom: 320px; // Space for fixed summary
}

.cart-item-card {
  background: var(--ane-color-light);
  border: 1px solid var(--ane-color-gray-200);
  border-radius: var(--ane-radius-xl);
  padding: var(--ane-spacing-lg);
  margin-bottom: var(--ane-spacing-lg);
  display: grid;
  grid-template-columns: 80px 1fr auto;
  grid-template-rows: auto auto;
  gap: var(--ane-spacing-md);
  position: relative;
  box-shadow: var(--ane-shadow-sm);
  @include smooth-transition;
}

.cart-item-image {
  grid-row: 1 / 3;
  width: 80px;
  height: 80px;
  border-radius: var(--ane-radius-lg);
  overflow: hidden;
  background: var(--ane-color-gray-100);

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}

.cart-item-image-placeholder {
  width: 100%;
  height: 100%;
  @include flex-center;
  color: var(--ane-color-gray-400);
}

.cart-item-info {
  grid-column: 2;
  grid-row: 1;
  display: flex;
  flex-direction: column;
  gap: var(--ane-spacing-xs);
}

.cart-item-name {
  font-size: var(--ane-font-size-base);
  font-weight: var(--ane-font-weight-semibold);
  color: var(--ane-color-text-primary);
  margin: 0;
  line-height: var(--ane-line-height-tight);
}

.cart-item-variant {
  font-size: var(--ane-font-size-sm);
  color: var(--ane-color-text-muted);
  margin: 0;
}

.cart-item-sku {
  font-size: var(--ane-font-size-xs);
  color: var(--ane-color-gray-400);
  margin: 0;
}

.cart-item-price {
  font-size: var(--ane-font-size-lg);
  font-weight: var(--ane-font-weight-bold);
  color: var(--ane-color-primary);
  margin: 0;
}

.cart-item-delete {
  grid-column: 3;
  grid-row: 1;
  width: 40px;
  height: 40px;
  @include flex-center;
  background: transparent;
  border: none;
  color: var(--ane-color-primary);
  cursor: pointer;
  border-radius: var(--ane-radius-full);
  @include smooth-transition;

  &:active {
    background: var(--ane-color-primary-light);
    transform: scale(0.95);
  }
}

.cart-item-quantity {
  grid-column: 2 / 4;
  grid-row: 2;
  display: flex;
  align-items: center;
  gap: var(--ane-spacing-sm);
  justify-self: end;
}

.qty-btn {
  width: 36px;
  height: 36px;
  @include flex-center;
  background: var(--ane-color-gray-100);
  border: none;
  border-radius: var(--ane-radius-full);
  color: var(--ane-color-text-primary);
  cursor: pointer;
  @include smooth-transition;
  min-height: 36px; // Touch target

  &:active {
    background: var(--ane-color-gray-200);
    transform: scale(0.95);
  }
}

.qty-input {
  width: 50px;
  height: 36px;
  text-align: center;
  font-size: var(--ane-font-size-base);
  font-weight: var(--ane-font-weight-semibold);
  color: var(--ane-color-text-primary);
  border: none;
  background: transparent;
  -moz-appearance: textfield;

  &::-webkit-outer-spin-button,
  &::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
}

// ============================================================================
// CART SUMMARY (FIXED BOTTOM)
// ============================================================================

.cart-summary {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  max-width: var(--ane-max-width-mobile);
  margin: 0 auto;
  background: var(--ane-color-light);
  border-top: 1px solid var(--ane-color-gray-200);
  border-radius: var(--ane-radius-2xl) var(--ane-radius-2xl) 0 0;
  padding: var(--ane-spacing-xl) var(--ane-spacing-lg);
  padding-bottom: calc(var(--ane-spacing-xl) + env(safe-area-inset-bottom));
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.12);
  z-index: var(--ane-z-modal); // 1050
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--ane-spacing-md) 0;
}

.summary-label {
  font-size: var(--ane-font-size-sm);
  color: var(--ane-color-text-muted);
}

.summary-value {
  font-size: var(--ane-font-size-base);
  font-weight: var(--ane-font-weight-semibold);
  color: var(--ane-color-text-primary);
}

.total-row {
  border-top: 1px solid var(--ane-color-gray-200);
  padding-top: var(--ane-spacing-lg);
  margin-top: var(--ane-spacing-sm);

  .summary-label {
    font-size: var(--ane-font-size-base);
    font-weight: var(--ane-font-weight-semibold);
    color: var(--ane-color-text-primary);
  }

  .summary-value {
    font-size: var(--ane-font-size-2xl);
    font-weight: var(--ane-font-weight-bold);
    color: var(--ane-color-text-primary);
  }
}

// ============================================================================
// EMPTY STATE
// ============================================================================

.empty-cart {
  @include flex-center;
  flex-direction: column;
  padding: 60px var(--ane-spacing-xl);
  text-align: center;
  min-height: 60vh;

  svg {
    margin-bottom: var(--ane-spacing-2xl);
    color: var(--ane-color-gray-400);
  }
}

.empty-cart-title {
  font-size: var(--ane-font-size-xl);
  font-weight: var(--ane-font-weight-bold);
  color: var(--ane-color-text-primary);
  margin: 0 0 var(--ane-spacing-md) 0;
}

.empty-cart-text {
  font-size: var(--ane-font-size-sm);
  color: var(--ane-color-text-muted);
  margin: 0 0 var(--ane-spacing-2xl) 0;
  max-width: 300px;
}

// ============================================================================
// RESPONSIVE
// ============================================================================

@media (max-width: 360px) {
  .cart-item-card {
    grid-template-columns: 70px 1fr;
    gap: var(--ane-spacing-sm);
  }

  .cart-item-image {
    width: 70px;
    height: 70px;
  }

  .cart-item-delete {
    position: absolute;
    top: var(--ane-spacing-md);
    right: var(--ane-spacing-md);
    width: 32px;
    height: 32px;
  }

  .cart-item-quantity {
    grid-column: 1 / 3;
    justify-self: start;
  }
}
```

#### 4.3. Aturan Penulisan SCSS

**DO:**
- ✅ Gunakan `@use '../abstracts/mixins' as *;` di awal file
- ✅ Gunakan CSS variables (`var(--ane-*)`) untuk semua nilai
- ✅ Gunakan mixins yang tersedia (`@include flex-center`, dll)
- ✅ Nesting maksimal 3 level
- ✅ Indentasi 2 spasi
- ✅ Comment section dengan `// ===...===`
- ✅ Gunakan `:active` untuk mobile interactions
- ✅ Tambahkan `@media (max-width: 360px)` jika perlu

**DON'T:**
- ❌ Hardcode warna (gunakan variables)
- ❌ Hardcode spacing (gunakan variables)
- ❌ Hardcode font-size (gunakan variables)
- ❌ Gunakan `:hover` (ini mobile-first, gunakan `:active`)
- ❌ Nesting lebih dari 3 level
- ❌ Import file lain selain mixins
- ❌ Buat variable baru tanpa menambahkan ke `_variables.scss`

#### 4.4. Checklist SCSS File

**Pastikan sudah ada:**
- [ ] Header comment dengan nama halaman dan deskripsi
- [ ] Import mixins: `@use '../abstracts/mixins' as *;`
- [ ] Semua warna menggunakan `var(--ane-color-*)`
- [ ] Semua spacing menggunakan `var(--ane-spacing-*)`
- [ ] Semua font-size menggunakan `var(--ane-font-size-*)`
- [ ] Semua border-radius menggunakan `var(--ane-radius-*)`
- [ ] Z-index menggunakan `var(--ane-z-*)`
- [ ] Button menggunakan `:active` bukan `:hover`
- [ ] Touch target minimal 44px untuk button
- [ ] Responsive untuk 360px jika diperlukan
- [ ] Section comments untuk organisasi code

---

### 📦 Step 5: Import SCSS di app.scss

**Lokasi:** `resources/scss/app.scss`

#### 5.1. Buka File app.scss

```bash
# Read file terlebih dahulu
cat resources/scss/app.scss
```

#### 5.2. Tambahkan Import di Section PAGES

**PENTING:** Import harus ditambahkan di section yang benar!

```scss
/**
 * TOKO AMBU — STOREFRONT SCSS
 * ============================
 * Mobile-First Design (340px max-width)
 * Modular SCSS Architecture with CSS Variables
 */

// ============================================================================
// 1. ABSTRACTS
// ============================================================================
@use 'abstracts/variables';
@use 'abstracts/mixins' as *;

// ============================================================================
// 2. BASE
// ============================================================================
@use 'base/reset';

// ============================================================================
// 3. LAYOUTS
// ============================================================================
@use 'layouts/app';

// ============================================================================
// 4. COMPONENTS
// ============================================================================
@use 'components/button';
@use 'components/header';
@use 'components/bottom-nav';
@use 'components/hero-banner';
@use 'components/category-circles';
@use 'components/product-card';

// ============================================================================
// 5. PAGES
// ============================================================================
@use 'pages/shop';
@use 'pages/product-detail';
@use 'pages/cart';
@use 'pages/checkout';          // ← Tambahkan di sini (contoh)
@use 'pages/namahalaman';       // ← Import file baru di sini
```

**Aturan Import:**
1. **Urutan harus sesuai:** abstracts → base → layouts → components → pages
2. **Nama file TANPA underscore:** `_cart.scss` → `@use 'pages/cart'`
3. **Nama file TANPA extension:** Jangan tulis `.scss`
4. **Alphabetical order:** Urutkan secara alfabet dalam section yang sama

#### 5.3. Checklist Import

- [ ] Import ditambahkan di section PAGES (section 5)
- [ ] Format: `@use 'pages/namafile';` (tanpa `_` dan `.scss`)
- [ ] Urutan sesuai: PAGES di paling bawah
- [ ] Tidak ada typo di nama file
- [ ] Semicolon (`;`) di akhir

---

### ⚙️ Step 6: Compile SCSS

**Lokasi:** Terminal di direktori `app/`

#### 6.1. Build untuk Production

```bash
cd app
npm run build
```

**Output:**
```
VITE v5.x.x  ready in XXX ms

✓ built in XXXms
✓ XX modules transformed.

dist/assets/app-[hash].js    XX.XX kB │ gzip: XX.XX kB
dist/assets/app-[hash].css   XX.XX kB │ gzip: XX.XX kB

✓ built in XXXms
```

#### 6.2. Development Mode (Auto-reload)

```bash
cd app
npm run dev
```

**Output:**
```
VITE v5.x.x  ready in XXX ms

➜  Local:   http://localhost:5173/
➜  Network: use --host to expose

➜  press h to show help
```

**Keuntungan `npm run dev`:**
- ✅ Auto-reload saat save file SCSS
- ✅ Hot Module Replacement (HMR)
- ✅ Faster development
- ✅ Error message realtime

**Catatan:**
- Jangan lupa jalankan Laravel server juga: `php artisan serve`
- Vite hanya untuk compile assets

#### 6.3. Troubleshooting Compile Error

**Error: "Cannot find module"**
```
✘ [ERROR] Cannot find module '@use pages/cart'
```

**Solusi:**
- Cek nama file: Apakah `_cart.scss` ada di `resources/scss/pages/`?
- Cek typo di `app.scss`: `@use 'pages/cart';` (bukan `@use 'pages/_cart'`)

**Error: "Undefined variable"**
```
✘ [ERROR] Undefined variable: "--ane-color-primary"
```

**Solusi:**
- Cek apakah variable ada di `resources/scss/abstracts/_variables.scss`
- Cek penulisan: `var(--ane-color-primary)` bukan `--ane-color-primary`

**Error: "Undefined mixin"**
```
✘ [ERROR] Undefined mixin: @include flex-center
```

**Solusi:**
- Cek import: Apakah ada `@use '../abstracts/mixins' as *;` di file SCSS?
- Cek nama mixin: Apakah benar `flex-center` atau `flexCenter`?

#### 6.4. Checklist Compile

- [ ] Command `npm run build` berjalan tanpa error
- [ ] File CSS ter-generate di `public/build/assets/`
- [ ] Browser bisa load CSS (cek Network tab di DevTools)
- [ ] Styling muncul di browser
- [ ] Tidak ada error di Console browser

---

### 🌐 Step 7: Test di Browser

#### 7.1. Buka Halaman di Browser

```bash
# Pastikan Laravel server running
php artisan serve

# Buka browser
http://localhost:8000/cart
```

#### 7.2. Test Viewport Mobile (480px)

**Chrome DevTools:**
1. Klik kanan → **Inspect**
2. Klik icon **Toggle Device Toolbar** (Ctrl+Shift+M)
3. Pilih **Responsive**
4. Set width: **480px** (atau pilih device: iPhone SE)

#### 7.3. Checklist Visual

**Layout:**
- [ ] Max-width 480px (tidak lebih lebar)
- [ ] Header sticky saat scroll
- [ ] Bottom nav tidak overlap dengan content
- [ ] Fixed summary tidak overlap dengan bottom nav
- [ ] Spacing konsisten dengan halaman lain

**Typography:**
- [ ] Font-size sesuai hierarki (title > body > small)
- [ ] Line-height tidak terlalu rapat
- [ ] Text color kontras dengan background

**Colors:**
- [ ] Primary color (orange) untuk CTA dan penting
- [ ] Gray untuk secondary text
- [ ] White background bersih

**Interactions:**
- [ ] Button ada feedback saat di-tap (`:active` state)
- [ ] Touch target minimal 44px
- [ ] Tidak ada elemen yang terlalu kecil untuk di-tap

**Images:**
- [ ] Image loading dengan benar
- [ ] Placeholder muncul jika tidak ada image
- [ ] Image tidak distort (aspect ratio benar)

**Responsive 360px:**
- [ ] Test juga di 360px (Galaxy S8)
- [ ] Semua element masih bisa di-tap
- [ ] Text tidak terpotong

#### 7.4. Test Functionality

**JavaScript:**
- [ ] Event listener berfungsi (click, input, dll)
- [ ] AJAX request berhasil (cek Network tab)
- [ ] Error handling berfungsi
- [ ] Loading state jika ada

**Data:**
- [ ] Data dari backend tampil dengan benar
- [ ] Format number (currency) benar
- [ ] Conditional rendering benar (@if, @foreach)

---

### ✅ Step 8: Final Checklist

**Sebelum commit, pastikan:**

#### Backend Integration
- [ ] Controller method sudah ada dan bekerja
- [ ] Route sudah didefinisikan
- [ ] Data passing ke view dengan benar
- [ ] Eager loading relationship jika perlu

#### Blade View
- [ ] Extends layout yang benar (`app-mobile`)
- [ ] Title section ada
- [ ] Struktur HTML semantic
- [ ] Tidak ada Bootstrap class
- [ ] Image menggunakan `Storage::url()`
- [ ] Route menggunakan `route()` helper
- [ ] CSRF token untuk form/AJAX
- [ ] Data attribute untuk JavaScript

#### SCSS
- [ ] File ada di `resources/scss/pages/`
- [ ] Import mixins di awal file
- [ ] Semua nilai menggunakan CSS variables
- [ ] Nesting maksimal 3 level
- [ ] Comment section yang jelas
- [ ] Responsive untuk 360px jika perlu
- [ ] Import sudah ditambahkan di `app.scss`

#### Compilation
- [ ] `npm run build` berhasil tanpa error
- [ ] CSS ter-generate di `public/build/assets/`
- [ ] File size reasonable (tidak terlalu besar)

#### Browser Testing
- [ ] Test di viewport 480px
- [ ] Test di viewport 360px
- [ ] Semua styling muncul dengan benar
- [ ] JavaScript berfungsi
- [ ] Tidak ada error di Console
- [ ] Performance OK (no lag saat scroll/interact)

#### Code Quality
- [ ] Tidak ada code yang duplikat
- [ ] Tidak ada inline style (kecuali spacer/dynamic)
- [ ] Tidak ada hardcoded value
- [ ] Naming convention konsisten
- [ ] Code rapi dan readable

#### Git
- [ ] File yang di-commit hanya file yang perlu
- [ ] Tidak commit `node_modules/`, `vendor/`, dll
- [ ] Commit message jelas dan deskriptif

**Jika semua ✅, BARU boleh commit!**

---

## 📚 Referensi & Resources

### File Penting yang WAJIB Dipahami

#### 1. CSS Variables (`resources/scss/abstracts/_variables.scss`)
**Buka file ini untuk melihat semua variable yang tersedia:**
```bash
cat resources/scss/abstracts/_variables.scss
```

**Isi file (summary):**
- Colors: `--ane-color-*`
- Spacing: `--ane-spacing-*`
- Font sizes: `--ane-font-size-*`
- Border radius: `--ane-radius-*`
- Z-index layers: `--ane-z-*`
- Shadows: `--ane-shadow-*`
- Transitions: `--ane-transition-*`

#### 2. Mixins (`resources/scss/abstracts/_mixins.scss`)
**Mixins yang tersedia untuk memudahkan styling:**

```scss
// Flexbox helpers
@include flex-center;      // Center horizontal & vertical
@include flex-between;     // Space between dengan align center
@include flex-column;      // Flex direction column

// Transitions
@include smooth-transition; // transition: all 0.2s ease

// Touch targets
@include touch-target;     // min-height 44px untuk mobile

// Text truncate
@include text-truncate;    // Ellipsis untuk text panjang
@include line-clamp($lines); // Multi-line truncate
```

#### 3. Button Component (`resources/scss/components/_button.scss`)
**Contoh component yang well-structured:**
- Struktur yang baik dengan modifier classes
- Menggunakan CSS variables
- Touch-friendly interaction
- Accessible (aria-label, focus states)

#### 4. Layout Mobile (`resources/views/storefront/layouts/app-mobile.blade.php`)
**Layout utama storefront:**
- Header dengan Vite directive
- Bottom navigation
- Safe area inset untuk iOS
- Meta tags untuk PWA

#### 5. Shop Page (Referensi Terbaik)
**Files:**
- Blade: `resources/views/storefront/shop/index.blade.php`
- SCSS: `resources/scss/pages/_shop.scss`

**Kenapa bagus?**
- Pattern product listing yang clean
- Image loading yang proper
- Grid layout responsive
- Empty state handling

---

## 🛠️ Command Reference (Cheat Sheet)

### Development Commands

```bash
# ============================================
# LARAVEL
# ============================================

# Start Laravel development server
php artisan serve

# Run queue worker
php artisan queue:work

# Run migrations
php artisan migrate

# Fresh migration with seeders
php artisan migrate:fresh --seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# ============================================
# VITE / NPM
# ============================================

# Install dependencies
npm install

# Development mode (auto-reload)
npm run dev

# Build for production
npm run build

# Watch files (compile on change)
npm run watch

# ============================================
# CODE QUALITY
# ============================================

# Format code with Laravel Pint
./vendor/bin/pint

# Format specific file
./vendor/bin/pint resources/views/storefront/cart/index.blade.php

# ============================================
# SEARCHING & FINDING
# ============================================

# Find files by pattern
find resources/views/storefront -name "*.blade.php"

# Search text in files (grep)
grep -r "product-card" resources/scss/

# Search with line numbers
grep -rn "featuredMedia" app/Models/

# Search in specific file type
grep -r --include="*.scss" "var(--ane-color-primary)" resources/scss/

# List files in directory
ls -la resources/scss/pages/

# ============================================
# FILE OPERATIONS
# ============================================

# Read file
cat resources/scss/app.scss

# Read file with line numbers
cat -n app/Models/ProductVariant.php

# Create directory
mkdir -p resources/views/storefront/checkout

# Copy file
cp resources/views/storefront/cart/index.blade.php resources/views/storefront/cart/index.blade.php.backup

# ============================================
# GIT
# ============================================

# Check status
git status

# Add specific file
git add resources/views/storefront/cart/index.blade.php
git add resources/scss/pages/_cart.scss

# Commit with message
git commit -m "feat: add cart page with mobile-first design"

# View recent commits
git log --oneline -10

# View changes before commit
git diff

# Discard changes in file
git checkout -- resources/views/storefront/cart/index.blade.php
```

---

## ❌ Common Mistakes & Solutions

### Mistake 1: Menggunakan Bootstrap Class

**❌ SALAH:**
```html
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <button class="btn btn-primary">Submit</button>
        </div>
      </div>
    </div>
  </div>
</div>
```

**✅ BENAR:**
```html
<div class="storefront-main">
  <div class="cart-items-list">
    <div class="cart-item-card">
      <button class="btn btn-primary">Submit</button>
    </div>
  </div>
</div>
```

---

### Mistake 2: Hardcode Warna dan Spacing

**❌ SALAH:**
```scss
.cart-summary {
  background: #FFFFFF;
  padding: 24px 16px;
  border-radius: 20px;
  color: #333333;
}
```

**✅ BENAR:**
```scss
.cart-summary {
  background: var(--ane-color-light);
  padding: var(--ane-spacing-xl) var(--ane-spacing-lg);
  border-radius: var(--ane-radius-2xl);
  color: var(--ane-color-text-primary);
}
```

---

### Mistake 3: Lupa Import Mixins

**❌ SALAH:**
```scss
/**
 * PAGES — CART
 */

.cart-header {
  @include flex-between; // ← ERROR: Undefined mixin
}
```

**✅ BENAR:**
```scss
/**
 * PAGES — CART
 */

@use '../abstracts/mixins' as *; // ← WAJIB ADA!

.cart-header {
  @include flex-between;
}
```

---

### Mistake 4: Menggunakan `:hover` di Mobile

**❌ SALAH:**
```scss
.btn {
  &:hover {
    background: var(--ane-color-primary-hover);
  }
}
```

**✅ BENAR:**
```scss
.btn {
  &:active {
    background: var(--ane-color-primary-hover);
    transform: scale(0.98);
  }
}
```

---

### Mistake 5: Menambahkan Method yang Sudah Ada

**❌ SALAH:**
```php
class ProductVariant extends Model
{
    // Langsung tambah tanpa cek
    public function featuredMedia()
    {
        return $this->belongsTo(Media::class);
    }
}
```

**✅ BENAR:**
```bash
# Step 1: CEK DULU
grep -n "featuredMedia" app/Models/ProductVariant.php

# Output: Line 56 sudah ada method featuredMedia()

# Step 2: Jangan tambahkan lagi!
```

---

### Mistake 6: Lupa Compile SCSS

**❌ SALAH:**
1. Edit file SCSS
2. Refresh browser
3. "Kok styling tidak berubah?" ❌

**✅ BENAR:**
1. Edit file SCSS
2. **Run `npm run build`** ✅
3. Refresh browser
4. Styling berubah ✅

---

### Mistake 7: Wrong Layout

**❌ SALAH:**
```blade
@extends('layouts.app') {{-- Ini layout admin (Tailwind) --}}
```

**✅ BENAR:**
```blade
@extends('storefront.layouts.app-mobile') {{-- Layout storefront --}}
```

---

### Mistake 8: Inline Style Everything

**❌ SALAH:**
```blade
<div style="display: flex; justify-content: space-between; padding: 24px; background: #FFFFFF;">
  <h1 style="font-size: 20px; font-weight: bold; color: #333;">Title</h1>
</div>
```

**✅ BENAR:**
```blade
<div class="cart-header">
  <h1 class="cart-title">Title</h1>
</div>
```

```scss
// Di _cart.scss
.cart-header {
  @include flex-between;
  padding: var(--ane-spacing-xl);
  background: var(--ane-color-light);
}

.cart-title {
  font-size: var(--ane-font-size-xl);
  font-weight: var(--ane-font-weight-bold);
  color: var(--ane-color-text-primary);
}
```

---

## 💡 Pro Tips

### Tip 1: Gunakan `npm run dev` saat Development
Jalankan di terminal terpisah agar auto-compile saat save file:
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

### Tip 2: Copy-Paste adalah Teman
Jangan malu copy dari file yang sudah ada. Konsistensi lebih penting dari originalitas.

### Tip 3: Chrome DevTools adalah Sahabat
- **Elements tab**: Inspect styling, test changes live
- **Console tab**: Cek JavaScript error
- **Network tab**: Cek request/response AJAX
- **Device toolbar**: Test responsive

### Tip 4: Comment yang Jelas
```scss
// ============================================================================
// CART SUMMARY (FIXED BOTTOM)
// ============================================================================
// Summary fixed di bottom dengan z-index lebih tinggi dari bottom-nav
// Padding-bottom menggunakan safe-area-inset untuk iOS
```

### Tip 5: Gunakan Data Attributes untuk JavaScript
```html
<button class="qty-btn" data-cart-item-id="{{ $item->id }}">+</button>
```

```javascript
const cartItemId = this.dataset.cartItemId;
```

### Tip 6: Format Currency dengan Benar
```blade
{{-- ✅ BENAR - Format Indonesia --}}
Rp {{ number_format($price, 0, ',', '.') }}

{{-- Output: Rp 100.000 --}}
```

### Tip 7: Eager Loading untuk Performance
```php
// ✅ BENAR - Load semua relationship sekaligus
$items = CartItem::with(['product.featuredMedia', 'variant.featuredMedia'])->get();

// ❌ SALAH - N+1 query problem
$items = CartItem::all();
foreach ($items as $item) {
    $item->product->featuredMedia; // Query untuk setiap item!
}
```

---

## 🆘 Troubleshooting Guide

### Problem: Styling Tidak Muncul

**Checklist:**
1. ✅ Apakah file SCSS sudah di-import di `app.scss`?
2. ✅ Apakah sudah run `npm run build`?
3. ✅ Apakah ada error saat compile? (cek terminal)
4. ✅ Apakah class name di HTML sama dengan di SCSS?
5. ✅ Apakah browser cache sudah di-clear? (Ctrl+Shift+R)

### Problem: JavaScript Tidak Jalan

**Checklist:**
1. ✅ Apakah ada error di Console browser?
2. ✅ Apakah selector benar? (cek class name)
3. ✅ Apakah event listener ditambahkan setelah DOM ready?
4. ✅ Apakah CSRF token sudah benar?
5. ✅ Apakah route API ada dan return JSON?

### Problem: Image Tidak Muncul

**Checklist:**
1. ✅ Apakah relationship sudah eager load?
2. ✅ Apakah path image benar? (cek `Storage::url()`)
3. ✅ Apakah file image exist di storage?
4. ✅ Apakah symlink `storage/app/public` → `public/storage` sudah dibuat?
   ```bash
   php artisan storage:link
   ```

### Problem: Layout Rusak di Mobile

**Checklist:**
1. ✅ Apakah viewport meta tag ada di layout?
2. ✅ Apakah max-width sudah diset 480px?
3. ✅ Apakah test di responsive mode DevTools?
4. ✅ Apakah ada element dengan width fixed > 480px?

### Problem: Compile Error "Cannot find module"

**Solusi:**
```bash
# Cek apakah file exist
ls resources/scss/pages/_cart.scss

# Cek typo di app.scss
cat resources/scss/app.scss | grep cart

# Pastikan format benar: @use 'pages/cart'; (tanpa _ dan .scss)
```

---

## 📖 Additional Resources

### Documentation Links
- [Laravel 12 Docs](https://laravel.com/docs/12.x)
- [Laravel Blade](https://laravel.com/docs/12.x/blade)
- [Vite with Laravel](https://laravel.com/docs/12.x/vite)
- [SCSS Documentation](https://sass-lang.com/documentation)

### Project Documentation
- `CLAUDE.md` - Project overview dan conventions
- `README.md` - Setup instructions
- `RAJAONGKIR_COMMAND_GUIDE.md` - Location system
- `SISTEM_ALAMAT_SUMMARY.md` - Address handling

### Learning Resources
- Cek file yang sudah ada di `resources/scss/`
- Cek component di `resources/scss/components/`
- Cek halaman existing di `resources/views/storefront/`

---

## 🎯 Quick Start Checklist

**Ketika dapat task buat halaman baru:**

1. [ ] **Step 0:** Pahami task, cek backend ready
2. [ ] **Step 1:** Baca file referensi (blade + SCSS)
3. [ ] **Step 2:** Buat wireframe/sketch struktur
4. [ ] **Step 3:** Buat file blade view
5. [ ] **Step 4:** Buat file SCSS
6. [ ] **Step 5:** Import SCSS di app.scss
7. [ ] **Step 6:** Compile dengan `npm run build`
8. [ ] **Step 7:** Test di browser (480px & 360px)
9. [ ] **Step 8:** Review final checklist
10. [ ] **Commit:** Jika semua ✅

---

## 📝 Commit Message Convention

```bash
# Format: <type>: <subject>

# Types:
feat: Fitur baru
fix: Bug fix
style: Styling changes (CSS/SCSS)
refactor: Refactoring code
docs: Documentation
chore: Maintenance tasks

# Examples:
git commit -m "feat: add cart page with mobile-first design"
git commit -m "fix: cart summary z-index below bottom-nav"
git commit -m "style: adjust cart item spacing for 360px viewport"
git commit -m "refactor: extract cart item card to component"
```

---

## 🔄 Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-01-12 | Initial SOP document |

---

**Terakhir diperbarui:** 2026-01-12
**Penulis:** Tim Development Toko Ambu
**Status:** ✅ Final Version

---

## ⚠️ DISCLAIMER

**BACA INI SEBELUM MULAI CODING:**

1. **Ini aplikasi EXISTING** - Jangan edit sembarangan
2. **Frontend mengikuti backend** - Backend sudah OK, focus di UI
3. **SELALU CEK DULU** - Jangan langsung coding
4. **Gunakan referensi** - Copy pattern yang sudah ada
5. **Test di mobile** - 480px max-width, test di 360px juga
6. **Compile SCSS** - Jangan lupa `npm run build`
7. **No Bootstrap** - Gunakan custom SCSS dengan CSS variables

**Jika ragu, TANYA DULU atau CEK dokumentasi!**

---

_Semoga SOP ini membantu menghindari kesalahan di kemudian hari. Happy coding! 🚀_
