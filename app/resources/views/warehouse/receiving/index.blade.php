<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Receiving</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('warehouse.receiving.index') }}">
                        <div class="flex flex-wrap items-end gap-4 md:flex-nowrap">
                            <div class="w-full md:flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari PO / Supplier" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            </div>
                            <div class="flex items-end gap-2 md:flex-none pt-6">
                                <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Filter</button>
                                <a href="{{ route('warehouse.receiving.index') }}" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center flex items-center">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900">Purchase Orders</h3>
                </div>
                @if (session('success'))
                    <div class="mb-3 px-6">
                        <div class="p-3 rounded bg-green-100 border border-green-300 text-green-700 text-sm">
                        {{ session('success') }}
                        </div>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-3 px-6">
                        <div class="p-3 rounded bg-red-100 border border-red-300 text-red-700 text-sm">
                        {{ $errors->first() }}
                        </div>
                    </div>
                @endif
                @php
                    $currentSort = request('sort', 'created_at');
                    $currentDirection = request('direction', 'desc');
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
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('purchase_number') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        PO <span class="text-[8px] leading-none">{{ $sortIndicator('purchase_number') }}</span>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('supplier') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Supplier <span class="text-[8px] leading-none">{{ $sortIndicator('supplier') }}</span>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('status') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Status <span class="text-[8px] leading-none">{{ $sortIndicator('status') }}</span>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($purchases as $purchase)
                                @php
                                    $ordered = $purchase->items->sum('quantity');
                                    $received = ($receivedMap[$purchase->id] ?? collect())->sum();
                                    $remaining = $ordered - $received;
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $purchase->purchase_number }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $purchase->supplier->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $received }} / {{ $ordered }}</td>
                                    <td class="px-6 py-4 text-sm capitalize text-gray-500">
                                        {{ $remaining > 0 ? 'ordered' : 'received' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if ($remaining > 0)
                                            <div class="inline-flex items-center gap-3">
                                                <button
                                                    class="text-blue hover:text-blue-light"
                                                    onclick="openModal({{ $purchase->id }}, '{{ $purchase->purchase_number }}')">
                                                    Terima Barang
                                                </button>
                                                <button class="text-gray-600 hover:text-gray-900" onclick="openHistory({{ $purchase->id }}, '{{ $purchase->purchase_number }}')">History</button>
                                            </div>
                                        @else
                                            <div class="inline-flex items-center gap-3">
                                                <span class="text-gray-500 text-xs">Sudah diterima</span>
                                                <button class="text-gray-600 hover:text-gray-900" onclick="openHistory({{ $purchase->id }}, '{{ $purchase->purchase_number }}')">History</button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

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
                        <div>Total purchase order: {{ number_format($purchases->total()) }}</div>
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

    <!-- Modal Receiving -->
    <div id="receivingModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold" id="modalTitle">Receiving</h3>
                <button onclick="closeModal()" class="text-gray-500">&times;</button>
            </div>

            <form id="receivingForm" method="POST">
                @csrf
                <div class="space-y-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">
                                <th class="text-left px-3 py-2">Produk</th>
                                <th class="text-left px-3 py-2 w-24">Qty</th>
                                <th class="text-left px-3 py-2 w-48">Lokasi Simpan</th>
                            </tr>
                        </thead>
                        <tbody id="itemRows" class="divide-y divide-gray-200"></tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button type="button" onclick="closeModal()" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>

    <div id="historyModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold" id="historyTitle">History Receiving</h3>
                <button onclick="closeHistory()" class="text-gray-500">&times;</button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-3 py-2 text-left">Tanggal</th>
                            <th class="px-3 py-2 text-left">Produk</th>
                            <th class="px-3 py-2 text-left">Lokasi</th>
                            <th class="px-3 py-2 text-left">Qty</th>
                            <th class="px-3 py-2 text-left">User</th>
                        </tr>
                    </thead>
                    <tbody id="historyRows" class="text-sm divide-y divide-gray-200"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const purchases = @json($purchases->keyBy('id'));
        const locations = @json($locations->map(fn($l) => [
            'id' => $l->id,
            'label' => trim(($l->warehouse->code ?? '') . ' ' . ($l->warehouse->name ?? '')) . ' - ' . $l->code
        ]));
        const receivedMap = @json($receivedMap);
        const histories = @json($histories);

        function openModal(purchaseId, title) {
            const purchase = purchases[purchaseId];
            if (!purchase) return;

            document.getElementById('modalTitle').innerText = `Receiving ${title}`;
            const form = document.getElementById('receivingForm');
            form.action = `/warehouse/receiving/${purchaseId}`;

            const tbody = document.getElementById('itemRows');
            tbody.innerHTML = '';

            let idx = 0;
            (purchase.items || purchase.purchase_items || []).forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'border-b';

                let productName = item.product?.name || item.name || 'Produk';

                // Add variant info if exists
                if (item.product_variant && item.product_variant.variant_attributes) {
                    const variantAttrs = Object.values(item.product_variant.variant_attributes).join(' / ');
                    productName += ` <span class="text-xs text-gray-500">(${variantAttrs})</span>`;
                }

                const qtyOrdered = item.quantity || item.qty || item.qty_ordered || 0;
                const variantId = item.product_variant_id || item.product_variant?.id || null;
                const receivedKey = item.product_id + '_' + (variantId || 'null');
                const received = (receivedMap[purchaseId]?.[receivedKey] ?? 0);
                const remaining = Math.max(qtyOrdered - received, 0);

                tr.innerHTML = `
                    <td class="px-3 py-2 text-sm">${productName}</td>
                    <td class="px-3 py-2">
                        <input type="number" step="0.01" min="0" max="${remaining}" name="items[${idx}][qty]" value="${remaining}" class="w-24 h-10 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        <input type="hidden" name="items[${idx}][product_id]" value="${item.product_id || item.product?.id || ''}">
                        <input type="hidden" name="items[${idx}][product_variant_id]" value="${variantId || ''}">
                        <input type="hidden" name="items[${idx}][ordered_qty]" value="${qtyOrdered}">
                    </td>
                    <td class="px-3 py-2">
                        <select name="items[${idx}][location_id]" class="w-full h-10 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            <option value="">Pilih Lokasi</option>
                            ${locations.map(loc => `<option value="${loc.id}">${loc.label}</option>`).join('')}
                        </select>
                    </td>
                `;
                idx++;
                tbody.appendChild(tr);
            });

            document.getElementById('receivingModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('receivingModal').classList.add('hidden');
        }
    </script>

    <script>
        function openHistory(purchaseId, title) {
            const rows = histories[purchaseId] || [];
            document.getElementById('historyTitle').innerText = `History Receiving ${title}`;
            const tbody = document.getElementById('historyRows');
            tbody.innerHTML = '';
            if (rows.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="5" class="px-3 py-2 text-center text-gray-500">Belum ada history.</td>`;
                tbody.appendChild(tr);
            } else {
                rows.forEach(row => {
                    let productName = row.product?.name ?? '-';
                    if (row.product_variant && row.product_variant.variant_attributes) {
                        const variantAttrs = Object.values(row.product_variant.variant_attributes).join(' / ');
                        productName += ` <span class="text-xs text-gray-500">(${variantAttrs})</span>`;
                    }

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-3 py-2">${row.movement_date ?? ''}</td>
                        <td class="px-3 py-2">${productName}</td>
                        <td class="px-3 py-2">${row.to_location?.warehouse?.code ?? ''} ${row.to_location?.warehouse?.name ?? ''} - ${row.to_location?.code ?? ''}</td>
                        <td class="px-3 py-2">${row.qty}</td>
                        <td class="px-3 py-2">${row.user?.name ?? '-'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
            document.getElementById('historyModal').classList.remove('hidden');
        }

        function closeHistory() {
            document.getElementById('historyModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
