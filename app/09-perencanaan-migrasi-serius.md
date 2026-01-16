# Perencanaan Migrasi Serius - Routing Architecture

> **Tanggal:** 2026-01-16
> **Status:** Planning Phase
> **Tujuan:** Memisahkan dengan jelas antara Frontend (Storefront) dan Backend (Admin) untuk konsistensi dan maintainability

---

## 🎯 EXECUTIVE SUMMARY

Aplikasi Toko Ambu saat ini memiliki **mixed routing architecture** yang mencampur antara frontend (customer-facing) dan backend (admin panel) dalam file routing yang sama. Dokumen ini menyediakan analisis lengkap dan roadmap untuk migrasi ke arsitektur yang lebih clean dan maintainable.

**Current Status:**
- ✅ Separation by guards (`web` untuk admin, `customer` untuk storefront)
- ✅ Separation by route files (`web.php`, `storefront.php`, `warehouse.php`)
- ⚠️ Domain checking dilakukan manual di controller/middleware
- ⚠️ Hardcoded domain strings tersebar di codebase
- ⚠️ Duplikasi routes untuk settings

**Target Architecture:**
- ✅ Router-level domain separation
- ✅ Centralized domain configuration
- ✅ Clear file organization
- ✅ No hardcoded domains
- ✅ Consistent naming conventions

---

## 📊 CURRENT ROUTING STRUCTURE

### Route File Loading Order
```
Bootstrap: bootstrap/app.php
├── web:      routes/web.php          (main admin routes)
├── api:      routes/api.php          (API routes)
├── commands: routes/console.php
└── then:
    ├── routes/warehouse.php    (warehouse operations)
    └── routes/storefront.php   (customer-facing)
```

### Authentication Guards
```php
'web'      => User model      (Admin/Staff)
'customer' => Customer model  (Storefront customers)
```

---

## 🏪 FRONTEND/STOREFRONT ROUTES

**Domain:** `tokoambu.com`
**File:** `routes/storefront.php`
**Guard:** `auth:customer`
**Purpose:** Customer-facing storefront, shopping, checkout

### Public Routes (No Authentication)

#### Shop & Browse
```
GET    /shop                          → Shop index (all products)
GET    /shop/search                   → Search products
GET    /shop/flash-sale               → Flash sale products
GET    /shop/bundles                  → Bundle sales
GET    /shop/{slug}                   → Product detail page
GET    /shop/bundles/{promotion}      → Bundle detail page
GET    /{category:slug}               → Category page
```

#### Cart Management
```
GET    /cart                          → View cart
POST   /cart/add                      → Add item to cart
POST   /cart/bundle                   → Add bundle to cart
PUT    /cart/update                   → Update cart quantity
DELETE /cart/{id}                     → Remove from cart
POST   /cart/coupon                   → Apply coupon code
DELETE /cart/coupon                   → Remove coupon
```

#### Customer Authentication
```
GET    /account/register              → Registration form
POST   /account/register              → Process registration
GET    /account/login                 → Login form
POST   /account/login                 → Process login
GET    /account/forgot-password       → Password reset request
POST   /account/forgot-password       → Send reset email
GET    /account/reset-password/{token} → Password reset form
POST   /account/reset-password        → Process password reset
```

#### Static Content
```
GET    /page/{slug}                   → Static pages (Terms, Privacy, etc)
```

### Protected Routes (auth:customer)

#### Checkout
```
GET    /checkout                      → Checkout form
POST   /checkout                      → Process checkout & create order
GET    /order/{order}/confirmation    → Order confirmation page
```

#### Customer Dashboard
```
GET    /customer/dashboard            → Customer dashboard
GET    /customer/orders               → Order history
GET    /customer/orders/{order}       → Order detail/tracking
GET    /customer/wishlist             → Wishlist
POST   /customer/wishlist/toggle      → Add/remove from wishlist
GET    /customer/notifications        → Notifications
POST   /customer/notifications/{id}/read → Mark as read
POST   /account/logout                → Logout
```

#### Profile Management
```
GET    /customer/profile              → Profile page
PUT    /customer/profile              → Update profile
PUT    /customer/profile/password     → Update password
```

#### Payment Processing
```
GET    /customer/payment/{order}/select → Select payment method
GET    /customer/payment/{order}/bank-transfer → Bank transfer details
POST   /customer/payment/{order}/bank-transfer/confirm → Confirm bank transfer
GET    /customer/payment/{order}/ipaymu → iPaymu payment page
POST   /customer/payment/{order}/ipaymu/create → Create iPaymu transaction
GET    /customer/payment/{order}/ipaymu/result → Payment result
```

---

## 🔧 BACKEND/ADMIN ROUTES

**Domain:** `admin.tokoambu.com`
**Files:** `routes/web.php`, `routes/warehouse.php`
**Guard:** `web`
**Purpose:** Admin panel, inventory, management

### Root Route Logic (CURRENT)
```php
GET / → Domain-based routing:
   if (request()->getHost() === 'admin.tokoambu.com')
      authenticated   → redirect('/dashboard')
      unauthenticated → redirect('/login')
   else
      → redirect('/shop')  // Storefront
```

### Public Routes (No Auth Required)

#### Invoice Sharing
```
GET    /public/invoices/{order}           → View invoice (signed URL)
GET    /public/invoices/{order}/download  → Download PDF (signed URL)
```

#### Payment Webhooks
```
GET    /ipaymu/proxy-qr                   → iPaymu QR code proxy
POST   /ipaymu/notify                     → iPaymu webhook (NO CSRF)
```

### Admin Dashboard
```
GET    /dashboard                         → Admin dashboard overview
```

### Product Management
**Permission Required:** `view_products`, `create_products`, `edit_products`, `delete_products`

#### Products
```
GET    /products                          → List all products
GET    /products/create                   → Create new product form
POST   /products                          → Store new product
GET    /products/{product}                → View product details
GET    /products/{product}/edit           → Edit product form
PUT    /products/{product}                → Update product
DELETE /products/{product}                → Delete product
```

#### Product Categories
```
GET    /product-categories                → List categories
POST   /product-categories                → Create category
GET    /product-categories/{id}/edit      → Edit category
PUT    /product-categories/{id}           → Update category
DELETE /product-categories/{id}           → Delete category
```

#### Product Variants
```
GET    /products/{product}/variants       → List variants
POST   /products/{product}/variants       → Create variant
POST   /products/{product}/variants/generate → Generate combinations
POST   /products/variants/bulk-pricing    → Apply bulk pricing
PUT    /products/{product}/variants       → Update variants
DELETE /products/{product}/variants       → Delete variant
```

#### Pages (CMS)
```
GET    /pages                             → List pages
POST   /pages                             → Create page
GET    /pages/{page}/edit                 → Edit page
PUT    /pages/{page}                      → Update page
DELETE /pages/{page}                      → Delete page
```

### Supplier Management
**Permission Required:** `view_suppliers`, `create_suppliers`, `edit_suppliers`, `delete_suppliers`

```
GET    /suppliers                         → List suppliers
GET    /suppliers/create                  → Create form
POST   /suppliers                         → Store supplier
GET    /suppliers/{supplier}              → View details
GET    /suppliers/{supplier}/edit         → Edit form
PUT    /suppliers/{supplier}              → Update supplier
DELETE /suppliers/{supplier}              → Delete supplier
POST   /suppliers/{supplier}/bank-accounts → Add bank account
DELETE /suppliers/{supplier}/bank-accounts/{account} → Delete account
```

### Vendors & Employees
**Permission Required:** `view_products` (inconsistent!)

#### Vendors
```
GET    /vendors                           → List vendors
POST   /vendors                           → Create vendor
GET    /vendors/{vendor}                  → View vendor
PUT    /vendors/{vendor}                  → Update vendor
DELETE /vendors/{vendor}                  → Delete vendor
```

#### Employees
```
GET    /employees                         → List employees
POST   /employees                         → Create employee
GET    /employees/{employee}              → View employee
PUT    /employees/{employee}              → Update employee
DELETE /employees/{employee}              → Delete employee
```

### Customer Management (Admin Side)
**Permission Required:** `view_customers`, `create_customers`, `edit_customers`, `delete_customers`

```
GET    /customers                         → List customers
GET    /customers/create                  → Create form
POST   /customers                         → Store customer
GET    /customers/{customer}              → View details
GET    /customers/{customer}/edit         → Edit form
PUT    /customers/{customer}              → Update customer
DELETE /customers/{customer}              → Delete customer
POST   /customers/{customer}/bank-accounts → Add bank account
DELETE /customers/{customer}/bank-accounts/{account} → Delete account
```

### Order Management
**Permission Required:** `view_orders`, `create_orders`, `edit_orders`, `delete_orders`, `update_order_status`

```
GET    /orders                            → List orders
GET    /orders/packing                    → Packing view
GET    /orders/bulk-print                 → Bulk print labels
GET    /orders/create                     → Create order form
POST   /orders                            → Store order
GET    /orders/{order}                    → View order details
GET    /orders/{order}/edit               → Edit order
PUT    /orders/{order}                    → Update order
GET    /orders/{order}/label              → View label
GET    /orders/{order}/print              → Print label
POST   /orders/{order}/cancel             → Cancel order
POST   /orders/{order}/cancel-and-refund  → Cancel & refund
POST   /orders/{order}/refund             → Refund order
POST   /orders/bulk-mark-packed           → Bulk mark as packed
POST   /orders/{order}/items/{item}/pick  → Pick item
DELETE /orders/{order}                    → Delete order
```

### Payment & Financial
**Permission Required:** `view_payments`, `create_payments`, `verify_payments`, `view_ledger`, `create_ledger_entry`

#### Payments
```
GET    /payments                          → List payments
GET    /payments/create                   → Create payment form
POST   /payments                          → Store payment
GET    /payments/{payment}                → View payment
PATCH  /payments/{payment}/verify         → Verify payment
```

#### Ledger
```
GET    /ledger                            → List ledger entries
GET    /ledger/report                     → Financial reports
GET    /ledger/create                     → Create entry form
POST   /ledger                            → Store entry
GET    /ledger/{entry}                    → View entry
```

#### Financial Categories
```
GET    /financial-categories              → List categories
GET    /financial-categories/create       → Create form
POST   /financial-categories              → Store category
GET    /financial-categories/{id}/edit    → Edit form
PUT    /financial-categories/{id}         → Update category
DELETE /financial-categories/{id}         → Delete category
```

### Preorders
**Permission Required:** `view_orders`, `edit_orders`

```
GET    /preorders                         → List preorder products
GET    /preorders/{product}               → View preorder details
GET    /preorders/{order}/whatsapp/{type} → Generate WhatsApp message
POST   /preorders/{product}/mark-ready    → Mark product ready
GET    /preorders/{product}/periods/create → Create period form
POST   /preorders/{product}/periods       → Store period
POST   /preorders/periods/{period}/close  → Close period
POST   /preorders/periods/{period}/archive → Archive period
POST   /preorders/periods/{period}/reopen → Reopen period
```

### Promotions
**Permission Required:** `view_products`, `edit_products`

```
GET    /promotions                        → List promotions
GET    /promotions/create                 → Create form
POST   /promotions                        → Store promotion
GET    /promotions/{promotion}            → View details
GET    /promotions/{promotion}/edit       → Edit form
PUT    /promotions/{promotion}            → Update promotion
POST   /promotions/{promotion}/duplicate  → Duplicate promotion
POST   /promotions/{promotion}/end        → End promotion now
POST   /promotions/{promotion}/archive    → Archive promotion
```

### Purchase Orders
**Permission Required:** `view_purchases`, `create_purchases`, `edit_purchases`, `delete_purchases`

```
GET    /purchases                         → List purchases
GET    /purchases/create                  → Create form
POST   /purchases                         → Store purchase
GET    /purchases/{purchase}              → View details
GET    /purchases/{purchase}/edit         → Edit form
PUT    /purchases/{purchase}              → Update purchase
POST   /purchases/{purchase}/pay          → Pay purchase
DELETE /purchases/{purchase}              → Delete purchase
```

### Shipment Management
**Permission Required:** `view_shipments`, `create_shipments`, `update_shipment_status`, `delete_shipments`

```
GET    /shipments                         → List shipments
GET    /shipments/create                  → Create form
POST   /shipments                         → Store shipment
GET    /shipments/{shipment}              → View details
GET    /shipments/{shipment}/edit         → Edit form
PUT    /shipments/{shipment}              → Update shipment
GET    /shipments/{shipment}/label        → View label
GET    /shipments/{shipment}/print        → Print label
POST   /shipments/{shipment}/status       → Update status
POST   /shipments/{shipment}/track        → Track shipment
POST   /shipments/{shipment}/pick-and-ship → Pick & ship
DELETE /shipments/{shipment}              → Delete shipment
```

### Refunds
**Permission Required:** NONE (⚠️ INCONSISTENCY!)

```
GET    /refunds                           → List refunds
GET    /refunds/create                    → Create form
POST   /refunds                           → Store refund
GET    /refunds/{refund}                  → View details
GET    /refunds/{refund}/edit             → Edit form
PUT    /refunds/{refund}                  → Update refund
POST   /refunds/{refund}/approve          → Approve refund
POST   /refunds/{refund}/reject           → Reject refund
DELETE /refunds/{refund}                  → Delete refund
```

### Invoices
**Permission Required:** Authenticated only (no specific permission)

```
GET    /invoices                          → List invoices
GET    /invoices/{order}                  → View invoice
GET    /invoices/{order}/download         → Download PDF
GET    /invoices/{order}/print            → Print invoice
POST   /invoices/{order}/send             → Send via email
```

### Media Management
**Permission Required:** Authenticated only

```
GET    /media                             → List media
POST   /media                             → Upload media
DELETE /media/{media}                     → Delete media
POST   /media/gallery-order               → Update gallery order
GET    /media/payment-proof/list          → List payment proofs
GET    /media/product-photo/list          → List product photos
GET    /media/shipment-proof/list         → List shipment proofs
GET    /media/banner-image/list           → List banner images
```

### AI Gateway (Ambu Magic)
**Permission Required:** Authenticated only

```
GET    /ai/features                       → Get available AI features
POST   /ai/enhance                        → Enhance image with AI
GET    /ai/jobs/{aiLog}                   → Get job status
```

### Settings & Configuration
**Permission Required:** `role:Super Admin`

#### Settings (Old Implementation)
```
GET    /settings                          → Show settings
POST   /settings                          → Update settings
POST   /settings/bank-accounts            → Add bank account
DELETE /settings/bank-accounts/{account}  → Delete bank account
POST   /settings/users                    → Create user
PUT    /settings/users/{user}             → Update user
DELETE /settings/users/{user}             → Delete user
```

#### Admin Settings (New Implementation - ⚠️ DUPLICATE)
```
GET    /admin/settings                    → General & payment settings
PUT    /admin/settings/general            → Update general settings
PUT    /admin/settings/storefront         → Update storefront settings
PUT    /admin/settings/payment-methods    → Update payment methods
PUT    /admin/settings/payment            → Update payment settings
POST   /admin/settings/footer-menu        → Create footer menu item
PUT    /admin/settings/footer-menu/{item} → Update footer menu item
DELETE /admin/settings/footer-menu/{item} → Delete footer menu item
POST   /admin/settings/footer-menu/reorder → Reorder menu
POST   /admin/settings/users              → Create user
PUT    /admin/settings/users/{user}       → Update user
DELETE /admin/settings/users/{user}       → Delete user
```

### Profile Management
**Permission Required:** Authenticated only

```
GET    /profile                           → Show profile
PATCH  /profile                           → Update profile
DELETE /profile                           → Delete account
```

---

## 🏭 WAREHOUSE ROUTES

**Domain:** `admin.tokoambu.com` (same as admin)
**File:** `routes/warehouse.php`
**Guard:** `web`
**Purpose:** Inventory operations, stock management

### Warehouse Operations
**Permission Required:** Various `warehouse_*` permissions

```
GET    /warehouse                         → Warehouse dashboard
GET    /warehouse/receiving               → Receiving list
POST   /warehouse/receiving/{purchase}    → Receive purchase
GET    /warehouse/transfer                → Transfer form
POST   /warehouse/transfer                → Store transfer
GET    /warehouse/adjustments             → Adjustment form
POST   /warehouse/adjustments             → Store adjustment
GET    /warehouse/opname                  → Stock opname list
GET    /warehouse/opname/view             → View opname
POST   /warehouse/opname                  → Store opname
GET    /warehouse/reports/stock-out       → Stock out report
```

### Warehouse Master Data
**Permission Required:** `view_products` (⚠️ INCONSISTENT!)

```
GET    /warehouse/warehouses              → List warehouses
POST   /warehouse/warehouses              → Create warehouse
PUT    /warehouse/warehouses/{id}         → Update warehouse
DELETE /warehouse/warehouses/{id}         → Delete warehouse
GET    /warehouse/locations               → List locations
POST   /warehouse/locations               → Create location
PUT    /warehouse/locations/{id}          → Update location
DELETE /warehouse/locations/{id}          → Delete location
```

---

## 🔌 API ROUTES

**File:** `routes/api.php`
**Purpose:** RESTful APIs for frontend/integrations

### Public APIs (No Authentication)

#### Location Services
```
GET    /api/provinces                     → Get all provinces
GET    /api/provinces/search              → Search provinces
GET    /api/cities                        → Get cities by province
GET    /api/cities/search                 → Search cities
GET    /api/cities/{provinceId}           → Get cities by province ID
GET    /api/districts                     → Get districts by city
GET    /api/districts/search              → Search districts
GET    /api/districts/{cityId}            → Get districts by city ID
```

#### Shipping Services (RajaOngkir)
```
POST   /api/shipping/cost                 → Calculate shipping cost
POST   /api/shipping/track                → Track shipment
```

### Protected APIs (auth:sanctum)

```
GET    /api/user                          → Get current user
GET    /api/product-categories            → List categories
POST   /api/product-categories            → Create category
GET    /api/product-categories/{id}/custom-fields → Get custom fields
GET    /api/suppliers                     → List suppliers
POST   /api/suppliers                     → Create supplier
POST   /api/bank-accounts                 → Create bank account
```

---

## 🔐 AUTHENTICATION ROUTES

**File:** `routes/auth.php`
**Guard:** `web` (admin authentication)
**Purpose:** Admin login/password management

### Guest Routes (middleware:guest)
```
GET    /login                             → Login form
POST   /login                             → Process login
GET    /forgot-password                   → Password reset form
POST   /forgot-password                   → Send reset email
GET    /reset-password/{token}            → Reset password form
POST   /reset-password                    → Process password reset
```

### Authenticated Routes (middleware:auth)
```
GET    /verify-email                      → Email verification prompt
GET    /verify-email/{id}/{hash}          → Verify email (signed)
POST   /email/verification-notification   → Resend verification
GET    /confirm-password                  → Confirm password
POST   /confirm-password                  → Process confirm
PUT    /password                          → Update password
POST   /logout                            → Logout
```

---

## ⚠️ CRITICAL ISSUES IDENTIFIED

### 1. **Mixed Settings Routes (DUPLICATE PATHS)**

**CONFLICT:** Two separate settings implementations exist:

```
/settings          → SettingController (old)
/admin/settings    → SettingsController (new)
```

**Impact:**
- Code duplication
- Confusion for developers
- Potential data inconsistency
- Two different controllers with similar functionality

**Evidence:**
- Both routes exist in `routes/web.php`
- Both require `role:Super Admin`
- Different controller classes and methods

**Recommended Action:**
1. Choose primary implementation (recommend `/admin/settings`)
2. Migrate any unique functionality from old to new
3. Remove `/settings` routes
4. Update any hardcoded links in Blade templates

---

### 2. **Hardcoded Domain Strings**

**Locations Found:**
- `routes/web.php` line 32: `'admin.tokoambu.com'`
- `bootstrap/app.php` line 41: `'admin.tokoambu.com'`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php` line 46: `'admin.tokoambu.com'`
- `app/Http/Controllers/InvoiceController.php` line 33: Domain manipulation logic

**Problems:**
- Not configurable via environment
- Hard to test locally
- Error-prone when deploying to different environments
- Violates DRY principle

**Recommended Solution:**
```php
// config/domains.php
return [
    'admin' => env('ADMIN_DOMAIN', 'admin.tokoambu.com'),
    'storefront' => env('STOREFRONT_DOMAIN', 'tokoambu.com'),
    'api' => env('API_DOMAIN', 'api.tokoambu.com'), // future
];

// Usage:
if (request()->getHost() === config('domains.admin')) {
    // ...
}
```

---

### 3. **Inconsistent Permission Requirements**

**Issues Found:**

#### A. Refunds Resource (NO PERMISSION!)
```php
Route::resource('refunds', RefundController::class);
```
❌ **Missing:** Should require `permission:manage_refunds`

#### B. Vendors & Employees (WRONG PERMISSION!)
```php
Route::resource('vendors', VendorController::class)
    ->middleware('permission:view_products');
```
❌ **Wrong:** Uses `view_products` instead of dedicated permission

#### C. Warehouse Master Data (WRONG PERMISSION!)
```php
Route::resource('/warehouse/warehouses', WarehouseController::class)
    ->middleware('permission:view_products');
```
❌ **Wrong:** Should use `warehouse_*` permissions

**Recommended Action:**
1. Create dedicated permissions:
   - `manage_refunds`
   - `manage_vendors`
   - `manage_employees`
2. Update route middleware
3. Update role permissions in database seeder

---

### 4. **Domain Routing NOT at Router Level**

**Current Implementation:** Manual checks in controllers

```php
// routes/web.php - Root route
Route::get('/', function () {
    if (request()->getHost() === 'admin.tokoambu.com') {
        return auth()->check() ? redirect('/dashboard') : redirect('/login');
    }
    return redirect()->route('shop.index');
});
```

**Problem:**
- Domain logic scattered throughout application
- Not utilizing Laravel's built-in domain routing
- Difficult to maintain

**Better Approach:**
```php
// bootstrap/app.php or routes/web.php
Route::domain(config('domains.admin'))->group(function() {
    Route::get('/', function() {
        return auth()->check() ? redirect('/dashboard') : redirect('/login');
    });
    require __DIR__.'/admin.php';
});

Route::domain(config('domains.storefront'))->group(function() {
    Route::get('/', fn() => redirect()->route('shop.index'));
    require __DIR__.'/storefront.php';
});
```

---

### 5. **Public Invoice Routes in Wrong File**

**Current:**
```php
// routes/web.php (admin file)
Route::get('/public/invoices/{order}', [InvoiceController::class, 'publicShow'])
    ->name('invoices.public')
    ->middleware('signed');
```

**Issue:**
- Public-facing route in admin route file
- Should be accessible from BOTH domains
- Currently requires domain-specific URL generation

**Recommended:**
- Move to shared routes file OR
- Make explicitly available on both domains
- Use helper method for URL generation (already implemented: `InvoiceController::generatePublicUrl()`)

---

### 6. **Webhook Route Security**

**Current:**
```php
Route::post('/ipaymu/notify', [IPaymuWebhookController::class, 'notify'])
    ->name('ipaymu.notify');
```

**Issues:**
- CSRF exception configured in `bootstrap/app.php` but not explicitly shown on route
- No signature verification middleware
- No rate limiting
- Public route in admin route file

**Recommended:**
```php
Route::post('/ipaymu/notify', [IPaymuWebhookController::class, 'notify'])
    ->name('ipaymu.notify')
    ->withoutMiddleware(['web', 'csrf'])
    ->middleware(['ipaymu.signature', 'throttle:60,1']);
```

---

### 7. **API Routes Not Organized by Access Level**

**Current:** Mixed public and protected routes in same file without clear grouping

**Better Organization:**
```php
// Public APIs
Route::prefix('api/public')->group(function() {
    Route::get('provinces', ...);
    Route::post('shipping/cost', ...);
});

// Protected APIs
Route::prefix('api')->middleware('auth:sanctum')->group(function() {
    Route::get('user', ...);
    Route::apiResource('categories', ...);
});
```

---

## 🎯 MIGRATION ROADMAP

### Phase 1: Configuration & Cleanup (Week 1)

#### 1.1 Create Domain Configuration
```bash
# Create config/domains.php
# Update .env.example with domain variables
# Update all hardcoded domains to use config
```

**Files to Update:**
- Create: `config/domains.php`
- Update: `.env`, `.env.example`
- Update: `routes/web.php`
- Update: `bootstrap/app.php`
- Update: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- Update: `app/Http/Controllers/InvoiceController.php`

#### 1.2 Consolidate Settings Routes
```bash
# Choose primary implementation: /admin/settings
# Migrate unique functionality
# Remove /settings routes
# Update Blade templates
```

**Files to Update:**
- Remove: Routes in `routes/web.php` for `/settings`
- Review: `app/Http/Controllers/SettingController.php` (old)
- Keep: `app/Http/Controllers/Admin/SettingsController.php` (new)
- Update: All Blade files linking to `/settings`

#### 1.3 Fix Permission Inconsistencies
```bash
# Create missing permissions in database
# Update route middleware
# Test authorization
```

**Changes:**
- Database seeder: Add `manage_refunds`, `manage_vendors`, `manage_employees`, `warehouse_manage`
- Update routes: `refunds`, `vendors`, `employees`, `warehouse/warehouses`, `warehouse/locations`

---

### Phase 2: Router-Level Domain Separation (Week 2)

#### 2.1 Reorganize Route Files
```
routes/
├── admin.php                 (NEW - consolidate from web.php)
├── storefront.php            (existing, verify)
├── warehouse.php             (existing, verify)
├── api.php                   (existing, reorganize)
├── auth.php                  (existing, verify)
├── shared.php                (NEW - public invoices, webhooks)
└── web.php                   (becomes router only)
```

#### 2.2 Implement Domain Routing
Update `bootstrap/app.php` or create `routes/web.php` as router:

```php
// Admin domain
Route::domain(config('domains.admin'))
    ->middleware('web')
    ->group(base_path('routes/admin.php'));

// Storefront domain
Route::domain(config('domains.storefront'))
    ->group(base_path('routes/storefront.php'));

// Shared routes (accessible from both)
require base_path('routes/shared.php');
```

---

### Phase 3: Testing & Validation (Week 3)

#### 3.1 Test Scenarios

**Admin Domain Tests:**
- [ ] Login redirects to `/login` when unauthenticated
- [ ] Dashboard accessible after login
- [ ] All admin routes require authentication
- [ ] Permissions properly enforced
- [ ] Settings accessible only to Super Admin
- [ ] Logout redirects to admin login

**Storefront Domain Tests:**
- [ ] Root redirects to `/shop`
- [ ] Customer login/register works
- [ ] Cart functionality works
- [ ] Checkout process works
- [ ] Customer dashboard accessible
- [ ] Logout works correctly

**Cross-Domain Tests:**
- [ ] Public invoice URLs work from both domains
- [ ] Webhooks receive callbacks correctly
- [ ] API endpoints accessible
- [ ] Media/assets load correctly

#### 3.2 Local Testing Setup
```bash
# Update /etc/hosts
127.0.0.1 tokoambu.test
127.0.0.1 admin.tokoambu.test

# Update .env for local
APP_URL=http://admin.tokoambu.test:8080
ADMIN_DOMAIN=admin.tokoambu.test
STOREFRONT_DOMAIN=tokoambu.test
```

---

### Phase 4: Production Deployment (Week 4)

#### 4.1 Pre-Deployment Checklist
- [ ] All tests passing
- [ ] Database migrations ready (permissions)
- [ ] Environment variables documented
- [ ] Rollback plan prepared
- [ ] Monitoring/logging configured

#### 4.2 Deployment Steps
1. Deploy code to staging
2. Test all critical flows in staging
3. Update production `.env` with domain config
4. Deploy to production during low-traffic window
5. Monitor error logs
6. Verify all critical routes

#### 4.3 Post-Deployment Validation
- [ ] Admin login works
- [ ] Customer login works
- [ ] Orders can be created
- [ ] Payments process correctly
- [ ] Webhooks receiving callbacks
- [ ] Public invoices accessible

---

## 📋 DETAILED FILE CHANGES

### Files to CREATE

#### `config/domains.php`
```php
<?php

return [
    'admin' => env('ADMIN_DOMAIN', 'admin.tokoambu.com'),
    'storefront' => env('STOREFRONT_DOMAIN', 'tokoambu.com'),
];
```

#### `routes/admin.php`
```php
<?php
// Consolidate all admin routes from web.php
// Add domain-specific logic here
```

#### `routes/shared.php`
```php
<?php
// Public invoices, webhooks, etc
// Routes accessible from both domains
```

### Files to MODIFY

#### `bootstrap/app.php`
**Change:** Update routing configuration to use domain groups

#### `routes/web.php`
**Change:** Become router only, delegate to domain-specific files

#### `routes/storefront.php`
**Change:** Verify routes, ensure consistent naming

#### `routes/api.php`
**Change:** Organize by public/protected groups

#### `.env` & `.env.example`
**Add:**
```
ADMIN_DOMAIN=admin.tokoambu.com
STOREFRONT_DOMAIN=tokoambu.com
```

#### All Controllers with Domain Checks
**Change:** Replace hardcoded domains with `config('domains.admin')`

**Affected Controllers:**
- `AuthenticatedSessionController.php`
- `InvoiceController.php`
- Any others with domain logic

### Files to DELETE/DEPRECATE

#### Routes
- `/settings` routes in `web.php`

#### Controllers (if unused after migration)
- `app/Http/Controllers/SettingController.php` (verify first)

---

## 🔍 VERIFICATION CHECKLIST

### Pre-Migration
- [ ] Document all current routes (`php artisan route:list > routes-before.txt`)
- [ ] Document all permissions in database
- [ ] Identify all hardcoded domain references
- [ ] List all settings-related code

### During Migration
- [ ] Create domain config file
- [ ] Update all hardcoded domains
- [ ] Reorganize route files
- [ ] Implement router-level domain separation
- [ ] Fix permission inconsistencies
- [ ] Consolidate settings routes

### Post-Migration
- [ ] Document all routes again (`php artisan route:list > routes-after.txt`)
- [ ] Compare before/after route lists
- [ ] Test all critical user flows
- [ ] Verify permissions work correctly
- [ ] Test on local environment
- [ ] Test on staging environment
- [ ] Monitor production logs

---

## 📊 IMPACT ANALYSIS

### Low Risk Changes
✅ Create domain config file
✅ Add environment variables
✅ Fix permission middleware (gradual rollout possible)

### Medium Risk Changes
⚠️ Consolidate settings routes (test thoroughly)
⚠️ Update domain checks to use config

### High Risk Changes
🔴 Router-level domain separation (major structural change)
🔴 Route file reorganization (affects all routes)

**Recommendation:** Implement in phases, test extensively at each phase.

---

## 🚀 SUCCESS CRITERIA

Migration is considered successful when:

1. ✅ No hardcoded domains in codebase
2. ✅ All routes accessible from correct domain
3. ✅ Guest redirects work correctly per domain
4. ✅ Logout redirects to correct login page
5. ✅ Public invoice URLs use storefront domain
6. ✅ All permissions properly enforced
7. ✅ No duplicate settings routes
8. ✅ All tests passing
9. ✅ Zero production errors related to routing
10. ✅ Documentation updated

---

## 📝 NOTES & CONSIDERATIONS

### Current Workarounds Implemented

#### Public Invoice URL Generation
**Status:** ✅ Implemented
**Location:** `InvoiceController::generatePublicUrl()`
**Logic:** Dynamically removes `admin.` subdomain from current URL
**Note:** Works but should use domain config once implemented

#### Logout Redirect
**Status:** ✅ Implemented
**Location:** `AuthenticatedSessionController::destroy()`
**Logic:** Checks host and redirects to appropriate login
**Note:** Works but should use domain config

### Future Enhancements

1. **API Subdomain:** Consider `api.tokoambu.com` for RESTful APIs
2. **Mobile App Backend:** Separate routes for mobile API
3. **Webhook Signature Verification:** Implement middleware for all webhooks
4. **Rate Limiting:** Per-domain rate limiting rules
5. **CDN Integration:** Domain-specific asset serving

---

## 🔗 RELATED DOCUMENTATION

- `CLAUDE.md` - Project overview and conventions
- `02-warehouse_inventory_system_blueprint.md` - Inventory architecture
- `RAJAONGKIR_COMMAND_GUIDE.md` - Location services
- `SISTEM_ALAMAT_SUMMARY.md` - Address system

---

## 👥 STAKEHOLDERS

**Technical Lead:** Review and approve architecture
**Backend Team:** Implement routing changes
**Frontend Team:** Update any hardcoded URLs in JavaScript
**QA Team:** Test all scenarios thoroughly
**DevOps:** Update deployment scripts, environment configs

---

## ✅ APPROVAL & SIGN-OFF

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Project Lead | | | |
| Technical Architect | | | |
| Senior Developer | | | |
| QA Lead | | | |

---

**Document Version:** 1.0
**Last Updated:** 2026-01-16
**Status:** Draft - Awaiting Review
