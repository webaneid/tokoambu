@php
    $filters = [
        'search' => request('search'),
        'type' => request('type'),
        'status' => request('status'),
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Promo Center</h2>
            <a href="{{ route('promotions.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-hover transition">+ Buat Promo</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                <form method="GET" class="flex flex-wrap items-end gap-4 md:flex-nowrap">
                    <div class="w-full md:flex-[2]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                        <input type="text" name="search" value="{{ $filters['search'] }}" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Nama promo">
                    </div>
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                        <select name="type" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            <option value="">Semua</option>
                            <option value="flash_sale" @selected($filters['type'] === 'flash_sale')>Flash Sale</option>
                            <option value="bundle" @selected($filters['type'] === 'bundle')>Bundle</option>
                            <option value="coupon" @selected($filters['type'] === 'coupon')>Coupon</option>
                            <option value="cart_rule" @selected($filters['type'] === 'cart_rule')>Cart Rule</option>
                        </select>
                    </div>
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            <option value="">Semua</option>
                            <option value="draft" @selected($filters['status'] === 'draft')>Draft</option>
                            <option value="scheduled" @selected($filters['status'] === 'scheduled')>Scheduled</option>
                            <option value="active" @selected($filters['status'] === 'active')>Active</option>
                            <option value="ended" @selected($filters['status'] === 'ended')>Ended</option>
                            <option value="archived" @selected($filters['status'] === 'archived')>Archived</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2 md:flex-none pt-6">
                        <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition text-sm">Filter</button>
                        <a href="{{ route('promotions.index') }}" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-sm flex items-center">Reset</a>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                @if($promotions->count())
                    <div class="block sm:hidden divide-y divide-gray-100">
                        @foreach($promotions as $promotion)
                            @php
                                $bundleCount = $promotion->bundle ? $promotion->bundle->items->count() : 0;
                                $productCount = $promotion->type === 'bundle'
                                    ? $bundleCount
                                    : ($promotion->type === 'flash_sale' ? ($promotion->targets_count ?? 0) : 0);
                            @endphp
                            <div class="p-4 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $promotion->name }}</p>
                                        <p class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $promotion->type) }}</p>
                                    </div>
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700 capitalize">{{ $promotion->status }}</span>
                                </div>
                                <div class="text-xs text-gray-600 space-y-1">
                                    <p><span class="font-medium text-gray-800">Periode:</span>
                                        @if($promotion->start_at || $promotion->end_at)
                                            {{ $promotion->start_at?->format('d M Y') ?? '-' }} - {{ $promotion->end_at?->format('d M Y') ?? '-' }}
                                        @else
                                            -
                                        @endif
                                    </p>
                                    <p><span class="font-medium text-gray-800">Total Produk:</span> {{ $productCount }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-3 text-sm">
                                    <a href="{{ route('promotions.show', $promotion) }}" class="inline-flex items-center gap-1 text-blue hover:text-blue-light" title="Lihat">
                                        <x-heroicon name="eye" class="w-4 h-4" /> Lihat
                                    </a>
                                    <a href="{{ route('promotions.edit', $promotion) }}" class="inline-flex items-center gap-1 text-blue hover:text-blue-light" title="Edit">
                                        <x-heroicon name="pencil-square" class="w-4 h-4" /> Edit
                                    </a>
                                    <form action="{{ route('promotions.duplicate', $promotion) }}" method="POST" class="inline-flex items-center gap-1">
                                        @csrf
                                        <button type="submit" class="text-gray-500 hover:text-gray-700" title="Duplikat">
                                            <x-heroicon name="document-duplicate" class="w-4 h-4" /> Duplikat
                                        </button>
                                    </form>
                                    @if($promotion->status !== 'ended')
                                        <form action="{{ route('promotions.end', $promotion) }}" method="POST" class="inline-flex items-center gap-1">
                                            @csrf
                                            <button type="submit" class="text-orange-500 hover:text-orange-600" title="Akhiri">
                                                <x-heroicon name="stop-circle" class="w-4 h-4" /> Akhiri
                                            </button>
                                        </form>
                                    @endif
                                    @if($promotion->status !== 'archived')
                                        <form action="{{ route('promotions.archive', $promotion) }}" method="POST" class="inline-flex items-center gap-1">
                                            @csrf
                                            <button type="submit" class="text-gray-500 hover:text-gray-700" title="Archive">
                                                <x-heroicon name="archive-box" class="w-4 h-4" /> Arsipkan
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="block sm:hidden text-center py-8 text-sm text-gray-500">Belum ada promo.</div>
                @endif

                <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-[720px] w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($promotions as $promotion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $promotion->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ str_replace('_', ' ', ucfirst($promotion->type)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($promotion->status) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($promotion->start_at || $promotion->end_at)
                                        {{ $promotion->start_at?->format('d M Y') ?? '-' }} - {{ $promotion->end_at?->format('d M Y') ?? '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                @php
                                    $bundleCount = $promotion->bundle ? $promotion->bundle->items->count() : 0;
                                    $productCount = $promotion->type === 'bundle'
                                        ? $bundleCount
                                        : ($promotion->type === 'flash_sale' ? ($promotion->targets_count ?? 0) : 0);
                                @endphp
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ $productCount }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('promotions.show', $promotion) }}" class="text-blue hover:text-blue-light" title="Lihat">
                                            <x-heroicon name="eye" class="w-4 h-4" />
                                        </a>
                                        <a href="{{ route('promotions.edit', $promotion) }}" class="text-blue hover:text-blue-light" title="Edit">
                                            <x-heroicon name="pencil-square" class="w-4 h-4" />
                                        </a>
                                        <form action="{{ route('promotions.duplicate', $promotion) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-gray-500 hover:text-gray-700" title="Duplikat">
                                                <x-heroicon name="document-duplicate" class="w-4 h-4" />
                                            </button>
                                        </form>
                                        @if($promotion->status !== 'ended')
                                            <form action="{{ route('promotions.end', $promotion) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-orange-500 hover:text-orange-600" title="Akhiri">
                                                    <x-heroicon name="stop-circle" class="w-4 h-4" />
                                                </button>
                                            </form>
                                        @endif
                                        @if($promotion->status !== 'archived')
                                            <form action="{{ route('promotions.archive', $promotion) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-gray-500 hover:text-gray-700" title="Archive">
                                                    <x-heroicon name="archive-box" class="w-4 h-4" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-6 text-center text-sm text-gray-500">Belum ada promo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>

            @if ($promotions->lastPage() > 1)
                @php
                    $currentPage = $promotions->currentPage();
                    $lastPage = $promotions->lastPage();
                    $pages = collect([
                        1,
                        2,
                        $currentPage - 1,
                        $currentPage,
                        $currentPage + 1,
                        $lastPage - 1,
                        $lastPage,
                    ])->filter(fn ($page) => $page >= 1 && $page <= $lastPage)->unique()->sort()->values();
                @endphp

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600">
                        <div>Total promo: {{ $promotions->total() }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $promotions->previousPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $promotions->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                               aria-label="Sebelumnya">
                                <x-heroicon name="chevron-left" class="w-4 h-4" />
                            </a>
                            @php $prev = null; @endphp
                            @foreach ($pages as $page)
                                @if ($prev && $page > $prev + 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $promotions->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </a>
                                @php $prev = $page; @endphp
                            @endforeach
                            <a href="{{ $promotions->nextPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $promotions->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
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
