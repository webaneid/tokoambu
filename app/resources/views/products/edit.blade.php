<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('products.index') }}" class="text-blue-600 hover:underline">Produk</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-900">Edit Produk</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-6">Form Edit Produk</h3>

                    <form action="{{ route('products.update', $product) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="sku" class="block text-sm font-medium text-gray-700 mb-2">SKU <span class="text-red-500">*</span></label>
                            <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('sku') border-red-500 @enderror" placeholder="SKU" required>
                            @error('sku')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Produk <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('name') border-red-500 @enderror" placeholder="Nama Produk" required>
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <div class="flex-1 relative">
                                    <input type="hidden" id="category_id" name="category_id" value="{{ old('category_id', $product->category_id) }}">
                                    <input type="text" id="category_search" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('category_id') border-red-500 @enderror" placeholder="Cari atau pilih kategori" autocomplete="off" value="{{ old('category_name', $product->category->name ?? '') }}">
                                    <div id="category_suggestions" class="absolute z-10 top-full left-0 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto"></div>
                                </div>
                                <button type="button" id="btn_add_category" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">+ Kategori</button>
                            </div>
                            @error('category_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                            <div class="flex gap-2">
                                <div class="flex-1 relative">
                                    <input type="hidden" id="supplier_id" name="supplier_id" value="{{ old('supplier_id', $product->supplier_id) }}">
                                    <input type="text" id="supplier_search" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('supplier_id') border-red-500 @enderror" placeholder="Cari atau pilih supplier" autocomplete="off" value="{{ old('supplier_name', $product->supplier->name ?? '') }}">
                                    <div id="supplier_suggestions" class="absolute z-10 top-full left-0 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto"></div>
                                </div>
                                <button type="button" id="btn_add_supplier" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">+ Supplier</button>
                            </div>
                            @error('supplier_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4" id="simple_cost_price_field">
                            <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-2">Harga Modal <span class="text-red-500">*</span></label>
                            <input type="number" id="cost_price" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('cost_price') border-red-500 @enderror" placeholder="0.00">
                            @error('cost_price')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4" id="simple_selling_price_field">
                            <label for="selling_price" class="block text-sm font-medium text-gray-700 mb-2">Harga Jual <span class="text-red-500">*</span></label>
                            <input type="number" id="selling_price" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('selling_price') border-red-500 @enderror" placeholder="0.00">
                            <p class="text-xs text-gray-500 mt-1" id="margin_hint_edit"></p>
                            @error('selling_price')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4" id="simple_weight_field">
                            <label for="weight_grams" class="block text-sm font-medium text-gray-700 mb-2">Berat (gram)</label>
                            <input type="number" id="weight_grams" name="weight_grams" value="{{ old('weight_grams', $product->weight_grams ?? 0) }}" min="0" step="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary @error('weight_grams') border-red-500 @enderror" placeholder="0">
                            <p class="text-xs text-gray-500 mt-1">Dipakai untuk hitung ongkir (RajaOngkir).</p>
                            @error('weight_grams')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preorder</label>
                            <div class="flex items-center gap-3">
                                <input type="checkbox" id="allow_preorder" name="allow_preorder" value="1" class="h-4 w-4 text-primary border-gray-300 rounded" @checked(old('allow_preorder', $product->allow_preorder))>
                                <label for="allow_preorder" class="text-sm text-gray-700">Izinkan preorder saat stok habis</label>
                            </div>
                            @error('allow_preorder')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <div id="quill-editor" class="bg-white border border-gray-300 rounded-lg" style="min-height: 200px;"></div>
                            <textarea id="description" name="description" class="hidden">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Variant Manager -->
                        <div class="mb-6 border-t border-gray-200 pt-6">
                            @include('products.partials.variant-manager', ['product' => $product])
                        </div>

                        <!-- Featured Image -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Gambar Utama <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-start gap-4">
                                <div id="featuredImagePreview" class="w-40 h-40 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden">
                                    @if($product->featuredMedia)
                                        <img src="{{ $product->featuredMedia->url }}" alt="{{ $product->featuredMedia->filename }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xs text-gray-400 text-center px-2">Belum ada gambar utama</span>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <button type="button" id="btnSelectFeatured" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover text-sm font-medium mb-2">
                                        Pilih Gambar Utama
                                    </button>
                                    <p class="text-xs text-gray-500 mb-1">Gambar utama yang ditampilkan di list produk.</p>
                                    <p class="text-xs text-gray-400">• Auto crop 1:1 (1080x1080)</p>
                                    <p class="text-xs text-gray-400">• Dikonversi ke WebP</p>
                                </div>
                            </div>
                            <input type="hidden" name="featured_media_id" id="featuredMediaId" value="{{ old('featured_media_id', $product->featured_media_id) }}" required>
                            @error('featured_media_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Gallery -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Gallery Produk
                                <span class="text-gray-400 text-xs font-normal">(Maksimal 5 foto)</span>
                            </label>
                            <div id="galleryGrid" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-3 gap-4">
                                <!-- Will be populated by JavaScript -->
                            </div>
                            <input type="hidden" name="gallery_media_ids" id="galleryMediaIds" value="{{ old('gallery_media_ids', json_encode($product->galleryMedia->pluck('id')->toArray())) }}">
                            <p class="text-xs text-gray-500 mt-2">Drag & drop untuk mengubah urutan tampilan.</p>
                            @error('gallery_media_ids') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Custom Fields -->
                        <div class="mb-6" id="customFieldsSection" style="display: none;">
                            <div class="border-t border-gray-200 pt-6">
                                <h4 class="text-sm font-semibold text-gray-700 mb-4">Informasi Detail Produk</h4>
                                <div id="customFieldsInputContainer"></div>
                                <input type="hidden" name="custom_field_values" id="customFieldValuesData" value="{{ old('custom_field_values', json_encode($product->custom_field_values ?: new stdClass())) }}">
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('products.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Batal</a>
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Kategori -->
    <div id="modal_add_category" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
            <h4 class="text-lg font-semibold mb-4">Tambah Kategori Produk</h4>
            <form id="form_add_category">
                <div class="mb-4">
                    <label for="category_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" id="category_name" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Nama kategori" required>
                    <p id="error_category_name" class="text-red-500 text-sm mt-1 hidden"></p>
                </div>
                <div class="mb-6">
                    <label for="category_description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea id="category_description" name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Deskripsi kategori"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" id="btn_close_modal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" id="btn_submit_category" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Supplier -->
    <div id="modal_add_supplier" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
            <h4 class="text-lg font-semibold mb-4">Tambah Supplier</h4>
            <form id="form_add_supplier">
                <div class="mb-4">
                    <label for="supplier_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Supplier <span class="text-red-500">*</span></label>
                    <input type="text" id="supplier_name" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Nama supplier" required>
                    <p id="error_supplier_name" class="text-red-500 text-sm mt-1 hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="supplier_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="supplier_email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="email@example.com">
                </div>
                <div class="mb-4">
                    <label for="supplier_phone" class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                    <input type="text" id="supplier_phone" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="08xx xxxx xxxx">
                </div>
                <div class="mb-6">
                    <label for="supplier_address" class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                    <textarea id="supplier_address" name="address" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Alamat lengkap"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" id="btn_close_supplier_modal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" id="btn_submit_supplier" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

    <!-- Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script src="{{ asset('js/product-form.js') }}"></script>
    <script src="{{ asset('js/media-picker.js') }}"></script>
    <script src="{{ asset('js/media-gallery.js') }}"></script>
    <script src="{{ asset('js/custom-fields-renderer.js') }}"></script>
    <script>
        // Initialize Quill Editor
        const quillEditor = new Quill('#quill-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['link'],
                    ['clean']
                ]
            },
            placeholder: 'Tulis deskripsi produk di sini...'
        });

        // Load existing description
        const existingDescription = {!! json_encode(old('description', $product->description)) !!};
        if (existingDescription) {
            quillEditor.root.innerHTML = existingDescription;
        }

        // Keep hidden textarea in sync so updates always persist
        const descriptionInput = document.getElementById('description');
        if (descriptionInput) {
            descriptionInput.value = quillEditor.root.innerHTML;
        }

        quillEditor.on('text-change', function () {
            if (descriptionInput) {
                descriptionInput.value = quillEditor.root.innerHTML;
            }
        });

        // Sync Quill content to hidden textarea before form submit
        const editForm = document.querySelector('form');
        editForm.addEventListener('submit', function() {
            if (descriptionInput) {
                descriptionInput.value = quillEditor.root.innerHTML;
            }

            // IMPORTANT: Update variants with images before submit
            // This ensures variant_image_id is set based on uploaded images
            if (typeof window.updateVariantsWithImages === 'function') {
                console.log('Calling updateVariantsWithImages before form submit...');
                window.updateVariantsWithImages();
            }
        });

        // Store quillEditor globally for access
        window.quillEditor = quillEditor;

        const minMarginPercentEdit = {{ $minMargin }};
        window.minMarginPercent = minMarginPercentEdit; // Make it globally available for variant-manager
        const costInputEdit = document.getElementById('cost_price');
        const sellInputEdit = document.getElementById('selling_price');
        const marginHintEdit = document.getElementById('margin_hint_edit');
        const featuredMediaId = document.getElementById('featuredMediaId');
        const galleryMediaIds = document.getElementById('galleryMediaIds');
        const btnSelectFeatured = document.getElementById('btnSelectFeatured');

        function updateMarginHintEdit() {
            const cost = parseFloat(costInputEdit.value || 0);
            const sell = parseFloat(sellInputEdit.value || 0);
            if (!marginHintEdit) return;
            if (sell <= 0 || cost <= 0) {
                marginHintEdit.textContent = '';
                return;
            }
            const marginPct = ((sell - cost) / sell) * 100;
            const suggested = (cost * (1 + minMarginPercentEdit / 100)).toFixed(2);
            marginHintEdit.textContent = `Margin: ${marginPct.toFixed(1)}% (minimum ${minMarginPercentEdit}% → harga saran Rp ${Number(suggested).toLocaleString('id-ID')})`;
            marginHintEdit.className = `text-xs mt-1 ${marginPct < minMarginPercentEdit ? 'text-red-500' : 'text-gray-500'}`;
        }

        costInputEdit.addEventListener('input', updateMarginHintEdit);
        sellInputEdit.addEventListener('input', updateMarginHintEdit);
        updateMarginHintEdit();

        // Initialize ProductGallery with existing product data
        const gallery = new ProductGallery({
            featuredImageId: {{ $product->featured_media_id ?? 'null' }},
            galleryMediaIds: {!! json_encode($product->galleryMedia->pluck('id')->toArray()) !!},
            listUrl: '{{ route('media.product_photo.list') }}',
            uploadUrl: '{{ route('media.store') }}',
            productId: {{ $product->id }},
            aiEnabled: {{ ($aiEnabled ?? false) ? 'true' : 'false' }},
            aiRoutes: {!! json_encode([
                'features' => route('ai.features'),
                'enhance' => route('ai.enhance'),
                'job' => url('ai/jobs'),
            ]) !!},
            csrfToken: '{{ csrf_token() }}',
            onFeaturedChange: (mediaId) => {
                featuredMediaId.value = mediaId;
            },
            onGalleryChange: (mediaIds) => {
                galleryMediaIds.value = JSON.stringify(mediaIds);
            }
        });

        // Set existing featured media data
        @if($product->featuredMedia)
        gallery.featuredMedia = {!! json_encode([
            'id' => $product->featuredMedia->id,
            'url' => $product->featuredMedia->url,
            'filename' => $product->featuredMedia->filename
        ]) !!};
        gallery.renderFeatured();
        @endif

        // Set existing gallery media data
        gallery.galleryMedia = {!! json_encode($product->galleryMedia->map(function($media) {
            return [
                'id' => $media->id,
                'url' => $media->url,
                'filename' => $media->filename
            ];
        })->toArray()) !!};
        gallery.renderGallery();

        // Button event listener
        btnSelectFeatured.addEventListener('click', () => {
            gallery.openFeaturedPicker();
        });

        // Custom Fields Renderer
        const customFieldsRenderer = new CustomFieldsRenderer({
            containerId: 'customFieldsInputContainer',
            hiddenInputId: 'customFieldValuesData',
            initialValues: {!! json_encode($product->custom_field_values ?: new stdClass()) !!},
            autoInit: false
        });

        // Load category custom fields from server data
        const categoryCustomFields = {!! json_encode($categoryCustomFields) !!};
        if (categoryCustomFields && categoryCustomFields.length > 0) {
            customFieldsRenderer.setFields(categoryCustomFields);
            document.getElementById('customFieldsSection').style.display = 'block';
        }

        // Watch for category changes and reload custom fields
        const categoryIdInput = document.getElementById('category_id');
        if (categoryIdInput) {
            const observer = new MutationObserver(() => {
                const newCategoryId = categoryIdInput.value;
                // Validate: must be a valid number, not empty, not "undefined" string
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

            // Also listen to input event
            categoryIdInput.addEventListener('change', function() {
                const newCategoryId = this.value;
                // Validate: must be a valid number, not empty, not "undefined" string
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
                if (!response.ok) {
                    throw new Error('Failed to load custom fields');
                }

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

        // Handle variant toggle - hide/show simple product fields
        // Note: has_variants_toggle checkbox was removed. Now check product's has_variants property from server
        const simpleCostField = document.getElementById('simple_cost_price_field');
        const simpleSellingField = document.getElementById('simple_selling_price_field');
        const simpleWeightField = document.getElementById('simple_weight_field');

        // Check if product has variants from data attribute
        const variantManager = document.getElementById('variant-manager');
        const productHasVariants = variantManager?.dataset.hasVariants === 'true' || variantManager?.dataset.hasVariants === '1';

        // Initial state: hide simple product fields if product has variants
        function toggleSimpleFields(isVariant) {
            const displayMode = isVariant ? 'none' : 'block';

            if (simpleCostField) simpleCostField.style.display = displayMode;
            if (simpleSellingField) simpleSellingField.style.display = displayMode;
            if (simpleWeightField) simpleWeightField.style.display = displayMode;

            // Update required attribute
            const costInput = document.getElementById('cost_price');
            const sellInput = document.getElementById('selling_price');
            if (costInput) costInput.required = !isVariant;
            if (sellInput) sellInput.required = !isVariant;
        }

        // Initial toggle based on product data
        toggleSimpleFields(productHasVariants);

        // Debug form submission
        const productForm = document.querySelector('form');

        if (productForm) {
            productForm.addEventListener('submit', function(e) {
                const variantsInput = document.getElementById('variants_data');
                const variantGroupsInput = document.getElementById('variant_groups_data');

                // Don't prevent default, let form submit normally
            });
        } else {
            console.error('Product form not found!');
        }
    </script>
</x-app-layout>
