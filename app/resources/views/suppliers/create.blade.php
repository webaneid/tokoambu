<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('suppliers.index') }}" class="text-blue-600 hover:underline">Supplier</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-900">Tambah Supplier</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-6">Form Tambah Supplier</h3>

                    <form action="{{ route('suppliers.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Supplier <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('name') border-red-500 @enderror" placeholder="Nama Supplier" required>
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('email') border-red-500 @enderror" placeholder="email@example.com">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('phone') border-red-500 @enderror" placeholder="08xx xxxx xxxx">
                            @error('phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <label for="whatsapp_number" class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                                <label class="flex items-center space-x-2 text-sm text-gray-600">
                                    <input type="checkbox" id="waSameAsPhone" class="rounded border-gray-300">
                                    <span>Samakan dengan telepon</span>
                                </label>
                            </div>
                            <input type="text" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('whatsapp_number') border-red-500 @enderror" placeholder="62xxxxxxxxxx">
                            <p class="text-xs text-gray-500 mt-1">Nomor WhatsApp harus lengkap dengan kode negara (contoh 62 tanpa tanda +).</p>
                            @error('whatsapp_number')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <textarea id="address" name="address" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('address') border-red-500 @enderror" placeholder="Alamat lengkap">{{ old('address') }}</textarea>
                            @error('address')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="border-t pt-6 mt-8">
                            <h4 class="text-md font-semibold text-gray-900 mb-4">Lokasi</h4>

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
                                            autocomplete="off"
                                            value="{{ old('province_name') }}">
                                        <input type="hidden" name="province_id" id="provinceId" value="{{ old('province_id') }}">
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
                                            value="{{ old('city_name') }}"
                                            {{ old('province_id') ? '' : 'disabled' }}>
                                        <input type="hidden" name="city_id" id="cityId" value="{{ old('city_id') }}">
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
                                            value="{{ old('district_name') }}"
                                            {{ old('city_id') ? '' : 'disabled' }}>
                                        <input type="hidden" name="district_id" id="districtId" value="{{ old('district_id') }}">
                                        <div id="districtSuggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 hidden max-h-48 overflow-y-auto shadow-lg"></div>
                                    </div>
                                    @error('district_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Kode Pos -->
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Pos</label>
                                <input type="text" name="postal_code" id="postalCode" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" readonly value="{{ old('postal_code') }}">
                                @error('postal_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <p class="text-xs text-gray-500 mt-4">Alamat di atas sudah mewakili alamat lengkap.</p>
                        </div>

                        <div class="border-t pt-6 mt-8">
                            <h4 class="text-md font-semibold text-gray-900 mb-4">Rekening Supplier</h4>
                            <div id="bankAccountsContainer" class="space-y-3 mb-4"></div>
                            <button type="button" id="addBankAccount" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover">+ Tambah Rekening</button>
                        </div>

                        <div class="mb-6 mt-8">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                            <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('notes') border-red-500 @enderror" placeholder="Catatan tambahan">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('suppliers.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Batal</a>
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Simpan</button>
                        </div>
                    </form>
                </div>
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
        const phoneInput = document.getElementById('phone');

        const syncWaIfChecked = () => {
            if (waSameCheckbox.checked) {
                waInput.value = phoneInput.value || '';
            }
        };

        waSameCheckbox?.addEventListener('change', syncWaIfChecked);
        phoneInput?.addEventListener('input', syncWaIfChecked);

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

        // Bank accounts repeater (create)
        const bankContainer = document.getElementById('bankAccountsContainer');
        const addBankBtn = document.getElementById('addBankAccount');
        let bankCount = 0;

        addBankBtn?.addEventListener('click', () => {
            const idx = bankCount++;
            const wrapper = document.createElement('div');
            wrapper.className = 'grid grid-cols-1 md:grid-cols-2 gap-3 items-end bg-gray-50 p-3 rounded';
            wrapper.innerHTML = `
                <input type="text" name="bank_accounts[${idx}][bank_name]" placeholder="Bank" class="w-full px-3 py-2 border rounded-lg" required>
                <input type="text" name="bank_accounts[${idx}][bank_code]" placeholder="Kode Bank" class="w-full px-3 py-2 border rounded-lg">
                <input type="text" name="bank_accounts[${idx}][account_number]" placeholder="No. Rekening" class="w-full px-3 py-2 border rounded-lg" required>
                <div class="flex gap-2">
                    <input type="text" name="bank_accounts[${idx}][account_name]" placeholder="Nama Rekening" class="w-full px-3 py-2 border rounded-lg" required>
                    <button type="button" class="px-3 py-2 bg-red-500 text-white rounded remove-bank">Hapus</button>
                </div>
            `;
            bankContainer.appendChild(wrapper);
        });

        bankContainer?.addEventListener('click', (e) => {
            if (e.target.closest('.remove-bank')) {
                e.target.closest('.grid')?.remove();
            }
        });
    </script>
</x-app-layout>
