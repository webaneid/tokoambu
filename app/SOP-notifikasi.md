# SOP Notifikasi Modal - Toko Ambu

Dokumentasi lengkap untuk sistem notifikasi modal yang dapat digunakan kembali di seluruh aplikasi storefront.

## 📋 Daftar Isi

1. [Overview](#overview)
2. [Jenis Notifikasi](#jenis-notifikasi)
3. [Cara Penggunaan](#cara-penggunaan)
4. [Contoh Implementasi](#contoh-implementasi)
5. [Customization](#customization)
6. [Best Practices](#best-practices)

---

## Overview

Sistem notifikasi modal adalah komponen global yang tersedia di semua halaman storefront (yang menggunakan layout `app-mobile.blade.php`). Modal ini memberikan feedback visual yang lebih baik dibandingkan `alert()` JavaScript standar.

### Lokasi File

**HTML/Blade:**
- Layout: `/resources/views/storefront/layouts/app-mobile.blade.php` (baris 201-253)

**SCSS:**
- Component: `/resources/scss/components/_cart-modal.scss`
- Import: `/resources/scss/app.scss` (baris 28)

**JavaScript:**
- Global functions di `app-mobile.blade.php` (baris 224-253)

---

## Jenis Notifikasi

### 1. Success Modal (✅ Sudah Tersedia)

**Warna:** Hijau (`--ane-color-success`)
**Icon:** Checkmark dalam lingkaran
**Digunakan untuk:** Konfirmasi sukses (add to cart, update data, dll)

### 2. Error Modal (❌ Belum Tersedia)

**Warna:** Merah (`--ane-color-danger`)
**Icon:** X dalam lingkaran
**Digunakan untuk:** Pesan error dan kegagalan

### 3. Warning Modal (⚠️ Belum Tersedia)

**Warna:** Kuning (`--ane-color-warning`)
**Icon:** Tanda seru dalam lingkaran
**Digunakan untuk:** Peringatan dan konfirmasi

---

## Cara Penggunaan

### Success Modal

```javascript
showCartSuccessModal(message, isPreorder);
```

**Parameters:**
- `message` (string): Pesan yang akan ditampilkan
- `isPreorder` (boolean): `true` jika untuk preorder, `false` untuk regular

**Contoh:**
```javascript
// Regular success
showCartSuccessModal('Produk berhasil ditambahkan ke keranjang', false);

// Preorder success
showCartSuccessModal('Produk preorder berhasil ditambahkan ke keranjang', true);

// Custom message
showCartSuccessModal('Jumlah produk berhasil diperbarui', false);
```

### Close Modal

```javascript
closeCartModal();
```

Modal juga akan otomatis tertutup ketika:
- User klik backdrop (area gelap di luar modal)
- User klik tombol "Lanjut Belanja"

---

## Contoh Implementasi

### 1. Add to Cart (Product Detail)

**File:** `/resources/views/storefront/shop/show.blade.php`

```javascript
fetch('{{ route("cart.store") }}', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
    },
    body: JSON.stringify({
        product_id: productId,
        quantity: quantity,
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        const isPreorder = {{ $product->allow_preorder ? 'true' : 'false' }};
        if (isPreorder) {
            showCartSuccessModal('Produk preorder berhasil ditambahkan ke keranjang', true);
        } else {
            showCartSuccessModal('Produk berhasil ditambahkan ke keranjang', false);
        }
    } else {
        alert(data.message || 'Gagal menambahkan ke keranjang');
    }
});
```

### 2. Update Quantity (Cart Page)

```javascript
fetch('/cart/update', {
    method: 'POST',
    // ... headers dan body
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        showCartSuccessModal('Jumlah produk berhasil diperbarui', false);
        // Update UI
    } else {
        alert(data.message);
    }
});
```

### 3. Remove from Cart

```javascript
fetch(`/cart/remove/${cartItemId}`, {
    method: 'DELETE',
    // ... headers
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        showCartSuccessModal('Produk dihapus dari keranjang', false);
        // Update UI
    }
});
```

### 4. Add to Wishlist

```javascript
fetch('/wishlist/add', {
    method: 'POST',
    // ... headers dan body
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        showCartSuccessModal('Produk ditambahkan ke wishlist', false);
    }
});
```

---

## Customization

### Mengubah Teks Title

Modal memiliki 2 jenis title:

**Regular Product:**
```
"Berhasil Ditambahkan!"
```

**Preorder Product:**
```
"Preorder Berhasil!"
```

Untuk mengubah, edit fungsi `showCartSuccessModal()` di `app-mobile.blade.php`:

```javascript
if (isPreorder) {
    titleEl.textContent = 'Preorder Berhasil!'; // Ubah di sini
} else {
    titleEl.textContent = 'Berhasil Ditambahkan!'; // Ubah di sini
}
```

### Mengubah Button Text

**Button 1 (Secondary):** "Lanjut Belanja"
**Button 2 (Primary):** "Buka Keranjang"

Edit di `app-mobile.blade.php` baris 213-218:

```blade
<button type="button" class="cart-modal__btn cart-modal__btn--secondary" onclick="closeCartModal()">
    Lanjut Belanja <!-- Ubah text di sini -->
</button>
<a href="{{ route('cart.index') }}" class="cart-modal__btn cart-modal__btn--primary">
    Buka Keranjang <!-- Ubah text di sini -->
</a>
```

### Mengubah Button Action

Untuk redirect ke halaman lain setelah success:

```blade
<button type="button" class="cart-modal__btn cart-modal__btn--primary" onclick="window.location.href='/checkout'">
    Checkout Sekarang
</button>
```

### Styling Custom

Semua styling ada di `/resources/scss/components/_cart-modal.scss`.

**Mengubah Warna Success Icon:**
```scss
.cart-modal__icon {
  background: var(--ane-color-success); // Ubah ke warna lain
}
```

**Mengubah Ukuran Modal:**
```scss
.cart-modal__content {
  max-width: 400px; // Ubah ukuran
  padding: var(--ane-spacing-2xl); // Ubah padding
}
```

**Mengubah Animasi:**
```scss
@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(-20px); // Ubah efek
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
```

---

## Best Practices

### ✅ DO (Lakukan)

1. **Gunakan untuk feedback positif:**
   ```javascript
   showCartSuccessModal('Data berhasil disimpan', false);
   ```

2. **Berikan pesan yang jelas dan spesifik:**
   ```javascript
   showCartSuccessModal('Alamat pengiriman berhasil diperbarui', false);
   ```

3. **Set isPreorder sesuai konteks:**
   ```javascript
   const isPreorder = product.allow_preorder;
   showCartSuccessModal('Produk ditambahkan ke keranjang', isPreorder);
   ```

4. **Gunakan untuk konfirmasi aksi penting:**
   - Add to cart ✅
   - Update quantity ✅
   - Remove from cart ✅
   - Add to wishlist ✅
   - Profile updated ✅

### ❌ DON'T (Jangan)

1. **Jangan pakai untuk error messages:**
   ```javascript
   // SALAH - gunakan alert() atau modal error
   showCartSuccessModal('Gagal menambahkan produk', false);
   ```

2. **Jangan pakai pesan yang terlalu panjang:**
   ```javascript
   // SALAH - terlalu panjang
   showCartSuccessModal('Produk Anda telah berhasil ditambahkan ke dalam keranjang belanja dan siap untuk checkout kapan saja', false);

   // BENAR - singkat dan jelas
   showCartSuccessModal('Produk ditambahkan ke keranjang', false);
   ```

3. **Jangan hardcode URL di fungsi global:**
   ```javascript
   // SALAH - URL hardcoded
   function showCartSuccessModal() {
       modal.innerHTML = '<a href="/cart">Go to Cart</a>';
   }

   // BENAR - gunakan route helper di Blade
   <a href="{{ route('cart.index') }}">Buka Keranjang</a>
   ```

4. **Jangan gunakan untuk setiap aksi kecil:**
   - Click bookmark icon ❌ (cukup ubah icon saja)
   - Hover product ❌
   - Scroll page ❌

---

## Troubleshooting

### Modal tidak muncul

**Penyebab:** Fungsi tidak terdefinisi
**Solusi:** Pastikan halaman extend dari `storefront.layouts.app-mobile`

```blade
@extends('storefront.layouts.app-mobile') // BENAR
@extends('storefront.layouts.app')        // SALAH - fungsi tidak ada
```

### Modal muncul tapi styling rusak

**Penyebab:** CSS belum di-compile
**Solusi:**
```bash
cd /Users/webane/sites/tokoambu/app
npm run build
php artisan view:clear
```

### Body masih bisa di-scroll saat modal terbuka

**Penyebab:** JavaScript error
**Solusi:** Cek console browser untuk error. Pastikan fungsi `showCartSuccessModal()` dipanggil dengan benar.

### Button tidak berfungsi

**Penyebab:** Route tidak ditemukan
**Solusi:** Pastikan route `cart.index` ada di `routes/web.php`

---

## Perubahan di Masa Depan

### Rencana Enhancement

1. **Error Modal** - untuk pesan error yang lebih user-friendly
2. **Warning Modal** - untuk konfirmasi aksi berbahaya (hapus item, dll)
3. **Auto-close timer** - modal auto-close setelah 3 detik
4. **Custom buttons** - bisa custom jumlah dan jenis button
5. **Loading state** - tampilkan spinner saat proses async

### Cara Menambah Error Modal (Template)

```javascript
function showErrorModal(message) {
    // Similar structure to success modal
    // Change icon to X
    // Change color to red
    // Keep same button structure
}
```

---

## Changelog

**v1.0.0 (2026-01-13)**
- ✅ Initial release
- ✅ Success modal dengan 2 buttons
- ✅ Support preorder vs regular mode
- ✅ Slide-in animation
- ✅ Mobile-first responsive design
- ✅ Click backdrop to close

---

## Credits

**Created by:** Claude Code
**Design System:** Toko Ambu Mobile-First
**Color Palette:** Orange Primary (#F17B0D), Green Success (#10B981)
**Framework:** Laravel 12 + Tailwind-like Custom SCSS

---

## Lihat Juga

- [SOP-front-end.md](SOP-front-end.md) - Guidelines frontend development
- [CLAUDE.md](CLAUDE.md) - Project overview dan conventions
- [_cart-modal.scss](resources/scss/components/_cart-modal.scss) - Styling lengkap modal
