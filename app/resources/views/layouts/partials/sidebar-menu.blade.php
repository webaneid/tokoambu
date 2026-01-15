<div class="flex h-full flex-col">
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-4">
        <a href="{{ route('dashboard') }}" class="flex items-center justify-center w-full">
            <x-application-logo class="h-12 w-auto max-w-full" />
        </a>
        @if(!empty($showCloseButton))
            <button @click="sidebarOpen = false" class="rounded-full p-2 text-gray-400 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/40">
                <span class="sr-only">Tutup navigasi</span>
                <x-heroicon name="x-mark" class="h-5 w-5" />
            </button>
        @endif
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-4">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 rounded-lg px-4 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
            <x-heroicon name="home" class="h-5 w-5 flex-shrink-0" />
            <span>Dashboard</span>
        </a>

        @if(auth()->user()->can('view_products') || auth()->user()->can('view_suppliers') || auth()->user()->can('view_customers') || auth()->user()->can('view_ledger'))
        <div class="mt-1 space-y-1" x-data="{ submenuOpen: @json(request()->routeIs('products.*') || request()->routeIs('product-categories.*') || request()->routeIs('financial-categories.*') || request()->routeIs('suppliers.*') || request()->routeIs('customers.*') || request()->routeIs('warehouse.warehouses.*') || request()->routeIs('vendors.*') || request()->routeIs('employees.*')) }">
            <button @click="submenuOpen = !submenuOpen" class="flex w-full items-center space-x-3 rounded-lg px-4 py-2 text-left text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100">
                <x-heroicon name="cube" class="h-5 w-5 flex-shrink-0" />
                <span>Master Data</span>
                <x-heroicon name="chevron-down" class="ml-auto h-4 w-4 transition-transform" x-bind:class="{ 'rotate-180': submenuOpen }" />
            </button>
            <div x-show="submenuOpen" x-transition.opacity class="ml-4 mt-1 space-y-1 border-l border-gray-200 pl-3">
                @can('view_products')
                <a href="{{ route('products.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Produk</a>
                <a href="{{ route('product-categories.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Kategori Produk</a>
                @endcan
                @can('view_suppliers')
                <a href="{{ route('suppliers.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Supplier</a>
                @endcan
                @can('view_customers')
                <a href="{{ route('customers.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Customer</a>
                @endcan
                @can('view_products')
                <a href="{{ route('warehouse.warehouses.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Master Gudang</a>
                @endcan
                @can('view_ledger')
                <a href="{{ route('financial-categories.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Kategori Ledger</a>
                @endcan
                @can('view_products')
                <a href="{{ route('vendors.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Vendor</a>
                <a href="{{ route('employees.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Karyawan</a>
                @endcan
            </div>
        </div>
        @endif

        @can('view_products')
        <div class="space-y-1" x-data="{ submenuOpen: @json(request()->routeIs('pages.*')) }">
            <button @click="submenuOpen = !submenuOpen" class="flex w-full items-center space-x-3 rounded-lg px-4 py-2 text-left text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100">
                <x-heroicon name="document-text" class="h-5 w-5 flex-shrink-0" />
                <span>Laman</span>
                <x-heroicon name="chevron-down" class="ml-auto h-4 w-4 transition-transform" x-bind:class="{ 'rotate-180': submenuOpen }" />
            </button>
            <div x-show="submenuOpen" x-transition.opacity class="ml-4 mt-1 space-y-1 border-l border-gray-200 pl-3">
                <a href="{{ route('pages.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Semua Laman</a>
                @can('create_products')
                <a href="{{ route('pages.create') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Buat Laman</a>
                @endcan
            </div>
        </div>
        @endcan

        @can('view_purchases')
        <div class="space-y-1" x-data="{ submenuOpen: @json(request()->routeIs('purchases.*')) }">
            <button @click="submenuOpen = !submenuOpen" class="flex w-full items-center space-x-3 rounded-lg px-4 py-2 text-left text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100">
                <x-heroicon name="shopping-cart" class="h-5 w-5 flex-shrink-0" />
                <span>Pembelian</span>
                <x-heroicon name="chevron-down" class="ml-auto h-4 w-4 transition-transform" x-bind:class="{ 'rotate-180': submenuOpen }" />
            </button>
            <div x-show="submenuOpen" x-transition.opacity class="ml-4 mt-1 space-y-1 border-l border-gray-200 pl-3">
                <a href="{{ route('purchases.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Semua Pembelian</a>
                @can('create_purchases')
                <a href="{{ route('purchases.create') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Buat Pembelian</a>
                @endcan
            </div>
        </div>
        @endcan

        @if(auth()->user()->can('warehouse_dashboard') || auth()->user()->can('warehouse_receiving') || auth()->user()->can('warehouse_transfer') || auth()->user()->can('warehouse_adjustment') || auth()->user()->can('warehouse_opname'))
        <div class="space-y-1" x-data="{ submenuOpen: true }">
            <button @click="submenuOpen = !submenuOpen" class="flex w-full items-center space-x-3 rounded-lg px-4 py-2 text-left text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100">
                <x-heroicon name="building-storefront" class="h-5 w-5 flex-shrink-0" />
                <span>Gudang</span>
                <x-heroicon name="chevron-down" class="ml-auto h-4 w-4 transition-transform" x-bind:class="{ 'rotate-180': submenuOpen }" />
            </button>
            <div x-show="submenuOpen" x-transition.opacity class="ml-4 mt-1 space-y-1 border-l border-gray-200 pl-3">
                @can('warehouse_dashboard')
                <a href="{{ route('warehouse.dashboard') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Dashboard Gudang</a>
                @endcan
                @can('warehouse_receiving')
                <a href="{{ route('warehouse.receiving.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Penerimaan</a>
                @endcan
                @can('warehouse_transfer')
                <a href="{{ route('warehouse.transfer.create') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Transfer Stok</a>
                @endcan
                @can('warehouse_adjustment')
                <a href="{{ route('warehouse.adjustments.create') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Pengeluaran Stok</a>
                @endcan
                @can('warehouse_opname')
                <a href="{{ route('warehouse.opname.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Stock Opname</a>
                @endcan
            </div>
        </div>
        @endif

        @can('view_products')
        <div class="space-y-1" x-data="{ submenuOpen: @json(request()->is('promotions*')) }">
            <button @click="submenuOpen = !submenuOpen" class="flex w-full items-center space-x-3 rounded-lg px-4 py-2 text-left text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100">
                <x-heroicon name="ticket" class="h-5 w-5 flex-shrink-0" />
                <span>Promo</span>
                <x-heroicon name="chevron-down" class="ml-auto h-4 w-4 transition-transform" x-bind:class="{ 'rotate-180': submenuOpen }" />
            </button>
            <div x-show="submenuOpen" x-transition.opacity class="ml-4 mt-1 space-y-1 border-l border-gray-200 pl-3">
                <a href="{{ url('/promotions') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Promo Center</a>
            </div>
        </div>
        @endcan

        @can('view_orders')
        <div class="space-y-1" x-data="{ submenuOpen: @json(request()->routeIs('orders.*') || request()->routeIs('payments.*') || request()->routeIs('shipments.*') || request()->routeIs('invoices.*')) }">
            <button @click="submenuOpen = !submenuOpen" class="flex w-full items-center space-x-3 rounded-lg px-4 py-2 text-left text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100">
                <x-heroicon name="shopping-bag" class="h-5 w-5 flex-shrink-0" />
                <span>Penjualan</span>
                <x-heroicon name="chevron-down" class="ml-auto h-4 w-4 transition-transform" x-bind:class="{ 'rotate-180': submenuOpen }" />
            </button>
            <div x-show="submenuOpen" x-transition.opacity class="ml-4 mt-1 space-y-1 border-l border-gray-200 pl-3">
                <a href="{{ route('orders.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Order</a>
                @can('view_payments')
                <a href="{{ route('payments.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Pembayaran</a>
                @endcan
                <a href="{{ route('invoices.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Invoice</a>
                <a href="{{ route('orders.packing') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Pengemasan</a>
                @can('view_shipments')
                <a href="{{ route('shipments.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Pengiriman</a>
                @endcan
            </div>
        </div>
        @endcan

        @can('view_orders')
        <div class="space-y-1" x-data="{ submenuOpen: @json(request()->routeIs('preorders.*')) }">
            <button @click="submenuOpen = !submenuOpen" class="flex w-full items-center space-x-3 rounded-lg px-4 py-2 text-left text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100">
                <x-heroicon name="clock" class="h-5 w-5 flex-shrink-0" />
                <span>Preorder</span>
                <x-heroicon name="chevron-down" class="ml-auto h-4 w-4 transition-transform" x-bind:class="{ 'rotate-180': submenuOpen }" />
            </button>
            <div x-show="submenuOpen" x-transition.opacity class="ml-4 mt-1 space-y-1 border-l border-gray-200 pl-3">
                <a href="{{ route('preorders.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Dashboard Preorder</a>
            </div>
        </div>
        @endcan

        @can('view_ledger')
        <div class="space-y-1" x-data="{ submenuOpen: @json(request()->routeIs('ledger.*') || request()->routeIs('refunds.*')) }">
            <button @click="submenuOpen = !submenuOpen" class="flex w-full items-center space-x-3 rounded-lg px-4 py-2 text-left text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100">
                <x-heroicon name="currency-dollar" class="h-5 w-5 flex-shrink-0" />
                <span>Keuangan</span>
                <x-heroicon name="chevron-down" class="ml-auto h-4 w-4 transition-transform" x-bind:class="{ 'rotate-180': submenuOpen }" />
            </button>
            <div x-show="submenuOpen" x-transition.opacity class="ml-4 mt-1 space-y-1 border-l border-gray-200 pl-3">
                <a href="{{ route('ledger.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Buku Kas</a>
                @can('create_ledger_entry')
                <a href="{{ route('ledger.create') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Tambah Transaksi</a>
                @endcan
                <a href="{{ route('refunds.index') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Refund</a>
            </div>
        </div>
        @endcan

        @if(auth()->user()->can('view_ledger') || auth()->user()->can('warehouse_report'))
        <div class="space-y-1" x-data="{ submenuOpen: true }">
            <button @click="submenuOpen = !submenuOpen" class="flex w-full items-center space-x-3 rounded-lg px-4 py-2 text-left text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100">
                <x-heroicon name="chart-bar" class="h-5 w-5 flex-shrink-0" />
                <span>Laporan</span>
                <x-heroicon name="chevron-down" class="ml-auto h-4 w-4 transition-transform" x-bind:class="{ 'rotate-180': submenuOpen }" />
            </button>
            <div x-show="submenuOpen" x-transition.opacity class="ml-4 mt-1 space-y-1 border-l border-gray-200 pl-3">
                @can('view_ledger')
                <a href="{{ route('ledger.report') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Laporan Keuangan</a>
                @endcan
                @can('warehouse_report')
                <a href="{{ route('warehouse.reports.stock_out') }}" class="block rounded px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-primary">Pengeluaran Stok</a>
                @endcan
            </div>
        </div>
        @endif

        @if(auth()->user()?->hasRole('Super Admin'))
            <a href="{{ route('admin.settings.index') }}" class="mt-4 flex items-center space-x-3 rounded-lg border-t border-gray-100 px-4 pt-4 text-sm font-medium transition hover:text-primary">
                <x-heroicon name="cog-6-tooth" class="h-5 w-5 flex-shrink-0" />
                <span>Pengaturan</span>
            </a>
        @endif
    </div>

    <div class="border-t border-gray-200 px-4 py-4 text-sm">
        @if(Auth::check())
            <div class="font-semibold text-gray-900">{{ Auth::user()->name }}</div>
            <div class="text-xs text-gray-500">{{ implode(', ', Auth::user()->getRoleNames()->toArray()) }}</div>
        @else
            <div class="font-semibold text-gray-900">Guest</div>
        @endif
    </div>
</div>
