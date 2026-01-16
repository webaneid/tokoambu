<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">Laporan Keuangan</h2>
            <div class="flex gap-2">
                <a href="{{ route('ledger.report.export') }}?{{ http_build_query(request()->query()) }}"
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">
                    📥 Export Excel
                </a>
                <a href="{{ route('ledger.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition text-sm">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 md:py-12" x-data="{ showCustomDate: {{ $period === 'custom' ? 'true' : 'false' }} }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Period Tabs --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <div class="flex overflow-x-auto gap-2 pb-2 -mx-2 px-2 scrollbar-thin">
                        <a href="{{ route('ledger.report', ['period' => 'today'] + request()->only('category_id')) }}"
                           class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition {{ $period === 'today' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Hari Ini
                        </a>
                        <a href="{{ route('ledger.report', ['period' => 'this_week'] + request()->only('category_id')) }}"
                           class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition {{ $period === 'this_week' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Minggu Ini
                        </a>
                        <a href="{{ route('ledger.report', ['period' => 'this_month'] + request()->only('category_id')) }}"
                           class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition {{ $period === 'this_month' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Bulan Ini
                        </a>
                        <a href="{{ route('ledger.report', ['period' => 'this_year'] + request()->only('category_id')) }}"
                           class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition {{ $period === 'this_year' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Tahun Ini
                        </a>
                        <button @click="showCustomDate = !showCustomDate"
                                class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition {{ $period === 'custom' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Custom
                        </button>
                    </div>

                    {{-- Custom Date Picker --}}
                    <div x-show="showCustomDate" x-collapse class="mt-4 pt-4 border-t border-gray-200">
                        <form method="GET" class="flex flex-wrap items-end gap-4 md:flex-nowrap">
                            <input type="hidden" name="period" value="custom">
                            @if(request('category_id'))
                                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                            @endif
                            <div class="w-full md:flex-1">
                                <label class="text-sm font-medium text-gray-700 mb-1 block">Tanggal Mulai</label>
                                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                                       class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            </div>
                            <div class="w-full md:flex-1">
                                <label class="text-sm font-medium text-gray-700 mb-1 block">Tanggal Selesai</label>
                                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                                       class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            </div>
                            <div class="flex items-end gap-2 md:flex-none">
                                <button type="submit" class="h-10 px-6 bg-primary text-white rounded-lg hover:bg-primary-hover transition text-sm">
                                    Filter
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Category Filter --}}
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <form method="GET" class="flex items-end gap-2">
                            <input type="hidden" name="period" value="{{ $period }}">
                            @if($period === 'custom')
                                <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                                <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                            @endif
                            <div class="flex-1">
                                <label class="text-sm font-medium text-gray-700 mb-1 block">Filter Kategori</label>
                                <select name="category_id" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    <option value="">Semua Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $categoryFilter == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="h-10 px-6 bg-primary text-white rounded-lg hover:bg-primary-hover transition text-sm">
                                Filter
                            </button>
                            @if($categoryFilter)
                                <a href="{{ route('ledger.report', ['period' => $period]) }}"
                                   class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center flex items-center text-sm">
                                    Reset
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            {{-- Summary Cards (4 cards) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 md:p-6">
                        <p class="text-xs md:text-sm text-gray-600 mb-1">💰 Total Pemasukan</p>
                        <p class="text-xl md:text-3xl font-bold text-green-600">Rp {{ number_format($income, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 md:p-6">
                        <p class="text-xs md:text-sm text-gray-600 mb-1">💸 Total Pengeluaran</p>
                        <p class="text-xl md:text-3xl font-bold text-red-600">Rp {{ number_format($expense, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 md:p-6">
                        <p class="text-xs md:text-sm text-gray-600 mb-1">📈 Keuntungan</p>
                        <p class="text-xl md:text-3xl font-bold {{ $profit >= 0 ? 'text-blue' : 'text-red-600' }}">
                            Rp {{ number_format($profit, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 md:p-6">
                        <p class="text-xs md:text-sm text-gray-600 mb-1">📊 Margin Keuntungan</p>
                        <p class="text-xl md:text-3xl font-bold text-gray-700">{{ number_format($profitMargin, 1) }}%</p>
                    </div>
                </div>
            </div>

            {{-- Comparison Stats --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 md:p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">📅 Perbandingan dengan Periode Sebelumnya</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600">Pemasukan</span>
                            <span class="text-sm font-bold {{ $incomeGrowth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $incomeGrowth >= 0 ? '+' : '' }}{{ number_format($incomeGrowth, 1) }}% {{ $incomeGrowth >= 0 ? '↑' : '↓' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600">Pengeluaran</span>
                            <span class="text-sm font-bold {{ $expenseGrowth <= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $expenseGrowth >= 0 ? '+' : '' }}{{ number_format($expenseGrowth, 1) }}% {{ $expenseGrowth >= 0 ? '↑' : '↓' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600">Keuntungan</span>
                            <span class="text-sm font-bold {{ $profitGrowth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $profitGrowth >= 0 ? '+' : '' }}{{ number_format($profitGrowth, 1) }}% {{ $profitGrowth >= 0 ? '↑' : '↓' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bar Chart --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 md:p-6">
                    <h3 class="text-base md:text-lg font-semibold text-gray-700 mb-4">Pemasukan vs Pengeluaran</h3>
                    <div class="h-64 md:h-96">
                        <canvas id="incomeExpenseChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Category Breakdown (2 Pie Charts) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 md:p-6">
                        <h3 class="text-base font-semibold text-gray-700 mb-4">Pemasukan per Kategori</h3>
                        <div class="h-64">
                            @if($incomeByCategory->isEmpty())
                                <div class="flex items-center justify-center h-full text-gray-400 text-sm">
                                    Tidak ada data pemasukan
                                </div>
                            @else
                                <canvas id="incomeCategoryChart"></canvas>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 md:p-6">
                        <h3 class="text-base font-semibold text-gray-700 mb-4">Pengeluaran per Kategori</h3>
                        <div class="h-64">
                            @if($expenseByCategory->isEmpty())
                                <div class="flex items-center justify-center h-full text-gray-400 text-sm">
                                    Tidak ada data pengeluaran
                                </div>
                            @else
                                <canvas id="expenseCategoryChart"></canvas>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Transaction Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 md:p-6">
                    <h3 class="text-base md:text-lg font-semibold text-gray-700 mb-4">Detail Transaksi</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[720px] w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-4 md:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                                <th class="px-4 md:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Kredit</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($entries as $entry)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ optional($entry->entry_date ?? $entry->created_at)->format('d M Y') }}
                                    </td>
                                    <td class="px-4 md:px-6 py-4 text-sm text-gray-500">
                                        {{ $entry->description }}
                                    </td>
                                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ optional($entry->category)->name ?? '-' }}
                                    </td>
                                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                        {{ $entry->type === 'income' ? 'Rp '.number_format($entry->amount, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                        {{ $entry->type === 'expense' ? 'Rp '.number_format($entry->amount, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 md:px-6 py-12 text-center text-gray-500 text-sm">
                                        Tidak ada data untuk rentang tanggal ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
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
                    <div class="px-4 md:px-6 py-4 flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600">
                        <div>Total transaksi: {{ number_format($entries->total()) }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $entries->previousPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $entries->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                               aria-label="Sebelumnya">
                                ‹
                            </a>
                            @php $prev = null; @endphp
                            @foreach ($pages as $page)
                                @if ($prev && $page > $prev + 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $entries->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border text-sm {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </a>
                                @php $prev = $page; @endphp
                            @endforeach
                            <a href="{{ $entries->nextPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $entries->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
                               aria-label="Berikutnya">
                                ›
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Bar Chart - Income vs Expense
        const barCtx = document.getElementById('incomeExpenseChart');
        if (barCtx) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: @json($chartData['income']),
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 1
                        },
                        {
                            label: 'Pengeluaran',
                            data: @json($chartData['expense']),
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }

        // Pie Chart - Income by Category
        const incomePieCtx = document.getElementById('incomeCategoryChart');
        @if($incomeByCategory->isNotEmpty())
        if (incomePieCtx) {
            new Chart(incomePieCtx, {
                type: 'pie',
                data: {
                    labels: @json($incomeByCategory->pluck('category')),
                    datasets: [{
                        data: @json($incomeByCategory->pluck('amount')),
                        backgroundColor: @json($incomeByCategory->pluck('color')),
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += 'Rp ' + context.parsed.toLocaleString('id-ID');
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
        @endif

        // Pie Chart - Expense by Category
        const expensePieCtx = document.getElementById('expenseCategoryChart');
        @if($expenseByCategory->isNotEmpty())
        if (expensePieCtx) {
            new Chart(expensePieCtx, {
                type: 'pie',
                data: {
                    labels: @json($expenseByCategory->pluck('category')),
                    datasets: [{
                        data: @json($expenseByCategory->pluck('amount')),
                        backgroundColor: @json($expenseByCategory->pluck('color')),
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += 'Rp ' + context.parsed.toLocaleString('id-ID');
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
        @endif
    </script>
    @endpush
</x-app-layout>
