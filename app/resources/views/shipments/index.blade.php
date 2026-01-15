<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">Daftar Pengiriman</h2>
            <a href="{{ route('shipments.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover transition-colors">
                + Catat Pengiriman
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Table Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="block sm:hidden divide-y divide-gray-200">
                    @forelse($shipments as $shipment)
                        <div class="p-4 space-y-2">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $shipment->shipment_number }}</p>
                                <p class="text-sm text-gray-500">{{ $shipment->order->order_number ?? '-' }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                <span>{{ $shipment->courier ?? '-' }}</span>
                                <span class="text-gray-300">•</span>
                                <span>{{ $shipment->tracking_number ?? '-' }}</span>
                            </div>
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium
                                @if($shipment->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($shipment->status === 'shipped') bg-blue-100 text-blue-800
                                @elseif($shipment->status === 'delivered') bg-green-100 text-green-800
                                @endif">
                                {{ ucfirst($shipment->status) }}
                            </span>
                            <div class="flex items-center gap-3 pt-2">
                                <a href="{{ route('shipments.show', $shipment) }}" class="text-blue hover:text-blue-light" title="Lihat">
                                    <x-heroicon name="eye" class="w-4 h-4" />
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-gray-500 text-sm">
                            Belum ada pengiriman
                        </div>
                    @endforelse
                </div>

                <div class="hidden sm:block">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Pengiriman</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kurir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Resi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($shipments as $shipment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $shipment->shipment_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $shipment->order->order_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $shipment->courier }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $shipment->tracking_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium
                                            @if($shipment->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($shipment->status === 'shipped') bg-blue-100 text-blue-800
                                            @elseif($shipment->status === 'delivered') bg-green-100 text-green-800
                                            @endif">
                                            {{ ucfirst($shipment->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('shipments.show', $shipment) }}" class="text-blue hover:text-blue-light inline-flex items-center gap-1" title="Lihat">
                                            <x-heroicon name="eye" class="w-4 h-4" />
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">
                                        Belum ada pengiriman
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination Card --}}
            @if ($shipments->lastPage() > 1)
                @php
                    $currentPage = $shipments->currentPage();
                    $lastPage = $shipments->lastPage();
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
                        <div>Total pengiriman: {{ number_format($shipments->total()) }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $shipments->previousPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $shipments->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                               aria-label="Sebelumnya">
                                <x-heroicon name="chevron-left" class="w-4 h-4" />
                            </a>
                            @php $prev = null; @endphp
                            @foreach ($pages as $page)
                                @if ($prev && $page > $prev + 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $shipments->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </a>
                                @php $prev = $page; @endphp
                            @endforeach
                            <a href="{{ $shipments->nextPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $shipments->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
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
