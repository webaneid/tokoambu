// Category Autocomplete
document.getElementById('category_search').addEventListener('input', async (e) => {
    const query = e.target.value.trim();
    const suggestionsDiv = document.getElementById('category_suggestions');
    
    if (query.length === 0) {
        suggestionsDiv.classList.add('hidden');
        document.getElementById('category_id').value = '';
        return;
    }

    try {
        const response = await fetch(`/api/product-categories?search=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.length === 0) {
            suggestionsDiv.innerHTML = '<div class="p-3 text-gray-500 text-center">Tidak ada kategori ditemukan</div>';
            suggestionsDiv.classList.remove('hidden');
        } else {
            suggestionsDiv.innerHTML = data.map(cat => `
                <div class="p-3 hover:bg-blue-50 cursor-pointer border-b last:border-b-0 transition" data-id="${cat.id}" data-name="${cat.name}">
                    <p class="font-medium text-gray-900">${cat.name}</p>
                    ${cat.description ? `<p class="text-sm text-gray-500 mt-1">${cat.description}</p>` : ''}
                </div>
            `).join('');
            suggestionsDiv.classList.remove('hidden');
            
            document.querySelectorAll('#category_suggestions div[data-id]').forEach(item => {
                item.addEventListener('click', () => {
                    document.getElementById('category_search').value = item.dataset.name;
                    document.getElementById('category_id').value = item.dataset.id;
                    suggestionsDiv.classList.add('hidden');
                });
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        suggestionsDiv.innerHTML = '<div class="p-3 text-red-500 text-center">Error memuat kategori</div>';
        suggestionsDiv.classList.remove('hidden');
    }
});

// Hide category suggestions when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('#category_search') && !e.target.closest('#category_suggestions')) {
        document.getElementById('category_suggestions').classList.add('hidden');
    }
});

// Open Category Modal
document.getElementById('btn_add_category').addEventListener('click', () => {
    document.getElementById('modal_add_category').classList.remove('hidden');
});

// Close Category Modal
document.getElementById('btn_close_modal').addEventListener('click', () => {
    document.getElementById('modal_add_category').classList.add('hidden');
    document.getElementById('form_add_category').reset();
    document.getElementById('error_category_name').classList.add('hidden');
});

// Submit Category Form
document.getElementById('form_add_category').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(document.getElementById('form_add_category'));
    const errorDiv = document.getElementById('error_category_name');
    
    try {
        const response = await fetch('/api/product-categories', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        });
        
        const data = await response.json();

        if (!response.ok) {
            if (data.errors && data.errors.name) {
                errorDiv.textContent = data.errors.name[0];
                errorDiv.classList.remove('hidden');
            }
            return;
        }

        // API returns { success, message, data: { id, name, ... } }
        const category = data.data || data;
        document.getElementById('category_search').value = category.name;
        document.getElementById('category_id').value = category.id;
        document.getElementById('modal_add_category').classList.add('hidden');
        document.getElementById('form_add_category').reset();
        errorDiv.classList.add('hidden');
    } catch (error) {
        console.error('Error:', error);
        errorDiv.textContent = 'Error menyimpan kategori';
        errorDiv.classList.remove('hidden');
    }
});

// Supplier Autocomplete
document.getElementById('supplier_search').addEventListener('input', async (e) => {
    const query = e.target.value.trim();
    const suggestionsDiv = document.getElementById('supplier_suggestions');
    
    if (query.length === 0) {
        suggestionsDiv.classList.add('hidden');
        document.getElementById('supplier_id').value = '';
        return;
    }

    try {
        const response = await fetch(`/api/suppliers?search=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.length === 0) {
            suggestionsDiv.innerHTML = '<div class="p-3 text-gray-500 text-center">Tidak ada supplier ditemukan</div>';
            suggestionsDiv.classList.remove('hidden');
        } else {
            suggestionsDiv.innerHTML = data.map(sup => `
                <div class="p-3 hover:bg-blue-50 cursor-pointer border-b last:border-b-0 transition" data-id="${sup.id}" data-name="${sup.name}">
                    <p class="font-medium text-gray-900">${sup.name}</p>
                    ${sup.email ? `<p class="text-sm text-gray-500 mt-1">${sup.email}</p>` : ''}
                    ${sup.phone ? `<p class="text-sm text-gray-500">${sup.phone}</p>` : ''}
                </div>
            `).join('');
            suggestionsDiv.classList.remove('hidden');
            
            document.querySelectorAll('#supplier_suggestions div[data-id]').forEach(item => {
                item.addEventListener('click', () => {
                    document.getElementById('supplier_search').value = item.dataset.name;
                    document.getElementById('supplier_id').value = item.dataset.id;
                    suggestionsDiv.classList.add('hidden');
                });
            });
        }
    } catch (error) {
        console.error('Error loading suppliers:', error);
        suggestionsDiv.innerHTML = '<div class="p-3 text-red-500 text-center">Error memuat supplier</div>';
        suggestionsDiv.classList.remove('hidden');
    }
});

// Hide supplier suggestions when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('#supplier_search') && !e.target.closest('#supplier_suggestions')) {
        document.getElementById('supplier_suggestions').classList.add('hidden');
    }
});

// Open Supplier Modal
document.getElementById('btn_add_supplier').addEventListener('click', () => {
    document.getElementById('modal_add_supplier').classList.remove('hidden');
});

// Close Supplier Modal
document.getElementById('btn_close_supplier_modal').addEventListener('click', () => {
    document.getElementById('modal_add_supplier').classList.add('hidden');
    document.getElementById('form_add_supplier').reset();
    document.getElementById('error_supplier_name').classList.add('hidden');
});

// Submit Supplier Form
document.getElementById('form_add_supplier').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(document.getElementById('form_add_supplier'));
    const errorDiv = document.getElementById('error_supplier_name');
    
    try {
        const response = await fetch('/api/suppliers', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        });
        
        const data = await response.json();

        if (!response.ok) {
            if (data.errors && data.errors.name) {
                errorDiv.textContent = data.errors.name[0];
                errorDiv.classList.remove('hidden');
            }
            return;
        }

        // API returns { success, message, data: { id, name, ... } }
        const supplier = data.data || data;
        document.getElementById('supplier_search').value = supplier.name;
        document.getElementById('supplier_id').value = supplier.id;
        document.getElementById('modal_add_supplier').classList.add('hidden');
        document.getElementById('form_add_supplier').reset();
        errorDiv.classList.add('hidden');
    } catch (error) {
        console.error('Error:', error);
        errorDiv.textContent = 'Error menyimpan supplier';
        errorDiv.classList.remove('hidden');
    }
});
