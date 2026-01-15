# Toko Ambu — Promo Engine Blueprint

Dokumen ini berisi:
1) ERD (struktur tabel + relasi) untuk sistem promo (Flash Sale, Bundling, Kupon, Cart Rule)
2) UX Flow (alur dashboard) dari buat promo → select produk → pricing → preview → publish

---

## A. Prinsip Desain (biar sistem produk tetap “solid”)
- **Produk tetap single source of truth**: stok, harga dasar, laporan tetap di `products`.
- Promo adalah **layer** di atas produk.
- Promo bisa **scheduled** (punya start/end), **draft**, dan **archived**.
- Prioritas promo dibikin jelas supaya **nggak double-discount** kecuali diizinkan.

---

## B. ERD (Entity Relationship) — Promo Engine

> Catatan: Nama tabel/kolom menyesuaikan gaya Laravel lo (snake_case, timestamps, soft deletes optional).

### 1) Tabel Inti

#### `promotions`
Menyimpan semua jenis promo dalam satu tabel (polymorphic via `type`).
- id (PK)
- type (enum) → `flash_sale | bundle | coupon | cart_rule`
- name
- description (nullable)
- status (enum) → `draft | scheduled | active | ended | archived`
- priority (int) → default: FlashSale 100, Bundle 90, CartRule 80, Coupon 70
- stackable (bool) → boleh digabung promo lain atau tidak
- start_at (datetime, nullable)
- end_at (datetime, nullable)
- rules (json) → rules tambahan (min_qty, max_qty, per_user_limit, etc)
- created_by (FK → users.id)
- created_at, updated_at
- deleted_at (optional)

Relasi:
- promotions 1—N promotion_targets
- promotions 1—N promotion_benefits
- promotions 1—N promotion_usages (tracking)

---

### 2) Targeting (produk mana yang kena promo)

#### `promotion_targets`
Target bisa produk spesifik, kategori, tag, brand/publisher.
- id (PK)
- promotion_id (FK → promotions.id)
- target_type (enum) → `product | category | tag | brand`
- target_id (bigint)
- include (bool) default true → untuk exclude logic (optional)
- created_at

Catatan:
- Kalau mau simpel V1: hanya `include=true`.
- Kalau V2: tambahin `include=false` untuk blacklist.

---

### 3) Benefit (diskon/benefit yang diberikan promo)

#### `promotion_benefits`
Satu promo bisa punya satu atau lebih benefit (biasanya 1).
- id (PK)
- promotion_id (FK)
- benefit_type (enum) → `percent_off | amount_off | fixed_price | free_shipping`
- value (decimal)
- max_discount (decimal, nullable)
- apply_scope (enum) → `item | cart | shipping`
- created_at

Contoh:
- Flash Sale: `benefit_type=fixed_price` atau `percent_off`
- Coupon: `percent_off` / `amount_off` / `free_shipping`

---

### 4) Coupon detail (khusus promo type=coupon)

#### `coupons`
- id (PK)
- promotion_id (FK → promotions.id) UNIQUE
- code (unique)
- per_user_limit (int, nullable)
- global_limit (int, nullable)
- min_order_amount (decimal, nullable)
- first_purchase_only (bool default false)
- created_at

---

### 5) Bundle detail (khusus promo type=bundle)

#### `bundles`
- id (PK)
- promotion_id (FK → promotions.id) UNIQUE
- pricing_mode (enum) → `fixed | percent_off | amount_off`
- bundle_price (decimal, nullable) → jika pricing_mode=fixed
- discount_value (decimal, nullable) → jika percent/amount
- must_be_cheaper (bool default true)
- compare_basis (enum) → `sum_items` (default)
- created_at

#### `bundle_items`
- id (PK)
- bundle_id (FK → bundles.id)
- product_id (FK → products.id)
- qty (int default 1)
- created_at

Validasi wajib:
- `bundle_effective_price < sum(product_price * qty)` jika `must_be_cheaper=true`.

---

### 6) Cart rule detail (khusus promo type=cart_rule)

#### `cart_rules`
- id (PK)
- promotion_id (FK → promotions.id) UNIQUE
- rule_logic (json)
- created_at

Contoh rule_logic (json):
- {"if": {"cart_qty_gte": 3, "category_id": 12}, "then": {"amount_off": 15000}}

---

### 7) Tracking pemakaian & audit

#### `promotion_usages`
Tracking agregat dan per transaksi.
- id (PK)
- promotion_id (FK)
- order_id (FK → orders.id)
- user_id (FK → users.id, nullable)
- coupon_code (nullable)
- discount_amount (decimal default 0)
- applied_at (datetime)

---

## C. Integrasi ke Order (ringkas tapi jelas)

### Perhitungan di Checkout (rekomendasi flow)
1) Build cart lines (product_id, qty, base_price)
2) Cari promo aktif & eligible berdasarkan `target`
3) Apply berdasarkan **priority**
4) Respect `stackable` (default: tidak stack)
5) Simpan hasil:
- order_subtotal
- order_discount_total
- order_total
- `promotion_usages` (log)

---

## D. UX Flow (Dashboard & Wizard)

### 1) Promo Center — List
- Tabs: Active | Scheduled | Draft | Ended
- Search promo name
- Filter by type
- CTA: **+ Create Promo**

Row item menampilkan:
- Name
- Type badge
- Status
- Active window (start–end)
- Affected products count (computed)
- Actions: View | Duplicate | End now | Archive

---

### 2) Create Promo — Step 0 (Pilih jenis)
Card pilihan:
- Flash Sale
- Bundle
- Coupon
- Cart Rule

Klik → masuk Wizard.

---

## E. Wizard — FLASH SALE

### Step 1 — Basic Info
- Name
- Start at / End at
- Status: Draft / Scheduled

### Step 2 — Select Products (tanpa buka satu-satu)
Pilihan mode:
- By Category (multi)
- By Tag (multi)
- By Brand/Publisher (multi)
- Manual multi-select products

Panel kanan: **Selected preview**
- total selected
- show sample 10 produk

### Step 3 — Pricing
Opsi:
- Percent off
- Amount off
- Fixed price

Live preview table (sample):
- Product | Base price | New price

### Step 4 — Constraints
- Max qty per order (optional)
- Max qty per user (optional)
- Min stock threshold (optional)

### Step 5 — Review & Publish
- Ringkasan
- Total produk terdampak
- Estimasi diskon rata-rata (optional)
- Publish

---

## F. Wizard — BUNDLE

### Step 1 — Basic Info
- Bundle name
- Active window

### Step 2 — Choose Items
- Add product (search)
- Set qty per item
- Show subtotal normal = Σ(price * qty)

### Step 3 — Bundle Pricing
Pricing mode:
- Fixed price
- Percent off (from sum items)
- Amount off (from sum items)

**Auto validation**:
- If must_be_cheaper=true:
  - Jika bundle_price >= sum_items → show error + block publish

UI preview:
- Normal total: 120.000
- Bundle price: 99.000
- Customer saves: 21.000

### Step 4 — Placement
- Where to show:
  - Bundle page
  - Product page (recommendation)
  - Cart upsell

### Step 5 — Review & Publish

Checkout behavior:
- User add bundle → cart line “Bundle X”
- Expand show items
- Stock reduce on each product line

---

## G. Wizard — COUPON

### Step 1 — Basic
- Name
- Code (auto-generate + edit)
- Active window

### Step 2 — Benefit
- Percent / Amount / Free shipping

### Step 3 — Eligibility
- Targets: all | category | tag | products | bundle-only
- Min order
- Per user limit
- Global limit
- First purchase only

### Step 4 — Review & Publish

---

## H. Wizard — CART RULE

### Step 1 — Basic
- Name
- Active window

### Step 2 — Condition Builder (simple V1)
Dropdown builder:
- If cart total >= X
- If cart qty >= X
- If category qty >= X
- If contains products A+B

### Step 3 — Benefit
- Amount off / Percent off / Free shipping

### Step 4 — Review & Publish

---

## I. UX Microcopy yang bikin admin “enak”
- Flash sale selection: “Pilih produk via kategori/tag biar nggak capek klik satu-satu.”
- Bundle pricing: “Bundle harus lebih hemat dari total beli satuan.”
- Review screen: “Ini tampilan final yang akan dilihat customer.”

---

## J. Output yang wajib ada di UI (biar gak chaos)
- Status badge (Draft/Scheduled/Active)
- Countdown untuk promo active
- Affected products count
- Duplicate promo button (buat hemat waktu)

---

## K. Next step implementasi (recommended)
1) Build schema: promotions + targets + benefits
2) Implement Flash Sale first (paling cepat ROI)
3) Add Bundle (virtual bundle)
4) Add Coupon
5) Add Cart Rule

Kalau lo mau, gue bisa lanjutkan ke:
- contoh struktur JSON `rules` per promo type
- pseudo-code apply promo di checkout (priority + stackable)
- wireframe screen-by-screen (lebih visual)



---



---

# CATATAN AKHIR
Dokumen ini **hanya mencakup sistem PROMO** (Flash Sale, Bundling, Kupon, Cart Rule) sesuai diskusi promo dari awal.

Integrasi ke modul lain (Order, Inventory, Payment, Ledger) **sengaja tidak dimasukkan** agar blueprint ini:
- fokus
- hemat token
- mudah di-maintain
- bisa dijadikan referensi tunggal khusus PROMO ENGINE

Jika nanti dibutuhkan, integrasi ke modul lain akan dibuat sebagai dokumen terpisah.

