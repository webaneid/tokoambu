# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Toko Ambu** is a Laravel-based e-commerce web application for UMKM (small/medium businesses) to manage products, orders, preorders, payments, inventory, and financial tracking with a mobile-first responsive design.

## Development Commands

### Initial Setup
```bash
cd app
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

### Running Development Environment
```bash
cd app
composer dev
```
This runs concurrently: Laravel server, queue worker, Pail logs, and Vite dev server.

Alternatively, run services individually:
```bash
php artisan serve              # Server at http://localhost:8000
php artisan queue:listen        # Queue worker
php artisan pail                # Real-time logs
npm run dev                     # Vite dev server
```

### Testing
```bash
composer test                   # Run PHPUnit tests
php artisan test                # Alternative
php artisan test --filter=TestName  # Run specific test
```

### Code Quality
```bash
./vendor/bin/pint               # Laravel Pint (code formatter)
```

### Database
```bash
php artisan migrate             # Run migrations
php artisan migrate:fresh --seed # Fresh migration with seeders
php artisan db:seed             # Run seeders only
```

### Build for Production
```bash
npm run build
```

### RajaOngkir Location Data
```bash
php artisan rajaongkir:fetch           # Fetch & save to database
php artisan rajaongkir:fetch --save-json  # Also create JSON backup
```

## High-Level Architecture

### Project Structure

The project uses a **hybrid architecture**:

- **Standard Laravel MVC** for most features (Orders, Products, Customers, Payments)
- **Domain-Driven Design (DDD)** for complex domains like Inventory
- **Service Layer** for business logic (LocationService, InventoryService)
- **Event/Listener Pattern** for inventory state management

### Directory Layout (inside `app/`)

```
app/app/
├── Console/Commands/      # Artisan commands (e.g., FetchRajaongkirData)
├── Http/
│   ├── Controllers/       # Standard controllers
│   │   ├── Api/          # API endpoints (locations autocomplete)
│   │   ├── Auth/         # Laravel Breeze auth
│   │   └── Warehouse/    # Warehouse-specific controllers
├── Models/               # Eloquent models
├── Services/             # Business logic services
└── View/                 # View composers

app/Domain/               # DDD modules (outside app/ directory)
└── Inventory/
    ├── Events/          # Domain events (StockAdjusted, etc.)
    ├── Listeners/       # Event handlers
    └── Services/        # InventoryService
```

### Key Architectural Decisions

#### 1. Inventory System (Event-Driven)

**CRITICAL RULES:**
- **Never update stock directly** in database tables
- All stock changes **must** create a `stock_movements` record
- Stock changes happen **only via domain events**:
  - `PurchaseReceived` → increases inventory
  - `OrderPackedOrShipped` → decreases inventory
  - `StockAdjusted` → manual adjustments (damaged, gifts, etc.)
  - `StockTransferred` → moves stock between locations
  - `StockOpnameConfirmed` → stock opname corrections

**Source of Truth:**
- `inventory_balances` table (current stock per location)
- `stock_movements` table (audit trail)

**When Stock Decreases:**
- Stock is reduced when order status changes to `packed` or `shipped` (NOT when order is created)

#### 2. Location System (Indonesia Address Data)

Uses **RajaOngkir API** for Indonesian provinces, cities, and districts.

**Data Flow:**
- Primary: Database tables (`provinces`, `cities`, `districts`)
- Fallback: JSON file (`storage/rajaongkir/locations.json`)
- Service: `LocationService` auto-detects which to use

**Autocomplete Implementation:**
- Frontend uses autocomplete inputs (not dropdowns) for better UX with 7000+ districts
- Cascading: Province → City → District → Postal Code
- API endpoints: `/api/provinces/search`, `/api/cities/search`, `/api/districts/search`

#### 3. Role-Based Access Control (Spatie Permission)

**Roles:**
- `Super Admin` - Full access, manage settings, users, API keys
- `Operator` - Manage products, orders, shipments (no financial reports)
- `Finance` - Manage payments, ledger, reports (no inventory operations)

Use Spatie permission middleware:
```php
Route::middleware('role:Super Admin')->group(function () { ... });
Route::middleware('permission:stock_adjustment')->group(function () { ... });
```

#### 4. Order Status Flow

```
draft → waiting_payment → dp_paid → paid → packed → shipped → done
                        ↘ cancelled
```

**Important:** Stock reduction happens at `packed` or `shipped`, NOT at order creation.

#### 5. Purchase Status Flow

```
draft → ordered → shipped → received
                 ↘ cancelled
```

**Important:** Stock increase happens at `received` status (after receiving + putaway process).

### Core Domain Models

**Master Data:**
- `User`, `Product`, `Supplier`, `Customer`
- `Province`, `City`, `District` (location data)
- `Warehouse`, `Location` (inventory locations)

**Transactions:**
- `Order`, `OrderItem`, `Payment`, `Attachment`
- `Purchase`, `PurchaseItem`
- `Shipment`
- `LedgerEntry` (cash flow tracking)

**Inventory:**
- `InventoryBalance` (current stock per location)
- `StockMovement` (audit trail of all stock changes)
- `InventoryAnalytics` (dead stock, slow-moving analysis)

### Styling System

**Framework:** Tailwind CSS v3 with Vite

**Color Palette (Locked):**
- Primary (Orange): `#F17B0D` → use `bg-primary`, `text-primary`, `hover:bg-primary-hover`
- Secondary (Blue): `#0D36AA` → use `bg-blue`, `text-blue`
- Accent (Pink): `#D00086` → use sparingly for badges/highlights
- Neutral: White backgrounds (`#FFFFFF`), gray borders (`#E5E7EB`)

**Design Principles:**
- Clean, white/light backgrounds (professional UMKM feel)
- Orange for CTAs and important numbers
- Blue for informational elements
- Pink used sparingly as accent
- Mobile-first responsive design

**DO NOT:**
- Hardcode colors outside Tailwind config
- Use gradients excessively
- Use more than 2 accent colors per screen

## Important Conventions

### Database Transactions
Always wrap stock/inventory operations in DB transactions:
```php
DB::transaction(function () {
    // Stock changes here
});
```

### Event Dispatching
Fire domain events for inventory changes:
```php
use App\Domain\Inventory\Events\PurchaseReceived;

event(new PurchaseReceived($purchase));
```

### Service Layer Usage
Use services for complex business logic:
```php
$locationService = new LocationService();
$provinces = $locationService->searchProvinces('jakarta', 6);

$inventoryService = new InventoryService();
$inventoryService->adjustStock($product, $location, $qty, $reason);
```

### Stock Movement Types
- `receive` - Stock in from supplier
- `ship` - Stock out to customer
- `transfer` - Move between locations
- `adjust` - Manual adjustment (damaged/gift/lost)
- `reserve` - Reserved for preorder
- `unreserve` - Release reservation

### Ledger Entry Types
- `income` - Money in (customer payments)
- `expense` - Money out (supplier purchases)

## Testing Checklist for New Features

When adding inventory-related features:
- [ ] Creates `stock_movements` record
- [ ] Updates `inventory_balances` atomically
- [ ] Wrapped in DB transaction
- [ ] Validates stock availability before reduction
- [ ] Includes proper audit trail (reference_type, reference_id)

When adding customer/order features:
- [ ] Location autocomplete works (province → city → district)
- [ ] Postal code auto-fills from district
- [ ] Form validation handles Indonesian addresses
- [ ] Status transitions follow defined flow

## RajaOngkir Integration Notes

See [RAJAONGKIR_COMMAND_GUIDE.md](RAJAONGKIR_COMMAND_GUIDE.md) and [SISTEM_ALAMAT_SUMMARY.md](SISTEM_ALAMAT_SUMMARY.md) for detailed implementation.

**Key Points:**
- API key stored in `.env` as `RAJAONGKIR_API_KEY`
- Fetch command populates ~7000 districts
- LocationService auto-detects DB vs JSON mode
- Autocomplete returns max 6 items by default

## Warehouse/Inventory System

See [02-warehouse_inventory_system_blueprint.md](02-warehouse_inventory_system_blueprint.md) for comprehensive blueprint.

**Critical Rules:**
1. Never update stock without creating stock_movement
2. Reduce stock only when order is `packed` or `shipped`
3. Increase stock only when purchase is `received`
4. All movements must have reference (order_id, purchase_id, etc.)
5. Use event-driven approach for all stock changes

**Dead Stock Detection:**
- Configured thresholds in settings
- `slow_moving` - no movement for X days
- `dead_stock` - no movement for Y days
- Generates recommendations (not automatic price changes)

## Payment & Financial Tracking

- Payments support DP (down payment) and full payment
- Each payment can have multiple attachments (transfer receipts)
- Payment verification creates ledger entries automatically
- Order status updates based on payment amount (dp_paid vs paid)

## Invoice & Label Generation

- Invoice: Uses DomPDF (`barryvdh/laravel-dompdf`)
- Shipping Label: Printable format with barcode support
- Both use shop info from settings table

## Settings/Configuration

Super Admin can configure via `/settings`:
- Shop info (name, logo, address, WhatsApp, email)
- Invoice prefix and format
- Shipping defaults (origin city for RajaOngkir)
- RajaOngkir API key and mode
- Stock threshold settings (dead stock days)

## Common Pitfalls to Avoid

1. **Don't reduce stock when order is created** - only when packed/shipped
2. **Don't increase stock when purchase is created** - only when received
3. **Don't update inventory without stock_movements** - always use events
4. **Don't hardcode colors** - use Tailwind config classes
5. **Don't skip DB transactions** for financial/inventory operations
6. **Don't use dropdowns for districts** - use autocomplete (too many records)
7. **Don't modify stock directly** - fire domain events instead

## Performance Considerations

- Location data: Use JSON mode for faster autocomplete (no DB queries)
- Inventory queries: Index `location_id`, `product_id` on `inventory_balances`
- Stock movements: Index `reference_type`, `reference_id` for audit queries
- Dead stock: Run as scheduled job (don't compute on every page load)
