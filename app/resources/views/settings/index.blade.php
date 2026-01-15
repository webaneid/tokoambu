<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">Pengaturan</h2>
    </x-slot>

    <div class="py-12" x-data="{ activeTab: 'info' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tab Navigation -->
            <div class="bg-white rounded-t-lg shadow overflow-x-auto">
                <div class="flex border-b border-gray-200">
                    <button @click="activeTab = 'info'"
                            :class="activeTab === 'info' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-colors">
                        Informasi Toko
                    </button>
                    <button @click="activeTab = 'rajaongkir'"
                            :class="activeTab === 'rajaongkir' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-colors">
                        Raja Ongkir
                    </button>
                    <button @click="activeTab = 'whatsapp'"
                            :class="activeTab === 'whatsapp' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-colors">
                        Pesan WhatsApp
                    </button>
                    <button @click="activeTab = 'preorder'"
                            :class="activeTab === 'preorder' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-colors">
                        Preorder
                    </button>
                    <button @click="activeTab = 'ai'"
                            :class="activeTab === 'ai' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-colors">
                        AI Studio
                    </button>
                    <button @click="activeTab = 'bank'"
                            :class="activeTab === 'bank' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-colors">
                        Rekening
                    </button>
                    <button @click="activeTab = 'admin'"
                            :class="activeTab === 'admin' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-colors">
                        Admin
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="bg-white rounded-b-lg shadow p-6">
                <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Tab 1: Informasi Toko -->
                    <div x-show="activeTab === 'info'" x-cloak class="space-y-6">
                        <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Informasi Toko</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Toko</label>
                                <input type="text" name="store_name" value="{{ old('store_name', $settings['store_name'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                @error('store_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                                <input type="text" name="store_phone" value="{{ old('store_phone', $settings['store_phone'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                @error('store_phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="store_email" value="{{ old('store_email', $settings['store_email'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                @error('store_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">No. WhatsApp</label>
                                <input type="text" name="store_whatsapp" value="{{ old('store_whatsapp', $settings['store_whatsapp'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="628xxxxxxxxxx">
                                <p class="text-xs text-gray-500 mt-1">Format: 628xxxxxxxxxx (tanpa tanda +)</p>
                                @error('store_whatsapp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                                <input type="text" name="store_website" value="{{ old('store_website', $settings['store_website'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="https://">
                                @error('store_website') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kota</label>
                                <input type="text" name="store_city" value="{{ old('store_city', $settings['store_city'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                @error('store_city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Toko</label>
                            <textarea name="store_address" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3">{{ old('store_address', $settings['store_address'] ?? '') }}</textarea>
                            @error('store_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description (Shop)</label>
                            <textarea name="storefront_meta_description" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="3">{{ old('storefront_meta_description', $settings['storefront_meta_description'] ?? '') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Dipakai untuk deskripsi meta halaman /shop.</p>
                            @error('storefront_meta_description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Logo & Favicon -->
                        <div class="border-t pt-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-4">Logo & Favicon</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Logo -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Logo Toko</label>
                                    <div class="flex flex-col items-start gap-4">
                                        <div id="logoPreview" class="w-48 h-48 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden">
                                            @if($logoMedia)
                                                <img src="{{ $logoMedia->url }}" alt="Logo" class="w-full h-full object-contain">
                                            @else
                                                <span class="text-sm text-gray-400 text-center px-4">Belum ada logo</span>
                                            @endif
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" id="btnSelectLogo" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover text-sm font-medium">
                                                {{ $logoMedia ? 'Ubah Logo' : 'Pilih Logo' }}
                                            </button>
                                            @if($logoMedia)
                                                <button type="button" id="btnRemoveLogo" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm font-medium">
                                                    Hapus
                                                </button>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500">• Format: PNG, JPG (max 2MB)<br>• Direkomendasikan: 512x512px</p>
                                    </div>
                                    <input type="hidden" name="logo_media_id" id="logoMediaId" value="{{ old('logo_media_id', $logoMedia->id ?? '') }}">
                                    @error('logo_media_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Favicon -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Favicon</label>
                                    <div class="flex flex-col items-start gap-4">
                                        <div id="faviconPreview" class="w-48 h-48 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden">
                                            @if($faviconMedia)
                                                <img src="{{ $faviconMedia->url }}" alt="Favicon" class="w-full h-full object-contain">
                                            @else
                                                <span class="text-sm text-gray-400 text-center px-4">Belum ada favicon</span>
                                            @endif
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" id="btnSelectFavicon" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover text-sm font-medium">
                                                {{ $faviconMedia ? 'Ubah Favicon' : 'Pilih Favicon' }}
                                            </button>
                                            @if($faviconMedia)
                                                <button type="button" id="btnRemoveFavicon" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm font-medium">
                                                    Hapus
                                                </button>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500">• Format: PNG, ICO (max 1MB)<br>• Direkomendasikan: 32x32px atau 64x64px</p>
                                    </div>
                                    <input type="hidden" name="favicon_media_id" id="faviconMediaId" value="{{ old('favicon_media_id', $faviconMedia->id ?? '') }}">
                                    @error('favicon_media_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Prefix Invoice</label>
                                <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                @error('invoice_prefix') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Footer Invoice</label>
                                <input type="text" name="invoice_footer" value="{{ old('invoice_footer', $settings['invoice_footer'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                @error('invoice_footer') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Threshold Slow Moving (hari)</label>
                                <input type="number" min="1" name="dead_stock_slow_days" value="{{ old('dead_stock_slow_days', $settings['dead_stock_slow_days'] ?? 60) }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                @error('dead_stock_slow_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Threshold Dead Stock (hari)</label>
                                <input type="number" min="1" name="dead_stock_dead_days" value="{{ old('dead_stock_dead_days', $settings['dead_stock_dead_days'] ?? 120) }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                @error('dead_stock_dead_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Minimal Margin (%)</label>
                                <input type="number" min="0" max="100" step="0.1" name="min_margin_percent" value="{{ old('min_margin_percent', $settings['min_margin_percent'] ?? 20) }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <p class="text-xs text-gray-500 mt-1">Dipakai sebagai acuan menghitung harga jual default dan warning jika margin di bawah batas ini.</p>
                                @error('min_margin_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t">
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover">Simpan</button>
                        </div>
                    </div>

                    <!-- Tab 2: Raja Ongkir -->
                    <div x-show="activeTab === 'rajaongkir'" x-cloak class="space-y-6">
                        <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Pengaturan RajaOngkir</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">RajaOngkir API Key</label>
                                <input type="text" name="rajaongkir_key" value="{{ old('rajaongkir_key', $settings['rajaongkir_key'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                @error('rajaongkir_key') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">RajaOngkir Mode</label>
                                <select name="rajaongkir_mode" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="">Pilih mode</option>
                                    @foreach (['starter','basic','pro'] as $mode)
                                        <option value="{{ $mode }}" @selected(old('rajaongkir_mode', $settings['rajaongkir_mode'] ?? '') === $mode)>{{ ucfirst($mode) }}</option>
                                    @endforeach
                                </select>
                                @error('rajaongkir_mode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kurir Aktif</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach(($couriers ?? []) as $code => $name)
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="active_couriers[]" value="{{ $code }}"
                                            @checked(in_array($code, old('active_couriers', $settings['active_couriers'] ?? []), true))
                                        >
                                        <span>{{ $name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Pilih kurir yang ditampilkan di halaman order.</p>
                            @error('active_couriers') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            @error('active_couriers.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="border rounded-lg p-4 space-y-4 bg-gray-50">
                            <h4 class="text-md font-semibold text-gray-900">Alamat Asal Pengiriman</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Provinsi Asal</label>
                                    <input type="hidden" name="origin_province_id" id="origin_province_id" value="{{ old('origin_province_id', $settings['origin_province_id'] ?? '') }}">
                                    <input type="text" name="origin_province_name" id="origin_province_search" value="{{ old('origin_province_name', $settings['origin_province_name'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Cari provinsi" autocomplete="off">
                                    <div id="origin_province_suggestions" class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto mt-1"></div>
                                    @error('origin_province_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    @error('origin_province_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Kota/Kabupaten Asal</label>
                                    <input type="hidden" name="origin_city_id" id="origin_city_id" value="{{ old('origin_city_id', $settings['origin_city_id'] ?? '') }}">
                                    <input type="text" name="origin_city_name" id="origin_city_search" value="{{ old('origin_city_name', $settings['origin_city_name'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Cari kota" autocomplete="off">
                                    <div id="origin_city_suggestions" class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto mt-1"></div>
                                    @error('origin_city_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    @error('origin_city_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Kecamatan Asal</label>
                                    <input type="hidden" name="origin_district_id" id="origin_district_id" value="{{ old('origin_district_id', $settings['origin_district_id'] ?? '') }}">
                                    <input type="text" name="origin_district_name" id="origin_district_search" value="{{ old('origin_district_name', $settings['origin_district_name'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Cari kecamatan" autocomplete="off">
                                    <div id="origin_district_suggestions" class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto mt-1"></div>
                                    @error('origin_district_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    @error('origin_district_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Kode Pos Asal</label>
                                    <input type="text" name="origin_postal_code" id="origin_postal_code" value="{{ old('origin_postal_code', $settings['origin_postal_code'] ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Kode pos">
                                    @error('origin_postal_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">Dipakai sebagai alamat asal saat hitung ongkir RajaOngkir.</p>
                        </div>

                        <div class="flex justify-end pt-4 border-t">
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover">Simpan</button>
                        </div>
                    </div>

                    <!-- Tab 3: Pesan WhatsApp -->
                    <div x-show="activeTab === 'whatsapp'" x-cloak class="space-y-6">
                        <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Template Pesan WhatsApp</h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pesan Order Diterima</label>
                            <textarea name="wa_order_message" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="7">{{ old('wa_order_message', $settings['wa_order_message'] ?? "Yth. Ibu/Bapak {customer_name}\n\nPesanan Anda nomor: {order_number} yaitu:\n{items}\ntelah kami buat/terima, total pembayaran: {total_amount}\nsilahkan melakukan pembayaran melalui:\n{invoice_url}\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}") }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">
                                Gunakan token: {customer_name}, {order_number}, {items}, {total_amount}, {invoice_url}, {store_name}, {store_phone}, {store_website}.
                            </p>
                            @error('wa_order_message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pembayaran DP Diterima</label>
                            <textarea name="wa_dp_received_message" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="6">{{ old('wa_dp_received_message', $settings['wa_dp_received_message'] ?? "Yth. Ibu/Bapak {customer_name}\n\nPembayaran DP untuk pesanan {order_number} sebesar {dp_amount} sudah kami terima.\nSisa pembayaran: {remaining_amount}\nTotal tagihan: {total_amount}\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}") }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">
                                Token wajib: {customer_name}, {order_number}, {dp_amount}, {remaining_amount}, {total_amount}, {store_name}, {store_phone}, {store_website}.
                            </p>
                            @error('wa_dp_received_message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pembayaran Lunas</label>
                            <textarea name="wa_paid_message" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="5">{{ old('wa_paid_message', $settings['wa_paid_message'] ?? "Yth. Ibu/Bapak {customer_name}\n\nPembayaran untuk pesanan {order_number} sudah kami terima dengan total {total_amount}.\n\nTerimakasih\n{store_name}\n{store_phone}\n{store_website}") }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">
                                Token wajib: {customer_name}, {order_number}, {total_amount}, {store_name}, {store_phone}, {store_website}.
                            </p>
                            @error('wa_paid_message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pesanan Sudah Dikemas</label>
                            <textarea name="wa_packed_message" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="4">{{ old('wa_packed_message', $settings['wa_packed_message'] ?? "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} sudah kami kemas dan siap dikirim.\n\nTerimakasih\n{store_name}") }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">
                                Token wajib: {customer_name}, {order_number}, {store_name}.
                            </p>
                            @error('wa_packed_message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pesanan Sudah Dikirim</label>
                            <textarea name="wa_shipped_message" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="5">{{ old('wa_shipped_message', $settings['wa_shipped_message'] ?? "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} sudah kami kirim melalui {courier}.\nNo. Resi: {tracking_number}\n\nTerimakasih\n{store_name}") }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">
                                Token wajib: {customer_name}, {order_number}, {courier}, {tracking_number}, {store_name}.
                            </p>
                            @error('wa_shipped_message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pengiriman Telah Sampai</label>
                            <textarea name="wa_delivered_message" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="7">{{ old('wa_delivered_message', $settings['wa_delivered_message'] ?? "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah sampai di tujuan.\nDiterima oleh: {received_by}\nTanggal & Jam: {delivered_at}\n\nTerimakasih telah berbelanja di {store_name}.\n\nSalam,\n{store_name}\n{store_phone}\n{store_website}") }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">
                                Token wajib: {customer_name}, {order_number}, {store_name}.
                            </p>
                            @error('wa_delivered_message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pesanan Dibatalkan</label>
                            <textarea name="wa_cancelled_message" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="4">{{ old('wa_cancelled_message', $settings['wa_cancelled_message'] ?? "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah dibatalkan.\nJika ada pertanyaan, silakan hubungi kami.\n\nTerimakasih\n{store_name}\n{store_phone}") }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">
                                Token wajib: {customer_name}, {order_number}, {store_name}.
                            </p>
                            @error('wa_cancelled_message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pesanan Dibatalkan - Refund Pending</label>
                            <textarea name="wa_cancelled_refund_pending_message" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="4">{{ old('wa_cancelled_refund_pending_message', $settings['wa_cancelled_refund_pending_message'] ?? "Yth. Ibu/Bapak {customer_name}\n\nPesanan {order_number} telah dibatalkan dan sedang diproses refund sebesar {refund_amount}.\nMohon tunggu konfirmasi lebih lanjut dari kami.\n\nTerimakasih\n{store_name}\n{store_phone}") }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">
                                Token wajib: {customer_name}, {order_number}, {refund_amount}, {store_name}.
                            </p>
                            @error('wa_cancelled_refund_pending_message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Refund Diproses</label>
                            <textarea name="wa_refunded_message" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="4">{{ old('wa_refunded_message', $settings['wa_refunded_message'] ?? "Yth. Ibu/Bapak {customer_name}\n\nRefund untuk pesanan {order_number} sebesar {refund_amount} telah diproses.\nSilakan cek rekening Anda.\n\nTerimakasih\n{store_name}\n{store_phone}") }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">
                                Token wajib: {customer_name}, {order_number}, {refund_amount}, {store_name}.
                            </p>
                            @error('wa_refunded_message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end pt-4 border-t">
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover">Simpan</button>
                        </div>
                    </div>

                    <!-- Tab 4: Preorder -->
                    <div x-show="activeTab === 'preorder'" x-cloak class="space-y-6">
                        <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Pengaturan Preorder</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                                    <input type="checkbox" name="preorder_dp_required" value="1" @checked(old('preorder_dp_required', $settings['preorder_dp_required'] ?? true)) class="rounded">
                                    DP Wajib
                                </label>
                                <p class="text-xs text-gray-500">Jika diaktifkan, customer wajib bayar DP untuk preorder</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Persentase DP (%)</label>
                                <input type="number" name="preorder_dp_percentage" value="{{ old('preorder_dp_percentage', $settings['preorder_dp_percentage'] ?? 30) }}" min="1" max="100" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                @error('preorder_dp_percentage') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Batas Waktu DP (hari)</label>
                                <input type="number" name="preorder_dp_deadline_days" value="{{ old('preorder_dp_deadline_days', $settings['preorder_dp_deadline_days'] ?? 7) }}" min="1" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                @error('preorder_dp_deadline_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Batas Waktu Pelunasan (hari)</label>
                                <input type="number" name="preorder_final_deadline_days" value="{{ old('preorder_final_deadline_days', $settings['preorder_final_deadline_days'] ?? 7) }}" min="1" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                @error('preorder_final_deadline_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <h4 class="text-md font-semibold text-gray-800 mt-6 pt-6 border-t">Template WhatsApp Preorder</h4>
                        <p class="text-xs text-gray-500">Token yang tersedia: {customer_name}, {order_number}, {items}, {total_qty}, {total_amount}, {dp_amount}, {remaining_amount}, {deadline}, {invoice_url}, {store_name}, {store_phone}, {store_website}</p>
                        <p class="text-xs text-gray-500 mt-1">Catatan: {items} akan menampilkan list produk dengan format "- NamaProduk (X pcs) @ Rp Y"</p>

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reminder DP</label>
                                <textarea name="preorder_wa_dp_reminder" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="8">{{ old('preorder_wa_dp_reminder', $settings['preorder_wa_dp_reminder'] ?? "Yth. Ibu/Bapak *{customer_name}*\n\nPesanan Preorder Anda nomor: *{order_number}* sebanyak *{total_qty} pcs* yaitu:\n{items}\n\ntelah kami terima dengan rincian:\n*Total Pembayaran:* Rp {total_amount}\n*DP yang harus dibayar:* Rp {dp_amount}\n*Batas Waktu:* {deadline}\n\nSilakan melakukan pembayaran melalui URL dibawah ini sebelum batas waktu yang ditentukan:\n{invoice_url}\n\nTerima kasih\n{store_name}\n{store_phone}\n{store_website}") }}</textarea>
                                @error('preorder_wa_dp_reminder') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi DP Diterima</label>
                                <textarea name="preorder_wa_dp_confirmed" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="8">{{ old('preorder_wa_dp_confirmed', $settings['preorder_wa_dp_confirmed'] ?? "Yth. Ibu/Bapak *{customer_name}*\n\nPembayaran DP Anda telah kami terima untuk pesanan:\n\n*Order:* {order_number}\n*Produk ({total_qty} pcs):*\n{items}\n\n*DP Dibayar:* Rp {dp_amount}\n*Sisa Pembayaran:* Rp {remaining_amount}\n\nProduk sedang kami pesan ke supplier. Kami akan menginformasikan ketika produk sudah siap.\n\nTerima kasih!\n{store_name}\n{store_phone}\n{store_website}") }}</textarea>
                                @error('preorder_wa_dp_confirmed') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Produk Siap</label>
                                <textarea name="preorder_wa_product_ready" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="8">{{ old('preorder_wa_product_ready', $settings['preorder_wa_product_ready'] ?? "Yth. Ibu/Bapak *{customer_name}*\n\nKabar gembira! Produk preorder Anda sudah siap:\n\n*Order:* {order_number}\n*Produk ({total_qty} pcs):*\n{items}\n\n*Total Pembayaran:* Rp {total_amount}\n*Sudah Dibayar (DP):* Rp {dp_amount}\n*Sisa Pembayaran:* Rp {remaining_amount}\n*Batas Waktu Pelunasan:* {deadline}\n\nMohon segera lakukan pelunasan melalui:\n{invoice_url}\n\nTerima kasih!\n{store_name}\n{store_phone}\n{store_website}") }}</textarea>
                                @error('preorder_wa_product_ready') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reminder Pelunasan</label>
                                <textarea name="preorder_wa_final_reminder" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="8">{{ old('preorder_wa_final_reminder', $settings['preorder_wa_final_reminder'] ?? "Yth. Ibu/Bapak *{customer_name}*\n\nIni adalah pengingat pelunasan pesanan preorder Anda:\n\n*Order:* {order_number}\n*Produk ({total_qty} pcs):*\n{items}\n\n*Total Pembayaran:* Rp {total_amount}\n*Sudah Dibayar (DP):* Rp {dp_amount}\n*Sisa Pembayaran:* Rp {remaining_amount}\n*Batas Waktu:* {deadline}\n\nProduk sudah siap dan menunggu pelunasan dari Anda. Silakan bayar melalui:\n{invoice_url}\n\nTerima kasih!\n{store_name}\n{store_phone}\n{store_website}") }}</textarea>
                                @error('preorder_wa_final_reminder') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pesanan Dibatalkan</label>
                                <textarea name="preorder_wa_cancelled" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="6">{{ old('preorder_wa_cancelled', $settings['preorder_wa_cancelled'] ?? "Yth. Ibu/Bapak *{customer_name}*\n\nMohon maaf, pesanan preorder Anda telah dibatalkan karena melewati batas waktu pembayaran:\n\n*Order:* {order_number}\n*Produk ({total_qty} pcs):*\n{items}\n\nJika Anda masih berminat, silakan buat pesanan baru.\n\nTerima kasih atas pengertiannya.\n{store_name}\n{store_phone}\n{store_website}") }}</textarea>
                                @error('preorder_wa_cancelled') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t">
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover">Simpan</button>
                        </div>
                    </div>

                    <!-- Tab 5: AI Studio -->
                    <div x-show="activeTab === 'ai'" x-cloak class="space-y-6">
                        <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">AI Studio (Gemini)</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gemini API Key</label>
                                @php
                                    $geminiFieldValue = old('gemini_api_key', $geminiKeyExists ? $geminiMaskPlaceholder : '');
                                @endphp
                                <input type="password" name="gemini_api_key" value="{{ $geminiFieldValue }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Masukkan API Key Gemini">
                                <p class="text-xs text-gray-500 mt-1">Kunci disimpan terenkripsi. Kosongkan jika tidak ingin mengubah.</p>
                                @if($geminiKeyExists)
                                    <p class="text-xs text-gray-400">API key sudah tersimpan.@if($geminiIntegration && $geminiIntegration->updated_at) Terakhir diperbarui {{ $geminiIntegration->updated_at->diffForHumans() }}.@endif</p>
                                @else
                                    <p class="text-xs text-gray-400">Belum ada API key yang tersimpan.</p>
                                @endif
                                @error('gemini_api_key') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Model Gemini</label>
                                @php
                                    $modelValue = old('gemini_model', optional($geminiIntegration)->model ?? 'gemini-2.5-flash-image-preview');
                                @endphp
                                <input type="text" name="gemini_model" value="{{ $modelValue }}" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="cth: gemini-2.5-flash-image-preview">
                                <p class="text-xs text-gray-500 mt-1">Gunakan model yang mendukung generateContent untuk gambar.</p>
                                @error('gemini_model') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Warna Latar Default</label>
                                @php
                                    $bgValue = old('gemini_default_bg_color', optional($geminiIntegration)->default_bg_color ?? '#FFFFFF');
                                @endphp
                                <div class="flex items-center space-x-3">
                                    <input type="color" name="gemini_default_bg_color" value="{{ $bgValue }}" class="w-16 h-10 border rounded-md" oninput="this.nextElementSibling.value = this.value">
                                    <input type="text" value="{{ $bgValue }}" oninput="this.previousElementSibling.value = this.value" class="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="#FFFFFF">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Dipakai saat solid background aktif.</p>
                                @error('gemini_default_bg_color') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-4">
                                @php
                                    $solidOld = old('gemini_use_solid_background');
                                    $solidChecked = !is_null($solidOld) ? (bool)$solidOld : (optional($geminiIntegration)->use_solid_background ?? true);
                                    $enabledOld = old('gemini_is_enabled');
                                    $enabledChecked = !is_null($enabledOld) ? (bool)$enabledOld : (optional($geminiIntegration)->is_enabled ?? true);
                                @endphp
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Solid Background</p>
                                        <p class="text-xs text-gray-500">Aktifkan untuk warna latar blok penuh.</p>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="hidden" name="gemini_use_solid_background" value="0">
                                        <input type="checkbox" name="gemini_use_solid_background" value="1" class="w-5 h-5 text-primary focus:ring-primary border-gray-300 rounded" @checked($solidChecked)>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Aktifkan Gemini Studio</p>
                                        <p class="text-xs text-gray-500">Nonaktifkan jika butuh jeda pemakaian.</p>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="hidden" name="gemini_is_enabled" value="0">
                                        <input type="checkbox" name="gemini_is_enabled" value="1" class="w-5 h-5 text-primary focus:ring-primary border-gray-300 rounded" @checked($enabledChecked)>
                                    </div>
                                </div>
                                @error('gemini_use_solid_background') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                                @error('gemini_is_enabled') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t">
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover">Simpan</button>
                        </div>
                    </div>
                </form>

                <!-- Tab 6: Rekening (separate form) -->
                <div x-show="activeTab === 'bank'" x-cloak class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Rekening Bank</h3>

                    <form action="{{ route('settings.bank_accounts.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bank</label>
                            <input type="text" name="bank_name" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                            @error('bank_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Bank</label>
                            <input type="text" name="bank_code" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            @error('bank_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. Rekening</label>
                            <input type="text" name="account_number" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                            @error('account_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Rekening</label>
                            <input type="text" name="account_name" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                            @error('account_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-4 flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover">+ Tambah Rekening</button>
                        </div>
                    </form>

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
                            <tbody class="divide-y divide-gray-200">
                                @forelse($bankAccounts as $account)
                                    <tr>
                                        <td class="px-4 py-2 text-sm">{{ $account->bank_name }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $account->bank_code ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $account->account_number }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $account->account_name }}</td>
                                        <td class="px-4 py-2 text-right text-sm">
                                            <form action="{{ route('settings.bank_accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('Hapus rekening ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">Belum ada rekening.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 7: Admin -->
                <div x-show="activeTab === 'admin'" x-cloak class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Manajemen User Admin</h3>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Informasi:</p>
                            <ul class="space-y-1 text-blue-700 list-disc list-inside">
                                <li>Super Admin dapat membuat user admin baru dengan berbagai role</li>
                                <li>User admin tidak bisa mendaftar sendiri, harus ditambahkan oleh Super Admin</li>
                                <li>Super Admin dapat membuat Super Admin lainnya</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" id="btnAddUser" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">
                            + Tambah User
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="users-list" class="bg-white divide-y divide-gray-200">
                                @forelse($users as $user)
                                    <tr data-id="{{ $user->id }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ $user->roles->pluck('name')->join(', ') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button type="button" class="btn-edit-user text-blue-600 hover:text-blue-900 mr-3" data-id="{{ $user->id }}">
                                                Edit
                                            </button>
                                            @if($user->id !== auth()->id())
                                            <button type="button" class="btn-delete-user text-red-600 hover:text-red-900" data-id="{{ $user->id }}">
                                                Hapus
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                            Belum ada user
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('settings.partials.user-modal')

    <script>
        const originProvinceInput = document.getElementById('origin_province_search');
        const originProvinceIdInput = document.getElementById('origin_province_id');
        const originProvinceSuggestions = document.getElementById('origin_province_suggestions');
        const originCityInput = document.getElementById('origin_city_search');
        const originCityIdInput = document.getElementById('origin_city_id');
        const originCitySuggestions = document.getElementById('origin_city_suggestions');
        const originDistrictInput = document.getElementById('origin_district_search');
        const originDistrictIdInput = document.getElementById('origin_district_id');
        const originDistrictSuggestions = document.getElementById('origin_district_suggestions');
        const originPostalInput = document.getElementById('origin_postal_code');

        async function fetchOriginSuggestions(url) {
            const res = await fetch(url);
            if (!res.ok) return [];
            return res.json();
        }

        function renderOriginSuggestions(el, items, onSelect) {
            if (!items.length) {
                el.classList.add('hidden');
                return;
            }
            el.innerHTML = items.map(item => `
                <button type="button" data-id="${item.code}" data-name="${item.name}" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100">
                    ${item.name}
                </button>
            `).join('');
            el.classList.remove('hidden');
            el.onclick = (e) => {
                const btn = e.target.closest('button[data-id]');
                if (!btn) return;
                onSelect(btn.dataset.id, btn.dataset.name, items.find(i => i.code === btn.dataset.id));
                el.classList.add('hidden');
            };
        }

        originProvinceInput?.addEventListener('input', async (e) => {
            const q = encodeURIComponent(e.target.value);
            const items = await fetchOriginSuggestions(`/api/provinces/search?q=${q}`);
            renderOriginSuggestions(originProvinceSuggestions, items, (id, name) => {
                originProvinceIdInput.value = id;
                originProvinceInput.value = name;
                originCityInput.value = '';
                originCityIdInput.value = '';
                originDistrictInput.value = '';
                originDistrictIdInput.value = '';
            });
        });

        originCityInput?.addEventListener('input', async (e) => {
            if (!originProvinceIdInput.value) return;
            const q = encodeURIComponent(e.target.value);
            const items = await fetchOriginSuggestions(`/api/cities/search?q=${q}&province_code=${originProvinceIdInput.value}`);
            renderOriginSuggestions(originCitySuggestions, items, (id, name) => {
                originCityIdInput.value = id;
                originCityInput.value = name;
                originDistrictInput.value = '';
                originDistrictIdInput.value = '';
            });
        });

        originDistrictInput?.addEventListener('input', async (e) => {
            if (!originCityIdInput.value) return;
            const q = encodeURIComponent(e.target.value);
            const items = await fetchOriginSuggestions(`/api/districts/search?q=${q}&city_code=${originCityIdInput.value}`);
            renderOriginSuggestions(originDistrictSuggestions, items, (id, name, item) => {
                originDistrictIdInput.value = id;
                originDistrictInput.value = name;
                if (item && item.postal_code && !originPostalInput.value) {
                    originPostalInput.value = item.postal_code;
                }
            });
        });

        document.addEventListener('click', (e) => {
            [originProvinceSuggestions, originCitySuggestions, originDistrictSuggestions].forEach(el => {
                if (el && !el.contains(e.target) && !el.previousElementSibling.contains(e.target)) {
                    el.classList.add('hidden');
                }
            });
        });

        // Logo & Favicon Media Picker
        const btnSelectLogo = document.getElementById('btnSelectLogo');
        const btnSelectFavicon = document.getElementById('btnSelectFavicon');
        const btnRemoveLogo = document.getElementById('btnRemoveLogo');
        const btnRemoveFavicon = document.getElementById('btnRemoveFavicon');
        const logoPreview = document.getElementById('logoPreview');
        const faviconPreview = document.getElementById('faviconPreview');
        const logoMediaIdInput = document.getElementById('logoMediaId');
        const faviconMediaIdInput = document.getElementById('faviconMediaId');

        btnSelectLogo?.addEventListener('click', () => {
            openMediaPicker({
                type: 'banner_image',
                title: 'Pilih Logo Toko',
                listUrl: '{{ route('media.banner_image.list') }}',
                uploadUrl: '{{ route('media.store') }}',
                context: {},
                onSelect: (media) => {
                    logoMediaIdInput.value = media.id;
                    logoPreview.innerHTML = `<img src="${media.url}" alt="Logo" class="w-full h-full object-contain">`;

                    // Update button text
                    btnSelectLogo.textContent = 'Ubah Logo';

                    // Show remove button if not exists
                    if (!btnRemoveLogo) {
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.id = 'btnRemoveLogo';
                        removeBtn.className = 'px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm font-medium';
                        removeBtn.textContent = 'Hapus';
                        removeBtn.addEventListener('click', removeLogo);
                        btnSelectLogo.parentElement.appendChild(removeBtn);
                    }
                },
                aiEnabled: false,
                csrfToken: '{{ csrf_token() }}'
            });
        });

        btnSelectFavicon?.addEventListener('click', () => {
            openMediaPicker({
                type: 'banner_image',
                title: 'Pilih Favicon',
                listUrl: '{{ route('media.banner_image.list') }}',
                uploadUrl: '{{ route('media.store') }}',
                context: {},
                onSelect: (media) => {
                    faviconMediaIdInput.value = media.id;
                    faviconPreview.innerHTML = `<img src="${media.url}" alt="Favicon" class="w-full h-full object-contain">`;

                    // Update button text
                    btnSelectFavicon.textContent = 'Ubah Favicon';

                    // Show remove button if not exists
                    if (!btnRemoveFavicon) {
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.id = 'btnRemoveFavicon';
                        removeBtn.className = 'px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm font-medium';
                        removeBtn.textContent = 'Hapus';
                        removeBtn.addEventListener('click', removeFavicon);
                        btnSelectFavicon.parentElement.appendChild(removeBtn);
                    }
                },
                aiEnabled: false,
                csrfToken: '{{ csrf_token() }}'
            });
        });

        function removeLogo() {
            logoMediaIdInput.value = '';
            logoPreview.innerHTML = '<span class="text-sm text-gray-400 text-center px-4">Belum ada logo</span>';
            btnSelectLogo.textContent = 'Pilih Logo';
            btnRemoveLogo?.remove();
        }

        function removeFavicon() {
            faviconMediaIdInput.value = '';
            faviconPreview.innerHTML = '<span class="text-sm text-gray-400 text-center px-4">Belum ada favicon</span>';
            btnSelectFavicon.textContent = 'Pilih Favicon';
            btnRemoveFavicon?.remove();
        }

        btnRemoveLogo?.addEventListener('click', removeLogo);
        btnRemoveFavicon?.addEventListener('click', removeFavicon);
    </script>

    <!-- Media Picker Script -->
    <script src="{{ asset('js/media-picker.js') }}"></script>
</x-app-layout>
