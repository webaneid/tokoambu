<!-- Variant Manager Component -->
<div id="variant-manager" data-product-sku="{{ $product->sku ?? '' }}" data-has-variants="{{ $product->has_variants ?? false }}">
    <!-- Variant Configuration -->
    <div id="variant-config">
        <!-- Variant Groups Builder -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Kelompok Variasi</label>

            <div id="variant-groups-container" class="space-y-3">
                <!-- Dynamic variant groups will be added here -->
            </div>

            <button type="button" id="add-variant-group" class="mt-3 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                + Tambah Kelompok Variasi
            </button>
        </div>

        <!-- Generate Button -->
        <div class="mb-6">
            <button type="button" id="generate-combinations" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-hover transition font-semibold">
                Generate Kombinasi Variasi
            </button>
            <span id="combination-count" class="ml-3 text-sm text-gray-600"></span>
        </div>

        <!-- Tabs: Variasi / Harga / Stok / Gambar Variasi -->
        <div class="mb-6">
            <div class="flex flex-wrap gap-2 border-b border-gray-200">
                <button type="button" data-variant-tab="variasi" class="px-4 py-2 text-sm font-semibold border-b-2 border-primary text-primary">
                    Variasi
                </button>
                <button type="button" data-variant-tab="harga" class="px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-gray-900">
                    Harga
                </button>
                <button type="button" data-variant-tab="stok" class="px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-gray-900">
                    Stok
                </button>
                <button type="button" data-variant-tab="gambar" class="px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-gray-900">
                    Gambar Variasi
                </button>
            </div>
        </div>

        <!-- Tab: Variasi -->
        <div data-variant-panel="variasi" class="mb-6">
            <div class="overflow-x-auto">
                <table id="variants-table" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kombinasi</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Harga Modal</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Harga Jual</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Margin %</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Berat (g)</th>
                        </tr>
                    </thead>
                    <tbody id="variants-tbody" class="divide-y divide-gray-200 bg-white">
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 text-sm">
                                Klik "Generate Kombinasi Variasi" untuk membuat variasi
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Harga (Bulk Pricing Helper) -->
        <div data-variant-panel="harga" class="mb-6 hidden">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-gray-700"><strong>Bulk Pricing Helper:</strong> Atur harga untuk semua variasi dengan atribut tertentu sekaligus.</p>
            </div>

            <div id="bulk-pricing-rules" class="space-y-4">
                <!-- Dynamic bulk pricing rules -->
            </div>

            <button type="button" id="add-bulk-rule" class="mt-3 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                + Tambah Aturan Harga
            </button>

            <div class="mt-4">
                <button type="button" id="apply-bulk-pricing" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-hover transition font-semibold">
                    Terapkan Harga Massal
                </button>
            </div>
        </div>

        <!-- Tab: Stok (Info only, actual stock managed in inventory) -->
        <div data-variant-panel="stok" class="mb-6 hidden">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-gray-700">
                    <strong>Info:</strong> Stok untuk setiap variasi dikelola melalui sistem inventory (Purchase → Receive).
                    Saat ini Anda hanya perlu mengatur harga dan informasi variasi. Stok akan muncul setelah ada penerimaan barang.
                </p>
            </div>
        </div>

        <!-- Tab: Gambar Variasi -->
        <div data-variant-panel="gambar" class="mb-6 hidden">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-gray-700">
                    <strong>Upload Foto per Grup Variasi:</strong> Upload foto untuk setiap opsi variasi di bawah ini.
                    Foto akan otomatis diterapkan ke kombinasi variasi yang sesuai.
                </p>
            </div>

            <div id="variant-images-container" class="space-y-4">
                <!-- Dynamically rendered variant groups with image upload -->
            </div>
        </div>

        <!-- Hidden input for variant data -->
        <input type="hidden" id="variant_groups_data" name="variant_groups" value="{{ json_encode($product->variant_groups ?? []) }}">
        <input type="hidden" id="variants_data" name="variants" value="">
        <input type="hidden" id="variant_images_data" name="variant_images" value="">
    </div>
</div>

<script>
(function() {
    const manager = document.getElementById('variant-manager');
    if (!manager) return;

    const variantConfig = document.getElementById('variant-config');
    const groupsContainer = document.getElementById('variant-groups-container');
    const addGroupBtn = document.getElementById('add-variant-group');
    const generateBtn = document.getElementById('generate-combinations');
    const combinationCount = document.getElementById('combination-count');
    const variantsTbody = document.getElementById('variants-tbody');
    const bulkPricingRules = document.getElementById('bulk-pricing-rules');
    const addBulkRuleBtn = document.getElementById('add-bulk-rule');
    const applyBulkPricingBtn = document.getElementById('apply-bulk-pricing');

    const variantGroupsInput = document.getElementById('variant_groups_data');
    const variantsInput = document.getElementById('variants_data');
    const variantImagesInput = document.getElementById('variant_images_data');
    const variantImagesContainer = document.getElementById('variant-images-container');

    let variantGroups = [];
    let variants = [];
    let variantImages = {}; // { "Warna": { "Merah": media_id, "Hijau": media_id } }
    let groupIdCounter = 0;

    // Load existing variant groups
    try {
        const existingGroups = JSON.parse(variantGroupsInput.value || '[]');

        if (existingGroups.length > 0) {
            variantGroups = existingGroups.map((g) => {
                return {
                    id: groupIdCounter++,
                    name: g.name,
                    options: g.options
                };
            });
            renderGroups();
        }
    } catch (e) {
        console.error('Failed to parse variant groups', e);
    }

    // Load existing variants from server (for edit mode)
    const productSku = manager.dataset.productSku;
    const hasExistingVariants = manager.dataset.hasVariants === 'true' || manager.dataset.hasVariants === '1';

    if (productSku && hasExistingVariants) {
        fetch(`/products/${productSku}/variants`)
            .then(response => response.json())
            .then(data => {
                if (data.variants && data.variants.length > 0) {
                    variants = data.variants;

                    // Extract variant images from loaded variants
                    loadVariantImagesFromVariants();

                    renderVariantsTable();
                    renderBulkPricingOptions();
                }
            })
            .catch(error => {
                console.error('Failed to load existing variants', error);
            });
    }

    // Helper function to extract variant images from loaded variants
    function loadVariantImagesFromVariants() {
        variantImages = {}; // Reset

        variants.forEach(variant => {
            if (variant.variant_image_id && variant.variant_image) {
                // variant_image is the Media relationship loaded by API
                const imageUrl = variant.variant_image.url || `/storage/${variant.variant_image.path}`;

                // Map image to variant attributes
                for (const [groupName, attributeValue] of Object.entries(variant.variant_attributes)) {
                    if (!variantImages[groupName]) {
                        variantImages[groupName] = {};
                    }

                    // Store image for this attribute value
                    variantImages[groupName][attributeValue] = {
                        id: variant.variant_image_id,
                        url: imageUrl
                    };


                    // Only use first attribute group with image
                    break;
                }
            }
        });

        saveVariantImagesData();
    }

    // Note: Variant toggle is now managed in create.blade.php Step 2
    // This component is only shown when has_variants is true

    // Add variant group
    addGroupBtn.addEventListener('click', function() {
        const group = {
            id: groupIdCounter++,
            name: '',
            options: []
        };
        variantGroups.push(group);
        renderGroups();
    });

    function renderGroups() {

        groupsContainer.innerHTML = '';
        variantGroups.forEach((group, index) => {
            const groupEl = document.createElement('div');
            groupEl.className = 'p-4 border border-gray-300 rounded-lg bg-white';
            groupEl.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="flex-1">
                        <input type="text"
                            data-group-id="${group.id}"
                            data-field="name"
                            value="${group.name}"
                            placeholder="Nama Variasi (contoh: Ukuran, Warna)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 focus:outline-none focus:border-primary">
                        <input type="text"
                            data-group-id="${group.id}"
                            data-field="options"
                            value="${group.options.join(', ')}"
                            placeholder="Opsi (pisahkan dengan koma, contoh: M, L, XL)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                    <button type="button" data-group-id="${group.id}" class="remove-group px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                        Hapus
                    </button>
                </div>
            `;
            groupsContainer.appendChild(groupEl);
        });

        // Attach event listeners
        groupsContainer.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', handleGroupInput);
        });
        groupsContainer.querySelectorAll('.remove-group').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.dataset.groupId);
                variantGroups = variantGroups.filter(g => g.id !== id);
                renderGroups();
                updateCombinationCount();
            });
        });

        updateCombinationCount();
    }

    function handleGroupInput(e) {
        const id = parseInt(e.target.dataset.groupId);
        const field = e.target.dataset.field;
        const value = e.target.value;

        const group = variantGroups.find(g => g.id === id);
        if (!group) return;

        if (field === 'name') {
            group.name = value;
        } else if (field === 'options') {
            group.options = value.split(',').map(opt => opt.trim()).filter(opt => opt.length > 0);
        }

        updateCombinationCount();
    }

    function updateCombinationCount() {
        let count = 1;
        let valid = true;

        variantGroups.forEach(group => {
            if (!group.name || group.options.length === 0) {
                valid = false;
            }
            count *= group.options.length;
        });

        if (variantGroups.length === 0 || !valid) {
            combinationCount.textContent = '';
        } else {
            combinationCount.textContent = `(${count} kombinasi akan dibuat)`;
        }
    }

    // Generate combinations locally (for create mode)
    function generateCombinationsLocally(groups) {
        // Generate cartesian product
        const cartesian = (...arrays) => {
            return arrays.reduce((acc, array) => {
                return acc.flatMap(x => array.map(y => [...x, y]));
            }, [[]]);
        };

        const optionArrays = groups.map(g => g.options);
        const combinations = cartesian(...optionArrays);

        // Get default prices from product form
        const defaultCostPrice = parseFloat(document.getElementById('cost_price')?.value) || 0;
        const defaultSellingPrice = parseFloat(document.getElementById('selling_price')?.value) || 0;
        const defaultWeight = parseFloat(document.getElementById('weight_grams')?.value) || 0;

        return combinations.map((combo, index) => {
            const attributes = {};
            groups.forEach((group, i) => {
                attributes[group.name] = combo[i];
            });

            // Generate SKU suffix from combination
            const skuSuffix = combo.join('-').toUpperCase().replace(/\s+/g, '');
            const baseSku = document.getElementById('sku')?.value || 'PROD';

            return {
                sku: `${baseSku}-${skuSuffix}`,
                variant_attributes: attributes,
                cost_price: defaultCostPrice,
                selling_price: defaultSellingPrice,
                weight_grams: defaultWeight,
                is_active: true
            };
        });
    }

    // Generate combinations
    generateBtn.addEventListener('click', async function() {
        // Validate
        if (variantGroups.length === 0) {
            alert('Tambahkan minimal 1 kelompok variasi terlebih dahulu.');
            return;
        }

        for (const group of variantGroups) {
            if (!group.name || group.options.length === 0) {
                alert(`Kelompok variasi "${group.name || '(kosong)'}" tidak lengkap. Pastikan nama dan opsi terisi.`);
                return;
            }
        }

        // Prepare data
        const groups = variantGroups.map(g => ({
            name: g.name,
            options: g.options
        }));

        // Save to hidden input
        variantGroupsInput.value = JSON.stringify(groups);

        // For create mode (no product SKU yet), generate locally
        const productSku = manager.dataset.productSku;
        if (!productSku || productSku === '') {
            variants = generateCombinationsLocally(groups);
            renderVariantsTable();
            renderBulkPricingOptions();
            saveVariantsData(); // Explicitly save
            return;
        }

        // For edit mode, call API to generate
        try {
            const response = await fetch(`/products/${productSku}/variants/generate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ variant_groups: groups })
            });

            if (!response.ok) {
                throw new Error('API request failed');
            }

            const data = await response.json();
            variants = data.combinations;
            renderVariantsTable();
            renderBulkPricingOptions();
        } catch (error) {
            console.error('Failed to generate combinations', error);
            alert('Gagal generate kombinasi. Silakan coba lagi.');
        }
    });

    function renderVariantsTable() {
        if (variants.length === 0) {
            variantsTbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-500 text-sm">Belum ada variasi</td></tr>';
            return;
        }

        variantsTbody.innerHTML = '';
        variants.forEach((variant, index) => {
            const row = document.createElement('tr');
            const displayName = Object.values(variant.variant_attributes).join(' / ');

            // Calculate margin
            const cost = parseFloat(variant.cost_price) || 0;
            const sell = parseFloat(variant.selling_price) || 0;
            const marginPct = (sell > 0 && cost > 0) ? ((sell - cost) / sell) * 100 : 0;
            const minMargin = window.minMarginPercent || 0;
            const marginClass = marginPct < minMargin ? 'text-red-600 font-semibold' : 'text-green-600 font-semibold';

            row.innerHTML = `
                <td class="px-4 py-2 text-sm font-mono">${variant.sku}</td>
                <td class="px-4 py-2 text-sm">${displayName}</td>
                <td class="px-4 py-2">
                    <input type="number"
                        data-variant-index="${index}"
                        data-field="cost_price"
                        value="${variant.cost_price || 0}"
                        step="1"
                        min="0"
                        class="w-full px-2 py-1 border border-gray-300 rounded text-right focus:outline-none focus:border-primary">
                </td>
                <td class="px-4 py-2">
                    <input type="number"
                        data-variant-index="${index}"
                        data-field="selling_price"
                        value="${variant.selling_price || 0}"
                        step="1"
                        min="0"
                        class="w-full px-2 py-1 border border-gray-300 rounded text-right focus:outline-none focus:border-primary">
                </td>
                <td class="px-4 py-2">
                    <span class="text-sm ${marginClass}" data-margin-display="${index}">
                        ${marginPct > 0 ? marginPct.toFixed(1) + '%' : '-'}
                    </span>
                </td>
                <td class="px-4 py-2">
                    <input type="number"
                        data-variant-index="${index}"
                        data-field="weight_grams"
                        value="${variant.weight_grams || 0}"
                        step="1"
                        min="0"
                        class="w-full px-2 py-1 border border-gray-300 rounded text-center focus:outline-none focus:border-primary">
                </td>
            `;
            variantsTbody.appendChild(row);
        });

        // Attach event listeners
        variantsTbody.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', handleVariantInput);
        });

        saveVariantsData();
    }

    function handleVariantInput(e) {
        const index = parseInt(e.target.dataset.variantIndex);
        const field = e.target.dataset.field;
        const value = e.target.value;

        if (variants[index]) {
            variants[index][field] = parseFloat(value) || 0;

            // Update margin display if cost or selling price changed
            if (field === 'cost_price' || field === 'selling_price') {
                updateVariantMargin(index);
            }

            saveVariantsData();
        }
    }

    function updateVariantMargin(index) {
        const variant = variants[index];
        const cost = parseFloat(variant.cost_price) || 0;
        const sell = parseFloat(variant.selling_price) || 0;
        const marginPct = (sell > 0 && cost > 0) ? ((sell - cost) / sell) * 100 : 0;
        const minMargin = window.minMarginPercent || 0;

        const marginDisplay = document.querySelector(`[data-margin-display="${index}"]`);
        if (marginDisplay) {
            marginDisplay.textContent = marginPct > 0 ? marginPct.toFixed(1) + '%' : '-';

            // Update color based on margin
            marginDisplay.className = `text-sm ${marginPct < minMargin ? 'text-red-600 font-semibold' : 'text-green-600 font-semibold'}`;
        }
    }

    function saveVariantsData() {
        const jsonString = JSON.stringify(variants);
        variantsInput.value = jsonString;
    }

    function renderBulkPricingOptions() {
        // Clear existing rules
        bulkPricingRules.innerHTML = '';
    }

    addBulkRuleBtn.addEventListener('click', function() {
        if (variantGroups.length === 0) {
            alert('Generate kombinasi terlebih dahulu.');
            return;
        }

        const ruleEl = document.createElement('div');
        ruleEl.className = 'p-4 border border-gray-300 rounded-lg bg-white';

        let optionsHtml = '<option value="">Pilih atribut</option>';
        variantGroups.forEach(group => {
            optionsHtml += `<optgroup label="${group.name}">`;
            group.options.forEach(opt => {
                optionsHtml += `<option value="${group.name}|${opt}">${group.name}: ${opt}</option>`;
            });
            optionsHtml += `</optgroup>`;
        });

        ruleEl.innerHTML = `
            <div class="grid grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Atribut</label>
                    <select class="bulk-attribute w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:border-primary">
                        ${optionsHtml}
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Harga Modal</label>
                    <input type="number" class="bulk-cost-price w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:border-primary" step="1" min="0">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Harga Jual</label>
                    <input type="number" class="bulk-selling-price w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:border-primary" step="1" min="0">
                </div>
                <div class="flex items-end">
                    <button type="button" class="remove-bulk-rule w-full px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition text-sm">Hapus</button>
                </div>
            </div>
        `;

        bulkPricingRules.appendChild(ruleEl);

        ruleEl.querySelector('.remove-bulk-rule').addEventListener('click', function() {
            ruleEl.remove();
        });
    });

    applyBulkPricingBtn.addEventListener('click', function() {
        const rules = [];
        bulkPricingRules.querySelectorAll('.p-4').forEach(ruleEl => {
            const attrSelect = ruleEl.querySelector('.bulk-attribute');
            const costInput = ruleEl.querySelector('.bulk-cost-price');
            const sellingInput = ruleEl.querySelector('.bulk-selling-price');

            if (attrSelect.value) {
                const [attribute, value] = attrSelect.value.split('|');
                rules.push({
                    attribute,
                    value,
                    cost_price: parseFloat(costInput.value) || null,
                    selling_price: parseFloat(sellingInput.value) || null
                });
            }
        });

        if (rules.length === 0) {
            alert('Tambahkan minimal 1 aturan harga.');
            return;
        }

        // Apply rules to variants
        rules.forEach(rule => {
            variants.forEach(variant => {
                if (variant.variant_attributes[rule.attribute] === rule.value) {
                    if (rule.cost_price !== null) {
                        variant.cost_price = rule.cost_price;
                    }
                    if (rule.selling_price !== null) {
                        variant.selling_price = rule.selling_price;
                    }
                }
            });
        });

        renderVariantsTable();
        alert('Harga massal berhasil diterapkan!');
    });

    // Render Variant Images Tab
    function renderVariantImagesTab() {

        if (!variantImagesContainer) {
            console.warn('variantImagesContainer not found');
            return;
        }

        if (variantGroups.length === 0) {
            variantImagesContainer.innerHTML = `
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-500 text-sm">Tambahkan grup variasi terlebih dahulu</p>
                    <p class="text-gray-400 text-xs mt-1">Klik tab "Variasi" untuk membuat kelompok variasi</p>
                </div>
            `;
            return;
        }

        variantImagesContainer.innerHTML = '';

        variantGroups.forEach(group => {
            // Safety check
            if (!group.name) {
                console.warn('Skipping group with no name:', group);
                return;
            }
            if (!Array.isArray(group.options) || group.options.length === 0) {
                console.warn('Skipping group with no options:', group);
                return;
            }


            const groupCard = document.createElement('div');
            groupCard.className = 'bg-white border border-gray-200 rounded-lg overflow-hidden';

            // Create options list using DOM manipulation instead of HTML string
            const optionsList = document.createElement('div');

            group.options.forEach(option => {
                const imageData = variantImages[group.name]?.[option] || null;
                const hasImage = imageData !== null;

                // Support both old format (just ID) and new format (object with id and url)
                let imageUrl = null;
                if (hasImage) {
                    if (typeof imageData === 'object' && imageData.url) {
                        imageUrl = imageData.url;
                    }
                }

                // Create row
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between p-4 border-b last:border-b-0 hover:bg-gray-50 transition';

                // Left side
                const leftSide = document.createElement('div');
                leftSide.className = 'flex items-center space-x-4';

                // Drag icon
                leftSide.innerHTML = `
                    <div class="flex items-center justify-center w-6 h-6">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </div>
                `;

                // Preview box
                const previewBox = document.createElement('div');
                previewBox.className = 'w-12 h-12 border-2 border-gray-300 rounded-lg overflow-hidden bg-white flex items-center justify-center';
                previewBox.dataset.variantPreview = `${group.name}|${option}`;

                if (hasImage && imageUrl) {
                    const img = document.createElement('img');
                    img.src = imageUrl;
                    img.alt = option;
                    img.className = 'w-full h-full object-cover';
                    previewBox.appendChild(img);
                } else {
                    previewBox.innerHTML = `
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    `;
                }
                leftSide.appendChild(previewBox);

                // Option name
                const optionLabel = document.createElement('span');
                optionLabel.className = 'font-medium text-gray-900';
                optionLabel.textContent = option;
                leftSide.appendChild(optionLabel);

                row.appendChild(leftSide);

                // Right side - buttons
                const rightSide = document.createElement('div');
                rightSide.className = 'flex items-center space-x-2';

                // Remove button (only if has image)
                if (hasImage) {
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn-remove-variant-image p-2 text-gray-400 hover:text-red-500 transition';
                    removeBtn.dataset.group = group.name;
                    removeBtn.dataset.option = option;
                    removeBtn.title = 'Hapus foto';
                    removeBtn.innerHTML = `
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    `;
                    rightSide.appendChild(removeBtn);
                }

                // Upload button
                const uploadBtn = document.createElement('button');
                uploadBtn.type = 'button';
                uploadBtn.className = 'btn-upload-variant-image p-2 text-gray-400 hover:text-primary transition';
                uploadBtn.dataset.group = group.name;
                uploadBtn.dataset.option = option;
                uploadBtn.title = hasImage ? 'Ganti foto' : 'Upload foto';
                uploadBtn.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                `;
                rightSide.appendChild(uploadBtn);

                row.appendChild(rightSide);
                optionsList.appendChild(row);
            });

            // Create header
            const header = document.createElement('div');
            header.className = 'bg-gray-50 px-4 py-3 border-b border-gray-200';
            header.innerHTML = `
                <h4 class="font-semibold text-gray-700 text-sm">Grup Variasi</h4>
                <p class="text-lg font-bold text-gray-900 mt-1">${group.name}</p>
            `;

            groupCard.appendChild(header);
            groupCard.appendChild(optionsList);

            variantImagesContainer.appendChild(groupCard);
        });

        // Attach event listeners
        document.querySelectorAll('.btn-upload-variant-image').forEach(btn => {
            btn.addEventListener('click', function() {
                const groupName = this.dataset.group;
                const optionName = this.dataset.option;
                openMediaPickerForVariant(groupName, optionName);
            });
        });

        document.querySelectorAll('.btn-remove-variant-image').forEach(btn => {
            btn.addEventListener('click', function() {
                const groupName = this.dataset.group;
                const optionName = this.dataset.option;
                if (confirm(`Hapus foto untuk ${groupName}: ${optionName}?`)) {
                    removeVariantImage(groupName, optionName);
                }
            });
        });

    }

    function openMediaPickerForVariant(groupName, optionName) {

        // Open media picker with callback
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                type: 'product_photo',
                title: `Pilih Foto untuk ${groupName}: ${optionName}`,
                listUrl: '/media/product-photo/list',
                uploadUrl: '/media',
                context: {
                    variant_group: groupName,
                    variant_option: optionName
                },
                onSelect: function(media) {
                    // Set the selected media for this variant option (pass full media object)
                    setVariantImage(groupName, optionName, media);
                }
            });
        } else {
            console.error('openMediaPicker function not found. Make sure media-picker.js is loaded.');
            alert('Media picker belum tersedia. Pastikan media-picker.js sudah dimuat.');
        }
    }

    function setVariantImage(groupName, optionName, media) {

        if (!variantImages[groupName]) {
            variantImages[groupName] = {};
        }

        // Store media object with id and url for preview
        if (typeof media === 'object') {
            variantImages[groupName][optionName] = {
                id: media.id,
                url: media.url
            };
        } else {
            // Fallback if only ID is provided
            variantImages[groupName][optionName] = media;
        }

        saveVariantImagesData();
        renderVariantImagesTab();
        updateVariantsWithImages();

    }

    function removeVariantImage(groupName, optionName) {
        if (variantImages[groupName]) {
            delete variantImages[groupName][optionName];
            if (Object.keys(variantImages[groupName]).length === 0) {
                delete variantImages[groupName];
            }
        }
        saveVariantImagesData();
        renderVariantImagesTab();
        updateVariantsWithImages();
    }

    function saveVariantImagesData() {
        variantImagesInput.value = JSON.stringify(variantImages);
    }

    function updateVariantsWithImages() {
        console.log('=== DEBUG: updateVariantsWithImages called ===');
        console.log('variantImages:', JSON.stringify(variantImages, null, 2));

        // Update existing variants with matching images
        variants.forEach((variant, index) => {

            // Reset variant_image_id first
            variant.variant_image_id = null;

            console.log(`\nProcessing variant ${index}:`, variant.variant_attributes);

            // IMPORTANT: Apply image based on the GROUP that has images uploaded
            // For example: if "Warna: Hijau" has image, all variants with Warna=Hijau get that image
            // The image is tied to the GROUP+OPTION, not to the specific combination
            for (const [groupName, options] of Object.entries(variantImages)) {
                const attributeValue = variant.variant_attributes[groupName];
                console.log(`  Checking group "${groupName}", variant has value: "${attributeValue}"`);
                console.log(`  Available options in this group:`, Object.keys(options));

                if (attributeValue && options[attributeValue]) {
                    // Extract ID if it's an object, otherwise use as-is
                    const imageData = options[attributeValue];
                    const imageId = (typeof imageData === 'object' && imageData.id) ? imageData.id : imageData;
                    console.log(`  ✓ MATCH! Setting variant_image_id = ${imageId}`);
                    variant.variant_image_id = imageId;
                    break; // Use first matching group with image
                } else {
                    console.log(`  ✗ No match for "${attributeValue}" in group "${groupName}"`);
                }
            }

            console.log(`  Final variant_image_id: ${variant.variant_image_id}`);
        });

        console.log('\n=== Final variants data ===');
        console.log(variants.map(v => ({
            attrs: v.variant_attributes,
            image_id: v.variant_image_id
        })));

        saveVariantsData();
    }

    // Tab switching
    document.querySelectorAll('[data-variant-tab]').forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.variantTab;

            // Update button styles
            document.querySelectorAll('[data-variant-tab]').forEach(b => {
                b.classList.remove('border-primary', 'text-primary');
                b.classList.add('border-transparent', 'text-gray-600');
            });
            this.classList.add('border-primary', 'text-primary');
            this.classList.remove('border-transparent', 'text-gray-600');

            // Show/hide panels
            document.querySelectorAll('[data-variant-panel]').forEach(panel => {
                panel.classList.add('hidden');
            });
            document.querySelector(`[data-variant-panel="${tab}"]`).classList.remove('hidden');

            // Render variant images tab when switched to
            if (tab === 'gambar') {
                renderVariantImagesTab();
            }
        });
    });

    // Expose updateVariantsWithImages to global scope so it can be called before form submit
    window.updateVariantsWithImages = updateVariantsWithImages;
})();
</script>
