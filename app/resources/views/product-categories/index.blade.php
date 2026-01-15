<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900">Kategori Produk</h2>
            <a href="{{ route('product-categories.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">
                + Tambah Kategori
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($message = Session::get('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ $message }}
                </div>
            @endif
            @if ($message = Session::get('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ $message }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('product-categories.index') }}">
                        <div class="flex flex-wrap items-end gap-4 md:flex-nowrap">
                            <div class="w-full md:flex-1">
                                <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                                <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Nama atau deskripsi kategori" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            </div>
                            <div class="flex items-end gap-2 md:flex-none pt-6">
                                <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Filter</button>
                                <a href="{{ route('product-categories.index') }}" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center flex items-center">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($categories->count())
                    <div class="block sm:hidden divide-y divide-gray-100">
                        @foreach ($categories as $category)
                            <div class="p-4 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $category->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $category->description ?? 'Tidak ada deskripsi' }}</p>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm">
                                        <a href="{{ route('product-categories.edit', $category) }}" class="text-blue hover:text-blue-light" title="Edit">
                                            <x-heroicon name="pencil-square" class="w-4 h-4" />
                                        </a>
                                        <form action="{{ route('product-categories.destroy', $category) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700" title="Hapus" onclick="return confirm('Yakin ingin menghapus?')">
                                                <x-heroicon name="trash" class="w-4 h-4" />
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @php
                        $currentSort = request('sort', 'name');
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

                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-[640px] w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('name') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Nama <span class="text-[8px] leading-none">{{ $sortIndicator('name') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('description') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Deskripsi <span class="text-[8px] leading-none">{{ $sortIndicator('description') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($categories as $category)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $category->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $category->description ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('product-categories.edit', $category) }}" class="text-blue hover:text-blue-light" title="Edit">
                                                    <x-heroicon name="pencil-square" class="w-4 h-4" />
                                                </a>
                                                <form action="{{ route('product-categories.destroy', $category) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-700" title="Hapus" onclick="return confirm('Yakin ingin menghapus?')">
                                                        <x-heroicon name="trash" class="w-4 h-4" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">Belum ada kategori produk.</p>
                    </div>
                @endif
            </div>

            @if ($categories->lastPage() > 1)
                @php
                    $currentPage = $categories->currentPage();
                    $lastPage = $categories->lastPage();
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
                        <div>Total kategori: {{ number_format($categories->total()) }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $categories->previousPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $categories->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                               aria-label="Sebelumnya">
                                <x-heroicon name="chevron-left" class="w-4 h-4" />
                            </a>
                            @php $prev = null; @endphp
                            @foreach ($pages as $page)
                                @if ($prev && $page > $prev + 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $categories->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </a>
                                @php $prev = $page; @endphp
                            @endforeach
                            <a href="{{ $categories->nextPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $categories->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
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
