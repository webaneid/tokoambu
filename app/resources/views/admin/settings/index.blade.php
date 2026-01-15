<x-app-layout>
    <div class="py-0 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Pengaturan Storefront</h1>
                <p class="mt-1 text-sm text-gray-600">Kelola storefront Anda dengan mudah dan fleksibel.</p>
            </div>

            <!-- Success Alert -->
            @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="text-green-800 hover:text-green-900" onclick="this.parentElement.remove()">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            @endif

            <!-- Error Alert -->
            @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <strong class="font-semibold">Terjadi Kesalahan:</strong>
                        <ul class="mt-2 ml-4 list-disc">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <!-- Tab Navigation -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px overflow-x-auto whitespace-nowrap">
                        <button
                            type="button"
                            id="tab-storefront"
                            class="tab-button active py-4 px-6 text-sm font-medium border-b-2 border-primary text-primary focus:outline-none flex-shrink-0"
                            onclick="switchTab('storefront')"
                        >
                            Store Front
                        </button>
                        <button
                            type="button"
                            id="tab-methods"
                            class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none flex-shrink-0"
                            onclick="switchTab('methods')"
                        >
                            Metode Pembayaran
                        </button>
                        <button
                            type="button"
                            id="tab-ipaymu"
                            class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none hidden flex-shrink-0"
                            onclick="switchTab('ipaymu')"
                        >
                            Pengaturan iPaymu
                        </button>
                        <button
                            type="button"
                            id="tab-footer-nav"
                            class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none flex-shrink-0"
                            onclick="switchTab('footer-nav')"
                        >
                            Footer Nav
                        </button>
                        <button
                            type="button"
                            id="tab-admin"
                            class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none flex-shrink-0"
                            onclick="switchTab('admin')"
                        >
                            Admin
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Tab Content: Store Front -->
            <div id="content-storefront" class="tab-content bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- Info Banner -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-1">Informasi Banner Hero</p>
                                <ul class="space-y-1 text-blue-700">
                                    <li>• Banner ditampilkan di halaman utama toko</li>
                                    <li>• Gambar harus rasio 16:9 untuk hasil terbaik</li>
                                    <li>• Urutan banner sesuai urutan di bawah (drag to reorder)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('admin.settings.update-storefront') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Banners Repeater -->
                        <div id="banners-container" class="space-y-4 mb-6">
                            @php
                                $banners = old('banners', $settings['banners'] ?? []);
                            @endphp

                            @forelse($banners as $index => $banner)
                                <div class="banner-item border border-gray-300 rounded-lg p-4" data-index="{{ $index }}">
                                    <!-- Header -->
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-gray-400 cursor-move drag-handle" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                            </svg>
                                            <span class="font-medium text-gray-900">Banner #<span class="banner-number">{{ $index + 1 }}</span></span>
                                        </div>
                                        <button type="button" class="text-red-600 hover:text-red-800" onclick="removeBanner(this)">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Type Selection -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Banner</label>
                                        <div class="grid grid-cols-2 gap-4">
                                            <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                                <input type="radio" name="banners[{{ $index }}][type]" value="image" {{ ($banner['type'] ?? 'image') === 'image' ? 'checked' : '' }} class="mr-2" onchange="toggleBannerType(this)">
                                                <span class="text-sm">Image + Link</span>
                                            </label>
                                            <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                                <input type="radio" name="banners[{{ $index }}][type]" value="text" {{ ($banner['type'] ?? 'image') === 'text' ? 'checked' : '' }} class="mr-2" onchange="toggleBannerType(this)">
                                                <span class="text-sm">Text Banner</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Image Type Fields -->
                                    <div class="image-fields {{ ($banner['type'] ?? 'image') === 'image' ? '' : 'hidden' }}">
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Gambar (Rasio 16:9)</label>
                                            <div class="flex gap-4">
                                                <div id="banner-preview-{{ $index }}" class="w-32 h-32 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden flex-shrink-0">
                                                    @if(!empty($banner['image_url'] ?? ''))
                                                        <img src="{{ $banner['image_url'] }}" class="w-full h-full object-contain rounded-lg">
                                                    @else
                                                        <span class="text-xs text-gray-400 text-center px-2">Belum ada gambar</span>
                                                    @endif
                                                </div>
                                                <div class="flex-1">
                                                    <input type="hidden" name="banners[{{ $index }}][image_url]" value="{{ $banner['image_url'] ?? '' }}" id="banner-image-{{ $index }}">
                                                    <button type="button" onclick="openBannerImagePicker({{ $index }})" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-hover transition">
                                                        Pilih Gambar
                                                    </button>
                                                    <p class="mt-2 text-xs text-gray-500">Klik tombol untuk memilih gambar dari gallery</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Link URL</label>
                                            <div class="relative">
                                                <input
                                                    type="text"
                                                    id="banner-link-search-{{ $index }}"
                                                    autocomplete="off"
                                                    value="{{ $banner['link'] ?? '' }}"
                                                    placeholder="/shop/category/promo"
                                                    class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                                                >
                                                <input type="hidden" id="banner-link-{{ $index }}" name="banners[{{ $index }}][link]" value="{{ $banner['link'] ?? '' }}">
                                                <div id="banner-link-dropdown-{{ $index }}" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Text Type Fields -->
                                    <div class="text-fields {{ ($banner['type'] ?? 'image') === 'text' ? '' : 'hidden' }}">
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                                            <input type="text" name="banners[{{ $index }}][title]" value="{{ $banner['title'] ?? '' }}" placeholder="Promo Spesial Hari Ini!" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Singkat</label>
                                            <textarea name="banners[{{ $index }}][description]" rows="2" placeholder="Dapatkan diskon hingga 50% untuk produk pilihan" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">{{ $banner['description'] ?? '' }}</textarea>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Link URL</label>
                                                <div class="relative">
                                                    <input
                                                        type="text"
                                                        id="banner-text-link-search-{{ $index }}"
                                                        autocomplete="off"
                                                        value="{{ $banner['link'] ?? '' }}"
                                                        placeholder="/shop/category/promo"
                                                        class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                                                    >
                                                    <div id="banner-text-link-dropdown-{{ $index }}" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Text Link</label>
                                                <input type="text" name="banners[{{ $index }}][link_text]" value="{{ $banner['link_text'] ?? '' }}" placeholder="Lihat Promo" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Active Status -->
                                    <div class="mt-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="banners[{{ $index }}][is_active]" value="1" {{ ($banner['is_active'] ?? true) ? 'checked' : '' }} class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary">
                                            <span class="ml-2 text-sm text-gray-700">Aktif</span>
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <!-- Empty state will be replaced by template -->
                            @endforelse
                        </div>

                        <!-- Add Banner Button -->
                        <button type="button" onclick="addBanner()" class="mb-6 h-10 px-6 border-2 border-dashed border-gray-300 text-gray-600 text-sm font-medium rounded-lg hover:border-primary hover:text-primary transition flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Banner
                        </button>

                        <!-- Color Settings Section -->
                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Warna Storefront</h3>

                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="text-sm text-yellow-800">
                                        <p class="font-semibold mb-1">Perhatian</p>
                                        <p>Perubahan warna akan mempengaruhi tampilan seluruh storefront. Pastikan warna yang dipilih kontras dan mudah dibaca.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <!-- Primary Color -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Warna Utama (Primary)</label>
                                    <div class="flex gap-2">
                                        <input type="color" name="color_primary" value="{{ old('color_primary', $settings['color_primary'] ?? '#F17B0D') }}" class="h-10 w-16 border border-gray-300 rounded-lg cursor-pointer">
                                        <input type="text" name="color_primary_hex" value="{{ old('color_primary', $settings['color_primary'] ?? '#F17B0D') }}" placeholder="#F17B0D" class="flex-1 h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Tombol CTA, link aktif, highlight</p>
                                </div>

                                <!-- Primary Hover -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Warna Utama Hover</label>
                                    <div class="flex gap-2">
                                        <input type="color" name="color_primary_hover" value="{{ old('color_primary_hover', $settings['color_primary_hover'] ?? '#DD5700') }}" class="h-10 w-16 border border-gray-300 rounded-lg cursor-pointer">
                                        <input type="text" name="color_primary_hover_hex" value="{{ old('color_primary_hover', $settings['color_primary_hover'] ?? '#DD5700') }}" placeholder="#DD5700" class="flex-1 h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Warna saat hover pada elemen primary</p>
                                </div>

                                <!-- Secondary Color -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Warna Sekunder (Secondary)</label>
                                    <div class="flex gap-2">
                                        <input type="color" name="color_secondary" value="{{ old('color_secondary', $settings['color_secondary'] ?? '#0D36AA') }}" class="h-10 w-16 border border-gray-300 rounded-lg cursor-pointer">
                                        <input type="text" name="color_secondary_hex" value="{{ old('color_secondary', $settings['color_secondary'] ?? '#0D36AA') }}" placeholder="#0D36AA" class="flex-1 h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Badge info, elemen dekoratif sekunder</p>
                                </div>

                                <!-- Alternative/Accent Color -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Warna Aksen (Alternative)</label>
                                    <div class="flex gap-2">
                                        <input type="color" name="color_alternative" value="{{ old('color_alternative', $settings['color_alternative'] ?? '#D00086') }}" class="h-10 w-16 border border-gray-300 rounded-lg cursor-pointer">
                                        <input type="text" name="color_alternative_hex" value="{{ old('color_alternative', $settings['color_alternative'] ?? '#D00086') }}" placeholder="#D00086" class="flex-1 h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Badge promo, highlight khusus</p>
                                </div>

                                <!-- Dark Color -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Warna Gelap (Dark)</label>
                                    <div class="flex gap-2">
                                        <input type="color" name="color_dark" value="{{ old('color_dark', $settings['color_dark'] ?? '#1F2937') }}" class="h-10 w-16 border border-gray-300 rounded-lg cursor-pointer">
                                        <input type="text" name="color_dark_hex" value="{{ old('color_dark', $settings['color_dark'] ?? '#1F2937') }}" placeholder="#1F2937" class="flex-1 h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Header, footer, teks heading</p>
                                </div>

                                <!-- Light/Background Color -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Warna Background</label>
                                    <div class="flex gap-2">
                                        <input type="color" name="color_light_gray" value="{{ old('color_light_gray', $settings['color_light_gray'] ?? '#F9FAFB') }}" class="h-10 w-16 border border-gray-300 rounded-lg cursor-pointer">
                                        <input type="text" name="color_light_gray_hex" value="{{ old('color_light_gray', $settings['color_light_gray'] ?? '#F9FAFB') }}" placeholder="#F9FAFB" class="flex-1 h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Background section, card background</p>
                                </div>

                            </div>

                            <!-- Reset Button -->
                            <div class="mt-4">
                                <button type="button" onclick="resetColors()" class="text-sm text-gray-600 hover:text-primary">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Reset ke Default
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center gap-3 mt-6">
                            <button type="submit" class="h-10 px-6 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-hover transition flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Storefront
                            </button>
                            <a href="{{ route('dashboard') }}" class="h-10 px-6 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition flex items-center">
                                Batal
                            </a>
                        </div>
                    </form>

                </div>
            </div>

            <!-- Tab Content: Payment Methods -->
            <div id="content-methods" class="tab-content bg-white overflow-hidden shadow-sm sm:rounded-lg hidden">
                <div class="p-6 text-gray-900">

                    <!-- Info Banner -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-1">Informasi Metode Pembayaran</p>
                                <ul class="space-y-1 text-blue-700">
                                    <li>• Aktifkan metode pembayaran yang ingin Anda gunakan</li>
                                    <li>• Pelanggan hanya dapat memilih metode yang diaktifkan</li>
                                    <li>• iPaymu memerlukan konfigurasi tambahan setelah diaktifkan</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('admin.settings.update-payment-methods') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Payment Method Checkboxes -->
                        <div class="space-y-4 mb-8">

                            <!-- COD -->
                            <label class="flex items-start p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="payment_method_cod"
                                    id="payment_method_cod"
                                    value="1"
                                    {{ old('payment_method_cod', $settings['payment_method_cod'] ?? false) ? 'checked' : '' }}
                                    class="mt-1 h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary"
                                >
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span class="font-medium text-gray-900">COD (Cash on Delivery)</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Pelanggan membayar tunai saat barang diterima</p>
                                </div>
                            </label>

                            <!-- Bank Transfer -->
                            <label class="flex items-start p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="payment_method_bank_transfer"
                                    id="payment_method_bank_transfer"
                                    value="1"
                                    {{ old('payment_method_bank_transfer', $settings['payment_method_bank_transfer'] ?? false) ? 'checked' : '' }}
                                    class="mt-1 h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary"
                                >
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                        </svg>
                                        <span class="font-medium text-gray-900">Bank Transfer</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Transfer manual ke rekening bank toko</p>
                                </div>
                            </label>

                            <!-- E-wallet -->
                            <label class="flex items-start p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="payment_method_ewallet"
                                    id="payment_method_ewallet"
                                    value="1"
                                    {{ old('payment_method_ewallet', $settings['payment_method_ewallet'] ?? false) ? 'checked' : '' }}
                                    class="mt-1 h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary"
                                >
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span class="font-medium text-gray-900">E-wallet</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Pembayaran via e-wallet (OVO, GoPay, Dana, dll)</p>
                                </div>
                            </label>

                            <!-- iPaymu -->
                            <label class="flex items-start p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="payment_method_ipaymu"
                                    id="payment_method_ipaymu"
                                    value="1"
                                    {{ old('payment_method_ipaymu', $settings['payment_method_ipaymu'] ?? false) ? 'checked' : '' }}
                                    class="mt-1 h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary"
                                    onchange="toggleIpaymuTab(this.checked)"
                                >
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        <span class="font-medium text-gray-900">iPaymu</span>
                                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Payment Gateway</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Integrasi payment gateway iPaymu - memerlukan konfigurasi tambahan</p>
                                </div>
                            </label>

                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center gap-3">
                            <button
                                type="submit"
                                class="h-10 px-6 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-hover transition flex items-center"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Metode Pembayaran
                            </button>

                            <a
                                href="{{ route('dashboard') }}"
                                class="h-10 px-6 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition flex items-center"
                            >
                                Batal
                            </a>
                        </div>
                    </form>

                </div>
            </div>

            <!-- Tab Content: iPaymu Configuration -->
            <div id="content-ipaymu" class="tab-content bg-white overflow-hidden shadow-sm sm:rounded-lg hidden">
                <div class="p-6 text-gray-900">

                    <!-- Info Banner -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-1">Informasi Keamanan</p>
                                <ul class="space-y-1 text-blue-700">
                                    <li>• Kredensial akan dienkripsi sebelum disimpan ke database</li>
                                    <li>• Dapatkan VA dan API Key dari <a href="https://my.ipaymu.com" target="_blank" class="underline hover:text-blue-900">Dashboard iPaymu</a></li>
                                    <li>• Gunakan mode Sandbox untuk testing, Production untuk transaksi live</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('admin.settings.update-payment') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Mode Selection -->
                        <div class="mb-6">
                            <label for="ipaymu_mode" class="block text-sm font-medium text-gray-700 mb-2">
                                Mode iPaymu <span class="text-red-600">*</span>
                            </label>
                            <select
                                id="ipaymu_mode"
                                name="ipaymu_mode"
                                class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('ipaymu_mode') border-red-500 @enderror"
                                required
                            >
                                <option value="">-- Pilih Mode --</option>
                                <option value="sandbox" {{ old('ipaymu_mode', $settings['ipaymu_mode']) === 'sandbox' ? 'selected' : '' }}>
                                    Sandbox (Pengembangan/Testing)
                                </option>
                                <option value="production" {{ old('ipaymu_mode', $settings['ipaymu_mode']) === 'production' ? 'selected' : '' }}>
                                    Production (Live Transactions)
                                </option>
                            </select>
                            @error('ipaymu_mode')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Gunakan Sandbox untuk testing, Production untuk transaksi live</p>
                        </div>

                        <!-- Virtual Account (VA) -->
                        <div class="mb-6">
                            <label for="ipaymu_va" class="block text-sm font-medium text-gray-700 mb-2">
                                Virtual Account (VA) <span class="text-red-600">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    type="password"
                                    id="ipaymu_va"
                                    name="ipaymu_va"
                                    value="{{ old('ipaymu_va', $settings['ipaymu_va'] ? '••••••••••••••' : '') }}"
                                    placeholder="Contoh: 0000005210626455"
                                    class="w-full h-10 px-4 py-2 pr-12 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('ipaymu_va') border-red-500 @enderror"
                                    required
                                />
                                <button
                                    type="button"
                                    id="toggleVA"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>
                            </div>
                            @error('ipaymu_va')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Masukkan VA baru untuk mengubahnya. Field akan terisi dengan karakter tersembunyi jika sudah tersimpan</p>
                        </div>

                        <!-- API Key -->
                        <div class="mb-6">
                            <label for="ipaymu_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                                API Key <span class="text-red-600">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    type="password"
                                    id="ipaymu_api_key"
                                    name="ipaymu_api_key"
                                    value="{{ old('ipaymu_api_key', $settings['ipaymu_api_key'] ? '••••••••••••••••••••••••••••••' : '') }}"
                                    placeholder="Contoh: SANDBOXB9C9CCE9-A0DF-4DF4-868B-2B681C7A24B3"
                                    class="w-full h-10 px-4 py-2 pr-12 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('ipaymu_api_key') border-red-500 @enderror"
                                    required
                                />
                                <button
                                    type="button"
                                    id="toggleAPIKey"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>
                            </div>
                            @error('ipaymu_api_key')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Masukkan API Key baru untuk mengubahnya. Field akan terisi dengan karakter tersembunyi jika sudah tersimpan</p>
                        </div>

                        <!-- Security Notice -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <svg class="w-5 h-5 text-gray-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div class="text-sm text-gray-700">
                                    <p class="font-semibold mb-2">Keamanan Kredensial</p>
                                    <ul class="space-y-1">
                                        <li>✓ Kredensial dienkripsi dengan Laravel encryption sebelum disimpan</li>
                                        <li>✓ Hanya Super Admin yang dapat mengakses halaman ini</li>
                                        <li>✓ Jangan share kredensial Production ke orang lain</li>
                                        <li>✓ Rotate API Key secara berkala untuk keamanan maksimal</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center gap-3">
                            <button
                                type="submit"
                                class="h-10 px-6 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-hover transition flex items-center"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Pengaturan iPaymu
                            </button>

                            <a
                                href="{{ route('dashboard') }}"
                                class="h-10 px-6 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition flex items-center"
                            >
                                Batal
                            </a>
                        </div>
                    </form>

                </div>
            </div>

            <!-- Tab Content: Footer Nav -->
            <div id="content-footer-nav" class="tab-content bg-white overflow-hidden shadow-sm sm:rounded-lg hidden">
                <div class="p-6 text-gray-900">

                    <!-- Info Banner -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-1">Menu Footer Storefront</p>
                                <ul class="space-y-1 text-blue-700">
                                    <li>• Kelola menu yang tampil di footer halaman storefront</li>
                                    <li>• Drag & drop untuk mengubah urutan menu</li>
                                    <li>• Menu dapat link ke halaman atau custom URL</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Add New Menu Button -->
                    <div class="mb-6">
                        <button
                            type="button"
                            id="btnAddFooterMenu"
                            class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-hover transition flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Menu
                        </button>
                    </div>

                    <!-- Footer Menu List (Sortable) -->
                    <div id="footer-menu-list" class="space-y-3">
                        @forelse($footerMenuItems as $item)
                            <div class="footer-menu-item border border-gray-300 rounded-lg p-4 bg-white" data-id="{{ $item->id }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3 flex-1">
                                        <!-- Drag Handle -->
                                        <svg class="w-5 h-5 text-gray-400 cursor-move drag-handle" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                        </svg>

                                        <!-- Menu Info -->
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">{{ $item->label }}</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                @if($item->type === 'page' && $item->page)
                                                    Page: {{ $item->page->title }} ({{ $item->page->slug }})
                                                @else
                                                    Custom URL: {{ $item->custom_url }}
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Active Status -->
                                        <div>
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="toggle-active w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary" data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }}>
                                                <span class="ml-2 text-sm text-gray-600">Aktif</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex gap-2 ml-4">
                                        <button type="button" class="btn-edit-menu text-blue-600 hover:text-blue-800 p-2" data-id="{{ $item->id }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="btn-delete-menu text-red-600 hover:text-red-800 p-2" data-id="{{ $item->id }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div id="empty-state" class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                                <p>Belum ada menu footer. Klik "Tambah Menu" untuk membuat menu baru.</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>

            <!-- Tab Content: Admin -->
            <div id="content-admin" class="tab-content bg-white overflow-hidden shadow-sm sm:rounded-lg hidden">
                <div class="p-6 text-gray-900">

                    <!-- Info Banner -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-1">Manajemen User Admin</p>
                                <ul class="space-y-1 text-blue-700">
                                    <li>• Super Admin dapat membuat user admin baru dengan berbagai role</li>
                                    <li>• User admin tidak bisa mendaftar sendiri, harus ditambahkan oleh Super Admin</li>
                                    <li>• Super Admin dapat membuat Super Admin lainnya</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Add New User Button -->
                    <div class="mb-6">
                        <button
                            type="button"
                            id="btnAddUser"
                            class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-hover transition flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah User
                        </button>
                    </div>

                    <!-- Users List -->
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

    <!-- Modals for User Management -->
    @include('admin.settings.partials.user-modal')

    <!-- JavaScript for tab switching and password toggle -->
    <script src="{{ asset('js/autocomplete.js') }}"></script>
    <script>
    let bannerIndex = {{ count($settings['banners'] ?? []) }};
    const storefrontUrls = @json($storefrontUrls ?? []);
    const bannerLinkAutocompletes = [];

    // Color Reset Function
    function resetColors() {
        const defaults = {
            'color_primary': '#F17B0D',
            'color_primary_hover': '#DD5700',
            'color_secondary': '#0D36AA',
            'color_alternative': '#D00086',
            'color_dark': '#1F2937',
            'color_light_gray': '#F9FAFB'
        };

        Object.keys(defaults).forEach(key => {
            document.querySelector(`input[name="${key}"]`).value = defaults[key];
            document.querySelector(`input[name="${key}_hex"]`).value = defaults[key];
        });
    }

    // Sync color picker with text input
    document.addEventListener('DOMContentLoaded', function() {
        const colorInputs = ['color_primary', 'color_primary_hover', 'color_secondary', 'color_alternative', 'color_dark', 'color_light_gray'];

        colorInputs.forEach(name => {
            const colorPicker = document.querySelector(`input[name="${name}"]`);
            const textInput = document.querySelector(`input[name="${name}_hex"]`);

            if (colorPicker && textInput) {
                colorPicker.addEventListener('input', function() {
                    textInput.value = this.value;
                });

                textInput.addEventListener('input', function() {
                    if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                        colorPicker.value = this.value;
                    }
                });
            }
        });

        initAllBannerLinkAutocompletes();
    });

    // Banner Management Functions
    function initBannerLinkAutocomplete(bannerItem) {
        if (!bannerItem) {
            return;
        }

        const index = bannerItem.dataset.index;
        const hiddenInput = document.getElementById(`banner-link-${index}`);
        const imageInput = document.getElementById(`banner-link-search-${index}`);
        const imageDropdown = document.getElementById(`banner-link-dropdown-${index}`);
        const textInput = document.getElementById(`banner-text-link-search-${index}`);
        const textDropdown = document.getElementById(`banner-text-link-dropdown-${index}`);

        if (!hiddenInput || (!imageInput && !textInput)) {
            return;
        }

        const syncInputs = (value) => {
            const cleaned = (value || '').trim();
            hiddenInput.value = cleaned;
            if (imageInput) {
                imageInput.value = cleaned;
            }
            if (textInput) {
                textInput.value = cleaned;
            }
        };

        syncInputs(hiddenInput.value);

        const buildTemplate = (item) => {
            const label = item.label || item.url || item.name || item.id;
            const url = item.url || item.name || item.id;
            let html = `<div class="font-medium">${label}</div>`;
            if (url) {
                html += `<div class="text-xs text-gray-500">${url}</div>`;
            }
            return html;
        };

        if (imageInput && imageDropdown) {
            bannerLinkAutocompletes.push(new Autocomplete({
                inputId: imageInput.id,
                hiddenInputId: hiddenInput.id,
                dropdownId: imageDropdown.id,
                data: storefrontUrls,
                searchFields: ['name', 'label', 'url'],
                displayTemplate: buildTemplate,
                maxItems: 10,
                onSelect: (item) => {
                    syncInputs(item.url || item.name || item.id);
                },
            }));

            imageInput.addEventListener('blur', () => {
                syncInputs(imageInput.value);
            });
        }

        if (textInput && textDropdown) {
            bannerLinkAutocompletes.push(new Autocomplete({
                inputId: textInput.id,
                hiddenInputId: hiddenInput.id,
                dropdownId: textDropdown.id,
                data: storefrontUrls,
                searchFields: ['name', 'label', 'url'],
                displayTemplate: buildTemplate,
                maxItems: 10,
                onSelect: (item) => {
                    syncInputs(item.url || item.name || item.id);
                },
            }));

            textInput.addEventListener('blur', () => {
                syncInputs(textInput.value);
            });
        }
    }

    function initAllBannerLinkAutocompletes() {
        document.querySelectorAll('.banner-item').forEach((item) => {
            initBannerLinkAutocomplete(item);
        });
    }

    function addBanner() {
        const container = document.getElementById('banners-container');
        const template = `
            <div class="banner-item border border-gray-300 rounded-lg p-4" data-index="${bannerIndex}">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400 cursor-move drag-handle" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                        </svg>
                        <span class="font-medium text-gray-900">Banner #<span class="banner-number">${bannerIndex + 1}</span></span>
                    </div>
                    <button type="button" class="text-red-600 hover:text-red-800" onclick="removeBanner(this)">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Banner</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="banners[${bannerIndex}][type]" value="image" checked class="mr-2" onchange="toggleBannerType(this)">
                            <span class="text-sm">Image + Link</span>
                        </label>
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="banners[${bannerIndex}][type]" value="text" class="mr-2" onchange="toggleBannerType(this)">
                            <span class="text-sm">Text Banner</span>
                        </label>
                    </div>
                </div>

                <div class="image-fields">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gambar (Rasio 16:9)</label>
                        <div class="flex gap-4">
                            <div id="banner-preview-${bannerIndex}" class="w-32 h-32 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden flex-shrink-0">
                                <span class="text-xs text-gray-400 text-center px-2">Belum ada gambar</span>
                            </div>
                            <div class="flex-1">
                                <input type="hidden" name="banners[${bannerIndex}][image_url]" id="banner-image-${bannerIndex}">
                                <button type="button" onclick="openBannerImagePicker(${bannerIndex})" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-hover transition">
                                    Pilih Gambar
                                </button>
                                <p class="mt-2 text-xs text-gray-500">Klik tombol untuk memilih gambar dari gallery</p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Link URL</label>
                        <div class="relative">
                            <input
                                type="text"
                                id="banner-link-search-${bannerIndex}"
                                autocomplete="off"
                                placeholder="/shop/category/promo"
                                class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                            >
                            <input type="hidden" id="banner-link-${bannerIndex}" name="banners[${bannerIndex}][link]" value="">
                            <div id="banner-link-dropdown-${bannerIndex}" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>
                </div>

                <div class="text-fields hidden">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                        <input type="text" name="banners[${bannerIndex}][title]" placeholder="Promo Spesial Hari Ini!" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Singkat</label>
                        <textarea name="banners[${bannerIndex}][description]" rows="2" placeholder="Dapatkan diskon hingga 50% untuk produk pilihan" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Link URL</label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="banner-text-link-search-${bannerIndex}"
                                    autocomplete="off"
                                    placeholder="/shop/category/promo"
                                    class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                                >
                                <div id="banner-text-link-dropdown-${bannerIndex}" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Text Link</label>
                            <input type="text" name="banners[${bannerIndex}][link_text]" placeholder="Lihat Promo" class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="banners[${bannerIndex}][is_active]" value="1" checked class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <span class="ml-2 text-sm text-gray-700">Aktif</span>
                    </label>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', template);
        bannerIndex++;
        updateBannerNumbers();
        initBannerLinkAutocomplete(container.lastElementChild);
    }

    function removeBanner(button) {
        if (confirm('Hapus banner ini?')) {
            button.closest('.banner-item').remove();
            updateBannerNumbers();
        }
    }

    function toggleBannerType(radio) {
        const item = radio.closest('.banner-item');
        const imageFields = item.querySelector('.image-fields');
        const textFields = item.querySelector('.text-fields');

        if (radio.value === 'image') {
            imageFields.classList.remove('hidden');
            textFields.classList.add('hidden');
        } else {
            imageFields.classList.add('hidden');
            textFields.classList.remove('hidden');
        }
    }

    function updateBannerNumbers() {
        document.querySelectorAll('.banner-item').forEach((item, index) => {
            item.querySelector('.banner-number').textContent = index + 1;
        });
    }

    // Tab Switching Function
    function switchTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(function(content) {
            content.classList.add('hidden');
        });

        // Deactivate all tab buttons
        document.querySelectorAll('.tab-button').forEach(function(btn) {
            btn.classList.remove('active', 'border-primary', 'text-primary');
            btn.classList.add('border-transparent', 'text-gray-500');
        });

        // Show selected tab content
        document.getElementById('content-' + tabName).classList.remove('hidden');

        // Activate selected tab button
        const activeTab = document.getElementById('tab-' + tabName);
        activeTab.classList.add('active', 'border-primary', 'text-primary');
        activeTab.classList.remove('border-transparent', 'text-gray-500');
    }

    // Toggle iPaymu Tab Visibility
    function toggleIpaymuTab(isChecked) {
        const ipaymuTab = document.getElementById('tab-ipaymu');

        if (isChecked) {
            // Show iPaymu tab and switch to it
            ipaymuTab.classList.remove('hidden');
            switchTab('ipaymu');
        } else {
            // Hide iPaymu tab and switch back to methods
            ipaymuTab.classList.add('hidden');
            switchTab('methods');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Check if iPaymu is enabled on page load
        const ipaymuCheckbox = document.getElementById('payment_method_ipaymu');
        if (ipaymuCheckbox && ipaymuCheckbox.checked) {
            document.getElementById('tab-ipaymu').classList.remove('hidden');
        }

        // Toggle VA visibility
        const toggleVA = document.getElementById('toggleVA');
        const inputVA = document.getElementById('ipaymu_va');

        toggleVA.addEventListener('click', function() {
            const type = inputVA.getAttribute('type') === 'password' ? 'text' : 'password';
            inputVA.setAttribute('type', type);

            // Change icon
            if (type === 'text') {
                this.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                `;
            } else {
                this.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                `;
            }
        });

        // Toggle API Key visibility
        const toggleAPIKey = document.getElementById('toggleAPIKey');
        const inputAPIKey = document.getElementById('ipaymu_api_key');

        toggleAPIKey.addEventListener('click', function() {
            const type = inputAPIKey.getAttribute('type') === 'password' ? 'text' : 'password';
            inputAPIKey.setAttribute('type', type);

            // Change icon
            if (type === 'text') {
                this.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                `;
            } else {
                this.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                `;
            }
        });

        // Banner Image Picker
        window.openBannerImagePicker = function(index) {
            openMediaPicker({
                type: 'banner_image',
                title: 'Pilih Gambar Banner',
                listUrl: '{{ route('media.banner_image.list') }}',
                uploadUrl: '{{ route('media.store') }}',
                context: {},
                onSelect: (item) => {
                    document.getElementById('banner-image-' + index).value = item.url;
                    const preview = document.getElementById('banner-preview-' + index);
                    preview.innerHTML = `<img src="${item.url}" class="w-full h-full object-contain rounded-lg">`;
                }
            });
        };
    });
    </script>

    <script src="{{ asset('js/media-picker.js') }}"></script>

    <!-- SortableJS for drag & drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <!-- Footer Menu Manager -->
    <script>
        // Pass pages data to JavaScript
        window.footerMenuPages = @json($pages ?? []);
    </script>
    <script src="{{ asset('js/footer-menu-manager.js') }}"></script>
</x-app-layout>
