<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Buat Pembelian</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                <form id="purchase-form" action="{{ route('purchases.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">Supplier</label>
                                <button type="button" id="open-supplier-modal" class="flex items-center text-primary hover:text-primary-hover text-sm">
                                    <span class="inline-flex w-6 h-6 items-center justify-center border border-primary rounded-full mr-1">+</span>
                                    Tambah Supplier
                                </button>
                            </div>
                            <select id="supplier-select" name="supplier_id" required class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                <option value="">Pilih Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" required class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                <option value="draft">Draft</option>
                                <option value="ordered" selected>Ordered</option>
                                <option value="received">Received</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Purchase Items -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Item Pembelian</h3>
                        <div id="items-container" class="space-y-4 mb-4">
                            <div class="item-row p-4 border border-gray-200 rounded-lg bg-gray-50">
                <!-- Product Selector (always shown) -->
                <div class="mb-3 relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                    <input type="text" id="product-search-0" autocomplete="off" placeholder="Ketik untuk mencari produk..." class="product-search w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary bg-white" required>
                    <input type="hidden" name="items[0][product_id]" id="product-id-0" class="product-select" value="" data-has-variants="false" data-price="">
                    <div id="product-dropdown-0" class="product-dropdown hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                </div>

                <!-- Bottom Row: Variant + Qty + Price + Remove -->
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-12">
                    <!-- Variant Selector (conditional, shown when product has variants) -->
                    <div class="variant-selector-container col-span-2 hidden relative sm:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Variasi</label>
                        <input type="text" id="variant-search-0" autocomplete="off" placeholder="Ketik untuk mencari variasi..." class="variant-search w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary bg-white min-w-0">
                        <input type="hidden" name="items[0][product_variant_id]" id="variant-id-0" class="variant-select" value="" data-price="">
                        <div id="variant-dropdown-0" class="variant-dropdown hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                    </div>

                    <!-- Qty -->
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1 sm:text-sm">Qty</label>
                        <input type="number" name="items[0][quantity]" class="quantity w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary bg-white min-w-0" placeholder="Qty" min="1" value="1" required>
                    </div>

                    <!-- Price -->
                    <div class="col-span-1 sm:col-span-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1 sm:text-sm">Harga</label>
                        <input type="number" step="0.01" name="items[0][unit_price]" class="price w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary bg-white min-w-0" placeholder="Harga" required>
                    </div>

                    <!-- Remove Button -->
                    <div class="col-span-2 sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1 sm:text-sm">&nbsp;</label>
                        <button type="button" class="remove-item w-full h-10 bg-red-500 text-white px-3 rounded-lg hover:bg-red-600">Hapus</button>
                    </div>
                </div>

                <!-- Duplicate Warning (conditional) -->
                <div class="duplicate-warning hidden mt-3 p-3 bg-red-50 border border-red-300 rounded-lg">
                    <div class="flex items-start gap-2">
                        <span class="text-red-600 font-bold">⚠️</span>
                        <div class="flex-1">
                            <p class="text-sm text-red-700 font-medium duplicate-message"></p>
                            <button type="button" class="remove-duplicate-btn mt-2 text-xs text-red-600 hover:text-red-800 underline">
                                Hapus baris ini
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add Another Variant Button (conditional, shown when variant is selected) -->
                <div class="add-variant-btn-container hidden mt-3">
                    <button type="button" class="add-another-variant h-10 px-4 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm flex items-center gap-2">
                        <span>+</span> Tambah Variasi Lain
                    </button>
                </div>
            </div>
        </div>
                        <div class="flex flex-wrap gap-3">
                            <button type="button" id="add-item" class="h-10 bg-blue text-white px-4 rounded-lg hover:bg-blue-light">+ Tambah Item</button>
                            <button type="button" id="open-product-modal" class="h-10 bg-primary text-white px-4 rounded-lg hover:bg-primary-hover">+ Tambah Produk Baru</button>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <div class="text-right">
                            <p class="text-gray-600 mb-2">Total:</p>
                            <p class="text-3xl font-bold text-primary">Rp <span id="total-amount">0</span></p>
                            <input type="hidden" name="total_amount" id="total-amount-input">
                        </div>
                    </div>

                    <div class="flex gap-3 pt-6">
                        <button type="submit" class="flex-1 h-10 bg-primary text-white px-4 rounded-lg hover:bg-primary-hover transition-colors">
                            Simpan Pembelian
                        </button>
                        <a href="{{ route('purchases.index') }}" class="flex-1 h-10 bg-gray-300 text-gray-700 px-4 rounded-lg hover:bg-gray-400 text-center flex items-center justify-center">
                            Batal
                        </a>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/media-picker.js') }}"></script>
    <script src="{{ asset('js/media-gallery.js') }}"></script>
    <script src="{{ asset('js/autocomplete.js') }}"></script>

    <script>
        const itemsContainer = document.getElementById('items-container');
        const addItemBtn = document.getElementById('add-item');
        const openProductBtn = document.getElementById('open-product-modal');
        const supplierSelect = document.getElementById('supplier-select');
        const totalAmountSpan = document.getElementById('total-amount');
        const totalAmountInput = document.getElementById('total-amount-input');
        let itemCount = 1;
        const priceMap = @json($priceMap ?? []);

        function getSupplierPrice(productId) {
            const supplierId = supplierSelect.value;
            if (!supplierId || !productId) return null;
            return priceMap[supplierId]?.[productId] ?? null;
        }

        function createItemRow() {
            const div = document.createElement('div');
            div.className = 'item-row p-4 border border-gray-200 rounded-lg bg-gray-50';
            const index = itemCount++;
            div.innerHTML = `
                <!-- Product Selector (always shown) -->
                <div class="mb-3 relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                    <input type="text" id="product-search-${index}" autocomplete="off" placeholder="Ketik untuk mencari produk..." class="product-search w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary bg-white" required>
                    <input type="hidden" name="items[${index}][product_id]" id="product-id-${index}" class="product-select" value="" data-has-variants="false" data-price="">
                    <div id="product-dropdown-${index}" class="product-dropdown hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                </div>

                <!-- Bottom Row: Variant + Qty + Price + Remove -->
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-12">
                    <!-- Variant Selector (conditional, shown when product has variants) -->
                    <div class="variant-selector-container col-span-2 hidden relative sm:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Variasi</label>
                        <input type="text" id="variant-search-${index}" autocomplete="off" placeholder="Ketik untuk mencari variasi..." class="variant-search w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary bg-white min-w-0">
                        <input type="hidden" name="items[${index}][product_variant_id]" id="variant-id-${index}" class="variant-select" value="" data-price="">
                        <div id="variant-dropdown-${index}" class="variant-dropdown hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                    </div>

                    <!-- Qty -->
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1 sm:text-sm">Qty</label>
                        <input type="number" name="items[${index}][quantity]" class="quantity w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary bg-white min-w-0" placeholder="Qty" min="1" value="1" required>
                    </div>

                    <!-- Price -->
                    <div class="col-span-1 sm:col-span-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1 sm:text-sm">Harga</label>
                        <input type="number" step="0.01" name="items[${index}][unit_price]" class="price w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary bg-white min-w-0" placeholder="Harga" required>
                    </div>

                    <!-- Remove Button -->
                    <div class="col-span-2 sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1 sm:text-sm">&nbsp;</label>
                        <button type="button" class="remove-item w-full h-10 bg-red-500 text-white px-3 rounded-lg hover:bg-red-600">Hapus</button>
                    </div>
                </div>

                <!-- Duplicate Warning (conditional) -->
                <div class="duplicate-warning hidden mt-3 p-3 bg-red-50 border border-red-300 rounded-lg">
                    <div class="flex items-start gap-2">
                        <span class="text-red-600 font-bold">⚠️</span>
                        <div class="flex-1">
                            <p class="text-sm text-red-700 font-medium duplicate-message"></p>
                            <button type="button" class="remove-duplicate-btn mt-2 text-xs text-red-600 hover:text-red-800 underline">
                                Hapus baris ini
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add Another Variant Button (conditional, shown when variant is selected) -->
                <div class="add-variant-btn-container hidden mt-3">
                    <button type="button" class="add-another-variant h-10 px-4 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm flex items-center gap-2">
                        <span>+</span> Tambah Variasi Lain
                    </button>
                </div>
            `;
            return div;
        }

        function updateTotal() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.quantity').value) || 0;
                const price = parseFloat(row.querySelector('.price').value) || 0;
                total += qty * price;
            });
            totalAmountSpan.textContent = total.toLocaleString('id-ID');
            totalAmountInput.value = total;
        }

        function getItemKey(row) {
            const productSelect = row.querySelector('.product-select');
            const variantSelect = row.querySelector('.variant-select');
            const variantContainer = row.querySelector('.variant-selector-container');

            if (!productSelect || !productSelect.value) {
                return null;
            }

            const productId = productSelect.value;
            const hasVariants = productSelect.dataset.hasVariants === 'true';
            const isVariantContainerVisible = variantContainer && !variantContainer.classList.contains('hidden');

            let variantId = 'null';

            // Only include variant ID if:
            // 1. Product has variants AND
            // 2. Variant container is visible AND
            // 3. Variant select exists AND
            // 4. Variant is actually selected (value is not empty or "undefined")
            if (hasVariants && isVariantContainerVisible && variantSelect) {
                const actualValue = variantSelect.value;

                if (actualValue && actualValue !== '' && actualValue !== 'undefined') {
                    variantId = actualValue;
                }
            }

            return `${productId}_${variantId}`;
        }

        function checkDuplicates(currentRow) {
            const currentKey = getItemKey(currentRow);

            if (!currentKey) {
                return null;
            }

            // Check if this row has a variant product but no variant selected yet
            const productSelect = currentRow.querySelector('.product-select');
            const variantSelect = currentRow.querySelector('.variant-select');
            const variantContainer = currentRow.querySelector('.variant-selector-container');
            const hasVariants = productSelect?.dataset.hasVariants === 'true';
            const isVariantContainerVisible = variantContainer && !variantContainer.classList.contains('hidden');

            // If product has variants but variant is not selected, don't validate yet
            const variantValue = variantSelect ? variantSelect.value : '';
            const isVariantSelected = variantValue && variantValue !== '' && variantValue !== 'undefined';

            if (hasVariants && isVariantContainerVisible && !isVariantSelected) {
                return null;
            }

            const allRows = Array.from(document.querySelectorAll('.item-row'));
            let duplicateRowIndex = null;

            for (let i = 0; i < allRows.length; i++) {
                const row = allRows[i];

                // Skip current row
                if (row === currentRow) continue;

                const rowKey = getItemKey(row);

                if (rowKey === currentKey) {
                    duplicateRowIndex = i + 1; // Human-readable index (1-based)
                    break;
                }
            }

            return duplicateRowIndex;
        }

        function showDuplicateWarning(row, duplicateIndex) {
            const warningDiv = row.querySelector('.duplicate-warning');
            const messageEl = row.querySelector('.duplicate-message');
            const productSelect = row.querySelector('.product-select');
            const variantContainer = row.querySelector('.variant-selector-container');

            if (warningDiv && messageEl) {
                const productName = productSelect.options[productSelect.selectedIndex]?.text || 'Produk';
                messageEl.textContent = `Produk/variant ini sudah ada di Item #${duplicateIndex}. Silakan hapus baris ini atau ganti produk/variant.`;
                warningDiv.classList.remove('hidden');

                // Add red border to highlight
                row.classList.add('border-red-500', 'bg-red-50');
                row.classList.remove('border-gray-200', 'bg-gray-50');
            }
        }

        function hideDuplicateWarning(row) {
            const warningDiv = row.querySelector('.duplicate-warning');

            if (warningDiv) {
                warningDiv.classList.add('hidden');

                // Remove red border
                row.classList.remove('border-red-500', 'bg-red-50');
                row.classList.add('border-gray-200', 'bg-gray-50');
            }
        }

        function validateAllRows() {
            let hasDuplicate = false;

            document.querySelectorAll('.item-row').forEach(row => {
                const duplicateIndex = checkDuplicates(row);

                if (duplicateIndex) {
                    showDuplicateWarning(row, duplicateIndex);
                    hasDuplicate = true;
                } else {
                    hideDuplicateWarning(row);
                }
            });

            return !hasDuplicate;
        }

        async function loadVariants(productId, row) {
            const variantContainer = row.querySelector('.variant-selector-container');
            const variantSelect = row.querySelector('.variant-select');
            const priceInput = row.querySelector('.price');

            // Clear existing options
            variantSelect.innerHTML = '<option value="">Memuat variasi...</option>';

            try {
                const response = await fetch(`/products/${productId}/variants`);
                const data = await response.json();

                variantSelect.innerHTML = '<option value="">Pilih Variasi...</option>';

                data.variants.forEach(variant => {
                    const displayName = Object.values(variant.variant_attributes).join(' / ');
                    const option = document.createElement('option');
                    option.value = variant.id;
                    option.textContent = `${displayName} - ${variant.sku}`;
                    option.dataset.price = variant.cost_price;
                    variantSelect.appendChild(option);
                });

                // Show variant selector
                variantContainer.classList.remove('hidden');
            } catch (error) {
                console.error('Failed to load variants:', error);
                variantSelect.innerHTML = '<option value="">Gagal memuat variasi</option>';
            }
        }

        function attachItemListeners(row) {
            const priceInput = row.querySelector('.price');
            const quantityInput = row.querySelector('.quantity');
            const removeBtn = row.querySelector('.remove-item');
            const addVariantBtnContainer = row.querySelector('.add-variant-btn-container');
            const addVariantBtn = row.querySelector('.add-another-variant');

            // Note: Product and variant selection is now handled by autocomplete onSelect callbacks
            // No need for change event listeners on select elements

            // Handle "Add Another Variant" button click
            if (addVariantBtn) {
                addVariantBtn.addEventListener('click', async () => {
                    const currentProductIdInput = row.querySelector('.product-select');
                    const currentProductSearch = row.querySelector('.product-search');
                    const currentProductId = currentProductIdInput ? currentProductIdInput.value : null;
                    const currentProductName = currentProductSearch ? currentProductSearch.value : '';

                    if (!currentProductId) return;

                    // Create new row
                    const newRow = createItemRow();
                    row.parentNode.insertBefore(newRow, row.nextSibling);

                    // Attach listeners first
                    attachItemListeners(newRow);

                    // Initialize autocomplete for new row
                    const newRowIndex = itemCount - 1;
                    initializeProductAutocomplete(newRow, newRowIndex);

                    // Set product to same as current row
                    const newProductIdInput = newRow.querySelector('.product-select');
                    const newProductSearch = newRow.querySelector('.product-search');

                    if (newProductIdInput && newProductSearch) {
                        newProductIdInput.value = currentProductId;
                        newProductSearch.value = currentProductName;
                        newProductIdInput.dataset.hasVariants = 'true';

                        // Load variants for the new row
                        await loadVariantsForAutocomplete(currentProductId, newRow, newRowIndex);
                    }

                    // Scroll to new row
                    newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            }

            quantityInput.addEventListener('change', updateTotal);
            priceInput.addEventListener('input', updateTotal);
            removeBtn.addEventListener('click', () => {
                row.remove();
                updateTotal();
                validateAllRows(); // Re-check duplicates after removal
            });

            // Handle "Hapus baris ini" button in duplicate warning
            const removeDuplicateBtn = row.querySelector('.remove-duplicate-btn');
            if (removeDuplicateBtn) {
                removeDuplicateBtn.addEventListener('click', () => {
                    row.remove();
                    updateTotal();
                    validateAllRows();
                });
            }
        }

        addItemBtn.addEventListener('click', () => {
            const newRow = createItemRow();
            itemsContainer.appendChild(newRow);
            attachItemListeners(newRow);

            // Initialize autocomplete for the new row
            const rowIndex = itemCount - 1;
            initializeProductAutocomplete(newRow, rowIndex);
        });

        document.querySelectorAll('.item-row').forEach(attachItemListeners);

        // Initialize autocomplete for initial row
        const productsData = {!! json_encode($products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'cost_price' => $product->cost_price,
                'has_variants' => $product->has_variants
            ];
        })->values()) !!};

        function initializeProductAutocomplete(row, index) {
            const productAutocomplete = new Autocomplete({
                inputId: `product-search-${index}`,
                hiddenInputId: `product-id-${index}`,
                dropdownId: `product-dropdown-${index}`,
                data: productsData,
                searchFields: ['name'],
                displayTemplate: (item) => `<div class="p-2 hover:bg-gray-100 cursor-pointer">${item.name}</div>`,
                maxItems: 10,
                onSelect: async (item) => {
                    const productIdInput = document.getElementById(`product-id-${index}`);

                    // Update data attributes
                    productIdInput.dataset.hasVariants = item.has_variants ? 'true' : 'false';
                    productIdInput.dataset.price = item.cost_price;

                    const priceInput = row.querySelector('.price');
                    const variantContainer = row.querySelector('.variant-selector-container');
                    const variantSearch = row.querySelector('.variant-search');
                    const variantIdInput = row.querySelector('.variant-select');

                    if (item.has_variants) {
                        // Product has variants - load variant autocomplete
                        await loadVariantsForAutocomplete(item.id, row, index);
                        // Clear price until variant is selected
                        priceInput.value = '';
                        priceInput.setAttribute('readonly', true);
                    } else {
                        // Simple product - hide variant selector and set price
                        variantContainer.classList.add('hidden');
                        if (variantSearch) variantSearch.value = '';
                        if (variantIdInput) variantIdInput.value = '';
                        priceInput.removeAttribute('readonly');

                        const supplierPrice = getSupplierPrice(item.id);
                        priceInput.value = supplierPrice ?? item.cost_price;

                        // Check duplicate for simple product
                        setTimeout(() => validateAllRows(), 50);
                    }
                    updateTotal();
                }
            });
        }

        async function loadVariantsForAutocomplete(productId, row, index) {
            const variantContainer = row.querySelector('.variant-selector-container');
            const variantSearch = document.getElementById(`variant-search-${index}`);
            const priceInput = row.querySelector('.price');

            if (!variantSearch) return;

            // Show loading state
            variantSearch.value = 'Memuat variasi...';
            variantSearch.setAttribute('readonly', true);

            try {
                const response = await fetch(`/products/${productId}/variants`);
                const data = await response.json();

                const variantsData = data.variants.map(variant => ({
                    id: variant.id,
                    name: `${Object.values(variant.variant_attributes).join(' / ')} - ${variant.sku}`,
                    price: variant.cost_price,
                    sku: variant.sku,
                    variant_attributes: variant.variant_attributes
                }));

                // Clear loading state
                variantSearch.value = '';
                variantSearch.removeAttribute('readonly');

                // Initialize variant autocomplete
                const variantAutocomplete = new Autocomplete({
                    inputId: `variant-search-${index}`,
                    hiddenInputId: `variant-id-${index}`,
                    dropdownId: `variant-dropdown-${index}`,
                    data: variantsData,
                    searchFields: ['name', 'sku'],
                    displayTemplate: (item) => `<div class="p-2 hover:bg-gray-100 cursor-pointer">${item.name}</div>`,
                    maxItems: 10,
                    onSelect: (item) => {
                        const variantIdInput = document.getElementById(`variant-id-${index}`);
                        variantIdInput.dataset.price = item.price;
                        priceInput.value = item.price;
                        priceInput.removeAttribute('readonly');

                        // Show "Add Another Variant" button
                        const addVariantBtnContainer = row.querySelector('.add-variant-btn-container');
                        if (addVariantBtnContainer) {
                            addVariantBtnContainer.classList.remove('hidden');
                        }

                        // Check duplicate for variant product
                        setTimeout(() => validateAllRows(), 50);
                        updateTotal();
                    }
                });

                // Show variant selector
                variantContainer.classList.remove('hidden');
            } catch (error) {
                console.error('Failed to load variants:', error);
                variantSearch.value = '';
                variantSearch.removeAttribute('readonly');
                alert('Gagal memuat variasi produk');
            }
        }

        // Initialize autocomplete for the first row (index 0)
        initializeProductAutocomplete(document.querySelector('.item-row'), 0);

        supplierSelect.addEventListener('change', () => {
            document.querySelectorAll('.item-row').forEach(row => {
                const select = row.querySelector('.product-select');
                const priceInput = row.querySelector('.price');
                const supplierPrice = getSupplierPrice(select.value);
                if (supplierPrice !== null && supplierPrice !== undefined && select.value) {
                    priceInput.value = supplierPrice;
                }
            });
            updateTotal();
        });

        // Form submission validation
        const purchaseForm = document.getElementById('purchase-form');
        if (purchaseForm) {
            purchaseForm.addEventListener('submit', (e) => {
                // First, check if all variant products have variants selected
                let hasIncompleteVariant = false;
                let incompleteRow = null;

                document.querySelectorAll('.item-row').forEach(row => {
                    const productSelect = row.querySelector('.product-select');
                    const variantSelect = row.querySelector('.variant-select');
                    const variantContainer = row.querySelector('.variant-selector-container');

                    if (productSelect && productSelect.value) {
                        const hasVariants = productSelect.dataset.hasVariants === 'true';
                        const isVariantContainerVisible = variantContainer && !variantContainer.classList.contains('hidden');

                        if (hasVariants && isVariantContainerVisible) {
                            const variantValue = variantSelect ? variantSelect.value : '';

                            // Check if variant is not selected or has invalid value
                            if (!variantValue || variantValue === '' || variantValue === 'undefined') {
                                hasIncompleteVariant = true;
                                incompleteRow = row;
                            }
                        }
                    }
                });

                if (hasIncompleteVariant) {
                    e.preventDefault();
                    alert('❌ Produk dengan variasi harus memilih variasi! Silakan pilih variasi untuk semua produk yang memiliki variasi.');

                    if (incompleteRow) {
                        incompleteRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        const variantSelect = incompleteRow.querySelector('.variant-select');
                        if (variantSelect) {
                            variantSelect.focus();
                        }
                    }
                    return false;
                }

                // Then check for duplicates
                const isValid = validateAllRows();

                if (!isValid) {
                    e.preventDefault();
                    alert('❌ Terdapat produk/variant yang duplikat! Harap hapus atau ganti produk/variant yang duplikat sebelum menyimpan.');

                    // Scroll to first duplicate
                    const firstDuplicate = document.querySelector('.duplicate-warning:not(.hidden)');
                    if (firstDuplicate) {
                        firstDuplicate.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }

                    return false;
                }

                // Clean up any "undefined" variant values before submit
                document.querySelectorAll('.variant-select').forEach(select => {
                    if (select.value === 'undefined' || select.value === '') {
                        select.removeAttribute('name'); // Remove from form submission
                    }
                });
            });
        }
    </script>

    <!-- Modal Tambah Supplier -->
    <div id="supplierModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-start sm:items-center justify-center hidden z-50 overflow-y-auto p-4 sm:p-6">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md sm:max-w-lg p-5 sm:p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Tambah Supplier</h3>
                <button type="button" class="text-gray-500" onclick="closeSupplierModal()">&times;</button>
            </div>
            <form id="supplierForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" name="name" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" required>
                    <p class="text-xs text-red-500 mt-1 hidden" data-error="name"></p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                        <input type="text" name="phone" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        <p class="text-xs text-red-500 mt-1 hidden" data-error="phone"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. Whatsapp (+62...)</label>
                        <input type="text" name="whatsapp_number" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        <p class="text-xs text-red-500 mt-1 hidden" data-error="whatsapp_number"></p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    <p class="text-xs text-red-500 mt-1 hidden" data-error="email"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea name="address" rows="2" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"></textarea>
                    <p class="text-xs text-red-500 mt-1 hidden" data-error="address"></p>
                </div>
                <div class="border-t pt-4 space-y-3">
                    <p class="text-sm font-semibold text-gray-900">Lokasi</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
                            <input type="text" id="supplierProvinceInput" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Ketik provinsi..." autocomplete="off">
                            <input type="hidden" name="province_id" id="supplierProvinceId">
                            <div id="supplierProvinceSuggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 hidden max-h-48 overflow-y-auto shadow-lg"></div>
                            <p class="text-xs text-red-500 mt-1 hidden" data-error="province_id"></p>
                        </div>
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kota/Kabupaten</label>
                            <input type="text" id="supplierCityInput" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Ketik kota..." autocomplete="off" disabled>
                            <input type="hidden" name="city_id" id="supplierCityId">
                            <div id="supplierCitySuggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 hidden max-h-48 overflow-y-auto shadow-lg"></div>
                            <p class="text-xs text-red-500 mt-1 hidden" data-error="city_id"></p>
                        </div>
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kecamatan</label>
                            <input type="text" id="supplierDistrictInput" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Ketik kecamatan..." autocomplete="off" disabled>
                            <input type="hidden" name="district_id" id="supplierDistrictId">
                            <div id="supplierDistrictSuggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 hidden max-h-48 overflow-y-auto shadow-lg"></div>
                            <p class="text-xs text-red-500 mt-1 hidden" data-error="district_id"></p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
                        <input type="text" name="postal_code" id="supplierPostalCode" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50" readonly>
                        <p class="text-xs text-red-500 mt-1 hidden" data-error="postal_code"></p>
                    </div>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50" onclick="closeSupplierModal()">Batal</button>
                    <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover">Simpan</button>
                </div>
                <p class="text-xs text-red-500 mt-2 hidden" data-error="general"></p>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Produk -->
    <div id="productModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Tambah Produk</h3>
                <button type="button" class="text-gray-500" onclick="closeProductModal()">&times;</button>
            </div>
            <form id="productForm" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk</label>
                        <input type="text" name="name" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" required>
                        <p class="text-xs text-red-500 mt-1 hidden" data-error-product="name"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                        <input type="text" name="sku" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" required>
                        <p class="text-xs text-red-500 mt-1 hidden" data-error-product="sku"></p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select name="category_id" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-red-500 mt-1 hidden" data-error-product="category_id"></p>
                    </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Pokok</label>
                                <input type="number" step="0.01" name="cost_price" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" required>
                                <p class="text-xs text-red-500 mt-1 hidden" data-error-product="cost_price"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual</label>
                                <input type="number" step="0.01" name="selling_price" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" required>
                                <p class="text-xs text-red-500 mt-1 hidden" data-error-product="selling_price"></p>
                                <p class="text-xs text-gray-500 mt-1">Diisi otomatis memakai margin minimal, bisa diubah manual.</p>
                            </div>
                        </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"></textarea>
                    <p class="text-xs text-red-500 mt-1 hidden" data-error-product="description"></p>
                </div>
                <!-- Featured Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Gambar Utama <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-start gap-4">
                        <div id="productModalFeaturedPreview" class="w-32 h-32 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden">
                            <span class="text-xs text-gray-400 text-center px-2">Belum ada gambar utama</span>
                        </div>
                        <div class="flex-1">
                            <button type="button" id="btnModalSelectFeatured" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover text-sm font-medium mb-2">
                                Pilih Gambar Utama
                            </button>
                            <p class="text-xs text-gray-500 mb-1">Gambar utama yang ditampilkan di list produk.</p>
                            <p class="text-xs text-gray-400">• Auto crop 1:1 (1080x1080)</p>
                            <p class="text-xs text-gray-400">• Dikonversi ke WebP</p>
                        </div>
                    </div>
                    <input type="hidden" name="featured_media_id" id="productModalFeaturedMediaId" required>
                    <p class="text-xs text-red-500 mt-1 hidden" data-error-product="featured_media_id"></p>
                </div>

                <!-- Gallery -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Gallery Produk
                        <span class="text-gray-400 text-xs font-normal">(Maksimal 5 foto)</span>
                    </label>
                    <div id="productModalGalleryGrid" class="grid grid-cols-5 gap-3">
                        <!-- Initial empty state with add button -->
                        <div class="col-span-5">
                            <button type="button" id="btnModalInitialGallery" class="w-full bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 hover:border-primary cursor-pointer transition flex items-center justify-center h-32">
                                <div class="text-center">
                                    <div class="text-3xl text-gray-400">+</div>
                                    <div class="text-sm text-gray-500 mt-1">Tambah Foto ke Gallery</div>
                                </div>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="gallery_media_ids" id="productModalGalleryMediaIds" value="[]">
                    <p class="text-xs text-gray-500 mt-2">Drag & drop untuk mengubah urutan tampilan.</p>
                    <p class="text-xs text-red-500 mt-1 hidden" data-error-product="gallery_media_ids"></p>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50" onclick="closeProductModal()">Batal</button>
                    <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover">Simpan</button>
                </div>
                <p class="text-xs text-red-500 mt-2 hidden" data-error-product="general"></p>
            </form>
        </div>
    </div>
    <script>
        const supplierModal = document.getElementById('supplierModal');
        const openSupplierBtn = document.getElementById('open-supplier-modal');
        const supplierForm = document.getElementById('supplierForm');
        const supplierPhoneInput = supplierForm.querySelector('input[name="phone"]');
        const supplierWhatsappInput = supplierForm.querySelector('input[name="whatsapp_number"]');
        let supplierWhatsappEdited = false;

        openSupplierBtn.addEventListener('click', () => {
            supplierModal.classList.remove('hidden');
        });

        supplierWhatsappInput?.addEventListener('input', () => {
            supplierWhatsappEdited = true;
        });

        supplierPhoneInput?.addEventListener('input', () => {
            if (!supplierWhatsappEdited || !supplierWhatsappInput.value.trim()) {
                supplierWhatsappInput.value = supplierPhoneInput.value;
            }
        });

        function closeSupplierModal() {
            supplierModal.classList.add('hidden');
            supplierForm.reset();
            document.querySelectorAll('[data-error]').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });
            supplierWhatsappEdited = false;
        }

        supplierForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            document.querySelectorAll('[data-error]').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });
            const formData = new FormData(supplierForm);
            try {
                const res = await fetch('{{ route('suppliers.store') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });
                if (res.status === 201) {
                    const data = await res.json();
                    const s = data.supplier;
                    const option = document.createElement('option');
                    option.value = s.id;
                    option.textContent = s.name;
                    option.selected = true;
                    supplierSelect.appendChild(option);
                    closeSupplierModal();
                } else if (res.status === 422) {
                    const errorData = await res.json();
                    const errors = errorData.errors || {};
                    Object.keys(errors).forEach(key => {
                        const el = document.querySelector(`[data-error=\"${key}\"]`);
                        if (el) {
                            el.textContent = errors[key][0];
                            el.classList.remove('hidden');
                        }
                    });
                    if (!Object.keys(errors).length) {
                        const general = document.querySelector('[data-error=\"general\"]');
                        general.textContent = errorData.message || 'Gagal menyimpan supplier.';
                        general.classList.remove('hidden');
                    }
                } else {
                    const general = document.querySelector('[data-error=\"general\"]');
                    general.textContent = 'Gagal menyimpan supplier.';
                    general.classList.remove('hidden');
                }
            } catch (err) {
                const general = document.querySelector('[data-error=\"general\"]');
                general.textContent = 'Gagal menyimpan supplier.';
                general.classList.remove('hidden');
            }
        });

        // Lokasi autocomplete logic (modal)
        const provInput = document.getElementById('supplierProvinceInput');
        const provId = document.getElementById('supplierProvinceId');
        const provSug = document.getElementById('supplierProvinceSuggestions');

        const cityInput = document.getElementById('supplierCityInput');
        const cityId = document.getElementById('supplierCityId');
        const citySug = document.getElementById('supplierCitySuggestions');

        const distInput = document.getElementById('supplierDistrictInput');
        const distId = document.getElementById('supplierDistrictId');
        const distSug = document.getElementById('supplierDistrictSuggestions');

        const postalInput = document.getElementById('supplierPostalCode');

        const resetCity = () => {
            cityInput.value = '';
            cityId.value = '';
            cityInput.disabled = !provId.value;
            citySug.innerHTML = '';
            citySug.classList.add('hidden');
        };

        const resetDistrict = () => {
            distInput.value = '';
            distId.value = '';
            distInput.disabled = !cityId.value;
            distSug.innerHTML = '';
            distSug.classList.add('hidden');
            postalInput.value = '';
        };

        provInput?.addEventListener('input', () => {
            loadProv(provInput.value);
        });
        provInput?.addEventListener('focus', () => {
            if (!provInput.value) loadProv('');
        });

        cityInput?.addEventListener('input', () => {
            loadCity(cityInput.value);
        });
        cityInput?.addEventListener('focus', () => {
            if (!cityInput.value && provId.value) loadCity('');
        });

        distInput?.addEventListener('input', () => {
            loadDistrict(distInput.value);
        });
        distInput?.addEventListener('focus', () => {
            if (!distInput.value && cityId.value) loadDistrict('');
        });

        function loadProv(query) {
            const url = new URL('/api/provinces/search', window.location.origin);
            if (query) url.searchParams.append('q', query);
            url.searchParams.append('limit', 6);
            fetch(url).then(r => r.json()).then(list => {
                provSug.innerHTML = '';
                if (!list.length) {
                    provSug.innerHTML = '<div class="p-2 text-gray-500">Tidak ada hasil</div>';
                    provSug.classList.remove('hidden');
                    return;
                }
                list.forEach(p => {
                    const div = document.createElement('div');
                    div.className = 'p-2 hover:bg-blue-100 cursor-pointer border-b';
                    div.textContent = p.name;
                    div.addEventListener('click', () => {
                        provInput.value = p.name;
                        provId.value = p.code;
                        provSug.classList.add('hidden');
                        resetCity();
                        resetDistrict();
                    });
                    provSug.appendChild(div);
                });
                provSug.classList.remove('hidden');
            });
        }

        function loadCity(query) {
            if (!provId.value) return;
            const url = new URL('/api/cities/search', window.location.origin);
            url.searchParams.append('province_code', provId.value);
            if (query) url.searchParams.append('q', query);
            url.searchParams.append('limit', 6);
            fetch(url).then(r => r.json()).then(list => {
                citySug.innerHTML = '';
                if (!list.length) {
                    citySug.innerHTML = '<div class="p-2 text-gray-500">Tidak ada hasil</div>';
                    citySug.classList.remove('hidden');
                    return;
                }
                list.forEach(c => {
                    const div = document.createElement('div');
                    div.className = 'p-2 hover:bg-blue-100 cursor-pointer border-b';
                    div.textContent = c.name;
                    div.addEventListener('click', () => {
                        cityInput.value = c.name;
                        cityId.value = c.code;
                        citySug.classList.add('hidden');
                        resetDistrict();
                    });
                    citySug.appendChild(div);
                });
                citySug.classList.remove('hidden');
            });
        }

        function loadDistrict(query) {
            if (!cityId.value) return;
            const url = new URL('/api/districts/search', window.location.origin);
            url.searchParams.append('city_code', cityId.value);
            if (query) url.searchParams.append('q', query);
            url.searchParams.append('limit', 6);
            fetch(url).then(r => r.json()).then(list => {
                distSug.innerHTML = '';
                if (!list.length) {
                    distSug.innerHTML = '<div class="p-2 text-gray-500">Tidak ada hasil</div>';
                    distSug.classList.remove('hidden');
                    return;
                }
                list.forEach(d => {
                    const div = document.createElement('div');
                    div.className = 'p-2 hover:bg-blue-100 cursor-pointer border-b';
                    div.textContent = d.name;
                    div.addEventListener('click', () => {
                        distInput.value = d.name;
                        distId.value = d.code;
                        postalInput.value = d.postal_code || '';
                        distSug.classList.add('hidden');
                    });
                    distSug.appendChild(div);
                });
                distSug.classList.remove('hidden');
            });
        }

        // Close suggestions when clicking outside modal
        document.addEventListener('click', (e) => {
            if (!supplierModal.contains(e.target) || e.target.closest('#supplierModal') === null) return;
            [provSug, citySug, distSug].forEach(el => {
                if (!el.contains(e.target) && !el.previousElementSibling?.contains(e.target)) {
                    el.classList.add('hidden');
                }
            });
        });

        // Product modal logic
        const productModal = document.getElementById('productModal');
        const productForm = document.getElementById('productForm');
        const minMarginProduct = {{ $minMargin }};
        const productModalFeaturedMediaId = document.getElementById('productModalFeaturedMediaId');
        const productModalGalleryMediaIds = document.getElementById('productModalGalleryMediaIds');
        const btnModalSelectFeatured = document.getElementById('btnModalSelectFeatured');

        // Initialize ProductGallery for modal
        const modalGallery = new ProductGallery({
            featuredImageId: null,
            galleryMediaIds: [],
            listUrl: '{{ route('media.product_photo.list') }}',
            uploadUrl: '{{ route('media.store') }}',
            productId: null,
            onFeaturedChange: (mediaId) => {
                productModalFeaturedMediaId.value = mediaId;
            },
            onGalleryChange: (mediaIds) => {
                productModalGalleryMediaIds.value = JSON.stringify(mediaIds);
            }
        });

        // Override container IDs for modal
        modalGallery.renderFeatured = function() {
            const container = document.getElementById('productModalFeaturedPreview');
            if (!container) return;

            if (this.featuredMedia) {
                container.innerHTML = `
                    <img src="${this.featuredMedia.url}" alt="${this.featuredMedia.filename}"
                         class="w-full h-full object-cover rounded-lg">
                `;
            } else {
                container.innerHTML = `
                    <span class="text-xs text-gray-400 text-center px-2">Belum ada gambar utama</span>
                `;
            }
        };

        modalGallery.renderGallery = function() {
            const container = document.getElementById('productModalGalleryGrid');
            if (!container) return;

            container.innerHTML = '';

            if (this.galleryMedia.length === 0) {
                container.innerHTML = `
                    <div class="col-span-5">
                        <button type="button" id="btnModalAddToGallery" class="w-full bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 hover:border-primary cursor-pointer transition flex items-center justify-center h-32">
                            <div class="text-center">
                                <div class="text-3xl text-gray-400">+</div>
                                <div class="text-sm text-gray-500 mt-1">Tambah Foto ke Gallery</div>
                            </div>
                        </button>
                    </div>
                `;
                const btnModalAddToGallery = document.getElementById('btnModalAddToGallery');
                if (btnModalAddToGallery) {
                    btnModalAddToGallery.addEventListener('click', () => this.openGalleryPicker());
                }
                return;
            }

            this.galleryMedia.forEach((item, index) => {
                const card = document.createElement('div');
                card.className = 'relative cursor-move bg-white rounded-lg border-2 border-gray-200 overflow-hidden transition h-32';
                card.draggable = true;
                card.dataset.mediaId = item.id;
                card.dataset.index = index;

                card.innerHTML = `
                    <img src="${item.url || ''}" alt="${item.filename}" class="w-full h-full object-cover bg-gray-100">
                    <div class="absolute top-2 left-2 bg-white px-2 py-0.5 rounded shadow-sm text-xs font-semibold text-gray-700 z-20">${index + 1}</div>
                    <div class="absolute inset-0 bg-black transition-opacity flex items-center justify-center z-10" style="opacity: 0; pointer-events: none;"></div>
                    <div class="absolute inset-0 flex items-center justify-center z-20" style="pointer-events: none;">
                        <button type="button" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-medium transition" data-remove="${item.id}" style="opacity: 0; pointer-events: auto;">
                            Hapus
                        </button>
                    </div>
                `;

                const overlay = card.querySelector('.absolute.inset-0');
                const removeBtn = card.querySelector('[data-remove]');

                card.addEventListener('mouseenter', () => {
                    card.style.borderColor = '#F17B0D';
                    overlay.style.opacity = '0.4';
                    removeBtn.style.opacity = '1';
                });

                card.addEventListener('mouseleave', () => {
                    card.style.borderColor = '#E5E7EB';
                    overlay.style.opacity = '0';
                    removeBtn.style.opacity = '0';
                });

                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.removeFromGallery(item.id);
                });

                card.addEventListener('dragstart', (e) => this.handleDragStart(e));
                card.addEventListener('dragover', (e) => this.handleDragOver(e));
                card.addEventListener('drop', (e) => this.handleDrop(e));
                card.addEventListener('dragend', (e) => this.handleDragEnd(e));

                container.appendChild(card);
            });

            if (this.galleryMedia.length < 5) {
                const addBtn = document.createElement('div');
                addBtn.className = 'relative bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 hover:border-primary cursor-pointer transition flex items-center justify-center h-32';
                addBtn.innerHTML = `
                    <div class="text-center">
                        <div class="text-3xl text-gray-400">+</div>
                        <div class="text-xs text-gray-500 mt-1">Tambah Foto</div>
                    </div>
                `;
                addBtn.addEventListener('click', () => this.openGalleryPicker());
                container.appendChild(addBtn);
            }
        };

        btnModalSelectFeatured.addEventListener('click', () => {
            modalGallery.openFeaturedPicker();
        });

        // Initial gallery button
        const btnModalInitialGallery = document.getElementById('btnModalInitialGallery');
        if (btnModalInitialGallery) {
            btnModalInitialGallery.addEventListener('click', () => {
                modalGallery.openGalleryPicker();
            });
        }

        openProductBtn.addEventListener('click', () => {
            productModal.classList.remove('hidden');
        });

        function closeProductModal() {
            productModal.classList.add('hidden');
            productForm.reset();
            document.querySelectorAll('[data-error-product]').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });
            // Reset gallery
            modalGallery.featuredImageId = null;
            modalGallery.featuredMedia = null;
            modalGallery.galleryMediaIds = [];
            modalGallery.galleryMedia = [];
            modalGallery.renderFeatured();
            modalGallery.renderGallery();
            productModalFeaturedMediaId.value = '';
            productModalGalleryMediaIds.value = '[]';

            // Re-attach event listener for initial gallery button after render
            setTimeout(() => {
                const btnModalInitialGalleryNew = document.getElementById('btnModalInitialGallery');
                if (btnModalInitialGalleryNew) {
                    btnModalInitialGalleryNew.addEventListener('click', () => {
                        modalGallery.openGalleryPicker();
                    });
                }
            }, 0);
        }

        productForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            document.querySelectorAll('[data-error-product]').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });
            const formData = new FormData(productForm);
            try {
                const res = await fetch('{{ route('products.store') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
                    },
                    body: formData
                });
                if (res.status === 201) {
                    const data = await res.json();
                    const p = data.product;
                    addProductOptionToAllSelects(p.id, p.name, p.cost_price);
                    closeProductModal();
                } else if (res.status === 422) {
                    const errorData = await res.json();
                    const errors = errorData.errors || {};
                    Object.keys(errors).forEach(key => {
                        const el = document.querySelector(`[data-error-product=\"${key}\"]`);
                        if (el) {
                            el.textContent = errors[key][0];
                            el.classList.remove('hidden');
                        }
                    });
                    if (!Object.keys(errors).length) {
                        const general = document.querySelector('[data-error-product=\"general\"]');
                        general.textContent = errorData.message || 'Gagal menyimpan produk.';
                        general.classList.remove('hidden');
                    }
                } else {
                    const general = document.querySelector('[data-error-product=\"general\"]');
                    general.textContent = 'Gagal menyimpan produk.';
                    general.classList.remove('hidden');
                }
            } catch (err) {
                const general = document.querySelector('[data-error-product=\"general\"]');
                general.textContent = 'Gagal menyimpan produk.';
                general.classList.remove('hidden');
            }
        });

        // Autofill selling price in product modal based on min margin
        const modalCostInput = productForm.querySelector('input[name="cost_price"]');
        const modalSellInput = productForm.querySelector('input[name="selling_price"]');
        let modalSellEdited = false;

        modalSellInput.addEventListener('input', () => {
            modalSellEdited = true;
        });

        modalCostInput.addEventListener('input', () => {
            if (!modalSellEdited || !modalSellInput.value) {
                const cost = parseFloat(modalCostInput.value || 0);
                if (!isNaN(cost)) {
                    const suggested = cost * (1 + minMarginProduct / 100);
                    modalSellInput.value = suggested.toFixed(2);
                }
            }
        });

        function addProductOptionToAllSelects(id, name, costPrice) {
            document.querySelectorAll('.product-select').forEach(select => {
                const opt = document.createElement('option');
                opt.value = id;
                opt.textContent = name;
                opt.dataset.price = costPrice ?? 0;
                select.appendChild(opt);
                // set default for last added row only
                if (select.closest('.item-row') === itemsContainer.lastElementChild) {
                    select.value = id;
                    const priceInput = select.closest('.item-row').querySelector('.price');
                    if (priceInput) {
                        priceInput.value = costPrice ?? 0;
                    }
                }
            });
            updateTotal();
        }
    </script>
</x-app-layout>
