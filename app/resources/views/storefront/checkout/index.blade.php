@extends('storefront.layouts.app-mobile')

@section('title', 'Checkout - Toko Ambu')

@section('content')
<div class="storefront-app">
    <main class="storefront-main">
        {{-- Header --}}
        <div class="checkout-header">
            <a href="{{ route('cart.index') }}" class="back-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="checkout-title">Checkout</h1>
            <div style="width: 24px;"></div>
        </div>

        {{-- Content --}}
        <div class="checkout-content">
            <form action="{{ route('checkout.store') }}" method="POST" class="checkout-form">
                @csrf

                {{-- Alamat Pengiriman --}}
                <div class="checkout-section">
                    <h2 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        Alamat Pengiriman
                    </h2>

                    <div class="form-group">
                        <label for="shipping_address" class="form-label">Alamat Lengkap</label>
                        <textarea
                            class="form-textarea @error('shipping_address') form-input-error @enderror"
                            id="shipping_address"
                            name="shipping_address"
                            rows="3"
                            placeholder="Jl. Contoh No. 123, Blok A"
                            required
                        >{{ old('shipping_address', $customer?->address ?? '') }}</textarea>
                        @error('shipping_address')
                            <span class="form-error">{{ $message }}</span>
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
                                class="form-input @error('shipping_province_id') form-input-error @enderror"
                            >
                            <input type="hidden" id="shipping_province_id" name="shipping_province_id" value="{{ old('shipping_province_id', $customer?->province_id ?? '') }}">
                            <div id="province_dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        </div>
                        @error('shipping_province_id')
                            <span class="form-error">{{ $message }}</span>
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
                                class="form-input @error('shipping_city_id') form-input-error @enderror"
                                disabled
                            >
                            <input type="hidden" id="shipping_city_id" name="shipping_city_id" value="{{ old('shipping_city_id', $customer?->city_id ?? '') }}">
                            <div id="city_dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        </div>
                        @error('shipping_city_id')
                            <span class="form-error">{{ $message }}</span>
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
                                class="form-input @error('shipping_district_id') form-input-error @enderror"
                                disabled
                            >
                            <input type="hidden" id="shipping_district_id" name="shipping_district_id" value="{{ old('shipping_district_id', $customer?->district_id ?? '') }}">
                            <div id="district_dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        </div>
                        @error('shipping_district_id')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="shipping_postal_code" class="form-label">Kode Pos (Opsional)</label>
                        <input
                            type="text"
                            class="form-input @error('shipping_postal_code') form-input-error @enderror"
                            id="shipping_postal_code"
                            name="shipping_postal_code"
                            value="{{ old('shipping_postal_code', $customer?->postal_code ?? '') }}"
                            placeholder="40123"
                            maxlength="5"
                        >
                        @error('shipping_postal_code')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Shipping Cost Calculator --}}
                    <div class="form-group" style="margin-top: 1rem;">
                        <label class="form-label">Pilih Kurir & Hitung Ongkir</label>

                        <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <select id="shipping_courier" name="shipping_courier" class="form-input" style="flex: 1;">
                                <option value="">Pilih kurir</option>
                                @foreach($couriers as $code => $name)
                                    @if(empty($activeCouriers) || in_array($code, $activeCouriers, true))
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="button" id="btn_calc_shipping" class="btn btn--secondary" style="padding: 0.75rem 1.5rem; white-space: nowrap;">
                                Cek Ongkir
                            </button>
                        </div>

                        <select id="shipping_service" name="shipping_service" class="form-input" disabled style="margin-bottom: 0.5rem;">
                            <option value="">Pilih layanan</option>
                        </select>

                        <input type="hidden" name="shipping_cost" id="shipping_cost" value="0">
                        <input type="hidden" name="shipping_etd" id="shipping_etd">

                        <p id="shipping_calc_message" class="text-xs" style="color: #6B7280; margin-top: 0.25rem;"></p>

                        @if(empty($origin['district_id']))
                            <p class="text-xs" style="color: #EF4444; margin-top: 0.5rem;">
                                ⚠️ Alamat asal belum diatur. Hubungi admin untuk mengatur alamat toko.
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="checkout-section">
                    <h2 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        Catatan (Opsional)
                    </h2>

                    <div class="form-group">
                        <textarea
                            class="form-textarea"
                            name="notes"
                            rows="3"
                            placeholder="Tambahkan catatan atau permintaan khusus..."
                        >{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Order Summary --}}
                <div class="checkout-summary">
                    <h2 class="summary-title">Ringkasan Pesanan</h2>

                    <div class="summary-items">
                        @foreach ($items as $item)
                            @php
                                $isBundle = $item->bundle_id && $item->bundle;
                                $bundleItems = $item->bundle_items ?? [];
                                $bundleName = $item->bundle_name ?? $item->bundle?->promotion?->name ?? $item->product->name;
                                $unitPrice = $item->unit_price ?? $item->price;
                                $originalPrice = $item->original_price ?? null;
                            @endphp
                            <div class="summary-item">
                                <span class="summary-item-name">
                                    {{ $isBundle ? $bundleName : $item->product->name }}
                                    @if($item->variant && ! $isBundle)
                                        <small>({{ $item->variant->display_name }})</small>
                                    @endif
                                    <br>
                                    <small>×{{ $item->quantity }}</small>
                                    @if($isBundle)
                                        <div class="summary-item-bundle">
                                            @foreach($bundleItems as $bundleItem)
                                                @php
                                                    $bundleProduct = $bundleItem['item']->productVariant?->product ?? $bundleItem['item']->product;
                                                    $bundleVariant = $bundleItem['item']->productVariant?->display_name;
                                                @endphp
                                                <div class="summary-item-bundle__item">
                                                    {{ $bundleProduct?->name ?? '-' }}
                                                    @if($bundleVariant)
                                                        <small>({{ $bundleVariant }})</small>
                                                    @endif
                                                    ×{{ $bundleItem['qty'] }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </span>
                                <span class="summary-item-price">
                                    @if ($originalPrice && $originalPrice > $unitPrice)
                                        <span class="summary-item-badge {{ $isBundle ? 'summary-item-badge--bundle' : 'summary-item-badge--flash' }}">
                                            {{ $isBundle ? 'Bundle' : 'Flash Sale' }}
                                        </span>
                                        <span class="summary-item-price-original">Rp {{ number_format($originalPrice * $item->quantity, 0, ',', '.') }}</span>
                                    @endif
                                    Rp {{ number_format($unitPrice * $item->quantity, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-value">Rp <span id="display_subtotal">{{ number_format($subtotal, 0, ',', '.') }}</span></span>
                    </div>

                    @if (!empty($couponPromotion))
                        <div class="summary-row" id="summary_discount_row">
                            <span class="summary-label">Diskon ({{ $couponPromotion?->name ?? 'Kupon' }})</span>
                            <span class="summary-value summary-discount" id="summary_discount_value">-Rp {{ number_format($couponDiscount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="summary-row">
                        <span class="summary-label">Ongkir</span>
                        <span class="summary-value">Rp <span id="display_shipping">0</span></span>
                    </div>

                    <div class="summary-row total-row">
                        <span class="summary-label">Total</span>
                        <span class="summary-value">Rp <span id="display_total">{{ number_format($total, 0, ',', '.') }}</span></span>
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    Buat Pesanan
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
        hiddenInputId: 'shipping_province_id',
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
                        hiddenInputId: 'shipping_city_id',
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
                            document.getElementById('shipping_postal_code').value = '';

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
                                        hiddenInputId: 'shipping_district_id',
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
                                                document.getElementById('shipping_postal_code').value = district.postal_code;
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

    // Pre-populate existing data from customer profile
    @if($customer && $customer->province_id && $customer->province)
        console.log('Customer data found, pre-populating address fields...');
        console.log('Province:', {!! json_encode($customer->province->name ?? 'N/A') !!});

        // Set province
        document.getElementById('province_search').value = '{{ $customer->province->name }}';
        document.getElementById('shipping_province_id').value = '{{ $customer->province_id }}';

        // Load and set city
        @if($customer->city_id && $customer->city)
            const existingProvinceId = {{ $customer->province_id }};
            const response = await fetch(`/api/cities?province_id=${existingProvinceId}`);
            const cities = await response.json();

            document.getElementById('city_search').disabled = false;
            document.getElementById('city_search').placeholder = 'Ketik untuk mencari kabupaten/kota...';
            document.getElementById('city_search').value = '{{ $customer->city->name }}';
            document.getElementById('shipping_city_id').value = '{{ $customer->city_id }}';

            cityAutocomplete = new Autocomplete({
                inputId: 'city_search',
                hiddenInputId: 'shipping_city_id',
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
                    document.getElementById('shipping_postal_code').value = '';

                    try {
                        const response = await fetch(`/api/districts?city_id=${city.id}`);
                        const districts = await response.json();

                        if (districtAutocomplete) {
                            districtAutocomplete.updateData(districts);
                            districtAutocomplete.reset();
                        } else {
                            districtAutocomplete = new Autocomplete({
                                inputId: 'district_search',
                                hiddenInputId: 'shipping_district_id',
                                dropdownId: 'district_dropdown',
                                data: districts,
                                searchFields: ['name'],
                                displayTemplate: (district) => `<div class="font-medium">${district.name}</div>`,
                                maxItems: 10,
                                onSelect: (district) => {
                                    console.log('District selected:', district);
                                    // Auto-fill postal code if available
                                    if (district.postal_code) {
                                        document.getElementById('shipping_postal_code').value = district.postal_code;
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
                document.getElementById('shipping_district_id').value = '{{ $customer->district_id }}';

                districtAutocomplete = new Autocomplete({
                    inputId: 'district_search',
                    hiddenInputId: 'shipping_district_id',
                    dropdownId: 'district_dropdown',
                    data: districts,
                    searchFields: ['name'],
                    displayTemplate: (district) => `<div class="font-medium">${district.name}</div>`,
                    maxItems: 10,
                    onSelect: (district) => {
                        console.log('District selected:', district);
                        // Auto-fill postal code if available
                        if (district.postal_code) {
                            document.getElementById('shipping_postal_code').value = district.postal_code;
                        }
                    }
                });
            @endif
        @endif
    @else
        console.log('No customer address data to pre-populate');
        @if($customer)
            console.log('Customer logged in but no address saved:', {!! json_encode([
                'province_id' => $customer->province_id,
                'city_id' => $customer->city_id,
                'district_id' => $customer->district_id,
            ]) !!});
        @else
            console.log('No customer logged in (guest checkout)');
        @endif
    @endif
});

// ========================================
// Shipping Cost Calculation
// ========================================
const origin_district_id = @json($origin['district_id'] ?? null);
const subtotal = {{ $subtotal }};
const couponDiscount = {{ (float) $couponDiscount }};
const couponType = @json($couponPromotion?->benefits->first()?->benefit_type);

const courierSelect = document.getElementById('shipping_courier');
const serviceSelect = document.getElementById('shipping_service');
const calcButton = document.getElementById('btn_calc_shipping');
const calcMessage = document.getElementById('shipping_calc_message');
const shippingCostInput = document.getElementById('shipping_cost');
const shippingEtdInput = document.getElementById('shipping_etd');

const displaySubtotal = document.getElementById('display_subtotal');
const displayShipping = document.getElementById('display_shipping');
const displayTotal = document.getElementById('display_total');
const discountRow = document.getElementById('summary_discount_row');
const discountValue = document.getElementById('summary_discount_value');

function formatCurrency(value) {
    return value.toLocaleString('id-ID');
}

// Helper function to normalize shipping API response
function normalizeShippingOptions(payload) {
    console.log('normalizeShippingOptions input:', payload);

    if (!payload) {
        console.log('Payload is null/undefined');
        return [];
    }

    // Handle format: {meta: {...}, data: [...]}
    if (payload.data && Array.isArray(payload.data)) {
        console.log('Found payload.data array:', payload.data);
        const options = [];
        payload.data.forEach((item) => {
            console.log('Processing item:', item);
            // Each item has: service, description, cost, etd
            options.push({
                courier: item.courier || item.code || item.name || '-',
                service: item.service || item.name || '-',
                description: item.description || '',
                cost: Number(item.cost || item.value || 0),
                etd: item.etd || '',
            });
        });
        return options;
    }

    // Handle format: {options: [...]}
    if (Array.isArray(payload.options)) {
        console.log('Found payload.options array');
        return payload.options;
    }

    // Handle format: {rajaongkir: {results: [...]}}
    if (payload.rajaongkir && payload.rajaongkir.results && payload.rajaongkir.results.length) {
        console.log('Found rajaongkir.results:', payload.rajaongkir.results);
        const options = [];
        payload.rajaongkir.results.forEach((result) => {
            console.log('Processing result:', result);
            (result.costs || []).forEach((cost) => {
                console.log('Processing cost:', cost);
                let costValue = 0;
                if (cost.cost && cost.cost[0] && cost.cost[0].value) {
                    costValue = cost.cost[0].value;
                } else if (cost.cost) {
                    costValue = cost.cost;
                } else if (cost.value) {
                    costValue = cost.value;
                }

                let etdValue = '';
                if (cost.cost && cost.cost[0] && cost.cost[0].etd) {
                    etdValue = cost.cost[0].etd;
                } else if (cost.etd) {
                    etdValue = cost.etd;
                }

                options.push({
                    courier: result.code || result.name,
                    service: cost.service || cost.name || '-',
                    cost: Number(costValue) || 0,
                    etd: etdValue,
                });
            });
        });
        return options;
    }

    console.log('No matching format found. Payload keys:', Object.keys(payload));
    return [];
}

// Render service options to select dropdown
function renderServiceOptions(options) {
    serviceSelect.innerHTML = '<option value="">Pilih layanan</option>';
    options.forEach((option) => {
        const opt = document.createElement('option');
        const costLabel = `Rp ${option.cost.toLocaleString('id-ID')}`;
        const etdLabel = option.etd ? ` (${option.etd} hari)` : '';
        opt.value = option.service;
        opt.dataset.cost = option.cost ?? 0;
        opt.dataset.etd = option.etd ?? '';
        opt.textContent = `${option.service} - ${costLabel}${etdLabel}`;
        serviceSelect.appendChild(opt);
    });
    serviceSelect.disabled = options.length === 0;
}

// Update total display
function updateTotal() {
    const shipping = parseFloat(shippingCostInput.value) || 0;
    let discount = couponDiscount;

    if (couponType === 'free_shipping' || couponType === 'shipping') {
        discount = shipping;
    }

    const total = Math.max(0, subtotal + shipping - discount);

    displayShipping.textContent = formatCurrency(shipping);
    displayTotal.textContent = formatCurrency(total);

    if (discountRow && discountValue) {
        discountValue.textContent = `-Rp ${formatCurrency(discount)}`;
    }
}

// When service is selected, update shipping cost and total
serviceSelect.addEventListener('change', () => {
    const selected = serviceSelect.selectedOptions[0];
    if (!selected) return;

    const cost = Number(selected.dataset.cost || 0);
    const etd = selected.dataset.etd || '';

    shippingCostInput.value = cost;
    shippingEtdInput.value = etd;

    updateTotal();
});

// Reset service selection when courier changes
courierSelect.addEventListener('change', () => {
    serviceSelect.innerHTML = '<option value="">Pilih layanan</option>';
    serviceSelect.disabled = true;
    shippingCostInput.value = 0;
    shippingEtdInput.value = '';
    calcMessage.textContent = '';
    updateTotal();
});

// Calculate shipping cost button
calcButton.addEventListener('click', async () => {
    const destinationDistrictId = document.getElementById('shipping_district_id').value;
    const courier = courierSelect.value;

    // Validation
    if (!origin_district_id) {
        calcMessage.textContent = '⚠️ Alamat asal belum diatur di Settings.';
        calcMessage.style.color = '#EF4444';
        return;
    }
    if (!destinationDistrictId) {
        calcMessage.textContent = '⚠️ Pilih kecamatan tujuan terlebih dahulu.';
        calcMessage.style.color = '#EF4444';
        return;
    }
    if (!courier) {
        calcMessage.textContent = '⚠️ Pilih kurir terlebih dahulu.';
        calcMessage.style.color = '#EF4444';
        return;
    }

    // Disable button and show loading
    calcButton.disabled = true;
    calcButton.textContent = 'Menghitung...';
    calcMessage.textContent = 'Menghitung ongkir...';
    calcMessage.style.color = '#6B7280';

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        const res = await fetch('/api/shipping/cost', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
            },
            body: JSON.stringify({
                origin_district_id: origin_district_id,
                destination_district_id: destinationDistrictId,
                weight_grams: 1000, // Default 1kg untuk checkout (atau bisa dihitung dari produk)
                courier: courier,
            }),
        });

        const payload = await res.json().catch(() => null);

        // Debug: Log response
        console.log('API Response:', payload);
        console.log('Response status:', res.status);

        if (!res.ok) {
            calcMessage.textContent = (payload && payload.message) ? payload.message : 'Gagal menghitung ongkir.';
            calcMessage.style.color = '#EF4444';
            serviceSelect.innerHTML = '<option value="">Pilih layanan</option>';
            serviceSelect.disabled = true;
            return;
        }

        const options = normalizeShippingOptions(payload);
        console.log('Normalized options:', options);
        renderServiceOptions(options);

        if (options.length) {
            // Auto-select first option
            serviceSelect.value = options[0].service;
            shippingCostInput.value = options[0].cost ?? 0;
            shippingEtdInput.value = options[0].etd ?? '';
            updateTotal();
            calcMessage.textContent = `✓ Ditemukan ${options.length} layanan pengiriman.`;
            calcMessage.style.color = '#10B981';
        } else {
            calcMessage.textContent = '⚠️ Tidak ada layanan pengiriman tersedia.';
            calcMessage.style.color = '#EF4444';
        }

    } catch (error) {
        console.error('Shipping calc error:', error);
        calcMessage.textContent = 'Terjadi kesalahan saat menghitung ongkir.';
        calcMessage.style.color = '#EF4444';
    } finally {
        calcButton.disabled = false;
        calcButton.textContent = 'Cek Ongkir';
    }
});
</script>
@endpush
