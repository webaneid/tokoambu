<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Dashboard Gudang</h2>
    </x-slot>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-500">Total SKU</div>
                    <div class="text-3xl font-bold text-gray-900 mt-1">{{ $totalSku }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-500">Total Stok On Hand</div>
                    <div class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalOnHand, 2) }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-500">Slow Moving</div>
                    <div class="text-3xl font-bold text-orange-500 mt-1">{{ $slow }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-500">Dead Stock</div>
                    <div class="text-3xl font-bold text-red-500 mt-1">{{ $dead }}</div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900">Ringkasan Stok per Lokasi</h3>
                    <form method="GET" class="mt-4" x-data="{
                        showWarehouse: false,
                        showLocation: false,
                        selectedWarehouses: {{ json_encode($filters['warehouses'] ?? []) }},
                        selectedLocations: {{ json_encode($filters['locations'] ?? []) }},
                        warehouseLabel() {
                            if (this.selectedWarehouses.length === 0) return 'Semua Gudang';
                            if (this.selectedWarehouses.length === 1) {
                                const wh = @js($warehouses).find(w => w.id == this.selectedWarehouses[0]);
                                return wh ? wh.code + ' - ' + wh.name : '1 Gudang';
                            }
                            return this.selectedWarehouses.length + ' Gudang dipilih';
                        },
                        locationLabel() {
                            if (this.selectedLocations.length === 0) return 'Semua Lokasi';
                            if (this.selectedLocations.length === 1) {
                                const loc = @js($locations).find(l => l.id == this.selectedLocations[0]);
                                return loc ? loc.warehouse.code + '-' + loc.code : '1 Lokasi';
                            }
                            return this.selectedLocations.length + ' Lokasi dipilih';
                        }
                    }">
                        <div class="flex flex-col gap-4 md:flex-row md:flex-nowrap md:items-end">
                            <div class="w-full md:flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Produk (nama / SKU)</label>
                                <input type="text" name="product" value="{{ $filters['product'] ?? '' }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Misal: Buku atau SKU">
                            </div>

                            <!-- Warehouse Multi-Select -->
                            <div class="w-full md:flex-1 relative">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Filter Gudang</label>
                                <button type="button" @click="showWarehouse = !showWarehouse" class="w-full h-10 px-4 py-2 text-sm border border-gray-400 rounded-lg focus:outline-none focus:border-primary text-left flex items-center justify-between bg-white shadow-sm ring-1 ring-gray-200">
                                    <span x-text="warehouseLabel()"></span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="showWarehouse" @click.away="showWarehouse = false" class="absolute z-10 left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto" x-cloak>
                                    <div class="p-2 border-b border-gray-200">
                                        <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input type="checkbox" @click="selectedWarehouses = []" :checked="selectedWarehouses.length === 0" class="rounded border-gray-300 text-primary focus:ring-primary">
                                            <span class="text-sm text-gray-700 font-medium">Semua Gudang</span>
                                        </label>
                                    </div>
                                    @foreach($warehouses as $wh)
                                        <label class="flex items-center gap-2 p-2 hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" name="warehouses[]" value="{{ $wh->id }}" x-model="selectedWarehouses" class="rounded border-gray-300 text-primary focus:ring-primary">
                                            <span class="text-sm text-gray-700">{{ $wh->code }} - {{ $wh->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Location Multi-Select -->
                            <div class="w-full md:flex-1 relative">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Filter Lokasi</label>
                                <button type="button" @click="showLocation = !showLocation" class="w-full h-10 px-4 py-2 text-sm border border-gray-400 rounded-lg focus:outline-none focus:border-primary text-left flex items-center justify-between bg-white shadow-sm ring-1 ring-gray-200">
                                    <span x-text="locationLabel()"></span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="showLocation" @click.away="showLocation = false" class="absolute z-10 left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto" x-cloak>
                                    <div class="p-2 border-b border-gray-200">
                                        <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input type="checkbox" @click="selectedLocations = []" :checked="selectedLocations.length === 0" class="rounded border-gray-300 text-primary focus:ring-primary">
                                            <span class="text-sm text-gray-700 font-medium">Semua Lokasi</span>
                                        </label>
                                    </div>
                                    @php
                                        $groupedLocations = $locations->groupBy('warehouse_id');
                                    @endphp
                                    @foreach($groupedLocations as $warehouseId => $warehouseLocs)
                                        @php
                                            $warehouse = $warehouseLocs->first()->warehouse;
                                        @endphp
                                        <div class="p-2 border-b border-gray-100">
                                            <div class="text-xs font-semibold text-gray-500 uppercase px-2 py-1">{{ $warehouse->code }} - {{ $warehouse->name }}</div>
                                            @foreach($warehouseLocs as $loc)
                                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 cursor-pointer">
                                                    <input type="checkbox" name="locations[]" value="{{ $loc->id }}" x-model="selectedLocations" class="rounded border-gray-300 text-primary focus:ring-primary">
                                                    <span class="text-sm text-gray-700">{{ $loc->display_code ?? $loc->code }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex items-center gap-2 md:flex-none md:pt-6">
                                <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Filter</button>
                                <a href="{{ route('warehouse.dashboard') }}" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center flex items-center">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @php
                    $currentSort = request('sort', 'product');
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
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('product') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Produk <span class="text-[8px] leading-none">{{ $sortIndicator('product') }}</span>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('sku') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        SKU <span class="text-[8px] leading-none">{{ $sortIndicator('sku') }}</span>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('location') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Lokasi <span class="text-[8px] leading-none">{{ $sortIndicator('location') }}</span>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('qty_on_hand') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Qty On Hand <span class="text-[8px] leading-none">{{ $sortIndicator('qty_on_hand') }}</span>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('last_out_date') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Last Out <span class="text-[8px] leading-none">{{ $sortIndicator('last_out_date') }}</span>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortUrl('status') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Status <span class="text-[8px] leading-none">{{ $sortIndicator('status') }}</span>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($balances as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $row['product'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $row['sku'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $row['location'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($row['qty_on_hand'] ?? 0, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $row['last_out_date'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm capitalize text-gray-500">
                                        {{ $row['status'] ? str_replace('_', ' ', $row['status']) : 'Belum ada data' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-6 text-center text-gray-500">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($balances->lastPage() > 1)
                @php
                    $currentPage = $balances->currentPage();
                    $lastPage = $balances->lastPage();
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
                        <div>Total stok: {{ number_format($balances->total()) }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $balances->previousPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $balances->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                               aria-label="Sebelumnya">
                                <x-heroicon name="chevron-left" class="w-4 h-4" />
                            </a>
                            @php $prev = null; @endphp
                            @foreach ($pages as $page)
                                @if ($prev && $page > $prev + 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $balances->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </a>
                                @php $prev = $page; @endphp
                            @endforeach
                            <a href="{{ $balances->nextPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $balances->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
                               aria-label="Berikutnya">
                                <x-heroicon name="chevron-right" class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
