<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Transfer Stok</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                <form action="{{ route('warehouse.transfer.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Produk</label>
                        <input
                            type="text"
                            id="product-search"
                            autocomplete="off"
                            placeholder="Ketik untuk mencari produk..."
                            class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                        >
                        <input type="hidden" id="product-select" name="product_id" value="{{ old('product_id') }}">

                        <!-- Dropdown list -->
                        <div id="product-dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <!-- Products will be populated here -->
                        </div>

                        @error('product_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Variant Selector (conditional) -->
                    <div id="variant-selector-container" class="hidden relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Variasi</label>
                        <input
                            type="text"
                            id="variant-search"
                            autocomplete="off"
                            placeholder="Ketik untuk mencari variasi..."
                            class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                        >
                        <input type="hidden" id="variant-select" name="product_variant_id" value="{{ old('product_variant_id') }}">

                        <!-- Dropdown list -->
                        <div id="variant-dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <!-- Variants will be populated here -->
                        </div>

                        @error('product_variant_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Asal</label>
                            <select id="from-location-select" name="from_location_id" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                <option value="">Pilih produk terlebih dahulu</option>
                            </select>
                            @error('from_location_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Tujuan</label>
                            <select name="to_location_id" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                <option value="">Pilih lokasi tujuan</option>
                                @foreach ($locations as $loc)
                                    @php
                                        $warehouseLocationCount = $locations->where('warehouse_id', $loc->warehouse_id)->count();
                                        $displayName = $warehouseLocationCount === 1
                                            ? ($loc->warehouse->name ?? $loc->warehouse->code ?? '')
                                            : (($loc->warehouse->code ?? '') . ' - ' . $loc->code);
                                    @endphp
                                    <option value="{{ $loc->id }}" @selected(old('to_location_id') == $loc->id)>{{ $displayName }}</option>
                                @endforeach
                            </select>
                            @error('to_location_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Qty</label>
                            <input id="qty-input" type="number" step="0.01" min="0" name="qty" value="{{ old('qty') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            <p id="stock-info" class="text-xs text-gray-500 mt-1 hidden">Stok tersedia: <span id="stock-available">0</span></p>
                            @error('qty') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2 pt-4">
                        <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover">Pindahkan</button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/autocomplete.js') }}"></script>
    <script>
        const variantContainer = document.getElementById('variant-selector-container');
        const variantSelect = document.getElementById('variant-select');
        const fromLocationSelect = document.getElementById('from-location-select');
        const stockInfo = document.getElementById('stock-info');
        const stockAvailable = document.getElementById('stock-available');
        const qtyInput = document.getElementById('qty-input');

        // Products data from server
        const allProducts = @json($products);

        // All locations data from server
        const allLocations = @json($locations);

        // Balance data from server (grouped by location_id)
        const balanceByLocation = @json($balanceByLocation);

        // Initialize Product Autocomplete
        const productAutocomplete = new Autocomplete({
            inputId: 'product-search',
            hiddenInputId: 'product-select',
            dropdownId: 'product-dropdown',
            data: allProducts,
            searchFields: ['name', 'sku'],
            displayTemplate: (product) => {
                let html = `<div class="font-medium">${product.name}</div>`;
                if (product.sku) {
                    html += `<div class="text-xs text-gray-500">SKU: ${product.sku}</div>`;
                }
                return html;
            },
            maxItems: 10,
            onSelect: async (product) => {
                await handleProductSelect(product);
            }
        });

        // Initialize Variant Autocomplete (will be populated when product is selected)
        let variantAutocomplete = null;

        // Handle product selection
        async function handleProductSelect(product) {
            const hasVariants = product.has_variants;

            if (hasVariants) {
                // Load variants via API (only variants with stock)
                try {
                    const response = await fetch(`/products/${product.id}/variants?has_stock=1`);
                    const data = await response.json();

                    // Map variants to autocomplete format
                    const variantData = data.variants.map(variant => ({
                        id: variant.id,
                        name: Object.values(variant.variant_attributes).join(' / '),
                        sku: variant.sku,
                        variant_attributes: variant.variant_attributes
                    }));

                    // Initialize or update variant autocomplete
                    if (variantAutocomplete) {
                        variantAutocomplete.updateData(variantData);
                        variantAutocomplete.reset();
                    } else {
                        variantAutocomplete = new Autocomplete({
                            inputId: 'variant-search',
                            hiddenInputId: 'variant-select',
                            dropdownId: 'variant-dropdown',
                            data: variantData,
                            searchFields: ['name', 'sku'],
                            displayTemplate: (variant) => {
                                let html = `<div class="font-medium">${variant.name}</div>`;
                                if (variant.sku) {
                                    html += `<div class="text-xs text-gray-500">SKU: ${variant.sku}</div>`;
                                }
                                return html;
                            },
                            maxItems: 10,
                            onSelect: (variant) => {
                                updateFromLocationOptions();
                            }
                        });
                    }

                    // Show variant selector
                    variantContainer.classList.remove('hidden');

                    // Reset from location (need to select variant first)
                    fromLocationSelect.innerHTML = '<option value="">Pilih variasi terlebih dahulu</option>';
                } catch (error) {
                    console.error('Failed to load variants:', error);
                    alert('Gagal memuat variasi produk');
                }
            } else {
                // Hide variant selector for simple products
                variantContainer.classList.add('hidden');
                if (variantAutocomplete) {
                    variantAutocomplete.reset();
                }

                // Update from location options for simple product
                updateFromLocationOptions();
            }
        }

        // Update from_location dropdown based on selected product/variant
        function updateFromLocationOptions() {
            const productId = productAutocomplete.hiddenInput.value;
            const variantId = variantAutocomplete ? variantAutocomplete.hiddenInput.value : null;

            if (!productId) {
                fromLocationSelect.innerHTML = '<option value="">Pilih produk terlebih dahulu</option>';
                return;
            }

            // For products with variants, wait until variant is selected
            const selectedProduct = allProducts.find(p => p.id == productId);
            if (selectedProduct && selectedProduct.has_variants && !variantId) {
                fromLocationSelect.innerHTML = '<option value="">Pilih variasi terlebih dahulu</option>';
                return;
            }

            // Find all locations that have stock for this product/variant
            const availableLocations = [];

            Object.keys(balanceByLocation).forEach(locationId => {
                const balances = balanceByLocation[locationId];
                const balance = balances.find(b => {
                    return b.product_id == productId &&
                           (variantId ? b.product_variant_id == variantId : !b.product_variant_id);
                });

                if (balance && balance.qty_on_hand > 0) {
                    const location = allLocations.find(loc => loc.id == locationId);
                    if (location) {
                        // Count how many locations this warehouse has
                        const warehouseLocations = allLocations.filter(l => l.warehouse_id === location.warehouse_id);

                        // If only 1 location, show warehouse name only
                        // If multiple locations, show warehouse code + location code
                        let displayName;
                        if (warehouseLocations.length === 1) {
                            displayName = location.warehouse.name || location.warehouse.code || '';
                        } else {
                            displayName = `${location.warehouse.code || ''} - ${location.code}`;
                        }

                        availableLocations.push({
                            id: location.id,
                            name: displayName,
                            qty: parseFloat(balance.qty_on_hand).toFixed(2)
                        });
                    }
                }
            });

            // Populate dropdown
            if (availableLocations.length === 0) {
                fromLocationSelect.innerHTML = '<option value="">Tidak ada stok tersedia</option>';
            } else {
                let options = '<option value="">Pilih lokasi asal</option>';
                availableLocations.forEach(loc => {
                    options += `<option value="${loc.id}">${loc.name} (Stok: ${loc.qty})</option>`;
                });
                fromLocationSelect.innerHTML = options;
            }
        }

        function updateStockInfo() {
            const productId = productAutocomplete.hiddenInput.value;
            const variantId = variantAutocomplete ? variantAutocomplete.hiddenInput.value : null;
            const locationId = fromLocationSelect.value;

            if (!productId || !locationId) {
                stockInfo.classList.add('hidden');
                return;
            }

            // Find balance for this product/variant at this location
            const balances = balanceByLocation[locationId] || [];
            const balance = balances.find(b => {
                return b.product_id == productId &&
                       (variantId ? b.product_variant_id == variantId : !b.product_variant_id);
            });

            if (balance) {
                stockAvailable.textContent = parseFloat(balance.qty_on_hand).toFixed(2);
                stockInfo.classList.remove('hidden');
                qtyInput.max = balance.qty_on_hand;
            } else {
                stockInfo.classList.add('hidden');
                qtyInput.removeAttribute('max');
            }
        }

        // Update stock info when from_location changes
        fromLocationSelect.addEventListener('change', updateStockInfo);
    </script>
</x-app-layout>
