<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard Preorder
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @php
                        $selectedProduct = collect($productOptions ?? [])->firstWhere('id', (int) request('product_id'));
                    @endphp

                    <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-4 mb-6">
                        <form method="GET" action="{{ route('preorders.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_auto] md:items-end">
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Produk</label>
                                <input
                                    type="text"
                                    id="product-search"
                                    autocomplete="off"
                                    placeholder="Cari produk preorder..."
                                    value="{{ $selectedProduct['name'] ?? '' }}"
                                    class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                                >
                                <input type="hidden" id="product-id" name="product_id" value="{{ request('product_id') }}">
                                <div id="product-dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                                @error('product_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex gap-2">
                                <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition">
                                    Filter
                                </button>
                                <a href="{{ route('preorders.index') }}" class="h-10 px-4 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition inline-flex items-center">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <h3 class="text-lg font-semibold mb-4 text-gray-900">Produk Preorder Aktif</h3>

                    @if ($products->isEmpty())
                        <div class="text-center py-12 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="mt-4">Belum ada produk preorder aktif</p>
                            <a href="{{ route('products.index') }}" class="mt-2 inline-block text-primary hover:underline">
                                Kelola Produk
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($products as $product)
                                @php
                                    $activeOrders = $product->orders->whereIn('status', ['waiting_dp', 'dp_paid', 'product_ready', 'waiting_payment']);
                                    $totalQty = $activeOrders->sum(function ($order) use ($product) {
                                        return $order->items->where('product_id', $product->id)->sum('quantity');
                                    });
                                    $waitingDp = $activeOrders->where('status', 'waiting_dp')->count();
                                    $dpPaid = $activeOrders->where('status', 'dp_paid')->count();
                                    $productReady = $activeOrders->where('status', 'product_ready')->count();
                                    $waitingPayment = $activeOrders->where('status', 'waiting_payment')->count();
                                @endphp

                                <div class="border rounded-lg overflow-hidden hover:shadow-md transition">
                                    <div class="p-4 bg-gray-50 border-b">
                                        <h4 class="font-semibold text-gray-900 mb-1">{{ $product->name }}</h4>
                                        <p class="text-sm text-gray-600">SKU: {{ $product->sku }}</p>
                                    </div>

                                    <div class="p-4 space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Total Pesanan</span>
                                            <span class="font-semibold text-gray-900">{{ $activeOrders->count() }} order</span>
                                        </div>

                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Total Qty</span>
                                            <span class="font-semibold text-primary">{{ $totalQty }} pcs</span>
                                        </div>

                                        <div class="border-t pt-3 space-y-2">
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-600">Menunggu DP</span>
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">{{ $waitingDp }}</span>
                                            </div>
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-600">DP Lunas</span>
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">{{ $dpPaid }}</span>
                                            </div>
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-600">Produk Siap</span>
                                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded">{{ $productReady }}</span>
                                            </div>
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-600">Menunggu Pelunasan</span>
                                                <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded">{{ $waitingPayment }}</span>
                                            </div>
                                        </div>

                                        <div class="pt-3">
                                            <a href="{{ route('preorders.show', $product) }}"
                                               class="block w-full text-center px-4 py-2 bg-primary text-white rounded hover:bg-primary-hover transition">
                                                Lihat Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/autocomplete.js') }}"></script>
    <script>
        const preorderProducts = @json($productOptions ?? []);

        if (preorderProducts.length) {
            new Autocomplete({
                inputId: 'product-search',
                hiddenInputId: 'product-id',
                dropdownId: 'product-dropdown',
                data: preorderProducts,
                searchFields: ['name', 'sku'],
                displayTemplate: (product) => {
                    let html = `<div class="font-medium">${product.name}</div>`;
                    if (product.sku) {
                        html += `<div class="text-xs text-gray-500">SKU: ${product.sku}</div>`;
                    }
                    return html;
                },
                maxItems: 10
            });
        }
    </script>
</x-app-layout>
