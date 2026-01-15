<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Laporan Pengeluaran Stok (Reason)</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="GET">
                        <div class="flex flex-wrap items-end gap-4 md:flex-nowrap">
                            <div class="w-full md:flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Periode Mulai</label>
                                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            </div>
                            <div class="w-full md:flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Periode Akhir</label>
                                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            </div>
                            <div class="w-full md:flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                                <select name="product_id" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    <option value="">Semua</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                                <select name="location_id" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    <option value="">Semua</option>
                                    @foreach ($locations as $loc)
                                        <option value="{{ $loc->id }}" @selected(request('location_id') == $loc->id)>{{ ($loc->warehouse->code ?? '') . '-' . $loc->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                                <select name="reason" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    <option value="">Semua</option>
                                    @foreach ($reasons as $reason)
                                        <option value="{{ $reason }}" @selected(request('reason') == $reason)>{{ ucfirst(str_replace('_', ' ', $reason)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end gap-2 md:flex-none pt-6">
                                <button class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Filter</button>
                                <a href="{{ route('warehouse.reports.stock_out') }}" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center flex items-center">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Keluar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($movements as $mv)
                                @php
                                    $movementTypeLabel = $mv->movement_type === 'ship' ? 'Shipment' : 'Adjustment';
                                    $reasonLabel = $mv->movement_type === 'ship' ? 'shipment' : ($mv->reason ?? '-');
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $mv->movement_date?->format('d M Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div>{{ $mv->product->name ?? '-' }}</div>
                                        @if($mv->productVariant)
                                            <div class="text-xs text-gray-500 mt-1">
                                                Variasi: {{ implode(' / ', array_values($mv->productVariant->variant_attributes ?? [])) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ ($mv->fromLocation->warehouse->code ?? '') . '-' . ($mv->fromLocation->code ?? '-') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 text-right">{{ number_format($mv->qty, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $movementTypeLabel }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 capitalize">{{ str_replace('_', ' ', $reasonLabel) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $mv->user->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $mv->notes ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-6 text-center text-sm text-gray-500">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($movements->lastPage() > 1)
                @php
                    $currentPage = $movements->currentPage();
                    $lastPage = $movements->lastPage();
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
                        <div>Total pergerakan: {{ number_format($movements->total()) }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $movements->previousPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $movements->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                               aria-label="Sebelumnya">
                                <x-heroicon name="chevron-left" class="w-4 h-4" />
                            </a>
                            @php $prev = null; @endphp
                            @foreach ($pages as $page)
                                @if ($prev && $page > $prev + 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $movements->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </a>
                                @php $prev = $page; @endphp
                            @endforeach
                            <a href="{{ $movements->nextPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $movements->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
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
