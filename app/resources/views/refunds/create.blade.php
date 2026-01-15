<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('refunds.index') }}" class="text-blue-600 hover:underline">Refund</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-900">Form Refund</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('refunds.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Order <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="order-search"
                                    autocomplete="off"
                                    placeholder="Ketik untuk mencari order..."
                                    class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('order_id') border-red-500 @enderror"
                                >
                                <input type="hidden" id="order-id" name="order_id" value="{{ old('order_id') }}">
                                <input type="hidden" id="order-customer-id" value="">
                                <div id="order-dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                            </div>
                            @error('order_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                            <input type="text" id="customer-name" value="{{ old('customer_name') }}" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600" placeholder="Pilih order terlebih dahulu">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Refund <span class="text-red-500">*</span></label>
                            <input type="number" id="amount" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary @error('amount') border-red-500 @enderror" placeholder="0">
                            @error('amount')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                            <select id="paymentMethod" name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">Pilih Metode</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="debit" {{ old('payment_method') == 'debit' ? 'selected' : '' }}>Debit</option>
                                <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                <option value="qris" {{ old('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Pilih cara pembayaran refund (opsional).</p>
                            @error('payment_method')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="customerBankGroup" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rekening Customer</label>
                            <div class="flex gap-2">
                                <select id="customerBankSelect" name="customer_bank_account_id" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="">Pilih Rekening Customer</option>
                                </select>
                                <button type="button" id="btnAddCustomerBank" class="px-3 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover text-sm">+ Rekening</button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Rekening customer yang akan menerima refund.</p>
                            @error('customer_bank_account_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="shopBankGroup" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rekening Toko</label>
                            <select id="shopBankSelect" name="shop_bank_account_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">Pilih Rekening Toko</option>
                                @foreach($userBankAccounts as $acc)
                                    <option value="{{ $acc->id }}" {{ old('shop_bank_account_id') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->bank_name }} - {{ $acc->account_number }} ({{ $acc->account_name }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Rekening toko yang digunakan untuk refund ini.</p>
                            @error('shop_bank_account_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="transferFeeGroup" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Biaya Transfer</label>
                            <input type="number" id="transferFee" name="transfer_fee" min="0" step="0.01" value="{{ old('transfer_fee', 0) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <p class="text-xs text-gray-500 mt-1">Hanya diisi jika transfer antar bank memerlukan biaya transfer.</p>
                            @error('transfer_fee')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Transfer/Kwitansi</label>
                            <input type="hidden" id="paymentMediaId" name="payment_media_id" value="{{ old('payment_media_id') }}">
                            <div class="flex items-center gap-2">
                                <button type="button" id="btnSelectMedia" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Pilih dari Library</button>
                                <span id="selectedMediaLabel" class="text-sm text-gray-600">Belum dipilih</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Upload bukti transaksi refund (opsional tapi disarankan).</p>
                            @error('payment_media_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Alasan Refund <span class="text-red-500">*</span></label>
                            <input type="text" id="reason" name="reason" value="{{ old('reason') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary @error('reason') border-red-500 @enderror" placeholder="Alasan refund">
                            @error('reason')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                            <textarea id="notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary @error('notes') border-red-500 @enderror" placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('refunds.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Batal</a>
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Modal Tambah Rekening Customer -->
<div id="modalAddCustomerBank" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
        <h4 class="text-lg font-semibold mb-4">Tambah Rekening Customer</h4>
        <form id="formAddCustomerBank" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Bank <span class="text-red-500">*</span></label>
                <input type="text" id="customerBankName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required placeholder="Contoh: BCA, Mandiri, BRI">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Rekening <span class="text-red-500">*</span></label>
                <input type="text" id="customerAccountNumber" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required placeholder="Nomor rekening">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pemilik Rekening <span class="text-red-500">*</span></label>
                <input type="text" id="customerAccountName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required placeholder="Nama sesuai rekening">
            </div>
            <p id="errorCustomerBank" class="text-red-500 text-sm hidden"></p>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" id="btnCloseCustomerBankModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/autocomplete.js') }}"></script>
<script>
    (() => {
        const orders = @json($orders);
        const customerBankAccountsAll = @json($customerBankAccounts);
        const preselectedOrderId = @json($preselectedOrderId);

        // Order autocomplete
        const orderAutocomplete = new Autocomplete({
            inputId: 'order-search',
            hiddenInputId: 'order-id',
            dropdownId: 'order-dropdown',
            data: orders,
            searchFields: ['order_number', 'customer_name'],
            displayTemplate: (order) => {
                return `
                    <div class="font-medium">${order.order_number}</div>
                    <div class="text-xs text-gray-500">${order.customer_name} - Rp ${order.paid_amount_formatted}</div>
                `;
            },
            maxItems: 10,
            onSelect: (order) => {
                // Auto-fill amount with paid amount
                document.getElementById('amount').value = order.paid_amount;

                // Set customer name
                document.getElementById('customer-name').value = order.customer_name;

                // Store customer ID for filtering bank accounts
                document.getElementById('order-customer-id').value = order.customer_id;

                // Update customer bank accounts if payment method is selected
                const paymentMethod = document.getElementById('paymentMethod').value;
                if (paymentMethod) {
                    updateCustomerBankAccounts(order.customer_id);
                }
            }
        });

        // Auto-select order if preselectedOrderId is provided
        if (preselectedOrderId) {
            const selectedOrder = orders.find(o => o.id == preselectedOrderId);
            if (selectedOrder) {
                document.getElementById('order-id').value = selectedOrder.id;
                document.getElementById('order-search').value = selectedOrder.order_number;
                document.getElementById('amount').value = selectedOrder.paid_amount;
                document.getElementById('customer-name').value = selectedOrder.customer_name;
                document.getElementById('order-customer-id').value = selectedOrder.customer_id;
            }
        }

        // Customer bank account filtering
        const customerBankGroup = document.getElementById('customerBankGroup');
        const customerBankSelect = document.getElementById('customerBankSelect');

        function updateCustomerBankAccounts(customerId) {
            customerBankSelect.innerHTML = '<option value="">Pilih Rekening Customer</option>';

            if (!customerId) {
                customerBankGroup.classList.add('hidden');
                return;
            }

            const accounts = customerBankAccountsAll.filter(acc => acc.customer_id == customerId);
            if (accounts.length === 0) {
                customerBankSelect.innerHTML = '<option value="">Belum ada rekening customer terdaftar</option>';
                customerBankGroup.classList.remove('hidden');
                return;
            }

            accounts.forEach(acc => {
                const option = document.createElement('option');
                option.value = acc.id;
                option.textContent = `${acc.bank_name} - ${acc.account_number} (${acc.account_name})`;
                customerBankSelect.appendChild(option);
            });

            customerBankGroup.classList.remove('hidden');
        }

        // Payment method dynamic fields
        const paymentMethod = document.getElementById('paymentMethod');
        const shopBankGroup = document.getElementById('shopBankGroup');
        const transferFeeGroup = document.getElementById('transferFeeGroup');
        const btnSelectMedia = document.getElementById('btnSelectMedia');
        const paymentMediaId = document.getElementById('paymentMediaId');
        const selectedMediaLabel = document.getElementById('selectedMediaLabel');

        const METHODS_NEED_BANK = ['debit', 'credit_card', 'transfer', 'qris'];

        function togglePaymentFields(method) {
            const showFields = METHODS_NEED_BANK.includes(method);
            shopBankGroup?.classList.toggle('hidden', !showFields);
            transferFeeGroup?.classList.toggle('hidden', !showFields);

            // Show customer bank only if method needs bank AND order is selected
            const customerId = document.getElementById('order-customer-id').value;
            if (showFields && customerId) {
                updateCustomerBankAccounts(customerId);
            } else {
                customerBankGroup?.classList.add('hidden');
            }
        }

        paymentMethod?.addEventListener('change', (e) => {
            togglePaymentFields(e.target.value);
        });

        // Initial state
        if (paymentMethod?.value) {
            togglePaymentFields(paymentMethod.value);
        }

        // Media picker integration
        btnSelectMedia?.addEventListener('click', () => {
            if (typeof openMediaPicker === 'function') {
                openMediaPicker({
                    type: 'payment_proof',
                    title: 'Pilih Bukti Refund',
                    listUrl: '{{ route('media.payment_proof.list') }}',
                    uploadUrl: '{{ route('media.store') }}',
                    context: {},
                    onSelect: (item) => {
                        paymentMediaId.value = item.id;
                        selectedMediaLabel.textContent = item.filename;
                    },
                });
            } else {
                alert('Media picker belum tersedia. Pastikan media-picker.js sudah dimuat.');
            }
        });

        // Restore selected media label if old value exists
        if (paymentMediaId?.value) {
            selectedMediaLabel.textContent = 'File terpilih (ID: ' + paymentMediaId.value + ')';
        }

        // Modal customer bank account handling
        const btnAddCustomerBank = document.getElementById('btnAddCustomerBank');
        const modalAddCustomerBank = document.getElementById('modalAddCustomerBank');
        const formAddCustomerBank = document.getElementById('formAddCustomerBank');
        const errorCustomerBank = document.getElementById('errorCustomerBank');
        const customerBankName = document.getElementById('customerBankName');
        const customerAccountNumber = document.getElementById('customerAccountNumber');
        const customerAccountName = document.getElementById('customerAccountName');
        const btnCloseCustomerBankModal = document.getElementById('btnCloseCustomerBankModal');

        function openCustomerBankModal() {
            const customerId = document.getElementById('order-customer-id').value;
            if (!customerId) {
                alert('Pilih order terlebih dahulu');
                return;
            }

            modalAddCustomerBank.classList.remove('hidden');
            customerBankName.value = '';
            customerAccountNumber.value = '';
            customerAccountName.value = '';
            errorCustomerBank.classList.add('hidden');
            customerBankName.focus();
        }

        function closeCustomerBankModal() {
            modalAddCustomerBank.classList.add('hidden');
        }

        btnAddCustomerBank?.addEventListener('click', openCustomerBankModal);
        btnCloseCustomerBankModal?.addEventListener('click', closeCustomerBankModal);
        modalAddCustomerBank?.addEventListener('click', (e) => {
            if (e.target === modalAddCustomerBank) closeCustomerBankModal();
        });

        formAddCustomerBank?.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorCustomerBank.classList.add('hidden');

            const customerId = document.getElementById('order-customer-id').value;
            if (!customerId) {
                errorCustomerBank.textContent = 'Pilih order terlebih dahulu';
                errorCustomerBank.classList.remove('hidden');
                return;
            }

            const payload = {
                customer_id: customerId,
                bank_name: customerBankName.value.trim(),
                account_number: customerAccountNumber.value.trim(),
                account_name: customerAccountName.value.trim(),
                _token: '{{ csrf_token() }}'
            };

            if (!payload.bank_name || !payload.account_number || !payload.account_name) {
                errorCustomerBank.textContent = 'Semua field wajib diisi';
                errorCustomerBank.classList.remove('hidden');
                return;
            }

            try {
                const res = await fetch('{{ route('api.bank-accounts.store') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': payload._token,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();
                if (!res.ok) {
                    const msg = data.message || 'Gagal menambah rekening customer';
                    errorCustomerBank.textContent = msg;
                    errorCustomerBank.classList.remove('hidden');
                    return;
                }

                // Add to customerBankAccountsAll array
                const newBank = data.bank_account;
                customerBankAccountsAll.push(newBank);

                // Update dropdown
                updateCustomerBankAccounts(customerId);

                // Select the newly added bank account
                customerBankSelect.value = newBank.id;

                closeCustomerBankModal();
            } catch (error) {
                errorCustomerBank.textContent = 'Terjadi kesalahan, coba lagi.';
                errorCustomerBank.classList.remove('hidden');
            }
        });
    })();
</script>
<script src="{{ asset('js/media-picker.js') }}"></script>
