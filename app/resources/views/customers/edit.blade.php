<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Edit Customer</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                        <input type="text" name="name" value="{{ $customer->name }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ $customer->email }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. HP</label>
                        <input type="tel" name="phone" value="{{ $customer->phone }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        @php
                            $waSame = old('whatsapp_number', $customer->whatsapp_number) === old('phone', $customer->phone);
                        @endphp
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                            <label class="flex items-center space-x-2 text-sm text-gray-600">
                                <input type="checkbox" id="waSameAsPhone" class="rounded border-gray-300" {{ $waSame ? 'checked' : '' }}>
                                <span>Samakan dengan telepon</span>
                            </label>
                        </div>
                        <input type="text" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number', $customer->whatsapp_number) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="62xxxxxxxxxx">
                        <p class="text-xs text-gray-500 mt-1">Nomor WhatsApp harus lengkap dengan kode negara (contoh 62 tanpa tanda +).</p>
                        @error('whatsapp_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                        <textarea name="address" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="4">{{ $customer->address }}</textarea>
                        @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Location Fields (Autocomplete) -->
                    <div class="border-t pt-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Lokasi Pengiriman</h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Provinsi Autocomplete -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Provinsi</label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        id="provinceInput" 
                                        placeholder="Ketik provinsi..." 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                        autocomplete="off"
                                        value="{{ old('province_name', optional($customer->province)->name) }}">
                                    <input type="hidden" name="province_id" id="provinceId" value="{{ old('province_id', $customer->province_id) }}">
                                    <div id="provinceSuggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 hidden max-h-48 overflow-y-auto shadow-lg"></div>
                                </div>
                                @error('province_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Kota Autocomplete -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kota/Kabupaten</label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        id="cityInput" 
                                        placeholder="Ketik kota..." 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                        autocomplete="off"
                                        value="{{ old('city_name', optional($customer->city)->name) }}"
                                        {{ old('province_id', $customer->province_id) ? '' : 'disabled' }}>
                                    <input type="hidden" name="city_id" id="cityId" value="{{ old('city_id', $customer->city_id) }}">
                                    <div id="citySuggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 hidden max-h-48 overflow-y-auto shadow-lg"></div>
                                </div>
                                @error('city_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Kecamatan Autocomplete -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kecamatan</label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        id="districtInput" 
                                        placeholder="Ketik kecamatan..." 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                        autocomplete="off"
                                        value="{{ old('district_name', optional($customer->district)->name) }}"
                                        {{ old('city_id', $customer->city_id) ? '' : 'disabled' }}>
                                    <input type="hidden" name="district_id" id="districtId" value="{{ old('district_id', $customer->district_id) }}">
                                    <div id="districtSuggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 hidden max-h-48 overflow-y-auto shadow-lg"></div>
                                </div>
                                @error('district_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Kode Pos -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Pos</label>
                                <input type="text" name="postal_code" id="postalCode" value="{{ old('postal_code', $customer->postal_code) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" readonly>
                                @error('postal_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="is_active" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="1" {{ $customer->is_active ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ !$customer->is_active ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <!-- Bank Accounts Section -->
                    <div class="border-t pt-6 mt-8">
                        <h4 class="text-md font-semibold text-gray-900 mb-2">Rekening Bank Customer</h4>
                        <p class="text-sm text-gray-600 mb-4">Untuk keperluan refund jika terjadi pembatalan order</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                            <input type="text" id="bankName" placeholder="Nama Bank (contoh: BCA, Mandiri)" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <input type="text" id="bankCode" placeholder="Kode Bank (opsional)" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <input type="text" id="accountNumber" placeholder="Nomor Rekening" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <div class="flex gap-2">
                                <input type="text" id="accountName" placeholder="Nama Pemilik Rekening" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <button type="button" id="addBankBtn" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover whitespace-nowrap">Tambah</button>
                            </div>
                        </div>

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
                                <tbody class="divide-y divide-gray-200" id="customerBankBody" data-store-url="{{ route('customers.bank_accounts.store', $customer) }}">
                                    @forelse($customer->bankAccounts as $account)
                                        <tr>
                                            <td class="px-4 py-2 text-sm">{{ $account->bank_name }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $account->bank_code ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $account->account_number }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $account->account_name }}</td>
                                            <td class="px-4 py-2 text-right text-sm">
                                                <button type="button" data-delete-url="{{ route('customers.bank_accounts.destroy', [$customer, $account]) }}" class="text-red-500 hover:text-red-700 bank-delete-btn">Hapus</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="empty-row">
                                            <td colspan="5" class="px-4 py-3 text-center text-sm text-gray-500">Belum ada rekening. Rekening akan digunakan untuk proses refund.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-6">
                        <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-hover transition-colors">
                            Simpan
                        </button>
                        <a href="{{ route('customers.index') }}" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 text-center">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const provinceInput = document.getElementById('provinceInput');
        const provinceId = document.getElementById('provinceId');
        const provinceSuggestions = document.getElementById('provinceSuggestions');

        const cityInput = document.getElementById('cityInput');
        const cityId = document.getElementById('cityId');
        const citySuggestions = document.getElementById('citySuggestions');

        const districtInput = document.getElementById('districtInput');
        const districtId = document.getElementById('districtId');
        const districtSuggestions = document.getElementById('districtSuggestions');

        const postalCode = document.getElementById('postalCode');
        const waInput = document.getElementById('whatsapp_number');
        const waSameCheckbox = document.getElementById('waSameAsPhone');
        const phoneInput = document.querySelector('input[name="phone"]');

        const syncWaIfChecked = () => {
            if (waSameCheckbox?.checked) {
                waInput.value = phoneInput.value || '';
            }
        };

        waSameCheckbox?.addEventListener('change', syncWaIfChecked);
        phoneInput?.addEventListener('input', syncWaIfChecked);
        syncWaIfChecked();

        provinceInput.addEventListener('focus', () => {
            if (!provinceInput.value) {
                loadProvinceSuggestions('');
            }
        });

        provinceInput.addEventListener('input', () => {
            const query = provinceInput.value;
            loadProvinceSuggestions(query);
        });

        function loadProvinceSuggestions(query) {
            const url = new URL('/api/provinces/search', window.location.origin);
            if (query) url.searchParams.append('q', query);
            url.searchParams.append('limit', 6);

            fetch(url)
                .then(r => r.json())
                .then(provinces => {
                    provinceSuggestions.innerHTML = '';
                    if (provinces.length === 0) {
                        provinceSuggestions.innerHTML = '<div class="p-2 text-gray-500">Tidak ada hasil</div>';
                        provinceSuggestions.classList.remove('hidden');
                        return;
                    }

                    provinces.forEach(p => {
                        const div = document.createElement('div');
                        div.className = 'p-2 hover:bg-blue-100 cursor-pointer border-b';
                        div.textContent = p.name;
                        div.addEventListener('click', () => {
                            provinceInput.value = p.name;
                            provinceId.value = p.code;
                            provinceSuggestions.classList.add('hidden');

                            cityInput.value = '';
                            cityId.value = '';
                            citySuggestions.innerHTML = '';
                            citySuggestions.classList.add('hidden');
                            cityInput.disabled = false;

                            districtInput.value = '';
                            districtId.value = '';
                            districtSuggestions.innerHTML = '';
                            districtSuggestions.classList.add('hidden');
                            districtInput.disabled = true;

                            postalCode.value = '';
                        });
                        provinceSuggestions.appendChild(div);
                    });

                    provinceSuggestions.classList.remove('hidden');
                });
        }

        cityInput.addEventListener('focus', () => {
            if (!provinceId.value) {
                alert('Pilih provinsi dulu');
                provinceInput.focus();
                return;
            }
            if (!cityInput.value) {
                loadCitySuggestions('');
            }
        });

        cityInput.addEventListener('input', () => {
            if (!provinceId.value) {
                alert('Pilih provinsi dulu');
                provinceInput.focus();
                return;
            }
            const query = cityInput.value;
            loadCitySuggestions(query);
        });

        function loadCitySuggestions(query) {
            const url = new URL('/api/cities/search', window.location.origin);
            if (query) url.searchParams.append('q', query);
            url.searchParams.append('province_code', provinceId.value);
            url.searchParams.append('limit', 6);

            fetch(url)
                .then(r => r.json())
                .then(cities => {
                    citySuggestions.innerHTML = '';
                    if (cities.length === 0) {
                        citySuggestions.innerHTML = '<div class="p-2 text-gray-500">Tidak ada hasil</div>';
                        citySuggestions.classList.remove('hidden');
                        return;
                    }

                    cities.forEach(c => {
                        const div = document.createElement('div');
                        div.className = 'p-2 hover:bg-blue-100 cursor-pointer border-b';
                        div.innerHTML = `<div class="font-medium">${c.name}</div><div class="text-xs text-gray-500">${c.type || 'Kota'}</div>`;
                        div.addEventListener('click', () => {
                            cityInput.value = c.name;
                            cityId.value = c.code;
                            citySuggestions.classList.add('hidden');

                            districtInput.value = '';
                            districtId.value = '';
                            districtSuggestions.innerHTML = '';
                            districtSuggestions.classList.add('hidden');
                            districtInput.disabled = false;
                            postalCode.value = '';
                        });
                        citySuggestions.appendChild(div);
                    });

                    citySuggestions.classList.remove('hidden');
                });
        }

        districtInput.addEventListener('focus', () => {
            if (!cityId.value) {
                alert('Pilih kota dulu');
                cityInput.focus();
                return;
            }
            if (!districtInput.value) {
                loadDistrictSuggestions('');
            }
        });

        districtInput.addEventListener('input', () => {
            if (!cityId.value) {
                alert('Pilih kota dulu');
                cityInput.focus();
                return;
            }
            const query = districtInput.value;
            loadDistrictSuggestions(query);
        });

        function loadDistrictSuggestions(query) {
            const url = new URL('/api/districts/search', window.location.origin);
            if (query) url.searchParams.append('q', query);
            url.searchParams.append('city_code', cityId.value);
            url.searchParams.append('limit', 6);

            fetch(url)
                .then(r => r.json())
                .then(districts => {
                    districtSuggestions.innerHTML = '';
                    if (districts.length === 0) {
                        districtSuggestions.innerHTML = '<div class="p-2 text-gray-500">Tidak ada hasil</div>';
                        districtSuggestions.classList.remove('hidden');
                        return;
                    }

                    districts.forEach(d => {
                        const div = document.createElement('div');
                        div.className = 'p-2 hover:bg-blue-100 cursor-pointer border-b';
                        div.textContent = d.name;
                        div.addEventListener('click', () => {
                            districtInput.value = d.name;
                            districtId.value = d.code;
                            postalCode.value = d.postal_code || '';
                            districtSuggestions.classList.add('hidden');
                        });
                        districtSuggestions.appendChild(div);
                    });

                    districtSuggestions.classList.remove('hidden');
                });
        }

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.relative')) {
                provinceSuggestions.classList.add('hidden');
                citySuggestions.classList.add('hidden');
                districtSuggestions.classList.add('hidden');
            }
        });

        // Enable dependent fields if existing data present
        if (provinceId.value) {
            cityInput.disabled = false;
        }
        if (cityId.value) {
            districtInput.disabled = false;
        }

        // Bank Account Management
        const bankNameInput = document.getElementById('bankName');
        const bankCodeInput = document.getElementById('bankCode');
        const accountNumberInput = document.getElementById('accountNumber');
        const accountNameInput = document.getElementById('accountName');
        const addBankBtn = document.getElementById('addBankBtn');
        const bankBody = document.getElementById('customerBankBody');
        const bankStoreUrl = bankBody?.dataset.storeUrl;

        addBankBtn?.addEventListener('click', async () => {
            const payload = {
                bank_name: bankNameInput.value.trim(),
                bank_code: bankCodeInput.value.trim(),
                account_number: accountNumberInput.value.trim(),
                account_name: accountNameInput.value.trim(),
            };
            if (!payload.bank_name || !payload.account_number || !payload.account_name) {
                alert('Lengkapi Nama Bank, Nomor Rekening, dan Nama Pemilik Rekening');
                return;
            }

            try {
                const res = await fetch(bankStoreUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify(payload),
                });
                if (res.status === 201) {
                    const data = await res.json();
                    appendBankRow(data.account);
                    bankNameInput.value = '';
                    bankCodeInput.value = '';
                    accountNumberInput.value = '';
                    accountNameInput.value = '';
                }
            } catch (err) {
                console.error('Gagal tambah rekening', err);
                alert('Gagal menambahkan rekening');
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
                    <button type="button" data-delete-url="${bankStoreUrl}/${account.id}" class="text-red-500 hover:text-red-700 bank-delete-btn">Hapus</button>
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
                    if (bankBody.querySelectorAll('tr').length === 0) {
                        const empty = document.createElement('tr');
                        empty.classList.add('empty-row');
                        empty.innerHTML = `<td colspan="5" class="px-4 py-3 text-center text-sm text-gray-500">Belum ada rekening. Rekening akan digunakan untuk proses refund.</td>`;
                        bankBody.appendChild(empty);
                    }
                }
            } catch (err) {
                console.error('Gagal hapus rekening', err);
                alert('Gagal menghapus rekening');
            }
        });
    </script>
</x-app-layout>
