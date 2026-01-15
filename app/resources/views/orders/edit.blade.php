<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Edit Order {{ $order->order_number }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                <form action="{{ route('orders.update', $order) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Order</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="draft" {{ $order->status === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="waiting_payment" {{ $order->status === 'waiting_payment' ? 'selected' : '' }}>Waiting Payment</option>
                            <option value="dp_paid" {{ $order->status === 'dp_paid' ? 'selected' : '' }}>DP Paid</option>
                            <option value="paid" {{ $order->status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="packed" {{ $order->status === 'packed' ? 'selected' : '' }}>Packed</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="done" {{ $order->status === 'done' ? 'selected' : '' }}>Done</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" rows="3">{{ $order->notes }}</textarea>
                    </div>

                    <!-- Order Items -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Item Order</h3>
                        <div class="hidden md:grid grid-cols-12 gap-3 text-xs uppercase tracking-wide text-gray-500 mb-2">
                            <div class="col-span-5">Produk</div>
                            <div class="col-span-1 text-right">Qty</div>
                            <div class="col-span-2 text-right">Harga</div>
                            <div class="col-span-1 text-center">Preorder</div>
                            <div class="col-span-2">ETA (perkiraan)</div>
                            <div class="col-span-1 text-center">Aksi</div>
                        </div>
                        <div id="items-container" class="space-y-4 mb-4">
                            @foreach($order->items as $index => $item)
                                @php
                                    $etaDate = $item->preorder_eta_date ? $item->preorder_eta_date->format('Y-m-d') : '';
                                    $allowPreorder = $item->product?->allow_preorder ?? false;
                                @endphp
                                <div class="item-row grid grid-cols-12 gap-3">
                                    <select name="items[{{ $index }}][product_id]" class="col-span-5 product-select min-w-[220px] px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                                        <option value="">Pilih Produk</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                data-price="{{ $product->selling_price }}"
                                                data-weight="{{ $product->weight_grams ?? 0 }}"
                                                data-allow-preorder="{{ $product->allow_preorder ? 1 : 0 }}"
                                                data-preorder-eta="{{ $product->preorder_eta_date ? $product->preorder_eta_date->format('Y-m-d') : '' }}"
                                                {{ $item->product_id === $product->id ? 'selected' : '' }}
                                            >
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="items[{{ $index }}][quantity]" class="col-span-1 quantity px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Qty" min="1" value="{{ $item->quantity }}" required>
                                    <input type="number" name="items[{{ $index }}][unit_price]" class="col-span-2 price px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary bg-gray-50" placeholder="Harga" value="{{ $item->unit_price }}" readonly required>
                                    <label class="col-span-1 flex items-center justify-center gap-2 text-sm text-gray-700 preorder-cell {{ $allowPreorder ? '' : 'invisible' }}">
                                        <input type="checkbox"
                                               name="items[{{ $index }}][is_preorder]"
                                               value="1"
                                               class="preorder-toggle h-4 w-4 text-primary border-gray-300 rounded"
                                               title="Centang jika item ini pre-order"
                                               {{ $allowPreorder ? '' : 'disabled' }}
                                               {{ $item->is_preorder ? 'checked' : '' }}>
                                        <span>Preorder</span>
                                    </label>
                                    <input type="date"
                                           name="items[{{ $index }}][preorder_eta_date]"
                                           class="col-span-2 preorder-eta preorder-eta-cell px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary {{ $item->is_preorder ? '' : 'invisible' }}"
                                           value="{{ $etaDate }}"
                                           {{ $item->is_preorder ? '' : 'disabled' }}>
                                    <button type="button" class="col-span-1 remove-item bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-600">Hapus</button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-item" class="bg-blue text-white px-4 py-2 rounded-lg hover:bg-blue-light">+ Tambah Item</button>
                        @error('items') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Total Berat (gram)</label>
                            <input type="number" id="shipping_weight_grams" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly value="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ongkos Kirim</label>
                            <input type="number" name="shipping_cost" id="shipping_cost" min="0" step="0.01" value="{{ $order->shipping_cost ?? 0 }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="border-t pt-6">
                        <div class="text-right space-y-1">
                            @php
                                $itemsSubtotal = $order->items->sum(function ($item) {
                                    return $item->subtotal ?? ($item->quantity * $item->unit_price);
                                });
                                $shippingCost = $order->shipping_cost ?? 0;
                            @endphp
                            <p class="text-sm text-gray-500">Subtotal Item: <span class="text-gray-700">Rp {{ number_format($itemsSubtotal, 0, ',', '.') }}</span></p>
                            <p class="text-sm text-gray-500">Ongkos Kirim: <span class="text-gray-700">Rp {{ number_format($shippingCost, 0, ',', '.') }}</span></p>
                            <p class="text-gray-600 mb-2">Total:</p>
                            <p class="text-3xl font-bold text-primary">Rp <span id="total-amount">{{ number_format($order->total_amount, 0, ',', '.') }}</span></p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-6">
                        <button type="submit" class="flex-1 bg-primary text-white h-10 px-4 rounded-lg hover:bg-primary-hover transition">
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('orders.show', $order) }}" class="flex-1 bg-gray-300 text-gray-700 h-10 px-4 rounded-lg hover:bg-gray-400 text-center flex items-center justify-center">
                            Batal
                        </a>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const itemsContainer = document.getElementById('items-container');
        const addItemBtn = document.getElementById('add-item');
        const totalAmountSpan = document.getElementById('total-amount');
        const weightInput = document.getElementById('shipping_weight_grams');
        let itemCount = {{ $order->items->count() }};

        function createItemRow() {
            const div = document.createElement('div');
            div.className = 'item-row grid grid-cols-12 gap-3';
            const index = itemCount++;
            div.innerHTML = `
                <select name="items[${index}][product_id]" class="col-span-5 product-select min-w-[220px] px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                    <option value="">Pilih Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}"
                            data-price="{{ $product->selling_price }}"
                            data-weight="{{ $product->weight_grams ?? 0 }}"
                            data-allow-preorder="{{ $product->allow_preorder ? 1 : 0 }}"
                            data-preorder-eta="{{ $product->preorder_eta_date ? $product->preorder_eta_date->format('Y-m-d') : '' }}"
                        >
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
                <input type="number" name="items[${index}][quantity]" class="col-span-1 quantity px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Qty" min="1" value="1" required>
                <input type="number" name="items[${index}][unit_price]" class="col-span-2 price px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary bg-gray-50" placeholder="Harga" readonly required>
                <label class="col-span-1 flex items-center justify-center gap-2 text-sm text-gray-700 preorder-cell invisible">
                    <input type="checkbox" name="items[${index}][is_preorder]" value="1" class="preorder-toggle h-4 w-4 text-primary border-gray-300 rounded" title="Centang jika item ini pre-order" disabled>
                    <span>Preorder</span>
                </label>
                <input type="date" name="items[${index}][preorder_eta_date]" class="col-span-2 preorder-eta preorder-eta-cell px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary invisible" placeholder="ETA" disabled>
                <button type="button" class="col-span-1 remove-item bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-600">Hapus</button>
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
            const shipping = parseFloat(document.getElementById('shipping_cost')?.value || 0);
            const grandTotal = total + shipping;
            if (totalAmountSpan) {
                totalAmountSpan.textContent = grandTotal.toLocaleString('id-ID');
            }
        }

        function updateWeightTotal() {
            let totalWeight = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.quantity').value) || 0;
                const selected = row.querySelector('.product-select')?.selectedOptions?.[0];
                const weight = parseFloat(selected?.dataset?.weight || 0);
                totalWeight += qty * weight;
            });
            if (weightInput) {
                weightInput.value = Math.max(0, Math.round(totalWeight));
            }
        }

        function attachItemListeners(row) {
            const productSelect = row.querySelector('.product-select');
            const priceInput = row.querySelector('.price');
            const quantityInput = row.querySelector('.quantity');
            const removeBtn = row.querySelector('.remove-item');
            const preorderToggle = row.querySelector('.preorder-toggle');
            const preorderEta = row.querySelector('.preorder-eta');
            const preorderCell = row.querySelector('.preorder-cell');
            const preorderEtaCell = row.querySelector('.preorder-eta-cell');

            function updatePreorderVisibility(allowPreorder) {
                if (!preorderCell || !preorderEtaCell) {
                    return;
                }
                if (!allowPreorder) {
                    preorderCell.classList.add('invisible');
                    preorderEtaCell.classList.add('invisible');
                    return;
                }
                preorderCell.classList.remove('invisible');
                preorderEtaCell.classList.toggle('invisible', !preorderToggle.checked);
            }

            productSelect.addEventListener('change', () => {
                if (productSelect.value) {
                    const selected = productSelect.options[productSelect.selectedIndex];
                    priceInput.value = selected.dataset.price;
                    const allowPreorder = selected.dataset.allowPreorder === '1';
                    const defaultEta = selected.dataset.preorderEta || '';
                    preorderToggle.disabled = !allowPreorder;
                    if (!allowPreorder) {
                        preorderToggle.checked = false;
                        preorderEta.value = '';
                        preorderEta.disabled = true;
                    } else {
                        preorderEta.value = defaultEta;
                        preorderEta.disabled = !preorderToggle.checked;
                    }
                    updatePreorderVisibility(allowPreorder);
                }
                updateTotal();
                updateWeightTotal();
            });

            preorderToggle?.addEventListener('change', () => {
                preorderEta.disabled = !preorderToggle.checked;
                if (!preorderToggle.checked) {
                    preorderEta.value = '';
                } else if (!preorderEta.value && productSelect.value) {
                    const selected = productSelect.options[productSelect.selectedIndex];
                    preorderEta.value = selected.dataset.preorderEta || '';
                }
                updatePreorderVisibility(preorderToggle.disabled === false);
            });

            quantityInput.addEventListener('change', () => {
                updateTotal();
                updateWeightTotal();
            });
            removeBtn.addEventListener('click', () => {
                row.remove();
                updateTotal();
                updateWeightTotal();
            });

            if (productSelect.value) {
                productSelect.dispatchEvent(new Event('change'));
            } else {
                updatePreorderVisibility(false);
            }
        }

        addItemBtn.addEventListener('click', () => {
            const newRow = createItemRow();
            itemsContainer.appendChild(newRow);
            attachItemListeners(newRow);
            updateTotal();
            updateWeightTotal();
        });

        document.querySelectorAll('.item-row').forEach(attachItemListeners);
        document.getElementById('shipping_cost')?.addEventListener('input', updateTotal);
        updateTotal();
        updateWeightTotal();
    </script>
</x-app-layout>
