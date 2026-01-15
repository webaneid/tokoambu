<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('vendors.index') }}" class="text-blue-600 hover:underline">Vendor</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-900">Edit Vendor</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-6">Form Edit Vendor</h3>

                    <form action="{{ route('vendors.update', $vendor) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Vendor <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $vendor->name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('name') border-red-500 @enderror" placeholder="Nama Vendor" required>
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $vendor->email) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('email') border-red-500 @enderror" placeholder="email@example.com">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $vendor->phone) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('phone') border-red-500 @enderror" placeholder="08xx xxxx xxxx">
                            @error('phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <textarea id="address" name="address" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('address') border-red-500 @enderror" placeholder="Alamat lengkap">{{ old('address', $vendor->address) }}</textarea>
                            @error('address')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="border-t pt-6 mt-8">
                            <h4 class="text-md font-semibold text-gray-900 mb-4">Rekening Vendor</h4>
                            <div id="bankAccountsContainer" class="space-y-3 mb-4">
                                @foreach($vendor->bankAccounts as $account)
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 p-4 border border-gray-200 rounded-lg relative">
                                    <input type="hidden" name="bank_accounts[{{ $loop->index }}][id]" value="{{ $account->id }}">
                                    <div>
                                        <input type="text" name="bank_accounts[{{ $loop->index }}][bank_name]" value="{{ $account->bank_name }}" placeholder="Nama Bank" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" required>
                                    </div>
                                    <div>
                                        <input type="text" name="bank_accounts[{{ $loop->index }}][account_number]" value="{{ $account->account_number }}" placeholder="Nomor Rekening" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" required>
                                    </div>
                                    <div>
                                        <input type="text" name="bank_accounts[{{ $loop->index }}][account_name]" value="{{ $account->account_name }}" placeholder="Nama Pemilik" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" required>
                                    </div>
                                    <div class="flex items-center">
                                        <button type="button" class="remove-bank-account text-red-500 hover:text-red-700 text-sm">Hapus</button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" id="addBankAccount" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover">+ Tambah Rekening</button>
                        </div>

                        <div class="mb-6 mt-8">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                            <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('notes') border-red-500 @enderror" placeholder="Catatan tambahan">{{ old('notes', $vendor->notes) }}</textarea>
                            @error('notes')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $vendor->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Vendor Aktif</span>
                            </label>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('vendors.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Batal</a>
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let bankAccountIndex = {{ $vendor->bankAccounts->count() }};
        const container = document.getElementById('bankAccountsContainer');
        const addButton = document.getElementById('addBankAccount');

        addButton.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 md:grid-cols-4 gap-3 p-4 border border-gray-200 rounded-lg relative';
            row.innerHTML = `
                <div>
                    <input type="text" name="bank_accounts[${bankAccountIndex}][bank_name]" placeholder="Nama Bank" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" required>
                </div>
                <div>
                    <input type="text" name="bank_accounts[${bankAccountIndex}][account_number]" placeholder="Nomor Rekening" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" required>
                </div>
                <div>
                    <input type="text" name="bank_accounts[${bankAccountIndex}][account_name]" placeholder="Nama Pemilik" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" required>
                </div>
                <div class="flex items-center">
                    <button type="button" class="remove-bank-account text-red-500 hover:text-red-700 text-sm">Hapus</button>
                </div>
            `;
            container.appendChild(row);
            bankAccountIndex++;
        });

        container.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-bank-account')) {
                e.target.closest('.grid').remove();
            }
        });
    </script>
</x-app-layout>
