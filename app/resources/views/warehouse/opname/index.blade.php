<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Stock Opname</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('warehouse.opname.view') }}">
                        <div class="flex flex-wrap items-end gap-4 md:flex-nowrap">
                            <div class="w-full md:flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Lokasi</label>
                                <select name="location_id" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    <option value="">Pilih lokasi</option>
                                    @foreach ($locations as $loc)
                                        <option value="{{ $loc->id }}" @selected(($locationId ?? null) == $loc->id)>
                                            {{ ($loc->warehouse->code ?? '') . '-' . $loc->code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end gap-2 md:flex-none pt-6">
                                <button class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover">Lihat Stok</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if (!empty($locationId))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-900">Hitung Fisik - Lokasi: {{ ($locations->firstWhere('id', $locationId)->warehouse->code ?? '') . '-' . ($locations->firstWhere('id', $locationId)->code ?? '') }}</h3>
                    </div>
                    <form action="{{ route('warehouse.opname.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="location_id" value="{{ $locationId }}">
                        <div class="overflow-x-auto">
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
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ $sortUrl('product') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                                Produk <span class="text-[8px] leading-none">{{ $sortIndicator('product') }}</span>
                                            </a>
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ $sortUrl('qty_on_hand') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                                Stok Sistem <span class="text-[8px] leading-none">{{ $sortIndicator('qty_on_hand') }}</span>
                                            </a>
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Fisik</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($balances ?? [] as $balance)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $balance->product->name ?? 'Produk' }}
                                                <input type="hidden" name="items[][product_id]" value="{{ $balance->product_id }}">
                                                <input type="hidden" name="items[][system_qty]" value="{{ $balance->qty_on_hand }}">
                                            </td>
                                            <td class="px-6 py-4 text-sm text-right text-gray-500">{{ number_format($balance->qty_on_hand, 2) }}</td>
                                            <td class="px-6 py-4 text-sm text-right">
                                                <input type="number" step="0.01" min="0" name="items[][physical_qty]" value="{{ $balance->qty_on_hand }}" class="w-32 h-10 px-3 py-2 text-sm border border-gray-300 rounded-lg text-right focus:outline-none focus:border-primary">
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                <input type="text" name="items[][notes]" class="w-full h-10 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Opsional">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-6 text-center text-gray-500">Tidak ada stok di lokasi ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @error('items') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                        <div class="mt-4 flex justify-end px-6 pb-6">
                            <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover">Konfirmasi Opname</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
