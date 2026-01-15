# Toko Ambu — Storefront Development Blueprint

**Dokumen Perencanaan E-Commerce Storefront (Public Front-End)**

Tanggal: 2026-01-07
Status: Planning Phase

---

## 1) ANALISA SISTEM EXISTING

### 1.1 Infrastruktur Backend yang Sudah Ada ✅

**Database & Models:**
- ✅ `products` - SKU, name, description, category_id, supplier_id, cost_price, **selling_price**, is_active
- ✅ `product_categories` - name, description
- ✅ `media` - foto produk (type: product_photo)
- ✅ `customers` - name, phone, email, whatsapp, alamat lengkap (province, city, district, postal_code)
- ✅ `orders` - order_number, customer_id, type (order/preorder), status, total_amount, paid_amount, shipping address
- ✅ `order_items` - order_id, product_id, qty, price
- ✅ `payments` - order_id, amount, method, status (pending/verified), bukti transfer
- ✅ `shipments` - order_id, tracking_number, courier, shipping_cost, status
- ✅ `inventory_balances` - stok real-time per produk per lokasi
- ✅ `provinces`, `cities`, `districts` - data alamat Indonesia lengkap (RajaOngkir)

**Business Logic yang Sudah Solid:**
- ✅ **Order flow**: draft → waiting_payment → dp_paid → paid → packed → shipped → done
- ✅ **Payment system**: DP support, upload bukti transfer, verification flow
- ✅ **Inventory management**: Event-driven stock control, audit trail lengkap
- ✅ **Location autocomplete**: Province → City → District cascade dengan postal code
- ✅ **Invoice generation**: PDF invoice dengan branding
- ✅ **Shipping label**: Printable label untuk kurir

**Authentication (Laravel Breeze):**
- ✅ User model sudah ada (untuk admin/staff)
- ✅ Role-based access (Spatie Permission): Super Admin, Operator, Finance
- ❌ **Customer login belum ada** (masih managed by admin)

---

### 1.2 Gap Analysis: Apa yang Belum Ada untuk Storefront

#### ❌ **Yang BELUM Ada:**

1. **Customer Authentication & Account**
   - Customer registration & login
   - Customer dashboard (order history, profile)
   - Password reset untuk customer
   - Email verification (opsional)

2. **Shopping Cart System**
   - Cart model & table
   - Add to cart, update qty, remove item
   - Cart persistence (DB atau session)
   - Cart summary & checkout flow

3. **Product Catalog (Public View)**
   - Product listing dengan filter & search
   - Product detail page (public)
   - Category browsing
   - Stock availability check untuk customer
   - Product photos gallery (saat ini hanya 1 foto)

4. **Checkout Flow**
   - Cart review
   - Shipping address form (menggunakan autocomplete existing)
   - Shipping cost calculation (manual atau RajaOngkir integration)
   - Payment method selection
   - Order summary & confirmation

5. **Customer Order Management**
   - Track order status
   - Upload bukti transfer dari customer side
   - Download invoice (customer side)
   - Order cancellation request

6. **Public Routes & Controllers**
   - Storefront controller (product catalog)
   - Cart controller
   - Checkout controller
   - Customer account controller

7. **Frontend Design (Public)**
   - Landing page / homepage
   - Product listing page
   - Product detail page
   - Cart page
   - Checkout page
   - Customer account pages
   - Mobile-first design (sesuai konsep Toko Ambu)

8. **Wishlist (Opsional)**
   - Save products for later
   - Quick reorder dari wishlist

9. **Reviews & Ratings (Future)**
   - Customer reviews
   - Product ratings
   - Review moderation

---

## 2) KONSEP STOREFRONT TOKO AMBU

### 2.1 Tujuan Storefront

1. **Customer self-service**: Customer bisa browse, order, dan track sendiri tanpa WA/manual
2. **Reduce admin workload**: Operator tidak perlu input order manual untuk setiap customer
3. **Transparent pricing**: Customer lihat harga, stok, dan ongkir langsung
4. **Professional branding**: Toko terlihat lebih modern & kredibel
5. **24/7 availability**: Order bisa masuk kapan saja, tidak terbatas jam kerja

### 2.2 User Journey (Customer)

```
Landing Page
    ↓
Browse Products (by category / search)
    ↓
View Product Detail (foto, desc, harga, stok)
    ↓
Add to Cart
    ↓
View Cart & Update Qty
    ↓
Checkout:
    - Login / Register (atau guest checkout?)
    - Input Shipping Address (autocomplete province/city/district)
    - Select Shipping Method (kurir + service)
    - Calculate Shipping Cost
    - Payment Method Selection
    - Review Order
    ↓
Submit Order (status: waiting_payment)
    ↓
Upload Payment Proof
    ↓
Admin Verify Payment (backend)
    ↓
Admin Pack & Ship (backend)
    ↓
Customer Track Order Status
    ↓
Order Done → Customer bisa review (future)
```

---

## 3) DATABASE SCHEMA (NEW TABLES)

### 3.1 Tabel `carts`

**Purpose:** Persistent shopping cart

```sql
CREATE TABLE carts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,  -- NULL jika guest cart (session-based)
    session_id VARCHAR(255) NULL,  -- untuk guest
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    INDEX(user_id),
    INDEX(session_id)
);
```

### 3.2 Tabel `cart_items`

```sql
CREATE TABLE cart_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    cart_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,  -- snapshot harga saat add to cart
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_product (cart_id, product_id)
);
```

### 3.3 Extend Tabel `users` untuk Customer

**Opsi A: Extend `users` table dengan `user_type`**
```sql
ALTER TABLE users ADD COLUMN user_type ENUM('admin', 'customer') DEFAULT 'admin';
ALTER TABLE users ADD COLUMN customer_id BIGINT UNSIGNED NULL;
```

**Opsi B: Separate `customer_users` table (RECOMMENDED)**
```sql
CREATE TABLE customer_users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
```

**Kenapa Opsi B lebih baik:**
- Separation of concerns: Admin users vs Customer users
- Tidak confuse permission system (Spatie roles untuk admin saja)
- Lebih mudah manage (misal: admin bisa impersonate customer)

### 3.4 Tabel `wishlists` (Future / Optional)

```sql
CREATE TABLE wishlists (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,  -- customer_users.id
    product_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES customer_users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);
```

### 3.5 Extend Tabel `orders`

Tambahkan kolom untuk storefront orders:

```sql
ALTER TABLE orders ADD COLUMN source ENUM('admin', 'storefront') DEFAULT 'admin';
ALTER TABLE orders ADD COLUMN customer_user_id BIGINT UNSIGNED NULL;  -- jika dari storefront
ALTER TABLE orders ADD COLUMN ip_address VARCHAR(45) NULL;  -- fraud detection
```

---

## 4) ROUTING STRATEGY

### 4.1 Route Separation

**Admin Routes (existing):**
```
/dashboard
/products
/orders
/customers
/purchases
/warehouse
etc...
```

**Storefront Routes (NEW):**
```
/                         → Landing page (atau redirect ke /shop)
/shop                     → Product listing
/shop/categories/{slug}   → Filter by category
/shop/products/{slug}     → Product detail
/cart                     → Shopping cart
/checkout                 → Checkout flow
/account/login            → Customer login
/account/register         → Customer register
/account/dashboard        → Customer dashboard
/account/orders           → Order history
/account/orders/{id}      → Order detail & tracking
/account/profile          → Edit profile
/account/address          → Manage addresses
```

### 4.2 Middleware Strategy

```php
// routes/web.php (existing admin routes)
Route::middleware(['auth', 'verified'])->group(function () {
    // admin routes here
});

// routes/storefront.php (NEW)
Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/', [ShopController::class, 'index'])->name('index');
    Route::get('/products/{slug}', [ShopController::class, 'show'])->name('products.show');
    // ... public routes
});

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    // ...
});

Route::prefix('account')->name('account.')->middleware(['auth:customer'])->group(function () {
    Route::get('/dashboard', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    // ... customer protected routes
});
```

**Guard Strategy:**
- Admin: `auth` guard (default) → users table
- Customer: `auth:customer` guard → customer_users table

---

## 5) AUTHENTICATION SYSTEM

### 5.1 Multi-Guard Setup

**config/auth.php:**
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'customer' => [
        'driver' => 'session',
        'provider' => 'customers',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
    'customers' => [
        'driver' => 'eloquent',
        'model' => App\Models\CustomerUser::class,
    ],
],
```

### 5.2 Customer Registration Flow

1. Customer mengisi form: email, password, name, phone, whatsapp
2. Sistem create:
   - `customers` record (master data customer)
   - `customer_users` record (auth credentials)
3. Email verification (opsional, bisa pakai Laravel built-in)
4. Auto-login setelah register

### 5.3 Guest Checkout vs Registered User

**Opsi A: Require Login (RECOMMENDED)**
- Customer **harus** register/login sebelum checkout
- Benefit:
  - Order history tersimpan
  - Alamat shipping tersimpan (reusable)
  - Easier support & tracking
  - Reduce fraud

**Opsi B: Guest Checkout Allowed**
- Customer bisa checkout tanpa register
- Create `customers` record dengan email/phone
- Kirim order confirmation via email/WA
- Benefit: Lower barrier to purchase
- Downside: No order history, more manual work

**Rekomendasi:** **Opsi A** (require login) untuk fase 1.

---

## 6) SHOPPING CART IMPLEMENTATION

### 6.1 Cart Storage Strategy

**Opsi A: Session-based Cart (Simple, fast)**
- Cart disimpan di session
- Hilang saat logout / clear browser
- Cocok untuk: Guest checkout

**Opsi B: Database-based Cart (RECOMMENDED)**
- Cart disimpan di DB (tables: carts, cart_items)
- Persistent across devices
- Cocok untuk: Registered users
- Benefit:
  - Abandoned cart recovery (marketing)
  - Multi-device sync
  - Better analytics

**Opsi C: Hybrid (Best of Both)**
- Guest: session cart
- Registered: DB cart
- Saat login → merge session cart ke DB cart

**Rekomendasi:** **Opsi C** untuk flexibility.

### 6.2 Cart Service Class

```php
namespace App\Services;

class CartService
{
    public function add($productId, $quantity = 1);
    public function update($cartItemId, $quantity);
    public function remove($cartItemId);
    public function clear();
    public function getCart();  // return Cart model dengan items
    public function getTotalItems();
    public function getSubtotal();
    public function mergeGuestCart($sessionCart);  // saat login
}
```

### 6.3 Cart Validation

Saat add to cart:
- ✅ Product exists & is_active
- ✅ Stock available (check inventory_balances)
- ✅ Quantity > 0
- ✅ Max qty per order (opsional)

Saat checkout:
- ✅ Re-validate stock (bisa berkurang saat di cart)
- ✅ Re-validate price (jika harga berubah, notify customer)

---

## 7) PRODUCT CATALOG (PUBLIC VIEW)

### 7.1 Product Listing Features

**Filter:**
- By category
- By price range (slider)
- By stock availability (in stock / preorder)
- Search by name/SKU

**Sorting:**
- Newest first
- Price: low to high
- Price: high to low
- Best seller (future: berdasarkan order_items count)

**Pagination:**
- 12 / 24 / 48 items per page
- Lazy load / infinite scroll (opsional)

**Layout:**
- Grid view (default, 2-4 columns responsive)
- List view (opsional)

### 7.2 Product Detail Page

**Info yang ditampilkan:**
- ✅ Foto produk (square 1:1, saat ini 1 foto, future: gallery)
- ✅ Nama produk
- ✅ SKU
- ✅ Kategori
- ✅ Harga jual (Rp XXX)
- ✅ Stok availability:
  - "In Stock" (qty > 0)
  - "Low Stock" (qty < 5, configurable)
  - "Out of Stock" (qty = 0)
  - "Preorder Available" (jika support preorder)
- ✅ Deskripsi lengkap
- ✅ Quantity selector (input + - buttons)
- ✅ Add to Cart button
- ✅ Breadcrumb (Home → Category → Product)
- ❌ Related products (future)
- ❌ Reviews & ratings (future)

**Stock Display Logic:**
```php
// Jangan tampilkan stok exact number (kompetitor bisa monitoring)
// Cukup status:
if ($product->qty_on_hand > 10) {
    return 'In Stock';
} elseif ($product->qty_on_hand > 0) {
    return 'Low Stock - Only ' . $product->qty_on_hand . ' left';
} else {
    return 'Out of Stock';
}
```

### 7.3 SEO Considerations

- ✅ Friendly URL slugs: `/shop/products/bendera-marshall-islands` (bukan `/products/123`)
- ✅ Add `slug` column ke `products` table
- ✅ Meta tags: title, description, og:image (foto produk)
- ✅ Schema.org Product markup (JSON-LD)
- ✅ Sitemap.xml untuk product pages

---

## 8) CHECKOUT FLOW

### 8.1 Checkout Steps

**Step 1: Cart Review**
- Display cart items (foto, name, price, qty)
- Update qty / remove item
- Show subtotal
- CTA: "Proceed to Checkout"

**Step 2: Shipping Address**
- Jika customer punya saved address → pilih dari list
- Atau input new address:
  - Name (receiver name, bisa beda dari account)
  - Phone
  - Province → City → District (autocomplete existing)
  - Postal code (auto-fill)
  - Full address (textarea)
- Checkbox: "Save this address for future orders"

**Step 3: Shipping Method**
- Pilih courier (JNE, TIKI, POS, SiCepat, dll)
- Pilih service (REG, YES, OKE, dll)
- **Display shipping cost**:
  - Opsi A: Manual input by customer (not recommended)
  - Opsi B: RajaOngkir API (RECOMMENDED)
  - Opsi C: Flat rate / free shipping (promo)

**Step 4: Payment Method**
- Transfer Bank (default)
  - Display bank account info (dari settings)
  - Instruksi: "Upload bukti transfer setelah order dibuat"
- COD (future, jika support)
- E-wallet / QRIS (future, via payment gateway)

**Step 5: Order Summary**
- Review all details:
  - Items + qty + price
  - Subtotal produk
  - Shipping cost
  - **Total amount**
- Terms & conditions checkbox
- CTA: "Place Order"

### 8.2 Order Creation Logic

Saat customer klik "Place Order":
1. Validate cart items (stok masih ada?)
2. Create `customers` record (jika belum ada / update existing)
3. Create `orders` record:
   - order_number (auto-generated)
   - customer_id
   - customer_user_id (from auth)
   - source: 'storefront'
   - type: 'order' (atau 'preorder' jika applicable)
   - status: 'waiting_payment'
   - total_amount: subtotal + shipping_cost
   - paid_amount: 0
   - shipping address fields (province, city, district, postal_code, address)
4. Create `order_items` records (foreach cart items)
5. Create `shipments` record:
   - courier
   - shipping_cost
   - status: 'pending'
6. Clear cart
7. Redirect ke order confirmation page
8. Send email: "Order Received - Waiting Payment"

### 8.3 Inventory Reserve (IMPORTANT)

**Saat order dibuat:**
- **TIDAK** langsung kurangi stok (`qty_on_hand`)
- **Tapi** reserve stok (`qty_reserved` di inventory_balances)
- Stock movement: `reserve` (reference: order_id)

**Saat payment verified:**
- Tetap reserved

**Saat order packed/shipped:**
- Release reserve (unreserve movement)
- Kurangi qty_on_hand (ship movement) ← **existing logic**

**Saat order cancelled (timeout payment / customer cancel):**
- Release reserve (unreserve movement)

**Timeout Logic:**
- Jika order status = 'waiting_payment' selama > 24 jam (configurable):
  - Auto-cancel order
  - Release reserved stock
  - Send email: "Order cancelled - payment not received"

---

## 9) PAYMENT PROOF UPLOAD (Customer Side)

### 9.1 Flow

1. Customer buat order → dapat order number
2. Customer transfer ke rekening toko
3. Customer login → My Orders → pilih order
4. Klik "Upload Payment Proof"
5. Upload foto bukti transfer (JPG/PNG, max 2MB)
6. Submit → status payment jadi `pending` (menunggu verifikasi)
7. Admin (Finance) verify → status jadi `verified`
8. Order status update: `waiting_payment` → `paid`
9. Email ke customer: "Payment Verified - Order Processing"

### 9.2 Media Handling

Reuse existing `media` table:
- type: 'payment_proof'
- link to: order_id (atau payment_id jika create payment record)

**Upload endpoint:**
```php
POST /account/orders/{order}/upload-payment
```

---

## 10) CUSTOMER DASHBOARD & ORDER TRACKING

### 10.1 Dashboard Sections

**My Account Menu:**
- Dashboard (overview)
- My Orders
- Profile
- Addresses
- Logout

**Dashboard Overview:**
- Recent orders (5 latest)
- Order summary stats:
  - Total orders
  - Pending payment
  - Processing
  - Shipped

### 10.2 Order Listing

**Filters:**
- All orders
- Waiting payment
- Processing
- Shipped
- Completed
- Cancelled

**Per order card:**
- Order number
- Order date
- Total amount
- Status badge (colored)
- Quick actions:
  - View detail
  - Upload payment proof (jika waiting_payment)
  - Track shipment (jika shipped)
  - Download invoice (jika paid)

### 10.3 Order Detail Page

**Info:**
- Order number & date
- Status timeline (visual):
  - ✅ Order Placed
  - ⏳ Waiting Payment
  - ✅ Payment Verified
  - ⏳ Packing
  - ⏳ Shipped
  - ⏳ Delivered
- Items list (foto, nama, qty, harga, subtotal)
- Shipping info:
  - Address
  - Courier + service
  - Tracking number (jika ada)
  - Shipping cost
- Payment info:
  - Total amount
  - Paid amount
  - Remaining
  - Payment method
  - Bukti transfer (thumbnail, clickable)
- Actions:
  - Upload payment proof
  - Download invoice (PDF)
  - Cancel order (jika waiting_payment)

---

## 11) UI/UX DESIGN PRINCIPLES

### 11.1 MOBILE-ONLY APP-LIKE INTERFACE ⭐ **KEY DECISION**

**IMPORTANT:** Storefront Toko Ambu menggunakan **mobile-app centered design**, bukan responsive desktop.

**Max Width: 480px** (centered di desktop)
- Desktop: Tampilan tetap 480px max-width, centered dengan background
- Mobile: Full width (360px - 480px)
- Tablet: Tetap 480px centered (tidak expand)

**Benefit:**
- ✅ Konsisten UX di semua device
- ✅ Faster development (1 layout saja)
- ✅ App-like experience (modern, familiar)
- ✅ Performance optimal (optimized untuk mobile)
- ✅ Easier maintenance (no breakpoint complexity)

**Implementasi CSS:**
```css
.storefront-container {
    max-width: 480px;
    margin: 0 auto;
    background: white;
    min-height: 100vh;
}

/* Desktop background */
@media (min-width: 481px) {
    body {
        background: linear-gradient(135deg, #FFF5F0 0%, #F0F4FF 100%);
    }
}
```

### 11.2 Mengikuti Konsep Toko Ambu

**Dari blueprint existing:**
- **Mode terang (white UI)** ✅
- **Orange primary color** (#F17B0D) untuk CTA & harga
- **Biru** (#0D36AA) untuk info/link
- **Pink accent** (#D00086) untuk badges/highlights
- **Clean & minimal** ✅
- **Fokus kecepatan & keterbacaan** ✅

### 11.3 Storefront Layout Structure

**Global Container:**
```html
<div class="storefront-app" style="max-width: 480px; margin: 0 auto; background: white;">
    <!-- Top Header (fixed) -->
    <header class="sticky top-0 z-50 bg-white">
        <!-- Logo, Search, Cart Badge -->
    </header>

    <!-- Main Content (scrollable) -->
    <main class="pb-20">
        <!-- Page content here -->
    </main>

    <!-- Bottom Navigation (fixed) -->
    <nav class="fixed bottom-0 w-full max-w-[480px]">
        <!-- Home, Shop, Wishlist, Cart, Profile -->
    </nav>
</div>
```

### 11.4 Design Patterns (Referensi Wireframe)

**Homepage (Screen 1):**
- ✅ Minimal top bar (Logo + Notification bell + Profile avatar)
- ✅ Search bar prominent (dengan icon search di kanan)
- ✅ Promo banner card (rounded, shadow, bold typography)
  - "UP To 25% OFF"
  - "ENDS SOON"
  - CTA button dengan arrow →
- ✅ "Recommended Styles" section dengan "See All" link
- ✅ Product grid (2 kolom)
  - Square product image
  - Product name + price
  - Heart icon (wishlist)
  - Cart button (subtle)
- ✅ Bottom nav: Home, Shop, Wishlist, Chart, Profile (icons only)

**Product Listing (Screen 2 - kanan atas):**
- ✅ Simple layout (skeleton placeholder untuk loading)
- ✅ 2 kolom grid (konsisten)
- ✅ Card dengan rounded corners, shadow subtle
- ✅ Spacing generous (tidak cramped)

**Product Detail (Screen 3 - tengah):**
- ✅ Large product image (swipeable gallery jika multiple)
- ✅ Thumbnail selector (3 small images)
- ✅ Price (strikethrough old price, bold new price)
- ✅ Size selector (pills: 38.5, 39, 40, 41, 41.5)
- ✅ Rating dengan stars + review count
- ✅ Product title & description
- ✅ Quantity selector (- 01 +)
- ✅ Two CTA buttons:
  - "Add To Cart" (outline, secondary)
  - "Buy Now" (solid, primary yellow/orange)
- ✅ Bottom nav tetap visible

**Cart Page (Screen 4 - kanan bawah):**
- ✅ Header: "MY Cart" dengan back arrow + menu dots
- ✅ Cart items list:
  - Thumbnail image (square, kecil)
  - Product name + size
  - Price per item (1x $128 USD)
  - Quantity controls (+ -)
- ✅ Bottom sticky:
  - Subtotal display
  - Checkout button (full width, bold)

**Visual Style dari Wireframe:**
- ✅ Soft shadows (`shadow-sm`, `shadow-md`)
- ✅ Rounded corners (`rounded-lg`, `rounded-xl`)
- ✅ Generous padding (16px - 24px)
- ✅ Clean typography (sans-serif, varying weights)
- ✅ Icon-first approach (minimal text)
- ✅ Whitespace as design element

### 11.5 Color Adaptation untuk Storefront

**Primary Actions (CTA):**
- Background: `#F17B0D` (orange) atau `#FFD700` (yellow seperti wireframe)
- Text: `#000000` (high contrast)
- Hover: `#DD5700` (darker orange)

**Cards & Surfaces:**
- Background: `#FFFFFF`
- Border: `#E5E7EB` (light gray)
- Shadow: `0 2px 8px rgba(0,0,0,0.08)`

**Text Hierarchy:**
- Heading: `#1F2937` (gray-900)
- Body: `#6B7280` (gray-500)
- Price: `#F17B0D` (orange) atau `#000000` (bold)

**Bottom Nav:**
- Active: `#F17B0D` (orange icon + label)
- Inactive: `#9CA3AF` (gray-400)

### 11.6 Typography Scale (Mobile-Optimized)

```css
/* Storefront Typography */
.text-heading-1 { font-size: 24px; font-weight: 700; line-height: 1.2; }  /* Page titles */
.text-heading-2 { font-size: 20px; font-weight: 600; line-height: 1.3; }  /* Section titles */
.text-heading-3 { font-size: 18px; font-weight: 600; line-height: 1.4; }  /* Card titles */
.text-body { font-size: 14px; font-weight: 400; line-height: 1.5; }       /* Body text */
.text-body-bold { font-size: 14px; font-weight: 600; line-height: 1.5; } /* Emphasized */
.text-caption { font-size: 12px; font-weight: 400; line-height: 1.4; }   /* Meta info */
.text-price { font-size: 18px; font-weight: 700; line-height: 1.3; }     /* Prices */
```

### 11.7 Component Specifications

**Product Card (Grid Item):**
```html
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <!-- 1:1 Square Image -->
    <div class="relative w-full pb-[100%] bg-gray-100">
        <img src="..." class="absolute inset-0 w-full h-full object-cover">
        <button class="absolute top-2 right-2 p-2 bg-white rounded-full shadow">
            <Heart icon>
        </button>
    </div>

    <!-- Product Info -->
    <div class="p-3">
        <h3 class="text-sm font-medium text-gray-900 truncate">Product Name</h3>
        <p class="text-lg font-bold text-primary mt-1">$280</p>
        <button class="mt-2 w-full py-2 bg-gray-100 rounded-lg text-sm">
            Add to Cart
        </button>
    </div>
</div>
```

**Bottom Navigation:**
```html
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-4 py-2 mx-auto" style="max-width: 480px;">
    <div class="flex justify-around items-center">
        <a href="/shop" class="flex flex-col items-center gap-1 text-gray-400 active:text-primary">
            <HomeIcon class="w-6 h-6" />
            <span class="text-xs">Home</span>
        </a>
        <a href="/shop/products" class="flex flex-col items-center gap-1">
            <ShopIcon class="w-6 h-6" />
            <span class="text-xs">Shop</span>
        </a>
        <a href="/wishlist" class="flex flex-col items-center gap-1">
            <HeartIcon class="w-6 h-6" />
            <span class="text-xs">Wishlist</span>
        </a>
        <a href="/cart" class="flex flex-col items-center gap-1 relative">
            <CartIcon class="w-6 h-6" />
            <span class="text-xs">Cart</span>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
        </a>
        <a href="/account" class="flex flex-col items-center gap-1">
            <UserIcon class="w-6 h-6" />
            <span class="text-xs">Profile</span>
        </a>
    </div>
</nav>
```

**Search Bar (Top):**
```html
<div class="sticky top-0 z-50 bg-white px-4 py-3 border-b border-gray-200">
    <div class="relative">
        <input
            type="text"
            placeholder="Search products..."
            class="w-full pl-4 pr-12 py-3 bg-gray-100 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-primary"
        >
        <button class="absolute right-3 top-1/2 -translate-y-1/2 p-2 bg-primary rounded-full">
            <SearchIcon class="w-5 h-5 text-white" />
        </button>
    </div>
</div>
```

### 11.8 Touch Interactions

**Minimum Touch Targets:**
- Buttons: 44px × 44px (iOS guidelines)
- Icons: 24px × 24px (dalam container 44px)
- Product cards: Full card clickable
- Swipe gestures: Product carousel, image gallery

**Feedback:**
- Active state: Scale down slightly (transform: scale(0.98))
- Ripple effect (optional, dengan Alpine.js)
- Loading states: Skeleton screens (seperti wireframe screen 2)

**Gestures:**
- Swipe left/right: Gallery navigation
- Pull to refresh: Product listing (optional)
- Tap & hold: Quick preview (future)

### 11.9 Performance Optimization (Mobile-Specific)

- ✅ **Lazy load images** (Intersection Observer)
- ✅ **WebP format** dengan fallback JPEG
- ✅ **Responsive images** (max 600px width untuk mobile)
- ✅ **Prefetch** next page saat scroll bottom
- ✅ **Service Worker** untuk offline basic functionality (future)
- ✅ **Bundle size < 100KB** (initial JS)
- ✅ **First Contentful Paint < 1.5s**
- ✅ **Time to Interactive < 3s**

### 11.10 Accessibility (A11y)

- ✅ **Touch targets** minimum 44px
- ✅ **Color contrast** minimum 4.5:1 untuk text
- ✅ **Focus indicators** untuk keyboard navigation
- ✅ **ARIA labels** untuk icon-only buttons
- ✅ **Alt text** untuk semua product images
- ✅ **Semantic HTML** (nav, main, article, section)

---

## 12) INTEGRATION WITH EXISTING SYSTEMS

### 12.1 Reuse Existing Components

**Yang bisa langsung dipakai:**
- ✅ LocationService (autocomplete province/city/district)
- ✅ Invoice generation (InvoiceController)
- ✅ Order status flow (existing enum)
- ✅ Payment verification logic (PaymentController → adapt untuk customer upload)
- ✅ Media upload (MediaController)
- ✅ InventoryService (untuk stock reservation)

**Yang perlu adapter/wrapper:**
- OrderController → perlu frontend-facing version (simplified, no admin features)
- ProductController → public version (no cost_price, no supplier info)

### 12.2 API Endpoints (Opsional)

Jika ingin frontend terpisah (SPA / mobile app future):
- Buat API routes (`routes/api.php`)
- Use Laravel Sanctum untuk auth
- RESTful endpoints:
  - `GET /api/products` (listing)
  - `GET /api/products/{id}` (detail)
  - `POST /api/cart/add`
  - `POST /api/checkout`
  - dll

**Untuk MVP:** Tidak perlu API, cukup **Blade templates** dengan Livewire/Alpine.js untuk interactivity.

---

## 13) TEKNOLOGI STACK (RECOMMENDATIONS)

### 13.1 Backend (Existing)

- ✅ Laravel 12
- ✅ MySQL/PostgreSQL
- ✅ Queue (untuk email notifications)
- ✅ Cache (Redis recommended)

### 13.2 Frontend

**Opsi A: Blade + Alpine.js + HTMX (RECOMMENDED untuk MVP)**
- Blade templates (server-side rendering)
- Alpine.js untuk interactivity (cart, qty selector, modals)
- HTMX untuk dynamic updates (add to cart tanpa reload)
- Tailwind CSS (sudah dipakai)
- Benefit:
  - Fast development
  - SEO-friendly
  - Leverage existing Blade components
  - No build complexity

**Opsi B: Livewire (Laravel-native)**
- Full-stack framework
- Real-time updates
- Component-based
- Benefit: No JavaScript needed untuk most features
- Downside: Heavier (websocket connections)

**Opsi C: Inertia.js + Vue/React (Advanced)**
- SPA experience dengan SSR
- Modern frontend framework
- Benefit: Rich UX, reactive
- Downside: More complex, slower initial development

**Rekomendasi:** **Opsi A** (Blade + Alpine.js) untuk MVP, bisa evolve ke Opsi C later.

### 13.3 Additional Libraries

- **Swiper.js** - Product carousel
- **Choices.js** - Select dropdowns enhancement
- **Photoswipe** - Image gallery/lightbox
- **AOS (Animate On Scroll)** - Scroll animations
- **Lazysizes** - Lazy loading images

---

## 14) SHIPPING COST CALCULATION

### 14.1 RajaOngkir Integration

**Existing:**
- RajaOngkir API key sudah di settings
- Location data (province/city) sudah ada

**Implementation:**
```php
// ShippingService.php
public function getCost($origin, $destination, $weight, $courier)
{
    // Call RajaOngkir API /cost
    // Return: array of services dengan cost & etd
}
```

**Checkout flow:**
1. Customer pilih shipping address (get city_id)
2. System get origin city (dari settings: shop city)
3. Calculate total weight (sum semua products di cart)
4. Call RajaOngkir API dengan:
   - origin: shop city_id
   - destination: customer city_id
   - weight: total grams
   - courier: all / specific (JNE, TIKI, POS)
5. Display shipping options:
   - JNE REG - Rp 15,000 (2-3 hari)
   - JNE YES - Rp 25,000 (1-2 hari)
   - TIKI ECO - Rp 12,000 (3-4 hari)
6. Customer pilih → add shipping_cost ke order

**Product Weight:**
- Tambah kolom `weight` (grams) ke tabel `products`
- Default: 500 gram (jika kosong)

### 14.2 Fallback (Manual)

Jika RajaOngkir API down/limit:
- Tampilkan flat rate shipping: "Rp 10,000 - Contact us for exact cost"
- Admin adjust manually dari backend

---

## 15) EMAIL NOTIFICATIONS

### 15.1 Customer Email Events

**Queue-based emails (Laravel Mail + Queue):**

1. **Order Confirmation**
   - Trigger: Order created
   - Subject: "Order #{order_number} - Payment Instructions"
   - Content:
     - Order summary
     - Bank account details
     - Payment deadline (24 jam)
     - Upload payment proof link

2. **Payment Verification**
   - Trigger: Payment verified by admin
   - Subject: "Payment Confirmed - Order #{order_number}"
   - Content:
     - Payment verified
     - Order is being processed
     - Estimated shipping date

3. **Order Shipped**
   - Trigger: Shipment status → shipped
   - Subject: "Order Shipped - #{order_number}"
   - Content:
     - Tracking number
     - Courier name
     - Estimated delivery
     - Track order link

4. **Order Delivered**
   - Trigger: Shipment status → delivered
   - Subject: "Order Delivered - #{order_number}"
   - Content:
     - Thank you message
     - Review request (future)

5. **Order Cancelled**
   - Trigger: Auto-cancel (timeout) or manual cancel
   - Subject: "Order Cancelled - #{order_number}"
   - Content:
     - Reason
     - Contact support

### 15.2 WhatsApp Notifications (Future)

Integrate dengan WA Business API:
- Send same notifications via WA
- Higher open rate than email
- More familiar untuk UMKM customers

---

## 16) SECURITY CONSIDERATIONS

### 16.1 Storefront-Specific Security

1. **Rate Limiting**
   - Limit add to cart: 10 requests/minute per IP
   - Limit checkout: 3 orders/hour per user
   - Protect from bot orders

2. **CSRF Protection**
   - Laravel built-in CSRF (enabled by default)
   - All forms must have @csrf

3. **XSS Prevention**
   - Blade {{ }} auto-escapes
   - Sanitize product description (jika allow HTML)

4. **SQL Injection**
   - Use Eloquent ORM (already safe)
   - Validate all inputs

5. **File Upload Security**
   - Payment proof upload:
     - Max 2MB
     - Only JPG/PNG
     - Store outside public (storage/app/payment_proofs)
     - Serve via controller (authorization check)

6. **Stock Manipulation**
   - Server-side validation (don't trust client qty input)
   - Re-validate stock saat checkout
   - Use DB transactions untuk order creation

7. **Price Tampering**
   - NEVER trust price dari frontend
   - Always fetch price dari DB saat add to cart
   - Re-validate saat checkout

8. **Session Hijacking**
   - Use secure cookies
   - Regenerate session ID after login
   - HTTPS only (production)

---

## 17) TESTING STRATEGY

### 17.1 Feature Tests (PHPUnit)

**Critical flows to test:**
- ✅ Customer registration & login
- ✅ Add to cart (guest & logged in)
- ✅ Update cart qty
- ✅ Remove from cart
- ✅ Checkout flow (order creation)
- ✅ Stock reservation logic
- ✅ Payment proof upload
- ✅ Order status transitions
- ✅ Email sending (queue)

### 17.2 Manual Testing Checklist

- [ ] Browse products (filter, search, pagination)
- [ ] Add to cart → qty update → remove
- [ ] Cart persistence (logout/login)
- [ ] Guest cart merge saat login
- [ ] Checkout: shipping address autocomplete
- [ ] Checkout: shipping cost calculation
- [ ] Order creation success
- [ ] Upload payment proof
- [ ] Admin verify payment → customer notified
- [ ] Order tracking page
- [ ] Download invoice (customer side)
- [ ] Responsive design (mobile, tablet, desktop)
- [ ] Email notifications received

---

## 18) DEPLOYMENT & GO-LIVE

### 18.1 Pre-Launch Checklist

**Technical:**
- [ ] Database migrations run (production)
- [ ] Seed demo products dengan foto
- [ ] Configure email (SMTP / Mailgun)
- [ ] Configure queue worker (supervisor)
- [ ] Configure cache (Redis)
- [ ] SSL certificate (HTTPS)
- [ ] Backup strategy (daily DB backup)
- [ ] Error tracking (Sentry / Laravel Telescope)

**Content:**
- [ ] Homepage design & copy
- [ ] Product photos (minimal 10 produk)
- [ ] Product descriptions
- [ ] Category setup
- [ ] Bank account info di settings
- [ ] Shipping policy page
- [ ] Return policy page
- [ ] Privacy policy
- [ ] Terms & conditions

**Testing:**
- [ ] End-to-end test order (full flow)
- [ ] Payment gateway test (if using)
- [ ] Email delivery test
- [ ] Mobile responsiveness check
- [ ] Load testing (basic)

### 18.2 Soft Launch Strategy

1. **Phase 1: Internal Testing (1 minggu)**
   - Admin & staff test order
   - Fix critical bugs
   - Refine UX

2. **Phase 2: Beta Launch (2 minggu)**
   - Invite existing customers (via WA/email)
   - Limited promotions: "First 50 orders get free shipping"
   - Gather feedback

3. **Phase 3: Public Launch**
   - Announce di social media
   - Grand opening promo
   - Monitor closely (support 24/7 first week)

---

## 19) POST-LAUNCH: ANALYTICS & OPTIMIZATION

### 19.1 Metrics to Track

**Business Metrics:**
- Conversion rate (visitor → order)
- Average order value
- Cart abandonment rate
- Customer lifetime value
- Repeat purchase rate

**Technical Metrics:**
- Page load time
- API response time
- Error rate
- Uptime

**Customer Behavior:**
- Popular products
- Popular categories
- Peak order times
- Device usage (mobile vs desktop)

**Tools:**
- Google Analytics 4
- Laravel Telescope (dev/staging)
- Custom dashboard (admin panel)

### 19.2 A/B Testing Ideas

- Product card layout (grid vs list)
- CTA button copy ("Add to Cart" vs "Buy Now")
- Checkout steps (single page vs multi-step)
- Free shipping threshold (Rp 100K vs Rp 200K)

---

## 20) FUTURE ENHANCEMENTS (ROADMAP)

### Phase 1 (MVP) - 4-6 minggu
- ✅ Customer registration & login
- ✅ Product catalog (listing & detail)
- ✅ Shopping cart (DB-based)
- ✅ Checkout flow
- ✅ Payment proof upload
- ✅ Customer order tracking
- ✅ Email notifications
- ✅ Shipping cost (RajaOngkir)
- ✅ Responsive design

### Phase 2 - 2-4 minggu
- Wishlist
- Product reviews & ratings
- Search autocomplete
- Related products
- Recently viewed products
- Multiple product photos (gallery)
- Stock low notification (customer-facing)

### Phase 3 - 4-6 minggu
- Payment gateway integration (Midtrans/Xendit)
  - Credit card
  - E-wallet (GoPay, OVO, Dana)
  - QRIS
- COD (Cash on Delivery)
- Store credit / wallet system

### Phase 4 - Ongoing
- Mobile app (Flutter / React Native)
- Push notifications
- Loyalty program / points
- Referral program
- Flash sale / promo system
- Abandoned cart recovery (email automation)
- Live chat support
- WhatsApp Business API integration

---

## 21) COST ESTIMATION (DEVELOPMENT)

### 21.1 Development Hours (Conservative)

**Backend:**
- Customer authentication (multi-guard) - 8 jam
- Shopping cart system - 12 jam
- Checkout flow - 16 jam
- Order management (customer side) - 12 jam
- Payment proof upload - 6 jam
- Email notifications - 8 jam
- RajaOngkir integration - 8 jam
- Testing & bug fixes - 16 jam
**Subtotal Backend: 86 jam**

**Frontend:**
- Landing page - 8 jam
- Product listing (filter, search, pagination) - 12 jam
- Product detail page - 8 jam
- Cart page - 8 jam
- Checkout pages - 16 jam
- Customer dashboard & order tracking - 12 jam
- Login/register pages - 6 jam
- Responsive optimization - 12 jam
**Subtotal Frontend: 82 jam**

**Integration & Deployment:**
- Integration testing - 8 jam
- Performance optimization - 6 jam
- Security hardening - 4 jam
- Deployment setup - 4 jam
- Documentation - 4 jam
**Subtotal Integration: 26 jam**

**TOTAL: ~194 jam** (approx. 24 hari kerja @ 8 jam/hari)

**Dengan buffer 20%: ~233 jam (~29 hari kerja)**

### 21.2 Monthly Operational Costs

- Hosting (VPS / Cloud) - Rp 200K - 500K
- Domain - Rp 150K/tahun
- SSL certificate - Free (Let's Encrypt)
- Email service (Mailgun/Sendinblue) - Rp 100K - 300K
- RajaOngkir API - Rp 0 (Starter free) - Rp 300K (Pro)
- Payment gateway fee - 2-3% per transaction (Midtrans/Xendit)
- Backup storage - Rp 50K

**Estimated: Rp 500K - 1.5jt/bulan** (tergantung scale)

---

## 22) RISKS & MITIGATION

### 22.1 Technical Risks

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Stock oversell (race condition) | High | Medium | Use DB transactions + locking, reserve stock logic |
| RajaOngkir API down | Medium | Low | Fallback to manual/flat rate, cache responses |
| Payment gateway downtime | High | Low | Multiple payment methods, manual transfer always available |
| Scalability (high traffic) | Medium | Medium | Use caching, CDN, DB optimization, horizontal scaling |
| Security breach (customer data) | High | Low | HTTPS, secure coding, regular updates, penetration testing |

### 22.2 Business Risks

| Risk | Impact | Mitigation |
|------|--------|------------|
| Low adoption (customers prefer WA) | High | Incentive: "Online order discount 5%", educate customers |
| Abandoned carts | Medium | Email reminder, free shipping threshold, guest checkout |
| Fraudulent orders | Medium | Verify payment proof manually, limit COD, blacklist repeat offenders |
| Customer support overload | Medium | FAQ page, clear product info, automated emails reduce questions |

---

## 23) SUCCESS CRITERIA

### 23.1 MVP Launch Success Metrics (3 bulan pertama)

- ✅ 100+ customer registrations
- ✅ 50+ completed orders via storefront
- ✅ 30% repeat purchase rate
- ✅ < 5% payment verification rejection rate
- ✅ < 60% cart abandonment rate
- ✅ 95%+ uptime
- ✅ < 3 detik average page load time
- ✅ No critical security incidents

### 23.2 Long-term Goals (1 tahun)

- 50% of all orders melalui storefront (vs admin manual)
- Average 100 orders/bulan via storefront
- 40% repeat customer rate
- Positive ROI (revenue > operational costs)

---

## 24) DECISION MATRIX: PRIORITAS FITUR

### Must Have (MVP)
- ✅ Customer login/register
- ✅ Product catalog
- ✅ Shopping cart
- ✅ Checkout & order creation
- ✅ Payment proof upload
- ✅ Order tracking
- ✅ Email notifications

### Should Have (Phase 2)
- Wishlist
- Reviews & ratings
- Multiple payment methods
- Product image gallery

### Nice to Have (Future)
- Mobile app
- Live chat
- Loyalty program
- AR product preview

---

## 25) NEXT STEPS (ACTION PLAN)

### Week 1-2: Foundation
1. ✅ Setup multi-guard authentication (customer vs admin)
2. ✅ Create database migrations (carts, cart_items, customer_users)
3. ✅ Implement CustomerUser model & authentication
4. ✅ Create storefront routes structure

### Week 3-4: Product Catalog
1. ✅ Public product listing controller
2. ✅ Product detail page (public view)
3. ✅ Category filtering
4. ✅ Search functionality
5. ✅ Add product slug untuk SEO

### Week 5-6: Shopping Cart
1. ✅ Cart service class
2. ✅ Add to cart functionality
3. ✅ Cart page (view, update, remove)
4. ✅ Cart icon dengan badge
5. ✅ Guest cart merge logic

### Week 7-8: Checkout
1. ✅ Checkout flow (multi-step)
2. ✅ Shipping address form (reuse location autocomplete)
3. ✅ RajaOngkir integration (shipping cost)
4. ✅ Order creation logic
5. ✅ Stock reservation

### Week 9-10: Customer Dashboard
1. ✅ Order history page
2. ✅ Order detail & tracking
3. ✅ Payment proof upload
4. ✅ Profile management
5. ✅ Address book

### Week 11-12: Polish & Launch
1. ✅ Email notifications (all events)
2. ✅ Frontend polish (responsive, animations)
3. ✅ Testing (manual + automated)
4. ✅ Bug fixes
5. ✅ Deployment
6. ✅ Soft launch

---

## 26) KESIMPULAN

Sistem Toko Ambu **sudah sangat solid** di backend (order flow, inventory, payments). Untuk storefront, yang dibutuhkan adalah:

1. **Customer authentication system** (multi-guard)
2. **Shopping cart** (database-persistent)
3. **Public product catalog** (dengan filter & search)
4. **Checkout flow** (reuse existing location autocomplete)
5. **Customer dashboard** (order tracking & payment upload)

**Kunci sukses:**
- Reuse existing components (LocationService, InventoryService, dll)
- Keep UX simple & mobile-friendly (sesuai konsep Toko Ambu)
- Focus on MVP first (jangan overkill fitur)
- Test thoroughly before launch
- Monitor & iterate based on customer feedback

**Timeline realistis: 6-8 minggu** untuk MVP yang production-ready.

**ROI potensial:** Reduce admin workload 50%, increase order volume 2-3x, better customer experience → higher retention.

---

**Next:** Implementasi dimulai dengan setup authentication & database schema untuk storefront.
