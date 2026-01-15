<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">Buku Kas / Ledger</h2>
            <a href="{{ route('ledger.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover transition-colors">
                + Catat Transaksi
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Summary -->
            <div class="grid grid-cols-2 max-[360px]:grid-cols-1 gap-3 sm:gap-4 md:grid-cols-3 md:gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Pemasukan (Hari Ini)</h3>
                    <p class="text-3xl font-bold text-green-600">Rp {{ number_format($todayIncome ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Pengeluaran (Hari Ini)</h3>
                    <p class="text-3xl font-bold text-red-600">Rp {{ number_format($todayExpense ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Saldo (Hari Ini)</h3>
                    <p class="text-3xl font-bold text-primary">Rp {{ number_format(($todayIncome ?? 0) - ($todayExpense ?? 0), 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Transactions -->
            <div class="bg-white rounded-lg shadow">
                <div class="overflow-x-auto">
                <table class="min-w-[720px] w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pemasukan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pengeluaran</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($entries as $entry)
                            @php
                                $sourceLink = null;
                                $sourceText = $entry->description;

                                // Determine source link based on source_type
                                if ($entry->source_type && $entry->source_id) {
                                    // Normalize source_type (handle both 'payment' and 'App\\Models\\Payment')
                                    $sourceType = $entry->source_type;

                                    // Handle legacy format and new format
                                    if ($sourceType === 'payment' || $sourceType === 'App\\Models\\Payment') {
                                        // Get payment to find order
                                        $payment = \App\Models\Payment::find($entry->source_id);
                                        if ($payment && $payment->order_id) {
                                            $sourceLink = route('orders.show', $payment->order_id);
                                        }
                                    } elseif ($sourceType === 'refund' || $sourceType === 'App\\Models\\Refund') {
                                        $sourceLink = route('refunds.show', $entry->source_id);
                                    } elseif ($sourceType === 'purchase_payment' || $sourceType === 'App\\Models\\Purchase') {
                                        $sourceLink = route('purchases.show', $entry->source_id);
                                    } elseif ($sourceType === 'ledger_transfer_fee') {
                                        $sourceLink = route('ledger.show', $entry->source_id);
                                    }
                                }

                                // If no source link, default to ledger detail page
                                if (!$sourceLink) {
                                    $sourceLink = route('ledger.show', $entry);
                                }
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($entry->entry_date ?? $entry->created_at)->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <a href="{{ $sourceLink }}" class="text-blue-600 hover:underline">{{ $sourceText }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($entry->category)->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right text-green-600">
                                    {{ $entry->type === 'income' ? 'Rp ' . number_format($entry->amount, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right text-red-600">
                                    {{ $entry->type === 'expense' ? 'Rp ' . number_format($entry->amount, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Belum ada transaksi
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>

            <!-- Pagination -->
            @if ($entries->lastPage() > 1)
                @php
                    $currentPage = $entries->currentPage();
                    $lastPage = $entries->lastPage();
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
                        <div>Total transaksi: {{ number_format($entries->total()) }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $entries->previousPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $entries->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                               aria-label="Sebelumnya">
                                <x-heroicon name="chevron-left" class="w-4 h-4" />
                            </a>
                            @php $prev = null; @endphp
                            @foreach ($pages as $page)
                                @if ($prev && $page > $prev + 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $entries->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </a>
                                @php $prev = $page; @endphp
                            @endforeach
                            <a href="{{ $entries->nextPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $entries->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
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
