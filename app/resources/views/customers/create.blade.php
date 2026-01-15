<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Tambah Customer</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <form action="{{ route('customers.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. HP</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                            <label class="flex items-center space-x-2 text-sm text-gray-600">
                                <input type="checkbox" id="waSameAsPhone" class="rounded border-gray-300">
                                <span>Samakan dengan telepon</span>
                            </label>
                        </div>
                        <input type="text" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="62xxxxxxxxxx">
                        <p class="text-xs text-gray-500 mt-1">Nomor WhatsApp harus lengkap dengan kode negara (contoh 62 tanpa tanda +).</p>
                        @error('whatsapp_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                        <textarea name="address" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="4">{{ old('address') }}</textarea>
                        @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Location Fields -->
                    <div class="border-t pt-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Lokasi Pengiriman</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Provinsi Autocomplete -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Provinsi</label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        id="provinceInput" 
                                        placeholder="Ketik provinsi..." 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                        autocomplete="off">
                                    <input type="hidden" name="province_id" id="provinceId">
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
                                        disabled>
                                    <input type="hidden" name="city_id" id="cityId">
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
                                        disabled>
                                    <input type="hidden" name="district_id" id="districtId">
                                    <div id="districtSuggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 hidden max-h-48 overflow-y-auto shadow-lg"></div>
                                </div>
                                @error('district_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Kode Pos -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kode Pos</label>
                            <input type="text" name="postal_code" id="postalCode" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" readonly>
                            @error('postal_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Full Address -->
                        <div class="mt-4">
                            <p class="text-xs text-gray-500">Alamat di atas sudah mewakili alamat lengkap.</p>
                        </div>
                    </div>

                    <!-- Bank Accounts Section (Optional) -->
                    <div class="border-t pt-6 mt-8">
                        <h4 class="text-md font-semibold text-gray-900 mb-2">Rekening Bank Customer (Opsional)</h4>
                        <p class="text-sm text-gray-600 mb-4">Untuk keperluan refund jika terjadi pembatalan order</p>

                        <div id="bankAccountsContainer" class="space-y-3">
                            <!-- Bank accounts will be added here -->
                        </div>

                        <button type="button" id="addBankAccountBtn" class="mt-3 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                            + Tambah Rekening
                        </button>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="is_active" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
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
        const phoneInput = document.querySelector('input[name=\"phone\"]');

        const syncWaIfChecked = () => {
            if (waSameCheckbox.checked) {
                waInput.value = phoneInput.value || '';
            }
        };

        waSameCheckbox?.addEventListener('change', syncWaIfChecked);
        phoneInput?.addEventListener('input', syncWaIfChecked);

        // Load initial province suggestions on focus
        provinceInput.addEventListener('focus', () => {
            if (!provinceInput.value) {
                loadProvinceSuggestions('');
            }
        });

        // Province input - autocomplete
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

                            // Reset city and district
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

        // City input - autocomplete (depends on province)
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

                            // Reset district
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

        // District input - autocomplete (depends on city)
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

        // Close suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.relative')) {
                provinceSuggestions.classList.add('hidden');
                citySuggestions.classList.add('hidden');
                districtSuggestions.classList.add('hidden');
            }
        });

        // Bank Accounts Management
        let bankAccountIndex = 0;
        const bankAccountsContainer = document.getElementById('bankAccountsContainer');
        const addBankAccountBtn = document.getElementById('addBankAccountBtn');

        addBankAccountBtn.addEventListener('click', () => {
            const accountRow = document.createElement('div');
            accountRow.className = 'bg-gray-50 p-4 rounded-lg border border-gray-200';
            accountRow.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nama Bank</label>
                        <input type="text" name="bank_accounts[${bankAccountIndex}][bank_name]" placeholder="contoh: BCA, Mandiri" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Kode Bank (opsional)</label>
                        <input type="text" name="bank_accounts[${bankAccountIndex}][bank_code]" placeholder="contoh: 014" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Rekening</label>
                        <input type="text" name="bank_accounts[${bankAccountIndex}][account_number]" placeholder="Nomor rekening" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nama Pemilik</label>
                            <input type="text" name="bank_accounts[${bankAccountIndex}][account_name]" placeholder="Nama pemilik rekening" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="remove-bank-btn px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 text-sm">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            `;

            bankAccountsContainer.appendChild(accountRow);
            bankAccountIndex++;

            // Add remove functionality
            accountRow.querySelector('.remove-bank-btn').addEventListener('click', () => {
                accountRow.remove();
            });
        });
    </script>
</x-app-layout>
