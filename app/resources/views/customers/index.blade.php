<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">Daftar Customer</h2>
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('create_customers'))
                <a href="{{ route('customers.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover transition-colors">
                    + Tambah Customer
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('customers.index') }}">
                        <div class="flex flex-wrap items-end gap-4 md:flex-nowrap">
                            <div class="w-full md:flex-1">
                                <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                                <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Nama, email, telepon, atau alamat" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            </div>
                            <div class="flex items-end gap-2 md:flex-none pt-6">
                                <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Filter</button>
                                <a href="{{ route('customers.index') }}" class="h-10 px-4 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center flex items-center">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($customers->count())
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
                        $formatWhatsappNumber = static function ($customer) {
                            $rawWhatsapp = $customer->whatsapp_number ?? $customer->phone ?? '';
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
                        @foreach ($customers as $customer)
                            @php $waNumber = $formatWhatsappNumber($customer); @endphp
                            <div class="p-4 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">{{ $customer->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $customer->email ?? '-' }}</p>
                                    </div>
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                    <span>{{ $customer->phone ?? '-' }}</span>
                                    <span class="text-gray-300">•</span>
                                    <span>{{ Str::limit($customer->address ?? '-', 80) }}</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                    <span class="text-gray-400">Total order:</span>
                                    <span class="text-gray-900 font-medium">{{ number_format($customer->orders_count ?? 0) }}</span>
                                </div>
                                <div class="flex items-center gap-3 pt-2">
                                    <a href="{{ route('customers.show', $customer) }}" class="text-blue hover:text-blue-light" title="Lihat">
                                        <x-heroicon name="eye" class="w-4 h-4" />
                                    </a>
                                    <a href="{{ route('customers.edit', $customer) }}" class="text-blue hover:text-blue-light" title="Edit">
                                        <x-heroicon name="pencil-square" class="w-4 h-4" />
                                    </a>
                                    @if ($waNumber !== '')
                                        <a href="https://api.whatsapp.com/{{ $waNumber }}" class="text-green-600 hover:text-green-700" title="WhatsApp" target="_blank" rel="noopener">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path d="M20.52 3.48A11.84 11.84 0 0012.04 0C5.46 0 .1 5.36.1 11.94c0 2.1.54 4.16 1.58 5.98L0 24l6.28-1.64a11.87 11.87 0 005.76 1.47h.01c6.58 0 11.94-5.36 11.94-11.94 0-3.19-1.24-6.18-3.47-8.41zm-8.48 18.3h-.01a9.9 9.9 0 01-5.05-1.38l-.36-.22-3.73.98 1-3.63-.24-.37a9.88 9.88 0 01-1.51-5.22c0-5.46 4.44-9.9 9.9-9.9 2.64 0 5.12 1.03 6.99 2.9a9.83 9.83 0 012.9 6.99c0 5.46-4.44 9.9-9.9 9.9zm5.44-7.48c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.8-1.49-1.78-1.66-2.08-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.48-.5-.67-.5h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.5 0 1.48 1.08 2.9 1.23 3.1.15.2 2.13 3.26 5.15 4.57.72.31 1.28.5 1.72.64.72.23 1.37.2 1.88.12.57-.08 1.76-.72 2.01-1.41.25-.7.25-1.3.17-1.41-.07-.12-.27-.2-.57-.35z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700" title="Hapus">
                                            <x-heroicon name="trash" class="w-4 h-4" />
                                        </button>
                                    </form>
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
                                            Nama <span class="text-[8px] leading-none">{{ $sortIndicator('name') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('email') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Email <span class="text-[8px] leading-none">{{ $sortIndicator('email') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('phone') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            No. HP <span class="text-[8px] leading-none">{{ $sortIndicator('phone') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortUrl('orders_count') }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            Order <span class="text-[8px] leading-none">{{ $sortIndicator('orders_count') }}</span>
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
                                @forelse($customers as $customer)
                                @php $waNumber = $formatWhatsappNumber($customer); @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $customer->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $customer->email ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $customer->phone ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($customer->address, 30) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($customer->orders_count ?? 0) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('customers.show', $customer) }}" class="text-blue hover:text-blue-light" title="Lihat">
                                                <x-heroicon name="eye" class="w-4 h-4" />
                                            </a>
                                            <a href="{{ route('customers.edit', $customer) }}" class="text-blue hover:text-blue-light" title="Edit">
                                                <x-heroicon name="pencil-square" class="w-4 h-4" />
                                            </a>
                                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700" title="Hapus">
                                                    <x-heroicon name="trash" class="w-4 h-4" />
                                                </button>
                                            </form>
                                            @if ($waNumber !== '')
                                                <a href="https://api.whatsapp.com/{{ $waNumber }}" class="text-green-600 hover:text-green-700" title="WhatsApp" target="_blank" rel="noopener">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                        <path d="M20.52 3.48A11.84 11.84 0 0012.04 0C5.46 0 .1 5.36.1 11.94c0 2.1.54 4.16 1.58 5.98L0 24l6.28-1.64a11.87 11.87 0 005.76 1.47h.01c6.58 0 11.94-5.36 11.94-11.94 0-3.19-1.24-6.18-3.47-8.41zm-8.48 18.3h-.01a9.9 9.9 0 01-5.05-1.38l-.36-.22-3.73.98 1-3.63-.24-.37a9.88 9.88 0 01-1.51-5.22c0-5.46 4.44-9.9 9.9-9.9 2.64 0 5.12 1.03 6.99 2.9a9.83 9.83 0 012.9 6.99c0 5.46-4.44 9.9-9.9 9.9zm5.44-7.48c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.8-1.49-1.78-1.66-2.08-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.48-.5-.67-.5h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.5 0 1.48 1.08 2.9 1.23 3.1.15.2 2.13 3.26 5.15 4.57.72.31 1.28.5 1.72.64.72.23 1.37.2 1.88.12.57-.08 1.76-.72 2.01-1.41.25-.7.25-1.3.17-1.41-.07-.12-.27-.2-.57-.35z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Belum ada customer
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">Belum ada customer</p>
                    </div>
                @endif
            </div>

            @if ($customers->lastPage() > 1)
                @php
                    $currentPage = $customers->currentPage();
                    $lastPage = $customers->lastPage();
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
                        <div>Total customer: {{ number_format($customers->total()) }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $customers->previousPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $customers->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}"
                               aria-label="Sebelumnya">
                                <x-heroicon name="chevron-left" class="w-4 h-4" />
                            </a>
                            @php $prev = null; @endphp
                            @foreach ($pages as $page)
                                @if ($prev && $page > $prev + 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $customers->url($page) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $page === $currentPage ? 'border-primary bg-primary text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                                    {{ $page }}
                                </a>
                                @php $prev = $page; @endphp
                            @endforeach
                            <a href="{{ $customers->nextPageUrl() ?? '#' }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $customers->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}"
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
