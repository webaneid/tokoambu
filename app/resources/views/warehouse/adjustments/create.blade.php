<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Pengeluaran Stok (Adjustment)</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($message = Session::get('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ $message }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                <form action="{{ route('warehouse.adjustments.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Product Autocomplete -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Produk <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="product-search"
                            autocomplete="off"
                            placeholder="Ketik untuk mencari produk..."
                            class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        <input type="hidden" id="product-select" name="product_id" value="{{ old('product_id') }}">
                        <div id="product-dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        @error('product_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Variant Autocomplete (conditional) -->
                    <div id="variant-container" class="hidden relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Variasi <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="variant-search"
                            autocomplete="off"
                            placeholder="Ketik untuk mencari variasi..."
                            class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        <input type="hidden" id="variant-select" name="product_variant_id" value="{{ old('product_variant_id') }}">
                        <div id="variant-dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        @error('product_variant_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Asal <span class="text-red-500">*</span></label>
                            <select id="location-select" name="location_id" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                <option value="">Pilih produk terlebih dahulu</option>
                            </select>
                            @error('location_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Qty Keluar <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0" name="qty" value="{{ old('qty') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            @error('qty') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alasan <span class="text-red-500">*</span></label>
                            <select name="reason" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                <option value="">Pilih alasan</option>
                                @foreach ($reasons as $reason)
                                    <option value="{{ $reason }}" @selected(old('reason') == $reason)>{{ ucfirst(str_replace('_', ' ', $reason)) }}</option>
                                @endforeach
                            </select>
                            @error('reason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Opsional, wajib jika alasan 'lainnya'">
                            @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2 pt-4">
                        <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover">Simpan</button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/autocomplete.js') }}"></script>
    <script>
        const allProducts = @json($products);
        const allLocations = @json($locations);
        const balanceByLocation = @json($balanceByLocation);

        const variantContainer = document.getElementById('variant-container');
        const locationSelect = document.getElementById('location-select');

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
                                updateLocationOptions();
                            }
                        });
                    }

                    // Show variant selector
                    variantContainer.classList.remove('hidden');

                    // Reset location (need to select variant first)
                    locationSelect.innerHTML = '<option value="">Pilih variasi terlebih dahulu</option>';
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

                // Update location options for simple product
                updateLocationOptions();
            }
        }

        // Update location dropdown based on selected product/variant
        function updateLocationOptions() {
            const productId = productAutocomplete.hiddenInput.value;
            const variantId = variantAutocomplete ? variantAutocomplete.hiddenInput.value : null;

            if (!productId) {
                locationSelect.innerHTML = '<option value="">Pilih produk terlebih dahulu</option>';
                return;
            }

            // For products with variants, wait until variant is selected
            const selectedProduct = allProducts.find(p => p.id == productId);
            if (selectedProduct && selectedProduct.has_variants && !variantId) {
                locationSelect.innerHTML = '<option value="">Pilih variasi terlebih dahulu</option>';
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
                        const warehouseLocations = allLocations.filter(l => l.warehouse_id === location.warehouse_id);

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
                locationSelect.innerHTML = '<option value="">Tidak ada stok tersedia</option>';
            } else {
                let options = '<option value="">Pilih lokasi asal</option>';
                availableLocations.forEach(loc => {
                    options += `<option value="${loc.id}">${loc.name} (Stok: ${loc.qty})</option>`;
                });
                locationSelect.innerHTML = options;
            }
        }
    </script>
</x-app-layout>
