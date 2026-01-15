<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900">Detail Produk</h2>
            <div class="space-x-2">
                @can('edit_products')
                    <a href="{{ route('products.edit', $product) }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">
                        Edit
                    </a>
                @endcan
                <a href="{{ route('products.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Featured Image -->
            @if($product->featuredMedia)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="relative w-full overflow-hidden bg-gray-100" style="padding-bottom: 100%; height: 0;">
                        <img src="{{ $product->featuredMedia->url }}" alt="{{ $product->featuredMedia->filename }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; object-position: center;">
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center text-sm text-gray-500" style="height: 120px;">
                            Belum ada foto utama
                        </div>
                    </div>
                </div>
            @endif

            <!-- Gallery -->
            @if($product->galleryMedia->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Gallery Produk</h3>
                    <div class="grid grid-cols-5 gap-3">
                        @foreach($product->galleryMedia as $media)
                            <div class="relative bg-white rounded-lg border-2 border-gray-200 overflow-hidden h-32">
                                <img src="{{ $media->url }}" alt="{{ $media->filename }}" class="w-full h-full object-cover">
                                <div class="absolute top-2 left-2 bg-white px-2 py-0.5 rounded shadow-sm text-xs font-semibold text-gray-700">
                                    {{ $loop->iteration }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Card: Variasi (Variant Selector) -->
            @if($product->has_variants && $product->variant_groups && count($product->variant_groups) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Variasi</h3>

                    <div class="space-y-6" id="variant-selector">
                        @foreach($product->variant_groups as $group)
                            <div class="variant-group" data-group-name="{{ $group['name'] }}">
                                <label class="block text-sm font-medium text-gray-700 mb-3">{{ $group['name'] }}</label>
                                <div class="flex flex-wrap gap-3">
                                    @foreach($group['options'] as $option)
                                        <button
                                            type="button"
                                            class="variant-option-btn"
                                            data-group="{{ $group['name'] }}"
                                            data-option="{{ $option }}"
                                            data-has-image="false">
                                            <span class="option-label">{{ $option }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Selected Variant Info -->
                    <div id="selected-variant-info" class="mt-6 p-4 bg-gray-50 rounded-lg hidden">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-600">SKU</p>
                                <p class="text-sm font-semibold" id="variant-sku">-</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600">Harga</p>
                                <p class="text-sm font-semibold text-primary" id="variant-price">-</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600">Stok</p>
                                <p class="text-sm font-semibold" id="variant-stock">-</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600">Status</p>
                                <p class="text-sm" id="variant-status">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Card: Informasi Utama -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">SKU</p>
                            <p class="text-lg font-semibold">{{ $product->sku }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Nama Produk</p>
                            <p class="text-lg font-semibold">{{ $product->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Kategori</p>
                            <p class="text-lg">{{ $product->category?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="text-lg">
                                <span class="px-3 py-1 {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} rounded-full text-sm">
                                    {{ $product->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Pricing & Supplier -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Supplier</p>
                            <p class="text-lg">{{ $product->supplier?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Tipe Produk</p>
                            <p class="text-lg">
                                <span class="px-3 py-1 {{ $product->has_variants ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' }} rounded-full text-sm">
                                    {{ $product->has_variants ? 'Produk Variasi' : 'Produk Simple' }}
                                </span>
                            </p>
                        </div>
                        @if(!$product->has_variants)
                        <div>
                            <p class="text-sm text-gray-600">Harga Modal</p>
                            <p class="text-lg font-semibold">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Harga Jual</p>
                            <p class="text-lg font-semibold text-primary">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card: Logistik -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Berat</p>
                            <p class="text-lg font-semibold">{{ number_format($product->weight_grams ?? 0) }} g</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Preorder</p>
                            <p class="text-lg">
                                <span class="px-3 py-1 {{ $product->allow_preorder ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700' }} rounded-full text-sm">
                                    {{ $product->allow_preorder ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Inventory -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Inventory</h3>

                    @php
                        // Use reserved quantity calculated from actual DP-paid orders
                        $totalReserved = $reservedQty ?? 0;
                        $totalOnHand = $product->qty_on_hand;
                        $available = max(0, $totalOnHand - $totalReserved);
                    @endphp

                    <div class="grid grid-cols-3 gap-4">
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-xs text-blue-600 font-medium uppercase mb-1">Total Stock</p>
                            <p class="text-2xl font-bold text-blue-900">{{ number_format($totalOnHand, 0) }}</p>
                            <p class="text-xs text-blue-600 mt-1">Stock fisik di gudang</p>
                        </div>

                        <div class="p-4 bg-orange-50 rounded-lg">
                            <p class="text-xs text-orange-600 font-medium uppercase mb-1">Reserved (DP Paid)</p>
                            <p class="text-2xl font-bold text-orange-900">{{ number_format($totalReserved, 0) }}</p>
                            <p class="text-xs text-orange-600 mt-1">Sudah booking preorder</p>
                        </div>

                        <div class="p-4 bg-green-50 rounded-lg">
                            <p class="text-xs text-green-600 font-medium uppercase mb-1">Available</p>
                            <p class="text-2xl font-bold text-green-900">{{ number_format($available, 0) }}</p>
                            <p class="text-xs text-green-600 mt-1">Bisa dijual regular</p>
                        </div>
                    </div>

                    @if($product->allow_preorder)
                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-yellow-900">Produk Preorder</p>
                                    <p class="text-xs text-yellow-700 mt-1">Stock reserved otomatis ketika customer bayar DP. Hanya stock available yang bisa dijual ke customer regular.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 pt-4 border-t">
                        <p class="text-sm text-gray-600 mb-2">Backlog Preorder (Belum Allocated)</p>
                        <p class="text-lg font-semibold">{{ number_format($preorderBacklog ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Card: Custom Fields -->
            @if($product->custom_field_values && count($product->custom_field_values) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Custom Fields</h3>
                        <div class="grid grid-cols-2 gap-6">
                            @php
                                $categoryFields = $product->category?->custom_fields ?? [];
                                $fieldMap = collect($categoryFields)->keyBy('id');
                            @endphp
                            @foreach($product->custom_field_values as $fieldId => $value)
                                @php
                                    $field = $fieldMap->get($fieldId);
                                @endphp
                                @if($field)
                                    <div>
                                        <p class="text-sm text-gray-600">{{ $field['label'] ?? $fieldId }}</p>
                                        <p class="text-lg">{{ $value ?: '-' }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Card: Description -->
            @if ($product->description)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Deskripsi</h3>
                        <div class="prose max-w-none text-gray-700">
                            {!! $product->description !!}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Card: Tabs (Variasi, Pemesanan & Penjualan) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div data-tabs="product-detail">
                        <div class="flex flex-wrap gap-2">
                            @if($product->has_variants)
                            <button type="button" data-tab-button="variasi" class="px-4 py-2 rounded-full text-sm font-semibold bg-primary text-white">
                                Variasi ({{ $product->variants->count() }})
                            </button>
                            @endif
                            <button type="button" data-tab-button="pemesanan" class="px-4 py-2 rounded-full text-sm font-semibold {{ $product->has_variants ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-primary text-white' }}">
                                Pemesanan
                            </button>
                            <button type="button" data-tab-button="penjualan" class="px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200">
                                Penjualan
                            </button>
                        </div>

                        <div class="mt-4">
                            @if($product->has_variants)
                            <div data-tab-panel="variasi">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <h3 class="text-sm font-semibold text-gray-700">Daftar Variasi</h3>
                                        <a href="{{ route('products.edit', $product) }}#variasi" class="text-sm text-primary hover:underline">
                                            Edit Variasi
                                        </a>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Variasi</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Harga Modal</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Harga Jual</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Stock</th>
                                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                @forelse($product->variants as $variant)
                                                    <tr class="{{ $variant->is_active ? '' : 'bg-gray-50 opacity-60' }}">
                                                        <td class="px-4 py-2 text-sm font-mono">{{ $variant->sku }}</td>
                                                        <td class="px-4 py-2 text-sm">{{ $variant->display_name }}</td>
                                                        <td class="px-4 py-2 text-sm text-right">Rp {{ number_format($variant->cost_price, 0, ',', '.') }}</td>
                                                        <td class="px-4 py-2 text-sm text-right font-semibold text-primary">Rp {{ number_format($variant->selling_price, 0, ',', '.') }}</td>
                                                        <td class="px-4 py-2 text-sm text-right">{{ number_format($variant->total_stock, 2) }}</td>
                                                        <td class="px-4 py-2 text-center">
                                                            <span class="px-2 py-1 {{ $variant->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} rounded-full text-xs">
                                                                {{ $variant->is_active ? 'Aktif' : 'Nonaktif' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="px-4 py-2 text-center text-gray-500 text-sm">Belum ada variasi</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div data-tab-panel="pemesanan" class="{{ $product->has_variants ? 'hidden' : '' }}">
                                <div class="space-y-6">
                                    <div>
                                        <p class="text-sm text-gray-600 mb-2">Stok per Lokasi</p>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @forelse($balances as $balance)
                                                        <tr>
                                                            <td class="px-4 py-2 text-sm">
                                                                {{ ($balance->location->warehouse->code ?? '') . ' ' . ($balance->location->warehouse->name ?? '') . ' - ' . ($balance->location->code ?? '') }}
                                                            </td>
                                                            <td class="px-4 py-2 text-sm text-right">{{ number_format($balance->qty_on_hand, 2) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="2" class="px-4 py-2 text-center text-gray-500 text-sm">Belum ada stok di lokasi manapun.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-600 mb-2">Harga per Supplier</p>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Harga Terakhir</th>
                                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Rata-rata</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Terakhir Beli</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @forelse($supplierPrices as $price)
                                                        <tr>
                                                            <td class="px-4 py-2 text-sm">
                                                                @if($price->supplier)
                                                                    <a href="{{ route('suppliers.show', $price->supplier) }}" class="text-primary hover:underline">
                                                                        {{ $price->supplier->name }}
                                                                    </a>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-2 text-sm text-right">Rp {{ number_format($price->last_cost, 0, ',', '.') }}</td>
                                                            <td class="px-4 py-2 text-sm text-right">Rp {{ number_format($price->avg_cost ?? $price->last_cost, 0, ',', '.') }}</td>
                                                            <td class="px-4 py-2 text-sm">{{ $price->last_purchase_at ? $price->last_purchase_at->format('d M Y H:i') : '-' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="px-4 py-2 text-center text-gray-500 text-sm">Belum ada riwayat harga supplier.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div data-tab-panel="penjualan" class="hidden">
                                <p class="text-sm text-gray-600 mb-2">Histori Penjualan</p>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Pesan</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Kirim</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kurir</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @forelse($salesHistory as $item)
                                                <tr>
                                                    <td class="px-4 py-2 text-sm">
                                                        @if($item->order?->customer)
                                                            <a href="{{ route('customers.show', $item->order->customer) }}" class="text-primary hover:underline">
                                                                {{ $item->order->customer->name }}
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right">{{ number_format($item->quantity, 0) }}</td>
                                                    <td class="px-4 py-2 text-sm">
                                                        {{ $item->order?->created_at ? $item->order->created_at->format('d M Y H:i') : '-' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        {{ $item->order?->shipment?->shipped_at ? $item->order->shipment->shipped_at->format('d M Y H:i') : '-' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        {{ $item->order?->shipment?->courier ?? $item->order?->shipping_courier ?? '-' }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-4 py-2 text-center text-gray-500 text-sm">Belum ada histori penjualan.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Quill Content Styling -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

    <style>
        /* Quill content styling - match editor appearance */
        .prose {
            line-height: 1.6;
        }
        .prose h1, .prose h2, .prose h3 {
            margin-top: 1em;
            margin-bottom: 0.5em;
            font-weight: 600;
        }
        .prose h1 { font-size: 2em; }
        .prose h2 { font-size: 1.5em; }
        .prose h3 { font-size: 1.17em; }
        .prose p {
            margin-bottom: 0.75em;
        }
        .prose ul, .prose ol {
            margin-left: 1.5em;
            margin-bottom: 0.75em;
        }
        .prose ul { list-style-type: disc; }
        .prose ol { list-style-type: decimal; }
        .prose li {
            margin-bottom: 0.25em;
        }
        .prose strong { font-weight: 600; }
        .prose em { font-style: italic; }
        .prose u { text-decoration: underline; }
        .prose s { text-decoration: line-through; }
        .prose a {
            color: #F17B0D;
            text-decoration: underline;
        }
        .prose a:hover {
            color: #D96A0B;
        }

        /* Variant Selector Styles */
        .variant-option-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        /* Button without image - label only */
        .variant-option-btn[data-has-image="false"] {
            padding: 0.5rem 1rem;
        }

        /* Button with image - has thumbnail */
        .variant-option-btn[data-has-image="true"] {
            padding: 0.375rem;
            padding-right: 1rem;
            gap: 0.5rem;
        }

        .variant-option-btn:hover {
            border-color: #F17B0D;
            background: #FFF7ED;
        }

        .variant-option-btn.selected {
            border-color: #F17B0D;
            background: #FFF7ED;
            font-weight: 600;
        }

        .variant-option-btn.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        .variant-option-btn .option-thumbnail {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.375rem;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            position: relative;
        }

        .variant-option-btn .option-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Visual indicator for inherited images */
        .variant-option-btn[data-image-source="inherited"] .option-thumbnail {
            opacity: 0.85;
        }

        .variant-option-btn[data-image-source="featured"] .option-thumbnail {
            opacity: 0.75;
        }

        .variant-option-btn .option-label {
            font-size: 0.875rem;
        }
    </style>

    <script>
        (function () {
            const tabRoot = document.querySelector('[data-tabs="product-detail"]');
            if (!tabRoot) {
                return;
            }

            const buttons = Array.from(tabRoot.querySelectorAll('[data-tab-button]'));
            const panels = Array.from(tabRoot.querySelectorAll('[data-tab-panel]'));

            const setActive = (name) => {
                buttons.forEach((button) => {
                    const isActive = button.dataset.tabButton === name;
                    button.classList.toggle('bg-primary', isActive);
                    button.classList.toggle('text-white', isActive);
                    button.classList.toggle('bg-gray-100', !isActive);
                    button.classList.toggle('text-gray-700', !isActive);
                });

                panels.forEach((panel) => {
                    panel.classList.toggle('hidden', panel.dataset.tabPanel !== name);
                });
            };

            buttons.forEach((button) => {
                button.addEventListener('click', () => {
                    setActive(button.dataset.tabButton);
                });
            });

            setActive(@json($product->has_variants ? 'variasi' : 'pemesanan'));
        })();

        // Variant Selector Logic
        @if($product->has_variants)
        (function() {
            const variants = @json($product->variants);
            const selectedOptions = {};
            const variantInfoPanel = document.getElementById('selected-variant-info');
            const featuredImageUrl = @json($product->featuredMedia?->url ?? null);
            const variantGroups = @json($product->variant_groups);

            // Track which groups have images and map option -> image
            const groupsWithImages = new Set();
            const variantImageMap = new Map(); // Map: "GroupName:OptionValue" -> imageUrl

            // Load variant images and render buttons with thumbnails
            loadVariantImages();

            function loadVariantImages() {
                // Step 1: Identify variants with images and build image map
                variants.forEach(variant => {
                    if (variant.variant_image_id && variant.variant_image) {
                        const imageUrl = variant.variant_image.url || `/storage/${variant.variant_image.path}`;

                        // Find which attribute group has the image (use first match)
                        for (const [groupName, optionValue] of Object.entries(variant.variant_attributes)) {
                            const key = `${groupName}:${optionValue}`;

                            if (!variantImageMap.has(key)) {
                                variantImageMap.set(key, imageUrl);
                                groupsWithImages.add(groupName);
                                break; // Only apply to first matching attribute group
                            }
                        }
                    }
                });

                // Step 2: Add thumbnails to buttons that have variant-specific images
                variantImageMap.forEach((imageUrl, key) => {
                    const [groupName, optionValue] = key.split(':');
                    const btn = document.querySelector(
                        `.variant-option-btn[data-group="${groupName}"][data-option="${optionValue}"]`
                    );

                    if (btn && btn.dataset.hasImage === 'false') {
                        addThumbnailToButton(btn, imageUrl, optionValue, 'variant');
                    }
                });

                // Step 3: Add featured image as fallback for ALL buttons without images
                if (featuredImageUrl) {
                    document.querySelectorAll('.variant-option-btn[data-has-image="false"]').forEach(btn => {
                        addThumbnailToButton(btn, featuredImageUrl, btn.dataset.option, 'featured');
                    });
                }
            }

            function addThumbnailToButton(btn, imageUrl, altText, source = 'variant') {
                btn.dataset.hasImage = 'true';
                btn.dataset.imageSource = source; // 'variant', 'featured', or 'inherited'

                // Create thumbnail
                const thumbnail = document.createElement('div');
                thumbnail.className = 'option-thumbnail';
                const img = document.createElement('img');
                img.src = imageUrl;
                img.alt = altText;
                thumbnail.appendChild(img);

                // Insert thumbnail before label
                const label = btn.querySelector('.option-label');
                btn.insertBefore(thumbnail, label);
            }

            function removeThumbnailFromButton(btn) {
                const thumbnail = btn.querySelector('.option-thumbnail');
                if (thumbnail) {
                    thumbnail.remove();
                    btn.dataset.hasImage = 'false';
                    btn.dataset.imageSource = '';
                }
            }

            function updateInheritedImages() {
                // Find selected variants with images
                let selectedImageUrl = null;

                for (const [groupName, optionValue] of Object.entries(selectedOptions)) {
                    const key = `${groupName}:${optionValue}`;
                    if (variantImageMap.has(key)) {
                        selectedImageUrl = variantImageMap.get(key);
                        break; // Use first selected variant with image
                    }
                }

                // If no variant image selected, use featured image
                if (!selectedImageUrl && featuredImageUrl) {
                    selectedImageUrl = featuredImageUrl;
                }

                // Update buttons in groups without variant images
                variantGroups.forEach(group => {
                    const groupName = group.name;

                    // Skip groups that have their own variant images
                    if (groupsWithImages.has(groupName)) {
                        return;
                    }

                    // Update all buttons in this group
                    group.options.forEach(option => {
                        const btn = document.querySelector(
                            `.variant-option-btn[data-group="${groupName}"][data-option="${option}"]`
                        );

                        if (!btn) return;

                        // Remove existing inherited thumbnail
                        if (btn.dataset.imageSource === 'inherited') {
                            removeThumbnailFromButton(btn);
                        }

                        // Add inherited image if we have one
                        if (selectedImageUrl && btn.dataset.imageSource !== 'featured') {
                            addThumbnailToButton(btn, selectedImageUrl, option, 'inherited');
                        }
                    });
                });
            }

            // Handle option selection
            document.querySelectorAll('.variant-option-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const group = this.dataset.group;
                    const option = this.dataset.option;

                    // Deselect others in the same group
                    document.querySelectorAll(`.variant-option-btn[data-group="${group}"]`).forEach(b => {
                        b.classList.remove('selected');
                    });

                    // Select this option
                    this.classList.add('selected');
                    selectedOptions[group] = option;

                    // Update inherited images for other groups
                    updateInheritedImages();

                    // Find matching variant
                    updateVariantInfo();
                });
            });

            function updateVariantInfo() {
                // Find variant that matches all selected options
                const matchedVariant = variants.find(variant => {
                    return Object.entries(selectedOptions).every(([group, option]) => {
                        return variant.variant_attributes[group] === option;
                    });
                });

                if (matchedVariant) {
                    // Show info panel
                    variantInfoPanel.classList.remove('hidden');

                    // Update info
                    document.getElementById('variant-sku').textContent = matchedVariant.sku;
                    document.getElementById('variant-price').textContent = `Rp ${formatNumber(matchedVariant.selling_price)}`;
                    document.getElementById('variant-stock').textContent = formatNumber(matchedVariant.total_stock || 0);

                    const statusEl = document.getElementById('variant-status');
                    statusEl.innerHTML = matchedVariant.is_active
                        ? '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Aktif</span>'
                        : '<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Nonaktif</span>';
                } else {
                    // Hide info panel if no match
                    variantInfoPanel.classList.add('hidden');
                }
            }

            function formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(num);
            }
        })();
        @endif
    </script>
</x-app-layout>
