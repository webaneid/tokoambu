<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Catat Transaksi Keuangan</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form action="{{ route('ledger.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe</label>
                        <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="income">Pemasukan</option>
                            <option value="expense">Pengeluaran</option>
                        </select>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" id="category-search" autocomplete="off" placeholder="Ketik untuk mencari kategori..." class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                <input type="hidden" id="category-id" name="category_id" value="{{ old('category_id') }}">
                                <div id="category-dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                            </div>
                            <button type="button" id="btn_add_category" class="px-3 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover text-sm">+ Kategori</button>
                        </div>
                        @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                        <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('entry_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3" required>{{ old('description') }}</textarea>
                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                        <input type="number" name="amount" min="0.01" step="0.01" value="{{ old('amount') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                        <p class="text-xs text-gray-500 mt-1">Pilih cara bayar (opsional).</p>
                        @error('payment_method') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Diberikan/Diterima dari</label>
                        <select id="recipientType" name="recipient_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Pilih Jenis</option>
                            <option value="supplier" {{ old('recipient_type') == 'supplier' ? 'selected' : '' }}>Supplier</option>
                            <option value="vendor" {{ old('recipient_type') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                            <option value="customer" {{ old('recipient_type') == 'customer' ? 'selected' : '' }}>Customer</option>
                            <option value="employee" {{ old('recipient_type') == 'employee' ? 'selected' : '' }}>Karyawan</option>
                        </select>
                        @error('recipient_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="recipientAccountGroup" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Penerima</label>
                        <select id="recipientAccountSelect" name="recipient_bank_account_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Pilih Penerima</option>
                        </select>
                        @error('recipient_bank_account_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                        <p class="text-xs text-gray-500 mt-1">Rekening toko yang digunakan untuk transaksi ini.</p>
                        @error('shop_bank_account_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="transferFeeGroup" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Biaya Transfer</label>
                        <input type="number" id="transferFee" name="transfer_fee" min="0" step="0.01" value="{{ old('transfer_fee', 0) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <p class="text-xs text-gray-500 mt-1">Hanya diisi jika transfer antar bank memerlukan biaya transfer.</p>
                        @error('transfer_fee') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Transfer/Kwitansi</label>
                        <input type="hidden" id="paymentMediaId" name="payment_media_id" value="{{ old('payment_media_id') }}">
                        <div class="flex items-center gap-2">
                            <button type="button" id="btnSelectMedia" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Pilih dari Library</button>
                            <span id="selectedMediaLabel" class="text-sm text-gray-600">Belum dipilih</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Upload bukti transaksi (opsional tapi disarankan).</p>
                        @error('payment_media_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                        <textarea name="notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="2">{{ old('notes') }}</textarea>
                        @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3 pt-6">
                        <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover transition-colors">
                            Simpan Transaksi
                        </button>
                        <a href="{{ route('ledger.index') }}" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 text-center">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Modal Tambah Kategori Keuangan -->
<div id="modal_add_category" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
        <h4 class="text-lg font-semibold mb-4">Tambah Kategori Keuangan</h4>
        <form id="form_add_fin_category" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori</label>
                <input type="text" id="fin_category_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe</label>
                <select id="fin_category_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="income">Pemasukan</option>
                    <option value="expense">Pengeluaran</option>
                </select>
            </div>
            <p id="error_fin_category" class="text-red-500 text-sm hidden"></p>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" id="btn_close_fin_modal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/autocomplete.js') }}"></script>
<script>
    (() => {
        const allCategories = @json($categories);
        const typeSelect = document.querySelector('select[name="type"]');

        // Function to filter categories by type
        function filterCategoriesByType(type) {
            if (!type) return allCategories;
            return allCategories.filter(cat => cat.type === type);
        }

        // Initialize category autocomplete with filtered data
        const categoryAutocomplete = new Autocomplete({
            inputId: 'category-search',
            hiddenInputId: 'category-id',
            dropdownId: 'category-dropdown',
            data: filterCategoriesByType(typeSelect?.value || 'expense'),
            searchFields: ['name'],
            displayTemplate: (category) => {
                return `<div class="font-medium">${category.name}</div>`;
            },
            maxItems: 15,
            onSelect: (category) => {
                console.log('Category selected:', category);
            }
        });

        // Update category autocomplete when type changes
        typeSelect?.addEventListener('change', (e) => {
            const filteredCategories = filterCategoriesByType(e.target.value);
            categoryAutocomplete.updateData(filteredCategories);
            categoryAutocomplete.reset();
        });

        // Modal handling for add category
        const addBtn = document.getElementById('btn_add_category');
        const modal = document.getElementById('modal_add_category');
        const modalForm = document.getElementById('form_add_fin_category');
        const modalError = document.getElementById('error_fin_category');
        const modalName = document.getElementById('fin_category_name');
        const modalType = document.getElementById('fin_category_type');
        const closeModalBtn = document.getElementById('btn_close_fin_modal');

        function openModal() {
            modal.classList.remove('hidden');
            modalName.value = '';
            modalType.value = 'income';
            modalError.classList.add('hidden');
            modalName.focus();
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        addBtn?.addEventListener('click', openModal);
        closeModalBtn?.addEventListener('click', closeModal);
        modal?.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        modalForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            modalError.classList.add('hidden');

            const payload = {
                name: modalName.value.trim(),
                type: modalType.value,
                _token: '{{ csrf_token() }}'
            };

            if (!payload.name) {
                modalError.textContent = 'Nama kategori wajib diisi';
                modalError.classList.remove('hidden');
                return;
            }

            try {
                const res = await fetch('{{ route('financial-categories.store') }}', {
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
                    const msg = data.message || 'Gagal menambah kategori';
                    modalError.textContent = msg;
                    modalError.classList.remove('hidden');
                    return;
                }

                const cat = data.category;
                allCategories.push(cat);

                // Update autocomplete with filtered data based on current type
                const currentType = typeSelect?.value || 'expense';
                const filteredCategories = filterCategoriesByType(currentType);
                categoryAutocomplete.updateData(filteredCategories);
                document.getElementById('category-id').value = cat.id;
                document.getElementById('category-search').value = cat.name;
                categoryAutocomplete.hide();

                closeModal();
            } catch (error) {
                modalError.textContent = 'Terjadi kesalahan, coba lagi.';
                modalError.classList.remove('hidden');
            }
        });
    })();

    // Recipient type and bank account selector
    (() => {
        const recipientType = document.getElementById('recipientType');
        const recipientAccountGroup = document.getElementById('recipientAccountGroup');
        const recipientAccountSelect = document.getElementById('recipientAccountSelect');

        const bankAccountsByType = {
            supplier: @json($supplierBankAccounts->map(fn($acc) => [
                'id' => $acc->id,
                'name' => $acc->supplier->name ?? '-'
            ])),
            vendor: @json($vendorBankAccounts->map(fn($acc) => [
                'id' => $acc->id,
                'name' => $acc->vendor->name ?? '-'
            ])),
            customer: @json($customerBankAccounts->map(fn($acc) => [
                'id' => $acc->id,
                'name' => $acc->customer->name ?? '-'
            ])),
            employee: @json($employeeBankAccounts->map(fn($acc) => [
                'id' => $acc->id,
                'name' => $acc->employee->name ?? '-'
            ]))
        };

        function updateRecipientAccounts(type) {
            recipientAccountSelect.innerHTML = '<option value="">Pilih Penerima</option>';

            if (!type || !bankAccountsByType[type]) {
                recipientAccountGroup.classList.add('hidden');
                return;
            }

            const accounts = bankAccountsByType[type];
            if (accounts.length === 0) {
                recipientAccountSelect.innerHTML = '<option value="">Belum ada rekening terdaftar</option>';
                recipientAccountGroup.classList.remove('hidden');
                return;
            }

            accounts.forEach(acc => {
                const option = document.createElement('option');
                option.value = acc.id;
                option.textContent = acc.name;
                recipientAccountSelect.appendChild(option);
            });

            recipientAccountGroup.classList.remove('hidden');
        }

        recipientType?.addEventListener('change', (e) => {
            updateRecipientAccounts(e.target.value);
        });

        // Initial state
        if (recipientType?.value) {
            updateRecipientAccounts(recipientType.value);
        }
    })();

    // Payment method dynamic fields
    (() => {
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
                    title: 'Pilih Bukti Transfer',
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
    })();
</script>
<script src="{{ asset('js/media-picker.js') }}"></script>
