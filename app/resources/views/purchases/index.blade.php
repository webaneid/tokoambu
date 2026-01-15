<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">Daftar Pembelian</h2>
            <a href="{{ route('purchases.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover transition-colors">
                + Buat Pembelian
            </a>
        </div>
    </x-slot>

    @php
        $purchasePayloads = $purchases->mapWithKeys(function($p) {
            $paid = $p->payments->where('status', 'verified')->sum('amount');
            $remaining = max(($p->total_amount ?? 0) - $paid, 0);
            return [$p->id => [
                'id' => $p->id,
                'po' => $p->purchase_number,
                'supplier' => [
                    'id' => optional($p->supplier)->id,
                    'name' => optional($p->supplier)->name ?? '-',
                    'edit_url' => $p->supplier ? route('suppliers.edit', $p->supplier) : null,
                    'banks' => $p->supplier && $p->supplier->bankAccounts
                        ? $p->supplier->bankAccounts->map(fn($b) => [
                            'id' => $b->id,
                            'bank_name' => $b->bank_name,
                            'bank_code' => $b->bank_code,
                            'account_number' => $b->account_number,
                            'account_name' => $b->account_name,
                        ])->values()->all()
                        : [],
                ],
                'items' => $p->items->map(fn($item) => [
                    'product' => optional($item->product)->name ?? '-',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ])->values()->all(),
                'total' => $p->total_amount,
                'paid' => $paid,
                'remaining' => $remaining,
            ]];
        })->toArray();
        $userBankPayloads = $userBankAccounts->map(function($b) {
            return [
                'id' => $b->id,
                'bank_name' => $b->bank_name,
                'bank_code' => $b->bank_code,
                'account_number' => $b->account_number,
                'account_name' => $b->account_name,
            ];
        })->values();
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-3 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('purchases.index') }}">
                        <div class="flex flex-wrap items-end gap-4 md:flex-nowrap">
                            <div class="w-full md:flex-1">
                                <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                                <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="No. PO atau supplier" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            </div>
                            <div class="flex items-end gap-2 md:flex-none pt-6">
                                <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Filter</button>
                                <a href="{{ route('purchases.index') }}" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center flex items-center">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($purchases->count())
                    @php
                        $currentSort = request('sort', 'purchase_number');
                        $currentDirection = request('direction', 'asc');
                        $sortUrl = function (string $column) use ($currentSort, $currentDirection) {
                            $direction = $currentSort === $column && $currentDirection === 'asc' ? 'desc' : 'asc';
                            return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $direction]);
                        };
                        $sortIndicator = function (string $column) use ($currentSort, $currentDirection) {
                            if ($currentSort !== $column) {
                                return '';
                            }
                            return $currentDirection === 'asc' ? '▲' : '▼';
                        };
                    @endphp
                    <div class="block sm:hidden divide-y divide-gray-200">
                        @foreach($purchases as $purchase)
                            @php
                                $status = $purchase->status;
                                $statusClass = [
                                    'received' => 'bg-green-100 text-green-800',
                                    'ordered' => 'bg-blue-100 text-blue-800',
                                    'draft' => 'bg-yellow-100 text-yellow-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ][$status] ?? 'bg-gray-100 text-gray-800';
                                $statusLabel = [
                                    'received' => 'Diterima',
                                    'ordered' => 'Dipesan',
                                    'draft' => 'Draft',
                                    'cancelled' => 'Dibatalkan',
                                ][$status] ?? ucfirst($status);
                                $paid = $purchase->payment_status ?? 'pending';
                                $paidClass = $paid === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                            @endphp
                            <div class="p-4 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $purchase->purchase_number }}</p>
                                        <p class="text-xs text-gray-500">{{ $purchase->created_at->format('d M Y') }}</p>
                                    </div>
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                    <span class="font-medium text-gray-900">{{ $purchase->supplier->name ?? '-' }}</span>
                                    <span class="text-gray-300">•</span>
                                    <span class="text-gray-700">Total {{ 'Rp ' . number_format($purchase->total_amount, 0, ',', '.') }}</span>
                                </div>
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $paidClass }}">
                                    {{ $paid === 'paid' ? 'Dibayar' : 'Blm Dibayar' }}
                                </span>
                                <div class="flex flex-wrap items-center gap-3 pt-2 text-sm">
                                    <a href="{{ route('warehouse.receiving.index', ['q' => $purchase->purchase_number]) }}" class="text-blue hover:text-blue-light">Terima</a>
                                    @if($paid !== 'paid')
                                        <button type="button" class="text-blue hover:text-blue-light" onclick="openPaymentModal('{{ $purchase->id }}', '{{ $purchase->purchase_number }}')">Bayar</button>
                                    @else
                                        <span class="text-gray-400">Paid</span>
                                    @endif
                                    <a href="{{ route('purchases.show', $purchase) }}" class="text-blue hover:text-blue-light" title="Detail">
                                        <x-heroicon name="eye" class="w-4 h-4" />
                                    </a>
                                    <a href="{{ route('purchases.edit', $purchase) }}" class="text-blue hover:text-blue-light" title="Edit">
                                        <x-heroicon name="pencil-square" class="w-4 h-4" />
                                    </a>
                                    <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700" title="Hapus">
                                            <x-heroicon name="trash" class="w-4 h-4" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('purchase_number') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        No. PO <span class="text-[8px] leading-none">{{ $sortIndicator('purchase_number') }}</span>
                                    </a>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('supplier') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Supplier <span class="text-[8px] leading-none">{{ $sortIndicator('supplier') }}</span>
                                    </a>
                                </th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('total_amount') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Total <span class="text-[8px] leading-none">{{ $sortIndicator('total_amount') }}</span>
                                    </a>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('status') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Status <span class="text-[8px] leading-none">{{ $sortIndicator('status') }}</span>
                                    </a>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('payment_status') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Payment <span class="text-[8px] leading-none">{{ $sortIndicator('payment_status') }}</span>
                                    </a>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('created_at') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Tanggal <span class="text-[8px] leading-none">{{ $sortIndicator('created_at') }}</span>
                                    </a>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($purchases as $purchase)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $purchase->purchase_number }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $purchase->supplier->name ?? '-' }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-right text-gray-900">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $status = $purchase->status;
                                            $statusClass = [
                                                'received' => 'bg-green-100 text-green-800',
                                                'ordered' => 'bg-blue-100 text-blue-800',
                                                'draft' => 'bg-yellow-100 text-yellow-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                            ][$status] ?? 'bg-gray-100 text-gray-800';
                                            $statusLabel = [
                                                'received' => 'Diterima',
                                                'ordered' => 'Dipesan',
                                                'draft' => 'Draft',
                                                'cancelled' => 'Dibatalkan',
                                            ][$status] ?? ucfirst($status);
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $paid = $purchase->payment_status ?? 'pending';
                                            $paidClass = $paid === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $paidClass }}">
                                            {{ $paid === 'paid' ? 'Dibayar' : 'Blm Dibayar' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $purchase->created_at->format('d M Y') }}</td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm">
                                        <div class="inline-flex items-center gap-3">
                                            <a href="{{ route('warehouse.receiving.index', ['q' => $purchase->purchase_number]) }}" class="text-blue hover:text-blue-light">Terima</a>
                                            @if(($purchase->payment_status ?? 'pending') !== 'paid')
                                                <button type="button" class="text-blue hover:text-blue-light" onclick="openPaymentModal('{{ $purchase->id }}', '{{ $purchase->purchase_number }}')">Bayar</button>
                                            @else
                                                <span class="text-gray-400 text-sm">Paid</span>
                                            @endif
                                            <a href="{{ route('purchases.show', $purchase) }}" class="text-blue hover:text-blue-light" title="Detail">
                                                <x-heroicon name="eye" class="w-4 h-4" />
                                            </a>
                                            <a href="{{ route('purchases.edit', $purchase) }}" class="text-blue hover:text-blue-light" title="Edit">
                                                <x-heroicon name="pencil-square" class="w-4 h-4" />
                                            </a>
                                            <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700" title="Hapus">
                                                    <x-heroicon name="trash" class="w-4 h-4" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-4 text-center text-sm text-gray-500">
                                        Belum ada pembelian
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">Belum ada pembelian</p>
                    </div>
                @endif
            </div>

            @if ($purchases->lastPage() > 1)
                @php
                    $currentPage = $purchases->currentPage();
                    $lastPage = $purchases->lastPage();
                    $pages = collect([
                        1,
                        2,
                        $currentPage - 1,
                        $currentPage,
                        $currentPage + 1,
                        $lastPage - 1,
                        $lastPage,
                    ])->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                        ->unique()
                        ->sort()
                        ->values();
                @endphp
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600">
                        <div>Total pembelian: {{ number_format($purchases->total()) }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $purchases->previousPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $purchases->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                               aria-label="Sebelumnya">
                                <x-heroicon name="chevron-left" class="w-4 h-4" />
                            </a>
                            @php $prev = null; @endphp
                            @foreach ($pages as $page)
                                @if ($prev && $page > $prev + 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $purchases->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </a>
                                @php $prev = $page; @endphp
                            @endforeach
                            <a href="{{ $purchases->nextPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $purchases->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
                               aria-label="Berikutnya">
                                <x-heroicon name="chevron-right" class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Pembayaran -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-start justify-center hidden z-50 p-4 overflow-y-auto">
        <div class="mt-6 bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 max-h-[80vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold" id="paymentTitle">Pembayaran</h3>
                <button onclick="closePaymentModal()" class="text-gray-500">&times;</button>
            </div>
            <div class="space-y-4">
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="flex justify-between text-sm text-gray-700">
                        <div>
                            <div class="font-semibold text-gray-900" id="paySupplierName">-</div>
                            <div class="text-gray-500" id="payPoNumber">-</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500">Total</div>
                            <div class="text-xl font-semibold text-gray-900" id="payTotal">Rp 0</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-xs font-semibold text-gray-600 mb-1">Item Pembelian</div>
                        <div id="payItems" class="space-y-1 max-h-32 overflow-y-auto text-sm text-gray-700"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pembayaran</label>
                    <input type="number" id="paymentAmount" min="0.01" step="0.01" class="w-full border rounded-lg px-3 py-2" placeholder="Masukkan nominal" />
                    <p class="text-xs text-gray-500 mt-1">Otomatis terisi sisa tagihan, bisa diedit untuk pembayaran parsial.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Transfer</label>
                    <input type="number" id="transferFee" min="0" step="0.01" class="w-full border rounded-lg px-3 py-2" placeholder="0" />
                    <p class="text-xs text-gray-500 mt-1">Hanya diisi jika transfer antar bank memerlukan biaya transfer.</p>
                </div>
                <div class="rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700">
                    Total dibayarkan: <span class="font-semibold" id="totalPaymentLabel">Rp 0</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                    <select id="paymentMethod" class="w-full border rounded-lg px-3 py-2">
                        <option value="cash">Cash</option>
                        <option value="debit">Debit</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="transfer">Transfer</option>
                        <option value="qris">QRIS</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pilih cara bayar.</p>
                </div>

                <div id="supplierAccountGroup" class="hidden bg-gray-50 rounded-lg p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <div class="text-sm font-semibold text-gray-800">Rekening Tujuan (Supplier)</div>
                            <div class="text-xs text-gray-500">Wajib untuk metode non-cash & bukan kartu kredit.</div>
                        </div>
                        <a id="addSupplierAccountLink" href="#" target="_blank" class="text-blue-600 text-sm hover:underline">Tambah Rekening Supplier</a>
                    </div>
                    <select id="supplierAccountSelect" class="w-full border rounded-lg px-3 py-2"></select>
                    <p id="supplierAccountEmpty" class="text-xs text-red-500 mt-1 hidden">Supplier belum punya rekening. Tambahkan dulu.</p>
                    <div class="pt-2 space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Lampirkan Bukti Transfer</label>
                        <div class="flex items-center gap-2">
                            <button type="button" class="px-3 py-2 border rounded-lg text-sm" onclick="openProofPicker()">Pilih dari Library</button>
                            <span id="selectedProofLabel" class="text-sm text-gray-600">Belum dipilih</span>
                        </div>
                    </div>
                </div>

                <div id="userAccountGroup" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rekening Asal</label>
                    <select id="userAccountSelect" class="w-full border rounded-lg px-3 py-2"></select>
                </div>

                <div id="transferDateGroup" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pembayaran</label>
                    <input type="date" id="paymentDate" class="w-full border rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea id="paymentNotes" rows="3" class="w-full border rounded-lg px-3 py-2" placeholder="Catatan pembayaran (opsional)"></textarea>
                </div>

                <div class="flex justify-end mt-6 space-x-2">
                    <button type="button" class="px-4 py-2 border rounded-lg text-gray-700" onclick="closePaymentModal()">Tutup</button>
                    <button type="button" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover" onclick="submitPayment()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/media-picker.js') }}"></script>
    <script>
        const paymentModal = document.getElementById('paymentModal');
        const paymentTitle = document.getElementById('paymentTitle');
        const paySupplierName = document.getElementById('paySupplierName');
        const payPoNumber = document.getElementById('payPoNumber');
        const payTotal = document.getElementById('payTotal');
        const paymentAmount = document.getElementById('paymentAmount');
        const transferFee = document.getElementById('transferFee');
        const totalPaymentLabel = document.getElementById('totalPaymentLabel');
        const payItems = document.getElementById('payItems');
        const paymentMethod = document.getElementById('paymentMethod');
        const supplierAccountGroup = document.getElementById('supplierAccountGroup');
        const supplierAccountSelect = document.getElementById('supplierAccountSelect');
        const supplierAccountEmpty = document.getElementById('supplierAccountEmpty');
        const addSupplierAccountLink = document.getElementById('addSupplierAccountLink');
        const userAccountGroup = document.getElementById('userAccountGroup');
        const userAccountSelect = document.getElementById('userAccountSelect');
        const paymentNotes = document.getElementById('paymentNotes');
        const transferDateGroup = document.getElementById('transferDateGroup');
        const paymentDate = document.getElementById('paymentDate');
        const purchaseData = @json($purchasePayloads);
        const userBankAccounts = @json($userBankPayloads);
        const METHODS_NEED_DEST = ['debit', 'transfer', 'qris'];
        const METHODS_NEED_ORIGIN = ['debit', 'credit_card', 'transfer', 'qris'];
        let currentPurchaseId = null;
        let selectedProofId = null;
        let basePaymentAmount = 0;
        const selectedProofLabel = document.getElementById('selectedProofLabel');

        function formatCurrency(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        }

        function updateTotalPaymentLabel() {
            const amount = Number(paymentAmount?.value || 0);
            const fee = Number(transferFee?.value || 0);
            if (totalPaymentLabel) {
                totalPaymentLabel.textContent = formatCurrency(amount + fee);
            }
        }

        function renderUserBankSelect() {
            userAccountSelect.innerHTML = '';
            if (!userBankAccounts.length) {
                userAccountSelect.innerHTML = '<option value="">Tidak ada rekening. Tambahkan di /settings</option>';
                return;
            }
            userBankAccounts.forEach(acc => {
                const opt = document.createElement('option');
                const bankLabel = [acc.bank_name, acc.bank_code].filter(Boolean).join(' ');
                opt.value = acc.id;
                opt.textContent = `${bankLabel} - ${acc.account_number} a.n ${acc.account_name}`;
                userAccountSelect.appendChild(opt);
            });
        }

        function renderSupplierAccounts(data) {
            supplierAccountSelect.innerHTML = '';
            supplierAccountEmpty.classList.add('hidden');
            addSupplierAccountLink.href = data.supplier.edit_url || '#';
            if (!data.supplier.banks || data.supplier.banks.length === 0) {
                supplierAccountEmpty.classList.remove('hidden');
                supplierAccountSelect.innerHTML = '<option value="">Belum ada rekening</option>';
                return;
            }
            data.supplier.banks.forEach(acc => {
                const opt = document.createElement('option');
                const bankLabel = [acc.bank_name, acc.bank_code].filter(Boolean).join(' ');
                opt.value = acc.id;
                opt.textContent = `${bankLabel} - ${acc.account_number} a.n ${acc.account_name}`;
                supplierAccountSelect.appendChild(opt);
            });
        }

        function renderItems(items) {
            payItems.innerHTML = '';
            if (!items || items.length === 0) {
                payItems.innerHTML = '<div class="text-gray-500 text-sm">Tidak ada item.</div>';
                return;
            }
            items.forEach(item => {
                const row = document.createElement('div');
                row.className = 'flex justify-between';
                row.innerHTML = `<div>${item.product} <span class="text-gray-500">x${item.quantity}</span></div><div class="font-medium">${formatCurrency(item.subtotal)}</div>`;
                payItems.appendChild(row);
            });
        }

        function toggleByMethod(method) {
            const showDest = METHODS_NEED_DEST.includes(method);
            const showOrigin = METHODS_NEED_ORIGIN.includes(method);
            supplierAccountGroup.classList.toggle('hidden', !showDest);
            userAccountGroup.classList.toggle('hidden', !showOrigin);
            transferDateGroup.classList.toggle('hidden', method === 'cash');
        }

        function openPaymentModal(purchaseId, poNumber) {
            const data = purchaseData[purchaseId];
            if (!data) return;
            currentPurchaseId = purchaseId;
            paymentMethod.value = 'transfer';
            paymentTitle.textContent = `Pembayaran ${poNumber}`;
            paySupplierName.textContent = data.supplier.name || '-';
            payPoNumber.textContent = data.po;
            payTotal.textContent = formatCurrency(data.total);
            if (paymentAmount) {
                basePaymentAmount = data.remaining || data.total || 0;
                paymentAmount.value = basePaymentAmount;
            }
            if (transferFee) {
                transferFee.value = 0;
            }
            updateTotalPaymentLabel();
            renderItems(data.items);
            renderUserBankSelect();
            renderSupplierAccounts(data);
            toggleByMethod(paymentMethod.value);
            if (paymentDate) {
                paymentDate.value = new Date().toISOString().slice(0, 10);
            }
            paymentModal.classList.remove('hidden');
        }

        function closePaymentModal() {
            paymentModal.classList.add('hidden');
        }

        paymentMethod?.addEventListener('change', () => toggleByMethod(paymentMethod.value));
        transferFee?.addEventListener('input', updateTotalPaymentLabel);
        paymentAmount?.addEventListener('input', updateTotalPaymentLabel);

        // Init user bank select state
        renderUserBankSelect();

        const payFromQuery = new URLSearchParams(window.location.search).get('pay');
        if (payFromQuery) {
            openPaymentModal(payFromQuery, purchaseData?.[payFromQuery]?.po || '');
        }

        async function submitPayment() {
            if (!currentPurchaseId) return;
            if (!paymentAmount.value || Number(paymentAmount.value) <= 0) {
                alert('Masukkan jumlah pembayaran > 0');
                return;
            }
            const payload = {
                amount: paymentAmount.value || null,
                transfer_fee: transferFee?.value || 0,
                payment_method: paymentMethod.value,
                payment_date: paymentDate.value,
                supplier_bank_account_id: supplierAccountGroup.classList.contains('hidden') ? null : supplierAccountSelect.value || null,
                payer_bank_account_id: userAccountGroup.classList.contains('hidden') ? null : userAccountSelect.value || null,
                notes: paymentNotes.value,
                payment_media_id: selectedProofId,
            };
            try {
                const res = await fetch(`/purchases/${currentPurchaseId}/pay`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify(payload),
                });
                if (res.ok) {
                    window.location.reload();
                    return;
                }
                const data = await res.json();
                alert(data.message || 'Gagal menyimpan pembayaran');
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat menyimpan pembayaran');
            }
        }

        // Override picker with shared helper (clean UI)
        function openProofPicker() {
            openMediaPicker({
                type: 'payment_proof',
                title: 'Pilih Bukti Transfer',
                listUrl: '{{ route('media.payment_proof.list') }}',
                uploadUrl: '{{ route('media.store') }}',
                context: currentPurchaseId ? { purchase_id: currentPurchaseId } : {},
                onSelect: (item) => {
                    selectedProofId = item.id;
                    selectedProofLabel.textContent = item.filename;
                },
            });
        }
    </script>
</x-app-layout>
