<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">Pengemasan</h2>
            <span class="text-sm text-gray-500">Daftar order sudah paid dan siap dikemas</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('orders.packing') }}">
                        <div class="flex flex-wrap items-end gap-4 md:flex-nowrap">
                            <div class="w-full md:flex-1">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                                <input
                                    id="search"
                                    name="search"
                                    type="text"
                                    value="{{ request('search') }}"
                                    class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                                    placeholder="No. order atau nama customer"
                                />
                            </div>
                            <div class="flex items-end gap-2 md:flex-none pt-6">
                                <button type="submit" class="h-10 px-6 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Filter</button>
                                <a href="{{ route('orders.packing') }}" class="h-10 px-6 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center flex items-center">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Bulk Action Bar --}}
            <div id="bulk-action-bar" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 hidden">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm text-gray-600">
                        <span id="selected-count">0</span> order dipilih
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                        <button id="mark-packed-btn" class="w-full sm:w-auto bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors inline-flex items-center justify-center gap-2">
                            <x-heroicon name="check-circle" class="w-4 h-4" />
                            Tandai Sudah Dikemas
                        </button>
                        <button id="print-selected-btn" class="w-full sm:w-auto bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover transition-colors inline-flex items-center justify-center gap-2">
                            <x-heroicon name="printer" class="w-4 h-4" />
                            Print Label Terpilih
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($orders->count())
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
                    <div class="block sm:hidden divide-y divide-gray-200">
                        @foreach($orders as $order)
                            <div class="p-4 space-y-2">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</p>
                                        <p class="text-sm text-gray-500">{{ $order->customer->name ?? '-' }}</p>
                                    </div>
                                    <input type="checkbox" class="order-checkbox mt-1 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer" data-order-id="{{ $order->id }}">
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                    <span>{{ $order->created_at?->format('d/m/Y') ?? '-' }}</span>
                                    <span class="text-gray-300">•</span>
                                    <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                </div>
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                </span>
                                <div class="flex items-center gap-3 pt-2">
                                    <a href="{{ route('orders.show', $order) }}" class="text-blue hover:text-blue-light" title="Lihat">
                                        <x-heroicon name="eye" class="w-4 h-4" />
                                    </a>
                                    @if (!in_array($order->status, ['shipped', 'done'], true))
                                        <a href="{{ route('orders.edit', $order) }}" class="text-blue hover:text-blue-light" title="Edit">
                                            <x-heroicon name="pencil-square" class="w-4 h-4" />
                                        </a>
                                    @endif
                                    <a href="{{ route('orders.print', $order) }}" class="text-gray-700 hover:text-gray-900" title="Print Label" target="_blank" rel="noopener">
                                        <x-heroicon name="printer" class="w-4 h-4" />
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden sm:block">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                                        <input type="checkbox" id="select-all" class="rounded border-gray-300 text-primary focus:ring-primary cursor-pointer">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('order_number') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            No. Order <span class="text-[8px] leading-none">{{ $sortIndicator('order_number') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('customer') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Customer <span class="text-[8px] leading-none">{{ $sortIndicator('customer') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('total_amount') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Total <span class="text-[8px] leading-none">{{ $sortIndicator('total_amount') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('created_at') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Tanggal Order <span class="text-[8px] leading-none">{{ $sortIndicator('created_at') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('status') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Status <span class="text-[8px] leading-none">{{ $sortIndicator('status') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($orders as $order)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" class="order-checkbox rounded border-gray-300 text-primary focus:ring-primary cursor-pointer" data-order-id="{{ $order->id }}">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $order->order_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->customer->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->created_at?->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('orders.show', $order) }}" class="text-blue hover:text-blue-light" title="Lihat">
                                                    <x-heroicon name="eye" class="w-4 h-4" />
                                                </a>
                                                @if (!in_array($order->status, ['shipped', 'done'], true))
                                                    <a href="{{ route('orders.edit', $order) }}" class="text-blue hover:text-blue-light" title="Edit">
                                                        <x-heroicon name="pencil-square" class="w-4 h-4" />
                                                    </a>
                                                @endif
                                                <a href="{{ route('orders.print', $order) }}" class="text-gray-700 hover:text-gray-900" title="Print Label" target="_blank" rel="noopener">
                                                    <x-heroicon name="printer" class="w-4 h-4" />
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-6 text-center text-sm text-gray-500">
                                            Belum ada order siap dikemas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">Belum ada order siap dikemas</p>
                    </div>
                @endif
            </div>

            @if ($orders->lastPage() > 1)
                @php
                    $currentPage = $orders->currentPage();
                    $lastPage = $orders->lastPage();
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
                        <div>Total order: {{ number_format($orders->total()) }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $orders->previousPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $orders->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                               aria-label="Sebelumnya">
                                <x-heroicon name="chevron-left" class="w-4 h-4" />
                            </a>
                            @php $prev = null; @endphp
                            @foreach ($pages as $page)
                                @if ($prev && $page > $prev + 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $orders->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </a>
                                @php $prev = $page; @endphp
                            @endforeach
                            <a href="{{ $orders->nextPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $orders->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
                               aria-label="Berikutnya">
                                <x-heroicon name="chevron-right" class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const orderCheckboxes = document.querySelectorAll('.order-checkbox');
            const bulkActionBar = document.getElementById('bulk-action-bar');
            const selectedCountSpan = document.getElementById('selected-count');
            const markPackedBtn = document.getElementById('mark-packed-btn');
            const printSelectedBtn = document.getElementById('print-selected-btn');

            function updateBulkActionBar() {
                const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
                const count = checkedBoxes.length;

                if (count > 0) {
                    bulkActionBar.classList.remove('hidden');
                    selectedCountSpan.textContent = count;
                } else {
                    bulkActionBar.classList.add('hidden');
                }

                // Update select-all checkbox state
                if (count === orderCheckboxes.length && count > 0) {
                    selectAllCheckbox.checked = true;
                    selectAllCheckbox.indeterminate = false;
                } else if (count > 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = true;
                } else {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                }
            }

            // Select all checkbox
            selectAllCheckbox.addEventListener('change', function() {
                orderCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActionBar();
            });

            // Individual checkboxes
            orderCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBulkActionBar);
            });

            // Mark as packed
            markPackedBtn.addEventListener('click', function() {
                const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
                const orderIds = Array.from(checkedBoxes).map(cb => cb.dataset.orderId);

                if (orderIds.length === 0) {
                    alert('Pilih minimal 1 order untuk ditandai sudah dikemas');
                    return;
                }

                if (!confirm(`Tandai ${orderIds.length} order sebagai sudah dikemas?`)) {
                    return;
                }

                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("orders.bulk-mark-packed") }}';

                // CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                // Order IDs
                const idsInput = document.createElement('input');
                idsInput.type = 'hidden';
                idsInput.name = 'order_ids';
                idsInput.value = JSON.stringify(orderIds);
                form.appendChild(idsInput);

                document.body.appendChild(form);
                form.submit();
            });

            // Print selected labels
            printSelectedBtn.addEventListener('click', function() {
                const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
                const orderIds = Array.from(checkedBoxes).map(cb => cb.dataset.orderId);

                if (orderIds.length === 0) {
                    alert('Pilih minimal 1 order untuk print label');
                    return;
                }

                // Open bulk print page
                const url = '{{ route("orders.bulk-print") }}?ids=' + orderIds.join(',');
                window.open(url, '_blank');
            });
        });
    </script>
</x-app-layout>
