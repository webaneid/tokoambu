<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="{{ route('suppliers.index') }}" class="text-blue-600 hover:underline">Supplier</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-900">{{ $supplier->name }}</span>
            </div>
            <div class="space-x-2">
                @can('edit_suppliers')
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">
                        Edit
                    </a>
                @endcan
                <a href="{{ route('suppliers.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-6">Detail Supplier</h3>

                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div>
                            <p class="text-sm text-gray-600">Nama Supplier</p>
                            <p class="text-lg font-semibold">{{ $supplier->name }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="text-lg">
                                <span class="px-3 py-1 {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} rounded-full text-sm">
                                    {{ $supplier->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="text-lg">{{ $supplier->email ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">No. Telepon</p>
                            <p class="text-lg">{{ $supplier->phone ?? '-' }}</p>
                        </div>

                        <div class="flex items-center justify-between">
                            @php
                                $phoneClean = preg_replace('/\\D+/', '', $supplier->phone ?? '');
                                $waClean = preg_replace('/\\D+/', '', $supplier->whatsapp_number ?? '');
                                $waSame = $phoneClean && $waClean && $phoneClean === $waClean;
                                $waLink = $waClean ? 'https://api.whatsapp.com/send?phone=' . $waClean : null;
                            @endphp
                            <div class="flex items-center space-x-2">
                                <p class="text-lg">
                                    {{ $supplier->whatsapp_number ?? '-' }}
                                    @if ($waSame)
                                        <span class="ml-2 text-xs text-gray-500">(sama dengan telepon)</span>
                                    @endif
                                </p>
                                @if ($waLink)
                                    <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center space-x-2 px-3 py-1 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" class="fill-current">
                                            <path d="M13.95 4.24C11.86 1 7.58.04 4.27 2.05C1.04 4.06 0 8.44 2.09 11.67l.17.26l-.7 2.62l2.62-.7l.26.17c1.13.61 2.36.96 3.58.96c1.31 0 2.62-.35 3.75-1.05c3.23-2.1 4.19-6.39 2.18-9.71Zm-1.83 6.74c-.35.52-.79.87-1.4.96c-.35 0-.79.17-2.53-.52c-1.48-.7-2.71-1.84-3.58-3.15c-.52-.61-.79-1.4-.87-2.19c0-.7.26-1.31.7-1.75c.17-.17.35-.26.52-.26h.44c.17 0 .35 0 .44.35c.17.44.61 1.49.61 1.58c.09.09.05.76-.35 1.14c-.22.25-.26.26-.17.44c.35.52.79 1.05 1.22 1.49c.52.44 1.05.79 1.66 1.05c.17.09.35.09.44-.09c.09-.17.52-.61.7-.79c.17-.17.26-.17.44-.09l1.4.7c.17.09.35.17.44.26c.09.26.09.61-.09.87Z"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-8 pt-6 border-t">
                        <p class="text-sm text-gray-600 mb-3">Alamat & Lokasi</p>
                        <div class="space-y-2">
                            <div>
                                <p class="text-gray-800">{{ $supplier->address ?? '-' }}</p>
                            </div>
                            <div class="text-sm text-gray-700">
                                <div>Kecamatan: {{ $supplier->district->name ?? '-' }}</div>
                                <div>Kab/Kota: {{ $supplier->city->name ?? '-' }}</div>
                                <div>Provinsi: {{ $supplier->province->name ?? '-' }}</div>
                                <div>Kode Pos: {{ $supplier->postal_code ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    @if ($supplier->notes)
                        <div class="mb-8 pt-6 border-t">
                            <p class="text-sm text-gray-600 mb-2">Catatan</p>
                            <p class="text-gray-700">{{ $supplier->notes }}</p>
                        </div>
                    @endif

                    <div class="mb-8 pt-6 border-t">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm text-gray-600">Rekening Supplier</p>
                        </div>
                        @can('edit_suppliers')
                            <p class="text-sm text-gray-500 mb-4">
                                Tambah atau ubah rekening di halaman
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="text-primary hover:underline">Edit Supplier</a>.
                            </p>
                        @endcan
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bank</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No. Rekening</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Atas Nama</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200" id="supplierBankBody">
                                    @forelse($supplier->bankAccounts as $account)
                                        <tr>
                                            <td class="px-4 py-2 text-sm">{{ $account->bank_name }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $account->bank_code ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $account->account_number }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $account->account_name }}</td>
                                            <td class="px-4 py-2 text-right text-sm">
                                                @can('edit_suppliers')
                                                    <button data-delete-url="{{ route('suppliers.bank_accounts.destroy', [$supplier, $account]) }}" class="text-red-500 hover:text-red-700 bank-delete-btn">Hapus</button>
                                                @else
                                                    -
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="empty-row">
                                            <td colspan="5" class="px-4 py-3 text-center text-sm text-gray-500">Belum ada rekening.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produk Supplier -->
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-6 text-gray-900">Produk dari Supplier Ini</h3>

                    @if ($supplier->products->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse border border-gray-300">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Nama Produk</th>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Kategori</th>
                                        <th class="border border-gray-300 px-4 py-2 text-right">Jumlah Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($supplier->products as $product)
                                        <tr class="hover:bg-gray-50">
                                            <td class="border border-gray-300 px-4 py-2">
                                                <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:underline font-medium">
                                                    {{ $product->name }}
                                                </a>
                                            </td>
                                            <td class="border border-gray-300 px-4 py-2">{{ $product->category?->name ?? '-' }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right">{{ number_format($product->qty_on_hand, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">Tidak ada produk dari supplier ini</p>
                        </div>
                    @endif
        </div>
    </div>

    <script>
        const bankForm = document.getElementById('supplierBankForm');
        const bankBody = document.getElementById('supplierBankBody');

        bankForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(bankForm);
            try {
                const res = await fetch(bankForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: formData
                });
                if (res.status === 201) {
                    const data = await res.json();
                    appendBankRow(data.account);
                    bankForm.reset();
                }
            } catch (err) {
                console.error('Gagal tambah rekening', err);
            }
        });

        function appendBankRow(account) {
            if (!bankBody) return;
            bankBody.querySelectorAll('.empty-row').forEach(r => r.remove());
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-4 py-2 text-sm">${account.bank_name}</td>
                <td class="px-4 py-2 text-sm">${account.bank_code ?? '-'}</td>
                <td class="px-4 py-2 text-sm">${account.account_number}</td>
                <td class="px-4 py-2 text-sm">${account.account_name}</td>
                <td class="px-4 py-2 text-right text-sm">
                    <button data-delete-url="${bankForm.action}/${account.id}" class="text-red-500 hover:text-red-700 bank-delete-btn">Hapus</button>
                </td>
            `;
            bankBody.appendChild(tr);
        }

        bankBody?.addEventListener('click', async (e) => {
            const btn = e.target.closest('.bank-delete-btn');
            if (!btn) return;
            e.preventDefault();
            if (!confirm('Hapus rekening ini?')) return;
            const url = btn.dataset.deleteUrl;
            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                });
                if (res.ok) {
                    btn.closest('tr')?.remove();
                }
            } catch (err) {
                console.error('Gagal hapus rekening', err);
            }
        });
    </script>
</x-app-layout>
