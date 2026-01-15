<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Buat Order Baru</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form action="{{ route('orders.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Customer Autocomplete -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="customer-search"
                                autocomplete="off"
                                placeholder="Ketik untuk mencari customer..."
                                class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            <input type="hidden" id="customer-select" name="customer_id" value="" required>
                            <div id="customer-dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                            @error('customer_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe</label>
                            <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="order">Order</option>
                                <option value="preorder">Preorder</option>
                            </select>
                            @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="draft" selected>Draft</option>
                                <option value="waiting_payment">Waiting Payment</option>
                                <option value="dp_paid">DP Paid</option>
                                <option value="paid">Paid</option>
                                <option value="packed">Packed</option>
                                <option value="shipped">Shipped</option>
                                <option value="done">Done</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Item Order</h3>
                        <div id="items-container" class="space-y-6 mb-4">
                            <!-- Items will be added dynamically -->
                        </div>
                        <button type="button" id="add-item" class="bg-blue text-white px-4 py-2 rounded-lg hover:bg-blue-light">+ Tambah Item</button>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                        <textarea name="notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3"></textarea>
                    </div>

                    <!-- Shipping Info -->
                    <div class="border rounded-lg p-4 space-y-4 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Alamat Tujuan (Customer)</h3>
                        <div class="text-sm text-gray-600">
                            <p class="font-medium text-gray-800">Alamat Asal (Settings)</p>
                            <p>
                                {{ $origin['province_name'] ?? '-' }},
                                {{ $origin['city_name'] ?? '-' }},
                                {{ $origin['district_name'] ?? '-' }}
                                @if(!empty($origin['postal_code']))
                                    ({{ $origin['postal_code'] }})
                                @endif
                            </p>
                            @if(empty($origin['district_id']))
                                <p class="text-xs text-red-500 mt-1">Alamat asal belum diatur di Settings.</p>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Provinsi</label>
                                <input type="hidden" name="shipping_province_id" id="ship_province_id">
                                <input type="text" id="ship_province_search" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Cari provinsi" autocomplete="off">
                                <div id="ship_province_suggestions" class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto mt-1"></div>
                                @error('shipping_province_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kota/Kabupaten</label>
                                <input type="hidden" name="shipping_city_id" id="ship_city_id">
                                <input type="text" id="ship_city_search" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Cari kota" autocomplete="off">
                                <div id="ship_city_suggestions" class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto mt-1"></div>
                                @error('shipping_city_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kecamatan</label>
                                <input type="hidden" name="shipping_district_id" id="ship_district_id">
                                <input type="text" id="ship_district_search" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Cari kecamatan" autocomplete="off">
                                <div id="ship_district_suggestions" class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto mt-1"></div>
                                @error('shipping_district_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Pos</label>
                                <input type="text" name="shipping_postal_code" id="shipping_postal_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Kode Pos">
                                @error('shipping_postal_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Lengkap</label>
                            <textarea name="shipping_address" id="shipping_address_input" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3" placeholder="Nama jalan, no rumah, patokan"></textarea>
                            @error('shipping_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kurir</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <select id="shipping_courier" name="shipping_courier" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="">Pilih kurir</option>
                                    @foreach($couriers as $code => $name)
                                        @if(empty($activeCouriers) || in_array($code, $activeCouriers, true))
                                            <option value="{{ $code }}">{{ $name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <select id="shipping_service" name="shipping_service" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" disabled>
                                    <option value="">Pilih layanan</option>
                                </select>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Kurir mengikuti pengaturan di halaman Settings.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Berat (gram)</label>
                                <input type="number" id="shipping_weight_grams" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly value="0">
                            </div>
                            <div class="flex items-end">
                                <button type="button" id="btn_calc_shipping" class="w-full px-3 py-2 bg-blue text-white rounded-lg hover:bg-blue-light">Hitung Ongkir</button>
                            </div>
                        </div>
                        <div id="shipping_calc_message" class="text-xs text-gray-500"></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ongkos Kirim</label>
                            <div class="flex gap-2 items-center">
                                <input type="number" name="shipping_cost" id="shipping_cost" min="0" step="0.01" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Hitung otomatis via RajaOngkir atau isi manual.</p>
                            @error('shipping_cost') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estimasi Pengiriman</label>
                            <input type="text" name="shipping_etd" id="shipping_etd" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly>
                        </div>
                    </div>
                    <!-- Total -->
                    <div class="border-t pt-6">
                        <div class="text-right">
                            <p class="text-gray-600 mb-2">Total:</p>
                            <p class="text-3xl font-bold text-primary">Rp <span id="total-amount">0</span></p>
                            <input type="hidden" name="total_amount" id="total-amount-input">
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-6">
                        <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover transition-colors">
                            Simpan Order
                        </button>
                        <a href="{{ route('orders.index') }}" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 text-center">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/autocomplete.js') }}"></script>
    <script>
        const allCustomers = @json($customers);
        let allProducts = @json($products);
        const flashSalePromos = @json($flashSalePromos ?? []);
        const origin_district_id = @json($origin['district_id'] ?? null);

        const itemsContainer = document.getElementById('items-container');
        const addItemBtn = document.getElementById('add-item');
        const totalAmountSpan = document.getElementById('total-amount');
        const totalAmountInput = document.getElementById('total-amount-input');
        const shippingAddressInput = document.getElementById('shipping_address_input');
        const weightInput = document.getElementById('shipping_weight_grams');
        const courierSelect = document.getElementById('shipping_courier');
        const serviceSelect = document.getElementById('shipping_service');
        const calcButton = document.getElementById('btn_calc_shipping');
        const calcMessage = document.getElementById('shipping_calc_message');
        const orderTypeSelect = document.querySelector('select[name="type"]');

        let itemCount = 0;
        let itemAutocompletes = {};
        let variantAutocompletes = {};

        // Fetch filtered products based on order type
        async function fetchProductsByType(type) {
            try {
                const response = await fetch(`{{ route('api.products.by_order_type') }}?type=${type}`);
                const data = await response.json();
                return data.products;
            } catch (error) {
                console.error('Failed to fetch products:', error);
                return [];
            }
        }

        // Update all product autocompletes with new filtered data
        function updateAllProductAutocompletes(products) {
            allProducts = products;

            // Update all existing autocomplete instances
            Object.keys(itemAutocompletes).forEach(index => {
                if (itemAutocompletes[index]) {
                    itemAutocompletes[index].updateData(products);
                    itemAutocompletes[index].reset();
                }
            });
        }

        // Handle order type change
        orderTypeSelect?.addEventListener('change', async (e) => {
            const selectedType = e.target.value;
            const products = await fetchProductsByType(selectedType);
            updateAllProductAutocompletes(products);

            // Clear all selected products in item rows
            document.querySelectorAll('.item-row').forEach(row => {
                const index = row.dataset.index;
                const productSearchInput = document.getElementById(`product-search-${index}`);
                const productSelectInput = document.getElementById(`product-select-${index}`);
                const variantContainer = row.querySelector(`#variant-container-${index}`);
                const priceInput = row.querySelector('.price');

                // Reset product selection
                if (productSearchInput) productSearchInput.value = '';
                if (productSelectInput) productSelectInput.value = '';
                if (variantContainer) variantContainer.classList.add('hidden');
                if (priceInput) priceInput.value = '';
            });
        });

        // Initialize Customer Autocomplete
        const customerAutocomplete = new Autocomplete({
            inputId: 'customer-search',
            hiddenInputId: 'customer-select',
            dropdownId: 'customer-dropdown',
            data: allCustomers,
            searchFields: ['name', 'phone', 'email'],
            displayTemplate: (customer) => {
                let html = `<div class="font-medium">${customer.name}</div>`;
                if (customer.phone) {
                    html += `<div class="text-xs text-gray-500">Phone: ${customer.phone}</div>`;
                }
                return html;
            },
            maxItems: 10,
            onSelect: (customer) => {
                // Auto-fill shipping address from customer
                document.getElementById('ship_province_id').value = customer.province_id || '';
                document.getElementById('ship_province_search').value = customer.province?.name || '';
                document.getElementById('ship_city_id').value = customer.city_id || '';
                document.getElementById('ship_city_search').value = customer.city?.name || '';
                document.getElementById('ship_district_id').value = customer.district_id || '';
                document.getElementById('ship_district_search').value = customer.district?.name || '';
                document.getElementById('shipping_postal_code').value = customer.postal_code || '';
                shippingAddressInput.value = customer.full_address || customer.address || '';

                // Reset shipping service
                if (serviceSelect) {
                    serviceSelect.innerHTML = '<option value="">Pilih layanan</option>';
                    serviceSelect.disabled = true;
                }
            }
        });

        function createItemRow() {
            const index = itemCount++;
            const div = document.createElement('div');
            div.className = 'item-row border border-gray-300 rounded-lg p-4 bg-white';
            div.dataset.index = index;

            div.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Product Autocomplete -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Produk <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="product-search-${index}"
                            autocomplete="off"
                            placeholder="Ketik untuk mencari produk..."
                            class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        <input type="hidden" id="product-select-${index}" name="items[${index}][product_id]" value="">
                        <input type="hidden" id="variant-select-${index}" name="items[${index}][product_variant_id]" value="">
                        <div id="product-dropdown-${index}" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>

                        <!-- Variant Autocomplete (conditional) -->
                        <div id="variant-container-${index}" class="hidden mt-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Variasi <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="variant-search-${index}"
                                autocomplete="off"
                                placeholder="Pilih variasi..."
                                class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            <div id="variant-dropdown-${index}" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Qty <span class="text-red-500">*</span></label>
                            <input type="number" name="items[${index}][quantity]" class="quantity w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Qty" min="1" value="1" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Harga <span class="text-red-500">*</span></label>
                            <input type="number" name="items[${index}][unit_price]" class="price w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Harga" step="0.01" min="0" required>
                            <div class="flash-sale-info text-xs text-orange-600 mt-1 hidden">
                                <span class="flash-sale-label">Flash Sale:</span>
                                <span class="flash-sale-original line-through"></span>
                                <span class="flash-sale-arrow">→</span>
                                <span class="flash-sale-price font-semibold"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 mt-4">
                    <div class="preorder-eta-cell invisible">
                        <label class="block text-sm font-medium text-gray-700 mb-2">ETA Preorder</label>
                        <input type="date" name="items[${index}][preorder_eta_date]" class="preorder-eta w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="ETA">
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="button" class="remove-item bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 text-sm">Hapus Item</button>
                </div>
            `;

            return div;
        }

        function initializeItemAutocomplete(row) {
            const index = row.dataset.index;
            const quantityInput = row.querySelector('.quantity');
            const priceInput = row.querySelector('.price');
            const preorderEta = row.querySelector('.preorder-eta');
            const preorderEtaCell = row.querySelector('.preorder-eta-cell');
            const removeBtn = row.querySelector('.remove-item');
            const variantContainer = row.querySelector(`#variant-container-${index}`);

            // Product autocomplete
            itemAutocompletes[index] = new Autocomplete({
                inputId: `product-search-${index}`,
                hiddenInputId: `product-select-${index}`,
                dropdownId: `product-dropdown-${index}`,
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
                    await handleProductSelect(index, product, row);
                }
            });

            // Event listeners
            quantityInput.addEventListener('change', () => {
                updateItemPricing(row);
                updateTotal();
                updateWeightTotal();
            });

            priceInput.addEventListener('change', updateTotal);

            removeBtn.addEventListener('click', () => {
                row.remove();
                delete itemAutocompletes[index];
                delete variantAutocompletes[index];
                updateTotal();
                updateWeightTotal();
            });
        }

        async function handleProductSelect(index, product, row) {
            const priceInput = row.querySelector('.price');
            const quantityInput = row.querySelector('.quantity');
            const preorderEta = row.querySelector('.preorder-eta');
            const preorderEtaCell = row.querySelector('.preorder-eta-cell');
            const variantContainer = row.querySelector(`#variant-container-${index}`);
            const variantSelectInput = document.getElementById(`variant-select-${index}`);

            // Clear variant selection
            variantSelectInput.value = '';

            const hasVariants = product.has_variants;

            if (hasVariants) {
                // Load variants (with for_order parameter to filter by stock or preorder)
                try {
                    const response = await fetch(`/products/${product.id}/variants?for_order=1`);
                    const data = await response.json();

                    const variantData = data.variants.map(variant => ({
                        id: variant.id,
                        name: Object.values(variant.variant_attributes).join(' / '),
                        sku: variant.sku,
                        selling_price: variant.selling_price,
                        weight_grams: variant.weight_grams,
                        variant_attributes: variant.variant_attributes
                    }));

                    // Initialize or update variant autocomplete
                    if (variantAutocompletes[index]) {
                        variantAutocompletes[index].updateData(variantData);
                        variantAutocompletes[index].reset();
                    } else {
                        variantAutocompletes[index] = new Autocomplete({
                            inputId: `variant-search-${index}`,
                            hiddenInputId: `variant-select-${index}`,
                            dropdownId: `variant-dropdown-${index}`,
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
                                priceInput.value = variant.selling_price || 0;
                                updateItemPricing(row);
                                updateTotal();
                                updateWeightTotal();
                            }
                        });
                    }

                    // Show variant container
                    variantContainer.classList.remove('hidden');

                    // Don't auto-fill price until variant is selected
                    priceInput.value = '';
                    preorderEtaCell.classList.add('invisible');

                } catch (error) {
                    console.error('Failed to load variants:', error);
                    alert('Gagal memuat variasi produk');
                }
            } else {
                // Hide variant container for simple products
                variantContainer.classList.add('hidden');
                if (variantAutocompletes[index]) {
                    variantAutocompletes[index].reset();
                }

                // Fill price for simple product
                priceInput.value = product.selling_price || 0;

                // Show ETA field if product allows preorder and auto-fill if available
                const allowPreorder = product.allow_preorder;
                if (allowPreorder && product.preorder_eta_date) {
                    // Convert ISO timestamp to YYYY-MM-DD format for date input
                    const etaDate = new Date(product.preorder_eta_date);
                    const formattedDate = etaDate.toISOString().split('T')[0];
                    preorderEta.value = formattedDate;
                    preorderEtaCell.classList.remove('invisible');
                } else if (allowPreorder) {
                    preorderEta.value = '';
                    preorderEtaCell.classList.remove('invisible');
                } else {
                    preorderEta.value = '';
                    preorderEtaCell.classList.add('invisible');
                }
            }

            updateItemPricing(row);
            updateTotal();
            updateWeightTotal();
        }

        function applyFlashSaleBenefit(benefit, basePrice) {
            let discounted = basePrice;
            if (benefit.type === 'percent_off') {
                const discount = basePrice * (benefit.value / 100);
                discounted = basePrice - (benefit.max_discount && discount > benefit.max_discount ? benefit.max_discount : discount);
            } else if (benefit.type === 'amount_off') {
                discounted = basePrice - benefit.value;
            } else if (benefit.type === 'fixed_price') {
                discounted = Math.min(basePrice, benefit.value);
            } else {
                return basePrice;
            }

            return Math.max(0, discounted);
        }

        function getMatchingFlashSale(product, variant, qty, stock) {
            if (!flashSalePromos.length) {
                return null;
            }

            const productId = product?.id;
            const variantId = variant?.id;

            for (const promo of flashSalePromos) {
                let matched = false;
                let excluded = false;

                for (const target of promo.targets || []) {
                    if (target.type === 'variant' && variantId && target.id === variantId) {
                        if (!target.include) {
                            excluded = true;
                            break;
                        }
                        matched = true;
                    }
                    if (target.type === 'product' && productId && target.id === productId) {
                        if (!target.include) {
                            excluded = true;
                            break;
                        }
                        matched = true;
                    }
                }

                if (excluded || !matched) {
                    continue;
                }

                const rules = promo.rules || {};
                if (rules.min_qty && qty < Number(rules.min_qty)) {
                    continue;
                }
                if (rules.max_qty && qty > Number(rules.max_qty)) {
                    continue;
                }
                if (rules.min_stock_threshold !== undefined && stock < Number(rules.min_stock_threshold)) {
                    continue;
                }

                return promo;
            }

            return null;
        }

        function updateItemPricing(row) {
            const index = row.dataset.index;
            const priceInput = row.querySelector('.price');
            const qty = parseFloat(row.querySelector('.quantity').value) || 1;
            const product = itemAutocompletes[index]?.getSelected();
            const variant = variantAutocompletes[index]?.getSelected();
            const flashInfo = row.querySelector('.flash-sale-info');
            const originalEl = row.querySelector('.flash-sale-original');
            const priceEl = row.querySelector('.flash-sale-price');

            if (!product) {
                if (flashInfo) flashInfo.classList.add('hidden');
                return;
            }

            if (product.has_variants && !variant) {
                if (flashInfo) flashInfo.classList.add('hidden');
                return;
            }

            const basePrice = parseFloat((variant?.selling_price ?? product.selling_price) || 0);
            const stock = parseFloat((variant?.total_stock ?? product.qty_on_hand ?? 0) || 0);
            const promo = getMatchingFlashSale(product, variant, qty, stock);

            if (!promo) {
                if (flashInfo) flashInfo.classList.add('hidden');
                if (basePrice) {
                    priceInput.value = basePrice;
                }
                return;
            }

            const benefit = (promo.benefits || []).find(b => b.apply_scope === 'item') || (promo.benefits || [])[0];
            if (!benefit || benefit.apply_scope !== 'item' || benefit.type === 'free_shipping') {
                if (flashInfo) flashInfo.classList.add('hidden');
                priceInput.value = basePrice;
                return;
            }

            const discounted = applyFlashSaleBenefit(benefit, basePrice);
            if (discounted < basePrice) {
                priceInput.value = discounted;
                if (flashInfo && originalEl && priceEl) {
                    originalEl.textContent = `Rp ${basePrice.toLocaleString('id-ID')}`;
                    priceEl.textContent = `Rp ${discounted.toLocaleString('id-ID')}`;
                    flashInfo.classList.remove('hidden');
                }
            } else {
                if (flashInfo) flashInfo.classList.add('hidden');
                priceInput.value = basePrice;
            }
        }

        function updateTotal() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.quantity').value) || 0;
                const price = parseFloat(row.querySelector('.price').value) || 0;
                total += qty * price;
            });
            const shipping = parseFloat(document.getElementById('shipping_cost')?.value || 0);
            const grandTotal = total + shipping;
            totalAmountSpan.textContent = grandTotal.toLocaleString('id-ID');
            totalAmountInput.value = grandTotal;
        }

        function updateWeightTotal() {
            let totalWeight = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const index = row.dataset.index;
                const qty = parseFloat(row.querySelector('.quantity').value) || 0;
                const productAc = itemAutocompletes[index];
                const variantAc = variantAutocompletes[index];

                let weight = 0;
                if (variantAc && variantAc.getSelected()) {
                    weight = parseFloat(variantAc.getSelected().weight_grams || 0);
                } else if (productAc && productAc.getSelected()) {
                    weight = parseFloat(productAc.getSelected().weight_grams || 0);
                }

                totalWeight += qty * weight;
            });
            if (weightInput) {
                weightInput.value = Math.max(0, Math.round(totalWeight));
            }
        }

        // Add item button
        addItemBtn.addEventListener('click', () => {
            const newRow = createItemRow();
            itemsContainer.appendChild(newRow);
            initializeItemAutocomplete(newRow);
        });

        // Initialize first item immediately with existing data
        const firstRow = createItemRow();
        itemsContainer.appendChild(firstRow);
        initializeItemAutocomplete(firstRow);

        // Then fetch and update with filtered products in background
        async function updateProductsAfterLoad() {
            const defaultType = orderTypeSelect?.value || 'order';
            const products = await fetchProductsByType(defaultType);

            if (products && products.length > 0) {
                allProducts = products;
                // Update the autocomplete data for the first item
                if (itemAutocompletes[0]) {
                    itemAutocompletes[0].updateData(products);
                }
            }
        }

        // Update products in background
        updateProductsAfterLoad();

        // Shipping cost change
        document.getElementById('shipping_cost')?.addEventListener('input', updateTotal);

        // Location autocomplete (existing code for province/city/district)
        const provinceInput = document.getElementById('ship_province_search');
        const provinceIdInput = document.getElementById('ship_province_id');
        const provinceSuggestions = document.getElementById('ship_province_suggestions');
        const cityInput = document.getElementById('ship_city_search');
        const cityIdInput = document.getElementById('ship_city_id');
        const citySuggestions = document.getElementById('ship_city_suggestions');
        const districtInput = document.getElementById('ship_district_search');
        const districtIdInput = document.getElementById('ship_district_id');
        const districtSuggestions = document.getElementById('ship_district_suggestions');
        const postalInput = document.getElementById('shipping_postal_code');

        async function fetchSuggestions(url) {
            const res = await fetch(url);
            if (!res.ok) return [];
            const data = await res.json();
            return data;
        }

        function renderSuggestions(el, items, onSelect) {
            if (!items.length) {
                el.classList.add('hidden');
                return;
            }
            el.innerHTML = items.map(item => `
                <button type="button" data-id="${item.code}" data-name="${item.name}" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100">
                    ${item.name}
                </button>
            `).join('');
            el.classList.remove('hidden');
            el.onclick = (e) => {
                const btn = e.target.closest('button[data-id]');
                if (!btn) return;
                onSelect(btn.dataset.id, btn.dataset.name, items.find(i => i.code === btn.dataset.id));
                el.classList.add('hidden');
            };
        }

        provinceInput?.addEventListener('input', async (e) => {
            const q = encodeURIComponent(e.target.value);
            const items = await fetchSuggestions(`/api/provinces/search?q=${q}`);
            renderSuggestions(provinceSuggestions, items, (id, name) => {
                provinceIdInput.value = id;
                provinceInput.value = name;
                cityInput.value = '';
                cityIdInput.value = '';
                districtInput.value = '';
                districtIdInput.value = '';
            });
        });

        cityInput?.addEventListener('input', async (e) => {
            if (!provinceIdInput.value) return;
            const q = encodeURIComponent(e.target.value);
            const items = await fetchSuggestions(`/api/cities/search?q=${q}&province_code=${provinceIdInput.value}`);
            renderSuggestions(citySuggestions, items, (id, name) => {
                cityIdInput.value = id;
                cityInput.value = name;
                districtInput.value = '';
                districtIdInput.value = '';
            });
        });

        districtInput?.addEventListener('input', async (e) => {
            if (!cityIdInput.value) return;
            const q = encodeURIComponent(e.target.value);
            const items = await fetchSuggestions(`/api/districts/search?q=${q}&city_code=${cityIdInput.value}`);
            renderSuggestions(districtSuggestions, items, (id, name, item) => {
                districtIdInput.value = id;
                districtInput.value = name;
                if (item && item.postal_code && !postalInput.value) {
                    postalInput.value = item.postal_code;
                }
            });
        });

        document.addEventListener('click', (e) => {
            [provinceSuggestions, citySuggestions, districtSuggestions].forEach(el => {
                if (el && !el.contains(e.target) && !el.previousElementSibling.contains(e.target)) {
                    el.classList.add('hidden');
                }
            });
        });

        // Shipping cost calculation (existing logic)
        function renderServiceOptions(options) {
            serviceSelect.innerHTML = '<option value="">Pilih layanan</option>';
            options.forEach((option) => {
                const opt = document.createElement('option');
                const costLabel = typeof option.cost === 'number'
                    ? `Rp ${option.cost.toLocaleString('id-ID')}`
                    : option.cost;
                const etdLabel = option.etd ? ` (ETD ${option.etd})` : '';
                const descLabel = option.description ? ` - ${option.description}` : '';
                opt.value = option.service;
                opt.dataset.cost = option.cost ?? 0;
                opt.dataset.etd = option.etd ?? '';
                opt.textContent = `${option.service}${descLabel} - ${costLabel}${etdLabel}`;
                serviceSelect.appendChild(opt);
            });
            serviceSelect.disabled = options.length === 0;
        }

        function normalizeShippingOptions(payload) {
            if (!payload) return [];
            if (Array.isArray(payload.options)) return payload.options;
            if (payload.rajaongkir?.results?.length) {
                const options = [];
                payload.rajaongkir.results.forEach((result) => {
                    (result.costs || []).forEach((cost) => {
                        const costValue = cost.cost?.[0]?.value ?? cost.cost ?? cost.value ?? 0;
                        options.push({
                            courier: result.code || result.name,
                            service: cost.service || cost.name || '-',
                            cost: Number(costValue) || 0,
                            etd: cost.cost?.[0]?.etd ?? cost.etd ?? '',
                        });
                    });
                });
                return options;
            }
            if (Array.isArray(payload.data)) {
                const options = [];
                payload.data.forEach((item) => {
                    if (item.service && item.cost !== undefined) {
                        options.push({
                            courier: item.code || item.name,
                            service: item.service,
                            description: item.description || '',
                            cost: Number(item.cost) || 0,
                            etd: item.etd ?? '',
                        });
                        return;
                    }
                    if (Array.isArray(item.costs)) {
                        item.costs.forEach((cost) => {
                            const costValue = cost.cost?.[0]?.value ?? cost.cost ?? cost.value ?? 0;
                            options.push({
                                courier: item.code || item.name,
                                service: cost.service || cost.name || '-',
                                description: cost.description || '',
                                cost: Number(costValue) || 0,
                                etd: cost.cost?.[0]?.etd ?? cost.etd ?? '',
                            });
                        });
                        return;
                    }
                    if (Array.isArray(item.services)) {
                        item.services.forEach((service) => {
                            const costValue = service.cost ?? service.price ?? service.value ?? 0;
                            options.push({
                                courier: item.code || item.name,
                                service: service.service || service.name || '-',
                                description: service.description || '',
                                cost: Number(costValue) || 0,
                                etd: service.etd ?? '',
                            });
                        });
                    }
                });
                return options;
            }
            return [];
        }

        serviceSelect?.addEventListener('change', () => {
            const selected = serviceSelect.selectedOptions[0];
            if (!selected) return;
            const cost = Number(selected.dataset.cost || 0);
            document.getElementById('shipping_cost').value = cost;
            const etd = selected.dataset.etd || '';
            const etdInput = document.getElementById('shipping_etd');
            if (etdInput) {
                etdInput.value = etd;
            }
            updateTotal();
        });

        courierSelect?.addEventListener('change', () => {
            if (serviceSelect) {
                serviceSelect.innerHTML = '<option value="">Pilih layanan</option>';
                serviceSelect.disabled = true;
            }
            calcMessage.textContent = '';
        });

        calcButton?.addEventListener('click', async () => {
            const destinationDistrictId = districtIdInput.value;
            const courier = courierSelect?.value;
            const weight = Number(weightInput?.value || 0);

            if (!origin_district_id) {
                calcMessage.textContent = 'Alamat asal belum diatur di Settings.';
                return;
            }
            if (!destinationDistrictId) {
                calcMessage.textContent = 'Pilih kecamatan tujuan dulu.';
                return;
            }
            if (!courier) {
                calcMessage.textContent = 'Pilih kurir dulu.';
                return;
            }
            if (weight <= 0) {
                calcMessage.textContent = 'Berat total belum terisi.';
                return;
            }

            calcButton.disabled = true;
            calcMessage.textContent = 'Menghitung ongkir...';

            try {
                const res = await fetch('/api/shipping/cost', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        origin_district_id: origin_district_id,
                        destination_district_id: destinationDistrictId,
                        weight_grams: weight,
                        courier: courier,
                    }),
                });
                const payload = await res.json().catch(() => null);
                if (!res.ok) {
                    calcMessage.textContent = payload?.message || 'Gagal menghitung ongkir.';
                    serviceSelect.innerHTML = '<option value="">Pilih layanan</option>';
                    serviceSelect.disabled = true;
                    return;
                }
                const options = normalizeShippingOptions(payload);
                renderServiceOptions(options);
                if (options.length) {
                    serviceSelect.value = options[0].service;
                    document.getElementById('shipping_cost').value = options[0].cost ?? 0;
                    const etdInput = document.getElementById('shipping_etd');
                    if (etdInput) {
                        etdInput.value = options[0].etd ?? '';
                    }
                    updateTotal();
                }
                calcMessage.textContent = options.length ? 'Ongkir ditemukan.' : 'Ongkir tidak tersedia.';
            } catch (error) {
                calcMessage.textContent = 'Gagal menghubungi layanan ongkir.';
            } finally {
                calcButton.disabled = false;
            }
        });
    </script>
</x-app-layout>
