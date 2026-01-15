<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Promo</h2>
            <a href="{{ route('promotions.index') }}" class="text-sm text-gray-600 hover:text-primary">Kembali</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $oldBundleItems = old('bundle_items');
                if ($oldBundleItems) {
                    $bundleItems = collect($oldBundleItems)->map(function ($item) {
                        if (isset($item['target'])) {
                            return [
                                'target' => $item['target'],
                                'qty' => $item['qty'] ?? 1,
                            ];
                        }
                        if (isset($item['product_id'])) {
                            return [
                                'target' => 'product:' . $item['product_id'],
                                'qty' => $item['qty'] ?? 1,
                            ];
                        }

                        return [
                            'target' => '',
                            'qty' => $item['qty'] ?? 1,
                        ];
                    })->values();
                } else {
                    $bundleItems = collect([['target' => '', 'qty' => 1]]);
                }

                $catalogItems = $products->flatMap(function ($product) {
                    $items = [[
                        'id' => 'product:' . $product->id,
                        'key' => 'product:' . $product->id,
                        'name' => $product->name,
                        'label' => $product->name,
                        'sku' => $product->sku,
                        'price' => (float) $product->selling_price,
                    ]];

                    foreach ($product->variants as $variant) {
                        if (!$product->allow_preorder && $variant->total_stock <= 0) {
                            continue;
                        }
                        $attrs = implode(' / ', array_values($variant->variant_attributes ?? []));
                        $label = $product->name . ($attrs ? ' — ' . $attrs : ' — Variasi');
                        $items[] = [
                            'id' => 'variant:' . $variant->id,
                            'key' => 'variant:' . $variant->id,
                            'name' => $label,
                            'label' => $label,
                            'sku' => $variant->sku,
                            'price' => (float) $variant->selling_price,
                        ];
                    }

                    $items = collect($items)->map(function ($item) {
                        return array_merge($item, [
                            'id' => $item['id'] ?? $item['key'],
                            'name' => $item['label'] ?? $item['name'],
                        ]);
                    })->values()->all();

                    return $items;
                })->values();
            @endphp
            <script src="{{ asset('js/autocomplete.js') }}"></script>
            <script>
                window.promoCreateData = {
                    promoType: @json(old('type', $type)),
                    products: @json($catalogItems),
                    bundleItems: @json($bundleItems),
                    bundlePricingMode: @json(old('bundle_pricing_mode', 'fixed')),
                    bundlePrice: @json(old('bundle_price', '')),
                    bundleDiscountValue: @json(old('bundle_discount_value', '')),
                    applyScope: @json(old('apply_scope', old('type', $type) === 'cart_rule' ? 'cart' : 'item')),
                    bundleAutocomplete: {},
                    productByKey(key) {
                        return this.products.find((product) => product.key === key);
                    },
                    initBundleAutocomplete(index) {
                        if (this.bundleAutocomplete[index]) {
                            return;
                        }

                        const inputId = `bundle-item-search-${index}`;
                        const hiddenId = `bundle-item-target-${index}`;
                        const dropdownId = `bundle-item-dropdown-${index}`;
                        const input = document.getElementById(inputId);
                        const hidden = document.getElementById(hiddenId);
                        const dropdown = document.getElementById(dropdownId);

                        if (!input || !hidden || !dropdown || !window.Autocomplete) {
                            return;
                        }

                        const selected = this.productByKey(this.bundleItems[index]?.target);
                        if (selected && !input.value) {
                            input.value = selected.label;
                            hidden.value = selected.key;
                        }

                        this.bundleAutocomplete[index] = new Autocomplete({
                            inputId,
                            hiddenInputId: hiddenId,
                            dropdownId,
                            data: this.products,
                            searchFields: ['label', 'name', 'sku'],
                            displayTemplate: (item) => {
                                let html = `<div class="font-medium">${item.label}</div>`;
                                if (item.sku) {
                                    html += `<div class="text-xs text-gray-500">SKU: ${item.sku}</div>`;
                                }
                                return html;
                            },
                            maxItems: 10,
                            onSelect: (item) => {
                                this.bundleItems[index].target = item.key;
                            },
                        });
                    },
                    productPrice(key) {
                        const product = this.productByKey(key);
                        return product ? Number(product.price) : 0;
                    },
                    lineTotal(item) {
                        return this.productPrice(item.target) * (Number(item.qty) || 0);
                    },
                    bundleSubtotal() {
                        return this.bundleItems.reduce((total, item) => total + this.lineTotal(item), 0);
                    },
                    bundleEffectiveTotal() {
                        const subtotal = this.bundleSubtotal();
                        const mode = this.bundlePricingMode;
                        if (mode === 'fixed') {
                            return Number(this.bundlePrice) || 0;
                        }
                        if (mode === 'percent_off') {
                            const percent = Number(this.bundleDiscountValue) || 0;
                            return subtotal * (1 - percent / 100);
                        }
                        if (mode === 'amount_off') {
                            const amount = Number(this.bundleDiscountValue) || 0;
                            return Math.max(0, subtotal - amount);
                        }
                        return subtotal;
                    },
                    bundleSavings() {
                        return Math.max(0, this.bundleSubtotal() - this.bundleEffectiveTotal());
                    },
                    formatCurrency(value) {
                        return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0,
                        }).format(value || 0);
                    },
                };
            </script>
            <form method="POST" action="{{ route('promotions.store') }}" class="space-y-6" x-data="window.promoCreateData" x-effect="if (promoType === 'cart_rule') { applyScope = 'cart'; }">
                @csrf

                <div class="bg-white overflow-visible shadow-sm sm:rounded-lg p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Step 1 — Informasi Dasar</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Promo</label>
                            <select name="type" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" x-model="promoType">
                                <option value="flash_sale">Flash Sale</option>
                                <option value="coupon">Coupon</option>
                                <option value="bundle">Bundle</option>
                                <option value="cart_rule">Cart Rule</option>
                            </select>
                            @error('type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Promo</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" required>
                            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                                <option value="scheduled" @selected(old('status') === 'scheduled')>Scheduled</option>
                                <option value="active" @selected(old('status') === 'active')>Active</option>
                            </select>
                            @error('status') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mulai</label>
                            <input type="datetime-local" name="start_at" value="{{ old('start_at') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            @error('start_at') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Selesai</label>
                            <input type="datetime-local" name="end_at" value="{{ old('end_at') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            @error('end_at') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
                            <input type="number" name="priority" value="{{ old('priority', 100) }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" min="0" required>
                            @error('priority') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input type="checkbox" name="stackable" value="1" class="rounded border-gray-300 text-primary focus:ring-primary" @checked(old('stackable'))>
                            <span class="text-sm text-gray-700">Boleh digabung promo lain</span>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="description" rows="3" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900" x-show="promoType === 'coupon'">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Step 2 — Pengaturan Coupon</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Coupon</label>
                            <input type="text" name="coupon_code" value="{{ old('coupon_code') }}" class="w-full h-10 px-4 py-2 text-sm uppercase border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="CONTOH-123" x-bind:disabled="promoType !== 'coupon'">
                            @error('coupon_code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Limit Global</label>
                            <input type="number" name="coupon_global_limit" value="{{ old('coupon_global_limit') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" min="1" x-bind:disabled="promoType !== 'coupon'">
                            @error('coupon_global_limit') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Limit per User</label>
                            <input type="number" name="coupon_per_user_limit" value="{{ old('coupon_per_user_limit') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" min="1" x-bind:disabled="promoType !== 'coupon'">
                            @error('coupon_per_user_limit') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Minimal Order</label>
                            <input type="number" name="coupon_min_order_amount" value="{{ old('coupon_min_order_amount') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" min="0" step="0.01" x-bind:disabled="promoType !== 'coupon'">
                            @error('coupon_min_order_amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input type="checkbox" name="coupon_first_purchase_only" value="1" class="rounded border-gray-300 text-primary focus:ring-primary" @checked(old('coupon_first_purchase_only')) x-bind:disabled="promoType !== 'coupon'">
                            <span class="text-sm text-gray-700">Hanya pembelian pertama</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900" x-show="promoType === 'bundle'">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Step 3 — Bundle Items</h3>
                    <div class="space-y-3">
                        <template x-for="(item, index) in bundleItems" :key="index">
                            <div class="grid grid-cols-1 md:grid-cols-8 gap-3 items-end" x-init="$nextTick(() => initBundleAutocomplete(index))">
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                                    <div class="relative">
                                        <input type="text"
                                               :id="`bundle-item-search-${index}`"
                                               autocomplete="off"
                                               placeholder="Ketik untuk mencari produk..."
                                               class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                                               x-bind:disabled="promoType !== 'bundle'">
                                        <input type="hidden"
                                               :id="`bundle-item-target-${index}`"
                                               :name="`bundle_items[${index}][target]`"
                                               x-model="item.target"
                                               x-bind:disabled="promoType !== 'bundle'">
                                        <div :id="`bundle-item-dropdown-${index}`" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                                    </div>
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
                                    <div class="h-10 px-4 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 flex items-center"
                                         x-text="formatCurrency(productPrice(item.target))"></div>
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Qty</label>
                                    <input type="number" min="1" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                                           :name="`bundle_items[${index}][qty]`" x-model="item.qty"
                                           x-bind:disabled="promoType !== 'bundle'">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                                    <div class="h-10 px-4 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 flex items-center"
                                         x-text="formatCurrency(lineTotal(item))"></div>
                                </div>
                                <div class="md:col-span-1 flex items-end">
                                    <button type="button" class="h-10 px-3 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition w-full"
                                            @click="bundleItems.splice(index, 1)" x-show="bundleItems.length > 1">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-sm"
                                @click="bundleItems.push({ target: '', qty: 1 })">
                            + Tambah Produk
                        </button>
                        <div class="border-t border-gray-200 pt-4 text-sm text-gray-700 flex flex-col md:flex-row md:items-center md:justify-end gap-3">
                            <div>Subtotal: <span class="font-semibold" x-text="formatCurrency(bundleSubtotal())"></span></div>
                            <div>Total Bundle: <span class="font-semibold text-primary" x-text="formatCurrency(bundleEffectiveTotal())"></span></div>
                            <div x-show="bundleSavings() > 0">Hemat: <span class="font-semibold text-green-600" x-text="formatCurrency(bundleSavings())"></span></div>
                        </div>
                        @if($errors->has('bundle_items.*.target'))
                            <p class="text-xs text-red-500 mt-1">{{ $errors->first('bundle_items.*.target') }}</p>
                        @endif
                        @if($errors->has('bundle_items.*.qty'))
                            <p class="text-xs text-red-500 mt-1">{{ $errors->first('bundle_items.*.qty') }}</p>
                        @endif
                        @error('bundle_items') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    @php
                        $bundleFeaturedMediaId = old('bundle_featured_media_id');
                    @endphp
                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-semibold text-gray-900">Gambar Utama Bundle</h4>
                        <p class="text-xs text-gray-500 mt-1">Opsional. Jika kosong, gambar bundle akan mengambil produk pertama.</p>
                        <div class="mt-3 flex flex-col md:flex-row md:items-center gap-4">
                            <input type="hidden" name="bundle_featured_media_id" id="bundle-featured-media-id" value="{{ $bundleFeaturedMediaId }}">
                            <div class="w-32 h-32 border border-gray-200 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden">
                                <img id="bundle-featured-media-preview" src="" alt="Preview Bundle" class="hidden w-full h-full object-cover">
                                <span id="bundle-featured-media-empty" class="text-xs text-gray-400">Belum ada gambar</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" id="bundle-featured-media-btn" class="h-10 px-4 rounded-lg bg-primary text-white text-sm hover:bg-primary-hover transition">
                                    Pilih Gambar Bundle
                                </button>
                                <button type="button" id="bundle-featured-media-clear" class="h-10 px-4 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
                                    Hapus
                                </button>
                            </div>
                        </div>
                        @error('bundle_featured_media_id') <p class="text-xs text-red-500 mt-2">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900" x-show="promoType === 'bundle'">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Step 4 — Pricing Bundle</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pricing Mode</label>
                            <select name="bundle_pricing_mode" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" x-model="bundlePricingMode">
                                <option value="fixed" @selected(old('bundle_pricing_mode') === 'fixed')>Fixed</option>
                                <option value="percent_off" @selected(old('bundle_pricing_mode') === 'percent_off')>Percent Off</option>
                                <option value="amount_off" @selected(old('bundle_pricing_mode') === 'amount_off')>Amount Off</option>
                            </select>
                            @error('bundle_pricing_mode') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div x-show="bundlePricingMode === 'fixed'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Bundle</label>
                            <input type="number" name="bundle_price" value="{{ old('bundle_price') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" min="0" step="0.01" x-model="bundlePrice" x-bind:required="promoType === 'bundle' && bundlePricingMode === 'fixed'">
                            @error('bundle_price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div x-show="bundlePricingMode !== 'fixed'">
                            <label class="block text-sm font-medium text-gray-700 mb-1" x-text="bundlePricingMode === 'percent_off' ? 'Diskon (%)' : 'Diskon (Rp)'"></label>
                            <input type="number" name="bundle_discount_value" value="{{ old('bundle_discount_value') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" min="0" step="0.01" x-model="bundleDiscountValue" x-bind:required="promoType === 'bundle' && bundlePricingMode !== 'fixed'">
                            @error('bundle_discount_value') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input type="checkbox" name="bundle_must_be_cheaper" value="1" class="rounded border-gray-300 text-primary focus:ring-primary" @checked(old('bundle_must_be_cheaper', true))>
                            <span class="text-sm text-gray-700">Harga bundle harus lebih murah</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900" x-show="promoType === 'flash_sale' || promoType === 'coupon'">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Step 3 — Target Produk</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                        @php $selectedTargets = collect(old('targets', [])); @endphp
                        <select name="targets[]" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" multiple size="8" x-bind:required="promoType === 'flash_sale'">
                            @foreach($catalogItems as $item)
                                <option value="{{ $item['key'] }}" @selected($selectedTargets->contains($item['key']))>
                                    {{ $item['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-2">Gunakan Ctrl/Cmd untuk memilih multiple produk.</p>
                        @error('targets') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900" x-show="promoType !== 'bundle'">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Step 4 — Pricing</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Benefit</label>
                            <select name="benefit_type" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" x-bind:required="promoType !== 'bundle'">
                                <option value="percent_off" @selected(old('benefit_type') === 'percent_off')>Percent Off</option>
                                <option value="amount_off" @selected(old('benefit_type') === 'amount_off')>Amount Off</option>
                                <option value="fixed_price" @selected(old('benefit_type') === 'fixed_price')>Fixed Price</option>
                                <option value="free_shipping" @selected(old('benefit_type') === 'free_shipping')>Free Shipping</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nilai</label>
                            <input type="number" name="benefit_value" value="{{ old('benefit_value', 0) }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" min="0" step="0.01" x-bind:required="promoType !== 'bundle'">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Diskon</label>
                            <input type="number" name="max_discount" value="{{ old('max_discount') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" min="0" step="0.01">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Apply Scope</label>
                            <select name="apply_scope" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" x-model="applyScope" x-bind:required="promoType !== 'bundle'">
                                <option value="item" @selected(old('apply_scope') === 'item') :disabled="promoType === 'cart_rule'">Item</option>
                                <option value="cart" @selected(old('apply_scope') === 'cart')>Cart</option>
                                <option value="shipping" @selected(old('apply_scope') === 'shipping') :disabled="promoType === 'cart_rule'">Shipping</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-2" x-show="promoType === 'cart_rule'">Cart rule berlaku untuk total keranjang, jadi Apply Scope otomatis ke Cart.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Step 5 — Constraints</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span class="inline-flex items-center gap-1">
                                    Min Qty
                                    <span class="relative" x-data="{ open: false }">
                                        <button type="button" class="text-gray-400 hover:text-gray-600" @click="open = !open" @click.away="open = false" aria-label="Info Min Qty">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <circle cx="12" cy="12" r="9"></circle>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0-9h.01"></path>
                                            </svg>
                                        </button>
                                        <div x-cloak x-show="open" class="absolute left-0 z-50 mt-2 rounded-md border border-gray-200 bg-white p-2 text-xs text-gray-600 shadow-lg" style="width: 200px;">
                                            Minimal total item di keranjang agar promo berlaku.
                                        </div>
                                    </span>
                                </span>
                            </label>
                            <input type="number" name="min_qty" value="{{ old('min_qty') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" min="1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span class="inline-flex items-center gap-1">
                                    Max Qty
                                    <span class="relative" x-data="{ open: false }">
                                        <button type="button" class="text-gray-400 hover:text-gray-600" @click="open = !open" @click.away="open = false" aria-label="Info Max Qty">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <circle cx="12" cy="12" r="9"></circle>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0-9h.01"></path>
                                            </svg>
                                        </button>
                                        <div x-cloak x-show="open" class="absolute left-0 z-50 mt-2 rounded-md border border-gray-200 bg-white p-2 text-xs text-gray-600 shadow-lg" style="width: 200px;">
                                            Batas maksimal total item yang boleh ikut promo.
                                        </div>
                                    </span>
                                </span>
                            </label>
                            <input type="number" name="max_qty" value="{{ old('max_qty') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" min="1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span class="inline-flex items-center gap-1">
                                    Limit per User
                                    <span class="relative" x-data="{ open: false }">
                                        <button type="button" class="text-gray-400 hover:text-gray-600" @click="open = !open" @click.away="open = false" aria-label="Info Limit per User">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <circle cx="12" cy="12" r="9"></circle>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0-9h.01"></path>
                                            </svg>
                                        </button>
                                        <div x-cloak x-show="open" class="absolute left-0 z-50 mt-2 rounded-md border border-gray-200 bg-white p-2 text-xs text-gray-600 shadow-lg" style="width: 200px;">
                                            Batas pemakaian promo per customer.
                                        </div>
                                    </span>
                                </span>
                            </label>
                            <input type="number" name="per_user_limit" value="{{ old('per_user_limit') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" min="1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span class="inline-flex items-center gap-1">
                                    Min Stock
                                    <span class="relative" x-data="{ open: false }">
                                        <button type="button" class="text-gray-400 hover:text-gray-600" @click="open = !open" @click.away="open = false" aria-label="Info Min Stock">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <circle cx="12" cy="12" r="9"></circle>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0-9h.01"></path>
                                            </svg>
                                        </button>
                                        <div x-cloak x-show="open" class="absolute left-0 z-50 mt-2 rounded-md border border-gray-200 bg-white p-2 text-xs text-gray-600 shadow-lg" style="width: 200px;">
                                            Promo hanya berlaku jika stok produk >= nilai ini.
                                        </div>
                                    </span>
                                </span>
                            </label>
                            <input type="number" name="min_stock_threshold" value="{{ old('min_stock_threshold') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" min="0">
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900 flex items-center justify-between">
                    <div class="text-sm text-gray-500">Step 6 — Review & Publish</div>
                    <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-hover transition">Simpan Promo</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<script src="{{ asset('js/media-picker.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.getElementById('bundle-featured-media-btn');
    if (!trigger) {
        return;
    }
    const input = document.getElementById('bundle-featured-media-id');
    const preview = document.getElementById('bundle-featured-media-preview');
    const empty = document.getElementById('bundle-featured-media-empty');
    const clear = document.getElementById('bundle-featured-media-clear');

    const setPreview = (media) => {
        input.value = media?.id ?? '';
        if (media?.url) {
            preview.src = media.url;
            preview.classList.remove('hidden');
            empty.classList.add('hidden');
        } else {
            preview.src = '';
            preview.classList.add('hidden');
            empty.classList.remove('hidden');
        }
    };

    trigger.addEventListener('click', () => {
        openMediaPicker({
            type: 'product_photo',
            title: 'Pilih Gambar Bundle',
            listUrl: '{{ route('media.product_photo.list') }}',
            uploadUrl: '{{ route('media.store') }}',
            csrfToken: '{{ csrf_token() }}',
            aiEnabled: {{ ($aiEnabled ?? false) ? 'true' : 'false' }},
            aiRoutes: {!! json_encode([
                'features' => route('ai.features'),
                'enhance' => route('ai.enhance'),
                'job' => url('ai/jobs'),
            ]) !!},
            onSelect: setPreview,
        });
    });

    clear?.addEventListener('click', () => setPreview(null));
});
</script>
