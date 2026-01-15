@extends('storefront.layouts.app-mobile')

@section('title', 'Profil Saya - Toko Ambu')

@section('content')
<div class="storefront-app">
    <main class="storefront-main">

        {{-- ========================================== --}}
        {{-- HEADER (Sticky) --}}
        {{-- ========================================== --}}
        <div class="profile-header">
            <a href="{{ route('customer.dashboard') }}" class="back-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="profile-title">Profil Saya</h1>
            <div style="width: 24px;"></div>
        </div>

        {{-- ========================================== --}}
        {{-- ALERTS --}}
        {{-- ========================================== --}}
        @if (session('success'))
        <div class="alert alert-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <div>
                @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ========================================== --}}
        {{-- PROFILE AVATAR SECTION --}}
        {{-- ========================================== --}}
        <div class="profile-avatar-section">
            <div class="profile-avatar">
                @if($customer->avatar)
                    <img src="{{ Storage::url($customer->avatar) }}" alt="{{ $customer->name }}">
                @else
                    @php
                        $gravatarHash = md5(strtolower(trim($customer->email)));
                        $gravatarUrl = "https://www.gravatar.com/avatar/{$gravatarHash}?s=160&d=mp";
                    @endphp
                    <img src="{{ $gravatarUrl }}" alt="{{ $customer->name }}" loading="lazy">
                @endif
            </div>
            <h2 class="profile-name">{{ $customer->name }}</h2>
            <p class="profile-email">{{ $customer->email }}</p>
            <p class="profile-joined">Bergabung sejak {{ $customer->created_at->format('d M Y') }}</p>
        </div>

        {{-- ========================================== --}}
        {{-- PERSONAL INFO FORM --}}
        {{-- ========================================== --}}
        <div class="profile-section">
            <div class="profile-section__header">
                <h3 class="profile-section__title">Informasi Pribadi</h3>
            </div>

            <form action="{{ route('customer.profile.update') }}" method="POST" class="profile-form">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input 
                        type="text" 
                        class="form-input @error('name') is-invalid @enderror" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $customer->name) }}" 
                        required
                    >
                    @error('name')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        class="form-input @error('email') is-invalid @enderror" 
                        id="email" 
                        name="email" 
                        value="{{ old('email', $customer->email) }}" 
                        required
                    >
                    @error('email')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Nomor Telepon</label>
                    <input 
                        type="tel" 
                        class="form-input @error('phone') is-invalid @enderror" 
                        id="phone" 
                        name="phone" 
                        value="{{ old('phone', $customer->phone) }}" 
                        required
                        placeholder="08123456789"
                    >
                    @error('phone')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="whatsapp_number" class="form-label">Nomor WhatsApp</label>
                    <input 
                        type="tel" 
                        class="form-input @error('whatsapp_number') is-invalid @enderror" 
                        id="whatsapp_number" 
                        name="whatsapp_number" 
                        value="{{ old('whatsapp_number', $customer->whatsapp_number) }}"
                        placeholder="628123456789 (bisa sama dengan telepon)"
                    >
                    @error('whatsapp_number')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                    <small class="form-hint">Nomor WhatsApp untuk komunikasi pesanan WAJIB diawali kodenagara</small>
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Alamat Lengkap</label>
                    <textarea 
                        class="form-input @error('address') is-invalid @enderror" 
                        id="address" 
                        name="address" 
                        rows="3"
                        placeholder="Jalan, No. Rumah, RT/RW, Desa/Kelurahan"
                    >{{ old('address', $customer->address) }}</textarea>
                    @error('address')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="province_search" class="form-label">Provinsi</label>
                    <div class="relative">
                        <input
                            type="text"
                            id="province_search"
                            autocomplete="off"
                            placeholder="Ketik untuk mencari provinsi..."
                            class="form-input @error('province_id') is-invalid @enderror"
                        >
                        <input type="hidden" id="province_id" name="province_id" value="{{ old('province_id', $customer->province_id) }}">
                        <div id="province_dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                    </div>
                    @error('province_id')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="city_search" class="form-label">Kabupaten/Kota</label>
                    <div class="relative">
                        <input
                            type="text"
                            id="city_search"
                            autocomplete="off"
                            placeholder="Pilih provinsi terlebih dahulu..."
                            class="form-input @error('city_id') is-invalid @enderror"
                            disabled
                        >
                        <input type="hidden" id="city_id" name="city_id" value="{{ old('city_id', $customer->city_id) }}">
                        <div id="city_dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                    </div>
                    @error('city_id')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="district_search" class="form-label">Kecamatan</label>
                    <div class="relative">
                        <input
                            type="text"
                            id="district_search"
                            autocomplete="off"
                            placeholder="Pilih kabupaten/kota terlebih dahulu..."
                            class="form-input @error('district_id') is-invalid @enderror"
                            disabled
                        >
                        <input type="hidden" id="district_id" name="district_id" value="{{ old('district_id', $customer->district_id) }}">
                        <div id="district_dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                    </div>
                    @error('district_id')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="postal_code" class="form-label">Kode Pos</label>
                    <input
                        type="text"
                        class="form-input @error('postal_code') is-invalid @enderror"
                        id="postal_code"
                        name="postal_code"
                        value="{{ old('postal_code', $customer->postal_code) }}"
                        placeholder="12345"
                        maxlength="5"
                    >
                    @error('postal_code')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Bank Account Section --}}
                <div class="form-group" style="margin-top: var(--ane-spacing-xl);">
                    <div style="padding: var(--ane-spacing-md); background: var(--ane-color-gray-100); border-radius: var(--ane-radius-md); margin-bottom: var(--ane-spacing-md);">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px; color: var(--ane-color-primary); display: inline-block; vertical-align: middle; margin-right: 8px;">
                            <path d="M2.273 5.625A4.483 4.483 0 0 1 5.25 4.5h13.5c1.141 0 2.183.425 2.977 1.125A3 3 0 0 0 18.75 3H5.25a3 3 0 0 0-2.977 2.625ZM2.273 8.625A4.483 4.483 0 0 1 5.25 7.5h13.5c1.141 0 2.183.425 2.977 1.125A3 3 0 0 0 18.75 6H5.25a3 3 0 0 0-2.977 2.625ZM5.25 9a3 3 0 0 0-3 3v6a3 3 0 0 0 3 3h13.5a3 3 0 0 0 3-3v-6a3 3 0 0 0-3-3H15a.75.75 0 0 0-.75.75 2.25 2.25 0 0 1-4.5 0A.75.75 0 0 0 9 9H5.25Z" />
                        </svg>
                        <small style="color: var(--ane-color-text-secondary); line-height: 1.5;">
                            <strong>Informasi Rekening Bank</strong><br>
                            Data rekening diperlukan untuk proses refund jika terjadi pembatalan pesanan atau pengembalian dana.
                        </small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="bank_name" class="form-label">Nama Bank</label>
                    <input
                        type="text"
                        class="form-input @error('bank_name') is-invalid @enderror"
                        id="bank_name"
                        name="bank_name"
                        value="{{ old('bank_name', $bankAccount?->bank_name) }}"
                        placeholder="Contoh: BCA, Mandiri, BNI, BRI"
                    >
                    @error('bank_name')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="account_number" class="form-label">Nomor Rekening</label>
                    <input
                        type="text"
                        class="form-input @error('account_number') is-invalid @enderror"
                        id="account_number"
                        name="account_number"
                        value="{{ old('account_number', $bankAccount?->account_number) }}"
                        placeholder="Contoh: 1234567890"
                    >
                    @error('account_number')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="account_name" class="form-label">Nama Pemilik Rekening</label>
                    <input
                        type="text"
                        class="form-input @error('account_name') is-invalid @enderror"
                        id="account_name"
                        name="account_name"
                        value="{{ old('account_name', $bankAccount?->account_name) }}"
                        placeholder="Nama sesuai rekening bank"
                    >
                    @error('account_name')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                    <small class="form-hint">Nama harus sesuai dengan yang tertera di rekening bank</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
            </form>
        </div>

        {{-- ========================================== --}}
        {{-- PASSWORD CHANGE FORM --}}
        {{-- ========================================== --}}
        <div class="profile-section">
            <div class="profile-section__header">
                <h3 class="profile-section__title">Keamanan Akun</h3>
            </div>

            <form action="{{ route('customer.password.update') }}" method="POST" class="profile-form">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="current_password" class="form-label">Kata Sandi Saat Ini</label>
                    <input 
                        type="password" 
                        class="form-input @error('current_password') is-invalid @enderror" 
                        id="current_password" 
                        name="current_password" 
                        required
                    >
                    @error('current_password')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Kata Sandi Baru</label>
                    <input 
                        type="password" 
                        class="form-input @error('password') is-invalid @enderror" 
                        id="password" 
                        name="password" 
                        required
                    >
                    @error('password')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                    <small class="form-hint">Minimal 8 karakter</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru</label>
                    <input 
                        type="password" 
                        class="form-input" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>Perbarui Kata Sandi</span>
                </button>
            </form>
        </div>

    </main>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/autocomplete.js') }}"></script>
<script>
// Location Autocomplete (Province -> City -> District)
document.addEventListener('DOMContentLoaded', async function() {
    const provinces = @json($provinces);
    
    let provinceAutocomplete = null;
    let cityAutocomplete = null;
    let districtAutocomplete = null;

    // Initialize Province Autocomplete
    provinceAutocomplete = new Autocomplete({
        inputId: 'province_search',
        hiddenInputId: 'province_id',
        dropdownId: 'province_dropdown',
        data: provinces.map(p => ({ id: p.id, name: p.name })),
        searchFields: ['name'],
        displayTemplate: (province) => {
            return `<div class="font-medium">${province.name}</div>`;
        },
        maxItems: 10,
        onSelect: async (province) => {
            console.log('Province selected:', province);
            
            // Enable city input
            document.getElementById('city_search').disabled = false;
            document.getElementById('city_search').placeholder = 'Ketik untuk mencari kabupaten/kota...';
            
            // Reset city & district
            if (cityAutocomplete) {
                cityAutocomplete.reset();
            }
            if (districtAutocomplete) {
                districtAutocomplete.reset();
            }
            document.getElementById('district_search').disabled = true;
            document.getElementById('district_search').placeholder = 'Pilih kabupaten/kota terlebih dahulu...';
            
            // Load cities
            try {
                const response = await fetch(`/api/cities?province_id=${province.id}`);
                const cities = await response.json();
                
                if (cityAutocomplete) {
                    cityAutocomplete.updateData(cities);
                    cityAutocomplete.reset();
                } else {
                    cityAutocomplete = new Autocomplete({
                        inputId: 'city_search',
                        hiddenInputId: 'city_id',
                        dropdownId: 'city_dropdown',
                        data: cities,
                        searchFields: ['name'],
                        displayTemplate: (city) => {
                            return `<div class="font-medium">${city.name}</div>`;
                        },
                        maxItems: 10,
                        onSelect: async (city) => {
                            console.log('City selected:', city);
                            
                            // Enable district input
                            document.getElementById('district_search').disabled = false;
                            document.getElementById('district_search').placeholder = 'Ketik untuk mencari kecamatan...';
                            
                            // Reset district
                            if (districtAutocomplete) {
                                districtAutocomplete.reset();
                            }
                            
                            // Clear postal code when city changes
                            document.getElementById('postal_code').value = '';
                            
                            // Load districts
                            try {
                                const response = await fetch(`/api/districts?city_id=${city.id}`);
                                const districts = await response.json();
                                
                                if (districtAutocomplete) {
                                    districtAutocomplete.updateData(districts);
                                    districtAutocomplete.reset();
                                } else {
                                    districtAutocomplete = new Autocomplete({
                                        inputId: 'district_search',
                                        hiddenInputId: 'district_id',
                                        dropdownId: 'district_dropdown',
                                        data: districts,
                                        searchFields: ['name'],
                                        displayTemplate: (district) => {
                                            return `<div class="font-medium">${district.name}</div>`;
                                        },
                                        maxItems: 10,
                                        onSelect: (district) => {
                                            console.log('District selected:', district);
                                            // Auto-fill postal code if available
                                            if (district.postal_code) {
                                                document.getElementById('postal_code').value = district.postal_code;
                                            }
                                        }
                                    });
                                }
                            } catch (error) {
                                console.error('Error loading districts:', error);
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading cities:', error);
            }
        }
    });

    // Pre-populate existing data
    @if($customer->province_id && $customer->province)
        // Set province
        document.getElementById('province_search').value = '{{ $customer->province->name }}';
        document.getElementById('province_id').value = '{{ $customer->province_id }}';
        
        // Load and set city
        @if($customer->city_id && $customer->city)
            const existingProvinceId = {{ $customer->province_id }};
            const response = await fetch(`/api/cities?province_id=${existingProvinceId}`);
            const cities = await response.json();
            
            document.getElementById('city_search').disabled = false;
            document.getElementById('city_search').placeholder = 'Ketik untuk mencari kabupaten/kota...';
            document.getElementById('city_search').value = '{{ $customer->city->name }}';
            document.getElementById('city_id').value = '{{ $customer->city_id }}';
            
            cityAutocomplete = new Autocomplete({
                inputId: 'city_search',
                hiddenInputId: 'city_id',
                dropdownId: 'city_dropdown',
                data: cities,
                searchFields: ['name'],
                displayTemplate: (city) => `<div class="font-medium">${city.name}</div>`,
                maxItems: 10,
                onSelect: async (city) => {
                    document.getElementById('district_search').disabled = false;
                    document.getElementById('district_search').placeholder = 'Ketik untuk mencari kecamatan...';
                    
                    if (districtAutocomplete) {
                        districtAutocomplete.reset();
                    }
                    
                    // Clear postal code when city changes
                    document.getElementById('postal_code').value = '';
                    
                    try {
                        const response = await fetch(`/api/districts?city_id=${city.id}`);
                        const districts = await response.json();
                        
                        if (districtAutocomplete) {
                            districtAutocomplete.updateData(districts);
                            districtAutocomplete.reset();
                        } else {
                            districtAutocomplete = new Autocomplete({
                                inputId: 'district_search',
                                hiddenInputId: 'district_id',
                                dropdownId: 'district_dropdown',
                                data: districts,
                                searchFields: ['name'],
                                displayTemplate: (district) => `<div class="font-medium">${district.name}</div>`,
                                maxItems: 10,
                                onSelect: (district) => {
                                    console.log('District selected:', district);
                                    // Auto-fill postal code if available
                                    if (district.postal_code) {
                                        document.getElementById('postal_code').value = district.postal_code;
                                    }
                                }
                            });
                        }
                    } catch (error) {
                        console.error('Error loading districts:', error);
                    }
                }
            });
            
            // Load and set district
            @if($customer->district_id && $customer->district)
                const existingCityId = {{ $customer->city_id }};
                const districtResponse = await fetch(`/api/districts?city_id=${existingCityId}`);
                const districts = await districtResponse.json();
                
                document.getElementById('district_search').disabled = false;
                document.getElementById('district_search').placeholder = 'Ketik untuk mencari kecamatan...';
                document.getElementById('district_search').value = '{{ $customer->district->name }}';
                document.getElementById('district_id').value = '{{ $customer->district_id }}';
                
                districtAutocomplete = new Autocomplete({
                    inputId: 'district_search',
                    hiddenInputId: 'district_id',
                    dropdownId: 'district_dropdown',
                    data: districts,
                    searchFields: ['name'],
                    displayTemplate: (district) => `<div class="font-medium">${district.name}</div>`,
                    maxItems: 10,
                    onSelect: (district) => {
                        console.log('District selected:', district);
                        // Auto-fill postal code if available
                        if (district.postal_code) {
                            document.getElementById('postal_code').value = district.postal_code;
                        }
                    }
                });
            @endif
        @endif
    @endif
});
</script>
@endpush
