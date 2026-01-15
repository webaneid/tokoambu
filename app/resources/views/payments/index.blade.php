<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">Daftar Pembayaran</h2>
            <a href="{{ route('payments.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover transition">
                + Catat Pembayaran
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Table Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="block sm:hidden divide-y divide-gray-200">
                    @forelse($payments as $payment)
                        <div class="p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs text-gray-500">No. Order</div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $payment->order->order_number ?? '-' }}</div>
                                </div>
                                <a href="{{ route('payments.show', $payment) }}" class="text-blue hover:text-blue-light inline-flex items-center gap-1" title="Lihat">
                                    <x-heroicon name="eye" class="w-4 h-4" />
                                </a>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <div class="text-xs text-gray-500">Customer</div>
                                    <div class="text-gray-900">{{ $payment->order->customer->name ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Jumlah</div>
                                    <div class="text-gray-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Status</div>
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $payment->status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $payment->status === 'verified' ? 'Terverifikasi' : 'Pending' }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Tanggal</div>
                                    <div class="text-gray-900">{{ $payment->paid_at ? $payment->paid_at->format('d M Y') : ($payment->created_at ? $payment->created_at->format('d M Y') : '-') }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-gray-500 text-sm">
                            Belum ada pembayaran
                        </div>
                    @endforelse
                </div>
                <div class="hidden sm:block">
                    <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('payments.index', ['sort' => 'order_number', 'direction' => (request('sort') === 'order_number' && request('direction') === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-700">
                                    No. Order
                                    @if(request('sort') === 'order_number')
                                        <span class="text-[8px] leading-none">{{ request('direction') === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('payments.index', ['sort' => 'customer', 'direction' => (request('sort') === 'customer' && request('direction') === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-700">
                                    Customer
                                    @if(request('sort') === 'customer')
                                        <span class="text-[8px] leading-none">{{ request('direction') === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('payments.index', ['sort' => 'amount', 'direction' => (request('sort') === 'amount' && request('direction') === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center justify-end gap-1 hover:text-gray-700">
                                    Jumlah
                                    @if(request('sort') === 'amount')
                                        <span class="text-[8px] leading-none">{{ request('direction') === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('payments.index', ['sort' => 'status', 'direction' => (request('sort') === 'status' && request('direction') === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-700">
                                    Status
                                    @if(request('sort') === 'status')
                                        <span class="text-[8px] leading-none">{{ request('direction') === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('payments.index', ['sort' => 'paid_at', 'direction' => (request('sort') === 'paid_at' && request('direction') === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-700">
                                    Tanggal
                                    @if(request('sort') === 'paid_at' || !request('sort'))
                                        <span class="text-[8px] leading-none">{{ (request('direction') === 'asc') ? '▲' : '▼' }}</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $payment->order->order_number ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $payment->order->customer->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium {{ $payment->status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $payment->status === 'verified' ? 'Terverifikasi' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $payment->paid_at ? $payment->paid_at->format('d M Y') : ($payment->created_at ? $payment->created_at->format('d M Y') : '-') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('payments.show', $payment) }}" class="text-blue hover:text-blue-light inline-flex items-center gap-1" title="Lihat">
                                        <x-heroicon name="eye" class="w-4 h-4" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">
                                    Belum ada pembayaran
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination Card --}}
            @if($payments->lastPage() > 1)
                @php
                    $currentPage = $payments->currentPage();
                    $lastPage = $payments->lastPage();
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
                        <div>Total pembayaran: {{ number_format($payments->total()) }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $payments->previousPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $payments->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                               aria-label="Sebelumnya">
                                <x-heroicon name="chevron-left" class="w-4 h-4" />
                            </a>
                            @php $prev = null; @endphp
                            @foreach ($pages as $page)
                                @if ($prev && $page > $prev + 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $payments->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </a>
                                @php $prev = $page; @endphp
                            @endforeach
                            <a href="{{ $payments->nextPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $payments->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
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
