<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="{{ route('customers.index') }}" class="text-blue-600 hover:underline">Customer</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-900">{{ $customer->name }}</span>
            </div>
            <div class="space-x-2">
                @can('edit_customers')
                    <a href="{{ route('customers.edit', $customer) }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">
                        Edit
                    </a>
                @endcan
                <a href="{{ route('customers.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-6">Detail Customer</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <p class="text-sm text-gray-600">Nama</p>
                            <p class="text-lg font-semibold">{{ $customer->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="text-lg">{{ $customer->email ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">No. Telepon</p>
                            <p class="text-lg">{{ $customer->phone ?? '-' }}</p>
                        </div>

                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600">Nomor WhatsApp</p>
                            @php
                                $phoneClean = preg_replace('/\\D+/', '', $customer->phone ?? '');
                                $waClean = preg_replace('/\\D+/', '', $customer->whatsapp_number ?? '');
                                $waSame = $phoneClean && $waClean && $phoneClean === $waClean;
                                $waLink = $waClean ? 'https://api.whatsapp.com/send?phone=' . $waClean : null;
                            @endphp
                            <div class="flex items-center space-x-2">
                                <p class="text-lg">
                                    {{ $customer->whatsapp_number ?? '-' }}
                                    @if ($waSame)
                                        <span class="ml-2 text-xs text-gray-500">(sama dengan telepon)</span>
                                    @endif
                                </p>
                                @if ($waLink)
                                    <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center space-x-2 px-3 py-1 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" class="fill-current">
                                            <path d="M13.95 4.24C11.86 1 7.58.04 4.27 2.05C1.04 4.06 0 8.44 2.09 11.67l.17.26l-.7 2.62l2.62-.7l.26.17c1.13.61 2.36.96 3.58.96c1.31 0 2.62-.35 3.75-1.05c3.23-2.1 4.19-6.39 2.18-9.71Zm-1.83 6.74c-.35.52-.79.87-1.4.96c-.35 0-.79.17-2.53-.52c-1.48-.7-2.71-1.84-3.58-3.15c-.52-.61-.79-1.4-.87-2.19c0-.7.26-1.31.7-1.75c.17-.17.35-.26.52-.26h.44c.17 0 .35 0 .44.35c.17.44.61 1.49.61 1.58c.09.09.05.76-.35 1.14c-.22.25-.26.26-.17.44c.35.52.79 1.05 1.22 1.49c.52.44 1.05.79 1.66 1.05c.17.09.35.09.44-.09c.09-.17.52-.61.7-.79c.17-.17.26-.17.44-.09l1.4.7c.17.09.35.17.44.26c.09.26.09.61-.09.87Z"/>
                                        </svg>
                                        <span>WhatsApp</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-8 pt-6 border-t">
                        <p class="text-sm text-gray-600 mb-3">Alamat & Lokasi</p>
                        <div class="space-y-2">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Alamat</p>
                                <p class="text-gray-800">{{ $customer->address ?? '-' }}</p>
                            </div>
                            <div class="text-sm text-gray-700">
                                <div>Kecamatan: {{ $customer->district->name ?? '-' }}</div>
                                <div>Kab/Kota: {{ $customer->city->name ?? '-' }}</div>
                                <div>Provinsi: {{ $customer->province->name ?? '-' }}</div>
                                <div>Kode Pos: {{ $customer->postal_code ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">Riwayat Order</h3>
                    @if ($orders->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Order</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Item</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Tanggal</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Status</th>
                                        <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Total</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach ($orders as $order)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm font-medium">{{ $order->order_number }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">
                                                <div class="space-y-1">
                                                    @forelse ($order->items as $item)
                                                        <div>
                                                            {{ $item->product?->name ?? '-' }} × {{ number_format($item->quantity, 0) }}
                                                        </div>
                                                    @empty
                                                        <span class="text-gray-400">-</span>
                                                    @endforelse
                                                </div>
                                            </td>
                                            <td class="px-4 py-2 text-sm">{{ $order->created_at->format('d M Y') }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right font-semibold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:underline">Lihat</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
                    @else
                        <p class="text-gray-500 text-center py-6">Belum ada riwayat order.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
