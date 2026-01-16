<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-primary">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="font-semibold text-2xl text-gray-900">Tambah Produk Baru</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="productWizard()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Progress Indicator -->
            <div class="bg-white rounded-lg shadow-sm mb-6 p-6 md:p-8">
                <div class="flex items-center justify-between">
                    <template x-for="(step, index) in steps" :key="index">
                        <div class="flex-1 flex items-center" :class="{'opacity-50': index > currentStep}">
                            <!-- Step Circle -->
                            <div class="flex items-center justify-center w-12 h-12 md:w-14 md:h-14 rounded-full transition-all duration-300 flex-shrink-0"
                                 :class="index <= currentStep ? 'bg-primary text-white shadow-lg' : 'bg-gray-200 text-gray-500'">
                                <template x-if="index < currentStep">
                                    <svg class="w-6 h-6 md:w-7 md:h-7" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </template>
                                <template x-if="index >= currentStep">
                                    <span x-text="index + 1" class="font-bold text-lg md:text-xl"></span>
                                </template>
                            </div>

                            <!-- Step Label (Desktop) -->
                            <div class="ml-4 hidden md:block flex-shrink-0">
                                <p class="text-sm font-semibold leading-tight" :class="index <= currentStep ? 'text-gray-900' : 'text-gray-400'" x-text="step.title"></p>
                                <p class="text-xs text-gray-500 mt-0.5" x-text="step.subtitle"></p>
                            </div>

                            <!-- Connector Line -->
                            <div class="flex-1 h-0.5 mx-3 md:mx-6" x-show="index < steps.length - 1" :class="index < currentStep ? 'bg-primary' : 'bg-gray-200'"></div>
                        </div>
                    </template>
                </div>

                <!-- Mobile Step Title -->
                <div class="md:hidden text-center mt-6 pt-4 border-t border-gray-200">
                    <p class="text-base font-semibold text-gray-900" x-text="steps[currentStep].title"></p>
                    <p class="text-sm text-gray-500 mt-1" x-text="steps[currentStep].subtitle"></p>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('products.store') }}" method="POST" @submit.prevent="handleSubmit" id="productForm">
                @csrf

                <!-- Hidden inputs to preserve data across steps -->
                <input type="hidden" name="sku" id="sku_hidden">
                <input type="hidden" name="name" id="name_hidden">
                <input type="hidden" name="category_id" id="category_id_hidden">
                <input type="hidden" name="supplier_id" id="supplier_id_hidden">
                <input type="hidden" name="cost_price" id="cost_price_hidden">
                <input type="hidden" name="selling_price" id="selling_price_hidden">
                <input type="hidden" name="weight_grams" id="weight_grams_hidden">

                <!-- Step 1: Basic Info -->
                <div x-show="currentStep === 0" x-cloak class="bg-white rounded-lg shadow-sm p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700 mb-2">SKU <span class="text-red-500">*</span></label>
                            <input type="text" id="sku" x-model="formData.sku"
                                   class="w-full h-11 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent @error('sku') border-red-500 @enderror"
                                   placeholder="Contoh: PRD-001" required>
                            @error('sku')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Produk <span class="text-red-500">*</span></label>
                            <input type="text" id="name" x-model="formData.name"
                                   class="w-full h-11 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent @error('name') border-red-500 @enderror"
                                   placeholder="Contoh: Kaos Polos Premium" required>
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <div class="flex-1 relative">
                                    <input type="hidden" id="category_id" x-model="formData.category_id">
                                    <input type="text" id="category_search"
                                           class="w-full h-11 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent @error('category_id') border-red-500 @enderror"
                                           placeholder="Cari atau pilih kategori" autocomplete="off">
                                    <div id="category_suggestions" class="absolute z-10 top-full left-0 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto"></div>
                                </div>
                                <button type="button" id="btn_add_category" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition whitespace-nowrap">
                                    + Baru
                                </button>
                            </div>
                            @error('category_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">Supplier <span class="text-gray-400 text-xs">(Opsional)</span></label>
                            <div class="flex gap-2">
                                <div class="flex-1 relative">
                                    <input type="hidden" id="supplier_id" x-model="formData.supplier_id">
                                    <input type="text" id="supplier_search"
                                           class="w-full h-11 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                           placeholder="Cari atau pilih supplier" autocomplete="off">
                                    <div id="supplier_suggestions" class="absolute z-10 top-full left-0 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto"></div>
                                </div>
                                <button type="button" id="btn_add_supplier" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition whitespace-nowrap">
                                    + Baru
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Pricing & Inventory -->
                <div x-show="currentStep === 1" x-cloak class="bg-white rounded-lg shadow-sm p-6 space-y-6" x-init="console.log('Step 2 initialized, currentStep:', currentStep)">
                    <!-- Product Type Toggle -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Produk dengan Variasi</p>
                                <p class="text-xs text-gray-500 mt-1">Aktifkan jika produk memiliki pilihan (misal: ukuran, warna)</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="has_variants_toggle" name="has_variants" value="1" x-model="formData.has_variants" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Simple Product Fields -->
                    <div x-show="!formData.has_variants" x-cloak>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-2">Harga Modal <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                    <input type="number" id="cost_price" x-model="formData.cost_price" step="0.01"
                                           class="w-full h-11 pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                           placeholder="0" :required="!formData.has_variants">
                                </div>
                            </div>

                            <div>
                                <label for="selling_price" class="block text-sm font-medium text-gray-700 mb-2">Harga Jual <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                    <input type="number" id="selling_price" x-model="formData.selling_price" step="0.01"
                                           class="w-full h-11 pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                           placeholder="0" :required="!formData.has_variants">
                                </div>
                                <p class="text-xs text-gray-500 mt-1" id="margin_hint"></p>
                            </div>

                            <div>
                                <label for="weight_grams" class="block text-sm font-medium text-gray-700 mb-2">Berat (gram)</label>
                                <input type="number" id="weight_grams" x-model="formData.weight_grams" min="0" step="1"
                                       class="w-full h-11 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="0">
                                <p class="text-xs text-gray-500 mt-1">Untuk hitung ongkir</p>
                            </div>
                        </div>
                    </div>

                    <!-- Variant Manager -->
                    <div x-show="formData.has_variants" x-cloak>
                        @include('products.partials.variant-manager', ['product' => (object)['sku' => '', 'has_variants' => false, 'variant_groups' => []]])
                    </div>

                    <!-- Preorder Settings -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex items-start gap-3 mb-4">
                            <input type="checkbox" id="allow_preorder" name="allow_preorder" value="1" x-model="formData.allow_preorder"
                                   class="h-4 w-4 text-primary border-gray-300 rounded mt-1">
                            <div>
                                <label for="allow_preorder" class="text-sm font-medium text-gray-700 cursor-pointer">Izinkan Preorder</label>
                                <p class="text-xs text-gray-500 mt-1">Pembeli bisa order meski stok habis</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="is_hidden" name="is_hidden" value="1" x-model="formData.is_hidden"
                                   class="h-4 w-4 text-primary border-gray-300 rounded mt-1">
                            <div>
                                <label for="is_hidden" class="text-sm font-medium text-gray-700 cursor-pointer">Jangan tampilkan di Storefront</label>
                                <p class="text-xs text-gray-500 mt-1">Produk hanya bisa dipesan melalui admin/backend</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Media -->
                <div x-show="currentStep === 2" x-cloak class="bg-white rounded-lg shadow-sm p-6 space-y-6">
                    <!-- Featured Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Gambar Utama <span class="text-gray-400 text-xs">(Opsional)</span>
                        </label>
                        <div class="flex flex-col md:flex-row items-start gap-6">
                            <div id="featuredImagePreview" class="w-full md:w-48 h-48 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden">
                                <span class="text-sm text-gray-400 text-center px-4">Belum ada gambar</span>
                            </div>
                            <div class="flex-1">
                                <button type="button" id="btnSelectFeatured" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-hover text-sm font-medium mb-3 w-full md:w-auto">
                                    Pilih Gambar Utama
                                </button>
                                <div class="space-y-2 text-sm text-gray-600">
                                    <p>• Gambar ditampilkan di katalog produk</p>
                                    <p>• Auto crop 1:1 (1080x1080)</p>
                                    <p>• Format: JPG, PNG (max 2MB)</p>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="featured_media_id" id="featuredMediaId" required>
                    </div>

                    <!-- Gallery -->
                    <div class="border-t border-gray-200 pt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Gallery Produk <span class="text-gray-400 text-xs font-normal">(Maks. 5 foto)</span>
                        </label>
                        <div id="galleryGrid" class="grid grid-cols-2 md:grid-cols-5 gap-3">
                            <div class="col-span-2 md:col-span-5">
                                <button type="button" id="btnAddToGallery" class="w-full bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 hover:border-primary hover:bg-blue-50 cursor-pointer transition flex items-center justify-center h-32">
                                    <div class="text-center">
                                        <div class="text-3xl text-gray-400">+</div>
                                        <div class="text-sm text-gray-500 mt-1">Tambah Foto</div>
                                    </div>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="gallery_media_ids" id="galleryMediaIds" value="[]">
                        <p class="text-xs text-gray-500 mt-3">💡 Drag & drop untuk ubah urutan tampilan</p>
                    </div>
                </div>

                <!-- Step 4: Description -->
                <div x-show="currentStep === 3" x-cloak class="bg-white rounded-lg shadow-sm p-6 space-y-6">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-gray-700">Deskripsi Produk</label>
                            <!-- Future: AI Generator Button -->
                            <!-- <button type="button" class="text-xs text-primary hover:text-primary-hover flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M13 7H7v6h6V7z"></path></svg>
                                Generate dengan AI
                            </button> -->
                        </div>
                        <div id="quill-editor" class="bg-white border border-gray-300 rounded-lg" style="min-height: 300px;"></div>
                        <input type="hidden" name="description" id="description" x-model="formData.description">
                        <p class="text-xs text-gray-500 mt-2">Jelaskan detail produk, kelebihan, dan informasi penting lainnya</p>
                    </div>

                    <!-- Custom Fields -->
                    <div id="customFieldsSection" class="border-t border-gray-200 pt-6" style="display: none;">
                        <h4 class="text-sm font-semibold text-gray-700 mb-4">Informasi Detail Produk</h4>
                        <div id="customFieldsInputContainer"></div>
                        <input type="hidden" name="custom_field_values" id="customFieldValuesData" value="{}">
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                    <div class="flex flex-col sm:flex-row justify-between gap-3">
                        <button type="button" @click="prevStep" x-show="currentStep > 0"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium order-2 sm:order-1">
                            ← Sebelumnya
                        </button>

                        <div class="flex gap-3 order-1 sm:order-2">
                            <a href="{{ route('products.index') }}"
                               class="flex-1 sm:flex-none px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center font-medium">
                                Batal
                            </a>
                            <button type="button" @click="nextStep" x-show="currentStep < steps.length - 1"
                                    class="flex-1 sm:flex-none px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-hover transition font-medium">
                                Selanjutnya →
                            </button>
                            <button type="submit" x-show="currentStep === steps.length - 1"
                                    class="flex-1 sm:flex-none px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-hover transition font-medium">
                                Simpan Produk
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modals (unchanged) -->
    @include('products.partials.modals')

    <!-- Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

    <!-- Alpine.js x-cloak style -->
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <!-- Scripts -->
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script src="{{ asset('js/autocomplete.js') }}"></script>
    <script src="{{ asset('js/product-form.js') }}"></script>
    <script src="{{ asset('js/media-picker.js') }}"></script>
    <script src="{{ asset('js/media-gallery.js') }}"></script>
    <script src="{{ asset('js/custom-fields-renderer.js') }}"></script>

    <script>
        function productWizard() {
            return {
                currentStep: 0,
                steps: [
                    { title: 'Informasi Dasar', subtitle: 'SKU, Nama & Kategori' },
                    { title: 'Harga & Stok', subtitle: 'Pricing & Inventory' },
                    { title: 'Media', subtitle: 'Foto & Gallery' },
                    { title: 'Deskripsi', subtitle: 'Detail Produk' }
                ],
                formData: {
                    sku: '{{ old('sku') }}',
                    name: '{{ old('name') }}',
                    category_id: '{{ old('category_id') }}',
                    supplier_id: '{{ old('supplier_id') }}',
                    cost_price: '{{ old('cost_price') }}',
                    selling_price: '{{ old('selling_price') }}',
                    weight_grams: '{{ old('weight_grams', 0) }}',
                    has_variants: {{ old('has_variants') ? 'true' : 'false' }},
                    allow_preorder: {{ old('allow_preorder') ? 'true' : 'false' }},
                    is_hidden: {{ old('is_hidden') ? 'true' : 'false' }},
                    description: '{{ old('description') }}'
                },

                nextStep() {
                    console.log('nextStep called, currentStep before:', this.currentStep);
                    if (this.validateCurrentStep()) {
                        // IMPORTANT: Sync visible input values to Alpine formData before hiding the step
                        this.syncCurrentStepData();

                        if (this.currentStep < this.steps.length - 1) {
                            this.currentStep++;
                            console.log('currentStep after increment:', this.currentStep);
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    } else {
                        console.log('Validation failed for step:', this.currentStep);
                    }
                },

                syncCurrentStepData() {
                    // Sync visible DOM values to Alpine formData before step changes
                    switch(this.currentStep) {
                        case 0: // Basic Info
                            this.formData.sku = document.getElementById('sku')?.value || this.formData.sku;
                            this.formData.name = document.getElementById('name')?.value || this.formData.name;
                            this.formData.category_id = document.getElementById('category_id')?.value || this.formData.category_id;
                            this.formData.supplier_id = document.getElementById('supplier_id')?.value || this.formData.supplier_id;
                            console.log('Synced Step 0 data:', this.formData.sku, this.formData.name);
                            break;
                        case 1: // Pricing
                            if (!this.formData.has_variants) {
                                this.formData.cost_price = document.getElementById('cost_price')?.value || this.formData.cost_price;
                                this.formData.selling_price = document.getElementById('selling_price')?.value || this.formData.selling_price;
                                this.formData.weight_grams = document.getElementById('weight_grams')?.value || this.formData.weight_grams;
                                console.log('Synced Step 1 pricing:', this.formData.cost_price, this.formData.selling_price, 'weight:', this.formData.weight_grams);
                            }
                            break;
                    }
                },

                prevStep() {
                    if (this.currentStep > 0) {
                        this.currentStep--;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                validateCurrentStep() {
                    console.log('Validating step:', this.currentStep);
                    switch(this.currentStep) {
                        case 0: // Basic Info
                            const categoryIdValue = document.getElementById('category_id').value;
                            console.log('Step 0 validation - SKU:', this.formData.sku, 'Name:', this.formData.name, 'Category ID:', categoryIdValue);
                            if (!this.formData.sku || !this.formData.name || !categoryIdValue) {
                                alert('Mohon lengkapi SKU, Nama Produk, dan Kategori');
                                return false;
                            }
                            break;
                        case 1: // Pricing
                            console.log('Step 1 validation - has_variants:', this.formData.has_variants);
                            if (!this.formData.has_variants) {
                                // Check DOM directly for price values (similar to category_id fix)
                                const costPriceValue = document.getElementById('cost_price')?.value;
                                const sellingPriceValue = document.getElementById('selling_price')?.value;
                                console.log('Price values from DOM - cost:', costPriceValue, 'selling:', sellingPriceValue);

                                if (!costPriceValue || !sellingPriceValue || parseFloat(costPriceValue) <= 0 || parseFloat(sellingPriceValue) <= 0) {
                                    alert('Mohon lengkapi Harga Modal dan Harga Jual');
                                    return false;
                                }
                            }
                            break;
                        case 2: // Media
                            // Featured image is now optional (nullable)
                            break;
                    }
                    return true;
                },

                handleSubmit() {
                    if (this.validateCurrentStep()) {
                        // Sync current step data one last time before submit
                        this.syncCurrentStepData();

                        console.log('Final formData before submit:', this.formData);

                        // Sync to hidden inputs (these will be submitted, visible inputs won't)
                        // Use formData as primary source since we've been syncing it
                        document.getElementById('sku_hidden').value = this.formData.sku || '';
                        document.getElementById('name_hidden').value = this.formData.name || '';
                        document.getElementById('category_id_hidden').value = this.formData.category_id || '';
                        document.getElementById('supplier_id_hidden').value = this.formData.supplier_id || '';

                        // Sync pricing (if not variant product)
                        if (!this.formData.has_variants) {
                            document.getElementById('cost_price_hidden').value = this.formData.cost_price || '';
                            document.getElementById('selling_price_hidden').value = this.formData.selling_price || '';
                            document.getElementById('weight_grams_hidden').value = this.formData.weight_grams || 0;
                        }

                        // Sync Quill content
                        if (window.quillEditor) {
                            document.getElementById('description').value = window.quillEditor.root.innerHTML;
                        }

                        // Debug: log all form data before submit
                        const formElement = document.getElementById('productForm');
                        const formData = new FormData(formElement);
                        console.log('=== FORM SUBMISSION DEBUG ===');
                        for (let [key, value] of formData.entries()) {
                            console.log(`${key}:`, value);
                        }
                        console.log('=== END DEBUG ===');

                        // Submit the form
                        try {
                            formElement.submit();
                            console.log('Form submitted successfully');
                        } catch (error) {
                            console.error('Form submission error:', error);
                            alert('Terjadi kesalahan saat submit form: ' + error.message);
                        }
                    }
                }
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Category Autocomplete (sesuai SOP)
            const categories = @json($categories);
            const categoryAutocomplete = new Autocomplete({
                inputId: 'category_search',
                hiddenInputId: 'category_id',
                dropdownId: 'category_suggestions',
                data: categories,
                searchFields: ['name'],
                displayTemplate: (category) => {
                    let html = `<div class="font-medium">${category.name}</div>`;
                    if (category.description) {
                        html += `<div class="text-xs text-gray-500">${category.description}</div>`;
                    }
                    return html;
                },
                maxItems: 10,
                onSelect: (category) => {
                    console.log('Category selected:', category);
                }
            });

            // Supplier Autocomplete (sesuai SOP)
            const suppliers = @json($suppliers);
            const supplierAutocomplete = new Autocomplete({
                inputId: 'supplier_search',
                hiddenInputId: 'supplier_id',
                dropdownId: 'supplier_suggestions',
                data: suppliers,
                searchFields: ['name', 'email', 'phone'],
                displayTemplate: (supplier) => {
                    let html = `<div class="font-medium">${supplier.name}</div>`;
                    if (supplier.phone) {
                        html += `<div class="text-xs text-gray-500">Phone: ${supplier.phone}</div>`;
                    }
                    return html;
                },
                maxItems: 10,
                onSelect: (supplier) => {
                    console.log('Supplier selected:', supplier);
                }
            });

            // Modal handlers
            const modalAddCategory = document.getElementById('modal_add_category');
            const modalAddSupplier = document.getElementById('modal_add_supplier');
            const btnAddCategory = document.getElementById('btn_add_category');
            const btnAddSupplier = document.getElementById('btn_add_supplier');
            const btnCloseModal = document.getElementById('btn_close_modal');
            const btnCloseSupplierModal = document.getElementById('btn_close_supplier_modal');
            const formAddCategory = document.getElementById('form_add_category');
            const formAddSupplier = document.getElementById('form_add_supplier');

            // Open category modal
            btnAddCategory.addEventListener('click', () => {
                modalAddCategory.classList.remove('hidden');
            });

            // Open supplier modal
            btnAddSupplier.addEventListener('click', () => {
                modalAddSupplier.classList.remove('hidden');
            });

            // Close category modal
            btnCloseModal.addEventListener('click', () => {
                modalAddCategory.classList.add('hidden');
                formAddCategory.reset();
            });

            // Close supplier modal
            btnCloseSupplierModal.addEventListener('click', () => {
                modalAddSupplier.classList.add('hidden');
                formAddSupplier.reset();
            });

            // Submit category form
            formAddCategory.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(formAddCategory);
                const data = Object.fromEntries(formData.entries());

                try {
                    const response = await fetch('{{ route('product-categories.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (response.ok) {
                        // Add to categories data and autocomplete
                        categoryAutocomplete.data.push(result.category);
                        categoryAutocomplete.updateData(categoryAutocomplete.data);

                        // Select the new category
                        categoryAutocomplete.select(result.category);

                        // Close modal and reset form
                        modalAddCategory.classList.add('hidden');
                        formAddCategory.reset();
                    } else {
                        // Show validation errors
                        if (result.errors) {
                            if (result.errors.name) {
                                document.getElementById('error_category_name').textContent = result.errors.name[0];
                                document.getElementById('error_category_name').classList.remove('hidden');
                            }
                        } else {
                            alert(result.message || 'Gagal menyimpan kategori');
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan kategori');
                }
            });

            // Submit supplier form
            formAddSupplier.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(formAddSupplier);
                const data = Object.fromEntries(formData.entries());

                try {
                    const response = await fetch('{{ route('suppliers.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (response.ok) {
                        // Add to suppliers data and autocomplete
                        supplierAutocomplete.data.push(result.supplier);
                        supplierAutocomplete.updateData(supplierAutocomplete.data);

                        // Select the new supplier
                        supplierAutocomplete.select(result.supplier);

                        // Close modal and reset form
                        modalAddSupplier.classList.add('hidden');
                        formAddSupplier.reset();
                    } else {
                        // Show validation errors
                        if (result.errors) {
                            if (result.errors.name) {
                                document.getElementById('error_supplier_name').textContent = result.errors.name[0];
                                document.getElementById('error_supplier_name').classList.remove('hidden');
                            }
                        } else {
                            alert(result.message || 'Gagal menyimpan supplier');
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan supplier');
                }
            });

            // Quill Editor
            window.quillEditor = new Quill('#quill-editor', {
                theme: 'snow',
                placeholder: 'Tuliskan deskripsi produk di sini...',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'align': [] }],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });

            // Load old description if exists
            const oldDescription = '{{ old('description') }}';
            if (oldDescription) {
                window.quillEditor.root.innerHTML = oldDescription;
            }

            // Keep hidden textarea in sync so updates always persist
            const descriptionInput = document.getElementById('description');
            if (descriptionInput) {
                descriptionInput.value = window.quillEditor.root.innerHTML;
            }

            window.quillEditor.on('text-change', function () {
                if (descriptionInput) {
                    descriptionInput.value = window.quillEditor.root.innerHTML;
                }
            });

            const createForm = document.querySelector('form');
            if (createForm) {
                createForm.addEventListener('submit', function () {
                    if (descriptionInput) {
                        descriptionInput.value = window.quillEditor.root.innerHTML;
                    }
                });
            }

            // Margin Calculator
            const minMarginPercent = {{ $minMargin }};
            window.minMarginPercent = minMarginPercent; // Make it globally available for variant-manager
            const costInput = document.getElementById('cost_price');
            const sellInput = document.getElementById('selling_price');
            const marginHint = document.getElementById('margin_hint');
            let sellingEdited = false;

            sellInput.addEventListener('input', () => {
                sellingEdited = true;
                updateMarginHint();
            });

            costInput.addEventListener('input', () => {
                if (!sellingEdited || !sellInput.value) {
                    const cost = parseFloat(costInput.value || 0);
                    // Formula untuk margin (bukan markup): Jual = Modal / (1 - Margin%)
                    const suggested = cost / (1 - minMarginPercent / 100);
                    if (!isNaN(suggested) && suggested > 0) {
                        sellInput.value = suggested.toFixed(2);
                    }
                }
                updateMarginHint();
            });

            function updateMarginHint() {
                const cost = parseFloat(costInput.value || 0);
                const sell = parseFloat(sellInput.value || 0);
                if (!marginHint) return;
                if (sell <= 0 || cost <= 0) {
                    marginHint.textContent = '';
                    return;
                }
                const marginPct = ((sell - cost) / sell) * 100;
                // Formula untuk margin: Jual = Modal / (1 - Margin%)
                const suggested = (cost / (1 - minMarginPercent / 100)).toFixed(2);
                marginHint.textContent = `Margin: ${marginPct.toFixed(1)}% (minimum ${minMarginPercent}% → saran Rp ${Number(suggested).toLocaleString('id-ID')})`;
                marginHint.className = `text-xs mt-1 ${marginPct < minMarginPercent ? 'text-red-500' : 'text-green-600'}`;
            }

            // Initialize Product Gallery
            const gallery = new ProductGallery({
                featuredImageId: null,
                galleryMediaIds: [],
                listUrl: '{{ route('media.product_photo.list') }}',
                uploadUrl: '{{ route('media.store') }}',
                productId: null,
                aiEnabled: {{ ($aiEnabled ?? false) ? 'true' : 'false' }},
                aiRoutes: {!! json_encode([
                    'features' => route('ai.features'),
                    'enhance' => route('ai.enhance'),
                    'job' => url('ai/jobs'),
                ]) !!},
                csrfToken: '{{ csrf_token() }}',
                onFeaturedChange: (mediaId) => {
                    document.getElementById('featuredMediaId').value = mediaId;
                },
                onGalleryChange: (mediaIds) => {
                    document.getElementById('galleryMediaIds').value = JSON.stringify(mediaIds);
                }
            });

            document.getElementById('btnSelectFeatured').addEventListener('click', () => {
                gallery.openFeaturedPicker();
            });

            const btnAddToGallery = document.getElementById('btnAddToGallery');
            if (btnAddToGallery) {
                btnAddToGallery.addEventListener('click', () => {
                    gallery.openGalleryPicker();
                });
            }

            // Custom Fields Renderer
            const customFieldsRenderer = new CustomFieldsRenderer({
                containerId: 'customFieldsInputContainer',
                hiddenInputId: 'customFieldValuesData',
                initialValues: {},
                autoInit: false
            });

            // Watch for category changes
            const categoryIdInput = document.getElementById('category_id');
            if (categoryIdInput) {
                const observer = new MutationObserver(() => {
                    const newCategoryId = categoryIdInput.value;
                    if (newCategoryId && newCategoryId !== 'undefined' && !isNaN(newCategoryId) && newCategoryId.trim() !== '') {
                        loadCategoryCustomFields(newCategoryId);
                    } else {
                        customFieldsRenderer.clear();
                        document.getElementById('customFieldsSection').style.display = 'none';
                    }
                });

                observer.observe(categoryIdInput, {
                    attributes: true,
                    attributeFilter: ['value']
                });

                categoryIdInput.addEventListener('change', function() {
                    const newCategoryId = this.value;
                    if (newCategoryId && newCategoryId !== 'undefined' && !isNaN(newCategoryId) && newCategoryId.trim() !== '') {
                        loadCategoryCustomFields(newCategoryId);
                    } else {
                        customFieldsRenderer.clear();
                        document.getElementById('customFieldsSection').style.display = 'none';
                    }
                });
            }

            async function loadCategoryCustomFields(categoryId) {
                try {
                    const response = await fetch(`/api/product-categories/${categoryId}/custom-fields`);
                    if (!response.ok) throw new Error('Failed to load custom fields');
                    const data = await response.json();
                    if (data.custom_fields && data.custom_fields.length > 0) {
                        customFieldsRenderer.setFields(data.custom_fields);
                        document.getElementById('customFieldsSection').style.display = 'block';
                    } else {
                        customFieldsRenderer.clear();
                        document.getElementById('customFieldsSection').style.display = 'none';
                    }
                } catch (error) {
                    console.error('Error loading custom fields:', error);
                    customFieldsRenderer.clear();
                    document.getElementById('customFieldsSection').style.display = 'none';
                }
            }
        });
    </script>
</x-app-layout>
