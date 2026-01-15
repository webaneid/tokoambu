<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Catat Pembayaran</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                <form action="{{ route('payments.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                        <div class="relative">
                            <input
                                type="text"
                                id="orderSearch"
                                placeholder="Cari ID order atau nama customer"
                                class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                                value="{{ $selectedOrder ? ($selectedOrder->order_number . ' - ' . ($selectedOrder->customer->name ?? '')) : '' }}"
                                autocomplete="off"
                                required
                            >
                            <input type="hidden" name="order_id" id="order_id" value="{{ $selectedOrder?->id }}">
                            <div
                                id="orderResults"
                                class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-sm max-h-60 overflow-auto hidden"
                            ></div>
                        </div>
                        @error('order_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if($selectedOrder)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600">Total Order</p>
                                    <p class="text-lg font-bold text-gray-900">Rp {{ number_format($selectedOrder->total_amount, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Sudah Dibayar</p>
                                    <p class="text-lg font-bold text-green-600">Rp {{ number_format($selectedOrder->payments()->where('status', 'verified')->sum('amount'), 0, ',', '.') }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-gray-600">Sisa Pembayaran</p>
                                    <p class="text-lg font-bold text-orange-600">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pembayaran</label>
                        <input type="number" name="amount" id="amountInput" min="0.01" step="0.01" value="{{ old('amount') ?? ($remainingAmount ?? '') }}" required class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p id="amountError" class="text-red-500 text-xs mt-1 hidden"></p>
                        @if($selectedOrder)
                            <p class="text-xs text-gray-500 mt-2">Otomatis terisi dengan sisa pembayaran: Rp {{ number_format($remainingAmount, 0, ',', '.') }}</p>
                            @if($minimumDp)
                                <p class="text-xs text-orange-600 font-medium mt-1">Minimal transfer {{ \App\Models\Setting::getPreorderDpPercentage() }}% (Rp {{ number_format($minimumDp, 0, ',', '.') }})</p>
                            @endif
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Pembayaran</label>
                        <select name="method" id="methodSelect" required class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            <option value="transfer" {{ old('method', 'transfer') === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                            <option value="cash" {{ old('method') === 'cash' ? 'selected' : '' }}>Tunai</option>
                            <option value="check" {{ old('method') === 'check' ? 'selected' : '' }}>Cek</option>
                            <option value="other" {{ old('method') === 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('method') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="transferFields" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pengirim (sesuai rekening)</label>
                            <input type="text" name="sender_name" value="{{ old('sender_name') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Nama di rekening bank">
                            @error('sender_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Pengirim</label>
                            <input type="text" name="sender_bank" value="{{ old('sender_bank') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Contoh: BCA, Mandiri, BNI">
                            @error('sender_bank') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Transfer ke Rekening Toko</label>
                            <select name="shop_bank_account_id" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                <option value="">Pilih Rekening Toko</option>
                                @foreach($shopBankAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('shop_bank_account_id') == $account->id ? 'selected' : '' }}>{{ $account->bank_name }} - {{ $account->account_number }} ({{ $account->account_name }})</option>
                                @endforeach
                            </select>
                            @error('shop_bank_account_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bukti Pembayaran</label>
                        <input type="hidden" name="payment_media_id" id="payment_media_id">
                        <div class="flex flex-wrap items-center gap-3">
                            <button type="button" id="openProofPicker" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                Pilih / Upload Bukti
                            </button>
                            <span id="selectedProofLabel" class="text-sm text-gray-600">Belum ada bukti dipilih</span>
                        </div>
                        @error('payment_media_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" rows="3"></textarea>
                    </div>

                    <div class="flex gap-3 pt-6">
                        <button type="submit" class="flex-1 bg-primary text-white h-10 px-4 rounded-lg hover:bg-primary-hover transition">
                            Simpan Pembayaran
                        </button>
                        <a href="{{ route('payments.index') }}" class="flex-1 bg-gray-300 text-gray-700 h-10 px-4 rounded-lg hover:bg-gray-400 text-center flex items-center justify-center">
                            Batal
                        </a>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/media-picker.js') }}"></script>
    <script>
        const orders = @json($orderOptions);
        const orderSearch = document.getElementById('orderSearch');
        const orderResults = document.getElementById('orderResults');
        const orderIdInput = document.getElementById('order_id');
        const amountInput = document.getElementById('amountInput');
        const minimumDp = {{ $minimumDp ?? 'null' }};
        const dpPercentage = {{ \App\Models\Setting::getPreorderDpPercentage() }};

        const renderResults = (items) => {
            orderResults.innerHTML = '';
            if (!items.length) {
                orderResults.classList.add('hidden');
                return;
            }
            items.forEach((item) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50';
                button.textContent = item.label;
                button.addEventListener('click', () => {
                    orderSearch.value = item.label;
                    orderIdInput.value = item.id;
                    amountInput.value = item.remaining;
                    orderResults.classList.add('hidden');
                    window.location.href = `{{ route('payments.create') }}?order_id=${item.id}`;
                });
                orderResults.appendChild(button);
            });
            orderResults.classList.remove('hidden');
        };

        orderSearch.addEventListener('input', (event) => {
            const keyword = event.target.value.trim().toLowerCase();
            if (!keyword) {
                orderResults.classList.add('hidden');
                orderIdInput.value = '';
                return;
            }
            const filtered = orders
                .filter((item) => item.search.includes(keyword))
                .slice(0, 8);
            renderResults(filtered);
        });

        orderSearch.addEventListener('focus', () => {
            if (orderSearch.value.trim()) {
                const keyword = orderSearch.value.trim().toLowerCase();
                const filtered = orders
                    .filter((item) => item.search.includes(keyword))
                    .slice(0, 8);
                renderResults(filtered);
            }
        });

        document.addEventListener('click', (event) => {
            if (!orderResults.contains(event.target) && event.target !== orderSearch) {
                orderResults.classList.add('hidden');
            }
        });

        const proofButton = document.getElementById('openProofPicker');
        const proofInput = document.getElementById('payment_media_id');
        const proofLabel = document.getElementById('selectedProofLabel');

        proofButton?.addEventListener('click', () => {
            openMediaPicker({
                type: 'payment_proof',
                title: 'Pilih Bukti Transfer',
                listUrl: '{{ route('media.payment_proof.list') }}',
                uploadUrl: '{{ route('media.store') }}',
                onSelect: (item) => {
                    proofInput.value = item.id;
                    proofLabel.textContent = item.filename;
                },
            });
        });

        // Real-time validation for minimum DP
        const amountError = document.getElementById('amountError');

        function validateAmount() {
            if (minimumDp !== null) {
                const amount = parseFloat(amountInput.value);
                if (amount && amount < minimumDp) {
                    amountError.textContent = `Minimal transfer ${dpPercentage}% yaitu Rp ${minimumDp.toLocaleString('id-ID')}`;
                    amountError.classList.remove('hidden');
                    amountInput.classList.add('border-red-500');
                    amountInput.classList.remove('border-gray-300');
                    return false;
                } else {
                    amountError.classList.add('hidden');
                    amountInput.classList.remove('border-red-500');
                    amountInput.classList.add('border-gray-300');
                    return true;
                }
            }
            return true;
        }

        // Validate on input change
        amountInput?.addEventListener('input', validateAmount);
        amountInput?.addEventListener('blur', validateAmount);

        // Validate before form submission
        const paymentForm = document.querySelector('form');
        paymentForm?.addEventListener('submit', (e) => {
            if (!validateAmount()) {
                e.preventDefault();
                alert(`Minimal transfer ${dpPercentage}% yaitu Rp ${minimumDp.toLocaleString('id-ID')}`);
                amountInput.focus();
            }
        });

        // Toggle transfer fields visibility
        const methodSelect = document.getElementById('methodSelect');
        const transferFields = document.getElementById('transferFields');

        function toggleTransferFields() {
            if (methodSelect.value === 'transfer') {
                transferFields.classList.remove('hidden');
            } else {
                transferFields.classList.add('hidden');
            }
        }

        methodSelect?.addEventListener('change', toggleTransferFields);
        toggleTransferFields(); // Initial check
    </script>
</x-app-layout>
