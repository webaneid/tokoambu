<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900">Daftar Supplier</h2>
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('create_suppliers'))
                <a href="{{ route('suppliers.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">
                    + Tambah Supplier
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($message = Session::get('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ $message }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('suppliers.index') }}">
                        <div class="flex flex-wrap items-end gap-4 md:flex-nowrap">
                            <div class="w-full md:flex-1">
                                <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                                <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Nama, email, telepon, atau alamat" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            </div>
                            <div class="flex items-end gap-2 md:flex-none pt-6">
                                <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Filter</button>
                                <a href="{{ route('suppliers.index') }}" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center flex items-center">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($suppliers->count())
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
                        $formatWhatsappNumber = static function ($supplier) {
                            $rawWhatsapp = $supplier->whatsapp_number ?? $supplier->phone ?? '';
                            $waNumber = preg_replace('/\D+/', '', $rawWhatsapp);
                            if ($waNumber === '') {
                                return '';
                            }
                            if (str_starts_with($waNumber, '0')) {
                                return '62' . substr($waNumber, 1);
                            }
                            if (!str_starts_with($waNumber, '62')) {
                                return '62' . $waNumber;
                            }
                            return $waNumber;
                        };
                    @endphp
                    <div class="block sm:hidden divide-y divide-gray-200">
                        @foreach ($suppliers as $supplier)
                            @php $waNumber = $formatWhatsappNumber($supplier); @endphp
                            <div class="p-4 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">{{ $supplier->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $supplier->email ?? '-' }}</p>
                                    </div>
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $supplier->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                    <span>{{ $supplier->phone ?? '-' }}</span>
                                    <span class="text-gray-300">•</span>
                                    <span>{{ $supplier->email ?? '-' }}</span>
                                </div>
                                <p class="text-sm text-gray-500">
                                    <span class="text-gray-400">Alamat:</span>
                                    <span class="text-gray-700">{{ Str::limit($supplier->address ?? '-', 80) }}</span>
                                </p>
                                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                    <span class="text-gray-900 font-medium">{{ number_format($supplier->products_count ?? 0) }}</span>
                                    <span>produk terhubung</span>
                                </div>
                                <div class="flex items-center gap-3 pt-2">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="text-blue hover:text-blue-light" title="Lihat">
                                        <x-heroicon name="eye" class="w-4 h-4" />
                                    </a>
                                    @can('edit_suppliers')
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="text-blue hover:text-blue-light" title="Edit">
                                            <x-heroicon name="pencil-square" class="w-4 h-4" />
                                        </a>
                                    @endcan
                                    @if ($waNumber !== '')
                                        <a href="https://api.whatsapp.com/{{ $waNumber }}" class="text-green-600 hover:text-green-700" title="WhatsApp" target="_blank" rel="noopener">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path d="M20.52 3.48A11.84 11.84 0 0012.04 0C5.46 0 .1 5.36.1 11.94c0 2.1.54 4.16 1.58 5.98L0 24l6.28-1.64a11.87 11.87 0 005.76 1.47h.01c6.58 0 11.94-5.36 11.94-11.94 0-3.19-1.24-6.18-3.47-8.41zm-8.48 18.3h-.01a9.9 9.9 0 01-5.05-1.38l-.36-.22-3.73.98 1-3.63-.24-.37a9.88 9.88 0 01-1.51-5.22c0-5.46 4.44-9.9 9.9-9.9 2.64 0 5.12 1.03 6.99 2.9a9.83 9.83 0 012.9 6.99c0 5.46-4.44 9.9-9.9 9.9zm5.44-7.48c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.8-1.49-1.78-1.66-2.08-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.48-.5-.67-.5h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.5 0 1.48 1.08 2.9 1.23 3.1.15.2 2.13 3.26 5.15 4.57.72.31 1.28.5 1.72.64.72.23 1.37.2 1.88.12.57-.08 1.76-.72 2.01-1.41.25-.7.25-1.3.17-1.41-.07-.12-.27-.2-.57-.35z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    @can('delete_suppliers')
                                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700" title="Hapus">
                                                <x-heroicon name="trash" class="w-4 h-4" />
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-[720px] w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('name') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Nama Supplier <span class="text-[8px] leading-none">{{ $sortIndicator('name') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('email') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Email <span class="text-[8px] leading-none">{{ $sortIndicator('email') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('phone') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            No. Telepon <span class="text-[8px] leading-none">{{ $sortIndicator('phone') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('products_count') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Produk <span class="text-[8px] leading-none">{{ $sortIndicator('products_count') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('is_active') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Status <span class="text-[8px] leading-none">{{ $sortIndicator('is_active') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($suppliers as $supplier)
                                    @php $waNumber = $formatWhatsappNumber($supplier); @endphp
                                    <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $supplier->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $supplier->email ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $supplier->phone ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ Str::limit($supplier->address, 30) ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($supplier->products_count ?? 0) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <span class="px-3 py-1 {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} rounded-full text-xs">
                                            {{ $supplier->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('suppliers.show', $supplier) }}" class="text-blue hover:text-blue-light" title="Lihat">
                                                <x-heroicon name="eye" class="w-4 h-4" />
                                            </a>
                                            @can('edit_suppliers')
                                                <a href="{{ route('suppliers.edit', $supplier) }}" class="text-blue hover:text-blue-light" title="Edit">
                                                    <x-heroicon name="pencil-square" class="w-4 h-4" />
                                                </a>
                                            @endcan
                                            @if ($waNumber !== '')
                                                <a href="https://api.whatsapp.com/{{ $waNumber }}" class="text-green-600 hover:text-green-700" title="WhatsApp" target="_blank" rel="noopener">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                        <path d="M20.52 3.48A11.84 11.84 0 0012.04 0C5.46 0 .1 5.36.1 11.94c0 2.1.54 4.16 1.58 5.98L0 24l6.28-1.64a11.87 11.87 0 005.76 1.47h.01c6.58 0 11.94-5.36 11.94-11.94 0-3.19-1.24-6.18-3.47-8.41zm-8.48 18.3h-.01a9.9 9.9 0 01-5.05-1.38l-.36-.22-3.73.98 1-3.63-.24-.37a9.88 9.88 0 01-1.51-5.22c0-5.46 4.44-9.9 9.9-9.9 2.64 0 5.12 1.03 6.99 2.9a9.83 9.83 0 012.9 6.99c0 5.46-4.44 9.9-9.9 9.9zm5.44-7.48c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.8-1.49-1.78-1.66-2.08-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.48-.5-.67-.5h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.5 0 1.48 1.08 2.9 1.23 3.1.15.2 2.13 3.26 5.15 4.57.72.31 1.28.5 1.72.64.72.23 1.37.2 1.88.12.57-.08 1.76-.72 2.01-1.41.25-.7.25-1.3.17-1.41-.07-.12-.27-.2-.57-.35z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                            @can('delete_suppliers')
                                                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-700" title="Hapus" onclick="return confirm('Yakin ingin menghapus?')">
                                                        <x-heroicon name="trash" class="w-4 h-4" />
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
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">Tidak ada supplier</p>
                    </div>
                @endif
            </div>

            @if ($suppliers->lastPage() > 1)
                @php
                    $currentPage = $suppliers->currentPage();
                    $lastPage = $suppliers->lastPage();
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
                        <div>Total supplier: {{ number_format($suppliers->total()) }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $suppliers->previousPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $suppliers->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                               aria-label="Sebelumnya">
                                <x-heroicon name="chevron-left" class="w-4 h-4" />
                            </a>
                            @php $prev = null; @endphp
                            @foreach ($pages as $page)
                                @if ($prev && $page > $prev + 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $suppliers->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </a>
                                @php $prev = $page; @endphp
                            @endforeach
                            <a href="{{ $suppliers->nextPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $suppliers->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
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
