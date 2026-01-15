<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900">Daftar Produk</h2>
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('create_products'))
                <a href="{{ route('products.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">
                    + Tambah Produk
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-0 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($message = Session::get('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ $message }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('products.index') }}">
                        <div class="flex flex-wrap items-end gap-4 md:flex-nowrap">
                            <div class="w-full md:flex-1">
                                <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                                <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="SKU atau nama produk" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            </div>
                            <div class="w-full md:flex-1">
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                <select id="category_id" name="category_id" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    <option value="">Semua kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) $category->id === (string) request('category_id'))>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:flex-1">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select id="status" name="status" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    <option value="">Semua status</option>
                                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                                    <option value="inactive" @selected(request('status') === 'inactive')>Tidak aktif</option>
                                </select>
                            </div>
                            <div class="w-full md:flex-1">
                                <label for="preorder" class="block text-sm font-medium text-gray-700 mb-1">Pre-order</label>
                                <select id="preorder" name="preorder" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    <option value="">Semua</option>
                                    <option value="enabled" @selected(request('preorder') === 'enabled')>Pre-order</option>
                                    <option value="disabled" @selected(request('preorder') === 'disabled')>Non pre-order</option>
                                </select>
                            </div>
                            <div class="flex items-end gap-2 md:flex-none pt-6">
                                <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Filter</button>
                                <a href="{{ route('products.index') }}" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center flex items-center">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($products->count())
                    @php
                        $currentSort = request('sort', 'sku');
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
                    <div class="block sm:hidden divide-y divide-gray-200">
                        @foreach ($products as $product)
                            <div class="p-4 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">{{ $product->name }}</p>
                                        <p class="text-sm text-gray-500">SKU: {{ $product->sku }}</p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $product->has_variants ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $product->has_variants ? 'Variasi (' . ($product->variants_count ?? 0) . ')' : 'Simple' }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                    <span>{{ $product->category?->name ?? 'Tanpa kategori' }}</span>
                                    <span class="text-gray-300">•</span>
                                    <span>Stok <span class="text-gray-900 font-medium">{{ number_format($product->qty_on_hand, 2) }}</span></span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-xs uppercase tracking-wide">
                                    @php $isPreorder = (bool) ($product->preorder_enabled ?? false); @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 {{ $isPreorder ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $isPreorder ? 'Pre-order' : 'Ready' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 pt-2">
                                    <a href="{{ route('products.show', $product) }}" class="text-blue hover:text-blue-light" title="Lihat">
                                        <x-heroicon name="eye" class="h-4 w-4" />
                                    </a>
                                    @can('edit_products')
                                        <a href="{{ route('products.edit', $product) }}" class="text-blue hover:text-blue-light" title="Edit">
                                            <x-heroicon name="pencil-square" class="h-4 w-4" />
                                        </a>
                                    @endcan
                                    @can('delete_products')
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700" title="Hapus">
                                                <x-heroicon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden sm:block">
                        <div class="-mx-4 overflow-x-auto sm:mx-0">
                            <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">
                                        <a href="{{ $sortUrl('sku') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            SKU <span class="text-[8px] leading-none">{{ $sortIndicator('sku') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">
                                        <a href="{{ $sortUrl('name') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Nama Produk <span class="text-[8px] leading-none">{{ $sortIndicator('name') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">Tipe</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">
                                        <a href="{{ $sortUrl('category') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Kategori <span class="text-[8px] leading-none">{{ $sortIndicator('category') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">
                                        <a href="{{ $sortUrl('qty_on_hand') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Stok <span class="text-[8px] leading-none">{{ $sortIndicator('qty_on_hand') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($products as $product)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900 whitespace-nowrap sm:px-6">{{ $product->sku }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-500 sm:px-6">
                                            <div class="max-w-[300px] truncate" title="{{ $product->name }}">{{ $product->name }}</div>
                                        </td>
                                        <td class="px-4 py-4 text-sm whitespace-nowrap sm:px-6">
                                            @if($product->has_variants)
                                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800">
                                                    Variasi ({{ $product->variants_count ?? 0 }})
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-700">
                                                    Simple
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-500 whitespace-nowrap sm:px-6">{{ $product->category?->name ?? '-' }}</td>
                                        <td class="px-4 py-4 text-right text-sm text-gray-500 whitespace-nowrap sm:px-6">{{ number_format($product->qty_on_hand, 2) }}</td>
                                        <td class="px-4 py-4 text-sm whitespace-nowrap sm:px-6">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('products.show', $product) }}" class="text-blue hover:text-blue-light" title="Lihat">
                                                    <x-heroicon name="eye" class="h-4 w-4" />
                                                </a>
                                                @can('edit_products')
                                                    <a href="{{ route('products.edit', $product) }}" class="text-blue hover:text-blue-light" title="Edit">
                                                        <x-heroicon name="pencil-square" class="h-4 w-4" />
                                                    </a>
                                                @endcan
                                                @can('delete_products')
                                                    <form action="{{ route('products.destroy', $product) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-700" title="Hapus" onclick="return confirm('Yakin ingin menghapus?')">
                                                            <x-heroicon name="trash" class="h-4 w-4" />
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>

                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">Tidak ada produk</p>
                    </div>
                @endif
            </div>

            @if ($products->lastPage() > 1)
                @php
                    $currentPage = $products->currentPage();
                    $lastPage = $products->lastPage();
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
                        <div>Total produk: {{ number_format($products->total()) }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $products->previousPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $products->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                               aria-label="Sebelumnya">
                                <x-heroicon name="chevron-left" class="w-4 h-4" />
                            </a>
                            @php $prev = null; @endphp
                            @foreach ($pages as $page)
                                @if ($prev && $page > $prev + 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $products->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </a>
                                @php $prev = $page; @endphp
                            @endforeach
                            <a href="{{ $products->nextPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $products->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
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
